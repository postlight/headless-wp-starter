<?php

namespace WPGraphQL\Type\PostObject\Mutation;

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\Types;

class TermObjectDelete {

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
			! empty( $taxonomy->graphql_single_name ) &&
			empty( self::$mutation[ $taxonomy->graphql_single_name ] )
		) {

			/**
			 * Set the name of the mutation being performed
			 */
			$mutation_name = 'Delete' . ucwords( $taxonomy->graphql_single_name );

			self::$mutation[ $taxonomy->graphql_single_name ] = Relay::mutationWithClientMutationId( [
				'name'                => esc_html( $mutation_name ),
				// Translators: The placeholder is the taxonomy name of the term being deleted
				'description'         => sprintf( esc_html__( 'Delete %1$s objects', 'wp-graphql' ), $taxonomy->graphql_single_name ),
				'inputFields'         => [
					'id' => [
						'type'        => Types::non_null( Types::id() ),
						// translators: The placeholder is the name of the taxonomy for the term being deleted
						'description' => sprintf( __( 'The ID of the %1$s to delete', 'wp-graphql' ), $taxonomy->graphql_single_name ),
					],
				],
				'outputFields'        => [
					'deletedId'                    => [
						'type'        => Types::id(),
						'description' => __( 'The ID of the deleted object', 'wp-graphql' ),
						'resolve'     => function( $payload ) use ( $taxonomy ) {
							$deleted = (object) $payload['termObject'];

							return ! empty( $deleted->term_id ) ? Relay::toGlobalId( $taxonomy->name, $deleted->term_id ) : null;
						},
					],
					$taxonomy->graphql_single_name => [
						'type'        => Types::term_object( $taxonomy->name ),
						'description' => __( 'The object before it was deleted', 'wp-graphql' ),
						'resolve'     => function( $payload ) use ( $taxonomy ) {
							$deleted = (object) $payload['termObject'];

							return ! empty( $deleted ) ? $deleted : null;
						},
					],
				],
				'mutateAndGetPayload' => function( $input ) use ( $taxonomy, $mutation_name ) {

					$id_parts = Relay::fromGlobalId( $input['id'] );

					if ( ! empty( $id_parts['id'] ) && absint( $id_parts['id'] ) ) {
						$term_id = absint( $id_parts['id'] );
					} else {
						// Translators: The placeholder is the name of the taxonomy for the term being deleted
						throw new UserError( sprintf( __( 'The ID for the %1$s was not valid', 'wp-graphql' ), $taxonomy->graphql_single_name ) );
					}

					/**
					 * Ensure the type for the Global ID matches the type being mutated
					 */
					if ( empty( $id_parts['type'] ) || $taxonomy->name !== $id_parts['type'] ) {
						// Translators: The placeholder is the name of the taxonomy for the term being edited
						throw new UserError( sprintf( __( 'The ID passed is not for a %1$s object', 'wp-graphql' ), $taxonomy->graphql_single_name ) );
					}

					/**
					 * Get the term before deleting it
					 */
					$term_object = get_term( $term_id, $taxonomy->name );

					/**
					 * Ensure the user can delete terms of this taxonomy
					 */
					if ( ! current_user_can( 'delete_term', $term_object->term_id ) ) {
						// Translators: The placeholder is the name of the taxonomy for the term being deleted
						throw new UserError( sprintf( __( 'You do not have permission to delete %1$s', 'wp-graphql' ), $taxonomy->graphql_plural_name ) );
					}

					/**
					 * Delete the term and get the response
					 */
					$deleted = wp_delete_term( $term_id, $taxonomy->name );

					/**
					 * If there was an error deleting the term, get the error message and return it
					 */
					if ( is_wp_error( $deleted ) ) {
						$error_message = $deleted->get_error_message();
						if ( ! empty( $error_message ) ) {
							throw new UserError( esc_html( $error_message ) );
						} else {
							// Translators: The placeholder is the name of the taxonomy for the term being deleted
							throw new UserError( sprintf( __( 'The %1$s failed to delete but no error was provided', 'wp-graphql' ), $taxonomy->name ) );
						}
					}

					/**
					 * Return the term object that was retrieved prior to deletion
					 */
					return [
						'termObject' => $term_object,
					];

				},
			] );

		}

		return ! empty( self::$mutation[ $taxonomy->graphql_single_name ] ) ? self::$mutation[ $taxonomy->graphql_single_name ] : null;

	}
}


