<?php

/**
 * Class WPMDBPro_Media_Files_Base
 *
 * Base class that holds common functionality required by both WPMDBPro_Media_Files_Local and
 * WPMDBPro_Media_Files_Remote. Extends WPMDBPro_Addon as we require functionality included in the WPMDB base classes.
 * Never instantiated on its own.
 */
class WPMDBPro_Media_Files_Base extends WPMDBPro_Addon {

	/**
	 * The number of seconds a batch should run for when queueing or comparing attachments
	 *
	 * @var int $media_diff_batch_time
	 */
	protected $media_diff_batch_time;

	/**
	 * The max number of attachments in a batch
	 *
	 * @var int $media_diff_batch_limit
	 */
	protected $media_diff_batch_limit;

	/**
	 * The number of seconds a batch should run for when scanning for files
	 *
	 * @var int $media_files_batch_time_limit
	 */
	protected $media_files_batch_time_limit;

	protected $accepted_fields;

	public function __construct( $plugin_file_path ) {
		parent::__construct( $plugin_file_path );

		$this->media_diff_batch_time        = apply_filters( 'wpmdb_media_diff_batch_time', 10 );
		$this->media_diff_batch_limit       = apply_filters( 'wpmdb_media_diff_batch_limit', 300 );
		$this->media_files_batch_time_limit = apply_filters( 'wpmdb_media_files_batch_time_limit', 15 );

		$this->accepted_fields = array(
			'media_files',
			'remove_local_media',
			'media_migration_option',
			'mf_select_subsites',
			'mf_selected_subsites',
		);

		add_filter( 'wpmdb_accepted_profile_fields', array( $this, 'accepted_profile_fields' ) );
		add_filter( 'wpmdbmf_include_subsite', array( $this, 'include_subsite' ), 10, 2 );
	}

	/**
	 * Whitelist media setting fields for use in AJAX save in core
	 *
	 * @param array $profile_fields Array of profile fields
	 *
	 * @return array Updated array of profile fields
	 */
	function accepted_profile_fields( $profile_fields ) {
		return array_merge( $profile_fields, $this->accepted_fields );
	}

	/**
	 * Return total number of local attachments
	 *
	 * For multisite returns the total number of attachments for all blogs
	 *
	 * @return int Total number of local attachments
	 */
	function get_local_attachments_count() {
		global $wpdb;
		$count = 0;

		if ( is_multisite() ) {
			$blogs = $this->get_blog_ids();
			foreach ( $blogs as $blog ) {
				$blog_prefix = $wpdb->get_blog_prefix( $blog );
				$count += $this->get_attachments_count( $blog_prefix );
			}
		} else {
			$count += $this->get_attachments_count( $wpdb->base_prefix );
		}

		return $count;
	}

	/**
	 * Retrieve the count of attachments for a blog
	 *
	 * @param string $prefix Blog db prefix
	 *
	 * @return int Number of attachments
	 */
	function get_attachments_count( $prefix ) {
		return $this->get_attachment_results( $prefix, 'count' );
	}

	/**
	 * Get all attachments for a blog
	 *
	 * @param string $prefix Blog db prefix
	 * @param int    $blog   Blog ID
	 * @param int    $limit  Limit passed to SQL query (for batching)
	 * @param array  $offset Offset (blog ID, post ID) passed to SQL query (for batching)
	 *
	 * @return array Attachments
	 */
	function get_attachments( $prefix, $blog, $limit, $offset ) {
		return $this->get_attachment_results( $prefix, 'rows', array( $blog, $offset, $limit ) );
	}

	/**
	 * Utility function for retrieving attachments
	 *
	 * @param string $prefix      Blog db prefix
	 * @param string $result_type Type of result we want to retrieve (count / rows / row)
	 * @param array  $args        Dependant of $result_type.
	 *                            'count'  - none
	 *                            'rows'   - $blog_id, $offset, $limit
	 *                            'row'    - $blog_id, $filename
	 *
	 * @return array Attachments
	 */
	function get_attachment_results( $prefix, $result_type = 'rows', $args = array() ) {
		global $wpdb;

		$core = " FROM `{$prefix}posts`
			INNER JOIN `{$prefix}postmeta` pm1 ON `{$prefix}posts`.`ID` = pm1.`post_id` AND pm1.`meta_key` = '_wp_attached_file'
			LEFT OUTER JOIN `{$prefix}postmeta` pm2 ON `{$prefix}posts`.`ID` = pm2.`post_id` AND pm2.`meta_key` = '_wp_attachment_metadata'
			WHERE `{$prefix}posts`.`post_type` = 'attachment' ";

		if ( 'count' == $result_type ) {
			$sql = 'SELECT COUNT(*)' . $core;

			return $wpdb->get_var( $sql );
		}

		$select = "SELECT `{$prefix}posts`.`ID` AS 'ID', `{$prefix}posts`.`post_modified_gmt` AS 'date', pm1.`meta_value` AS 'file', pm2.`meta_value` AS 'metadata', %d AS 'blog_id'";
		$sql    = $select . $core;

		if ( 'rows' == $result_type ) {
			$action = 'get_results';
			$sql .= "AND `{$prefix}posts`.`ID` > %d
				ORDER BY `{$prefix}posts`.`ID`
				LIMIT %d";
		} else {
			$action = 'get_row';
			$sql .= 'AND pm1.`meta_value` = %s';
		}

		$sql = $wpdb->prepare( $sql, $args );

		$results = $wpdb->$action( $sql, ARRAY_A );

		return $results;
	}

	/**
	 * Return a batch of attachments across all blogs
	 *
	 * @param mixed $blogs  Blogs
	 * @param int   $limit  Max attachments limit (for batching)
	 * @param array $offset Optional offset (blog ID, post ID) to use instead of $blog['last_post']
	 *
	 * @return array Local attachments and blogs
	 */
	function get_local_attachments_batch( $blogs, $limit, $offset = null ) {
		$all_limit = $limit;

		if ( ! is_array( $blogs ) ) {
			$blogs = unserialize( stripslashes( $blogs ) );
		}

		$all_attachments = array();
		$all_count       = 0;

		foreach ( $blogs as $blog_id => $blog ) {
			if ( 1 == $blog['processed'] ) {
				continue;
			}

			$blog_offset = $blog['last_post'];
			if ( is_array( $offset ) ) {
				if ( $offset[0] > $blog_id ) {
					$blogs[ $blog_id ]['processed'] = 1;
					continue;
				} elseif ( $blog_id == $offset[0] ) {
					$blog_offset = $offset[1];
				}
			}


			$attachments = $this->get_attachments( $blog['prefix'], $blog_id, $limit, $blog_offset );
			$count       = count( $attachments );
			if ( 0 == $count ) {
				// no more attachments, record the blog ID to skip next time
				$blogs[ $blog_id ]['processed'] = 1;
			} else {
				$all_count += $count;
				// process attachments for sizes files
				$attachments = array_map( array( $this, 'process_attachment_data' ), $attachments );
				$attachments = array_filter( $attachments );

				$all_attachments[ $blog_id ] = $attachments;
			}

			if ( $all_count >= $all_limit ) {
				break;
			}

			$limit = $limit - $count;
		}

		$return = array(
			'attachments' => $all_attachments,
			'blogs'       => $blogs,
		);

		return $return;
	}

	/**
	 * Return all attachment files across all blogs
	 *
	 * @param array $offset (blog ID, attachment ID) offset
	 *
	 * @return array Local media attachment files and last attachment ID
	 */
	function get_local_media_attachment_files_batch( $offset ) {
		$local_media_attachment_files = array();
		$last_attachment_id           = 0;

		$blogs                   = $this->get_blogs();
		$local_media_attachments = $this->get_local_attachments_batch( $blogs, $this->media_diff_batch_limit, $offset );
		$last_blog_id            = 1;

		// Get file paths from attachments
		foreach ( $local_media_attachments['attachments'] as $blog_id => $attachments ) {
			foreach ( $attachments as $attachment ) {
				if ( ! empty( $attachment['file_size'] ) ) {
					$local_media_attachment_files[] = $attachment['file'];
					$last_blog_id                   = $blog_id;
					$last_attachment_id             = $attachment['ID'];
				}

				if ( isset( $attachment['sizes'] ) && ! empty( $attachment['sizes'] ) ) {
					foreach ( $attachment['sizes'] as $size ) {
						if ( ! empty( $size['file_size'] ) ) {
							$local_media_attachment_files[] = $size['file'];
						}
					}
				}
			}
		}

		return array(
			'files'              => $local_media_attachment_files,
			'last_blog_id'       => $last_blog_id,
			'last_attachment_id' => $last_attachment_id,
		);
	}

	/**
	 * Return a batch of local media files
	 *
	 * Scans the uploads directory for physical files
	 *
	 * @param string $start_file The file or directory to start at
	 *
	 * @return array Local media files
	 */
	function get_local_media_files_batch( $start_file ) {
		$local_media_files = array();

		$upload_dir = $this->uploads_dir();

		if ( ! $this->filesystem->file_exists( $upload_dir ) ) {
			return $local_media_files;
		}

		// Check if we're just kicking off with the root uploads dir
		if ( empty( $start_file ) ) {
			$this->get_local_media_files_batch_recursive( '', '', $local_media_files );
		} else {
			$dir            = dirname( $start_file );
			$start_filename = basename( $start_file );
			$this->get_local_media_files_batch_recursive( trailingslashit( $dir ), $start_filename, $local_media_files );

			$dirs = explode( '/', $dir );
			while ( $dirs ) {
				$start_filename = array_pop( $dirs );
				$dir            = trailingslashit( implode( '/', $dirs ) );
				$this->get_local_media_files_batch_recursive( $dir, $start_filename, $local_media_files );
			}
		}

		return $local_media_files;
	}

	/**
	 * Recursively go through uploads directories and get a batch of media files.
	 * Stops when it has scanned all files/directories or after it has run for
	 * $this->media_files_batch_time_limit seconds, whichever comes first.
	 *
	 * @param string $dir               The directory to start in
	 * @param string $start_filename    The file or directory to start at within $dir
	 * @param array  $local_media_files Array to populate with media files found
	 */
	function get_local_media_files_batch_recursive( $dir, $start_filename, &$local_media_files ) {
		$upload_dir = $this->uploads_dir();

		static $allowed_mime_types;
		if ( is_null( $allowed_mime_types ) ) {
			$allowed_mime_types = array_flip( get_allowed_mime_types() );
		}

		static $finish_time;
		if ( is_null( $finish_time ) ) {
			$finish_time = microtime( true ) + $this->media_files_batch_time_limit;
		}

		$dir       = ( '/' == $dir ) ? '' : $dir;
		$dir_path  = $upload_dir . $dir;
		$sub_paths = glob( $dir_path . '*', GLOB_MARK );

		// Get all the files except the one we use to store backups.
		$wpmdb_upload_folder = $this->get_upload_info();
		$pattern             = '/' . preg_quote( $wpmdb_upload_folder, '/' ) . '/';
		$files               = preg_grep( $pattern, $sub_paths ? $sub_paths : array(), PREG_GREP_INVERT );

		$reached_start_file = false;

		foreach ( $files as $file_path ) {
			if ( microtime( true ) >= $finish_time ) {
				break;
			}

			// Are we starting from a certain file within the directory?
			// If so, we skip all the files that come before it.
			if ( $start_filename ) {
				if ( basename( $file_path ) == $start_filename ) {
					$reached_start_file = true;
					continue;
				} elseif ( ! $reached_start_file ) {
					continue;
				}
			}

			$short_file_path = str_replace( array( $upload_dir, '\\' ), array( '', '/' ), $file_path );

			// Is directory? We use this instead of is_dir() to save us an I/O call
			if ( substr( $file_path, -1 ) == DIRECTORY_SEPARATOR ) {
				$this->get_local_media_files_batch_recursive( $short_file_path, '', $local_media_files );
				continue;
			}

			// ignore files that we shouldn't touch, e.g. .php, .sql, etc
			$filetype = wp_check_filetype( $short_file_path );
			if ( ! isset( $allowed_mime_types[ $filetype['type'] ] ) ) {
				continue;
			}

			if ( apply_filters( 'wpmdbmf_exclude_local_media_file_from_removal', false, $upload_dir, $short_file_path, $this ) ) {
				continue;
			}

			$local_media_files[] = $short_file_path;
		}
	}

	/**
	 * Queues attachment file and image size files for migration if they exist on the source filesystem
	 *
	 * @param array $files_to_migrate List of files to migrate
	 * @param array $attachment       Attachment data
	 * @param bool  $local_attachment Used to compare if files actually exist locally
	 */
	function maybe_queue_attachment( &$files_to_migrate, $attachment, $local_attachment = false ) {
		if ( isset( $attachment['file_size'] ) && ( ! $local_attachment || ( $local_attachment && ! isset( $local_attachment['file_size'] ) ) ) ) {
			// if the remote attachment exists on the remote file system
			// and if a local attachment is supplied, if the file doesn't exist on local file system
			$files_to_migrate[ $attachment['file'] ] = $attachment['file_size'];
		}
		// check other image sizes of the attachment
		if ( empty( $attachment['sizes'] ) || apply_filters( 'wpmdb_exclude_resized_media', false ) ) {
			return;
		}
		foreach ( $attachment['sizes'] as $size ) {
			$original_file_name = $size['file'];
			// if dir_prefix is set, then the remote is a multisite and we need to compare the file without the subsite directory prefix
			if ( ! is_multisite() && $attachment['dir_prefix'] ) {
				$size['file'] = str_replace( $attachment['dir_prefix'], '', $size['file'] );
			}
			if ( isset( $size['file_size'] ) && ( ! $local_attachment || ( $local_attachment && ! $this->local_image_size_file_exists( $size, $local_attachment ) ) ) ) {
				// if the remote image size file exists on the remote file system
				$files_to_migrate[ $original_file_name ] = $size['file_size'];
			}
		}
	}

	/**
	 * Compare a batch of remote attachments with those on local site
	 *
	 * @param mixed $blogs           Blogs
	 * @param mixed $all_attachments Batch of attachments
	 * @param int   $progress        Progress count
	 *
	 * @return array Data to return to AJAX response
	 */
	function compare_remote_attachments( $blogs, $all_attachments, $progress ) {
		if ( ! is_array( $blogs ) ) {
			$blogs = unserialize( stripslashes( $blogs ) );
		}
		if ( ! is_array( $all_attachments ) ) {
			$all_attachments = unserialize( stripslashes( $all_attachments ) );
		}

		$files_to_migrate = array();
		$finish           = time() + $this->media_diff_batch_time;

		foreach ( $all_attachments as $blog_id => $attachments ) {
			foreach ( $attachments as $remote_attachment ) {

				if ( time() >= $finish ) {
					break;
				}

				// find local attachment
				$local_attachment = $this->find_attachment( $remote_attachment );
				if ( false === $local_attachment ) {
					// local attachment doesn't exist, definitely migrate remote
					$this->maybe_queue_attachment( $files_to_migrate, $remote_attachment );
				} else {
					// local attachment already exists
					// check the timestamps on the attachment
					$remote_timestamp = strtotime( $remote_attachment['date'] );
					$local_timestamp  = strtotime( $local_attachment['date'] );

					if ( $remote_timestamp != $local_timestamp ) {
						// timestamps are different, let's migrate remote
						$this->maybe_queue_attachment( $files_to_migrate, $remote_attachment );
					} else {
						// only migrate if the local files are missing
						$this->maybe_queue_attachment( $files_to_migrate, $remote_attachment, $local_attachment );
					}
				}

				$blogs[ $blog_id ]['last_post'] = $remote_attachment['ID'];
				$progress++;
			}
		}

		$return = array(
			'files_to_migrate'   => $files_to_migrate,
			'blogs'              => $blogs,
			'determine_progress' => $progress,
		);

		return $return;
	}

	/**
	 * Find an attachment in a specific blog
	 *
	 * @param array $attachment
	 *
	 * @return array|bool Attachment, false if not found
	 */
	function find_attachment( $attachment ) {
		global $wpdb;
		$prefix = $wpdb->base_prefix;

		if ( is_multisite() ) {
			$blog_ids = $this->get_blog_ids();

			// check the blog exists
			if ( ! in_array( $attachment['blog_id'], $blog_ids ) ) {
				return false;
			}

			$prefix = $wpdb->get_blog_prefix( $attachment['blog_id'] );
		}

		$filename = $attachment['file'];

		$dir_prefix = ( isset( $attachment['dir_prefix'] ) && strlen( $attachment['dir_prefix'] ) ) ? $attachment['dir_prefix'] : $this->get_dir_prefix( $attachment );
		// file names are stored in DB without dir prefix, so if the file has one then we need to remove it
		$filename = str_replace( $dir_prefix, '', $filename );

		$local_attachment = $this->get_attachment_results( $prefix, 'row', array( $attachment['blog_id'], $filename ) );

		if ( empty( $local_attachment ) ) {
			return false;
		}

		$local_attachment = $this->process_attachment_data( $local_attachment );

		return $local_attachment;
	}

	/**
	 * Process an attachment
	 *
	 * Adds the physical file size to an attachment including file sizes for all resized images
	 *
	 * @param array $attachment The attachment to process
	 *
	 * @return array The updated attachment
	 */
	function process_attachment_data( $attachment ) {
		// prepend site directory prefix for multisite blogs
		$attachment['dir_prefix'] = $this->get_dir_prefix( $attachment );
		if ( is_multisite() && $attachment['dir_prefix'] ) {
			$attachment['file'] = $attachment['dir_prefix'] . $attachment['file'];
		}

		// use the correct directory for image size files
		$upload_dir = str_replace( basename( $attachment['file'] ), '', $attachment['file'] );
		if ( ! empty( $attachment['metadata'] ) ) {
			$attachment['metadata'] = @unserialize( $attachment['metadata'] );
			if ( ! empty( $attachment['metadata']['sizes'] ) && is_array( $attachment['metadata']['sizes'] ) ) {
				foreach ( $attachment['metadata']['sizes'] as $size ) {
					if ( empty( $size['file'] ) ) {
						continue;
					}

					$size_data = array( 'file' => $upload_dir . $size['file'] );
					$size_data = $this->apply_file_size( $size_data );

					$attachment['sizes'][] = $size_data;
				}
			}
		}
		unset( $attachment['metadata'] );

		// get size of image on disk
		$attachment = $this->apply_file_size( $attachment );

		return $attachment;
	}

	/**
	 * Remove local files from the uploads directory
	 *
	 * @param mixed $local_files Files to remove
	 *
	 * @return array $errors Array of errors
	 */
	function remove_local_media_files( $local_files ) {
		if ( ! is_array( $local_files ) ) {
			$local_files = @unserialize( $local_files );
		}

		$errors = array();

		if ( empty( $local_files ) ) {
			return $errors;
		}

		$upload_dir = $this->uploads_dir();

		foreach ( $local_files as $local_file ) {
			if ( false === $this->filesystem->unlink( $upload_dir . $local_file ) && $this->filesystem->file_exists( $upload_dir . $local_file ) ) {
				$errors[] = sprintf( __( 'Could not delete "%s"', 'wp-migrate-db-pro-media-files' ), $upload_dir . $local_file ) . ' (#122mf)';
			}
		}

		return $errors;
	}

	/**
	 * Compare a set of files with those on the local filesystem
	 *
	 * @param mixed  $files Files to compare
	 * @param string $intent
	 *
	 * @return array $files_to_remove Files that do not exist locally
	 */
	function get_files_not_on_local( $files, $intent ) {
		if ( ! is_array( $files ) ) {
			$files = @unserialize( $files );
		}
		$upload_dir = $this->uploads_dir();

		$files_to_remove = array();

		foreach ( $files as $file ) {
			if ( ! $this->filesystem->file_exists( $upload_dir . apply_filters( 'wpmdbmf_file_not_on_local', $file, $intent, $this ) ) ) {
				$files_to_remove[] = $file;
			}
		}

		return $files_to_remove;
	}

	/**
	 * Check if a remote image size file exists locally
	 *
	 * @param array $remote_size      Remote attachment size
	 * @param array $local_attachment Local attachment
	 *
	 * @return bool
	 */
	function local_image_size_file_exists( $remote_size, $local_attachment ) {
		if ( empty( $local_attachment['sizes'] ) ) {
			return false;
		}

		foreach ( $local_attachment['sizes'] as $size ) {
			if ( $size['file'] == $remote_size['file'] ) {
				if ( isset( $size['file_size'] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Store the physical file size in an attachment
	 *
	 * Used for attachments files and resized images files
	 *
	 * @param array $attachment Attachment
	 *
	 * @return array Updated attachment
	 */
	function apply_file_size( $attachment ) {
		if ( ! isset( $attachment['file'] ) ) {
			return $attachment;
		}

		// get size of image on disk
		$size = $this->get_file_size( $attachment['file'], ( isset( $attachment['dir_prefix'] ) ? $attachment['dir_prefix'] : '' ) );
		if ( false !== $size ) {
			$attachment['file_size'] = $size;
		}

		return $attachment;
	}

	/**
	 * Calculate size on disk of a file
	 *
	 * @param string $file       File path
	 * @param string $dir_prefix Multisite blog specific directory
	 *
	 * @return int|bool File size if exists, false otherwise
	 */
	function get_file_size( $file, $dir_prefix = '' ) {
		$upload_dir = untrailingslashit( $this->uploads_dir() );
		if ( ! $this->filesystem->file_exists( $upload_dir ) ) {
			return false;
		}

		if ( ! is_multisite() && $dir_prefix ) {
			$file = str_replace( $dir_prefix, '', $file );
		}

		$file = $upload_dir . DIRECTORY_SEPARATOR . $file;
		if ( ! $this->filesystem->file_exists( $file ) ) {
			return false;
		}

		return $this->filesystem->filesize( $file );
	}

	/**
	 * Get the directory prefix for an attachment in a multisite blog
	 *
	 * @param array $attachment Attachment
	 *
	 * @return string Directory prefix
	 */
	function get_dir_prefix( $attachment ) {
		$dir_prefix = ''; // nothing for default blogs

		if ( isset( $attachment['blog_id'] ) && ! $this->is_current_blog( $attachment['blog_id'] ) ) {
			if ( defined( 'UPLOADBLOGSDIR' ) ) {
				$dir_prefix = sprintf( '%s/files/', $attachment['blog_id'] );
			} else {
				$dir_prefix = sprintf( 'sites/%s/', $attachment['blog_id'] );
			}
		}

		return $dir_prefix;
	}

	/**
	 * Return the base uploads directory
	 *
	 * @return string Path to uploads directory
	 */
	function uploads_dir() {
		static $upload_dir;

		if ( ! is_null( $upload_dir ) ) {
			return $upload_dir;
		}

		if ( defined( 'UPLOADBLOGSDIR' ) ) {
			$upload_dir = trailingslashit( ABSPATH ) . UPLOADBLOGSDIR;
		} else {
			$upload_dir = wp_upload_dir();
			$upload_dir = $upload_dir['basedir'];
		}

		if ( is_multisite() ) {
			// Remove multisite postfix
			$upload_dir = untrailingslashit( $upload_dir );
			$upload_dir = preg_replace( '/\/sites\/(\d)+$/', '', $upload_dir );
		}

		$upload_dir = trailingslashit( $upload_dir );

		return $upload_dir;
	}

	/**
	 * Get an array of the blogs in the site to be processed by the addon
	 *
	 * @return array Blogs to be processed
	 */
	function get_blogs() {
		global $wpdb;

		$blogs = array();

		if ( is_multisite() ) {
			$blog_ids = $this->get_blog_ids();
			foreach ( $blog_ids as $blog_id ) {
				$blogs[ $blog_id ] = array(
					'prefix'    => $wpdb->get_blog_prefix( $blog_id ),
					'last_post' => 0,
					'processed' => 0,
				);
			}
		} else {
			$blogs[1] = array(
				'prefix'    => $wpdb->base_prefix,
				'last_post' => 0, // record last post id process to be used as an offset in the next batch for the blog
				'processed' => 0, // flag to record if we have processed all attachments for the blog
			);
		}

		return $blogs;
	}

	/**
	 * Get all the IDs of the blogs for the multisite
	 *
	 * @return array Blog ID's
	 */
	function get_blog_ids() {
		$blog_ids = array();

		if ( ! is_multisite() ) {
			return $blog_ids;
		}

		$args  = array(
			'spam'     => 0,
			'deleted'  => 0,
			'archived' => 0,
			'number' => false
		);

		if ( version_compare( $GLOBALS['wp_version'], '4.6', '>=' ) ) {
			$blogs = get_sites( $args );
		} else {
			$blogs = wp_get_sites( $args );
		}

		foreach ( $blogs as $blog ) {
			$blog = (array) $blog;
			if ( apply_filters( 'wpmdbmf_include_subsite', true, $blog['blog_id'], $this ) ) {
				$blog_ids[] = $blog['blog_id'];
			}
		}

		return $blog_ids;
	}

	/**
	 * Compares the blog ID with the current site specified in wp-config.php
	 *
	 * @param int $blog_id Blog ID
	 *
	 * @return bool
	 */
	function is_current_blog( $blog_id ) {
		$default = defined( 'BLOG_ID_CURRENT_SITE' ) ? BLOG_ID_CURRENT_SITE : 1;

		if ( $default === (int) $blog_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Handler for "wpmdbmf_include_subsite" filter to disallow subsite's media to be migrated if not selected.
	 *
	 * @param bool $value
	 * @param int  $blog_id
	 *
	 * @return bool
	 */
	public function include_subsite( $value, $blog_id ) {
		$this->set_post_data();

		if ( is_null( $this->form_data ) && ! empty( $this->state_data['form_data'] ) ) {
			$this->form_data = $this->parse_migration_form_data( $this->state_data['form_data'] );
		}

		if ( false === $value || empty( $this->form_data['mf_select_subsites'] ) || empty( $this->form_data['mf_selected_subsites'] ) ) {
			return $value;
		}

		if ( ! in_array( $blog_id, $this->form_data['mf_selected_subsites'] ) ) {
			$value = false;
		}

		return $value;
	}

	/**
	 * Returns validated and sanitized form data.
	 *
	 * @param array|string $data
	 *
	 * @return array|string
	 */
	function parse_migration_form_data( $data ) {
		$form_data = parent::parse_migration_form_data( $data );

		$form_data = array_intersect_key( $form_data, array_flip( $this->accepted_fields ) );

		return $form_data;
	}
}
