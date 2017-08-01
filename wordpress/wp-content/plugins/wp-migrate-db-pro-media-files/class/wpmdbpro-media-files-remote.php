<?php

/**
 * Class WPMDBPro_Media_Files_Remote
 *
 * Handles all functionality and AJAX requests that are only required on the "remote" site.
 */
class WPMDBPro_Media_Files_Remote extends WPMDBPro_Media_Files_Base {

	public function __construct( $plugin_file_path ) {
		parent::__construct( $plugin_file_path );

		// Remote AJAX handlers
		add_action( 'wp_ajax_nopriv_wpmdbmf_get_remote_media_info', array( $this, 'respond_to_get_remote_media_info' ) );
		add_action( 'wp_ajax_nopriv_wpmdbmf_get_remote_attachment_batch', array( $this, 'respond_to_get_remote_attachment_batch' ) );
		add_action( 'wp_ajax_nopriv_wpmdbmf_compare_remote_attachments', array( $this, 'respond_to_compare_remote_attachments' ) );
		add_action( 'wp_ajax_nopriv_wpmdbmf_push_request', array( $this, 'respond_to_push_request' ) );
		add_action( 'wp_ajax_nopriv_wpmdbmf_get_local_media_files_batch', array( $this, 'respond_to_get_local_media_files_batch' ) );
		add_action( 'wp_ajax_nopriv_wpmdbmf_compare_local_media_files', array( $this, 'respond_to_compare_local_media_files' ) );
		add_action( 'wp_ajax_nopriv_wpmdbmf_remove_local_media_files', array( $this, 'respond_to_remove_local_media_files' ) );
	}

	/**
	 * Return information about remote site for use in media migration
	 *
	 * @return bool|null
	 */
	public function respond_to_get_remote_media_info() {
		add_filter( 'wpmdb_before_response', array( $this, 'scramble' ) );

		$key_rules = array(
			'action'          => 'key',
			'remote_state_id' => 'key',
			'intent'          => 'key',
			'sig'             => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );

		$filtered_post = $this->filter_post_elements( $this->state_data, array(
			'action',
			'remote_state_id',
			'intent',
		) );

		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' => 1,
				'body'        => $this->invalid_content_verification_error . ' (#100mf)',
			);
			$this->log_error( $return['body'], $filtered_post );
			$result = $this->end_ajax( serialize( $return ) );

			return $result;
		}

		if ( defined( 'UPLOADBLOGSDIR' ) ) {
			$upload_url = home_url( UPLOADBLOGSDIR );
		} else {
			$upload_dir = wp_upload_dir();
			$upload_url = $upload_dir['baseurl'];

			if ( is_multisite() ) {
				// Remove multisite postfix
				$upload_url = preg_replace( '/\/sites\/(\d)+$/', '', $upload_url );
			}
		}

		$return['remote_total_attachments'] = $this->get_local_attachments_count();
		$return['remote_uploads_url']       = $upload_url;
		$return['blogs']                    = serialize( $this->get_blogs() );
		$return['remote_max_upload_size']   = $this->get_max_upload_size();

		$result = $this->end_ajax( serialize( $return ) );

		return $result;
	}

	/**
	 * Return a batch of attachments from the remote site
	 *
	 * @return bool|null
	 */
	public function respond_to_get_remote_attachment_batch() {
		add_filter( 'wpmdb_before_response', array( $this, 'scramble' ) );

		$key_rules = array(
			'action'                 => 'key',
			'remote_state_id'        => 'key',
			'intent'                 => 'key',
			'blogs'                  => 'serialized',
			'attachment_batch_limit' => 'positive_int',
			'sig'                    => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );

		$filtered_post = $this->filter_post_elements( $this->state_data, array(
			'action',
			'remote_state_id',
			'intent',
			'blogs',
			'attachment_batch_limit',
		) );

		$filtered_post['blogs'] = stripslashes( $filtered_post['blogs'] );

		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' => 1,
				'body'        => $this->invalid_content_verification_error . ' (#116mf)',
			);
			$this->log_error( $return['body'], $filtered_post );
			$result = $this->end_ajax( serialize( $return ) );

			return $result;
		}
		$batch                        = $this->get_local_attachments_batch( $filtered_post['blogs'], $filtered_post['attachment_batch_limit'] );
		$return['remote_attachments'] = addslashes( serialize( $batch['attachments'] ) );
		$return['blogs']              = addslashes( serialize( $batch['blogs'] ) );

		$result = $this->end_ajax( serialize( $return ) );

		return $result;
	}

	/**
	 * Compare posted local files with those on the remote server
	 *
	 * @return bool|null
	 */
	public function respond_to_compare_remote_attachments() {
		add_filter( 'wpmdb_before_response', array( $this, 'scramble' ) );

		$key_rules = array(
			'action'             => 'key',
			'remote_state_id'    => 'key',
			'intent'             => 'key',
			'blogs'              => 'serialized',
			'determine_progress' => 'positive_int',
			'remote_attachments' => 'serialized',
			'sig'                => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );

		$filtered_post = $this->filter_post_elements( $this->state_data, array(
			'action',
			'remote_state_id',
			'intent',
			'blogs',
			'determine_progress',
			'remote_attachments',
		) );

		$filtered_post['blogs']              = stripslashes( $filtered_post['blogs'] );
		$filtered_post['remote_attachments'] = stripslashes( $filtered_post['remote_attachments'] );

		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' => 1,
				'body'        => $this->invalid_content_verification_error . ' (#118mf)',
			);
			$this->log_error( $return['body'], $filtered_post );
			$result = $this->end_ajax( serialize( $return ) );

			return $result;
		}

		// compare_remote_attachments will unslash these values again
		$filtered_post['blogs']              = addslashes( $filtered_post['blogs'] );
		$filtered_post['remote_attachments'] = addslashes( $filtered_post['remote_attachments'] );

		$return = $this->compare_remote_attachments( $filtered_post['blogs'], $filtered_post['remote_attachments'], $filtered_post['determine_progress'] );
		$result = $this->end_ajax( serialize( $return ) );

		return $result;
	}

	/**
	 * Move uploaded local site files from tmp to uploads directory
	 *
	 * @return bool|null
	 */
	public function respond_to_push_request() {
		add_filter( 'wpmdb_before_response', array( $this, 'scramble' ) );

		$key_rules = array(
			'action'          => 'key',
			'remote_state_id' => 'key',
			'files'           => 'serialized',
			'sig'             => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );

		$filtered_post = $this->filter_post_elements( $this->state_data, array(
			'action',
			'remote_state_id',
			'files',
		) );

		$filtered_post['files'] = stripslashes( $filtered_post['files'] );

		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' => 1,
				'body'        => $this->invalid_content_verification_error . ' (#111mf)',
			);
			$this->log_error( $return['body'], $filtered_post );
			$result = $this->end_ajax( serialize( $return ) );

			return $result;
		}

		if ( ! isset( $_FILES['media'] ) ) {
			$return = array(
				'wpmdb_error' => 1,
				'body'        => __( '$_FILES is empty, the upload appears to have failed', 'wp-migrate-db-pro-media-files' ) . ' (#106mf)',
			);
			$this->log_error( $return['body'] );
			$result = $this->end_ajax( serialize( $return ) );

			return $result;
		}

		$upload_dir = $this->uploads_dir();

		$files      = $this->diverse_array( $_FILES['media'] );
		$file_paths = unserialize( $filtered_post['files'] );
		$i          = 0;
		$errors     = array();
		$transfers  = array();
		foreach ( $files as &$file ) {
			$destination      = $upload_dir . apply_filters( 'wpmdbmf_destination_file_path', $file_paths[ $i ], 'push', $this );
			$folder           = dirname( $destination );
			$current_transfer = array( 'file' => $file_paths[ $i ], 'error' => false );

			if ( false === $this->filesystem->file_exists( $folder ) && false === $this->filesystem->mkdir( $folder ) ) {
				$error_string              = sprintf( __( 'Error attempting to create required directory: %s', 'wp-migrate-db-pro-media-files' ), $folder ) . ' (#108mf)';
				$errors[]                  = $error_string;
				$current_transfer['error'] = $error_string;
				++$i;
				$transfers[] = $current_transfer;
				continue;
			}

			if ( false === $this->filesystem->move_uploaded_file( $file['tmp_name'], $destination ) ) {
				$error_string              = sprintf( __( 'A problem occurred when attempting to move the temp file "%1$s" to "%2$s"', 'wp-migrate-db-pro-media-files' ), $file['tmp_name'], $destination ) . ' (#107mf)';
				$errors[]                  = $error_string;
				$current_transfer['error'] = $error_string;
			}
			$transfers[] = $current_transfer;
			++$i;
		}

		$return = array( 'success' => 1, 'transfers' => $transfers );

		if ( ! empty( $errors ) ) {
			$return['wpmdb_non_fatal_error'] = 1;

			$return['cli_body'] = $errors;
			$return['body']     = implode( '<br />', $errors ) . '<br />';
			$error_msg          = __( 'Failed attempting to respond to push request', 'wp-migrate-db-pro-media-files' ) . ' (#113mf)';
			$this->log_error( $error_msg, $errors );
		}

		$result = $this->end_ajax( serialize( $return ) );

		return $result;
	}

	/**
	 * AJAX callback for returning a batch of local media files
	 *
	 * @return bool|null
	 */
	public function respond_to_get_local_media_files_batch() {
		add_filter( 'wpmdb_before_response', array( $this, 'scramble' ) );

		$key_rules = array(
			'action'          => 'key',
			'remote_state_id' => 'key',
			'compare'         => 'positive_int',
			'offset'          => 'string',
			'sig'             => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );

		$filtered_post = $this->filter_post_elements( $this->state_data, array(
			'action',
			'remote_state_id',
			'compare',
			'offset',
		) );

		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' => 1,
				'body'        => $this->invalid_content_verification_error . ' (#109mf)',
			);
			$this->log_error( $return['body'], $filtered_post );
			$result = $this->end_ajax( serialize( $return ) );

			return $result;
		}

		$offset = isset( $filtered_post['offset'] ) ? json_decode( $filtered_post['offset'] ) : '0';

		$local_media_files            = array();
		$local_media_attachment_files = array();
		if ( 1 === (int) $filtered_post['compare'] ) {
			$local_media_attachment_files = $this->get_local_media_attachment_files_batch( $offset );
		} else {
			$local_media_files = $this->get_local_media_files_batch( array_pop( $offset ) );
		}

		$return = array(
			'success'                      => 1,
			'local_media_files'            => $local_media_files,
			'local_media_attachment_files' => $local_media_attachment_files,
		);

		$result = $this->end_ajax( serialize( $return ) );

		return $result;
	}

	/**
	 * AJAX callback to compare a posted batch of files with those on local site
	 *
	 * @return bool|null
	 */
	public function respond_to_compare_local_media_files() {
		add_filter( 'wpmdb_before_response', array( $this, 'scramble' ) );

		$key_rules = array(
			'action'          => 'key',
			'remote_state_id' => 'key',
			'files'           => 'serialized',
			'sig'             => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );

		$filtered_post = $this->filter_post_elements( $this->state_data, array(
			'action',
			'remote_state_id',
			'files',
		) );

		$filtered_post['files'] = stripslashes( $filtered_post['files'] );

		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' => 1,
				'body'        => $this->invalid_content_verification_error . ' (#117mf)',
			);
			$this->log_error( $return['body'], $filtered_post );
			$result = $this->end_ajax( serialize( $return ) );

			return $result;
		}

		// compare files to those on the local filesystem
		$files_to_remove = $this->get_files_not_on_local( $filtered_post['files'], 'pull' );

		$return = array(
			'success'         => 1,
			'files_to_remove' => $files_to_remove,
		);

		$result = $this->end_ajax( serialize( $return ) );

		return $result;
	}

	/**
	 * AJAX callback to remove files for the local filesystem
	 *
	 * @return bool|null
	 */
	public function respond_to_remove_local_media_files() {
		add_filter( 'wpmdb_before_response', array( $this, 'scramble' ) );

		$key_rules = array(
			'action'          => 'key',
			'remote_state_id' => 'key',
			'files_to_remove' => 'serialized',
			'sig'             => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );

		$filtered_post = $this->filter_post_elements( $this->state_data, array(
			'action',
			'remote_state_id',
			'files_to_remove',
		) );

		$filtered_post['files_to_remove'] = stripslashes( $filtered_post['files_to_remove'] );

		if ( ! $this->verify_signature( $filtered_post, $this->settings['key'] ) ) {
			$return = array(
				'wpmdb_error' => 1,
				'body'        => $this->invalid_content_verification_error . ' (#119mf)',
			);
			$this->log_error( $return['body'], $filtered_post );
			$result = $this->end_ajax( serialize( $return ) );

			return $result;
		}

		$errors = $this->remove_local_media_files( $filtered_post['files_to_remove'] );

		$return['success'] = 1;

		if ( ! empty( $errors ) ) {
			$return['wpmdb_non_fatal_error'] = 1;

			$return['cli_body'] = $errors;
			$return['body']     = implode( '<br />', $errors ) . '<br />';
			$error_msg          = __( 'There were errors when removing local media files from the remote site', 'wp-migrate-db-pro-media-files' ) . ' (#121mf)';
			$this->log_error( $error_msg, $errors );
		}

		$result = $this->end_ajax( serialize( $return ) );

		return $result;
	}

}
