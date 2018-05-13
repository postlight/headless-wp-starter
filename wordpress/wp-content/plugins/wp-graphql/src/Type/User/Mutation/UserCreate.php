<?php

namespace WPGraphQL\Type\User\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Types;

/**
 * Class UserCreate
 *
 * @package WPGraphQL\Type\User\Mutation
 */
class UserCreate {

	/**
	 * Stores the user create mutation
	 *
	 * @var array $mutation
	 * @access private
	 */
	private static $mutation;

	/**
	 * Process the user creat mutation
	 *
	 * @return array|null
	 * @access public
	 */
	public static function mutate() {

		if ( empty( self::$mutation ) ) {

			self::$mutation = Relay::mutationWithClientMutationId( [
				'name' => 'CreateUser',
				'description' => __( 'Create new user object', 'wp-graphql' ),
				'inputFields' => self::input_fields(),
				'outputFields' => [
					'user' => [
						'type' => Types::user(),
						'resolve' => function( $payload ) {
							return get_user_by( 'ID', $payload['id'] );
						}
					]
				],
				'mutateAndGetPayload' => function( $input, AppContext $context, ResolveInfo $info ) {

					if ( ! current_user_can( 'create_users' ) ) {
						throw new UserError( __( 'Sorry, you are not allowed to create a new user.', 'wp-graphql' ) );
					}

					/**
					 * Map all of the args from GQL to WP friendly
					 */
					$user_args = UserMutation::prepare_user_object( $input, 'userCreate' );

					/**
					 * Create the new user
					 */
					$user_id = wp_insert_user( $user_args );

					/**
					 * Throw an exception if the post failed to create
					 */
					if ( is_wp_error( $user_id ) ) {
						$error_message = $user_id->get_error_message();
						if ( ! empty( $error_message ) ) {
							throw new UserError( esc_html( $error_message ) );
						} else {
							throw new UserError( __( 'The object failed to create but no error was provided', 'wp-graphql' ) );
						}
					}

					/**
					 * If the $post_id is empty, we should throw an exception
					 */
					if ( empty( $user_id ) ) {
						throw new UserError( __( 'The object failed to create', 'wp-graphql' ) );
					}

					/**
					 * Update additional user data
					 */
					UserMutation::update_additional_user_object_data( $user_id, $input, 'create', $context, $info );

					/**
					 * Return the new user ID
					 */
					return [
						'id' => $user_id,
					];

				}

			] );
		}

		return ( ! empty( self::$mutation ) ) ? self::$mutation : null;

	}

	/**
	 * Add the email as a nonNull field for update mutations
	 *
	 * @return array
	 */
	private static function input_fields() {

		/**
		 * Update mutations require an ID to be passed
		 */
		return array_merge(
			UserMutation::input_fields(),
			[
				'username' => [
					'type'        => Types::non_null( Types::string() ),
					// translators: the placeholder is the name of the type of post object being updated
					'description' => __( 'A string that contains the user\'s username for logging in.', 'wp-graphql' ),
				],
			]
		);

	}

}
