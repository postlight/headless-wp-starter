<?php
/**
 * Uploading functionality with Google Cloud Storage
 *
 * Hijacks the uploading functionality in WordPress to use Google Cloud Storage
 * for the media library.
 *
 * Copyright 2017 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Google\Cloud\Storage\WordPress\Uploads;

defined('ABSPATH') or die('No direct access!');

Uploads::bootstrap_settings();
Uploads::bootstrap();

/**
 * Functionalities for media upload.
 */
class Uploads
{
    const USE_HTTPS_OPTION = 'gcs_use_https_for_media';
    const BUCKET_OPTION = 'gcs_bucket';

    /**
     * Register our filter.
     */
    public static function bootstrap()
    {
        add_filter('upload_dir', __CLASS__ . '::filter_upload_dir');
        add_filter('wp_delete_file', __CLASS__ . '::filter_delete_file');
    }

    /**
     * Swap the upload dir with gs:// path in the GCS bucket.
     */
    public static function filter_upload_dir($values)
    {
        $bucket = get_option(self::BUCKET_OPTION, '');
        if ($bucket === '') {
            // Do nothing without the bucket name.
            return $values;
        }
        $basedir = sprintf('gs://%s/%s', $bucket, get_current_blog_id());
        $use_https = get_option(self::USE_HTTPS_OPTION, false);
        $baseurl = sprintf(
            '%s://storage.googleapis.com/%s/%s',
            $use_https ? 'https' : 'http',
            $bucket,
            get_current_blog_id()
        );
        $values = array(
            'path' => $basedir . $values['subdir'],
            'subdir' => $values['subdir'],
            'error' => false,
        );
        $values['url'] = rtrim($baseurl . $values['subdir'], '/');
        $values['basedir'] = $basedir;
        $values['baseurl'] = $baseurl;
        return $values;
    }

    /**
     * Unlink files starts with 'gs://'
     *
     * This is needed because WordPress thinks a path starts with 'gs://' is
     * not an absolute path and manipulate it in a wrong way before unlinking
     * intermediate files.
     *
     * TODO: Use `path_is_absolute` filter when a bug below is resolved:
     *       https://core.trac.wordpress.org/ticket/38907#ticket
     */
    public static function filter_delete_file($file)
    {
        $prefix = 'gs://';
        if (substr($file, 0, strlen($prefix)) === $prefix) {
            @ unlink($file);
        }
        return $file;
    }

    /**
     * Add our options.
     */
    public static function bootstrap_settings()
    {
        add_action('gcs_register_settings', __CLASS__ . '::register_settings');
    }

    /**
     * Display the input form for the bucket.
     */
    public static function bucket_form()
    {
        $bucket = get_option(self::BUCKET_OPTION, '');
        echo sprintf(
            '<input id="%s" name="%s" type="text" value="%s">',
            self::BUCKET_OPTION,
            self::BUCKET_OPTION,
            esc_attr($bucket)
        );
        echo '<p class="description">'
            . __('GCS bucket name for media upload', 'gcp')
            . '</p>';
    }

    /**
     * Display the input form for use_https_for_media.
     */
    public static function use_https_form()
    {
        $enabled = get_option(self::USE_HTTPS_OPTION, false);
        echo sprintf(
            '<input id="%s", name="%s" type="checkbox" %s>',
            self::USE_HTTPS_OPTION,
            self::USE_HTTPS_OPTION,
            checked($enabled, true, false)
        );
        echo '<p class="description">'
            . __(
                'Check to serve uploaded media files over HTTPS. '
                . '<strong>Note:</strong>This setting only affects new uploads,'
                . ' it will not change the HTTP scheme for files previously '
                . 'uploaded',
                'gcp')
            . '</p>';
    }

    /**
     * Validate the bucket name in the form.
     */
    public static function validate_bucket($input)
    {
        $path = sprintf('gs://%s/', $input);
        if (!is_writable($path)) {
            add_settings_error(
                'gcs_settings',
                'invalid-bucket',
                __('The bucket does not exist, or is not writable', 'gcp'));
            return get_option(self::BUCKET_OPTION, '');
        }
        return $input;
    }

    /**
     * Validate the value for the use_https form.
     */
    public static function validate_use_https($input)
    {
        return (bool) $input;
    }

    /**
     * Register our options.
     */
    public static function register_settings()
    {
        add_option(self::USE_HTTPS_OPTION, true);
        register_setting(
            'gcs_settings',
            self::BUCKET_OPTION,
            __CLASS__ . '::validate_bucket'
        );
        register_setting(
            'gcs_settings',
            self::USE_HTTPS_OPTION,
            __CLASS__ . '::validate_use_https'
        );
        add_settings_section(
            'gcs_media',
            __('Media upload configurations', 'gcs'),
            null,
            'gcs'
        );
        add_settings_field(
            self::BUCKET_OPTION,
            __('Bucket name for media upload', 'gcs'),
            __CLASS__ . '::bucket_form',
            'gcs',
            'gcs_media'
        );
        add_settings_field(
            self::USE_HTTPS_OPTION,
            __('Use secure URLs for serving media files', 'gcs'),
            __CLASS__ . '::use_https_form',
            'gcs',
            'gcs_media'
        );
    }
}
