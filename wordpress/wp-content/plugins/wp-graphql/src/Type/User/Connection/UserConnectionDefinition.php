<?php
namespace WPGraphQL\Type\User\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Types;

/**
 * Class UserConnectionDefinition
 * @package WPGraphQL\Type\Comment\Connection
 * @since 0.0.5
 */
class UserConnectionDefinition {

	/**
	 * Stores some date for the Relay connection for term objects
	 *
	 * @var array $connection
	 * @since  0.0.5
	 * @access private
	 */
	private static $connection = [];

	/**
	 * Method that sets up the relay connection for term objects
	 *
	 * @param string $from_type The name of the type the connection is coming from
	 * @return mixed
	 * @since 0.0.5
	 */
	public static function connection( $from_type = 'Root' ) {

		if ( empty( self::$connection[ $from_type ] ) ) {
			$connection = Relay::connectionDefinitions( [
				'nodeType'         => Types::user(),
				'name'             => ucfirst( $from_type ) . 'Users',
				'connectionFields' => function() {
					return [
						'nodes' => [
							'type'        => Types::list_of( Types::user() ),
							'description' => __( 'The nodes of the connection, without the edges', 'wp-graphql' ),
							'resolve'     => function( $source, $args, $context, $info ) {
								return ! empty( $source['nodes'] ) ? $source['nodes'] : [];
							},
						],
					];
				},
			] );

			/**
			 * Add the "where" args to the commentConnection
			 *
			 * @since 0.0.5
			 */
			$args = [
				'where' => [
					'name' => 'where',
					'type' => Types::user_connection_query_args( ucfirst( $from_type ) . 'Users' ),
				],
			];

			self::$connection[ $from_type ] = [
				'type'        => $connection['connectionType'],
				'description' => __( 'A collection of user objects', 'wp-graphql' ),
				'args'        => array_merge( Relay::connectionArgs(), $args ),
				'resolve'     => function( $source, $args, AppContext $context, ResolveInfo $info ) {
					return DataSource::resolve_users_connection( $source, $args, $context, $info );
				},
			];
		}

		return ! empty( self::$connection[ $from_type ] ) ? self::$connection[ $from_type ] : null;

	}

}
