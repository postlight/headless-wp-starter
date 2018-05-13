<?php

namespace WPGraphQL\Type\PostObject\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

/**
 * Class PostObjectUpdate
 *
 * @package WPGraphQL\Type\PostObject\Mutation
 */
class PostObjectUpdate {

	/**
	 * Holds the mutation field definition
	 *
	 * @var array $mutation
	 */
	private static $mutation = [];

	/**
	 * Defines the Update mutation for PostTypeObjects
	 *
	 * @param \WP_Post_Type $post_type_object
	 *
	 * @return array|mixed
	 */
	public static function mutate( \WP_Post_Type $post_type_object ) {

		if (
			! empty( $post_type_object->graphql_single_name ) &&
			empty( self::$mutation[ $post_type_object->graphql_single_name ] )
		) {

			/**
			 * Set the name of the mutation being performed
			 */
			$mutation_name = 'Update' . ucwords( $post_type_object->graphql_single_name );

			self::$mutation[ $post_type_object->graphql_single_name ] = Relay::mutationWithClientMutationId( [
				'name'                => $mutation_name,
				// translators: The placeholder is the name of the post type being updated
				'description'         => sprintf( __( 'Updates %1$s objects', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				'inputFields'         => WPInputObjectType::prepare_fields( self::input_fields( $post_type_object ), $mutation_name ),
				'outputFields'        => [
					$post_type_object->graphql_single_name => [
						'type'    => Types::post_object( $post_type_object->name ),
						'resolve' => function( $payload ) {
							return get_post( $payload['postObjectId'] );
						},
					],
				],
				'mutateAndGetPayload' => function( $input, AppContext $context, ResolveInfo $info ) use ( $post_type_object, $mutation_name ) {

					$id_parts      = ! empty( $input['id'] ) ? Relay::fromGlobalId( $input['id'] ) : null;
					$existing_post = get_post( absint( $id_parts['id'] ) );

					/**
					 * If there's no existing post, throw an exception
					 */
					if ( empty( $id_parts['id'] ) || false === $existing_post || $id_parts['type'] !== $post_type_object->name ) {
						// translators: the placeholder is the name of the type of post being updated
						throw new UserError( sprintf( __( 'No %1$s could be found to update', 'wp-graphql' ), $post_type_object->graphql_single_name ) );
					}

					if ( $post_type_object->name !== $existing_post->post_type ) {
						// translators: The first placeholder is an ID and the second placeholder is the name of the post type being edited
						throw new UserError( sprintf( __( 'The id %1$d is not of the type "%2$s"', 'wp-graphql' ), $id_parts['id'], $post_type_object->name ) );
					}

					/**
					 * Stop now if a user isn't allowed to edit posts
					 */
					if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
						// translators: the $post_type_object->graphql_single_name placeholder is the name of the object being mutated
						throw new UserError( sprintf( __( 'Sorry, you are not allowed to update a %1$s', 'wp-graphql' ), $post_type_object->graphql_single_name ) );
					}

					/**
					 * If the mutation is setting the author to be someone other than the user making the request
					 * make sure they have permission to edit others posts
					 */
					$author_id_parts = ! empty( $input['authorId'] ) ? Relay::fromGlobalId( $input['authorId'] ) : null;
					if ( ! empty( $author_id_parts['id'] ) && get_current_user_id() !== $author_id_parts['id'] && ! current_user_can( $post_type_object->cap->edit_others_posts ) ) {
						// translators: the $post_type_object->graphql_single_name placeholder is the name of the object being mutated
						throw new UserError( sprintf( __( 'Sorry, you are not allowed to update %1$s as this user.', 'wp-graphql' ), $post_type_object->graphql_plural_name ) );
					}

					/**
					 * @todo: when we add support for making posts sticky, we should check permissions to make sure users can make posts sticky
					 * @see : https://github.com/WordPress/WordPress/blob/e357195ce303017d517aff944644a7a1232926f7/wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php#L640-L642
					 */

					/**
					 * @todo: when we add support for assigning terms to posts, we should check permissions to make sure they can assign terms
					 * @see : https://github.com/WordPress/WordPress/blob/e357195ce303017d517aff944644a7a1232926f7/wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php#L644-L646
					 */

					/**
					 * insert the post object and get the ID
					 */
					$post_args       = PostObjectMutation::prepare_post_object( $input, $post_type_object, $mutation_name );
					$post_args['ID'] = absint( $id_parts['id'] );

					/**
					 * Insert the post and retrieve the ID
					 */
					$post_id = wp_update_post( wp_slash( (array) $post_args ), true );

					/**
					 * Throw an exception if the post failed to update
					 */
					if ( is_wp_error( $post_id ) ) {
						throw new UserError( __( 'The object failed to update but no error was provided', 'wp-graphql' ) );
					}

					/**
					 * Fires after a single term is created or updated via a GraphQL mutation
					 *
					 * The dynamic portion of the hook name, `$taxonomy->name` refers to the taxonomy of the term being mutated
					 *
					 * @param int    $post_id       Inserted post ID
					 * @param array  $args          The args used to insert the term
					 * @param string $mutation_name The name of the mutation being performed
					 */
					do_action( "graphql_insert_{$post_type_object->name}", $post_id, $post_args, $mutation_name );

					/**
					 * This updates additional data not part of the posts table (postmeta, terms, other relations, etc)
					 *
					 * The input for the postObjectMutation will be passed, along with the $new_post_id for the
					 * postObject that was updated so that relations can be set, meta can be updated, etc.
					 */
					PostObjectMutation::update_additional_post_object_data( $post_id, $input, $post_type_object, $mutation_name, $context, $info );

					/**
					 * Return the payload
					 */
					return [
						'postObjectId' => $post_id,
					];

				},
			] );

		}

		return ! empty( self::$mutation[ $post_type_object->graphql_single_name ] ) ? self::$mutation[ $post_type_object->graphql_single_name ] : null;

	}

	/**
	 * Add the id as a nonNull field for update mutations
	 *
	 * @param \WP_Post_Type $post_type_object
	 *
	 * @return array
	 */
	private static function input_fields( $post_type_object ) {

		/**
		 * Update mutations require an ID to be passed
		 */
		return array_merge(
			[
				'id' => [
					'type'        => Types::non_null( Types::id() ),
					// translators: the placeholder is the name of the type of post object being updated
					'description' => sprintf( __( 'The ID of the %1$s object', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				],
			],
			PostObjectMutation::input_fields( $post_type_object )
		);

	}

}
