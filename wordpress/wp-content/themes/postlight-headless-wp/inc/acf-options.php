<?php
/**
 * Add ACF options page.
 *
 * @package  Postlight_Headless_WP
 */

// Add a custom options page to associate ACF fields.
if ( function_exists( 'acf_add_options_page' ) ) {
    acf_add_options_page(
        [
            'page_title' => 'Headless Settings',
            'menu_title' => 'Headless',
            'menu_slug'  => 'headless-settings',
            'capability' => 'manage_options',
            'post_id'    => 'headless-settings',
            'redirect'   => false,
        ]
    );
}
