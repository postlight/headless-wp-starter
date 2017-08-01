<?php global $loaded_profile; ?>
<div id="mf-select-subsites-section" class="option-section sub-option" style="display: block;">
	<label for="mf-select-subsites" class="mf-select-subsites-checkbox checkbox-label">
		<input type="checkbox" id="mf-select-subsites" value="1" autocomplete="off" name="mf_select_subsites"<?php $this->maybe_checked( $loaded_profile, 'mf_select_subsites' ); ?> />
		<?php _e( 'Only transfer files for selected subsites', 'wp-migrate-db-pro-media-files' ); ?>
	</label>

	<div class="indent-wrap expandable-content mf-subsite-select-wrap" style="display: none;">
		<div class="mf-selected-subsites-wrap select-wrap">
			<?php
			// If loading a Pull migration profile, we need to keep tabs on the original site selections until remote's data is available.
			if ( 'pull' === $loaded_profile['action'] && ! empty( $loaded_profile['mf_selected_subsites'] ) ) {
				?>
				<input type="hidden" name="_mf_selected_subsites" id="_mf-selected-subsites" value="<?php echo esc_attr( json_encode( $loaded_profile['mf_selected_subsites'] ) ); ?>">
				<?php
			}
			?>
			<select multiple="multiple" name="mf_selected_subsites[]" id="mf-selected-subsites" class="multiselect" autocomplete="off">
				<?php
				if ( 'pull' !== $loaded_profile['action'] ) {
					global $wpdb;
					$table_prefix = $wpdb->base_prefix;
					foreach ( $this->subsites_list() as $blog_id => $subsite_path ) {
						$selected = '';
						if ( empty( $loaded_profile['mf_selected_subsites'] ) ||
						     ( ! empty( $loaded_profile['mf_selected_subsites'] ) && in_array( $blog_id, $loaded_profile['mf_selected_subsites'] ) )
						) {
							$selected = ' selected="selected"';
						}
						$subsite_path .= ' (' . $table_prefix . ( '1' !== $blog_id ) ? $blog_id . '_' : '' . ')';
						printf(
							'<option value="%1$s"' . $selected . '>%2$s</option>',
							esc_attr( $blog_id ),
							esc_html( $subsite_path )
						);
					}
				}
				?>
			</select>
			<br/>
			<a href="#" class="multiselect-select-all js-action-link"><?php _e( 'Select All', 'wp-migrate-db-pro-media-files' ); ?></a>
			<span class="select-deselect-divider">/</span>
			<a href="#" class="multiselect-deselect-all js-action-link"><?php _e( 'Deselect All', 'wp-migrate-db-pro-media-files' ); ?></a>
			<span class="select-deselect-divider">/</span>
			<a href="#" class="multiselect-invert-selection js-action-link"><?php _e( 'Invert Selection', 'wp-migrate-db-pro-media-files' ); ?></a>
		</div>

		<p class="mf-selected-subsites-tables-differ inline-message warning" style="display: none; margin: 10px 0 0 0;">
			<strong><?php _e( 'Migrating Media Library data and files differ', 'wp-migrate-db-pro-media-files' ); ?></strong> &mdash; <?php _e( 'There is a mismatch between Media Library table data being updated and the files selected for transfer. You may end up with broken links to media.', 'wp-migrate-db-pro-media-files' ); ?>
		</p>
	</div>
</div>