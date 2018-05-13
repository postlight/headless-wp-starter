<?php

namespace WPGraphQL\Type\TermObject\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Types;

class TermObjectCreate {

	/**
	 * Holds the mutation field definition
	 *
	 * @var array $mutation
	 */
	private static $mutation = [];

	/**
	 * Defines the create mutation for TermObjects
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
			$mutation_name = 'Create' . ucwords( $taxonomy->graphql_single_name );

			self::$mutation[ $taxonomy->graphql_single_name ] = Relay::mutationWithClientMutationId( [
				'name'                => esc_html( $mutation_name ),
				// translators: The placeholder is the name of the object type
				'description'         => sprintf( __( 'Create %1$s objects', 'wp-graphql' ), $taxonomy->name ),
				'inputFields'         => self::input_fields( $taxonomy ),
				'outputFields'        => [
					$taxonomy->graphql_single_name => [
						'type'        => Types::term_object( $taxonomy->name ),
						// translators: Placeholder is the name of the taxonomy
						'description' => sprintf( __( 'The created %s', 'wp-graphql' ), $taxonomy->name ),
						'resolve'     => function( $payload ) use ( $taxonomy ) {
							return get_term( $payload['id'], $taxonomy->name );
						},
					],
				],
				'mutateAndGetPayload' => function( $input, AppContext $context, ResolveInfo $info ) use ( $taxonomy, $mutation_name ) {

					/**
					 * Ensure the user can edit_terms
					 */
					if ( ! current_user_can( $taxonomy->cap->edit_terms ) ) {
						// translators: the $taxonomy->graphql_plural_name placeholder is the name of the object being mutated
						throw new UserError( sprintf( __( 'Sorry, you are not allowed to create %1$s', 'wp-graphql' ), $taxonomy->graphql_plural_name ) );
					}

					/**
					 * Prepare the object for insertion
					 */
					$args = TermObjectMutation::prepare_object( $input, $taxonomy, $mutation_name );

					/**
					 * Ensure a name was provided
					 */
					if ( empty( $args['name'] ) ) {
						// Translators: The placeholder is the name of the taxonomy of the term being mutated
						throw new UserError( sprintf( __( 'A name is required to create a %1$s' ), $taxonomy->name ) );
					}

					/**
					 * Insert the term
					 */
					$term = wp_insert_term( wp_slash( $args['name'] ), $taxonomy->name, wp_slash( (array) $args ) );

					/**
					 * If it was an error, return the message as an exception
					 */
					if ( is_wp_error( $term ) ) {
						$error_message = $term->get_error_message();
						if ( ! empty( $error_message ) ) {
							throw new UserError( esc_html( $error_message ) );
						} else {
							throw new UserError( __( 'The object failed to update but no error was provided', 'wp-graphql' ) );
						}
					}

					/**
					 * If the response to creating the term didn't respond with a term_id, throw an exception
					 */
					if ( empty( $term['term_id'] ) ) {
						throw new UserError( __( 'The object failed to create', 'wp-graphql' ) );
					}

					/**
					 * Fires after a single term is created or updated via a GraphQL mutation
					 *
					 * The dynamic portion of the hook name, `$taxonomy->name` refers to the taxonomy of the term being mutated
					 *
					 * @param int         $term_id       Inserted term object
					 * @param array       $args          The args used to insert the term
					 * @param string      $mutation_name The name of the mutation being performed
					 * @param AppContext  $context       The AppContext passed down the resolve tree
					 * @param ResolveInfo $info          The ResolveInfo passed down the resolve tree
					 */
					do_action( "graphql_insert_{$taxonomy->name}", $term['term_id'], $args, $mutation_name, $context, $info );

					return [
						'id' => $term['term_id'],
					];

				},
			] );

		}

		return ! empty( self::$mutation[ $taxonomy->graphql_single_name ] ) ? self::$mutation[ $taxonomy->graphql_single_name ] : null;
	}

	/**
	 * Add the name as a nonNull field for create mutations
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
					'type'        => Types::non_null( Types::string() ),
					// Translators: The placeholder is the name of the taxonomy for the object being mutated
					'description' => sprintf( __( 'The name of the %1$s object to mutate', 'wp-graphql' ), $taxonomy->name ),
				],
			],
			TermObjectMutation::input_fields( $taxonomy )
		);

	}

}
