<?php

namespace WPGraphQL\Type\MediaItem\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Types;

/**
 * Class MediaItemUpdate
 *
 * @package WPGraphQL\Type\PostObject\Mutation
 */
class MediaItemUpdate {

	/**
	 * Holds the mutation field definition
	 *
	 * @var array $mutation
	 */
	private static $mutation = [];

	/**
	 * Defines the update mutation for MediaItems
	 *
	 * @param \WP_Post_Type $post_type_object
	 *
	 * @return array|mixed
	 */
	public static function mutate( \WP_Post_Type $post_type_object ) {

		/**
		 * Set the name of the mutation being performed
		 */
		$mutation_name = 'UpdateMediaItem';

		self::$mutation['mediaItem'] = Relay::mutationWithClientMutationId([
			'name'                => esc_html( $mutation_name ),
			'description'         => __( 'Updates mediaItem objects', 'wp-graphql' ),
			'inputFields'         => self::input_fields( $post_type_object ),
			'outputFields'        => [
				'mediaItem' => [
					'type'    => Types::post_object( $post_type_object->name ),
					'resolve' => function( $payload ) {
						return get_post( $payload['postObjectId'] );
					},
				],
			],
			'mutateAndGetPayload' => function( $input, AppContext $context, ResolveInfo $info ) use ( $post_type_object, $mutation_name ) {

				$id_parts      = ! empty( $input['id'] ) ? Relay::fromGlobalId( $input['id'] ) : null;
				$existing_media_item = get_post( absint( $id_parts['id'] ) );

				/**
				 * If there's no existing mediaItem, throw an exception
				 */
				if ( null === $existing_media_item ) {
					throw new UserError( __( 'No mediaItem with that ID could be found to update', 'wp-graphql' ) );
				} else {
					$author_id = $existing_media_item->post_author;
				}

				/**
				 * Stop now if the post isn't a mediaItem
				 */
				if ( $post_type_object->name !== $existing_media_item->post_type ) {
					// translators: The placeholder is the ID of the mediaItem being edited
					throw new UserError( sprintf( __( 'The id %1$d is not of the type mediaItem', 'wp-graphql' ), $id_parts['id'] ) );
				}

				/**
				 * Stop now if a user isn't allowed to edit mediaItems
				 */
				if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
					throw new UserError( __( 'Sorry, you are not allowed to update mediaItems', 'wp-graphql' ) );
				}

				/**
				 * If the mutation is setting the author to be someone other than the user making the request
				 * make sure they have permission to edit others posts
				 **/
				if ( ! empty( $input['authorId'] ) ) {
					$author_id_parts = Relay::fromGlobalId( $input['authorId'] );
					$author_id = $author_id_parts['id'];
				}

				/**
				 * Check to see if the existing_media_item author matches the current user,
				 * if not they need to be able to edit others posts to proceed
				 */
				if ( get_current_user_id() !== $author_id && ! current_user_can( $post_type_object->cap->edit_others_posts ) ) {
					throw new UserError( __( 'Sorry, you are not allowed to update mediaItems as this user.', 'wp-graphql' ) );
				}

				/**
				 * insert the post object and get the ID
				 */
				$post_args = MediaItemMutation::prepare_media_item( $input, $post_type_object, $mutation_name, false );
				$post_args['ID'] = absint( $id_parts['id'] );
				$post_args['post_author'] = $author_id;

				/**
				 * Insert the post and retrieve the ID
				 *
				 * This will not fail as long as we have an ID in $post_args
				 * Thanks to the validation above we will always have the ID
				 */
				$post_id = wp_update_post( wp_slash( (array) $post_args ), true );

				/**
				 * This updates additional data not part of the posts table (postmeta, terms, other relations, etc)
				 *
				 * The input for the postObjectMutation will be passed, along with the $new_post_id for the
				 * postObject that was updated so that relations can be set, meta can be updated, etc.
				 */
				MediaItemMutation::update_additional_media_item_data( $post_id, $input, $post_type_object, $mutation_name, $context, $info );

				/**
				 * Return the payload
				 */
				return [
					'postObjectId' => $post_id,
				];

			},
		]);

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
			MediaItemMutation::input_fields( $post_type_object )
		);

	}

}
