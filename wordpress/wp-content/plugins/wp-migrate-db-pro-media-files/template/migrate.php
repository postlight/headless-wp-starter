<?php global $loaded_profile; ?>
<div class="option-section media-files-options">

	<label class="media-files checkbox-label" for="media-files">
		<input type="checkbox" name="media_files" value="1" data-available="1" id="media-files"<?php echo ( isset( $loaded_profile['media_files'] ) ? ' checked="checked"' : '' ); ?> />
		<?php _e( 'Media Files', 'wp-migrate-db-pro-media-files' ); ?>
	</label>

	<div class="indent-wrap expandable-content">
		<?php
		do_action( 'wpmdbmf_before_migration_options' );

		$media_migration_option = isset( $loaded_profile['media_migration_option'] ) ? $loaded_profile['media_migration_option'] : 'compare';
		$remove_local_media = isset( $loaded_profile['remove_local_media'] ) ? $loaded_profile['remove_local_media'] : false;
		if ( $remove_local_media ) {
			$media_migration_option = 'compare-remove';
		}
		?>
		<ul>
			<li id="compare-media-list-item">
				<label for="compare-media" class="compare-media">
					<input type="radio" name="media_migration_option" value="compare" id="compare-media"<?php checked( $media_migration_option, 'compare', true ); ?> />
					<span class="action-text push">
						<?php _e( 'Compare then upload', 'wp-migrate-db-pro-media-files' ); ?>
						<a href="#" class="general-helper replace-guid-helper js-action-link"></a>
						<div class="mf-compare-message helper-message">
							<?php _e( 'Compare remote and local media files determining what files are missing or have been updated and need to be uploaded. Great for syncing two Media Libraries that only differ a little. For more details, see the <a href="https://deliciousbrains.com/wp-migrate-db-pro/doc/media-files-addon" target="_blank">Media Files doc</a>.', 'wp-migrate-db' ); ?>
						</div>
					</span>
					<span class="action-text pull">
						<?php _e( 'Compare then download', 'wp-migrate-db-pro-media-files' ); ?>
						<a href="#" class="general-helper replace-guid-helper js-action-link"></a>
						<div class="mf-compare-message helper-message">
							<?php _e( 'Compare remote and local media files determining what files are missing or have been updated and need to be downloaded. Great for syncing two Media Libraries that only differ a little. For more details, see the <a href="https://deliciousbrains.com/wp-migrate-db-pro/doc/media-files-addon" target="_blank">Media Files doc</a>.', 'wp-migrate-db' ); ?>
						</div>
					</span>
				</label>
			</li>
			<li id="compare-remove-media-list-item">
				<label for="compare-remove-media" class="compare-remove-media">
					<input type="radio" name="media_migration_option" value="compare-remove" id="compare-remove-media"<?php checked( $media_migration_option, 'compare-remove', true ); ?> />
					<span class="action-text push">
						<?php _e( 'Compare, upload then remove', 'wp-migrate-db-pro-media-files' ); ?>
						<a href="#" class="general-helper replace-guid-helper js-action-link"></a>
						<div class="mf-compare-remove-message helper-message">
							<?php _e( 'Same as the above option, but also removes any remote files that are not found in your local Media Library. Any files in the uploads folder that are not part of the Media Library will remain untouched.', 'wp-migrate-db' ); ?>
						</div>
						<div class="compare-remove-warning">
							<?php _e( 'WARNING: Any files in the remote Media Library that are not in the local Media Library will be removed.', 'wp-migrate-db-pro-media-files' ); ?>
						</div>
					</span>
					<span class="action-text pull">
						<?php _e( 'Compare, download then remove', 'wp-migrate-db-pro-media-files' ); ?>
						<a href="#" class="general-helper replace-guid-helper js-action-link"></a>
						<div class="mf-compare-remove-message helper-message">
							<?php _e( 'Same as the above option, but also removes any local files that are not found in your remote Media Library. Any files in the uploads folder that are not part of the Media Library will remain untouched.', 'wp-migrate-db' ); ?>
						</div>
						<div class="compare-remove-warning">
							<?php _e( 'WARNING: Any files in the local Media Library that are not in the remote Media Library will be removed.', 'wp-migrate-db-pro-media-files' ); ?>
						</div>
					</span>
				</label>
			</li>
			<li id="copy-entire-media-list-item">
				<label for="copy-entire-media" class="copy-entire-media">
					<input type="radio" name="media_migration_option" value="entire" id="copy-entire-media"<?php checked( $media_migration_option, 'entire', true ); ?> />
					<span class="action-text push">
						<?php _e( 'Remove all then upload all', 'wp-migrate-db-pro-media-files' ); ?>
						<a href="#" class="general-helper replace-guid-helper js-action-link"></a>
						<div class="mf-copy-entire-message helper-message">
							<?php _e( 'Removes all files in the remote uploads folder and uploads all files in the local uploads folder that are in the Media Library.', 'wp-migrate-db' ); ?>
						</div>
					</span>
					<span class="action-text pull">
						<?php _e( 'Remove all then download all', 'wp-migrate-db-pro-media-files' ); ?>
						<a href="#" class="general-helper replace-guid-helper js-action-link"></a>
						<div class="mf-copy-entire-message helper-message">
							<?php _e( 'Removes all files in the local uploads folder and downloads all files in the remote uploads folder that are in the Media Library.', 'wp-migrate-db' ); ?>
						</div>
					</span>
				</label>
			</li>
		</ul>

		<?php do_action( 'wpmdbmf_after_migration_options' ); ?>
	</div>

	<p class="media-migration-unavailable inline-message warning" style="display: none; margin: 10px 0 0 0;">
		<strong><?php _e( 'Addon Missing', 'wp-migrate-db-pro-media-files' ); ?></strong> &mdash; <?php _e( 'The Media Files addon is inactive on the <strong>remote site</strong>. Please install and activate it to enable media file migration.', 'wp-migrate-db-pro-media-files' ); ?>
	</p>

	<p class="media-files-different-plugin-version-notice inline-message warning" style="display: none; margin: 10px 0 0 0;">
		<strong><?php _e( 'Version Mismatch', 'wp-migrate-db-pro-media-files' ); ?></strong> &mdash; <?php printf( __( 'We have detected you have version <span class="media-file-remote-version"></span> of WP Migrate DB Pro Media Files at <span class="media-files-remote-location"></span> but are using %1$s here. Please go to the <a href="%2$s">Plugins page</a> on both installs and check for updates.', 'wp-migrate-db-pro-media-files' ), $GLOBALS['wpmdb_meta'][$this->plugin_slug]['version'], network_admin_url( 'plugins.php' ) ); ?>
	</p>

</div>
