<?php
namespace WPGraphQL\Type\PostObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

/**
 * Class PostObjectQuery
 * @package WPGraphQL\Type\PostObject
 * @Since 0.0.5
 */
class PostObjectQuery {

	/**
	 * Holds the root_query field definition
	 * @var array $root_query
	 * @since 0.0.5
	 */
	private static $root_query = [];

	/**
	 * Holds the definition of the $post_object_by field
	 * @var array $post_object_by
	 */
	private static $post_object_by = [];

	/**
	 * Holds the definition for the args that can be input on the $post_object_by field
	 * @var array $post_object_by_args
	 */
	private static $post_object_by_args = [];

	/**
	 * Method that returns the root query field definition for the post object type
	 *
	 * @param object $post_type_object
	 * @return array
	 * @since 0.0.5
	 */
	public static function root_query( $post_type_object ) {

		if ( ! empty( $post_type_object->name ) && empty( self::$root_query[ $post_type_object->name ] ) ) {
			self::$root_query[ $post_type_object->name ] = [
				'type'        => Types::post_object( $post_type_object->name ),
				'description' => sprintf( __( 'A % object', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				'args'        => [
					'id' => Types::non_null( Types::id() ),
				],
				'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $post_type_object ) {
					$id_components = Relay::fromGlobalId( $args['id'] );

					return DataSource::resolve_post_object( $id_components['id'], $post_type_object->name );
				},
			];
		}

		return ! empty( self::$root_query[ $post_type_object->name ] ) ? self::$root_query[ $post_type_object->name ] : null;
	}
	
	/**
	 * Method that returns the "post_object_by" field definition to get a post object by id, postId or slug.
	 *
	 * @param \WP_Post_Type $post_type_object
	 * @return array
	 */
	public static function post_object_by( \WP_Post_Type $post_type_object ) {

		if ( ! empty( $post_type_object->name ) && empty( self::$post_object_by[ $post_type_object->name ] ) ) {
			self::$post_object_by[ $post_type_object->name ] = [
				'type'        => Types::post_object( $post_type_object->name ),
				'description' => sprintf( __( 'A %s object', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				'args'        => self::post_object_by_args( $post_type_object ),
				'resolve'     => function( $source, array $args, AppContext $context, ResolveInfo $info ) use ( $post_type_object ) {

					$post_object = null;

					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql' ) );
						}
						$post_object = DataSource::resolve_post_object( absint( $id_components['id'] ), $post_type_object->name );
					} elseif ( ! empty( $args[ $post_type_object->graphql_single_name . 'Id' ] ) ) {
						$id          = $args[ $post_type_object->graphql_single_name . 'Id' ];
						$post_object = DataSource::resolve_post_object( $id, $post_type_object->name );
					} elseif ( ! empty( $args['uri'] ) ) {
						$uri         = esc_html( $args['uri'] );
						$post_object = DataSource::get_post_object_by_uri( $uri, 'OBJECT', $post_type_object->name );
					} elseif ( ! empty( $args['slug'] ) ) {
						$slug        = esc_html( $args['slug'] );
						$post_object = DataSource::get_post_object_by_uri( $slug, 'OBJECT', $post_type_object->name );
					}

					if ( empty( $post_object ) || is_wp_error( $post_object ) ) {
						throw new UserError( __( 'No resource could be found', 'wp-graphql' ) );
					}

					if ( ! $post_object instanceof \WP_Post ) {
						throw new UserError( __( 'The queried resource is not valid', 'wp-graphql' ) );
					}

					if ( $post_type_object->name !== $post_object->post_type ) {
						throw new UserError( __( 'The queried resource is not the correct type', 'wp-graphql' ) );
					}

					return $post_object;

				},
			];
		}
		return ! empty( self::$post_object_by[ $post_type_object->name ] ) ? self::$post_object_by[ $post_type_object->name ] : null;
	}

	/**
	 * Define the args to be used by the $postObject.By field
	 * @param \WP_Post_Type $post_type_object
	 *
	 * @return mixed
	 */
	public static function post_object_by_args( \WP_Post_Type $post_type_object ) {

		if ( empty( self::$post_object_by_args[ ucfirst( $post_type_object->name ) . 'ByArgs' ] ) ) {

			$args = [
				'id' => [
					'type'        => Types::string(),
					'description' => sprintf( __( 'Get the object by it\'s global ID', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				],
				$post_type_object->graphql_single_name . 'Id' => [
					'type'        => Types::int(),
					'description' => sprintf( __( 'Get the %s by it\'s database ID', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				],
				'uri' => [
					'type'        => Types::string(),
					'description' => sprintf( __( 'Get the %s by it\'s uri', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				]
			];

			if ( false === $post_type_object->hierarchical ) {
				$args['slug'] = [
					'type' => Types::string(),
					'description' => sprintf( __( 'Get the %s by it\'s slug (only available for non-hierarchical types)', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				];
			}

			self::$post_object_by_args[ $post_type_object->name . 'ByArgs' ] = WPInputObjectType::prepare_fields( $args, ucfirst( $post_type_object->name . 'ByArgs' ) );

		}

		return self::$post_object_by_args[ $post_type_object->name . 'ByArgs' ];

	}

}
