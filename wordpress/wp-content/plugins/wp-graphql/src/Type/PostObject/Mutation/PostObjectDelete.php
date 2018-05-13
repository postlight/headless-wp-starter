<?php

namespace WPGraphQL\Type\PostObject\Mutation;

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\Types;

/**
 * Class PostObjectDelete
 *
 * @package WPGraphQL\Type\PostObject\Mutation
 */
class PostObjectDelete {

	/**
	 * Holds the mutation field definition
	 *
	 * @var array $mutation
	 */
	private static $mutation = [];

	/**
	 * Defines the delete mutation for PostTypeObjects
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
			$mutation_name = 'Delete' . ucwords( $post_type_object->graphql_single_name );

			self::$mutation[ $post_type_object->graphql_single_name ] = Relay::mutationWithClientMutationId( [
				'name'                => esc_html( $mutation_name ),
				// translators: The placeholder is the name of the object type
				'description'         => sprintf( __( 'Delete %1$s objects. By default %1$s objects will be moved to the trash unless the forceDelete is used', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				'inputFields'         => [
					'id'          => [
						'type'        => Types::non_null( Types::id() ),
						// translators: The placeholder is the name of the post's post_type being deleted
						'description' => sprintf( __( 'The ID of the %1$s to delete', 'wp-graphql' ), $post_type_object->graphql_single_name ),
					],
					'forceDelete' => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether the object should be force deleted instead of being moved to the trash', 'wp-graphql' ),
					],
				],
				'outputFields'        => [
					'deletedId'                            => [
						'type'        => Types::id(),
						'description' => __( 'The ID of the deleted object', 'wp-graphql' ),
						'resolve'     => function( $payload ) use ( $post_type_object ) {
							$deleted = (object) $payload['postObject'];

							return ! empty( $deleted->ID ) ? Relay::toGlobalId( $post_type_object->name, absint( $deleted->ID ) ) : null;
						},
					],
					$post_type_object->graphql_single_name => [
						'type'        => Types::post_object( $post_type_object->name ),
						'description' => __( 'The object before it was deleted', 'wp-graphql' ),
						'resolve'     => function( $payload ) {
							$deleted = (object) $payload['postObject'];

							return ! empty( $deleted ) ? $deleted : null;
						},
					],
				],
				'mutateAndGetPayload' => function( $input ) use ( $post_type_object, $mutation_name ) {

					/**
					 * Get the ID from the global ID
					 */
					$id_parts = Relay::fromGlobalId( $input['id'] );

					/**
					 * Stop now if a user isn't allowed to delete a post
					 */
					if ( ! current_user_can( $post_type_object->cap->delete_post, absint( $id_parts['id'] ) ) ) {
						// translators: the $post_type_object->graphql_plural_name placeholder is the name of the object being mutated
						throw new UserError( sprintf( __( 'Sorry, you are not allowed to delete %1$s', 'wp-graphql' ), $post_type_object->graphql_plural_name ) );
					}

					/**
					 * Check if we should force delete or not
					 */
					$force_delete = ( ! empty( $input['forceDelete'] ) && true === $input['forceDelete'] ) ? true : false;

					/**
					 * Get the post object before deleting it
					 */
					$post_before_delete = get_post( absint( $id_parts['id'] ) );

					/**
					 * If the post is already in the trash, and the forceDelete input was not passed,
					 * don't remove from the trash
					 */
					if ( 'trash' === $post_before_delete->post_status ) {
						if ( true !== $force_delete ) {
							// Translators: the first placeholder is the post_type of the object being deleted and the second placeholder is the unique ID of that object
							throw new UserError( sprintf( __( 'The %1$s with id %2$s is already in the trash. To remove from the trash, use the forceDelete input', 'wp-graphql' ), $post_type_object->graphql_single_name, $input['id'] ) );
						}
					}

					/**
					 * Delete the post
					 */
					$deleted = wp_delete_post( $id_parts['id'], $force_delete );

					/**
					 * If the post was moved to the trash, spoof the object's status before returning it
					 */
					$post_before_delete->post_status = ( false !== $deleted && true !== $force_delete ) ? 'trash' : $post_before_delete->post_status;

					/**
					 * Return the deletedId and the object before it was deleted
					 */
					return [
						'postObject' => $post_before_delete,
					];

				},
			] );

		}

		return ! empty( self::$mutation[ $post_type_object->graphql_single_name ] ) ? self::$mutation[ $post_type_object->graphql_single_name ] : null;

	}

}
