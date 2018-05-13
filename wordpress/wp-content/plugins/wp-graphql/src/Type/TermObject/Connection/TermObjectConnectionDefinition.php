<?php
namespace WPGraphQL\Type\TermObject\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Types;

/**
 * Class TermObjectConnectionDefinition
 * @package WPGraphQL\Type\Comment\Connection
 * @since 0.0.5
 */
class TermObjectConnectionDefinition {

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
	 * @param object $taxonomy_object
	 * @return mixed
	 * @since 0.0.5
	 */
	public static function connection( $taxonomy_object, $from_type = 'Root' ) {

		if ( empty( self::$connection[ $from_type ][ $taxonomy_object->name ] ) ) {
			/**
			 * Setup the connectionDefinition
			 *
			 * @since 0.0.5
			 */
			$connection = Relay::connectionDefinitions( [
				'nodeType'         => Types::term_object( $taxonomy_object->name ),
				'name'             => ucfirst( $from_type ) . ucfirst( $taxonomy_object->graphql_plural_name ),
				'connectionFields' => function() use ( $taxonomy_object ) {
					return [
						'taxonomyInfo' => [
							'type'        => Types::taxonomy(),
							'description' => __( 'Information about the type of content being queried', 'wp-graphql' ),
							'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $taxonomy_object ) {
								return $taxonomy_object;
							},
						],
						'nodes'        => [
							'type'        => Types::list_of( Types::term_object( $taxonomy_object->name ) ),
							'description' => __( 'The nodes of the connection, without the edges', 'wp-graphql' ),
							'resolve'     => function( $source, $args, $context, $info ) {
								return ! empty( $source['nodes'] ) ? $source['nodes'] : [];
							},
						],
					];
				},
			] );

			/**
			 * Add the "where" args to the termObjectConnections
			 *
			 * @since 0.0.5
			 */
			$args[ $from_type ] = [
				'where' => [
					'name' => 'where',
					'type' => Types::term_object_query_args( ucfirst( $from_type ) . ucfirst( $taxonomy_object->graphql_plural_name ) ),
				],
			];

			/**
			 * Add the connection to the post_objects_connection object
			 *
			 * @since 0.0.5
			 */
			self::$connection[ $from_type ][ $taxonomy_object->name ] = [
				'type'        => $connection['connectionType'],
				'description' => sprintf( __( 'A collection of %s objects', 'wp-graphql' ), $taxonomy_object->graphql_plural_name ),
				'args'        => array_merge( Relay::connectionArgs(), $args[ $from_type ] ),
				'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $taxonomy_object ) {
					return DataSource::resolve_term_objects_connection( $source, $args, $context, $info, $taxonomy_object->name );
				},
			];
		}
		return self::$connection[ $from_type ][ $taxonomy_object->name ];
	}

}
