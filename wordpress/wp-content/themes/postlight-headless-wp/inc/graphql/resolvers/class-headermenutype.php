<?php

use \WPGraphQL\Types;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Header menu type class that extends WPObjectType
 */
class HeaderMenuType extends \WPGraphQL\Type\WPObjectType {
    /**
     * @var $fields HeaderMenuType fields
     */
    private static $fields;

    /**
     * Constructor
     */
    public function __construct() {
        $config = [
            'name'        => 'HeaderMenuType',
            'fields'      => self::fields(),
            'description' => __( 'Header Menu', 'headlesswp' ),
        ];
        parent::__construct( $config );
    }

    /**
     * Fields generator
     */
    protected static function fields() {
        if ( null === self::$fields ) {
            self::$fields = function () {
                $fields = [
                    'label' => [
                        'type'        => \WPGraphQL\Types::string(),
                        'description' => __( 'The URL label', 'headlesswp' ),
                    ],
                    'url'   => [
                        'type'        => \WPGraphQL\Types::string(),
                        'description' => __( 'The URL', 'headlesswp' ),
                    ],
                    'type'  => [
                        'type'        => \WPGraphQL\Types::string(),
                        'description' => __( 'internal or external', 'headlesswp' ),
                    ],
                ];
                return self::prepare_fields( $fields, 'HeaderMenuType' );
            };
        }
        return !empty( self::$fields ) ? self::$fields : null;
    }
}

/**
 * Get header menu items, used in the resolver
 */
function get_items() {
    $counter    = 0;
    $menu_items = wp_get_nav_menu_items( 'Header Menu' );
    foreach ( $menu_items as $item ) {
        $url_arr = explode( '/', $item->url );
        $slug    = $url_arr[ count( $url_arr )-2 ];

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

add_action('graphql_register_types', function () {
    register_graphql_field('RootQuery', 'headerMenu', [
        'type'        => \WPGraphQL\Types::list_of( new HeaderMenuType() ),
        'description' => __( 'Returns the header menu items', 'headlesswp' ),
        'resolve'     => function () {
            return get_items();
        },
    ]);
});
