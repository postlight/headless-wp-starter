<?php
/**
 * Custom Post Type UI Taxonomy Settings.
 *
 * @package CPTUI
 * @subpackage Taxonomies
 * @author WebDevStudios
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add our cptui.js file, with dependencies on jQuery and jQuery UI.
 *
 * @since 1.0.0
 *
 * @internal
 */
function cptui_taxonomies_enqueue_scripts() {

	$current_screen = get_current_screen();

	if ( ! is_object( $current_screen ) || 'cpt-ui_page_cptui_manage_taxonomies' !== $current_screen->base ) {
		return;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	wp_enqueue_script( 'cptui' );
	wp_localize_script(	'cptui', 'cptui_tax_data',
		array(
			'confirm' => esc_html__( 'Are you sure you want to delete this? Deleting will NOT remove created content.', 'custom-post-type-ui' ),
			'no_associated_type' => esc_html( 'Please select a post type to associate with.', 'custom-post-type-ui' )
		)
	);
}
add_action( 'admin_enqueue_scripts', 'cptui_taxonomies_enqueue_scripts' );

/**
 * Register our tabs for the Taxonomy screen.
 *
 * @since 1.3.0
 *
 * @internal
 *
 * @param array  $tabs         Array of tabs to display. Optional.
 * @param string $current_page Current page being shown. Optional. Default empty string.
 * @return array Amended array of tabs to show.
 */
function cptui_taxonomy_tabs( $tabs = array(), $current_page = '' ) {

	if ( 'taxonomies' === $current_page ) {
		$taxonomies = cptui_get_taxonomy_data();
		$classes    = array( 'nav-tab' );

		$tabs['page_title'] = get_admin_page_title();
		$tabs['tabs']       = array();
		// Start out with our basic "Add new" tab.
		$tabs['tabs']['add'] = array(
			'text'          => esc_html__( 'Add New Taxonomy', 'custom-post-type-ui' ),
			'classes'       => $classes,
			'url'           => cptui_admin_url( 'admin.php?page=cptui_manage_' . $current_page ),
			'aria-selected' => 'false',
		);

		$action = cptui_get_current_action();
		if ( empty( $action ) ) {
			$tabs['tabs']['add']['classes'][] = 'nav-tab-active';
			$tabs['tabs']['add']['aria-selected'] = 'true';
		}

		if ( ! empty( $taxonomies ) ) {

			if ( ! empty( $action ) ) {
				$classes[] = 'nav-tab-active';
			}
			$tabs['tabs']['edit'] = array(
				'text'          => esc_html__( 'Edit Taxonomies', 'custom-post-type-ui' ),
				'classes'       => $classes,
				'url'           => esc_url( add_query_arg( array( 'action' => 'edit' ), cptui_admin_url( 'admin.php?page=cptui_manage_' . $current_page ) ) ),
				'aria-selected' => ( ! empty( $action ) ) ? 'true' : 'false',
			);

			$tabs['tabs']['view'] = array(
				'text'          => esc_html__( 'View Taxonomies', 'custom-post-type-ui' ),
				'classes'       => array( 'nav-tab' ), // Prevent notices.
				'url'           => esc_url( cptui_admin_url( 'admin.php?page=cptui_listings#taxonomies' ) ),
				'aria-selected' => 'false',
			);

			$tabs['tabs']['export'] = array(
				'text'          => esc_html__( 'Import/Export Taxonomies', 'custom-post-type-ui' ),
				'classes'       => array( 'nav-tab' ), // Prevent notices.
				'url'           => esc_url( cptui_admin_url( 'admin.php?page=cptui_tools&action=taxonomies' ) ),
				'aria-selected' => 'false',
			);
		}
	}

	return $tabs;
}

add_filter( 'cptui_get_tabs', 'cptui_taxonomy_tabs', 10, 2 );

/**
 * Create our settings page output.
 *
 * @since 1.0.0
 *
 * @internal
 */
function cptui_manage_taxonomies() {

	$tab = ( ! empty( $_GET ) && ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) ? 'edit' : 'new';
	$tab_class = 'cptui-' . $tab; ?>

	<div class="wrap <?php echo esc_attr( $tab_class ); ?>">

	<?php
	/**
	 * Fires right inside the wrap div for the taxonomy editor screen.
	 *
	 * @since 1.3.0
	 */
	do_action( 'cptui_inside_taxonomy_wrap' );

	/**
	 * Filters whether or not a taxonomy was deleted.
	 *
	 * @since 1.4.0
	 *
	 * @param bool $value Whether or not taxonomy deleted. Default false.
	 */
	$taxonomy_deleted = apply_filters( 'cptui_taxonomy_deleted', false );

	// Create our tabs.
	cptui_settings_tab_menu( $page = 'taxonomies' );

	/**
	 * Fires below the output for the tab menu on the taxonomy add/edit screen.
	 *
	 * @since 1.3.0
	 */
	do_action( 'cptui_below_taxonomy_tab_menu' );

	if ( 'edit' == $tab ) {

		$taxonomies = cptui_get_taxonomy_data();

		$selected_taxonomy = cptui_get_current_taxonomy( $taxonomy_deleted );

		if ( $selected_taxonomy ) {
			if ( array_key_exists( $selected_taxonomy, $taxonomies ) ) {
				$current = $taxonomies[ $selected_taxonomy ];
			}
		}
	}

	$ui = new cptui_admin_ui();

	// Will only be set if we're already on the edit screen.
	if ( ! empty( $taxonomies ) ) { ?>
		<form id="cptui_select_taxonomy" method="post" action="<?php echo esc_url( cptui_get_post_form_action( $ui ) ); ?>">
			<label for="taxonomy"><?php esc_html_e( 'Select: ', 'custom-post-type-ui' ); ?></label>
			<?php
			cptui_taxonomies_dropdown( $taxonomies );

			/**
			 * Filters the text value to use on the select taxonomy button.
			 *
			 * @since 1.0.0
			 *
			 * @param string $value Text to use for the button.
			 */
			?>
			<input type="submit" class="button-secondary" name="cptui_select_taxonomy_submit" value="<?php echo esc_attr( apply_filters( 'cptui_taxonomy_submit_select', esc_attr__( 'Select', 'custom-post-type-ui' ) ) ); ?>" />
		</form>
	<?php

		/**
		 * Fires below the taxonomy select input.
		 *
		 * @since 1.1.0
		 *
		 * @param string $value Current taxonomy selected.
		 */
		do_action( 'cptui_below_taxonomy_select', $current['name'] );
	} ?>

	<form class="taxonomiesui" method="post" action="<?php echo esc_url( cptui_get_post_form_action( $ui ) ); ?>">
		<div class="postbox-container">
		<div id="poststuff">
			<div class="cptui-section postbox">
				<button type="button" class="handlediv button-link" aria-expanded="true">
					<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Basic settings', 'custom-post-type-ui' ); ?></span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
				<h2 class="hndle">
					<span><?php esc_html_e( 'Basic settings', 'custom-post-type-ui' ); ?></span>
				</h2>
				<div class="inside">
					<div class="main">
						<table class="form-table cptui-table">
							<?php
							echo $ui->get_tr_start() . $ui->get_th_start();
							echo $ui->get_label( 'name', esc_html__( 'Taxonomy Slug', 'custom-post-type-ui' ) ) . $ui->get_required_span();

							if ( 'edit' == $tab ) {
								echo '<p id="slugchanged" class="hidemessage">' . __( 'Slug has changed', 'custom_post_type_ui' ) . '</p>';
							}
							echo $ui->get_th_end() . $ui->get_td_start();

							echo $ui->get_text_input( array(
								'namearray'   => 'cpt_custom_tax',
								'name'        => 'name',
								'textvalue'   => ( isset( $current['name'] ) ) ? esc_attr( $current['name'] ) : '',
								'maxlength'   => '32',
								'helptext'    => esc_attr__( 'The taxonomy name/slug. Used for various queries for taxonomy content.', 'custom-post-type-ui' ),
								'required'    => true,
								'placeholder' => false,
								'wrap'        => false,
							) );

							echo '<p class="cptui-slug-details">';
							esc_html_e( 'Slugs should only contain alphanumeric, latin characters. Underscores should be used in place of spaces. Set "Custom Rewrite Slug" field to make slug use dashes for URLs.', 'custom-post-type-ui' );
							echo '</p>';

							if ( 'edit' == $tab ) {
								echo '<p>';
								esc_html_e( 'DO NOT EDIT the taxonomy slug unless also planning to migrate terms. Changing the slug registers a new taxonomy entry.', 'custom-post-type-ui' );
								echo '</p>';

								echo '<div class="cptui-spacer">';
								echo $ui->get_check_input( array(
									'checkvalue' => 'update_taxonomy',
									'checked'    => 'false',
									'name'       => 'update_taxonomy',
									'namearray'  => 'update_taxonomy',
									'labeltext'  => esc_html__( 'Migrate terms to newly renamed taxonomy?', 'custom-post-type-ui' ),
									'helptext'   => '',
									'default'    => false,
									'wrap'       => false,
								) );
								echo '</div>';
							}

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_tax',
								'name'      => 'label',
								'textvalue' => ( isset( $current['label'] ) ) ? esc_attr( $current['label'] ) : '',
								'aftertext' => esc_html__( '(e.g. Actors)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Plural Label', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Used for the taxonomy admin menu item.', 'custom-post-type-ui' ),
								'required'  => true,
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_tax',
								'name'      => 'singular_label',
								'textvalue' => ( isset( $current['singular_label'] ) ) ? esc_attr( $current['singular_label'] ) : '',
								'aftertext' => esc_html__( '(e.g. Actor)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Singular Label', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Used when a singular label is needed.', 'custom-post-type-ui' ),
								'required'  => true,
							) );

							echo $ui->get_td_end() . $ui->get_tr_end();

							echo $ui->get_tr_start() . $ui->get_th_start() . esc_html__( 'Attach to Post Type', 'custom-post-type-ui' ) . $ui->get_required_span();
							echo $ui->get_p( esc_html__( 'Add support for available registered post types. At least one is required.', 'custom-post-type-ui' ) );
							echo $ui->get_th_end() . $ui->get_td_start() . $ui->get_fieldset_start();

							/**
							 * Filters the arguments for post types to list for taxonomy association.
							 *
							 * @since 1.0.0
							 *
							 * @param array $value Array of default arguments.
							 */
							$args = apply_filters( 'cptui_attach_post_types_to_taxonomy', array( 'public' => true ) );

							// If they don't return an array, fall back to the original default. Don't need to check for empty, because empty array is default for $args param in get_post_types anyway.
							if ( ! is_array( $args ) ) {
								$args = array( 'public' => true );
							}
							$output = 'objects'; // Or objects.

							/**
							 * Filters the results returned to display for available post types for taxonomy.
							 *
							 * @since 1.3.0
							 *
							 * @param array  $value  Array of post type objects.
							 * @param array  $args   Array of arguments for the post type query.
							 * @param string $output The output type we want for the results.
							 */
							$post_types = apply_filters( 'cptui_get_post_types_for_taxonomies', get_post_types( $args, $output ), $args, $output );

							foreach ( $post_types as $post_type ) {
								$core_label = ( in_array( $post_type->name, array(
									'post',
									'page',
									'attachment',
								) ) ) ? esc_html__( '(WP Core)', 'custom-post-type-ui' ) : '';
								echo $ui->get_check_input( array(
									'checkvalue' => $post_type->name,
									'checked'    => ( ! empty( $current['object_types'] ) && is_array( $current['object_types'] ) && in_array( $post_type->name, $current['object_types'] ) ) ? 'true' : 'false',
									'name'       => $post_type->name,
									'namearray'  => 'cpt_post_types',
									'textvalue'  => $post_type->name,
									'labeltext'  => $post_type->label . ' ' . $core_label,
									'wrap'       => false,
								) );
							}

							echo $ui->get_fieldset_end() . $ui->get_td_end() . $ui->get_tr_end();
							?>
						</table>
						<p class="submit">
							<?php wp_nonce_field( 'cptui_addedit_taxonomy_nonce_action', 'cptui_addedit_taxonomy_nonce_field' );
							if ( ! empty( $_GET ) && ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) { ?>
								<?php

								/**
								 * Filters the text value to use on the button when editing.
								 *
								 * @since 1.0.0
								 *
								 * @param string $value Text to use for the button.
								 */
								?>
								<input type="submit" class="button-primary" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_taxonomy_submit_edit', esc_attr__( 'Save Taxonomy', 'custom-post-type-ui' ) ) ); ?>" />
								<?php

								/**
								 * Filters the text value to use on the button when deleting.
								 *
								 * @since 1.0.0
								 *
								 * @param string $value Text to use for the button.
								 */
								?>
								<input type="submit" class="button-secondary" name="cpt_delete" id="cpt_submit_delete" value="<?php echo esc_attr( apply_filters( 'cptui_taxonomy_submit_delete', __( 'Delete Taxonomy', 'custom-post-type-ui' ) ) ); ?>" />
							<?php } else { ?>
								<?php

								/**
								 * Filters the text value to use on the button when adding.
								 *
								 * @since 1.0.0
								 *
								 * @param string $value Text to use for the button.
								 */
								?>
								<input type="submit" class="button-primary cptui-taxonomy-submit" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_taxonomy_submit_add', esc_attr__( 'Add Taxonomy', 'custom-post-type-ui' ) ) ); ?>" />
							<?php } ?>

							<?php if ( ! empty( $current ) ) { ?>
								<input type="hidden" name="tax_original" id="tax_original" value="<?php echo esc_attr( $current['name'] ); ?>" />
							<?php }

							// Used to check and see if we should prevent duplicate slugs. ?>
							<input type="hidden" name="cpt_tax_status" id="cpt_tax_status" value="<?php echo esc_attr( $tab ); ?>" />
						</p>
					</div>
				</div>
			</div>
			<div class="cptui-section postbox">
				<button type="button" class="handlediv button-link" aria-expanded="true">
					<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Additional labels', 'custom-post-type-ui' ); ?></span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
				<h2 class="hndle">
					<span><?php esc_html_e( 'Additional labels', 'custom-post-type-ui' ); ?></span>
				</h2>
				<div class="inside">
					<div class="main">
						<table class="form-table cptui-table">

							<?php
							if ( isset( $current['description'] ) ) {
								$current['description'] = stripslashes_deep( $current['description'] );
							}
							echo $ui->get_textarea_input( array(
								'namearray' => 'cpt_custom_tax',
								'name'      => 'description',
								'rows'      => '4',
								'cols'      => '40',
								'textvalue' => ( isset( $current['description'] ) ) ? esc_textarea( $current['description'] ) : '',
								'labeltext' => esc_html__( 'Description', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Describe what your taxonomy is used for.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'menu_name',
								'textvalue' => ( isset( $current['labels']['menu_name'] ) ) ? esc_attr( $current['labels']['menu_name'] ) : '',
								'aftertext' => esc_attr__( '(e.g. Actors)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Menu Name', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom admin menu name for your taxonomy.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'all_items',
								'textvalue' => ( isset( $current['labels']['all_items'] ) ) ? esc_attr( $current['labels']['all_items'] ) : '',
								'aftertext' => esc_attr__( '(e.g. All Actors)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'All Items', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Used as tab text when showing all terms for hierarchical taxonomy while editing post.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'edit_item',
								'textvalue' => ( isset( $current['labels']['edit_item'] ) ) ? esc_attr( $current['labels']['edit_item'] ) : '',
								'aftertext' => esc_attr__( '(e.g. Edit Actor)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Edit Item', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Used at the top of the term editor screen for an existing taxonomy term.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'view_item',
								'textvalue' => ( isset( $current['labels']['view_item'] ) ) ? esc_attr( $current['labels']['view_item'] ) : '',
								'aftertext' => esc_attr__( '(e.g. View Actor)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'View Item', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Used in the admin bar when viewing editor screen for an existing taxonomy term.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'update_item',
								'textvalue' => ( isset( $current['labels']['update_item'] ) ) ? esc_attr( $current['labels']['update_item'] ) : '',
								'aftertext' => esc_attr__( '(e.g. Update Actor Name)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Update Item Name', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'add_new_item',
								'textvalue' => ( isset( $current['labels']['add_new_item'] ) ) ? esc_attr( $current['labels']['add_new_item'] ) : '',
								'aftertext' => esc_attr__( '(e.g. Add New Actor)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Add New Item', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Used at the top of the term editor screen and button text for a new taxonomy term.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'new_item_name',
								'textvalue' => ( isset( $current['labels']['new_item_name'] ) ) ? esc_attr( $current['labels']['new_item_name'] ) : '',
								'aftertext' => esc_attr__( '(e.g. New Actor Name)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'New Item Name', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'parent_item',
								'textvalue' => ( isset( $current['labels']['parent_item'] ) ) ? esc_attr( $current['labels']['parent_item'] ) : '',
								'aftertext' => esc_attr__( '(e.g. Parent Actor)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Parent Item', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'parent_item_colon',
								'textvalue' => ( isset( $current['labels']['parent_item_colon'] ) ) ? esc_attr( $current['labels']['parent_item_colon'] ) : '',
								'aftertext' => esc_attr__( '(e.g. Parent Actor:)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Parent Item Colon', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'search_items',
								'textvalue' => ( isset( $current['labels']['search_items'] ) ) ? esc_attr( $current['labels']['search_items'] ) : '',
								'aftertext' => esc_attr__( '(e.g. Search Actors)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Search Items', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'popular_items',
								'textvalue' => ( isset( $current['labels']['popular_items'] ) ) ? esc_attr( $current['labels']['popular_items'] ) : null,
								'aftertext' => esc_attr__( '(e.g. Popular Actors)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Popular Items', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'separate_items_with_commas',
								'textvalue' => ( isset( $current['labels']['separate_items_with_commas'] ) ) ? esc_attr( $current['labels']['separate_items_with_commas'] ) : null,
								'aftertext' => esc_attr__( '(e.g. Separate Actors with commas)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Separate Items with Commas', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'add_or_remove_items',
								'textvalue' => ( isset( $current['labels']['add_or_remove_items'] ) ) ? esc_attr( $current['labels']['add_or_remove_items'] ) : null,
								'aftertext' => esc_attr__( '(e.g. Add or remove Actors)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Add or Remove Items', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'choose_from_most_used',
								'textvalue' => ( isset( $current['labels']['choose_from_most_used'] ) ) ? esc_attr( $current['labels']['choose_from_most_used'] ) : null,
								'aftertext' => esc_attr__( '(e.g. Choose from the most used Actors)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Choose From Most Used', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'not_found',
								'textvalue' => ( isset( $current['labels']['not_found'] ) ) ? esc_attr( $current['labels']['not_found'] ) : null,
								'aftertext' => esc_attr__( '(e.g. No Actors found)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Not found', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy label. Used in the admin menu for displaying taxonomies.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'no_terms',
								'textvalue' => ( isset( $current['labels']['no_terms'] ) ) ? esc_attr( $current['labels']['no_terms'] ) : null,
								'aftertext' => esc_html__( '(e.g. No actors)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'No terms', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Used when indicating that there are no terms in the given taxonomy associated with an object.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'items_list_navigation',
								'textvalue' => ( isset( $current['labels']['items_list_navigation'] ) ) ? esc_attr( $current['labels']['items_list_navigation'] ) : null,
								'aftertext' => esc_html__( '(e.g. Actors list navigation)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Items List Navigation', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Screen reader text for the pagination heading on the term listing screen.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_tax_labels',
								'name'      => 'items_list',
								'textvalue' => ( isset( $current['labels']['items_list'] ) ) ? esc_attr( $current['labels']['items_list'] ) : null,
								'aftertext' => esc_html__( '(e.g. Actors list)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Items List', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Screen reader text for the items list heading on the term listing screen.', 'custom-post-type-ui' ),
							) );
							?>
						</table>
					</div>
				</div>
			</div>
			<div class="cptui-section postbox">
				<button type="button" class="handlediv button-link" aria-expanded="true">
					<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Settings', 'custom-post-type-ui' ); ?></span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
				<h2 class="hndle">
					<span><?php esc_html_e( 'Settings', 'custom-post-type-ui' ); ?></span>
				</h2>
				<div class="inside">
					<div class="main">
						<table class="form-table cptui-table">
							<?php

							$select             = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array(
										'attr'    => '1',
										'text'    => esc_attr__( 'True', 'custom-post-type-ui' ),
										'default' => 'true'
									),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['public'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['public'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'public',
								'labeltext'  => esc_html__( 'Public', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Whether or not the taxonomy should be publicly queryable.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select             = array(
								'options' => array(
									array(
										'attr'    => '0',
										'text'    => esc_attr__( 'False', 'custom-post-type-ui' ),
										'default' => 'true'
									),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ) ),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['hierarchical'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['hierarchical'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'hierarchical',
								'labeltext'  => esc_html__( 'Hierarchical', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: false) Whether the taxonomy can have parent-child relationships.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select             = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array(
										'attr'    => '1',
										'text'    => esc_attr__( 'True', 'custom-post-type-ui' ),
										'default' => 'true'
									),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['show_ui'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['show_ui'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'show_ui',
								'labeltext'  => esc_html__( 'Show UI', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Whether to generate a default UI for managing this custom taxonomy.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select             = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array(
										'attr'    => '1',
										'text'    => esc_attr__( 'True', 'custom-post-type-ui' ),
										'default' => 'true'
									),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['show_in_menu'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['show_in_menu'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'show_in_menu',
								'labeltext'  => esc_html__( 'Show in menu', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: value of show_ui) Whether to show the taxonomy in the admin menu.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select             = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array(
										'attr'    => '1',
										'text'    => esc_attr__( 'True', 'custom-post-type-ui' ),
										'default' => 'true'
									),
								),
							);
							$selected           = ( isset( $current ) && ! empty( $current['show_in_nav_menus'] ) ) ? disp_boolean( $current['show_in_nav_menus'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['show_in_nav_menus'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'show_in_nav_menus',
								'labeltext'  => esc_html__( 'Show in nav menus', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: value of public) Whether to make the taxonomy available for selection in navigation menus.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select             = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array(
										'attr'    => '1',
										'text'    => esc_attr__( 'True', 'custom-post-type-ui' ),
										'default' => 'true'
									),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['query_var'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['query_var'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'query_var',
								'labeltext'  => esc_html__( 'Query Var', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Sets the query_var key for this taxonomy.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_tax',
								'name'      => 'query_var_slug',
								'textvalue' => ( isset( $current['query_var_slug'] ) ) ? esc_attr( $current['query_var_slug'] ) : '',
								'aftertext' => esc_attr__( '(default: taxonomy slug). Query var needs to be true to use.', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Custom Query Var String', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Sets a custom query_var slug for this taxonomy.', 'custom-post-type-ui' ),
							) );

							$select             = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array(
										'attr'    => '1',
										'text'    => esc_attr__( 'True', 'custom-post-type-ui' ),
										'default' => 'true'
									),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['rewrite'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['rewrite'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'rewrite',
								'labeltext'  => esc_html__( 'Rewrite', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Whether or not WordPress should use rewrites for this taxonomy.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_tax',
								'name'      => 'rewrite_slug',
								'textvalue' => ( isset( $current['rewrite_slug'] ) ) ? esc_attr( $current['rewrite_slug'] ) : '',
								'aftertext' => esc_attr__( '(default: taxonomy name)', 'custom-post-type-ui' ),
								'labeltext' => esc_html__( 'Custom Rewrite Slug', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom taxonomy rewrite slug.', 'custom-post-type-ui' ),
							) );

							$select             = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array(
										'attr'    => '1',
										'text'    => esc_attr__( 'True', 'custom-post-type-ui' ),
										'default' => 'true'
									),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['rewrite_withfront'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['rewrite_withfront'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'rewrite_withfront',
								'labeltext'  => esc_html__( 'Rewrite With Front', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Should the permastruct be prepended with the front base.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select             = array(
								'options' => array(
									array(
										'attr'    => '0',
										'text'    => esc_attr__( 'False', 'custom-post-type-ui' ),
										'default' => 'false'
									),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ) ),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['rewrite_hierarchical'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['rewrite_hierarchical'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'rewrite_hierarchical',
								'labeltext'  => esc_html__( 'Rewrite Hierarchical', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: false) Should the permastruct allow hierarchical urls.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select             = array(
								'options' => array(
									array(
										'attr'    => '0',
										'text'    => esc_attr__( 'False', 'custom-post-type-ui' ),
										'default' => 'true'
									),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ) ),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['show_admin_column'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['show_admin_column'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'show_admin_column',
								'labeltext'  => esc_html__( 'Show Admin Column', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: false) Whether to allow automatic creation of taxonomy columns on associated post-types.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select             = array(
								'options' => array(
									array(
										'attr'    => '0',
										'text'    => esc_attr__( 'False', 'custom-post-type-ui' ),
										'default' => 'false'
									),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ) ),
								),
							);
							$selected           = ( isset( $current ) ) ? disp_boolean( $current['show_in_rest'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['show_in_rest'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'show_in_rest',
								'labeltext'  => esc_html__( 'Show in REST API', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: false) Whether to show this taxonomy data in the WP REST API.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_tax',
								'name'      => 'rest_base',
								'labeltext' => esc_html__( 'REST API base slug', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Slug to use in REST API URLs.', 'custom-post-type-ui' ),
								'textvalue' => ( isset( $current['rest_base'] ) ) ? esc_attr( $current['rest_base'] ) : '',
							) );

							$select             = array(
								'options' => array(
									array(
										'attr'    => '0',
										'text'    => esc_attr__( 'False', 'custom-post-type-ui' ),
										'default' => 'false'
									),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ) ),
								),
							);
							$selected           = ( isset( $current ) && ! empty( $current['show_in_quick_edit'] ) ) ? disp_boolean( $current['show_in_quick_edit'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['show_in_quick_edit'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_tax',
								'name'       => 'show_in_quick_edit',
								'labeltext'  => esc_html__( 'Show in quick/bulk edit panel.', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: false) Whether to show the taxonomy in the quick/bulk edit panel.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );
							?>
						</table>
					</div>
				</div>
			</div>

			<?php
			/**
			 * Fires after the default fieldsets on the taxonomy screen.
			 *
			 * @since 1.3.0
			 *
			 * @param cptui_admin_ui $ui Admin UI instance.
			 */
			do_action( 'cptui_taxonomy_after_fieldsets', $ui ); ?>

			<p class="submit">
				<?php wp_nonce_field( 'cptui_addedit_taxonomy_nonce_action', 'cptui_addedit_taxonomy_nonce_field' );
				if ( ! empty( $_GET ) && ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) { ?>
					<?php

					/**
					 * Filters the text value to use on the button when editing.
					 *
					 * @since 1.0.0
					 *
					 * @param string $value Text to use for the button.
					 */
					?>
					<input type="submit" class="button-primary cptui-taxonomy-submit" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_taxonomy_submit_edit', esc_attr__( 'Save Taxonomy', 'custom-post-type-ui' ) ) ); ?>" />
					<?php

					/**
					 * Filters the text value to use on the button when deleting.
					 *
					 * @since 1.0.0
					 *
					 * @param string $value Text to use for the button.
					 */
					?>
					<input type="submit" class="button-secondary" name="cpt_delete" id="cpt_submit_delete" value="<?php echo esc_attr( apply_filters( 'cptui_taxonomy_submit_delete', __( 'Delete Taxonomy', 'custom-post-type-ui' ) ) ); ?>" />
				<?php } else { ?>
					<?php

					/**
					 * Filters the text value to use on the button when adding.
					 *
					 * @since 1.0.0
					 *
					 * @param string $value Text to use for the button.
					 */
					?>
					<input type="submit" class="button-primary" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_taxonomy_submit_add', esc_attr__( 'Add Taxonomy', 'custom-post-type-ui' ) ) ); ?>" />
				<?php } ?>

				<?php if ( ! empty( $current ) ) { ?>
					<input type="hidden" name="tax_original" id="tax_original" value="<?php echo $current['name']; ?>" />
				<?php }

				// Used to check and see if we should prevent duplicate slugs. ?>
				<input type="hidden" name="cpt_tax_status" id="cpt_tax_status" value="<?php echo $tab; ?>" />
			</p>
		</div>
		</div>
	</form>
	</div><!-- End .wrap -->
<?php
}

/**
 * Construct a dropdown of our taxonomies so users can select which to edit.
 *
 * @since 1.0.0
 *
 * @param array $taxonomies Array of taxonomies that are registered. Optional.
 */
function cptui_taxonomies_dropdown( $taxonomies = array() ) {

	$ui = new cptui_admin_ui();

	if ( ! empty( $taxonomies ) ) {
		$select = array();
		$select['options'] = array();

		foreach ( $taxonomies as $tax ) {
			$text = ( ! empty( $tax['label'] ) ) ? $tax['label'] : $tax['name'];
			$select['options'][] = array( 'attr' => $tax['name'], 'text' => $text );
		}

		$current = cptui_get_current_taxonomy();

		$select['selected'] = $current;
		echo $ui->get_select_input( array(
			'namearray'     => 'cptui_selected_taxonomy',
			'name'          => 'taxonomy',
			'selections'    => $select,
			'wrap'          => false,
		) );
	}
}

/**
 * Get the selected taxonomy from the $_POST global.
 *
 * @since 1.0.0
 *
 * @internal
 *
 * @param bool $taxonomy_deleted Whether or not a taxonomy was recently deleted. Optional. Default false.
 * @return bool|string False on no result, sanitized taxonomy if set.
 */
function cptui_get_current_taxonomy( $taxonomy_deleted = false ) {

	$tax = false;

	if ( ! empty( $_POST ) ) {
		if ( isset( $_POST['cptui_selected_taxonomy']['taxonomy'] ) ) {
			$tax = sanitize_text_field( $_POST['cptui_selected_taxonomy']['taxonomy'] );
		} else if ( $taxonomy_deleted ) {
			$taxonomies = cptui_get_taxonomy_data();
			$tax = key( $taxonomies );
		} else if ( isset( $_POST['cpt_custom_tax']['name'] ) ) {
			// Return the submitted value.
			if ( ! in_array( $_POST['cpt_custom_tax']['name'], cptui_reserved_taxonomies(), true ) ) {
				$tax = sanitize_text_field( $_POST['cpt_custom_tax']['name'] );
			} else {
				// Return the original value since user tried to submit a reserved term.
				$tax = sanitize_text_field( $_POST['tax_original'] );
			}
		}
	} else if ( ! empty( $_GET ) && isset( $_GET['cptui_taxonomy'] ) ) {
		$tax = sanitize_text_field( $_GET['cptui_taxonomy'] );
	} else {
		$taxonomies = cptui_get_taxonomy_data();
		if ( ! empty( $taxonomies ) ) {
			// Will return the first array key.
			$tax = key( $taxonomies );
		}
	}

	/**
	 * Filters the current taxonomy to edit.
	 *
	 * @since 1.3.0
	 *
	 * @param string $tax Taxonomy slug.
	 */
	return apply_filters( 'cptui_current_taxonomy', $tax );
}

/**
 * Delete our custom taxonomy from the array of taxonomies.
 *
 * @since 1.0.0
 *
 * @internal
 *
 * @param array $data The $_POST values. Optional.
 * @return bool|string False on failure, string on success.
 */
function cptui_delete_taxonomy( $data = array() ) {

	if ( is_string( $data ) && taxonomy_exists( $data ) ) {
		$data = array(
			'cpt_custom_tax' => array(
				'name' => $data,
			),
		);
	}

	// Check if they selected one to delete.
	if ( empty( $data['cpt_custom_tax']['name'] ) ) {
		return cptui_admin_notices( 'error', '', false, esc_html__( 'Please provide a taxonomy to delete', 'custom-post-type-ui' ) );
	}

	/**
	 * Fires before a taxonomy is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of taxonomy data we are deleting.
	 */
	do_action( 'cptui_before_delete_taxonomy', $data );

	$taxonomies = cptui_get_taxonomy_data();

	if ( array_key_exists( strtolower( $data['cpt_custom_tax']['name'] ), $taxonomies ) ) {

		unset( $taxonomies[ $data['cpt_custom_tax']['name'] ] );

		/**
		 * Filters whether or not 3rd party options were saved successfully within taxonomy deletion.
		 *
		 * @since 1.3.0
		 *
		 * @param bool  $value      Whether or not someone else saved successfully. Default false.
		 * @param array $taxonomies Array of our updated taxonomies data.
		 * @param array $data       Array of submitted taxonomy to update.
		 */
		if ( false === ( $success = apply_filters( 'cptui_taxonomy_delete_tax', false, $taxonomies, $data ) ) ) {
			$success = update_option( 'cptui_taxonomies', $taxonomies );
		}
	}

	/**
	 * Fires after a taxonomy is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of taxonomy data that was deleted.
	 */
	do_action( 'cptui_after_delete_taxonomy', $data );

	// Used to help flush rewrite rules on init.
	set_transient( 'cptui_flush_rewrite_rules', 'true', 5 * 60 );

	if ( isset( $success ) ) {
		return 'delete_success';
	}
	return 'delete_fail';
}

/**
 * Add to or update our CPTUI option with new data.
 *
 * @since 1.0.0
 *
 * @internal
 *
 * @param array $data Array of taxonomy data to update. Optional.
 * @return bool|string False on failure, string on success.
 */
function cptui_update_taxonomy( $data = array() ) {

	/**
	 * Fires before a taxonomy is updated to our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of taxonomy data we are updating.
	 */
	do_action( 'cptui_before_update_taxonomy', $data );

	// They need to provide a name.
	if ( empty( $data['cpt_custom_tax']['name'] ) ) {
		return cptui_admin_notices( 'error', '', false, esc_html__( 'Please provide a taxonomy name', 'custom-post-type-ui' ) );
	}

	if ( empty( $data['cpt_post_types'] ) ) {
		return cptui_admin_notices( 'error', '', false, esc_html__( 'Please provide a post type to attach to.', 'custom-post-type-ui' ) );
	}

	if ( ! empty( $data['tax_original'] ) && $data['tax_original'] != $data['cpt_custom_tax']['name'] ) {
		if ( ! empty( $data['update_taxonomy'] ) ) {
			add_filter( 'cptui_convert_taxonomy_terms', '__return_true' );
		}
	}

	foreach ( $data as $key => $value ) {
		if ( is_string( $value ) ) {
			$data[ $key ] = sanitize_text_field( $value );
		} else {
			array_map( 'sanitize_text_field', $data[ $key ] );
		}
	}

	if ( false !== strpos( $data['cpt_custom_tax']['name'], '\'' ) ||
		false !== strpos( $data['cpt_custom_tax']['name'], '\"' ) ||
		false !== strpos( $data['cpt_custom_tax']['rewrite_slug'], '\'' ) ||
		false !== strpos( $data['cpt_custom_tax']['rewrite_slug'], '\"' ) ) {

		add_filter( 'cptui_custom_error_message', 'cptui_slug_has_quotes' );
		return 'error';
	}

	$taxonomies = cptui_get_taxonomy_data();

	/**
	 * Check if we already have a post type of that name.
	 *
	 * @since 1.3.0
	 *
	 * @param bool   $value      Assume we have no conflict by default.
	 * @param string $value      Post type slug being saved.
	 * @param array  $post_types Array of existing post types from CPTUI.
	 */
	$slug_exists = apply_filters( 'cptui_taxonomy_slug_exists', false, $data['cpt_custom_tax']['name'], $taxonomies );
	if ( true === $slug_exists ) {
		add_filter( 'cptui_custom_error_message', 'cptui_slug_matches_taxonomy' );
		return 'error';
	}

	foreach ( $data['cpt_tax_labels'] as $key => $label ) {
		if ( empty( $label ) ) {
			unset( $data['cpt_tax_labels'][ $key ] );
		}
		$label = str_replace( '"', '', htmlspecialchars_decode( $label ) );
		$label = htmlspecialchars( $label, ENT_QUOTES );
		$label = trim( $label );
		$data['cpt_tax_labels'][ $key ] = stripslashes_deep( $label );
	}

	$label = ucwords( str_replace( '_', ' ', $data['cpt_custom_tax']['name'] ) );
	if ( ! empty( $data['cpt_custom_tax']['label'] ) ) {
		$label = str_replace( '"', '', htmlspecialchars_decode( $data['cpt_custom_tax']['label'] ) );
		$label = htmlspecialchars( stripslashes( $label ), ENT_QUOTES );
	}

	$name = trim( $data['cpt_custom_tax']['name'] );

	$singular_label = ucwords( str_replace( '_', ' ', $data['cpt_custom_tax']['name'] ) );
	if ( ! empty( $data['cpt_custom_tax']['singular_label'] ) ) {
		$singular_label = str_replace( '"', '', htmlspecialchars_decode( $data['cpt_custom_tax']['singular_label'] ) );
		$singular_label = htmlspecialchars( stripslashes( $singular_label ) );
	}
	$description = stripslashes_deep( $data['cpt_custom_tax']['description'] );
	$query_var_slug = trim( $data['cpt_custom_tax']['query_var_slug'] );
	$rewrite_slug = trim( $data['cpt_custom_tax']['rewrite_slug'] );
	$rest_base = trim( $data['cpt_custom_tax']['rest_base'] );
	$show_quickpanel_bulk = ( ! empty( $data['cpt_custom_tax']['show_in_quick_edit'] ) ) ? disp_boolean( $data['cpt_custom_tax']['show_in_quick_edit'] ) : '';

	$taxonomies[ $data['cpt_custom_tax']['name'] ] = array(
		'name'                 => $name,
		'label'                => $label,
		'singular_label'       => $singular_label,
		'description'          => $description,
		'public'               => disp_boolean( $data['cpt_custom_tax']['public'] ),
		'hierarchical'         => disp_boolean( $data['cpt_custom_tax']['hierarchical'] ),
		'show_ui'              => disp_boolean( $data['cpt_custom_tax']['show_ui'] ),
		'show_in_menu'         => disp_boolean( $data['cpt_custom_tax']['show_in_menu'] ),
		'show_in_nav_menus'    => disp_boolean( $data['cpt_custom_tax']['show_in_nav_menus'] ),
		'query_var'            => disp_boolean( $data['cpt_custom_tax']['query_var'] ),
		'query_var_slug'       => $query_var_slug,
		'rewrite'              => disp_boolean( $data['cpt_custom_tax']['rewrite'] ),
		'rewrite_slug'         => $rewrite_slug,
		'rewrite_withfront'    => $data['cpt_custom_tax']['rewrite_withfront'],
		'rewrite_hierarchical' => $data['cpt_custom_tax']['rewrite_hierarchical'],
		'show_admin_column'    => disp_boolean( $data['cpt_custom_tax']['show_admin_column'] ),
		'show_in_rest'         => disp_boolean( $data['cpt_custom_tax']['show_in_rest'] ),
		'show_in_quick_edit'   => $show_quickpanel_bulk,
		'rest_base'            => $rest_base,
		'labels'               => $data['cpt_tax_labels'],
	);

	$taxonomies[ $data['cpt_custom_tax']['name'] ]['object_types'] = $data['cpt_post_types'];

	/**
	 * Filters whether or not 3rd party options were saved successfully within taxonomy add/update.
	 *
	 * @since 1.3.0
	 *
	 * @param bool  $value      Whether or not someone else saved successfully. Default false.
	 * @param array $taxonomies Array of our updated taxonomies data.
	 * @param array $data       Array of submitted taxonomy to update.
	 */
	if ( false === ( $success = apply_filters( 'cptui_taxonomy_update_save', false, $taxonomies, $data ) ) ) {
		$success = update_option( 'cptui_taxonomies', $taxonomies );
	}

	/**
	 * Fires after a taxonomy is updated to our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of taxonomy data that was updated.
	 */
	do_action( 'cptui_after_update_taxonomy', $data );

	// Used to help flush rewrite rules on init.
	set_transient( 'cptui_flush_rewrite_rules', 'true', 5 * 60 );

	if ( isset( $success ) ) {
		if ( 'new' == $data['cpt_tax_status'] ) {
			return 'add_success';
		}
	}

	return 'update_success';
}

/**
 * Return an array of names that users should not or can not use for taxonomy names.
 *
 * @since 1.3.0
 *
 * @return array $value Array of names that are recommended against.
 */
function cptui_reserved_taxonomies() {

	$reserved = array(
		'action',
		'attachment',
		'attachment_id',
		'author',
		'author_name',
		'calendar',
		'cat',
		'category',
		'category__and',
		'category__in',
		'category__not_in',
		'category_name',
		'comments_per_page',
		'comments_popup',
		'customize_messenger_channel',
		'customized',
		'cpage',
		'day',
		'debug',
		'error',
		'exact',
		'feed',
		'fields',
		'hour',
		'include',
		'link_category',
		'm',
		'minute',
		'monthnum',
		'more',
		'name',
		'nav_menu',
		'nonce',
		'nopaging',
		'offset',
		'order',
		'orderby',
		'p',
		'page',
		'page_id',
		'paged',
		'pagename',
		'pb',
		'perm',
		'post',
		'post__in',
		'post__not_in',
		'post_format',
		'post_mime_type',
		'post_status',
		'post_tag',
		'post_type',
		'posts',
		'posts_per_archive_page',
		'posts_per_page',
		'preview',
		'robots',
		's',
		'search',
		'second',
		'sentence',
		'showposts',
		'static',
		'subpost',
		'subpost_id',
		'tag',
		'tag__and',
		'tag__in',
		'tag__not_in',
		'tag_id',
		'tag_slug__and',
		'tag_slug__in',
		'taxonomy',
		'tb',
		'term',
		'theme',
		'type',
		'w',
		'withcomments',
		'withoutcomments',
		'year',
		'output',
	);

	/**
	 * Filters the list of reserved post types to check against.
	 * 3rd party plugin authors could use this to prevent duplicate post types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value Array of post type slugs to forbid.
	 */
	$custom_reserved = apply_filters( 'cptui_reserved_taxonomies', array() );

	if ( is_string( $custom_reserved ) && ! empty( $custom_reserved ) ) {
		$reserved[] = $custom_reserved;
	} else if ( is_array( $custom_reserved ) && ! empty( $custom_reserved ) ) {
		foreach ( $custom_reserved as $slug ) {
			$reserved[] = $slug;
		}
	}

	return $reserved;
}

/**
 * Convert taxonomies.
 *
 * @since 1.3.0
 *
 * @internal
 *
 * @param string $original_slug Original taxonomy slug. Optional. Default empty string.
 * @param string $new_slug      New taxonomy slug. Optional. Default empty string.
 */
function cptui_convert_taxonomy_terms( $original_slug = '', $new_slug = '' ) {
	global $wpdb;

	$args = array(
		'taxonomy'   => $original_slug,
		'hide_empty' => false,
		'fields'     => 'ids',
	);

	$term_ids = get_terms( $args );

	if ( is_int( $term_ids ) ) {
		$term_ids = (array) $term_ids;
	}

	if ( is_array( $term_ids ) && ! empty( $term_ids ) ) {
		$term_ids = implode( ',', $term_ids );

		$query = "UPDATE `{$wpdb->term_taxonomy}` SET `taxonomy` = %s WHERE `taxonomy` = %s AND `term_id` IN ( {$term_ids} )";

		$wpdb->query(
			$wpdb->prepare( $query, $new_slug, $original_slug )
		);
	}
	cptui_delete_taxonomy( $original_slug );
}

/**
 * Checks if we are trying to register an already registered taxonomy slug.
 *
 * @since 1.3.0
 *
 * @param bool   $slug_exists   Whether or not the post type slug exists. Optional. Default false.
 * @param string $taxonomy_slug The post type slug being saved. Optional. Default empty string.
 * @param array  $taxonomies    Array of CPTUI-registered post types. Optional.
 *
 * @return bool
 */
function cptui_check_existing_taxonomy_slugs( $slug_exists = false, $taxonomy_slug = '', $taxonomies = array() ) {

	// If true, then we'll already have a conflict, let's not re-process.
	if ( true === $slug_exists ) {
		return $slug_exists;
	}

	// Check if CPTUI has already registered this slug.
	if ( array_key_exists( strtolower( $taxonomy_slug ), $taxonomies ) ) {
		return true;
	}

	// Check if we're registering a reserved post type slug.
	if ( in_array( $taxonomy_slug, cptui_reserved_taxonomies() ) ) {
		return true;
	}

	// Check if other plugins have registered this same slug.
	$registered_taxonomies = get_post_types( array( '_builtin' => false, 'public' => false ) );
	if ( in_array( $taxonomy_slug, $registered_taxonomies ) ) {
		return true;
	}

	// If we're this far, it's false.
	return $slug_exists;
}
add_filter( 'cptui_taxonomy_slug_exists', 'cptui_check_existing_taxonomy_slugs', 10, 3 );

/**
 * Handle the save and deletion of taxonomy data.
 *
 * @since 1.4.0
 */
function cptui_process_taxonomy() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( ! is_admin() ) {
		return;
	}

	if ( ! empty( $_GET ) && isset( $_GET['page'] ) && 'cptui_manage_taxonomies' !== $_GET['page'] ) {
		return;
	}

	if ( ! empty( $_POST ) ) {
		$result = '';
		if ( isset( $_POST['cpt_submit'] ) ) {
			check_admin_referer( 'cptui_addedit_taxonomy_nonce_action', 'cptui_addedit_taxonomy_nonce_field' );
			$result = cptui_update_taxonomy( $_POST );
		} elseif ( isset( $_POST['cpt_delete'] ) ) {
			check_admin_referer( 'cptui_addedit_taxonomy_nonce_action', 'cptui_addedit_taxonomy_nonce_field' );
			$result = cptui_delete_taxonomy( $_POST );
			add_filter( 'cptui_taxonomy_deleted', '__return_true' );
		}
		if ( $result ) {
			add_action( 'admin_notices', "cptui_{$result}_admin_notice" );
		}
	}
}
add_action( 'init', 'cptui_process_taxonomy', 8 );

/**
 * Handle the conversion of taxonomy terms.
 *
 * This function came to be because we needed to convert AFTER registration.
 *
 * @since 1.4.3
 */
function cptui_do_convert_taxonomy_terms() {

	/**
	 * Whether or not to convert taxonomy terms.
	 *
	 * @since 1.4.3
	 *
	 * @param bool $value Whether or not to convert.
	 */
	if ( apply_filters( 'cptui_convert_taxonomy_terms', false ) ) {
		cptui_convert_taxonomy_terms( sanitize_text_field( $_POST['tax_original'] ), sanitize_text_field( $_POST['cpt_custom_tax']['name'] ) );
	}
}
add_action( 'init', 'cptui_do_convert_taxonomy_terms' );

/**
 * Handles slug_exist checks for cases of editing an existing taxonomy.
 *
 * @since 1.5.3
 *
 * @param bool   $slug_exists   Current status for exist checks.
 * @param string $taxonomy_slug Taxonomy slug being processed.
 * @param array  $taxonomies    CPTUI taxonomies.
 * @return bool
 */
function cptui_updated_taxonomy_slug_exists( $slug_exists, $taxonomy_slug = '', $taxonomies = array() ) {
	if (
		( ! empty( $_POST['cpt_tax_status'] ) && 'edit' == $_POST['cpt_tax_status'] ) &&
		! in_array( $taxonomy_slug, cptui_reserved_taxonomies() ) &&
		( ! empty( $_POST['tax_original'] ) && $taxonomy_slug === $_POST['tax_original'] )
	)
		{
		$slug_exists = false;
	}
	return $slug_exists;
}
add_filter( 'cptui_taxonomy_slug_exists', 'cptui_updated_taxonomy_slug_exists', 11, 3 );
