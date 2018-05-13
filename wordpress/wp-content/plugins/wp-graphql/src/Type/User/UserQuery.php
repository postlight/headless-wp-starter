<?php
namespace WPGraphQL\Type\User;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Types;

/**
 * Class UserQuery
 * @package WPGraphQL\Type\TermObject
 * @Since 0.0.5
 */
class UserQuery {

	/**
	 * Holds the root_query field definition
	 * @var array $root_query
	 * @since 0.0.5
	 */
	private static $root_query;

	/**
	 * Method that returns the root query field definition for the post object type
	 *
	 * @return array
	 * @since 0.0.5
	 */
	public static function root_query() {

		if ( null === self::$root_query ) {
			self::$root_query = [
				'type'        => Types::user(),
				'description' => __( 'Returns a user', 'wp-graphql' ),
				'args'        => [
					'id' => Types::non_null( Types::id() ),
				],
				'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$id_components = Relay::fromGlobalId( $args['id'] );

					return DataSource::resolve_user( $id_components['id'] );
				},
			];
		}
		return self::$root_query;
	}

}
