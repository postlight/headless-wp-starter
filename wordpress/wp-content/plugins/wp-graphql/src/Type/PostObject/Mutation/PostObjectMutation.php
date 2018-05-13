<?php

namespace WPGraphQL\Type\PostObject\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

/**
 * Class PostObjectMutation
 *
 * @package WPGraphQL\Type\PostObject
 */
class PostObjectMutation {

	/**
	 * Holds the input_fields configuration
	 *
	 * @var array
	 */
	private static $input_fields = [];

	/**
	 * @param $post_type_object
	 *
	 * @return mixed|array|null $input_fields
	 */
	public static function input_fields( $post_type_object ) {

		if ( ! empty( $post_type_object->graphql_single_name ) && empty( self::$input_fields[ $post_type_object->graphql_single_name ] ) ) {

			$input_fields = [
				'authorId'      => [
					'type'        => Types::id(),
					'description' => __( 'The userId to assign as the author of the post', 'wp-graphql' ),
				],
				'commentCount'  => [
					'type'        => Types::int(),
					'description' => __( 'The number of comments. Even though WPGraphQL denotes this field as an integer, in WordPress this field should be saved as a numeric string for compatability.', 'wp-graphql' ),
				],
				'commentStatus' => [
					'type'        => Types::string(),
					'description' => __( 'The comment status for the object', 'wp-graphql' ),
				],
				'content'       => [
					'type'        => Types::string(),
					'description' => __( 'The content of the object', 'wp-graphql' ),
				],
				'date'          => [
					'type'        => Types::string(),
					'description' => __( 'The date of the object. Preferable to enter as year/month/day (e.g. 01/31/2017) as it will rearrange date as fit if it is not specified. Incomplete dates may have unintended results for example, "2017" as the input will use current date with timestamp 20:17 ', 'wp-graphql' ),
				],
				'excerpt'       => [
					'type'        => Types::string(),
					'description' => __( 'The excerpt of the object', 'wp-graphql' ),
				],
				'menuOrder'     => [
					'type'        => Types::int(),
					'description' => __( 'A field used for ordering posts. This is typically used with nav menu items or for special ordering of hierarchical content types.', 'wp-graphql' ),
				],
				'mimeType'      => [
					'type'        => Types::mime_type_enum(),
					'description' => __( 'If the post is an attachment or a media file, this field will carry the corresponding MIME type. This field is equivalent to the value of WP_Post->post_mime_type and the post_mime_type column in the `post_objects` database table.', 'wp-graphql' ),
				],
				'parentId'      => [
					'type'        => Types::id(),
					'description' => __( 'The ID of the parent object', 'wp-graphql' ),
				],
				'password'      => [
					'type'        => Types::string(),
					'description' => __( 'The password used to protect the content of the object', 'wp-graphql' ),
				],
				'pinged'        => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'URLs that have been pinged.', 'wp-graphql' ),
				],
				'pingStatus'    => [
					'type'        => Types::string(),
					'description' => __( 'The ping status for the object', 'wp-graphql' ),
				],
				'slug'          => [
					'type'        => Types::string(),
					'description' => __( 'The slug of the object', 'wp-graphql' ),
				],
				'status'        => [
					'type'        => Types::post_status_enum(),
					'description' => __( 'The status of the object', 'wp-graphql' ),
				],
				'title'         => [
					'type'        => Types::string(),
					'description' => __( 'The title of the post', 'wp-graphql' ),
				],
				'toPing'        => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'URLs queued to be pinged.', 'wp-graphql' ),
				],
			];

			/**
			 * Add inputs for connected taxonomies
			 */
			$allowed_taxonomies = \WPGraphQL::$allowed_taxonomies;
			if ( ! empty( $allowed_taxonomies ) && is_array( $allowed_taxonomies ) ) {
				foreach ( $allowed_taxonomies as $taxonomy ) {
					// If the taxonomy is in the array of taxonomies registered to the post_type
					if ( in_array( $taxonomy, get_object_taxonomies( $post_type_object->name ), true ) ) {
						$tax_object = get_taxonomy( $taxonomy );

						$node_input = new WPInputObjectType( [
							'name'        => $post_type_object->graphql_single_name . ucfirst( $tax_object->graphql_plural_name ) . 'Nodes',
							'description' => sprintf( __( 'List of %1$s to connect the %2$s to. If an ID is set, it will be used to create the connection. If not, it will look for a slug. If neither are valid existing terms, and the site is configured to allow terms to be created during post mutations, a term will be created using the Name if it exists in the input, then fallback to the slug if it exists.', 'wp-graphql' ), $tax_object->graphql_plural_name, $post_type_object->graphql_single_name ),
							'fields'      => [
								'id'          => [
									'type'        => Types::id(),
									'description' => sprintf( __( 'The ID of the %1$s. If present, this will be used to connect to the %2$s. If no existing %1$s exists with this ID, no connection will be made.', 'wp-graphql' ), $tax_object->graphql_single_name, $post_type_object->graphql_single_name ),
								],
								'slug'        => [
									'type'        => Types::string(),
									'description' => sprintf( __( 'The slug of the %1$s. If no ID is present, this field will be used to make a connection. If no existing term exists with this slug, this field will be used as a fallback to the Name field when creating a new term to connect to, if term creation is enabled as a nested mutation.', 'wp-graphql' ), $tax_object->graphql_single_name ),
								],
								'description' => [
									'type'        => Types::string(),
									'description' => sprintf( __( 'The description of the %1$s. This field is used to set a description of the %1$s if a new one is created during the mutation.', 'wp-graphql' ), $tax_object->graphql_single_name ),
								],
								'name'        => [
									'type'        => Types::string(),
									'description' => sprintf( __( 'The name of the %1$s. This field is used to create a new term, if term creation is enabled in nested mutations, and if one does not already exist with the provided slug or ID or if a slug or ID is not provided. If no name is included and a term is created, the creation will fallback to the slug field.', 'wp-graphql' ), $tax_object->graphql_single_name ),
								],
							],
						] );

						$input_fields[ $tax_object->graphql_plural_name ] = [
							'description' => sprintf( __( 'Set connections between the %1$s and %2$s', 'wp-graphql' ), $post_type_object->graphql_single_name, $tax_object->graphql_plural_name ),
							'type'        => new WPInputObjectType( [
								'name'        => ucfirst( $post_type_object->graphql_single_name ) . ucfirst( $tax_object->graphql_plural_name ),
								'description' => sprintf( __( 'Set relationships between the %1$s to %2$s', 'wp-graphql' ), $post_type_object->graphql_single_name, $tax_object->graphql_plural_name ),
								'fields'      => [
									'append' => [
										'type'        => Types::boolean(),
										'description' => sprintf( __( 'If true, this will append the %1$s to existing related %2$s. If false, this will replace existing relationships. Default true.', 'wp-graphql' ), $tax_object->graphql_single_name, $tax_object->graphql_plural_name ),
									],
									'nodes'  => [
										'type' => Types::list_of( $node_input ),
									],
								],
							] ),
						];
					}
				}
			}

			/**
			 * Filters the mutation input fields for the object type
			 *
			 * @param array         $input_fields     The array of input fields
			 * @param \WP_Post_Type $post_type_object The post_type object for the type of Post being mutated
			 */
			self::$input_fields[ $post_type_object->graphql_single_name ] = apply_filters( 'graphql_post_object_mutation_input_fields', $input_fields, $post_type_object );


		} // End if().

		return ! empty( self::$input_fields[ $post_type_object->graphql_single_name ] ) ? self::$input_fields[ $post_type_object->graphql_single_name ] : null;

	}

	/**
	 * This handles inserting the post object
	 *
	 * @param array         $input            The input for the mutation
	 * @param \WP_Post_Type $post_type_object The post_type_object for the type of post being mutated
	 * @param string        $mutation_name    The name of the mutation being performed
	 *
	 * @return array $insert_post_args
	 * @throws \Exception
	 */
	public static function prepare_post_object( $input, $post_type_object, $mutation_name ) {

		/**
		 * Set the post_type for the insert
		 */
		$insert_post_args['post_type'] = $post_type_object->name;

		/**
		 * Prepare the data for inserting the post
		 * NOTE: These are organized in the same order as: https://developer.wordpress.org/reference/functions/wp_insert_post/
		 */
		$author_id_parts = ! empty( $input['authorId'] ) ? Relay::fromGlobalId( $input['authorId'] ) : null;
		if ( is_array( $author_id_parts ) && ! empty( $author_id_parts['id'] ) && is_int( $author_id_parts['id'] ) ) {
			$insert_post_args['post_author'] = absint( $author_id_parts['id'] );
		}

		if ( ! empty( $input['date'] ) && false !== strtotime( $input['date'] ) ) {
			$insert_post_args['post_date'] = date( 'Y-m-d H:i:s', strtotime( $input['date'] ) );
		}

		if ( ! empty( $input['content'] ) ) {
			$insert_post_args['post_content'] = $input['content'];
		}

		if ( ! empty( $input['title'] ) ) {
			$insert_post_args['post_title'] = $input['title'];
		}

		if ( ! empty( $input['excerpt'] ) ) {
			$insert_post_args['post_excerpt'] = $input['excerpt'];
		}

		if ( ! empty( $input['status'] ) ) {
			$insert_post_args['post_status'] = $input['status'];
		}

		if ( ! empty( $input['commentStatus'] ) ) {
			$insert_post_args['comment_status'] = $input['commentStatus'];
		}

		if ( ! empty( $input['pingStatus'] ) ) {
			$insert_post_args['ping_status'] = $input['pingStatus'];
		}

		if ( ! empty( $input['password'] ) ) {
			$insert_post_args['post_password'] = $input['password'];
		}

		if ( ! empty( $input['slug'] ) ) {
			$insert_post_args['post_name'] = $input['slug'];
		}

		if ( ! empty( $input['toPing'] ) ) {
			$insert_post_args['to_ping'] = $input['toPing'];
		}

		if ( ! empty( $input['pinged'] ) ) {
			$insert_post_args['pinged'] = $input['pinged'];
		}

		$parent_id_parts = ! empty( $input['parentId'] ) ? Relay::fromGlobalId( $input['parentId'] ) : null;
		if ( is_array( $parent_id_parts ) && ! empty( $parent_id_parts['id'] ) && is_int( $parent_id_parts['id'] ) ) {
			$insert_post_args['post_parent'] = absint( $parent_id_parts['id'] );
		}

		if ( ! empty( $input['menuOrder'] ) ) {
			$insert_post_args['menu_order'] = $input['menuOrder'];
		}

		if ( ! empty( $input['mimeType'] ) ) {
			$insert_post_args['post_mime_type'] = $input['mimeType'];
		}

		if ( ! empty( $input['commentCount'] ) ) {
			$insert_post_args['comment_count'] = $input['commentCount'];
		}

		/**
		 * Filter the $insert_post_args
		 *
		 * @param array         $insert_post_args The array of $input_post_args that will be passed to wp_insert_post
		 * @param array         $input            The data that was entered as input for the mutation
		 * @param \WP_Post_Type $post_type_object The post_type_object that the mutation is affecting
		 * @param string        $mutation_type    The type of mutation being performed (create, edit, etc)
		 */
		$insert_post_args = apply_filters( 'graphql_post_object_insert_post_args', $insert_post_args, $input, $post_type_object, $mutation_name );

		/**
		 * Return the $args
		 */
		return $insert_post_args;

	}

	/**
	 * This updates additional data related to a post object, such as postmeta, term relationships, etc.
	 *
	 * @param int           $post_id              $post_id      The ID of the postObject being mutated
	 * @param array         $input                The input for the mutation
	 * @param \WP_Post_Type $post_type_object     The Post Type Object for the type of post being mutated
	 * @param string        $mutation_name        The name of the mutation (ex: create, update, delete)
	 * @param AppContext    $context              The AppContext passed down to all resolvers
	 * @param ResolveInfo   $info                 The ResolveInfo passed down to all resolvers
	 * @param string        $intended_post_status The intended post_status the post should have according to the
	 *                                            mutation input
	 * @param string        $default_post_status  The default status posts should use if an intended status wasn't set
	 */
	public static function update_additional_post_object_data( $post_id, $input, $post_type_object, $mutation_name, AppContext $context, ResolveInfo $info, $default_post_status = null, $intended_post_status = null ) {

		/**
		 * Sets the post lock
		 *
		 * @param int           $post_id              The ID of the postObject being mutated
		 * @param array         $input                The input for the mutation
		 * @param \WP_Post_Type $post_type_object     The Post Type Object for the type of post being mutated
		 * @param string        $mutation_name        The name of the mutation (ex: create, update, delete)
		 * @param AppContext    $context              The AppContext passed down to all resolvers
		 * @param ResolveInfo   $info                 The ResolveInfo passed down to all resolvers
		 * @param string        $intended_post_status The intended post_status the post should have according to the mutation input
		 * @param string        $default_post_status  The default status posts should use if an intended status wasn't set
		 *
		 * @return bool
		 */
		if ( true === apply_filters( 'graphql_post_object_mutation_set_edit_lock', true, $post_id, $input, $post_type_object, $mutation_name, $context, $info, $default_post_status, $intended_post_status ) ) {
			/**
			 * Set the post_lock for the $new_post_id
			 */
			self::set_edit_lock( $post_id );
		}

		/**
		 * Update the _edit_last field
		 */
		update_post_meta( $post_id, '_edit_last', get_current_user_id() );

		/**
		 * Update the postmeta fields
		 */
		if ( ! empty( $input['desiredSlug'] ) ) {
			update_post_meta( $post_id, '_wp_desired_post_slug', $input['desiredSlug'] );
		}

		/**
		 * Set the object terms
		 *
		 * @param int           $post_id          The ID of the postObject being mutated
		 * @param array         $input            The input for the mutation
		 * @param \WP_Post_Type $post_type_object The Post Type Object for the type of post being mutated
		 * @param string        $mutation_name    The name of the mutation (ex: create, update, delete)
		 */
		self::set_object_terms( $post_id, $input, $post_type_object, $mutation_name );

		/**
		 * Run an action after the additional data has been updated. This is a great spot to hook into to
		 * update additional data related to postObjects, such as setting relationships, updating additional postmeta,
		 * or sending emails to Kevin. . .whatever you need to do with the postObject.
		 *
		 * @param int           $post_id              The ID of the postObject being mutated
		 * @param array         $input                The input for the mutation
		 * @param \WP_Post_Type $post_type_object     The Post Type Object for the type of post being mutated
		 * @param string        $mutation_name        The name of the mutation (ex: create, update, delete)
		 * @param AppContext    $context              The AppContext passed down to all resolvers
		 * @param ResolveInfo   $info                 The ResolveInfo passed down to all resolvers
		 * @param string        $intended_post_status The intended post_status the post should have according to the mutation input
		 * @param string        $default_post_status  The default status posts should use if an intended status wasn't set
		 */
		do_action( 'graphql_post_object_mutation_update_additional_data', $post_id, $input, $post_type_object, $mutation_name, $context, $info, $default_post_status, $intended_post_status );

		/**
		 * Sets the post lock
		 *
		 * @param int           $post_id              The ID of the postObject being mutated
		 * @param array         $input                The input for the mutation
		 * @param \WP_Post_Type $post_type_object     The Post Type Object for the type of post being mutated
		 * @param string        $mutation_name        The name of the mutation (ex: create, update, delete)
		 * @param AppContext    $context              The AppContext passed down to all resolvers
		 * @param ResolveInfo   $info                 The ResolveInfo passed down to all resolvers
		 * @param string        $intended_post_status The intended post_status the post should have according to the mutation input
		 * @param string        $default_post_status  The default status posts should use if an intended status wasn't set
		 *
		 * @return bool
		 */
		if ( true === apply_filters( 'graphql_post_object_mutation_set_edit_lock', true, $post_id, $input, $post_type_object, $mutation_name, $context, $info, $default_post_status, $intended_post_status ) ) {
			/**
			 * Set the post_lock for the $new_post_id
			 */
			self::remove_edit_lock( $post_id );
		}

	}

	/**
	 * Given a $post_id and $input from the mutation, check to see if any term associations are being made, and
	 * properly set the relationships
	 *
	 * @param int           $post_id          The ID of the postObject being mutated
	 * @param array         $input            The input for the mutation
	 * @param \WP_Post_Type $post_type_object The Post Type Object for the type of post being mutated
	 * @param string        $mutation_name    The name of the mutation (ex: create, update, delete)
	 */
	protected static function set_object_terms( $post_id, $input, $post_type_object, $mutation_name ) {

		/**
		 * Fire an action before setting object terms during a GraphQL Post Object Mutation.
		 *
		 * One example use for this hook would be to create terms from the input that may not exist yet, so that they can be set as a relation below.
		 *
		 * @param int           $post_id          The ID of the postObject being mutated
		 * @param array         $input            The input for the mutation
		 * @param \WP_Post_Type $post_type_object The Post Type Object for the type of post being mutated
		 * @param string        $mutation_name    The name of the mutation (ex: create, update, delete)
		 */
		do_action( 'graphql_post_object_mutation_set_object_terms', $post_id, $input, $post_type_object, $mutation_name );

		/**
		 * Get the allowed taxonomies and iterate through them to find the term inputs to use for setting relationships
		 */
		$allowed_taxonomies = \WPGraphQL::$allowed_taxonomies;

		if ( ! empty( $allowed_taxonomies ) && is_array( $allowed_taxonomies ) ) {

			foreach ( $allowed_taxonomies as $taxonomy ) {

				/**
				 * If the taxonomy is in the array of taxonomies registered to the post_type
				 */
				if ( in_array( $taxonomy, get_object_taxonomies( $post_type_object->name ), true ) ) {

					/**
					 * Get the tax object
					 */
					$tax_object = get_taxonomy( $taxonomy );

					/**
					 * If there is input for the taxonomy, process it
					 */
					if ( ! empty( $tax_object->graphql_plural_name ) && ! empty( $input[ $tax_object->graphql_plural_name ] ) ) {

						$term_input = $input[ $tax_object->graphql_plural_name ];

						/**
						 * Default append to true, but allow input to set it to false.
						 */
						$append = isset( $term_input['append'] ) && false === $term_input['append'] ? false : true;

						/**
						 * Start an array of terms to connect
						 */
						$terms_to_connect = [];

						/**
						 * Filter whether to allow terms to be created during a post mutation.
						 *
						 * If a post mutation includes term input for a term that does not already exist,
						 * this will allow terms to be created in order to connect the term to the post object,
						 * but if filtered to false, this will prevent the term that doesn't already exist
						 * from being created during the mutation of the post.
						 *
						 * @param bool         $allow_term_creation Whether new terms should be created during the post object mutation
						 * @param \WP_Taxonomy $tax_object          The Taxonomy object for the term being added to the Post Object
						 */
						$allow_term_creation = apply_filters( 'graphql_post_object_mutations_allow_term_creation', true, $tax_object );

						/**
						 * If there are nodes in the term_input
						 */
						if ( ! empty( $term_input['nodes'] ) && is_array( $term_input['nodes'] ) ) {

							foreach ( $term_input['nodes'] as $node ) {

								$term_exists = false;

								/**
								 * Handle the input for ID first.
								 */
								if ( ! empty( $node['id'] ) ) {

									if ( ! absint( $node['id'] ) ) {

										$id_parts = Relay::fromGlobalId( $node['id'] );

										if ( $id_parts['type'] !== $tax_object->name ) {
											return;
										}

										if ( ! empty( $id_parts['id'] ) ) {
											$term_exists = get_term_by( 'id', absint( $id_parts['id'] ), $tax_object->name );
											if ( $term_exists ) {
												$terms_to_connect[] = $term_exists->term_id;
											}
										}
									} else {
										$term_exists = get_term_by( 'id', absint( $node['id'] ), $tax_object->name );
										if ( $term_exists ) {
											$terms_to_connect[] = $term_exists->term_id;
										}
									}

									/**
									 * Next, handle the input for slug if there wasn't an ID input
									 */
								} elseif ( ! empty( $node['slug'] ) ) {
									$sanitized_slug = sanitize_text_field( $node['slug'] );
									$term_exists    = get_term_by( 'slug', $sanitized_slug, $tax_object->name );
									if ( $term_exists ) {
										$terms_to_connect[] = $term_exists->term_id;
									}
									/**
									 * If the input for the term isn't an existing term, check to make sure
									 * we're allowed to create new terms during a Post Object mutation
									 */
								}

								/**
								 * If no term exists so far, and terms are set to be allowed to be created
								 * during a post object mutation, create the term to connect based on the
								 * input
								 */
								if ( ! $term_exists && true === $allow_term_creation ) {

									/**
									 * If the current user cannot edit terms, don't create terms to connect
									 */
									if ( ! current_user_can( $tax_object->cap->edit_terms ) ) {
										return;
									}

									$created_term = self::create_term_to_connect( $node, $tax_object->name );

									if ( ! empty( $created_term ) ) {
										$terms_to_connect[] = $created_term;
									}

								}

							}
						}


						/**
						 * If there are terms to connect, set the connection
						 */
						if ( ! empty( $terms_to_connect ) && is_array( $terms_to_connect ) ) {

							/**
							 * If the current user cannot edit terms, don't create terms to connect
							 */
							if ( ! current_user_can( $tax_object->cap->assign_terms ) ) {
								return;
							}

							wp_set_object_terms( $post_id, $terms_to_connect, $tax_object->name, $append );
						}
					}

				}

			}
		}

	}

	/**
	 * Given an array of Term properties (slug, name, description, etc), create the term and return a term_id
	 *
	 * @param array  $node     The node input for the term
	 * @param string $taxonomy The taxonomy the term input is for
	 *
	 * @return int $term_id The ID of the created term. 0 if no term was created.
	 */
	protected static function create_term_to_connect( $node, $taxonomy ) {

		$created_term   = [];
		$term_to_create = [];
		$term_args      = [];

		if ( ! empty( $node['name'] ) ) {
			$term_to_create['name'] = sanitize_text_field( $node['name'] );
		} elseif ( ! empty( $node['slug'] ) ) {
			$term_to_create['name'] = sanitize_text_field( $node['slug'] );
		}

		if ( ! empty( $node['slug'] ) ) {
			$term_args['slug'] = sanitize_text_field( $node['slug'] );
		}

		if ( ! empty( $node['description'] ) ) {
			$term_args['description'] = sanitize_text_field( $node['description'] );
		}

		/**
		 * @todo: consider supporting "parent" input in $term_args
		 */

		if ( ! empty( $term_to_create['name'] ) ) {
			$created_term = wp_insert_term( $term_to_create['name'], $taxonomy, $term_args );
		}

		if ( is_wp_error( $created_term ) && isset( $created_term->error_data['term_exists'] ) ) {
			return $created_term->error_data['term_exists'];
		}

		/**
		 * Return the created term, or 0
		 */
		return ! empty( $created_term['term_id'] ) ? $created_term['term_id'] : 0;

	}

	/**
	 * This is a copy of the wp_set_post_lock function that exists in WordPress core, but is not
	 * accessible because that part of WordPress is never loaded for WPGraphQL executions
	 *
	 * Mark the post as currently being edited by the current user
	 *
	 * @param int $post_id ID of the post being edited.
	 *
	 * @return array|false Array of the lock time and user ID. False if the post does not exist, or
	 *                     there is no current user.
	 */
	public static function set_edit_lock( $post_id ) {

		$post    = get_post( $post_id );
		$user_id = get_current_user_id();

		if ( empty( $post ) ) {
			return false;
		}

		if ( 0 === $user_id ) {
			return false;
		}

		$now  = time();
		$lock = "$now:$user_id";
		update_post_meta( $post->ID, '_edit_lock', $lock );

		return [ $now, $user_id ];

	}

	/**
	 * Remove the edit lock for a post
	 *
	 * @param int $post_id ID of the post to delete the lock for
	 *
	 * @return bool
	 */
	public static function remove_edit_lock( $post_id ) {

		$post = get_post( $post_id );

		if ( ! is_a( $post, 'WP_Post' ) ) {
			return false;
		}

		return delete_post_meta( $post->ID, '_edit_lock' );

	}

}
