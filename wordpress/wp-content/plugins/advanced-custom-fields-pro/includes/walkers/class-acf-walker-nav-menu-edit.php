<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Walker_Nav_Menu_Edit') ) :

class ACF_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {

    /**
     * Starts the element output.
     *
     * Calls the Walker_Nav_Menu_Edit start_el function and then injects the custom field HTML  
     *
	 * @since 5.0.0
	 * @since 5.7.2 Added preg_replace based on https://github.com/ineagu/wp-menu-item-custom-fields
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param WP_Post  $item   Menu item data object.
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     * @param int      $id     Current item ID.
     */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		
		// vars
		$item_output = '';
		
		// call parent function
		parent::start_el( $item_output, $item, $depth, $args, $id );
		
		// inject custom field HTML
		$output .= preg_replace(
			// NOTE: Check this regex from time to time!
			'/(?=<(fieldset|p)[^>]+class="[^"]*field-move)/',
			$this->get_fields( $item, $depth, $args, $id ),
			$item_output
		);
	}


	/**
	 * Get custom fields HTML
	 *
	 * @since 5.0.0
	 * @since 5.7.2 Added action based on https://github.com/ineagu/wp-menu-item-custom-fields
	 *
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Menu item args.
	 * @param int    $id     Nav menu ID.
	 * @return string
	 */
	function get_fields( $item, $depth, $args = array(), $id = 0 ) {
		ob_start();
		
		/**
         * Get menu item custom fields from plugins/themes
         *
         * @since 5.7.2
         *
         * @param int    $item_id	post ID of menu
         * @param object $item		Menu item data object.
         * @param int    $depth		Depth of menu item. Used for padding.
         * @param array  $args		Menu item args.
         * @param int    $id		Nav menu ID.
         */
		do_action( 'wp_nav_menu_item_custom_fields', $item->ID, $item, $depth, $args, $id );
		return ob_get_clean();
	}		
}

endif;

?>