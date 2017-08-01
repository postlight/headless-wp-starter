<?php

class WPMDBPro_Media_Files_CLI extends WPMDBPro_Media_Files {

	function __construct( $plugin_file_path ) {
		parent::__construct( $plugin_file_path );

		// compatibility with CLI migrations
		add_filter( 'wpmdb_pro_cli_finalize_migration', array( $this, 'cli_migration' ), 10, 4 );
	}

	/**
	 * Run the media migration from the CLI
	 *
	 * @param bool  $outcome
	 * @param array $profile
	 * @param array $verify_connection_response
	 * @param array $initiate_migration_response
	 *
	 * @return bool
	 */
	function cli_migration( $outcome, $profile, $verify_connection_response, $initiate_migration_response ) {
		global $wpmdbpro, $wpmdbpro_cli;
		if ( true !== $outcome ) {
			return $outcome;
		}
		if ( empty( $profile['media_files'] ) ) {
			return $outcome;
		}

		if ( ! isset( $verify_connection_response['media_files_max_file_uploads'] ) ) {
			return $wpmdbpro_cli->cli_error( __( 'WP Migrate DB Pro Media Files does not seem to be installed/active on the remote website.', 'wp-migrate-db-pro-media-files' ) );
		}

		WP_CLI::log( __( 'Initiating media migration...', 'wp-migrate-db-pro-media-files' ) );

		$this->set_time_limit();
		$wpmdbpro->set_cli_migration();
		$this->set_cli_migration();
		$this->media_files_local->set_cli_migration();

		$intent                      = $profile['action'];
		$_POST['migration_state_id'] = $initiate_migration_response['migration_state_id'];

		$media_type         = ( isset( $profile['media_migration_option'] ) ) ? $profile['media_migration_option'] : 'compare';
		$copy_entire_media  = ( 'entire' === $media_type ) ? 1 : 0;
		$remove_local_media = ( 'compare' === $media_type && isset( $profile['remove_local_media'] ) ) ? $profile['remove_local_media'] : 0;
		if ( 'compare-remove' == $media_type ) {
			$media_type         = 'compare';
			$remove_local_media = 1;
		}

		// seems like this value needs to be different depending on pull/push?
		$bottleneck = $wpmdbpro->get_bottleneck();

		// if skipping comparison delete all files before migration
		if ( 'entire' === $media_type ) {
			do_action( 'wpmdb_cli_before_remove_files_recursive', $profile, $verify_connection_response, $initiate_migration_response );
			WP_CLI::log( $this->get_string( 'removing_all_files_' . $intent ) . '...' );

			$compare      = 0;
			$offset       = 0;
			$remove_files = 1;
			while ( 1 == $remove_files ) {
				$_POST['compare'] = $compare;
				$_POST['offset']  = json_encode( $offset );

				$response = $this->media_files_local->ajax_remove_files_recursive();
				if ( is_wp_error( $remove_files_recursive_response = $wpmdbpro_cli->verify_cli_response( $response, 'ajax_remove_files_recursive()' ) ) ) {
					return $remove_files_recursive_response;
				}

				$remove_files = $remove_files_recursive_response['remove_files'];
				$compare      = $remove_files_recursive_response['compare'];
				$offset       = $remove_files_recursive_response['offset'];
			} // END recursive removal of files
		}

		// start the recursive determine
		do_action( 'wpmdb_cli_before_determine_media_to_migrate', $profile, $verify_connection_response, $initiate_migration_response );

		$response = $this->media_files_local->ajax_prepare_determine_media();
		if ( is_wp_error( $prepare_media_to_migrate_response = $wpmdbpro_cli->verify_cli_response( $response, 'ajax_prepare_determine_media()' ) ) ) {
			return $prepare_media_to_migrate_response;
		}

		$attachment_batch_limit = $prepare_media_to_migrate_response['attachment_batch_limit'];
		$remote_uploads_url     = $prepare_media_to_migrate_response['remote_uploads_url'];
		$attachment_count       = $prepare_media_to_migrate_response['attachment_count'];
		$blogs                  = $prepare_media_to_migrate_response['blogs'];
		$determine_progress     = 0;

		// determine the media to migrate in batches
		while ( $determine_progress < $attachment_count ) {

			$_POST['attachment_batch_limit'] = $attachment_batch_limit;
			$_POST['remote_uploads_url']     = $remote_uploads_url;
			$_POST['attachment_count']       = $attachment_count;
			$_POST['blogs']                  = $blogs;
			$_POST['determine_progress']     = $determine_progress;
			$_POST['copy_entire_media']      = $copy_entire_media;
			$_POST['remove_local_media']     = $remove_local_media;

			$response = $this->media_files_local->ajax_determine_media_to_migrate_recursive();
			if ( is_wp_error( $determine_media_to_migrate_recursive_response = $wpmdbpro_cli->verify_cli_response( $response, 'ajax_determine_media_to_migrate_recursive_response()' ) ) ) {
				return $determine_media_to_migrate_recursive_response;
			}

			$blogs              = $determine_media_to_migrate_recursive_response['blogs'];
			$determine_progress = $determine_media_to_migrate_recursive_response['determine_progress'];
			$total_size         = $determine_media_to_migrate_recursive_response['total_size'];
			$files_to_migrate   = $determine_media_to_migrate_recursive_response['files_to_migrate'];

			$percent = ( $determine_progress / $attachment_count ) * 100;
			WP_CLI::log( sprintf( $this->get_string( 'determining_progress' ), $determine_progress, $attachment_count, round( $percent ) ) );

			$total_files = count( $files_to_migrate );
			if ( $total_files > 0 ) {
				$migrate_bar = new WPMDBPro_Media_Files_CLI_Bar( sprintf( $this->get_string( 'migrate_media_files_cli_' . $intent ), 0, $total_files ), 0 );
				$migrate_bar->setTotal( $total_size );

				$current_file_index = 0;

				// start the recursive migration of the files we have just determined
				while ( ! empty( $files_to_migrate ) ) {

					$file_chunk_to_migrate      = array();
					$file_chunk_size            = 0;
					$number_of_files_to_migrate = 0;
					foreach ( $files_to_migrate as $file_to_migrate => $file_size ) {
						if ( empty( $file_chunk_to_migrate ) ) {
							$file_chunk_to_migrate[] = $file_to_migrate;
							$file_chunk_size += $file_size;
							unset( $files_to_migrate[ $file_to_migrate ] );
							++$number_of_files_to_migrate;
						} else {
							if ( ( $file_chunk_size + $file_size ) > $bottleneck || $number_of_files_to_migrate >= $verify_connection_response['media_files_max_file_uploads'] ) {
								break;
							} else {
								$file_chunk_to_migrate[] = $file_to_migrate;
								$file_chunk_size += $file_size;
								unset( $files_to_migrate[ $file_to_migrate ] );
								++$number_of_files_to_migrate;
							}
						}
					}

					$current_file_index += $number_of_files_to_migrate;
					$migrate_bar->setMessage( sprintf( $this->get_string( 'migrate_media_files_cli_' . $intent ), $current_file_index, $total_files ) );

					$_POST['file_chunk'] = $file_chunk_to_migrate;

					do_action( 'wpmdb_media_files_cli_before_migrate_media' );

					$response = $this->media_files_local->ajax_migrate_media();
					if ( is_wp_error( $migrate_media_response = $wpmdbpro_cli->verify_cli_response( $response, 'ajax_migrate_media()' ) ) ) {
						return $migrate_media_response;
					}

					$migrate_bar->tick( $file_chunk_size );
				} // END recursive media migration

				// force migrate bar to show completion
				$migrate_bar->setMessage( sprintf( $this->get_string( 'migrate_media_files_cli_' . $intent ), $total_files, $total_files ) );
				$migrate_bar->finish();
			}
		} // END recursive media determine

		// if removing local media not found on remote after comparison
		if ( 1 == $remove_local_media ) {
			// start recursive batch delete of local files not found on remote
			do_action( 'wpmdb_cli_before_remove_files_not_found_recursive', $profile, $verify_connection_response, $initiate_migration_response );
			WP_CLI::log( $this->get_string( 'removing_files_' . $intent ) . '...' );
			$compare      = 1;
			$offset       = '';
			$remove_files = 1;
			while ( 1 == $remove_files ) {
				$_POST['compare'] = $compare;
				$_POST['offset']  = json_encode( $offset );

				$response = $this->media_files_local->ajax_remove_files_recursive();
				if ( is_wp_error( $remove_files_recursive_response = $wpmdbpro_cli->verify_cli_response( $response, 'ajax_remove_files_recursive()' ) ) ) {
					return $remove_files_recursive_response;
				}

				$remove_files = $remove_files_recursive_response['remove_files'];
				$compare      = $remove_files_recursive_response['compare'];
				$offset       = $remove_files_recursive_response['offset'];
			} // END recursive removal of files
		}

		return true;
	}
}
