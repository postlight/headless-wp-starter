<?php
/**
 * Header Menu Type used for GraphQL.
 *
 * @package Postlight_Headless_WP
 */

use \WPGraphQL\Types;
use \WPGraphQL\Type\WPObjectType;

/**
 * Header menu type class that extends WPObjectType
 */
class HeaderMenuType extends WPObjectType {
    /**
     * Graphql Fields
     *
     * @var $fields HeaderMenuType fields
     */
    private static $fields;

    /**
     * Constructor
     */
    public function __construct() {
        $config = array(
            'name'        => 'HeaderMenuType',
            'fields'      => self::fields(),
            'description' => __( 'Header Menu', 'postlight-headless-wp' ),
        );
        parent::__construct( $config );
    }

    /**
     * Fields generator
     */
    protected static function fields() {
        if ( null === self::$fields ) {
            self::$fields = function () {
                $fields = array(
                    'label' => array(
                        'type'        => Types::string(),
                        'description' => __( 'The URL label', 'postlight-headless-wp' ),
                    ),
                    'url'   => array(
                        'type'        => Types::string(),
                        'description' => __( 'The URL', 'postlight-headless-wp' ),
                    ),
                    'type'  => array(
                        'type'        => Types::string(),
                        'description' => __( 'internal or external', 'postlight-headless-wp' ),
                    ),
                );
                return self::prepare_fields( $fields, 'HeaderMenuType' );
            };
        }
        return ! empty( self::$fields ) ? self::$fields : null;
    }
}
