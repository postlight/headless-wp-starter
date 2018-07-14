<?php
/**
 * Custom Post Type UI Tools.
 *
 * @package CPTUI
 * @subpackage Tools
 * @author WebDevStudios
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register our tabs for the Tools screen.
 *
 * @since 1.3.0
 * @since 1.5.0 Renamed to "Tools"
 *
 * @internal
 *
 * @param array  $tabs         Array of tabs to display. Optional.
 * @param string $current_page Current page being shown. Optional. Default empty string.
 * @return array Amended array of tabs to show.
 */
function cptui_tools_tabs( $tabs = array(), $current_page = '' ) {

	if ( 'tools' === $current_page ) {
		$classes = array( 'nav-tab' );

		$tabs['page_title'] = get_admin_page_title();
		$tabs['tabs']       = array();
		$tabs['tabs']['post_types'] = array(
			'text'          => __( 'Post Types', 'custom-post-type-ui' ),
			'classes'       => $classes,
			'url'           => cptui_admin_url( 'admin.php?page=cptui_' . $current_page ),
			'aria-selected' => 'false',
		);

		$tabs['tabs']['taxonomies'] = array(
			'text'          => __( 'Taxonomies', 'custom-post-type-ui' ),
			'classes'       => $classes,
			'url'           => esc_url( add_query_arg( array( 'action' => 'taxonomies' ), cptui_admin_url( 'admin.php?page=cptui_' . $current_page ) ) ),
			'aria-selected' => 'false',
		);

		$tabs['tabs']['get_code'] = array(
			'text'          => __( 'Get Code', 'custom-post-type-ui' ),
			'classes'       => $classes,
			'url'           => esc_url( add_query_arg( array( 'action' => 'get_code' ), cptui_admin_url( 'admin.php?page=cptui_' . $current_page ) ) ),
			'aria-selected' => 'false',
		);

		$tabs['tabs']['debuginfo'] = array(
			'text'          => __( 'Debug Info', 'custom-post-type-ui' ),
			'classes'       => $classes,
			'url'           => esc_url( add_query_arg( array( 'action' => 'debuginfo' ), cptui_admin_url( 'admin.php?page=cptui_' . $current_page ) ) ),
			'aria-selected' => 'false',
		);

		$active_class = 'nav-tab-active';
		$action = cptui_get_current_action();
		if ( ! empty( $action ) ) {
			if ( 'taxonomies' === $action ) {
				$tabs['tabs']['taxonomies']['classes'][] = $active_class;
				$tabs['tabs']['taxonomies']['aria-selected'] = 'true';
			} elseif ( 'get_code' === $action ) {
				$tabs['tabs']['get_code']['classes'][] = $active_class;
				$tabs['tabs']['get_code']['aria-selected'] = 'true';
			} elseif ( 'debuginfo' === $action ) {
				$tabs['tabs']['debuginfo']['classes'][] = $active_class;
				$tabs['tabs']['debuginfo']['aria-selected'] = 'true';
			}
		} else {
			$tabs['tabs']['post_types']['classes'][] = $active_class;
			$tabs['tabs']['post_types']['aria-selected'] = 'true';
		}

		/**
		 * Filters the tabs being added for the tools area.
		 *
		 * @since 1.5.0
		 *
		 * @param array  $tabs         Array of tabs to show.
		 * @param string $action       Current tab being shown.
		 * @param string $active_class Class to use to mark the tab active.
		 */
		$tabs = apply_filters( 'cptui_tools_tabs', $tabs, $action, $active_class );
	}

	return $tabs;
}
add_filter( 'cptui_get_tabs', 'cptui_tools_tabs', 10, 2 );

/**
 * Create our settings page output.
 *
 * @since 1.0.0
 *
 * @internal
 */
function cptui_tools() {

	$tab = '';
	if ( ! empty( $_GET ) ) {
		if ( ! empty( $_GET['action'] ) && 'taxonomies' === $_GET['action'] ) {
			$tab = 'taxonomies';
		} elseif ( ! empty( $_GET['action'] ) && 'get_code' === $_GET['action'] ) {
			$tab = 'get_code';
		} elseif ( ! empty( $_GET['action'] ) && 'debuginfo' === $_GET['action'] ) {
			$tab = 'debuginfo';
		} else {
			$tab = 'post_types';
		}
	}

	echo '<div class="wrap">';

	/**
	 * Fires right inside the wrap div for the import/export pages.
	 *
	 * @since 1.3.0
	 *
	 * @deprecated 1.5.0
	 */
	do_action_deprecated( 'cptui_inside_importexport_wrap', array(), '1.5.0', 'cptui_inside_tools_wrap' );

	/**
	 * Fires right inside the wrap div for the tools pages.
	 *
	 * @since 1.5.0
	 */
	do_action( 'cptui_inside_tools_wrap' );

	// Create our tabs.
	cptui_settings_tab_menu( $page = 'tools' );

	/**
	 * Fires inside the markup for the import/export section.
	 *
	 * Allows for more modular control and adding more sections more easily.
	 *
	 * @since 1.2.0
	 *
	 * @deprecated 1.5.0
	 *
	 * @param string $tab Current tab being displayed.
	 */
	do_action_deprecated( 'cptui_import_export_sections', array( $tab ), '1.5.0', 'cptui_tools_sections' );

	/**
	 * Fires inside the markup for the tools section.
	 *
	 * Allows for more modular control and adding more sections more easily.
	 *
	 * @since 1.5.0
	 *
	 * @param string $tab Current tab being displayed.
	 */
	do_action( 'cptui_tools_sections', $tab );


	echo '</div><!-- End .wrap -->';
}

/**
 * Display our copy-able code for registered taxonomies.
 *
 * @since 1.0.0
 * @since 1.2.0 Added $cptui_taxonomies parameter.
 * @since 1.2.0 Added $single parameter.
 *
 * @param array $cptui_taxonomies Array of taxonomies to render. Optional.
 * @param bool  $single           Whether or not we are rendering a single taxonomy. Optional. Default false.
 */
function cptui_get_taxonomy_code( $cptui_taxonomies = array(), $single = false ) {
	if ( ! empty( $cptui_taxonomies ) ) {
		$callback = 'cptui_register_my_taxes';
		if ( $single ) {
			$key      = key( $cptui_taxonomies );
			$callback = 'cptui_register_my_taxes_' . str_replace( '-', '_', $cptui_taxonomies[ $key ]['name'] );
		}
	?>
function <?php echo $callback; ?>() {
<?php
	foreach ( $cptui_taxonomies as $tax ) {
		echo cptui_get_single_taxonomy_registery( $tax );
	} ?>
}

add_action( 'init', '<?php echo $callback; ?>' );
<?php
	} else {
		_e( 'No taxonomies to display at this time', 'custom-post-type-ui' );
	}
}

/**
 * Create output for single taxonomy to be ready for copy/paste from Get Code.
 *
 * @since 1.0.0
 *
 * @param array $taxonomy Taxonomy data to output. Optional.
 */
function cptui_get_single_taxonomy_registery( $taxonomy = array() ) {

	$post_types = "''";
	if ( is_array( $taxonomy['object_types'] ) ) {
		$post_types = 'array( "' . implode( '", "', $taxonomy['object_types'] ) . '" )';
	}

	if ( false !== get_disp_boolean( $taxonomy['rewrite'] ) ) {
		$rewrite = disp_boolean( $taxonomy['rewrite'] );

		$rewrite_slug = ' \'slug\' => \'' . $taxonomy['name'] . '\',';
		if ( ! empty( $taxonomy['rewrite_slug'] ) ) {
			$rewrite_slug = ' \'slug\' => \'' . $taxonomy['rewrite_slug'] . '\',';
		}

		$rewrite_withfront = '';
		$withfront = disp_boolean( $taxonomy['rewrite_withfront'] );
		if ( ! empty( $withfront ) ) {
			$rewrite_withfront = ' \'with_front\' => ' . $withfront . ', ';
		}

		$hierarchical = ( ! empty( $taxonomy['rewrite_hierarchical'] ) ) ? disp_boolean( $taxonomy['rewrite_hierarchical'] ) : '';
		$rewrite_hierarchcial = '';
		if ( ! empty( $hierarchical ) ) {
			$rewrite_hierarchcial = ' \'hierarchical\' => ' . $hierarchical . ', ';
		}

		if ( ! empty( $taxonomy['rewrite_slug'] ) || false !== disp_boolean( $taxonomy['rewrite_withfront'] ) ) {
			$rewrite_start = 'array(';
			$rewrite_end   = ')';

			$rewrite = $rewrite_start . $rewrite_slug . $rewrite_withfront . $rewrite_hierarchcial . $rewrite_end;
		}
	} else {
		$rewrite = disp_boolean( $taxonomy['rewrite'] );
	}
	$public = ( isset( $taxonomy['public'] ) ) ? disp_boolean( $taxonomy['public'] ) : 'true';
	$show_in_quick_edit = ( isset( $taxonomy['show_in_quick_edit'] ) ) ? disp_boolean( $taxonomy['show_in_quick_edit'] ) : disp_boolean( $taxonomy['show_ui'] );

	$show_in_menu = ( ! empty( $taxonomy['show_in_menu'] ) && false !== get_disp_boolean( $taxonomy['show_in_menu'] ) ) ? 'true' : 'false';
	if ( empty( $taxonomy['show_in_menu'] ) ) {
		$show_in_menu = disp_boolean( $taxonomy['show_ui'] );
	}

	$show_in_nav_menus = ( ! empty( $taxonomy['show_in_nav_menus'] ) && false !== get_disp_boolean( $taxonomy['show_in_nav_menus'] ) ) ? 'true' : 'false';
	if ( empty( $taxonomy['show_in_nav_menus'] ) ) {
		$show_in_nav_menus = $public;
	}

	$show_in_rest = ( ! empty( $taxonomy['show_in_rest'] ) && false !== get_disp_boolean( $taxonomy['show_in_rest'] ) ) ? 'true' : 'false';
	$rest_base    = ( ! empty( $taxonomy['rest_base'] ) ) ? $taxonomy['rest_base'] : $taxonomy['name'];

	$my_theme   = wp_get_theme();
	$textdomain = $my_theme->get( 'TextDomain' );
?>

	/**
	 * Taxonomy: <?php echo $taxonomy['label']; ?>.
	 */

	$labels = array(
		"name" => __( "<?php echo $taxonomy['label']; ?>", "<?php echo $textdomain; ?>" ),
		"singular_name" => __( "<?php echo $taxonomy['singular_label']; ?>", "<?php echo $textdomain; ?>" ),
<?php
	foreach ( $taxonomy['labels'] as $key => $label ) {
		if ( ! empty( $label ) ) {
			echo "\t\t" . '"' . $key . '" => __( "' . $label . '", "' . $textdomain . '" ),' . "\n";
		}
	}
?>
	);

	$args = array(
		"label" => __( "<?php echo $taxonomy['label']; ?>", "<?php echo $textdomain; ?>" ),
		"labels" => $labels,
		"public" => <?php echo $public; ?>,
		"hierarchical" => <?php echo $taxonomy['hierarchical']; ?>,
		"label" => "<?php echo $taxonomy['label']; ?>",
		"show_ui" => <?php echo disp_boolean( $taxonomy['show_ui'] ); ?>,
		"show_in_menu" => <?php echo $show_in_menu; ?>,
		"show_in_nav_menus" => <?php echo $show_in_nav_menus; ?>,
		"query_var" => <?php echo disp_boolean( $taxonomy['query_var'] );?>,
		"rewrite" => <?php echo $rewrite; ?>,
		"show_admin_column" => <?php echo $taxonomy['show_admin_column']; ?>,
		"show_in_rest" => <?php echo $show_in_rest; ?>,
		"rest_base" => "<?php echo $rest_base; ?>",
		"show_in_quick_edit" => <?php echo $show_in_quick_edit; ?>,
	);
	register_taxonomy( "<?php echo $taxonomy['name']; ?>", <?php echo $post_types; ?>, $args );
<?php
}

/**
 * Display our copy-able code for registered post types.
 *
 * @since 1.0.0
 * @since 1.2.0 Added $cptui_post_types parameter.
 * @since 1.2.0 Added $single parameter.
 *
 * @param array $cptui_post_types Array of post types to render. Optional.
 * @param bool  $single           Whether or not we are rendering a single post type. Optional. Default false.
 */
function cptui_get_post_type_code( $cptui_post_types = array(), $single = false ) {
	// Whitespace very much matters here, thus why it's all flush against the left side.
	if ( ! empty( $cptui_post_types ) ) {
		$callback = 'cptui_register_my_cpts';
		if ( $single ) {
			$key = key( $cptui_post_types );
			$callback = 'cptui_register_my_cpts_' . str_replace( '-', '_', $cptui_post_types[ $key ]['name'] );
		}
?>

function <?php echo $callback; ?>() {
<?php // Space before this line reflects in textarea.
		foreach ( $cptui_post_types as $type ) {
			echo cptui_get_single_post_type_registery( $type );
		}
?>
}

add_action( 'init', '<?php echo $callback; ?>' );
<?php
	} else {
		_e( 'No post types to display at this time', 'custom-post-type-ui' );
	}
}

/**
 * Create output for single post type to be ready for copy/paste from Get Code.
 *
 * @since 1.0.0
 *
 * @param array $post_type Post type data to output. Optional.
 */
function cptui_get_single_post_type_registery( $post_type = array() ) {

	/** This filter is documented in custom-post-type-ui/custom-post-type-ui.php */
	$post_type['map_meta_cap'] = apply_filters( 'cptui_map_meta_cap', 'true', $post_type['name'], $post_type );

	/** This filter is documented in custom-post-type-ui/custom-post-type-ui.php */
	$user_supports_params = apply_filters( 'cptui_user_supports_params', array(), $post_type['name'], $post_type );
	if ( is_array( $user_supports_params ) ) {
		$post_type['supports'] = array_merge( $post_type['supports'], $user_supports_params );
	}

	$yarpp = false; // Prevent notices.
	if ( ! empty( $post_type['custom_supports'] ) ) {
		$custom = explode( ',', $post_type['custom_supports'] );
		foreach ( $custom as $part ) {
			// We'll handle YARPP separately.
			if ( in_array( $part, array( 'YARPP', 'yarpp' ) ) ) {
				$yarpp = true;
				continue;
			}
			$post_type['supports'][] = $part;
		}
	}

	$rewrite_withfront = '';
	$rewrite = get_disp_boolean( $post_type['rewrite'] );
	if ( false !== $rewrite ) {
		$rewrite = disp_boolean( $post_type['rewrite'] );

		$rewrite_slug = ' "slug" => "' . $post_type['name'] . '",';
		if ( ! empty( $post_type['rewrite_slug'] ) ) {
			$rewrite_slug = ' "slug" => "' . $post_type['rewrite_slug'] . '",';
		}

		$withfront = disp_boolean( $post_type['rewrite_withfront'] );
		if ( ! empty( $withfront ) ) {
			$rewrite_withfront = ' "with_front" => ' . $withfront . ' ';
		}

		if ( ! empty( $post_type['rewrite_slug'] ) || ! empty( $post_type['rewrite_withfront'] ) ) {
			$rewrite_start = 'array(';
			$rewrite_end   = ')';

			$rewrite = $rewrite_start . $rewrite_slug . $rewrite_withfront . $rewrite_end;
		}
	} else {
		$rewrite = disp_boolean( $post_type['rewrite'] );
	}
	$has_archive = get_disp_boolean( $post_type['has_archive'] );
	if ( false !== $has_archive ) {
		$has_archive = disp_boolean( $post_type['has_archive'] );
		if ( ! empty( $post_type['has_archive_string'] ) ) {
			$has_archive = '"' . $post_type['has_archive_string'] . '"';
		}
	} else {
		$has_archive = disp_boolean( $post_type['has_archive'] );
	}

	$supports = '';
	// Do a little bit of php work to get these into strings.
	if ( ! empty( $post_type['supports'] ) && is_array( $post_type['supports'] ) ) {
		$supports = 'array( "' . implode( '", "', $post_type['supports'] ) . '" )';
	}

	if ( in_array( 'none', $post_type['supports'] ) ) {
		$supports = 'false';
	}

	$taxonomies = '';
	if ( ! empty( $post_type['taxonomies'] ) && is_array( $post_type['taxonomies'] ) ) {
		$taxonomies = 'array( "' . implode( '", "', $post_type['taxonomies'] ) . '" )';
	}

	if ( in_array( $post_type['query_var'], array( 'true', 'false', '0', '1' ) ) ) {
		$post_type['query_var'] = disp_boolean( $post_type['query_var'] );
	}
	if ( ! empty( $post_type['query_var_slug'] ) ) {
		$post_type['query_var'] = '"' . $post_type['query_var_slug'] . '"';
	}

	if ( empty( $post_type['show_in_rest'] ) ) {
		$post_type['show_in_rest'] = 'false';
	}

	$show_in_menu = get_disp_boolean( $post_type['show_in_menu'] );
	if ( false !== $show_in_menu ) {
		$show_in_menu = disp_boolean( $post_type['show_in_menu'] );
		if ( ! empty( $post_type['show_in_menu_string'] ) ) {
			$show_in_menu = '"' . $post_type['show_in_menu_string'] . '"';
		}
	} else {
		$show_in_menu = disp_boolean( $post_type['show_in_menu'] );
	}

	$public = ( isset( $post_type['public'] ) ) ? disp_boolean( $post_type['public'] ) : 'true';
	$show_in_nav_menus = ( ! empty( $post_type['show_in_nav_menus'] ) && false !== get_disp_boolean( $post_type['show_in_nav_menus'] ) ) ? 'true' : 'false';
	if ( empty( $post_type['show_in_nav_menus'] ) ) {
		$show_in_nav_menus = $public;
	}

	$post_type['description'] = addslashes( $post_type['description'] );

	$my_theme = wp_get_theme();
	$textdomain = $my_theme->get( 'TextDomain' );
?>

	/**
	 * Post Type: <?php echo $post_type['label']; ?>.
	 */

	$labels = array(
		"name" => __( "<?php echo $post_type['label']; ?>", "<?php echo $textdomain; ?>" ),
		"singular_name" => __( "<?php echo $post_type['singular_label']; ?>", "<?php echo $textdomain; ?>" ),
<?php
	foreach ( $post_type['labels'] as $key => $label ) {
		if ( ! empty( $label ) ) {
			if ( 'parent' === $key ) {
				// Fix for incorrect label key. See #439.
				echo "\t\t" . '"' . 'parent_item_colon' . '" => __( "' . $label . '", "' . $textdomain . '" ),' . "\n";
			} else {
				echo "\t\t" . '"' . $key . '" => __( "' . $label . '", "' . $textdomain . '" ),' . "\n";
			}
		}
	}
?>
	);

	$args = array(
		"label" => __( "<?php echo $post_type['label']; ?>", "<?php echo $textdomain; ?>" ),
		"labels" => $labels,
		"description" => "<?php echo $post_type['description']; ?>",
		"public" => <?php echo disp_boolean( $post_type['public'] ); ?>,
		"publicly_queryable" => <?php echo disp_boolean( $post_type['publicly_queryable'] ); ?>,
		"show_ui" => <?php echo disp_boolean( $post_type['show_ui'] ); ?>,
		"show_in_rest" => <?php echo disp_boolean( $post_type['show_in_rest'] ); ?>,
		"rest_base" => "<?php echo $post_type['rest_base']; ?>",
		"has_archive" => <?php echo $has_archive; ?>,
		"show_in_menu" => <?php echo $show_in_menu; ?>,
		"show_in_nav_menus" => <?php echo $show_in_nav_menus; ?>,
		"exclude_from_search" => <?php echo disp_boolean( $post_type['exclude_from_search'] ); ?>,
		"capability_type" => "<?php echo $post_type['capability_type']; ?>",
		"map_meta_cap" => <?php echo disp_boolean( $post_type['map_meta_cap'] ); ?>,
		"hierarchical" => <?php echo disp_boolean( $post_type['hierarchical'] ); ?>,
		"rewrite" => <?php echo $rewrite; ?>,
		"query_var" => <?php echo $post_type['query_var']; ?>,
<?php if ( ! empty( $post_type['menu_position'] ) ) { ?>
		"menu_position" => <?php echo $post_type['menu_position']; ?>,
<?php } ?>
<?php if ( ! empty( $post_type['menu_icon'] ) ) { ?>
		"menu_icon" => "<?php echo $post_type['menu_icon']; ?>",
<?php } ?>
<?php if ( ! empty( $supports ) ) { ?>
		"supports" => <?php echo $supports; ?>,
<?php } ?>
<?php if ( ! empty( $taxonomies ) ) { ?>
		"taxonomies" => <?php echo $taxonomies; ?>,
<?php } ?>
<?php if ( true === $yarpp ) { ?>
		"yarpp_support" => <?php echo disp_boolean( $yarpp ); ?>,
<?php } ?>
	);

	register_post_type( "<?php echo $post_type['name']; ?>", $args );
<?php
}

/**
 * Import the posted JSON data from a separate export.
 *
 * @since 1.0.0
 *
 * @internal
 *
 * @param array $postdata $_POST data as json. Optional.
 * @return mixed false on nothing to do, otherwise void.
 */
function cptui_import_types_taxes_settings( $postdata = array() ) {
	if ( ! isset( $postdata['cptui_post_import'] ) && ! isset( $postdata['cptui_tax_import'] ) ) {
		return false;
	}

	$status = 'import_fail';
	$success = false;

	/**
	 * Filters the post type data to import.
	 *
	 * Allows third parties to provide their own data dump and import instead of going through our UI.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $value Default to no data.
	 */
	$third_party_post_type_data = apply_filters( 'cptui_third_party_post_type_import', false );

	/**
	 * Filters the taxonomy data to import.
	 *
	 * Allows third parties to provide their own data dump and import instead of going through our UI.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $value Default to no data.
	 */
	$third_party_taxonomy_data  = apply_filters( 'cptui_third_party_taxonomy_import', false );

	if ( false !== $third_party_post_type_data ) {
		$postdata['cptui_post_import'] = $third_party_post_type_data;
	}

	if ( false !== $third_party_taxonomy_data ) {
		$postdata['cptui_tax_import'] = $third_party_taxonomy_data;
	}

	if ( ! empty( $postdata['cptui_post_import'] ) ) {
		$cpt_data = stripslashes_deep( trim( $postdata['cptui_post_import'] ) );
		$settings = json_decode( $cpt_data, true );

		// Add support to delete settings outright, without accessing database.
		// Doing double check to protect.
		if ( is_null( $settings ) && '{""}' === $cpt_data ) {

			/**
			 * Filters whether or not 3rd party options were deleted successfully within post type import.
			 *
			 * @since 1.3.0
			 *
			 * @param bool  $value    Whether or not someone else deleted successfully. Default false.
			 * @param array $postdata Post type data.
			 */
			if ( false === ( $success = apply_filters( 'cptui_post_type_import_delete_save', false, $postdata ) ) ) {
				$success = delete_option( 'cptui_post_types' );
			}
		}

		if ( $settings ) {
			if ( false !== cptui_get_post_type_data() ) {
				/** This filter is documented in /inc/import-export.php */
				if ( false === ( $success = apply_filters( 'cptui_post_type_import_delete_save', false, $postdata ) ) ) {
					delete_option( 'cptui_post_types' );
				}
			}

			/**
			 * Filters whether or not 3rd party options were updated successfully within the post type import.
			 *
			 * @since 1.3.0
			 *
			 * @param bool  $value    Whether or not someone else updated successfully. Default false.
			 * @param array $postdata Post type data.
			 */
			if ( false === ( $success = apply_filters( 'cptui_post_type_import_update_save', false, $postdata ) ) ) {
				$success = update_option( 'cptui_post_types', $settings );
			}
		}
		// Used to help flush rewrite rules on init.
		set_transient( 'cptui_flush_rewrite_rules', 'true', 5 * 60 );

		if ( $success ) {
			$status = 'import_success';
		}
	} elseif ( ! empty( $postdata['cptui_tax_import'] ) ) {
		$tax_data = stripslashes_deep( trim( $postdata['cptui_tax_import'] ) );
		$settings = json_decode( $tax_data, true );

		// Add support to delete settings outright, without accessing database.
		// Doing double check to protect.
		if ( is_null( $settings ) && '{""}' === $tax_data ) {

			/**
			 * Filters whether or not 3rd party options were deleted successfully within taxonomy import.
			 *
			 * @since 1.3.0
			 *
			 * @param bool  $value    Whether or not someone else deleted successfully. Default false.
			 * @param array $postdata Taxonomy data
			 */
			if ( false === ( $success = apply_filters( 'cptui_taxonomy_import_delete_save', false, $postdata ) ) ) {
				$success = delete_option( 'cptui_taxonomies' );
			}
		}

		if ( $settings ) {
			if ( false !== cptui_get_taxonomy_data() ) {
				/** This filter is documented in /inc/import-export.php */
				if ( false === ( $success = apply_filters( 'cptui_taxonomy_import_delete_save', false, $postdata ) ) ) {
					delete_option( 'cptui_taxonomies' );
				}
			}
			/**
			 * Filters whether or not 3rd party options were updated successfully within the taxonomy import.
			 *
			 * @since 1.3.0
			 *
			 * @param bool  $value    Whether or not someone else updated successfully. Default false.
			 * @param array $postdata Taxonomy data.
			 */
			if ( false === ( $success = apply_filters( 'cptui_taxonomy_import_update_save', false, $postdata ) ) ) {
				$success = update_option( 'cptui_taxonomies', $settings );
			}
		}
		// Used to help flush rewrite rules on init.
		set_transient( 'cptui_flush_rewrite_rules', 'true', 5 * 60 );
		if ( $success ) {
			$status = 'import_success';
		}
	}

	return $status;
}

/**
 * Content for the Post Types/Taxonomies Tools tab.
 *
 * @since 1.2.0
 *
 * @internal
 */
function cptui_render_posttypes_taxonomies_section() {
?>

	<p><?php _e( 'If you are wanting to migrate registered post types or taxonomies from this site to another, that will also use Custom Post Type UI, use the import and export functionality. If you are moving away from Custom Post Type UI, use the information in the "Get Code" tab.', 'custom-post-type-ui' ); ?></p>

<p>
<?php
	printf(
		'<strong>%s</strong>: %s',
		__( 'NOTE', 'custom-post-type-ui' ),
		__( 'This will not export the associated posts or taxonomy terms, just the settings.', 'custom-post-type-ui' )
	);
?>
</p>
<table class="form-table cptui-table">
	<?php if ( ! empty( $_GET ) && empty( $_GET['action'] ) ) { ?>
		<tr>
			<td class="outter">
				<h2><label for="cptui_post_import"><?php _e( 'Import Post Types', 'custom-post-type-ui' ); ?></label></h2>

				<form method="post">
					<textarea class="cptui_post_import" placeholder="<?php esc_attr_e( 'Paste content here.', 'custom-post-type-ui' ); ?>" id="cptui_post_import" name="cptui_post_import"></textarea>

					<p class="wp-ui-highlight">
						<strong><?php _e( 'Note:', 'custom-post-type-ui' ); ?></strong> <?php _e( 'Importing will overwrite previous registered settings.', 'custom-post-type-ui' ); ?>
					</p>

					<p>
						<strong><?php _e( 'To import post types from a different WordPress site, paste the exported content from that site and click the "Import" button.', 'custom-post-type-ui' ); ?></strong>
					</p>

					<p>
						<input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Import', 'custom-post-type-ui' ); ?>" />
					</p>
				</form>
			</td>
			<td class="outter">
				<h2><label for="cptui_post_export"><?php _e( 'Export Post Types', 'custom-post-type-ui' ); ?></label></h2>
				<?php
				$cptui_post_types = cptui_get_post_type_data();
				if ( ! empty( $cptui_post_types ) ) {
					$content = esc_html( json_encode( $cptui_post_types ) );
				} else {
					$content = __( 'No post types registered yet.', 'custom-post-type-ui' );
				}
				?>
				<textarea title="<?php esc_attr_e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'custom-post-type-ui' ); ?>" onclick="this.focus();this.select();" onfocus="this.focus();this.select();" readonly="readonly" aria-readonly="true" class="cptui_post_import" id="cptui_post_export" name="cptui_post_export"><?php echo $content; ?></textarea>

				<p>
					<strong><?php _e( 'Use the content above to import current post types into a different WordPress site. You can also use this to simply back up your post type settings.', 'custom-post-type-ui' ); ?></strong>
				</p>
			</td>
		</tr>
	<?php } elseif ( ! empty( $_GET ) && 'taxonomies' == $_GET['action'] ) { ?>
		<tr>
			<td class="outter">
				<h2><label for="cptui_tax_import"><?php _e( 'Import Taxonomies', 'custom-post-type-ui' ); ?></label></h2>

				<form method="post">
					<textarea class="cptui_tax_import" placeholder="<?php esc_attr_e( 'Paste content here.', 'custom-post-type-ui' ); ?>" id="cptui_tax_import" name="cptui_tax_import"></textarea>

					<p class="wp-ui-highlight">
						<strong><?php _e( 'Note:', 'custom-post-type-ui' ); ?></strong> <?php _e( 'Importing will overwrite previous registered settings.', 'custom-post-type-ui' ); ?>
					</p>

					<p>
						<strong><?php _e( 'To import taxonomies from a different WordPress site, paste the exported content from that site and click the "Import" button.', 'custom-post-type-ui' ); ?></strong>
					</p>

					<p>
						<input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Import', 'custom-post-type-ui' ); ?>" />
					</p>
				</form>
			</td>
			<td class="outter">
				<h2><label for="cptui_tax_export"><?php _e( 'Export Taxonomies', 'custom-post-type-ui' ); ?></label></h2>
				<?php
				$cptui_taxonomies = cptui_get_taxonomy_data();
				if ( ! empty( $cptui_taxonomies ) ) {
					$content = esc_html( json_encode( $cptui_taxonomies ) );
				} else {
					$content = __( 'No taxonomies registered yet.', 'custom-post-type-ui' );
				}
				?>
				<textarea title="<?php esc_attr_e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'custom-post-type-ui' ); ?>" onclick="this.focus();this.select()" onfocus="this.focus();this.select();" readonly="readonly" aria-readonly="true" class="cptui_tax_import" id="cptui_tax_export" name="cptui_tax_export"><?php echo $content; ?></textarea>

				<p>
					<strong><?php _e( 'Use the content above to import current taxonomies into a different WordPress site. You can also use this to simply back up your taxonomy settings.', 'custom-post-type-ui' ); ?></strong>
				</p>
			</td>
		</tr>
	<?php } ?>
</table>
<?php
}

/**
 * Content for the Get Code tab.
 *
 * @since 1.2.0
 *
 * @internal
 */
function cptui_render_getcode_section() {
?>
	<h1><?php _e( 'Get Post Type and Taxonomy Code', 'custom-post-type-ui' ); ?></h1>

		<h2><?php _e( 'All CPT UI Post Types', 'custom-post-type-ui' ); ?></h2>

		<p><?php esc_html_e( 'All of the selectable code snippets below are useful if you wish to migrate away from CPTUI and retain your existing registered post types or taxonomies.', 'custom-post-type-ui' ); ?></p>

		<?php $cptui_post_types = cptui_get_post_type_data(); ?>
		<label for="cptui_post_type_get_code"><?php _e( 'Copy/paste the code below into your functions.php file.', 'custom-post-type-ui' ); ?></label>
		<textarea name="cptui_post_type_get_code" id="cptui_post_type_get_code" class="cptui_post_type_get_code" onclick="this.focus();this.select()" onfocus="this.focus();this.select();" readonly="readonly" aria-readonly="true"><?php cptui_get_post_type_code( $cptui_post_types ); ?></textarea>

		<?php
		if ( ! empty( $cptui_post_types ) ) {
			foreach ( $cptui_post_types as $post_type ) { ?>
				<h2 id="<?php echo esc_attr( $post_type['name'] ); ?>"><?php
					$type = ( ! empty( $post_type['label'] ) ) ? $post_type['label'] : $post_type['name'];
					printf( __( '%s Post Type', 'custom-post-type-ui' ), $type ); ?></h2>
				<label for="cptui_post_type_get_code_<?php echo $post_type['name']; ?>"><?php _e( 'Copy/paste the code below into your functions.php file.', 'custom-post-type-ui' ); ?></label>
				<textarea name="cptui_post_type_get_code_<?php echo $post_type['name']; ?>" id="cptui_post_type_get_code_<?php echo $post_type['name']; ?>" class="cptui_post_type_get_code" onclick="this.focus();this.select()" onfocus="this.focus();this.select();" readonly="readonly" aria-readonly="true"><?php cptui_get_post_type_code( array( $post_type ), true ); ?></textarea>
			<?php }
		} ?>

		<h2><?php _e( 'All CPT UI Taxonomies', 'custom-post-type-ui' ); ?></h2>

		<?php $cptui_taxonomies = cptui_get_taxonomy_data(); ?>
		<label for="cptui_tax_get_code"><?php _e( 'Copy/paste the code below into your functions.php file.', 'custom-post-type-ui' ); ?></label>
		<textarea name="cptui_tax_get_code" id="cptui_tax_get_code" class="cptui_tax_get_code" onclick="this.focus();this.select()" onfocus="this.focus();this.select();" readonly="readonly" aria-readonly="true"><?php cptui_get_taxonomy_code( $cptui_taxonomies ); ?></textarea>

		<?php
		if ( ! empty( $cptui_taxonomies ) ) {
			foreach ( $cptui_taxonomies as $taxonomy ) { ?>
				<h2 id="<?php echo esc_attr( $taxonomy['name'] ); ?>"><?php
					$tax = ( ! empty( $taxonomy['label'] ) ) ? $taxonomy['label'] : $taxonomy['name'];
					printf( __( '%s Taxonomy', 'custom-post-type-ui' ), $tax ); ?></h2>
				<label for="cptui_tax_get_code_<?php echo $taxonomy['name']; ?>"><?php _e( 'Copy/paste the code below into your functions.php file.', 'custom-post-type-ui' ); ?></label>
				<textarea name="cptui_tax_get_code_<?php echo $taxonomy['name']; ?>" id="cptui_tax_get_code_<?php echo $taxonomy['name']; ?>" class="cptui_tax_get_code" onclick="this.focus();this.select()" onfocus="this.focus();this.select();" readonly="readonly" aria-readonly="true"><?php cptui_get_taxonomy_code( array( $taxonomy ), true ); ?></textarea>
			<?php }
		} ?>
	<?php
}

/**
 * Content for the Debug Info tab.
 *
 * @since 1.2.0
 *
 * @internal
 */
function cptui_render_debuginfo_section() {
	$debuginfo = new CPTUI_Debug_Info();

	echo '<form id="cptui_debug_info" method="post">';
	$debuginfo->tab_site_info();

	if ( ! empty( $_POST ) && isset( $_POST['cptui_debug_info_email'] ) ) {
		$email_args = array();
		$email_args['email'] = sanitize_text_field( $_POST['cptui_debug_info_email'] );
		$debuginfo->send_email( $email_args );
	}

	echo '<p><label for="cptui_debug_info_email">' . __( 'Please provide an email address to send debug information to: ', 'custom-post-type-ui' ) . '</label><input type="email" id="cptui_debug_info_email" name="cptui_debug_info_email" value="" /></p>';

	/**
	 * Filters the text value to use on the button when sending debug information.
	 *
	 * @since 1.2.0
	 *
	 * @param string $value Text to use for the button.
	 */
	echo '<p><input type="submit" class="button-primary" name="cptui_send_debug_email" value="' . esc_attr( apply_filters( 'cptui_debug_email_submit_button', __( 'Send debug info', 'custom-post-type-ui' ) ) ) . '" /></p>';
	echo '</form>';

	/**
	 * Fires after the display of the site information.
	 *
	 * @since 1.3.0
	 */
	do_action( 'cptui_after_site_info' );
}

/**
 * Renders various tab sections for the Tools page, based on current tab.
 *
 * @since 1.2.0
 *
 * @internal
 *
 * @param string $tab Current tab to display.
 */
function cptui_render_tools( $tab ) {
	if ( isset( $tab ) ) {
		if ( 'post_types' == $tab || 'taxonomies' == $tab ) {
			cptui_render_posttypes_taxonomies_section();
		}

		if ( 'get_code' == $tab ) {
			cptui_render_getcode_section();
		}

		if ( 'debuginfo' == $tab ) {
			cptui_render_debuginfo_section();
		}
	}
}
add_action( 'cptui_tools_sections', 'cptui_render_tools' );

/**
 * Handle the import of transferred post types and taxonomies.
 *
 * @since 1.5.0
 */
function cptui_do_import_types_taxes() {

	if ( ! empty( $_POST ) &&
	     ( ! empty( $_POST['cptui_post_import'] ) && isset( $_POST['cptui_post_import'] ) ) ||
	     ( ! empty( $_POST['cptui_tax_import'] ) && isset( $_POST['cptui_tax_import'] ) )
	) {
		$success = cptui_import_types_taxes_settings( $_POST );
		add_action( 'admin_notices', "cptui_{$success}_admin_notice" );
	}
}
add_action( 'init', 'cptui_do_import_types_taxes', 8 );
