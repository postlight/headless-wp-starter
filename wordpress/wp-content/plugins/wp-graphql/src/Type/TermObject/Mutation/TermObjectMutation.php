<?php

namespace WPGraphQL\Type\TermObject\Mutation;

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\Types;

class TermObjectMutation {

	/**
	 * Holds the input_fields configuration
	 *
	 * @var array
	 */
	private static $input_fields;

	/**
	 * @param \WP_Taxonomy $taxonomy
	 *
	 * @return mixed|null
	 */
	public static function input_fields( \WP_Taxonomy $taxonomy ) {

		if ( ! empty( $taxonomy->name ) && empty( self::$input_fields[ $taxonomy->name ] ) ) {

			$input_fields = [
				'name' => [
					'type'        => Types::string(),
					// Translators: The placeholder is the name of the taxonomy for the object being mutated
					'description' => sprintf( __( 'The name of the %1$s object to mutate', 'wp-graphql' ), $taxonomy->name ),
				],
				'aliasOf'     => [
					'type'        => Types::string(),
					// Translators: The placeholder is the name of the taxonomy for the object being mutated
					'description' => sprintf( __( 'The slug that the %1$s will be an alias of', 'wp-graphql' ), $taxonomy->name ),
				],
				'description' => [
					'type'        => Types::string(),
					// Translators: The placeholder is the name of the taxonomy for the object being mutated
					'description' => sprintf( __( 'The description of the %1$s object', 'wp-graphql' ), $taxonomy->name ),
				],
				'slug'        => [
					'type'        => Types::string(),
					'description' => __( 'If this argument exists then the slug will be checked to see if it is not an existing valid term. If that check succeeds (it is not a valid term), then it is added and the term id is given. If it fails, then a check is made to whether the taxonomy is hierarchical and the parent argument is not empty. If the second check succeeds, the term will be inserted and the term id will be given. If the slug argument is empty, then it will be calculated from the term name.' ),
				],
			];

			/**
			 * Add a parentId field to hierarchical taxonomies to allow parents to be set
			 */
			if ( true === $taxonomy->hierarchical ) {
				$input_fields['parentId'] = [
					'type'        => Types::id(),
					// Translators: The placeholder is the name of the taxonomy for the object being mutated
					'description' => sprintf( __( 'The ID of the %1$s that should be set as the parent', 'wp-graphql' ), $taxonomy->name ),
				];
			}

			/**
			 * Filter the mutation input fields for the object type
			 *
			 * @param array        $input_fields The array of input fields
			 * @param \WP_Taxonomy The           taxonomy of the Term object being mutated
			 */
			self::$input_fields[ $taxonomy->name ] = apply_filters( 'graphql_term_object_mutation_input_fields', $input_fields, $taxonomy );

		} // End if().

		return ! empty( self::$input_fields[ $taxonomy->name ] ) ? self::$input_fields[ $taxonomy->name ] : null;

	}

	/**
	 * This prepares the object to be mutated – ensures data is safe to be saved,
	 * and mapped from input args to WordPress $args
	 *
	 * @param array        $input         The input from the GraphQL Request
	 * @param \WP_Taxonomy $taxonomy      The Taxonomy object for the type of term being mutated
	 * @param string       $mutation_name The name of the mutation (create, update, etc)
	 *
	 * @throws \Exception
	 *
	 * @return mixed
	 */
	public static function prepare_object( $input, \WP_Taxonomy $taxonomy, $mutation_name ) {

		/**
		 * Set the taxonomy for insert
		 */
		$insert_args['taxonomy'] = $taxonomy->name;

		/**
		 * Prepare the data for inserting the term
		 */
		if ( ! empty( $input['aliasOf'] ) ) {
			$insert_args['alias_of'] = $input['aliasOf'];
		}

		if ( ! empty( $input['name'] ) ) {
			$insert_args['name'] = esc_sql( $input['name'] );
		}

		if ( ! empty( $input['description'] ) ) {
			$insert_args['description'] = esc_sql( $input['description'] );
		}

		if ( ! empty( $input['slug'] ) ) {
			$insert_args['slug'] = esc_sql( $input['slug'] );
		}

		/**
		 * If the parentId argument was entered, we need to validate that it's actually a legit term that can
		 * be set as a parent
		 */
		if ( ! empty( $input['parentId'] ) ) {

			/**
			 * Convert parent ID to WordPress ID
			 */
			$parent_id_parts = ! empty( $input['parentId'] ) ? Relay::fromGlobalId( $input['parentId'] ) : null;

			/**
			 * Ensure that the ID passed in is a valid GlobalID
			 */
			if ( is_array( $parent_id_parts ) && ! empty( $parent_id_parts['id'] ) ) {

				/**
				 * Get the Term ID from the global ID
				 */
				$parent_id = $parent_id_parts['id'];

				/**
				 * Ensure there's actually a parent term to be associated with
				 */
				$parent_term = get_term( absint( $parent_id ), $taxonomy->name );

				if ( ! $parent_term || is_wp_error( $parent_term ) ) {
					throw new UserError( __( 'The parent does not exist', 'wp-graphql' ) );
				}

				// Otherwise set the parent as the parent term's ID
				$insert_args['parent'] = $parent_term->term_id;

			} else {
				throw new UserError( __( 'The parent ID is not a valid ID', 'wp-graphql' ) );
			} // End if().
		}

		/**
		 * Filter the $insert_args
		 *
		 * @param array $insert_args The array of input args that will be passed to the functions that insert terms
		 * @param array $input The data that was entered as input for the mutation
		 * @param \WP_Taxonomy $taxonomy The taxonomy object of the term being mutated
		 * @param string $mutation_name The name of the mutation being performed (create, edit, etc)
		 */
		$insert_args = apply_filters( 'graphql_term_object_insert_term_args', $insert_args, $input, $taxonomy, $mutation_name );

		/**
		 * Return the $args
		 */
		return $insert_args;

	}

}