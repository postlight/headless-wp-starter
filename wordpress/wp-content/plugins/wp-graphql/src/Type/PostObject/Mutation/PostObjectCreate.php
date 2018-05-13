<?php

namespace WPGraphQL\Type\PostObject\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

/**
 * Class PostObjectCreate
 *
 * @package WPGraphQL\Type\PostObject\Mutation
 */
class PostObjectCreate {

	/**
	 * Holds the mutation field definition
	 *
	 * @var array $mutation
	 */
	private static $mutation = [];

	/**
	 * Defines the create mutation for PostTypeObjects
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
			$mutation_name = 'Create' . ucwords( $post_type_object->graphql_single_name );

			self::$mutation[ $post_type_object->graphql_single_name ] = Relay::mutationWithClientMutationId( [
				'name'                => $mutation_name,
				// translators: The placeholder is the name of the object type
				'description'         => sprintf( __( 'Create %1$s objects', 'wp-graphql' ), $post_type_object->graphql_single_name ),
				'inputFields'         => WPInputObjectType::prepare_fields( PostObjectMutation::input_fields( $post_type_object ), $mutation_name ),
				'outputFields'        => [
					$post_type_object->graphql_single_name => [
						'type'    => Types::post_object( $post_type_object->name ),
						'resolve' => function( $payload ) {
							return get_post( $payload['id'] );
						},
					],
				],
				'mutateAndGetPayload' => function( $input, AppContext $context, ResolveInfo $info ) use ( $post_type_object, $mutation_name ) {

					/**
					 * Throw an exception if there's no input
					 */
					if ( ( empty( $post_type_object->name ) ) || ( empty( $input ) || ! is_array( $input ) ) ) {
						throw new UserError( __( 'Mutation not processed. There was no input for the mutation or the post_type_object was invalid', 'wp-graphql' ) );
					}

					/**
					 * Stop now if a user isn't allowed to create a post
					 */
					if ( ! current_user_can( $post_type_object->cap->create_posts ) ) {
						// translators: the $post_type_object->graphql_plural_name placeholder is the name of the object being mutated
						throw new UserError( sprintf( __( 'Sorry, you are not allowed to create %1$s', 'wp-graphql' ), $post_type_object->graphql_plural_name ) );
					}

					/**
					 * If the post being created is being assigned to another user that's not the current user, make sure
					 * the current user has permission to edit others posts for this post_type
					 */
					if ( ! empty( $input['authorId'] ) && get_current_user_id() !== $input['authorId'] && ! current_user_can( $post_type_object->cap->edit_others_posts ) ) {
						// translators: the $post_type_object->graphql_plural_name placeholder is the name of the object being mutated
						throw new UserError( sprintf( __( 'Sorry, you are not allowed to create %1$s as this user', 'wp-graphql' ), $post_type_object->graphql_plural_name ) );
					}

					/**
					 * @todo: When we support assigning terms and setting posts as "sticky" we need to check permissions
					 * @see :https://github.com/WordPress/WordPress/blob/e357195ce303017d517aff944644a7a1232926f7/wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php#L504-L506
					 * @see : https://github.com/WordPress/WordPress/blob/e357195ce303017d517aff944644a7a1232926f7/wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php#L496-L498
					 */

					/**
					 * insert the post object and get the ID
					 */
					$post_args = PostObjectMutation::prepare_post_object( $input, $post_type_object, $mutation_name );

					/**
					 * Filter the default post status to use when the post is initially created. Pass through a filter to
					 * allow other plugins to override the default (for example, Edit Flow, which provides control over
					 * customizing stati or various E-commerce plugins that make heavy use of custom stati)
					 *
					 * @param string        $default_status   The default status to be used when the post is initially inserted
					 * @param \WP_Post_Type $post_type_object The Post Type that is being inserted
					 * @param string        $mutation_name    The name of the mutation currently in progress
					 */
					$default_post_status = apply_filters( 'graphql_post_object_create_default_post_status', 'draft', $post_type_object, $mutation_name );

					/**
					 * We want to cache the "post_status" and set the status later. We will set the initial status
					 * of the inserted post as the default status for the site, allow side effects to process with the
					 * inserted post (set term object connections, set meta input, sideload images if necessary, etc)
					 * Then will follow up with setting the status as what it was declared to be later
					 */
					$intended_post_status = ! empty( $post_args['post_status'] ) ? $post_args['post_status'] : $default_post_status;

					/**
					 * Set the post_status as the default for the initial insert. The intended $post_status will be set after
					 * side effects are complete.
					 */
					$post_args['post_status'] = $default_post_status;

					/**
					 * Insert the post and retrieve the ID
					 */
					$post_id = wp_insert_post( wp_slash( (array) $post_args ), true );

					/**
					 * Throw an exception if the post failed to create
					 */
					if ( is_wp_error( $post_id ) ) {
						$error_message = $post_id->get_error_message();
						if ( ! empty( $error_message ) ) {
							throw new UserError( esc_html( $error_message ) );
						} else {
							throw new UserError( __( 'The object failed to create but no error was provided', 'wp-graphql' ) );
						}
					}

					/**
					 * If the $post_id is empty, we should throw an exception
					 */
					if ( empty( $post_id ) ) {
						throw new UserError( __( 'The object failed to create', 'wp-graphql' ) );
					}

					/**
					 * This updates additional data not part of the posts table (postmeta, terms, other relations, etc)
					 *
					 * The input for the postObjectMutation will be passed, along with the $new_post_id for the
					 * postObject that was created so that relations can be set, meta can be updated, etc.
					 */
					PostObjectMutation::update_additional_post_object_data( $post_id, $input, $post_type_object, $mutation_name, $context, $info, $default_post_status, $intended_post_status );

					/**
					 * Determine whether the intended status should be set or not.
					 *
					 * By filtering to false, the $intended_post_status will not be set at the completion of the mutation.
					 *
					 * This allows for side-effect actions to set the status later. For example, if a post
					 * was being created via a GraphQL Mutation, the post had additional required assets, such as images
					 * that needed to be sideloaded or some other semi-time-consuming side effect, those actions could
					 * be deferred (cron or whatever), and when those actions complete they could come back and set
					 * the $intended_status.
					 *
					 * @param boolean       $should_set_intended_status Whether to set the intended post_status or not. Default true.
					 * @param \WP_Post_Type $post_type_object           The Post Type Object for the post being mutated
					 * @param string        $mutation_name              The name of the mutation currently in progress
					 * @param AppContext    $context                    The AppContext passed down to all resolvers
					 * @param ResolveInfo   $info                       The ResolveInfo passed down to all resolvers
					 * @param string        $intended_post_status       The intended post_status the post should have according to the mutation input
					 * @param string        $default_post_status        The default status posts should use if an intended status wasn't set
					 */
					$should_set_intended_status = apply_filters( 'graphql_post_object_create_should_set_intended_post_status', true, $post_type_object, $mutation_name, $context, $info, $intended_post_status, $default_post_status );

					/**
					 * If the intended post status and the default post status are not the same,
					 * update the post with the intended status now that side effects are complete.
					 */
					if ( $intended_post_status !== $default_post_status && true === $should_set_intended_status ) {

						/**
						 * If the post was deleted by a side effect action before getting here,
						 * don't proceed.
						 */
						if ( ! $new_post = get_post( $post_id ) ) {
							throw new UserError( sprintf( __( 'The status of the post could not be set', 'wp-graphql' ) ) );
						}

						/**
						 * If the $intended_post_status is different than the current status of the post
						 * proceed and update the status.
						 */
						if ( $intended_post_status !== $new_post->post_status ) {
							$update_args = [
								'ID'          => $post_id,
								'post_status' => $intended_post_status,
								// Prevent the post_date from being reset if the date was included in the create post $args
								// see: https://core.trac.wordpress.org/browser/tags/4.9/src/wp-includes/post.php#L3637
								'edit_date'   => ! empty( $post_args['post_date'] ) ? $post_args['post_date'] : false,
							];

							wp_update_post( $update_args );
						}

					}

					/**
					 * Return the post object
					 */
					return [
						'id' => $post_id,
					];
				},

			] );

		}

		return ! empty( self::$mutation[ $post_type_object->graphql_single_name ] ) ? self::$mutation[ $post_type_object->graphql_single_name ] : null;

	}

}
