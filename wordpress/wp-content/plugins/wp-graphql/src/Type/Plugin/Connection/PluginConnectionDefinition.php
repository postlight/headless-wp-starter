<?php
namespace WPGraphQL\Type\Plugin\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Types;

/**
 * Class CommentConnectionDefinition
 * @package WPGraphQL\Type\Comment\Connection
 * @since 0.0.5
 */
class PluginConnectionDefinition {

	/**
	 * @var array connection
	 * @since 0.0.5
	 */
	private static $connection = [];

	/**
	 * connection
	 * This sets up a connection of plugins
	 * @return mixed
	 * @since 0.0.5
	 */
	public static function connection( $from_type = 'Root' ) {

		if ( empty( self::$connection[ $from_type ] ) ) {

			/**
			 * Setup the connectionDefinition
			 *
			 * @since 0.0.5
			 */
			$connection = Relay::connectionDefinitions( [
				'nodeType'         => Types::plugin(),
				'name'             => 'Plugins',
				'connectionFields' => function() {
					return [
						'nodes' => [
							'type'        => Types::list_of( Types::plugin() ),
							'description' => __( 'The nodes of the connection, without the edges', 'wp-graphql' ),
							'resolve'     => function( $source, $args, $context, $info ) {
								return ! empty( $source['nodes'] ) ? $source['nodes'] : [];
							},
						],
					];
				},
			] );

			/**
			 * Add the connection to the post_objects_connection object
			 *
			 * @since 0.0.5
			 */
			self::$connection[ $from_type ] = [
				'type'        => $connection['connectionType'],
				'description' => __( 'A collection of plugins', 'wp-graphql' ),
				'args'        => Relay::connectionArgs(),
				'resolve'     => function( $source, $args, AppContext $context, ResolveInfo $info ) {
					return DataSource::resolve_plugins_connection( $source, $args, $context, $info );
				},
			];

		}

		return ! empty( self::$connection[ $from_type ] ) ? self::$connection[ $from_type ] : null;

	}

}
