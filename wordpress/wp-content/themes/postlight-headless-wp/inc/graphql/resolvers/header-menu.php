<?php
/**
 * Header Menu GraphQL resolver.
 *
 * @package Postlight_Headless_WP
 */

use \WPGraphQL\Types;
use \WPGraphQL\Type\WPObjectType;

require_once __DIR__ . '/../types/class-headermenutype.php';

/**
 * Get header menu items
 */
function get_items() {
    $counter    = 0;
    $menu_items = wp_get_nav_menu_items( 'Header Menu' );
    foreach ( $menu_items as $item ) {
        $url_arr = explode( '/', $item->url );
        $slug    = $url_arr[ count( $url_arr ) - 2 ];

        $resolve[ $counter ]['label'] = $item->title;
        $resolve[ $counter ]['type']  = 'internal';
        switch ( $item->object ) {
            case 'post':
                $resolve[ $counter ]['url'] = '/post/' . $slug;
                break;
            case 'category':
                $resolve[ $counter ]['url'] = '/category/' . $slug;
                break;
            case 'page':
                $resolve[ $counter ]['url'] = '/page/' . $slug;
                break;
            case 'custom':
                $resolve[ $counter ]['url']  = $item->url;
                $resolve[ $counter ]['type'] = 'external';
                break;
            default:
                break;
        }
        $counter++;
    }

    return $resolve;
}

add_action(
    'graphql_register_types',
    function () {
        register_graphql_field(
            'RootQuery',
            'headerMenu',
            [
                'type'        => Types::list_of( new HeaderMenuType() ),
                'description' => __( 'Returns the header menu items', 'postlight-headless-wp' ),
                'resolve'     => function () {
                    return get_items();
                },
            ]
        );
    }
);
