<?php
namespace WPGraphQL\Type\Plugin;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

/**
 * Class PluginType
 *
 * This sets up the PluginType schema.
 *
 * @package WPGraphQL\Type
 * @since 0.0.5
 */
class PluginType extends WPObjectType {

	/**
	 * Holds the type name
	 * @var string $type_name
	 */
	private static $type_name;

	/**
	 * Holds the $fields definition for the PluginType
	 * @var $fields
	 */
	private static $fields;

	/**
	 * PluginType constructor.
	 * @since 0.0.5
	 */
	public function __construct() {

		/**
		 * Set the type_name
		 * @since 0.0.5
		 */
		self::$type_name = 'Plugin';

		$config = [
			'name' => self::$type_name,
			'description' => __( 'An plugin object', 'wp-graphql' ),
			'fields' => self::fields(),
			'interfaces' => [ self::node_interface() ],
		];

		parent::__construct( $config );

	}

	/**
	 * This defines the fields for the PluginType. The fields are passed through a filter so the shape of the schema
	 * can be modified, for example to add entry points to Types that are unique to certain plugins.
	 *
	 * @return mixed
	 * @since 0.0.5
	 */
	private static function fields() {

		if ( null === self::$fields ) {
			self::$fields = function() {
				$fields = [
					'id'          => [
						'type'    => Types::non_null( Types::id() ),
						'resolve' => function( array $plugin, $args, AppContext $context, ResolveInfo $info ) {
							return ( ! empty( $plugin ) && ! empty( $plugin['Name'] ) ) ? Relay::toGlobalId( 'plugin', $plugin['Name'] ) : null;
						},
					],
					'name'        => [
						'type'        => Types::string(),
						'description' => __( 'Display name of the plugin.', 'wp-graphql' ),
						'resolve'     => function( array $plugin, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $plugin['Name'] ) ? $plugin['Name'] : '';
						},
					],
					'pluginUri'   => [
						'type'        => Types::string(),
						'description' => __( 'URI for the plugin website. This is useful for directing users for support requests etc.', 'wp-graphql' ),
						'resolve'     => function( array $plugin, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $plugin['PluginURI'] ) ? $plugin['PluginURI'] : '';
						},
					],
					'description' => [
						'type'        => Types::string(),
						'description' => __( 'Description of the plugin.', 'wp-graphql' ),
						'resolve'     => function( array $plugin, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $plugin['Description'] ) ? $plugin['Description'] : '';
						},
					],
					'author'      => [
						'type'        => Types::string(),
						'description' => __( 'Name of the plugin author(s), may also be a company name.', 'wp-graphql' ),
						'resolve'     => function( array $plugin, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $plugin['Author'] ) ? $plugin['Author'] : '';
						},
					],
					'authorUri'   => [
						'type'        => Types::string(),
						'description' => __( 'URI for the related author(s)/company website.', 'wp-graphql' ),
						'resolve'     => function( array $plugin, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $plugin['AuthorURI'] ) ? $plugin['AuthorURI'] : '';
						},
					],
					'version'     => [
						'type'        => Types::string(),
						'description' => __( 'Current version of the plugin.', 'wp-graphql' ),
						'resolve'     => function( array $plugin, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $plugin['Version'] ) ? $plugin['Version'] : '';
						},
					],
				];

				/**
				 * Pass the fields through a filter to allow for hooking in and adjusting the shape
				 * of the type's schema
				 *
				 * @since 0.0.5
				 */
				return self::prepare_fields( $fields, self::$type_name );

			};
		}
		return self::$fields;
	}

}
