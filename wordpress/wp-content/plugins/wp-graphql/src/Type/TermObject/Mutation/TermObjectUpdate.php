<?php

namespace WPGraphQL\Type\TermObject\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Types;

class TermObjectUpdate {

	/**
	 * Holds the mutation field definition
	 *
	 * @var array $mutation
	 */
	private static $mutation = [];

	/**
	 * Defines the update mutation for TermObjects
	 *
	 * @param \WP_Taxonomy $taxonomy
	 *
	 * @return array|mixed
	 */
	public static function mutate( \WP_Taxonomy $taxonomy ) {

		if (
			! empty( $taxonomy->graphql_single_name )
			&& empty( self::$mutation[ $taxonomy->graphql_single_name ] )
		) {

			$mutation_name = 'Update' . ucwords( $taxonomy->graphql_single_name );

			self::$mutation[ $taxonomy->graphql_single_name ] = Relay::mutationWithClientMutationId( [
				'name'                => esc_html( $mutation_name ),
				// translators: The placeholder is the name of the post type being updated
				'description'         => sprintf( esc_html__( 'Updates %1$s objects', 'wp-graphql' ), $taxonomy->graphql_single_name ),
				'inputFields'         => self::input_fields( $taxonomy ),
				'outputFields'        => [
					$taxonomy->graphql_single_name => [
						'type'    => Types::term_object( $taxonomy->name ),
						'resolve' => function( $payload ) use ( $taxonomy ) {
							return get_term( $payload['term_id'], $taxonomy->name );
						},
					],
				],
				'mutateAndGetPayload' => function( $input, AppContext $context, ResolveInfo $info ) use ( $taxonomy, $mutation_name ) {

					/**
					 * Get the ID parts
					 */
					$id_parts = ! empty( $input['id'] ) ? Relay::fromGlobalId( $input['id'] ) : null;

					/**
					 * Ensure the type for the Global ID matches the type being mutated
					 */
					if ( empty( $id_parts['type'] ) || $taxonomy->name !== $id_parts['type'] ) {
						// Translators: The placeholder is the name of the taxonomy for the term being edited
						throw new UserError( sprintf( __( 'The ID passed is not for a %1$s object', 'wp-graphql' ), $taxonomy->graphql_single_name ) );
					}

					/**
					 * Get the existing term
					 */
					$existing_term = get_term( absint( $id_parts['id'] ), $taxonomy->name );

					/**
					 * If there was an error getting the existing term, return the error message
					 */
					if ( is_wp_error( $existing_term ) ) {
						$error_message = $existing_term->get_error_message();
						if ( ! empty( $error_message ) ) {
							throw new UserError( esc_html( $error_message ) );
						} else {
							// Translators: The placeholder is the name of the taxonomy for the term being deleted
							throw new UserError( sprintf( __( 'The %1$s failed to update', 'wp-graphql' ), $taxonomy->name ) );
						}
					}

					/**
					 * Ensure the user has permission to edit terms
					 */
					if ( ! current_user_can( 'edit_term', $existing_term->term_id ) ) {
						// Translators: The placeholder is the name of the taxonomy for the term being deleted
						throw new UserError( sprintf( __( 'You do not have permission to update %1$s', 'wp-graphql' ), $taxonomy->graphql_plural_name ) );
					}

					/**
					 * Prepare the $args for mutation
					 */
					$args = TermObjectMutation::prepare_object( $input, $taxonomy, $mutation_name );

					if ( ! empty( $args ) ) {

						/**
						 * Update the term
						 */
						$update = wp_update_term( $existing_term->term_id, $taxonomy->name, wp_slash( (array) $args ) );

						/**
						 * Respond with any errors
						 */
						if ( is_wp_error( $update ) ) {
							// Translators: the placeholder is the name of the taxonomy
							throw new UserError( sprintf( __( 'The %1$s failed to update', 'wp-graphql' ), $taxonomy->name ) );
						}
					}

					/**
					 * Fires an action when a term is updated via a GraphQL Mutation
					 *
					 * @param int         $term_id       The ID of the term object that was mutated
					 * @param array       $args          The args used to update the term
					 * @param string      $mutation_name The name of the mutation being performed (create, update, delete, etc)
					 * @param AppContext  $context       The AppContext passed down the resolve tree
					 * @param ResolveInfo $info          The ResolveInfo passed down the resolve tree
					 */
					do_action( "graphql_update_{$taxonomy->name}", $existing_term->term_id, $args, $mutation_name, $context, $info );

					/**
					 * Return the payload
					 */
					return [
						'term_id' => $existing_term->term_id,
					];

				},
			] );

		}

		return ! empty( self::$mutation[ $taxonomy->graphql_single_name ] ) ? self::$mutation[ $taxonomy->graphql_single_name ] : null;

	}

	/**
	 * Add the id as an optional field for update mutations
	 *
	 * @param \WP_Taxonomy $taxonomy
	 *
	 * @return array
	 */
	private static function input_fields( $taxonomy ) {

		/**
		 * Add name as a non_null field for term creation
		 */
		return array_merge(
			[
				'name' => [
					'type'        => Types::string(),
					// Translators: The placeholder is the name of the taxonomy for the object being mutated
					'description' => sprintf( __( 'The name of the %1$s object to mutate', 'wp-graphql' ), $taxonomy->name ),
				],
				'id'   => [
					'type'        => Types::non_null( Types::id() ),
					// Translators: The placeholder is the taxonomy of the term being updated
					'description' => sprintf( __( 'The ID of the %1$s object to update', 'wp-graphql' ), $taxonomy->graphql_single_name ),
				],
			],
			TermObjectMutation::input_fields( $taxonomy )
		);

	}

}
