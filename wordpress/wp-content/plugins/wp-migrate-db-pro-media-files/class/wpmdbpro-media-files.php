<?php

/**
 * Class WPMDBPro_Media_Files
 *
 * Handles the addon setup and settings
 */
class WPMDBPro_Media_Files extends WPMDBPro_Addon {

	/**
	 * An array strings used for translations
	 *
	 * @var array $media_strings
	 */
	protected $media_strings;

	/**
	 * An instance of WPMDBPro_Media_Files_Local
	 *
	 * @var object $media_files_local
	 */
	public $media_files_local;

	/**
	 * An instance of WPMDBPro_Media_Files_Remote
	 *
	 * @var object $media_files_remote
	 */
	public $media_files_remote;

	function __construct( $plugin_file_path ) {
		parent::__construct( $plugin_file_path );

		$this->plugin_slug    = 'wp-migrate-db-pro-media-files';
		$this->plugin_version = $GLOBALS['wpmdb_meta']['wp-migrate-db-pro-media-files']['version'];

		if ( ! $this->meets_version_requirements( '1.7.1' ) ) {
			return;
		}

		add_action( 'wpmdb_after_advanced_options', array( $this, 'migration_form_controls' ) );
		add_action( 'wpmdb_load_assets', array( $this, 'load_assets' ) );
		add_action( 'wpmdb_diagnostic_info', array( $this, 'diagnostic_info' ) );
		add_action( 'wpmdbmf_after_migration_options', array( $this, 'after_migration_options_template' ) );
		add_filter( 'wpmdb_establish_remote_connection_data', array( $this, 'establish_remote_connection_data' ) );
		add_filter( 'wpmdb_nonces', array( $this, 'add_nonces' ) );
		add_filter( 'wpmdb_data', array( $this, 'js_variables' ) );

		$this->media_files_local  = new WPMDBPro_Media_Files_Local( $plugin_file_path );
		$this->media_files_remote = new WPMDBPro_Media_Files_Remote( $plugin_file_path );
	}

	/**
	 * Adds the media settings to the migration setting page in core
	 */
	function migration_form_controls() {
		$this->template( 'migrate' );
	}

	/**
	 * Get translated strings for javascript and other functions
	 *
	 * @return array Array of translations
	 */
	function get_strings() {
		$strings = array(
			'removing_all_files_pull'      => __( 'Removing all local files before download of remote media', 'wp-migrate-db-pro-media-files' ),
			'removing_all_files_push'      => __( 'Removing all remote files before upload of local media', 'wp-migrate-db-pro-media-files' ),
			'removing_files_pull'          => __( 'Removing local files that are not found on the remote site', 'wp-migrate-db-pro-media-files' ),
			'removing_files_push'          => __( 'Removing remote files that are not found on the local site', 'wp-migrate-db-pro-media-files' ),
			'determining'                  => __( 'Determining media to migrate', 'wp-migrate-db-pro-media-files' ),
			'determining_progress'         => __( 'Determining media to migrate - %1$d of %2$d attachments (%3$d%%)', 'wp-migrate-db-pro-media-files' ),
			'error_determining'            => __( 'Error while attempting to determine which attachments to migrate.', 'wp-migrate-db-pro-media-files' ),
			'migration_failed'             => __( 'Migration failed', 'wp-migrate-db-pro-media-files' ),
			'problem_migrating_media'      => __( 'A problem occurred when migrating the media files.', 'wp-migrate-db-pro-media-files' ),
			'media_attachments'            => __( 'Media Attachments', 'wp-migrate-db-pro-media-files' ),
			'media_files'                  => __( 'Files', 'wp-migrate-db-pro-media-files' ),
			'migrate_media_files_pull'     => __( 'Downloading files', 'wp-migrate-db-pro-media-files' ),
			'migrate_media_files_push'     => __( 'Uploading files', 'wp-migrate-db-pro-media-files' ),
			'migrate_media_files_cli_pull' => __( 'Downloading %d of %d files', 'wp-migrate-db-pro-media-files' ),
			'migrate_media_files_cli_push' => __( 'Uploading %d of %d files', 'wp-migrate-db-pro-media-files' ),
			'files_uploaded'               => __( 'Files Uploaded', 'wp-migrate-db-pro-media-files' ),
			'files_downloaded'             => __( 'Files Downloaded', 'wp-migrate-db-pro-media-files' ),
			'file_too_large'               => __( 'The following file is too large to migrate:', 'wp-migrate-db-pro-media-files' ),
			'please_select_a_subsite'      => __( 'Please select at least one subsite to transfer media files for.', 'wp-migrate-db-pro-media-files' ),
		);

		if ( is_null( $this->media_strings ) ) {
			$this->media_strings = $strings;
		}

		return $this->media_strings;
	}

	/**
	 * Retrieve a specific translated string
	 *
	 * @param string $key Array key
	 *
	 * @return string Translation
	 */
	function get_string( $key ) {
		$strings = $this->get_strings();

		return ( isset( $strings[ $key ] ) ) ? $strings[ $key ] : '';
	}

	/**
	 * Load media related assets in core plugin
	 */
	function load_assets() {
		$plugins_url = trailingslashit( plugins_url( $this->plugin_folder_name ) );
		$version     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : $this->plugin_version;
		$ver_string  = '-' . str_replace( '.', '', $this->plugin_version );
		$min         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$src = $plugins_url . 'asset/dist/css/styles.css';
		wp_enqueue_style( 'wp-migrate-db-pro-media-files-styles', $src, array( 'wp-migrate-db-pro-styles' ), $version );

		$src = $plugins_url . "asset/dist/js/script{$ver_string}{$min}.js";
		wp_enqueue_script( 'wp-migrate-db-pro-media-files-script', $src, array(
			'jquery',
			'wp-migrate-db-pro-common',
			'wp-migrate-db-pro-hook',
			'wp-migrate-db-pro-script',
		), $version, true );

		wp_localize_script( 'wp-migrate-db-pro-media-files-script', 'wpmdbmf_strings', $this->get_strings() );
	}

	/**
	 * Check the remote site has the media addon setup
	 *
	 * @param array $data Connection data
	 *
	 * @return array Updated connection data
	 */
	function establish_remote_connection_data( $data ) {
		$data['media_files_available'] = '1';
		$data['media_files_version']   = $this->plugin_version;
		if ( function_exists( 'ini_get' ) ) {
			$max_file_uploads = ini_get( 'max_file_uploads' );
		}
		$max_file_uploads                     = ( empty( $max_file_uploads ) ) ? 20 : $max_file_uploads;
		$data['media_files_max_file_uploads'] = apply_filters( 'wpmdbmf_max_file_uploads', $max_file_uploads );

		return $data;
	}

	/**
	 * Add media related javascript variables to the page
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	function js_variables( $data ) {
		$data['media_files_version'] = $this->plugin_version;

		return $data;
	}

	/**
	 * Adds extra information to the core plugin's diagnostic info
	 */
	function diagnostic_info() {
		// store the count of local attachments in a transient
		// so not to impact performance with sites with large media libraries
		if ( false === ( $attachment_count = get_transient( 'wpmdb_local_attachment_count' ) ) ) {
			$attachment_count = $this->media_files_local->get_local_attachments_count();
			set_transient( 'wpmdb_local_attachment_count', $attachment_count, 2 * HOUR_IN_SECONDS );
		}

		echo 'Media Files: ';
		echo number_format( $attachment_count );
		echo "\r\n";

		echo 'Number of Image Sizes: ';
		$sizes = count( get_intermediate_image_sizes() );
		echo number_format( $sizes );
		echo "\r\n";
		echo "\r\n";
	}

	/**
	 * Media addon nonces for core javascript variables
	 *
	 * @param array $nonces Array of nonces
	 *
	 * @return array Updated array of nonces
	 */
	function add_nonces( $nonces ) {
		$nonces['migrate_media']                        = wp_create_nonce( 'migrate-media' );
		$nonces['remove_files_recursive']               = wp_create_nonce( 'remove-files-recursive' );
		$nonces['prepare_determine_media']              = wp_create_nonce( 'prepare-determine-media' );
		$nonces['determine_media_to_migrate_recursive'] = wp_create_nonce( 'determine-media-to-migrate-recursive' );

		return $nonces;
	}

	/**
	 * Handler for "wpmdbmf_after_migration_options" action to append subsite select UI.
	 */
	public function after_migration_options_template() {
		if ( is_multisite() ) {
			$this->template( 'select-subsites' );
		}
	}
}
