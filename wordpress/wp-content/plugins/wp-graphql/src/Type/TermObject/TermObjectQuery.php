<?php
namespace WPGraphQL\Type\TermObject;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Types;

/**
 * Class TermObjectQuery
 * @package WPGraphQL\Type\TermObject
 * @Since 0.0.5
 */
class TermObjectQuery {

	/**
	 * Holds the root_query field definition
	 * @var array $root_query
	 * @since 0.0.5
	 */
	private static $root_query = [];

	/**
	 * Method that returns the root query field definition for the post object type
	 *
	 * @param object $taxonomy_object
	 * @return array
	 * @since 0.0.5
	 */
	public static function root_query( $taxonomy_object ) {

		if ( ! empty( $taxonomy_object->name ) && empty( self::$root_query[ $taxonomy_object->name ] ) ) {
			self::$root_query[ $taxonomy_object->name ] = [
				'type'        => Types::term_object( $taxonomy_object->name ),
				'description' => sprintf( __( 'A % object', 'wp-graphql' ), $taxonomy_object->graphql_single_name ),
				'args'        => [
					'id' => Types::non_null( Types::id() ),
				],
				'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $taxonomy_object ) {
					$id_components = Relay::fromGlobalId( $args['id'] );

					return DataSource::resolve_term_object( $id_components['id'], $taxonomy_object->name );
				},
			];

			return self::$root_query[ $taxonomy_object->name ];
		}
	}

}
