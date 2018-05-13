<?php

namespace WPGraphQL\Type\MediaItem\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Types;

/**
 * Class MediaItemCreate
 *
 * @package WPGraphQL\Type\MediaItem\Mutation
 */
class MediaItemCreate {

	/**
	 * Holds the mutation field definition
	 *
	 * @var array mutation
	 */
	private static $mutation = [];

	/**
	 * Defines the create mutation for MediaItems
	 *
	 * @var \WP_Post_Type $post_type_object
	 *
	 * @return array|mixed
	 */
	public static function mutate( \WP_Post_Type $post_type_object ) {

		/**
		 * Set the name of the mutation being performed
		 */
		$mutation_name = 'CreateMediaItem';

		self::$mutation['mediaItem'] = Relay::mutationWithClientMutationId( [
			'name' => esc_html( $mutation_name ),
			'description' => __( 'Create mediaItems', 'wp-graphql' ),
			'inputFields' => self::input_fields( $post_type_object ),
			'outputFields' => [
				'mediaItem' => [
					'type' => Types::post_object( $post_type_object->name ),
					'resolve' => function( $payload ) {
						return get_post( $payload['id'] );
					},
				],
			],
			'mutateAndGetPayload' => function( $input, AppContext $context, ResolveInfo $info ) use ( $post_type_object, $mutation_name ) {

				/**
				 * Stop now if a user isn't allowed to upload a mediaItem
				 */
				if ( ! current_user_can( 'upload_files' ) ) {
					throw new UserError( __( 'Sorry, you are not allowed to upload mediaItems', 'wp-graphql' ) );
				}

				/**
				 * Set the file name, whether it's a local file or from a URL.
				 * Then set the url for the uploaded file
				 */
				$file_name = basename( $input['filePath'] );
				$uploaded_file_url = $input['filePath'];

				/**
				 * Require the file.php file from wp-admin. This file includes the
				 * download_url and wp_handle_sideload methods
				 */
				require_once( ABSPATH . 'wp-admin/includes/file.php' );

				/**
				 * If the mediaItem file is from a local server, use wp_upload_bits before saving it to the uploads folder
				 */
				if ( 'file' === parse_url( $input['filePath'], PHP_URL_SCHEME ) ) {
					$uploaded_file = wp_upload_bits( $file_name, null, file_get_contents( $input['filePath'] ) );
					$uploaded_file_url = ( empty ( $uploaded_file['error'] ) ? $uploaded_file['url'] : null );
				}

				/**
				 * URL data for the mediaItem, timeout value is the default, see:
				 * https://developer.wordpress.org/reference/functions/download_url/
				 */
				$timeout_seconds = 300;
				$temp_file = download_url( $uploaded_file_url, $timeout_seconds );

				/**
				 * Handle the error from download_url if it occurs
				 */
				if ( is_wp_error( $temp_file ) ) {
					throw new UserError( __( 'Sorry, the URL for this file is invalid, it must be a valid URL', 'wp-graphql' ) );
				}

				/**
				 * Build the file data for side loading
				 */
				$file_data = [
					'name'     => $file_name,
					'type'     => ! empty ( $input['fileType'] ) ? $input['fileType'] : wp_check_filetype( $temp_file ),
					'tmp_name' => $temp_file,
					'error'    => 0,
					'size'     => filesize( $temp_file ),
				];

				/**
				 * Tells WordPress to not look for the POST form fields that would normally be present as
				 * we downloaded the file from a remote server, so there will be no form fields
				 * The default is true
				 */
				$overrides = [
					'test_form' => false,
				];

				/**
				 * Insert the mediaItem and retrieve it's data
				 */
				$file = wp_handle_sideload( $file_data, $overrides );

				/**
				 * Handle the error from wp_handle_sideload if it occurs
				 */
				if ( ! empty( $file['error'] ) ) {
					throw new UserError( __( 'Sorry, the URL for this file is invalid, it must be a path to the mediaItem file', 'wp-graphql' ) );
				}

				/**
				 * Insert the mediaItem object and get the ID
				 */
				$media_item_args = MediaItemMutation::prepare_media_item( $input, $post_type_object, $mutation_name, $file );

				/**
				 * Get the post parent and if it's not set, set it to false
				 */
				$attachment_parent_id = ( ! empty( $media_item_args['post_parent'] ) ? $media_item_args['post_parent'] : false );

				/**
				 * Stop now if a user isn't allowed to edit the parent post
				 */
				$parent = get_post( $attachment_parent_id );

				if ( null !== get_post( $attachment_parent_id ) ) {
					$post_parent_type = get_post_type_object( $parent->post_type );
					if ( ! current_user_can( $post_parent_type->cap->edit_post, $attachment_parent_id ) ) {
						throw new UserError( __( 'Sorry, you are not allowed to upload mediaItems to this post', 'wp-graphql' ) );
					}
				}

				/**
				 * If the mediaItem being created is being assigned to another user that's not the current user, make sure
				 * the current user has permission to edit others mediaItems
				 */
				if ( ! empty( $input['authorId'] ) && get_current_user_id() !== $input['authorId'] && ! current_user_can( $post_type_object->cap->edit_others_posts ) ) {
					throw new UserError( __( 'Sorry, you are not allowed to create mediaItems as this user', 'wp-graphql' ) );
				}

				/**
				 * Insert the mediaItem
				 *
				 * Required Argument defaults are set in the main MediaItemMutation.php if they aren't set
				 * by the user during input, they are:
				 * post_title (pulled from file if not entered)
				 * post_content (empty string if not entered)
				 * post_status (inherit if not entered)
				 * post_mime_type (pulled from the file if not entered in the mutation)
				 */
				$attachment_id = wp_insert_attachment( $media_item_args, $file['file'], $attachment_parent_id );

				/**
				 * Check if the wp_generate_attachment_metadata method exists and include it if not
				 */
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				/**
				 * Generate and update the mediaItem's metadata.
				 * If we make it this far the file and attachment
				 * have been validated and we will not receive any errors
				 */
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file['file'] );
				$attachment_data_update = wp_update_attachment_metadata( $attachment_id, $attachment_data );

				/**
				 * Update alt text postmeta for mediaItem
				 */
				MediaItemMutation::update_additional_media_item_data( $attachment_id, $input, $post_type_object, $mutation_name, $context, $info );

				return [
					'id' => $attachment_id,
				];

			},

		] );

		return ! empty( self::$mutation['mediaItem'] ) ? self::$mutation['mediaItem'] : null;
	}

	/**
	 * Add the filePath as a nonNull field for create mutations as its required
	 * to create a media item
	 *
	 * @param \WP_Post_Type $post_type_object
	 *
	 * @return array
	 */
	private static function input_fields( $post_type_object ) {

		/**
		 * Creating mutations requires a filePath to be passed
		 */
		return array_merge(
			[
				'filePath'      => [
					'type'        => Types::non_null( Types::string() ),
					'description' => __( 'The URL or file path to the mediaItem', 'wp-graphql' ),
				],
			],
			MediaItemMutation::input_fields( $post_type_object )
		);

	}
}
