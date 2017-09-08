<?php
/**
 * Custom Post Type UI Post Type Settings.
 *
 * @package CPTUI
 * @subpackage PostTypes
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
function cptui_post_type_enqueue_scripts() {

	$current_screen = get_current_screen();

	if ( ! is_object( $current_screen ) || 'cpt-ui_page_cptui_manage_post_types' !== $current_screen->base ) {
		return;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'cptui' );
	wp_localize_script( 'cptui', 'cptui_type_data',
		array(
			'confirm' => esc_html__( 'Are you sure you want to delete this? Deleting will NOT remove created content.', 'custom-post-type-ui' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'cptui_post_type_enqueue_scripts' );

/**
 * Register our tabs for the Post Type screen.
 *
 * @since 1.3.0
 *
 * @internal
 *
 * @param array  $tabs         Array of tabs to display. Optional.
 * @param string $current_page Current page being shown. Optional. Default empty string.
 * @return array Amended array of tabs to show.
 */
function cptui_post_type_tabs( $tabs = array(), $current_page = '' ) {

	if ( 'post_types' == $current_page ) {
		$post_types = cptui_get_post_type_data();
		$classes    = array( 'nav-tab' );

		$tabs['page_title'] = get_admin_page_title();
		$tabs['tabs'] = array();
		// Start out with our basic "Add new" tab.
		$tabs['tabs']['add'] = array(
			'text'          => __( 'Add New Post Type', 'custom-post-type-ui' ),
			'classes'       => $classes,
			'url'           => cptui_admin_url( 'admin.php?page=cptui_manage_' . $current_page ),
			'aria-selected' => 'false',
		);

		$action = cptui_get_current_action();
		if ( empty( $action ) ) {
			$tabs['tabs']['add']['classes'][] = 'nav-tab-active';
			$tabs['tabs']['add']['aria-selected'] = 'true';
		}

		if ( ! empty( $post_types ) ) {

			if ( ! empty( $action ) ) {
				$classes[] = 'nav-tab-active';
			}
			$tabs['tabs']['edit'] = array(
				'text'          => __( 'Edit Post Types', 'custom-post-type-ui' ),
				'classes'       => $classes,
				'url'           => esc_url( add_query_arg( array( 'action' => 'edit' ), cptui_admin_url( 'admin.php?page=cptui_manage_' . $current_page ) ) ),
				'aria-selected' => ( ! empty( $action ) ) ? 'true' : 'false',
			);

			$tabs['tabs']['view'] = array(
				'text'          => __( 'View Post Types', 'custom-post-type-ui' ),
				'classes'       => array( 'nav-tab' ), // Prevent notices.
				'url'           => esc_url( cptui_admin_url( 'admin.php?page=cptui_listings#post-types' ) ),
				'aria-selected' => 'false',
			);

			$tabs['tabs']['export'] = array(
				'text'          => __( 'Import/Export Post Types', 'custom-post-type-ui' ),
				'classes'       => array( 'nav-tab' ), // Prevent notices.
				'url'           => esc_url( cptui_admin_url( 'admin.php?page=cptui_tools' ) ),
				'aria-selected' => 'false',
			);
		}
	}

	return $tabs;
}
add_filter( 'cptui_get_tabs', 'cptui_post_type_tabs', 10, 2 );

/**
 * Create our settings page output.
 *
 * @since 1.0.0
 *
 * @internal
 */
function cptui_manage_post_types() {

	$tab = ( ! empty( $_GET ) && ! empty( $_GET['action'] ) && 'edit' === $_GET['action'] ) ? 'edit' : 'new';
	$tab_class = 'cptui-' . $tab; ?>

	<div class="wrap <?php echo esc_attr( $tab_class ); ?>">

	<?php
	/**
	 * Fires right inside the wrap div for the post type editor screen.
	 *
	 * @since 1.3.0
	 */
	do_action( 'cptui_inside_post_type_wrap' );

	/**
	 * Filters whether or not a post type was deleted.
	 *
	 * @since 1.4.0
	 *
	 * @param bool $value Whether or not post type deleted. Default false.
	 */
	$post_type_deleted = apply_filters( 'cptui_post_type_deleted', false );

	cptui_settings_tab_menu();

	/**
	 * Fires below the output for the tab menu on the post type add/edit screen.
	 *
	 * @since 1.3.0
	 */
	do_action( 'cptui_below_post_type_tab_menu' );

	if ( 'edit' == $tab ) {

		$post_types = cptui_get_post_type_data();

		$selected_post_type = cptui_get_current_post_type( $post_type_deleted );

		if ( $selected_post_type ) {
			if ( array_key_exists( $selected_post_type, $post_types ) ) {
				$current = $post_types[ $selected_post_type ];
			}
		}
	}

	$ui = new cptui_admin_ui();

	// Will only be set if we're already on the edit screen.
	if ( ! empty( $post_types ) ) { ?>
		<form id="cptui_select_post_type" method="post" action="<?php echo esc_url( cptui_get_post_form_action( $ui ) ); ?>">
			<label for="post_type"><?php esc_html_e( 'Select: ', 'custom-post-type-ui' ); ?></label>
			<?php
			cptui_post_types_dropdown( $post_types );

			/**
			 * Filters the text value to use on the select post type button.
			 *
			 * @since 1.0.0
			 *
			 * @param string $value Text to use for the button.
			 */
			?>
			<input type="submit" class="button-secondary" name="cptui_select_post_type_submit" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_select', __( 'Select', 'custom-post-type-ui' ) ) ); ?>" />
		</form>
	<?php

		/**
		 * Fires below the post type select input.
		 *
		 * @since 1.1.0
		 *
		 * @param string $value Current post type selected.
		 */
		do_action( 'cptui_below_post_type_select', $current['name'] );
	} ?>

	<form class="posttypesui" method="post" action="<?php echo esc_url( cptui_get_post_form_action( $ui ) ); ?>">
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
						echo $ui->get_label( 'name', esc_html__( 'Post Type Slug', 'custom-post-type-ui' ) );
						echo $ui->get_required_span();

						if ( 'edit' == $tab ) {
							echo '<p id="slugchanged" class="hidemessage">' . esc_html__( 'Slug has changed', 'custom_post_type_ui' ) . '</p>';
						}

						echo $ui->get_th_end() . $ui->get_td_start();

						echo $ui->get_text_input( array(
							'namearray'     => 'cpt_custom_post_type',
							'name'          => 'name',
							'textvalue'     => ( isset( $current['name'] ) ) ? esc_attr( $current['name'] ) : '',
							'maxlength'     => '20',
							'helptext'      => esc_html__( 'The post type name/slug. Used for various queries for post type content.', 'custom-post-type-ui' ),
							'required'      => true,
							'placeholder'   => false,
							'wrap'          => false,
						) );
						echo '<p class="cptui-slug-details">';
							esc_html_e( 'Slugs should only contain alphanumeric, latin characters. Underscores should be used in place of spaces. Set "Custom Rewrite Slug" field to make slug use dashes for URLs.', 'custom-post-type-ui' );
						echo '</p>';

						if ( 'edit' == $tab ) {
							echo '<p>';
							esc_html_e( 'DO NOT EDIT the post type slug unless also planning to migrate posts. Changing the slug registers a new post type entry.', 'custom-post-type-ui' );
							echo '</p>';

							echo '<div class="cptui-spacer">';
							echo $ui->get_check_input( array(
								'checkvalue' => 'update_post_types',
								'checked'    => 'false',
								'name'       => 'update_post_types',
								'namearray'  => 'update_post_types',
								'labeltext'  => esc_html__( 'Migrate posts to newly renamed post type?', 'custom-post-type-ui' ),
								'helptext'   => false,
								'default'    => false,
								'wrap'       => false,
							) );
							echo '</div>';
						}

						echo $ui->get_td_end(); echo $ui->get_tr_end();

						echo $ui->get_text_input( array(
							'namearray' => 'cpt_custom_post_type',
							'name'      => 'label',
							'textvalue' => ( isset( $current['label'] ) ) ? esc_attr( $current['label'] ) : '',
							'labeltext' => esc_html__( 'Plural Label', 'custom-post-type-ui' ),
							'aftertext' => esc_html__( '(e.g. Movies)', 'custom-post-type-ui' ),
							'helptext'  => esc_html__( 'Used for the post type admin menu item.', 'custom-post-type-ui' ),
							'required'  => true,
						) );

						echo $ui->get_text_input( array(
							'namearray' => 'cpt_custom_post_type',
							'name'      => 'singular_label',
							'textvalue' => ( isset( $current['singular_label'] ) ) ? esc_attr( $current['singular_label'] ) : '',
							'labeltext' => esc_html__( 'Singular Label', 'custom-post-type-ui' ),
							'aftertext' => esc_html__( '(e.g. Movie)', 'custom-post-type-ui' ),
							'helptext'  => esc_html__( 'Used when a singular label is needed.', 'custom-post-type-ui' ),
							'required'  => true,
						) );
					?>
				</table>
						<p class="submit">
						<?php wp_nonce_field( 'cptui_addedit_post_type_nonce_action', 'cptui_addedit_post_type_nonce_field' );
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
						<input type="submit" class="button-primary" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_edit', __( 'Save Post Type', 'custom-post-type-ui' ) ) ); ?>" />
						<?php

						/**
						 * Filters the text value to use on the button when deleting.
						 *
						 * @since 1.0.0
						 *
						 * @param string $value Text to use for the button.
						 */
						?>
						<input type="submit" class="button-secondary" name="cpt_delete" id="cpt_submit_delete" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_delete', __( 'Delete Post Type', 'custom-post-type-ui' ) ) ); ?>" />
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
						<input type="submit" class="button-primary" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_add', __( 'Add Post Type', 'custom-post-type-ui' ) ) ); ?>" />
						<?php }

						if ( ! empty( $current ) ) { ?>
							<input type="hidden" name="cpt_original" id="cpt_original" value="<?php esc_attr_e( $current['name'] ); ?>" />
						<?php }

						// Used to check and see if we should prevent duplicate slugs. ?>
						<input type="hidden" name="cpt_type_status" id="cpt_type_status" value="<?php esc_attr_e( $tab ); ?>" />
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
									'namearray' => 'cpt_custom_post_type',
									'name'      => 'description',
									'rows'      => '4',
									'cols'      => '40',
									'textvalue' => ( isset( $current['description'] ) ) ? esc_textarea( $current['description'] ) : '',
									'labeltext' => esc_html__( 'Post Type Description', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Perhaps describe what your custom post type is used for?', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Menu Name', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Custom admin menu name for your custom post type.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'menu_name',
									'textvalue' => ( isset( $current['labels']['menu_name'] ) ) ? esc_attr( $current['labels']['menu_name'] ) : '',
									'aftertext' => esc_html__( '(e.g. My Movies)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'All Items', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used in the post type admin submenu.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'all_items',
									'textvalue' => ( isset( $current['labels']['all_items'] ) ) ? esc_attr( $current['labels']['all_items'] ) : '',
									'aftertext' => esc_html__( '(e.g. All Movies)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Add New', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used in the post type admin submenu.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'add_new',
									'textvalue' => ( isset( $current['labels']['add_new'] ) ) ? esc_attr( $current['labels']['add_new'] ) : '',
									'aftertext' => esc_html__( '(e.g. Add New)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Add New Item', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used at the top of the post editor screen for a new post type post.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'add_new_item',
									'textvalue' => ( isset( $current['labels']['add_new_item'] ) ) ? esc_attr( $current['labels']['add_new_item'] ) : '',
									'aftertext' => esc_html__( '(e.g. Add New Movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Edit Item', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used at the top of the post editor screen for an existing post type post.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'edit_item',
									'textvalue' => ( isset( $current['labels']['edit_item'] ) ) ? esc_attr( $current['labels']['edit_item'] ) : '',
									'aftertext' => esc_html__( '(e.g. Edit Movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'New Item', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'new_item',
									'textvalue' => ( isset( $current['labels']['new_item'] ) ) ? esc_attr( $current['labels']['new_item'] ) : '',
									'aftertext' => esc_html__( '(e.g. New Movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'View Item', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used in the admin bar when viewing editor screen for a published post in the post type.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'view_item',
									'textvalue' => ( isset( $current['labels']['view_item'] ) ) ? esc_attr( $current['labels']['view_item'] ) : '',
									'aftertext' => esc_html__( '(e.g. View Movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'View Items', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used in the admin bar when viewing editor screen for a published post in the post type.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'view_items',
									'textvalue' => ( isset( $current['labels']['view_items'] ) ) ? esc_attr( $current['labels']['view_items'] ) : '',
									'aftertext' => esc_html__( '(e.g. View Movies)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Search Item', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used as the text for the search button on post type list screen.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'search_items',
									'textvalue' => ( isset( $current['labels']['search_items'] ) ) ? esc_attr( $current['labels']['search_items'] ) : '',
									'aftertext' => esc_html__( '(e.g. Search Movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Not Found', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used when there are no posts to display on the post type list screen.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'not_found',
									'textvalue' => ( isset( $current['labels']['not_found'] ) ) ? esc_attr( $current['labels']['not_found'] ) : '',
									'aftertext' => esc_html__( '(e.g. No Movies found)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Not Found in Trash', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used when there are no posts to display on the post type list trash screen.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'not_found_in_trash',
									'textvalue' => ( isset( $current['labels']['not_found_in_trash'] ) ) ? esc_attr( $current['labels']['not_found_in_trash'] ) : '',
									'aftertext' => esc_html__( '(e.g. No Movies found in Trash)', 'custom-post-type-ui' ),
								) );

								// As of 1.4.0, this will register into `parent_item_colon` paramter upon registration and export.
								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Parent', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used for hierarchical types that need a colon.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'parent',
									'textvalue' => ( isset( $current['labels']['parent'] ) ) ? esc_attr( $current['labels']['parent'] ) : '',
									'aftertext' => esc_html__( '(e.g. Parent Movie:)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Featured Image', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used as the "Featured Image" phrase for the post type.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'featured_image',
									'textvalue' => ( isset( $current['labels']['featured_image'] ) ) ? esc_attr( $current['labels']['featured_image'] ) : '',
									'aftertext' => esc_html__( '(e.g. Featured image for this movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Set Featured Image', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used as the "Set featured image" phrase for the post type.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'set_featured_image',
									'textvalue' => ( isset( $current['labels']['set_featured_image'] ) ) ? esc_attr( $current['labels']['set_featured_image'] ) : '',
									'aftertext' => esc_html__( '(e.g. Set featured image for this movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Remove Featured Image', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used as the "Remove featured image" phrase for the post type.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'remove_featured_image',
									'textvalue' => ( isset( $current['labels']['remove_featured_image'] ) ) ? esc_attr( $current['labels']['remove_featured_image'] ) : '',
									'aftertext' => esc_html__( '(e.g. Remove featured image for this movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Use Featured Image', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used as the "Use as featured image" phrase for the post type.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'use_featured_image',
									'textvalue' => ( isset( $current['labels']['use_featured_image'] ) ) ? esc_attr( $current['labels']['use_featured_image'] ) : '',
									'aftertext' => esc_html__( '(e.g. Use as featured image for this movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Archives', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Post type archive label used in nav menus.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'archives',
									'textvalue' => ( isset( $current['labels']['archives'] ) ) ? esc_attr( $current['labels']['archives'] ) : '',
									'aftertext' => esc_html__( '(e.g. Movie archives)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Insert into item', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used as the "Insert into post" or "Insert into page" phrase for the post type.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'insert_into_item',
									'textvalue' => ( isset( $current['labels']['insert_into_item'] ) ) ? esc_attr( $current['labels']['insert_into_item'] ) : '',
									'aftertext' => esc_html__( '(e.g. Insert into movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Uploaded to this Item', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used as the "Uploaded to this post" or "Uploaded to this page" phrase for the post type.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'uploaded_to_this_item',
									'textvalue' => ( isset( $current['labels']['uploaded_to_this_item'] ) ) ? esc_attr( $current['labels']['uploaded_to_this_item'] ) : '',
									'aftertext' => esc_html__( '(e.g. Uploaded to this movie)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Filter Items List', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Screen reader text for the filter links heading on the post type listing screen.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'filter_items_list',
									'textvalue' => ( isset( $current['labels']['filter_items_list'] ) ) ? esc_attr( $current['labels']['filter_items_list'] ) : '',
									'aftertext' => esc_html__( '(e.g. Filter movies list)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Items List Navigation', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Screen reader text for the pagination heading on the post type listing screen.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'items_list_navigation',
									'textvalue' => ( isset( $current['labels']['items_list_navigation'] ) ) ? esc_attr( $current['labels']['items_list_navigation'] ) : '',
									'aftertext' => esc_html__( '(e.g. Movies list navigation)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Items List', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Screen reader text for the items list heading on the post type listing screen.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'items_list',
									'textvalue' => ( isset( $current['labels']['items_list'] ) ) ? esc_attr( $current['labels']['items_list'] ) : '',
									'aftertext' => esc_html__( '(e.g. Movies list)', 'custom-post-type-ui' ),
								) );

								echo $ui->get_text_input( array(
									'labeltext' => esc_html__( 'Attributes', 'custom-post-type-ui' ),
									'helptext'  => esc_html__( 'Used for the title of the post attributes meta box.', 'custom-post-type-ui' ),
									'namearray' => 'cpt_labels',
									'name'      => 'attributes',
									'textvalue' => ( isset( $current['labels']['attributes'] ) ) ? esc_attr( $current['labels']['attributes'] ) : '',
									'aftertext' => esc_html__( '(e.g. Movies Attributes)', 'custom-post-type-ui' ),
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
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ), 'default' => 'true' ),
								),
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['public'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['public'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'public',
								'labeltext'  => esc_html__( 'Public', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(CPTUI default: true) Whether or not posts of this type should be shown in the admin UI and is publicly queryable.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ), 'default' => 'true' ),
								),
							);
							$selected = ( isset( $current ) && ! empty( $current['publicly_queryable'] ) ) ? disp_boolean( $current['publicly_queryable'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['publicly_queryable'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'publicly_queryable',
								'labeltext'  => esc_html__( 'Publicly Queryable', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Whether or not queries can be performed on the front end as part of parse_request()', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ), 'default' => 'true' ),
								),
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['show_ui'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['show_ui'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'show_ui',
								'labeltext'  => esc_html__( 'Show UI', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Whether or not to generate a default UI for managing this post type.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ), 'default' => 'true' ),
								),
							);
							$selected = ( isset( $current ) && ! empty( $current['show_in_nav_menus'] ) ) ? disp_boolean( $current['show_in_nav_menus'] ) : '';
							$select['selected'] = ( ! empty( $selected ) && ! empty( $current['show_in_nav_menus'] ) ) ? $current['show_in_nav_menus'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'show_in_nav_menus',
								'labeltext'  => esc_html__( 'Show in Nav Menus', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(CPTUI default: true) Whether or not this post type is available for selection in navigation menus.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ), 'default' => 'false' ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ) ),
								),
							);
							$selected = ( isset( $current ) && ! empty( $current['show_in_rest'] ) ) ? disp_boolean( $current['show_in_rest'] ) : '';
							$select['selected'] = ( ! empty( $selected ) && ! empty( $current['show_in_rest'] ) ) ? $current['show_in_rest'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'show_in_rest',
								'labeltext'  => esc_html__( 'Show in REST API', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: false) Whether or not to show this post type data in the WP REST API.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'rest_base',
								'labeltext' => esc_html__( 'REST API base slug', 'custom-post-type-ui' ),
								'aftertext' => esc_attr__( 'Slug to use in REST API URLs.', 'custom-post-type-ui' ),
								'textvalue' => ( isset( $current['rest_base'] ) ) ? esc_attr( $current['rest_base'] ) : '',
							) );

							echo $ui->get_tr_start() . $ui->get_th_start();
							echo $ui->get_label( 'has_archive', esc_html__( 'Has Archive', 'custom-post-type-ui' ) );
							echo $ui->get_p( esc_html__( 'If left blank, the archive slug will default to the post type slug.', 'custom-post-type-ui' ) );
							echo $ui->get_th_end() . $ui->get_td_start();

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ), 'default' => 'true' ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ) ),
								),
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['has_archive'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['has_archive'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'has_archive',
								'aftertext'  => esc_html__( '(default: false) Whether or not the post type will have a post type archive URL.', 'custom-post-type-ui' ),
								'selections' => $select,
								'wrap'       => false,
							) );

							echo '<br/>';

							echo $ui->get_text_input( array(
								'namearray'      => 'cpt_custom_post_type',
								'name'           => 'has_archive_string',
								'textvalue'      => ( isset( $current['has_archive_string'] ) ) ? esc_attr( $current['has_archive_string'] ) : '',
								'aftertext'      => esc_attr__( 'Slug to be used for archive URL.', 'custom-post-type-ui' ),
								'helptext_after' => true,
								'wrap'           => false,
							) );
							echo $ui->get_td_end() . $ui->get_tr_end();

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ), 'default' => 'true' ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ) ),
								),
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['exclude_from_search'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['exclude_from_search'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'exclude_from_search',
								'labeltext'  => esc_html__( 'Exclude From Search', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: false) Whether or not to exclude posts with this post type from front end search results.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'capability_type',
								'textvalue' => ( isset( $current['capability_type'] ) ) ? esc_attr( $current['capability_type'] ) : 'post',
								'labeltext' => esc_html__( 'Capability Type', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'The post type to use for checking read, edit, and delete capabilities.', 'custom-post-type-ui' ),
							) );

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ), 'default' => 'true' ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ) ),
								),
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['hierarchical'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['hierarchical'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'hierarchical',
								'labeltext'  => esc_html__( 'Hierarchical', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: false) Whether or not the post type can have parent-child relationships.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ), 'default' => 'true' ),
								),
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['rewrite'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['rewrite'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'rewrite',
								'labeltext'  => esc_html__( 'Rewrite', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Whether or not WordPress should use rewrites for this post type.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'rewrite_slug',
								'textvalue' => ( isset( $current['rewrite_slug'] ) ) ? esc_attr( $current['rewrite_slug'] ) : '',
								'labeltext' => esc_html__( 'Custom Rewrite Slug', 'custom-post-type-ui' ),
								'aftertext' => esc_attr__( '(default: post type slug)', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom post type slug to use instead of the default.', 'custom-post-type-ui' ),
							) );

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ), 'default' => 'true' ),
								),
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['rewrite_withfront'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['rewrite_withfront'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'rewrite_withfront',
								'labeltext'  => esc_html__( 'With Front', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Should the permastruct be prepended with the front base.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ), 'default' => 'true' ),
								),
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['query_var'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['query_var'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'query_var',
								'labeltext'  => esc_html__( 'Query Var', 'custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(default: true) Sets the query_var key for this post type.', 'custom-post-type-ui' ),
								'selections' => $select,
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'query_var_slug',
								'textvalue' => ( isset( $current['query_var_slug'] ) ) ? esc_attr( $current['query_var_slug'] ) : '',
								'labeltext' => esc_html__( 'Custom Query Var Slug', 'custom-post-type-ui' ),
								'aftertext' => esc_attr__( '(default: post type slug) Query var needs to be true to use.', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Custom query var slug to use instead of the default.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_tr_start() . $ui->get_th_start();
							echo $ui->get_label( 'menu_position', esc_html__( 'Menu Position', 'custom-post-type-ui' ) );
							echo $ui->get_p(
								sprintf(
									esc_html__(
										'See %s in the "menu_position" section. Range of 5-100',
										'custom-post-type-ui'
									),
									sprintf(
										'<a href="http://codex.wordpress.org/Function_Reference/register_post_type#Parameters" target="_blank">%s</a>',
										esc_html__( 'Available options', 'custom-post-type-ui' )
									)
								)
							);

							echo $ui->get_th_end() . $ui->get_td_start();
							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'menu_position',
								'textvalue' => ( isset( $current['menu_position'] ) ) ? esc_attr( $current['menu_position'] ) : '',
								'helptext'  => esc_html__( 'The position in the menu order the post type should appear. show_in_menu must be true.', 'custom-post-type-ui' ),
								'wrap'      => false,
							) );
							echo $ui->get_td_end() . $ui->get_tr_end();

							echo $ui->get_tr_start() . $ui->get_th_start();
							echo $ui->get_label( 'show_in_menu', esc_html__( 'Show in Menu', 'custom-post-type-ui' ) );
							echo $ui->get_p( esc_html__( '"Show UI" must be "true". If an existing top level page such as "tools.php" is indicated for second input, post type will be sub menu of that.', 'custom-post-type-ui' ) );
							echo $ui->get_th_end() . $ui->get_td_start();

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'custom-post-type-ui' ), 'default' => 'true' ),
								),
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['show_in_menu'] ) : '';
							$select['selected'] = ( ! empty( $selected ) ) ? $current['show_in_menu'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'show_in_menu',
								'aftertext'  => esc_html__( '(default: true) Whether or not to show the post type in the admin menu and where to show that menu.', 'custom-post-type-ui' ),
								'selections' => $select,
								'wrap'       => false,
							) );

							echo '<br/>';

							echo $ui->get_text_input( array(
								'namearray'      => 'cpt_custom_post_type',
								'name'           => 'show_in_menu_string',
								'textvalue'      => ( isset( $current['show_in_menu_string'] ) ) ? esc_attr( $current['show_in_menu_string'] ) : '',
								'helptext'       => esc_attr__( 'The top-level admin menu page file name for which the post type should be in the sub menu of.', 'custom-post-type-ui' ),
								'helptext_after' => true,
								'wrap'           => false,
							) );
							echo $ui->get_td_end() . $ui->get_tr_end();

							echo $ui->get_tr_start() . $ui->get_th_start() . '<label for="menu_icon">' . __( 'Menu Icon', 'custom-post-type-ui' ) . '</label>' . $ui->get_th_end() . $ui->get_td_start();
							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'menu_icon',
								'textvalue' => ( isset( $current['menu_icon'] ) ) ? esc_attr( $current['menu_icon'] ) : '',
								'aftertext' => esc_attr__( '(Full URL for icon or Dashicon class)', 'custom-post-type-ui' ),
								'helptext'  => esc_html__( 'Image URL or Dashicon class name to use for icon. Custom image should be 20px by 20px.', 'custom-post-type-ui' ),
								'wrap'      => false,
							) );

							echo '<div class="cptui-spacer">';

							echo $ui->get_button( array(
								'id'      => 'cptui_choose_icon',
								'textvalue' => esc_attr__( 'Choose image icon', 'custom-post-type-ui' ),
							) );
							echo '</div>';

							echo $ui->get_td_end() . $ui->get_tr_end();

							echo $ui->get_tr_start() . $ui->get_th_start() . esc_html__( 'Supports', 'custom-post-type-ui' );

							echo $ui->get_p( esc_html__( 'Add support for various available post editor features on the right. A checked value means the post type means the feature is supported.', 'custom_post_type_ui' ) );

							echo $ui->get_p( esc_html__( 'Use the "None" option to explicitly set "supports" to false.', 'custom-post-type-ui' ) );

							echo $ui->get_th_end() . $ui->get_td_start() . $ui->get_fieldset_start();

							$title_checked = ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'title', $current['supports'] ) ) ? 'true' : 'false';
							if ( 'new' == $tab ) {
								$title_checked = 'true';
							}
							echo $ui->get_check_input( array(
								'checkvalue' => 'title',
								'checked'    => $title_checked,
								'name'       => 'title',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'title',
								'labeltext'  => esc_html__( 'Title', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							$editor_checked = ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'editor', $current['supports'] ) ) ? 'true' : 'false';
							if ( 'new' == $tab ) {
								$editor_checked = 'true';
							}
							echo $ui->get_check_input( array(
								'checkvalue' => 'editor',
								'checked'    => $editor_checked,
								'name'       => 'editor',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'editor',
								'labeltext'  => esc_html__( 'Editor', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							$thumb_checked = ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'thumbnail', $current['supports'] ) ) ? 'true' : 'false';
							if ( 'new' == $tab ) {
								$thumb_checked = 'true';
							}
							echo $ui->get_check_input( array(
								'checkvalue' => 'thumbnail',
								'checked'    => $thumb_checked,
								'name'       => 'thumbnail',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'thumbnail',
								'labeltext'  => esc_html__( 'Featured Image', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							echo $ui->get_check_input( array(
								'checkvalue' => 'excerpt',
								'checked'    => ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'excerpt', $current['supports'] ) ) ? 'true' : 'false',
								'name'       => 'excerpts',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'excerpt',
								'labeltext'  => esc_html__( 'Excerpt', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							echo $ui->get_check_input( array(
								'checkvalue' => 'trackbacks',
								'checked'    => ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'trackbacks', $current['supports'] ) ) ? 'true' : 'false',
								'name'       => 'trackbacks',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'trackbacks',
								'labeltext'  => esc_html__( 'Trackbacks', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							echo $ui->get_check_input( array(
								'checkvalue' => 'custom-fields',
								'checked'    => ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'custom-fields', $current['supports'] ) ) ? 'true' : 'false',
								'name'       => 'custom-fields',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'custom-fields',
								'labeltext'  => esc_html__( 'Custom Fields', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							echo $ui->get_check_input( array(
								'checkvalue' => 'comments',
								'checked'    => ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'comments', $current['supports'] ) ) ? 'true' : 'false',
								'name'       => 'comments',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'comments',
								'labeltext'  => esc_html__( 'Comments', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							echo $ui->get_check_input( array(
								'checkvalue' => 'revisions',
								'checked'    => ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'revisions', $current['supports'] ) ) ? 'true' : 'false',
								'name'       => 'revisions',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'revisions',
								'labeltext'  => esc_html__( 'Revisions', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							echo $ui->get_check_input( array(
								'checkvalue' => 'author',
								'checked'    => ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'author', $current['supports'] ) ) ? 'true' : 'false',
								'name'       => 'author',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'author',
								'labeltext'  => esc_html__( 'Author', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							echo $ui->get_check_input( array(
								'checkvalue' => 'page-attributes',
								'checked'    => ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'page-attributes', $current['supports'] ) ) ? 'true' : 'false',
								'name'       => 'page-attributes',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'page-attributes',
								'labeltext'  => esc_html__( 'Page Attributes', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							echo $ui->get_check_input( array(
								'checkvalue' => 'post-formats',
								'checked'    => ( ! empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'post-formats', $current['supports'] ) ) ? 'true' : 'false',
								'name'       => 'post-formats',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'post-formats',
								'labeltext'  => esc_html__( 'Post Formats', 'custom-post-type-ui' ),
								'default'    => true,
								'wrap'       => false,
							) );

							echo $ui->get_check_input( array(
								'checkvalue' => 'none',
								'checked'    => ( ! empty( $current['supports'] ) && ( is_array( $current['supports'] ) && in_array( 'none', $current['supports'] ) ) ) ? 'true' : 'false',
								'name'       => 'none',
								'namearray'  => 'cpt_supports',
								'textvalue'  => 'none',
								'labeltext'  => esc_html__( 'None', 'custom-post-type-ui' ),
								'default'    => false,
								'wrap'       => false,
							) );

							echo $ui->get_fieldset_end() . $ui->get_td_end() . $ui->get_tr_end();

							echo $ui->get_tr_start() . $ui->get_th_start() . '<label for="custom_supports">' . esc_html__( 'Custom "Supports"', 'custom-post-type-ui' ) . '</label>';
							echo $ui->get_p( sprintf( esc_html__( 'Use this input to register custom "supports" values, separated by commas. Learn about this at %s', 'custom-post-type-ui' ), '<a href="http://docs.pluginize.com/article/28-third-party-support-upon-registration" target="_blank">' . esc_html__( 'Custom "Supports"', 'custom-post-type-ui' ) . '</a>' ) );
							echo $ui->get_th_end() . $ui->get_td_start();
							echo $ui->get_text_input( array(
								'namearray'      => 'cpt_custom_post_type',
								'name'           => 'custom_supports',
								'textvalue'      => ( isset( $current['custom_supports'] ) ) ? esc_attr( $current['custom_supports'] ) : '',
								'helptext'       => esc_attr__( 'Provide custom support slugs here.', 'custom-post-type-ui' ),
								'helptext_after' => true,
								'wrap'           => false,
							) );
							echo $ui->get_td_end() . $ui->get_tr_end();

							echo $ui->get_tr_start() . $ui->get_th_start() . esc_html__( 'Built-in Taxonomies', 'custom-post-type-ui' );

							echo $ui->get_p( esc_html__( 'Add support for available registered taxonomies.', 'custom-post-type-ui' ) );

							echo $ui->get_th_end() . $ui->get_td_start() . $ui->get_fieldset_start();

							/**
							 * Filters the arguments for taxonomies to list for post type association.
							 *
							 * @since 1.0.0
							 *
							 * @param array $value Array of default arguments.
							 */
							$args = apply_filters( 'cptui_attach_taxonomies_to_post_type', array( 'public' => true ) );

							// If they don't return an array, fall back to the original default. Don't need to check for empty, because empty array is default for $args param in get_post_types anyway.
							if ( ! is_array( $args ) ) {
								$args = array( 'public' => true );
							}

							$add_taxes = get_taxonomies( $args, 'objects' );
							unset( $add_taxes['nav_menu'] ); unset( $add_taxes['post_format'] );
							foreach ( $add_taxes as $add_tax ) {

								$core_label = ( in_array( $add_tax->name, array( 'category', 'post_tag' ) ) ) ? __( '(WP Core)', 'custom-post-type-ui' ) : '';
								echo $ui->get_check_input( array(
									'checkvalue' => $add_tax->name,
									'checked'    => ( ! empty( $current['taxonomies'] ) && is_array( $current['taxonomies'] ) && in_array( $add_tax->name, $current['taxonomies'] ) ) ? 'true' : 'false',
									'name'       => $add_tax->name,
									'namearray'  => 'cpt_addon_taxes',
									'textvalue'  => $add_tax->name,
									'labeltext'  => $add_tax->label . ' ' . $core_label,
									'helptext'   => sprintf( esc_attr__( 'Adds %s support', 'custom-post-type-ui' ), $add_tax->label ),
									'wrap'       => false,
								) );
							}
							echo $ui->get_fieldset_end() . $ui->get_td_end() . $ui->get_tr_end();
							?>
						</table>
					</div>
				</div>
			</div>

			<?php
			/**
			 * Fires after the default fieldsets on the post editor screen.
			 *
			 * @since 1.3.0
			 *
			 * @param cptui_admin_ui $ui Admin UI instance.
			 */
			do_action( 'cptui_post_type_after_fieldsets', $ui ); ?>

			<p>
			<?php
				if ( ! empty( $_GET ) && ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
					/**
					 * Filters the text value to use on the button when editing.
					 *
					 * @since 1.0.0
					 *
					 * @param string $value Text to use for the button.
					 */
					?>
					<input type="submit" class="button-primary" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_edit', __( 'Save Post Type', 'custom-post-type-ui' ) ) ); ?>" />
					<?php

					/**
					 * Filters the text value to use on the button when deleting.
					 *
					 * @since 1.0.0
					 *
					 * @param string $value Text to use for the button.
					 */
					?>
					<input type="submit" class="button-secondary" name="cpt_delete" id="cpt_submit_delete" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_delete', __( 'Delete Post Type', 'custom-post-type-ui' ) ) ); ?>" />
			<?php
				} else {

					/**
					 * Filters the text value to use on the button when adding.
					 *
					 * @since 1.0.0
					 *
					 * @param string $value Text to use for the button.
					 */
					?>
					<input type="submit" class="button-primary" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_add', __( 'Add Post Type', 'custom-post-type-ui' ) ) ); ?>" />
			<?php } ?>
			</p>
		</div>
	</form>
	</div><!-- End .wrap -->
<?php
}

/**
 * Construct a dropdown of our post types so users can select which to edit.
 *
 * @since 1.0.0
 *
 * @param array $post_types Array of post types that are registered. Optional.
 */
function cptui_post_types_dropdown( $post_types = array() ) {

	$ui = new cptui_admin_ui();

	if ( ! empty( $post_types ) ) {
		$select = array();
		$select['options'] = array();

		foreach ( $post_types as $type ) {
			$text = ( ! empty( $type['label'] ) ) ? $type['label'] : $type['name'];
			$select['options'][] = array( 'attr' => $type['name'], 'text' => $text );
		}

		$current = cptui_get_current_post_type();

		$select['selected'] = $current;
		echo $ui->get_select_input( array(
			'namearray'     => 'cptui_selected_post_type',
			'name'          => 'post_type',
			'selections'    => $select,
			'wrap'          => false,
		) );
	}
}

/**
 * Get the selected post type from the $_POST global.
 *
 * @since 1.0.0
 *
 * @internal
 *
 * @param bool $post_type_deleted Whether or not a post type was recently deleted. Optional. Default false.
 * @return bool|string $value False on no result, sanitized post type if set.
 */
function cptui_get_current_post_type( $post_type_deleted = false ) {

	$type = false;

	if ( ! empty( $_POST ) ) {
		if ( isset( $_POST['cptui_selected_post_type']['post_type'] ) ) {
			$type = sanitize_text_field( $_POST['cptui_selected_post_type']['post_type'] );
		} else if ( $post_type_deleted ) {
			$post_types = cptui_get_post_type_data();
			$type = key( $post_types );
		} else if ( isset( $_POST['cpt_custom_post_type']['name'] ) ) {
			// Return the submitted value.
			if ( ! in_array( $_POST['cpt_custom_post_type']['name'], cptui_reserved_post_types(), true ) ) {
				$type = sanitize_text_field( $_POST['cpt_custom_post_type']['name'] );
			} else {
				// Return the original value since user tried to submit a reserved term.
				$type = sanitize_text_field( $_POST['cpt_original'] );
			}
		}
	} else if ( ! empty( $_GET ) && isset( $_GET['cptui_post_type'] ) ) {
		$type = sanitize_text_field( $_GET['cptui_post_type'] );
	} else {
		$post_types = cptui_get_post_type_data();
		if ( ! empty( $post_types ) ) {
			// Will return the first array key.
			$type = key( $post_types );
		}
	}

	/**
	 * Filters the current post type to edit.
	 *
	 * @since 1.3.0
	 *
	 * @param string $type Post type slug.
	 */
	return apply_filters( 'cptui_current_post_type', $type );
}

/**
 * Delete our custom post type from the array of post types.
 *
 * @since 1.0.0
 *
 * @internal
 *
 * @param array $data $_POST values. Optional.
 * @return bool|string False on failure, string on success.
 */
function cptui_delete_post_type( $data = array() ) {

	// Pass double data into last function despite matching values.
	if ( is_string( $data ) && cptui_get_post_type_exists( $data, $data ) ) {
		$data = array(
			'cpt_custom_post_type' => array(
				'name' => $data,
			),
		);
	}

	if ( empty( $data['cpt_custom_post_type']['name'] ) ) {
		return cptui_admin_notices( 'error', '', false, __( 'Please provide a post type to delete', 'custom-post-type-ui' ) );
	}

	/**
	 * Fires before a post type is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of post type data we are deleting.
	 */
	do_action( 'cptui_before_delete_post_type', $data );

	$post_types = cptui_get_post_type_data();

	if ( array_key_exists( strtolower( $data['cpt_custom_post_type']['name'] ), $post_types ) ) {

		unset( $post_types[ $data['cpt_custom_post_type']['name'] ] );

		/**
		 * Filters whether or not 3rd party options were saved successfully within post type deletion.
		 *
		 * @since 1.3.0
		 *
		 * @param bool  $value      Whether or not someone else saved successfully. Default false.
		 * @param array $post_types Array of our updated post types data.
		 * @param array $data       Array of submitted post type to update.
		 */
		if ( false === ( $success = apply_filters( 'cptui_post_type_delete_type', false, $post_types, $data ) ) ) {
			$success = update_option( 'cptui_post_types', $post_types );
		}
	}

	/**
	 * Fires after a post type is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of post type data that was deleted.
	 */
	do_action( 'cptui_after_delete_post_type', $data );

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
 * @param array $data Array of post type data to update. Optional.
 * @return bool|string False on failure, string on success.
 */
function cptui_update_post_type( $data = array() ) {

	/**
	 * Fires before a post_type is updated to our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of post_type data we are updating.
	 */
	do_action( 'cptui_before_update_post_type', $data );

	// They need to provide a name.
	if ( empty( $data['cpt_custom_post_type']['name'] ) ) {
		return cptui_admin_notices( 'error', '', false, __( 'Please provide a post type name', 'custom-post-type-ui' ) );
	}

	if ( ! empty( $data['cpt_original'] ) && $data['cpt_original'] != $data['cpt_custom_post_type']['name'] ) {
		if ( ! empty( $data['update_post_types'] ) ) {
			add_filter( 'cptui_convert_post_type_posts', '__return_true' );
		}
	}

	// Clean up $_POST data.
	foreach ( $data as $key => $value ) {
		if ( is_string( $value ) ) {
			$data[ $key ] = sanitize_text_field( $value );
		} else {
			array_map( 'sanitize_text_field', $data[ $key ] );
		}
	}

	// Check if they didn't put quotes in the name or rewrite slug.
	if ( false !== strpos( $data['cpt_custom_post_type']['name'], '\'' ) ||
		 false !== strpos( $data['cpt_custom_post_type']['name'], '\"' ) ||
		 false !== strpos( $data['cpt_custom_post_type']['rewrite_slug'], '\'' ) ||
		 false !== strpos( $data['cpt_custom_post_type']['rewrite_slug'], '\"' ) ) {

		add_filter( 'cptui_custom_error_message', 'cptui_slug_has_quotes' );
		return 'error';
	}

	$post_types = cptui_get_post_type_data();

	/**
	 * Check if we already have a post type of that name.
	 *
	 * @since 1.3.0
	 *
	 * @param bool   $value Assume we have no conflict by default.
	 * @param string $value Post type slug being saved.
	 * @param array  $post_types Array of existing post types from CPTUI.
	 */
	$slug_exists = apply_filters( 'cptui_post_type_slug_exists', false, $data['cpt_custom_post_type']['name'], $post_types );
	if ( true === $slug_exists ) {
		add_filter( 'cptui_custom_error_message', 'cptui_slug_matches_post_type' );
		return 'error';
	}

	if ( 'new' == $data['cpt_type_status'] ) {
		$slug_as_page = cptui_check_page_slugs( $data['cpt_custom_post_type']['name'] );
		if ( true === $slug_as_page ) {
			add_filter( 'cptui_custom_error_message', 'cptui_slug_matches_page' );
			return 'error';
		}
	}

	if ( empty( $data['cpt_addon_taxes'] ) || ! is_array( $data['cpt_addon_taxes'] ) ) {
		$data['cpt_addon_taxes'] = array();
	}

	if ( empty( $data['cpt_supports'] ) || ! is_array( $data['cpt_supports'] ) ) {
		$data['cpt_supports'] = array();
	}

	foreach ( $data['cpt_labels'] as $key => $label ) {
		if ( empty( $label ) ) {
			unset( $data['cpt_labels'][ $key ] );
		}

		$label                      = str_replace( '"', '', htmlspecialchars_decode( $label ) );
		$label                      = htmlspecialchars( $label, ENT_QUOTES );
		$label                      = trim( $label );
		if ( 'parent' === $key ) {
			$data['cpt_labels']['parent_item_colon'] = stripslashes_deep( $label );
		} else {
			$data['cpt_labels'][ $key ] = stripslashes_deep( $label );
		}
	}

	if ( empty( $data['cpt_custom_post_type']['menu_icon'] ) ) {
		$data['cpt_custom_post_type']['menu_icon'] = null;
	}

	$label = ucwords( str_replace( '_', ' ', $data['cpt_custom_post_type']['name'] ) );
	if ( ! empty( $data['cpt_custom_post_type']['label'] ) ) {
		$label = str_replace( '"', '', htmlspecialchars_decode( $data['cpt_custom_post_type']['label'] ) );
		$label = htmlspecialchars( stripslashes( $label ), ENT_QUOTES );
	}

	$singular_label = ucwords( str_replace( '_', ' ', $data['cpt_custom_post_type']['name'] ) );
	if ( ! empty( $data['cpt_custom_post_type']['singular_label'] ) ) {
		$singular_label = str_replace( '"', '', htmlspecialchars_decode( $data['cpt_custom_post_type']['singular_label'] ) );
		$singular_label = htmlspecialchars( stripslashes( $singular_label ), ENT_QUOTES );
	}

	$name                = trim( $data['cpt_custom_post_type']['name'] );
	$description         = stripslashes_deep( $data['cpt_custom_post_type']['description'] );
	$rest_base           = trim( $data['cpt_custom_post_type']['rest_base'] );
	$has_archive_string  = trim( $data['cpt_custom_post_type']['has_archive_string'] );
	$capability_type     = trim( $data['cpt_custom_post_type']['capability_type'] );
	$rewrite_slug        = trim( $data['cpt_custom_post_type']['rewrite_slug'] );
	$query_var_slug      = trim( $data['cpt_custom_post_type']['query_var_slug'] );
	$menu_position       = trim( $data['cpt_custom_post_type']['menu_position'] );
	$show_in_menu_string = trim( $data['cpt_custom_post_type']['show_in_menu_string'] );
	$menu_icon           = trim( $data['cpt_custom_post_type']['menu_icon'] );
	$custom_supports     = trim( $data['cpt_custom_post_type']['custom_supports'] );

	$post_types[ $data['cpt_custom_post_type']['name'] ] = array(
		'name'                => $name,
		'label'               => $label,
		'singular_label'      => $singular_label,
		'description'         => $description,
		'public'              => disp_boolean( $data['cpt_custom_post_type']['public'] ),
		'publicly_queryable'  => disp_boolean( $data['cpt_custom_post_type']['publicly_queryable'] ),
		'show_ui'             => disp_boolean( $data['cpt_custom_post_type']['show_ui'] ),
		'show_in_nav_menus'   => disp_boolean( $data['cpt_custom_post_type']['show_in_nav_menus'] ),
		'show_in_rest'        => disp_boolean( $data['cpt_custom_post_type']['show_in_rest'] ),
		'rest_base'           => $rest_base,
		'has_archive'         => disp_boolean( $data['cpt_custom_post_type']['has_archive'] ),
		'has_archive_string'  => $has_archive_string,
		'exclude_from_search' => disp_boolean( $data['cpt_custom_post_type']['exclude_from_search'] ),
		'capability_type'     => $capability_type,
		'hierarchical'        => disp_boolean( $data['cpt_custom_post_type']['hierarchical'] ),
		'rewrite'             => disp_boolean( $data['cpt_custom_post_type']['rewrite'] ),
		'rewrite_slug'        => $rewrite_slug,
		'rewrite_withfront'   => disp_boolean( $data['cpt_custom_post_type']['rewrite_withfront'] ),
		'query_var'           => disp_boolean( $data['cpt_custom_post_type']['query_var'] ),
		'query_var_slug'      => $query_var_slug,
		'menu_position'       => $menu_position,
		'show_in_menu'        => disp_boolean( $data['cpt_custom_post_type']['show_in_menu'] ),
		'show_in_menu_string' => $show_in_menu_string,
		'menu_icon'           => $menu_icon,
		'supports'            => $data['cpt_supports'],
		'taxonomies'          => $data['cpt_addon_taxes'],
		'labels'              => $data['cpt_labels'],
		'custom_supports'     => $custom_supports,
	);

	/**
	 * Filters whether or not 3rd party options were saved successfully within post type add/update.
	 *
	 * @since 1.3.0
	 *
	 * @param bool  $value      Whether or not someone else saved successfully. Default false.
	 * @param array $post_types Array of our updated post types data.
	 * @param array $data       Array of submitted post type to update.
	 */
	if ( false === ( $success = apply_filters( 'cptui_post_type_update_save', false, $post_types, $data ) ) ) {
		$success = update_option( 'cptui_post_types', $post_types );
	}

	/**
	 * Fires after a post type is updated to our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of post type data that was updated.
	 */
	do_action( 'cptui_after_update_post_type', $data );

	// Used to help flush rewrite rules on init.
	set_transient( 'cptui_flush_rewrite_rules', 'true', 5 * 60 );

	if ( isset( $success ) ) {
		if ( 'new' == $data['cpt_type_status'] ) {
			return 'add_success';
		}
	}
	return 'update_success';
}

/**
 * Return an array of names that users should not or can not use for post type names.
 *
 * @since 1.0.0
 *
 * @return array $value Array of names that are recommended against.
 */
function cptui_reserved_post_types() {

	$reserved = array(
		'post',
		'page',
		'attachment',
		'revision',
		'nav_menu_item',
		'action',
		'order',
		'theme',
		'fields',
	);

	/**
	 * Filters the list of reserved post types to check against.
	 *
	 * 3rd party plugin authors could use this to prevent duplicate post types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value Array of post type slugs to forbid.
	 */
	$custom_reserved = apply_filters( 'cptui_reserved_post_types', array() );

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
 * Converts post type between original and newly renamed.
 *
 * @since 1.1.0
 *
 * @internal
 *
 * @param string $original_slug Original post type slug. Optional. Default empty string.
 * @param string $new_slug      New post type slug. Optional. Default empty string.
 */
function cptui_convert_post_type_posts( $original_slug = '', $new_slug = '' ) {
	$args = array(
		'posts_per_page' => -1,
		'post_type'      => $original_slug,
	);
	$convert = new WP_Query( $args );

	if ( $convert->have_posts() ) :
		while ( $convert->have_posts() ) :
			$convert->the_post();
			set_post_type( get_the_ID(), $new_slug );
		endwhile;
	endif;

	cptui_delete_post_type( $original_slug );
}

/**
 * Checks if we are trying to register an already registered post type slug.
 *
 * @since 1.3.0
 *
 * @param bool   $slug_exists    Whether or not the post type slug exists. Optional. Default false.
 * @param string $post_type_slug The post type slug being saved. Optional. Default empty string.
 * @param array  $post_types     Array of CPTUI-registered post types. Optional.
 * @return bool
 */
function cptui_check_existing_post_type_slugs( $slug_exists = false, $post_type_slug = '', $post_types = array() ) {

	// If true, then we'll already have a conflict, let's not re-process.
	if ( true === $slug_exists ) {
		return $slug_exists;
	}

	// Check if CPTUI has already registered this slug.
	if ( array_key_exists( strtolower( $post_type_slug ), $post_types ) ) {
		return true;
	}

	// Check if we're registering a reserved post type slug.
	if ( in_array( $post_type_slug, cptui_reserved_post_types() ) ) {
		return true;
	}

	// Check if other plugins have registered non-public this same slug.
	$public = get_post_types( array( '_builtin' => false, 'public' => true ) );
	$private = get_post_types( array( '_builtin' => false, 'public' => false ) );
	$registered_post_types = array_merge( $public, $private );
	if ( in_array( $post_type_slug, $registered_post_types ) ) {
		return true;
	}

	// If we're this far, it's false.
	return $slug_exists;
}
add_filter( 'cptui_post_type_slug_exists', 'cptui_check_existing_post_type_slugs', 10, 3 );

/**
 * Checks if the slug matches any existing page slug.
 *
 * @since 1.3.0
 *
 * @param string $post_type_slug The post type slug being saved. Optional. Default empty string.
 * @return bool Whether or not the slug exists.
 */
function cptui_check_page_slugs( $post_type_slug = '' ) {
	$page = get_page_by_path( $post_type_slug );

	if ( is_null( $page ) ) {
		return false;
	}

	if ( is_object( $page ) && ( true === $page instanceof WP_Post ) ) {
		return true;
	}

	return false;
}

/**
 * Handle the save and deletion of post type data.
 *
 * @since 1.4.0
 */
function cptui_process_post_type() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( ! is_admin() ) {
		return;
	}

	if ( ! empty( $_GET ) && isset( $_GET['page'] ) && 'cptui_manage_post_types' !== $_GET['page'] ) {
		return;
	}

	if ( ! empty( $_POST ) ) {
		$result = '';
		if ( isset( $_POST['cpt_submit'] ) ) {
			check_admin_referer( 'cptui_addedit_post_type_nonce_action', 'cptui_addedit_post_type_nonce_field' );
			$result = cptui_update_post_type( $_POST );
		} elseif ( isset( $_POST['cpt_delete'] ) ) {
			check_admin_referer( 'cptui_addedit_post_type_nonce_action', 'cptui_addedit_post_type_nonce_field' );
			$result = cptui_delete_post_type( $_POST );
			add_filter( 'cptui_post_type_deleted', '__return_true' );
		}

		if ( $result ) {
			add_action( 'admin_notices', "cptui_{$result}_admin_notice" );
		}
	}
}
add_action( 'init', 'cptui_process_post_type', 8 );

/**
 * Handle the conversion of post type posts.
 *
 * This function came to be because we needed to convert AFTER registration.
 *
 * @since 1.4.3
 */
function cptui_do_convert_post_type_posts() {

	/**
	 * Whether or not to convert post type posts.
	 *
	 * @since 1.4.3
	 *
	 * @param bool $value Whether or not to convert.
	 */
	if ( apply_filters( 'cptui_convert_post_type_posts', false ) ) {
		cptui_convert_post_type_posts( sanitize_text_field( $_POST['cpt_original'] ), sanitize_text_field( $_POST['cpt_custom_post_type']['name'] ) );
	}
}
add_action( 'init', 'cptui_do_convert_post_type_posts' );

/**
 * Handles slug_exist checks for cases of editing an existing post type.
 *
 * @since 1.5.3
 *
 * @param bool   $slug_exists    Current status for exist checks.
 * @param string $post_type_slug Post type slug being processed.
 * @param array  $post_types     CPTUI post types.
 * @return bool
 */
function cptui_updated_post_type_slug_exists( $slug_exists, $post_type_slug = '', $post_types = array() ) {
	if (
		( ! empty( $_POST['cpt_type_status'] ) && 'edit' == $_POST['cpt_type_status'] ) &&
		! in_array( $post_type_slug, cptui_reserved_taxonomies() ) &&
		( ! empty( $_POST['cpt_original'] ) && $post_type_slug === $_POST['cpt_original'] )
	)
		{
		$slug_exists = false;
	}
	return $slug_exists;
}
add_filter( 'cptui_post_type_slug_exists', 'cptui_updated_post_type_slug_exists', 11, 3 );
