<?php

namespace WPGraphQL\Type\User\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Types;

/**
 * Class UserUpdate
 *
 * @package WPGraphQL\Type\User\Mutation
 */
class UserUpdate {

	/**
	 * Stores the user update mutation
	 *
	 * @var array $mutation
	 */
	private static $mutation;

	/**
	 * Process the user update mutation
	 *
	 * @return array|null
	 * @access public
	 */
	public static function mutate() {

		if ( empty( self::$mutation ) ) {

			self::$mutation = Relay::mutationWithClientMutationId( [
				'name' => 'UpdateUser',
				'description' => 'Updates a user object',
				'inputFields' => self::input_fields(),
				'outputFields' => [
					'user' => [
						'type' => Types::user(),
						'description' => __( 'The updated user', 'wp-graphql' ),
						'resolve' => function( $payload ) {
							return get_user_by( 'ID', $payload['userId'] );
						}
					]
				], 'mutateAndGetPayload' => function( $input, AppContext $context, ResolveInfo $info ) {

					$id_parts      = ! empty( $input['id'] ) ? Relay::fromGlobalId( $input['id'] ) : null;
					$existing_user = get_user_by( 'ID', $id_parts['id'] );

					/**
					 * If there's no existing user, throw an exception
					 */
					if ( empty( $id_parts['id'] )  || false === $existing_user ) {
						throw new UserError( $id_parts['id'] );
					}

					if ( ! current_user_can( 'edit_users' ) ) {
						throw new UserError( __( 'You do not have the appropriate capabilities to perform this action', 'wp-graphql' ) );
					}

					$user_args = UserMutation::prepare_user_object( $input, 'userCreate' );
					$user_args['ID'] = absint( $id_parts['id'] );

					/**
					 * If the query is trying to modify the users role, but doesn't have permissions to do so, throw an exception
					 */
					if ( ! current_user_can( 'promote_users' ) && isset( $user_args['role'] ) ) {
						throw new UserError( __( 'You do not have the appropriate capabilities to change this users role.', 'wp-graphql' ) );
					}

					/**
					 * Update the user
					 */
					$user_id = wp_update_user( $user_args );

					/**
					 * Throw an exception if the post failed to create
					 */
					if ( is_wp_error( $user_id ) ) {
						$error_message = $user_id->get_error_message();
						if ( ! empty( $error_message ) ) {
							throw new UserError( esc_html( $error_message ) );
						} else {
							throw new UserError( __( 'The user failed to update but no error was provided', 'wp-graphql' ) );
						}
					}

					/**
					 * If the $user_id is empty, we should throw an exception
					 */
					if ( empty( $user_id ) ) {
						throw new UserError( __( 'The user failed to update', 'wp-graphql' ) );
					}

					/**
					 * Update additional user data
					 */
					UserMutation::update_additional_user_object_data( $user_id, $input, 'update', $context, $info );

					/**
					 * Return the new user ID
					 */
					return [
						'userId' => $user_id,
					];

				}

			] );
		}

		return ( ! empty( self::$mutation ) ) ? self::$mutation : null;

	}

	/**
	 * Add the id as a nonNull field for update mutations
	 *
	 * @return array
	 */
	private static function input_fields() {

		/**
		 * Update mutations require an ID to be passed
		 */
		return array_merge(
			[
				'id' => [
					'type'        => Types::non_null( Types::id() ),
					// translators: the placeholder is the name of the type of post object being updated
					'description' => __( 'The ID of the user', 'wp-graphql' ),
				],
			],
			UserMutation::input_fields()
		);

	}

}
