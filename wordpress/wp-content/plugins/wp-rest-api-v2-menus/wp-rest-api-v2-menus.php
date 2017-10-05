<?php 
/*
Plugin Name: WP-REST-API V2 Menus
Version: 0.3
Description: Adding menus endpoints on WP REST API v2
Author: Claudio La Barbera
Author URI: http://www.claudiolabarbera.com
*/

/**
 * Get all registered menus
 * @return array List of menus with slug and description
 */
function wp_api_v2_menus_get_all_menus () {
    $menus = [];
    foreach (get_registered_nav_menus() as $slug => $description) {
        $obj = new stdClass;
        $obj->slug = $slug;
        $obj->description = $description;
        $menus[] = $obj;
    }

    return $menus;
}

/**
 * Get menu's data from his id
 * @param  array $data WP REST API data variable
 * @return object Menu's data with his items
 */
function wp_api_v2_menus_get_menu_data ( $data ) {
    $menu = new stdClass;
	$menu->items = [];
    if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $data['id'] ] ) ) {
        $menu = get_term( $locations[ $data['id'] ] );
        $menu->items = wp_get_nav_menu_items($menu->term_id);
    }
    return $menu;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'menus/v1', '/menus', array(
        'methods' => 'GET',
        'callback' => 'wp_api_v2_menus_get_all_menus',
    ) );

    register_rest_route( 'menus/v1', '/menus/(?P<id>[a-zA-Z(-]+)', array(
        'methods' => 'GET',
        'callback' => 'wp_api_v2_menus_get_menu_data',
    ) );
} );
