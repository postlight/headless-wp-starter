<?php

namespace WPGraphQL\Type\PostObject;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;

use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Data\Loader;
use WPGraphQL\Type\Comment\Connection\CommentConnectionDefinition;
use WPGraphQL\Type\PostObject\Connection\PostObjectConnectionDefinition;
use WPGraphQL\Type\TermObject\Connection\TermObjectConnectionDefinition;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

/**
 * Class PostObjectType
 *
 * This sets up the base PostObjectType. Custom Post Types that are set to "show_in_graphql" automatically
 * use the PostObjectType and inherit the fields that are defined here. The fields get passed through a
 * filter unique to each type, so each post_type can modify it's type schema via field filters.
 *
 * NOTE: In some cases the shape of a Custom Post Type's schema is so drastically different from the standard
 * PostObjectType shape it might make more sense for the custom post type to register a different type
 * altogether instead of utilizing the PostObjectType.
 *
 * @package WPGraphQL\Type
 * @since   0.0.5
 */
class PostObjectType extends WPObjectType {

	/**
	 * Holds the $fields definition for the PostObjectType
	 *
	 * @var $fields
	 */
	private static $fields = [];

	/**
	 * Holds the post_type_object
	 *
	 * @var object $post_type_object
	 */
	private static $post_type_object;

	/**
	 * PostObjectType constructor.
	 *
	 * @param string $post_type The post_type name
	 *
	 * @since 0.0.5
	 */
	public function __construct( $post_type ) {

		/**
		 * Get the post_type_object from the post_type and store it
		 * for later use
		 *
		 * @since 0.0.5
		 */
		self::$post_type_object = get_post_type_object( $post_type );

		$config = [
			'name'        => ucfirst( self::$post_type_object->graphql_single_name ),
			// translators: the placeholder is the post_type of the object
			'description' => sprintf( __( 'The %s object type', 'wp-graphql' ), self::$post_type_object->graphql_single_name ),
			'fields'      => self::fields( self::$post_type_object ),
			'interfaces'  => [ self::node_interface() ],
		];

		parent::__construct( $config );
	}

	/**
	 * fields
	 * This defines the fields for PostObjectTypes
	 *
	 * @param $post_type_object
	 *
	 * @return \GraphQL\Type\Definition\FieldDefinition|mixed|null
	 * @since 0.0.5
	 */
	private static function fields( $post_type_object ) {

		/**
		 * Get the $single_name out of the post_type_object
		 *
		 * @since 0.0.5
		 */
		$single_name = self::$post_type_object->graphql_single_name;


		/**
		 * If the $fields haven't already been defined for this type,
		 * define the fields
		 *
		 * @since 0.0.5
		 */
		if ( empty( self::$fields[ $single_name ] ) ) {

			/**
			 * Get the taxonomies that are allowed in WPGraphQL
			 *
			 * @since 0.0.5
			 */
			$allowed_taxonomies = \WPGraphQL::$allowed_taxonomies;

			/**
			 * Define the fields for the post_type
			 *
			 * @return mixed
			 * @since 0.0.5
			 */
			self::$fields[ $single_name ] = function() use ( $single_name, $post_type_object, $allowed_taxonomies ) {
				$fields = [
					'id'                => [
						'type'        => Types::non_null( Types::id() ),
						'description' => __( 'The globally unique ID for the object', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ( ! empty( $post->post_type ) && ! empty( $post->ID ) ) ? Relay::toGlobalId( $post->post_type, $post->ID ) : null;
						},
					],
					$single_name . 'Id' => [
						'type'        => Types::non_null( Types::int() ),
						'description' => __( 'The id field matches the WP_Post->ID field.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return absint( $post->ID );
						},
					],
					'ancestors'         => [
						'type'        => Types::list_of( Types::post_object_union() ),
						'description' => esc_html__( 'Ancestors of the object', 'wp-graphql' ),
						'args'        => [
							'types' => [
								'type'        => Types::list_of( Types::post_type_enum() ),
								'description' => __( 'The types of ancestors to check for. Defaults to the same type as the current object', 'wp-graphql' ),
							],
						],
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							$ancestors    = [];
							$types        = ! empty( $args['types'] ) ? $args['types'] : [ $post->post_type ];
							$ancestor_ids = get_ancestors( $post->ID, $post->post_type );
							if ( ! empty( $ancestor_ids ) ) {
								foreach ( $ancestor_ids as $ancestor_id ) {
									$ancestor_obj = get_post( $ancestor_id );
									if ( in_array( $ancestor_obj->post_type, $types, true ) ) {
										$ancestors[] = $ancestor_obj;
									}
								}
							}

							return ! empty( $ancestors ) ? $ancestors : null;
						},
					],
					'author'            => [
						'type'        => Types::user(),
						'description' => __( "The author field will return a queryable User type matching the post's author.", 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return DataSource::resolve_user( $post->post_author );
						},
					],
					'date'              => [
						'type'        => Types::string(),
						'description' => __( 'Post publishing date.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->post_date ) ? $post->post_date : null;
						},
					],
					'dateGmt'           => [
						'type'        => Types::string(),
						'description' => __( 'The publishing date set in GMT.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->post_date_gmt ) ? Types::prepare_date_response( $post->post_date_gmt ) : null;
						},
					],
					'content'           => [
						'type'        => Types::string(),
						'description' => __( 'The content of the post.', 'wp-graphql' ),
						'args'        => [
							'format' => self::post_object_format_arg(),
						],
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {

							$content = ! empty( $post->post_content ) ? $post->post_content : null;

							// If the raw format is requested, don't apply any filters.
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $content;
							}

							return apply_filters( 'the_content', $content );
						},
					],
					'title'             => [
						'type'        => Types::string(),
						'description' => __( 'The title of the post. This is currently just the raw title. An amendment to support rendered title needs to be made.', 'wp-graphql' ),
						'args'        => [
							'format' => self::post_object_format_arg(),
						],
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {

							$title = ! empty( $post->post_title ) ? $post->post_title : null;

							// If the raw format is requested, don't apply any filters.
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $title;
							}

							return apply_filters( 'the_title', $title );
						},
					],
					'excerpt'           => [
						'type'        => Types::string(),
						'description' => __( 'The excerpt of the post. This is currently just the raw excerpt. An amendment to support rendered excerpts needs to be made.', 'wp-graphql' ),
						'args'        => [
							'format' => self::post_object_format_arg(),
						],
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {

							$excerpt = ! empty( $post->post_excerpt ) ? $post->post_excerpt : null;

							// If the raw format is requested, don't apply any filters.
							if ( isset( $args['format'] ) && 'raw' === $args['format'] ) {
								return $excerpt;
							}

							$excerpt = apply_filters( 'get_the_excerpt', $excerpt, $post );

							return apply_filters( 'the_excerpt', $excerpt );
						},
					],
					'status'            => [
						'type'        => Types::string(),
						'description' => __( 'The current status of the object', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->post_status ) ? $post->post_status : null;
						},
					],
					'commentStatus'     => array(
						'type'        => Types::string(),
						'description' => __( 'Whether the comments are open or closed for this particular post.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->comment_status ) ? $post->comment_status : null;
						},
					),
					'pingStatus'        => [
						'type'        => Types::string(),
						'description' => __( 'Whether the pings are open or closed for this particular post.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->ping_status ) ? $post->ping_status : null;
						},
					],
					'slug'              => [
						'type'        => Types::string(),
						'description' => __( 'The uri slug for the post. This is equivalent to the WP_Post->post_name field and the post_name column in the database for the `post_objects` table.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->post_name ) ? $post->post_name : null;
						},
					],
					'toPing'            => [
						'type'        => Types::list_of( Types::string() ),
						'description' => __( 'URLs queued to be pinged.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->to_ping ) ? implode( ',', $post->to_ping ) : null;
						},
					],
					'pinged'            => [
						'type'        => Types::list_of( Types::string() ),
						'description' => __( 'URLs that have been pinged.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->pinged ) ? implode( ',', $post->pinged ) : null;
						},
					],
					'modified'          => [
						'type'        => Types::string(),
						'description' => __( 'The local modified time for a post. If a post was recently updated the modified field will change to match the corresponding time.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->post_modified ) ? $post->post_modified : null;
						},
					],
					'modifiedGmt'       => [
						'type'        => Types::string(),
						'description' => __( 'The GMT modified time for a post. If a post was recently updated the modified field will change to match the corresponding time in GMT.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->post_modified_gmt ) ? Types::prepare_date_response( $post->post_modified_gmt ) : null;
						},
					],
					'parent'            => [
						'type'        => Types::post_object_union(),
						'description' => __( 'The parent of the object. The parent object can be of various types', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->post_parent ) ? get_post( $post->post_parent ) : null;
						},
					],
					'editLast'          => [
						'type'        => Types::user(),
						'description' => __( 'The user that most recently edited the object', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, array $args, AppContext $context, ResolveInfo $info ) {
							$edit_last = get_post_meta( $post->ID, '_edit_last', true );

							return ! empty( $edit_last ) ? DataSource::resolve_user( absint( $edit_last ) ) : null;
						},
					],
					'editLock'          => [
						'type'        => Types::edit_lock(),
						'description' => __( 'If a user has edited the object within the past 15 seconds, this will return the user and the time they last edited. Null if the edit lock doesn\'t exist or is greater than 15 seconds', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, array $args, AppContext $context, ResolveInfo $info ) {
							$edit_lock       = get_post_meta( $post->ID, '_edit_lock', true );
							$edit_lock_parts = explode( ':', $edit_lock );

							return ! empty( $edit_lock_parts ) ? $edit_lock_parts : null;
						},
					],
					'enclosure'         => [
						'type'        => Types::string(),
						'description' => __( 'The RSS enclosure for the object', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, array $args, AppContext $context, ResolveInfo $info ) {
							$enclosure = get_post_meta( $post->ID, 'enclosure', true );

							return ! empty( $enclosure ) ? $enclosure : null;
						},
					],
					'guid'              => [
						'type'        => Types::string(),
						'description' => __( 'The global unique identifier for this post. This currently matches the value stored in WP_Post->guid and the guid column in the `post_objects` database table.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->guid ) ? $post->guid : null;
						},
					],
					'menuOrder'         => [
						'type'        => Types::int(),
						'description' => __( 'A field used for ordering posts. This is typically used with nav menu items or for special ordering of hierarchical content types.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->menu_order ) ? absint( $post->menu_order ) : null;
						},
					],
					'desiredSlug'       => [
						'type'        => Types::string(),
						'description' => __( 'The desired slug of the post', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							$desired_slug = get_post_meta( $post->ID, '_wp_desired_post_slug', true );

							return ! empty( $desired_slug ) ? $desired_slug : null;
						},
					],
					'link'              => [
						'type'        => Types::string(),
						'description' => __( 'The permalink of the post', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							$link = get_permalink( $post->ID );

							return ! empty( $link ) ? $link : null;
						},
					],
					'uri'               => [
						'type'        => Types::string(),
						'description' => __( 'URI path for the resource', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							$uri = get_page_uri( $post->ID );

							return ! empty( $uri ) ? $uri : null;
						},
					],
					'terms'             => [
						'type'        => Types::list_of( Types::term_object_union() ),
						'args'        => [
							'taxonomies' => [
								'type'        => Types::list_of( Types::taxonomy_enum() ),
								'description' => __( 'Select which taxonomies to limit the results to', 'wp-graphql' ),
							],
						],
						// Translators: placeholder is the name of the post_type
						'description' => sprintf( __( 'Terms connected to the %1$s', 'wp-graphql' ), $single_name ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) use ( $allowed_taxonomies ) {

							/**
							 * If the $arg for taxonomies is populated, use it as the $allowed_taxonomies
							 * otherwise use the default $allowed_taxonomies passed down
							 */
							$taxonomies = [];
							if ( ! empty( $args['taxonomies'] ) && is_array( $args['taxonomies'] ) ) {
								$taxonomies = $args['taxonomies'];
							} else {
								$connected_taxonomies = get_object_taxonomies( $post, 'names' );
								foreach( $connected_taxonomies as $taxonomy ) {
									if ( in_array( $taxonomy, \WPGraphQL::$allowed_taxonomies ) ) {
										$taxonomies[] = $taxonomy;
									}
								}
							}

							$tax_terms = [];
							if ( ! empty( $taxonomies ) ) {

								$term_query = new \WP_Term_Query( [
									'taxonomy'   => $taxonomies,
									'object_ids' => $post->ID,
								] );

								$tax_terms = $term_query->get_terms();

							}

							return ! empty( $tax_terms ) && is_array( $tax_terms ) ? $tax_terms : null;
						},
					],
					'termNames'         => [
						'type'        => Types::list_of( Types::string() ),
						'args'        => [
							'taxonomies' => [
								'type'        => Types::list_of( Types::taxonomy_enum() ),
								'description' => __( 'Select which taxonomies to limit the results to', 'wp-graphql' ),
							],
						],
						// Translators: placeholder is the name of the post_type
						'description' => sprintf( __( 'Terms connected to the %1$s', 'wp-graphql' ), $single_name ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) use ( $allowed_taxonomies ) {

							/**
							 * If the $arg for taxonomies is populated, use it as the $allowed_taxonomies
							 * otherwise use the default $allowed_taxonomies passed down
							 */
							$taxonomies = [];
							if ( ! empty( $args['taxonomies'] ) && is_array( $args['taxonomies'] ) ) {
								$taxonomies = $args['taxonomies'];
							} else {
								$connected_taxonomies = get_object_taxonomies( $post, 'names' );
								foreach( $connected_taxonomies as $taxonomy ) {
									if ( in_array( $taxonomy, \WPGraphQL::$allowed_taxonomies ) ) {
										$taxonomies[] = $taxonomy;
									}
								}
							}

							$tax_terms = [];
							if ( ! empty( $taxonomies ) ) {

								$term_query = new \WP_Term_Query( [
									'taxonomy'   => $taxonomies,
									'object_ids' => [ $post->ID ],
								] );

								$tax_terms = $term_query->get_terms();

							}
							$term_names = ! empty( $tax_terms ) && is_array( $tax_terms ) ? wp_list_pluck( $tax_terms, 'name' ) : [];

							return ! empty( $term_names ) ? $term_names : null;
						},
					],
					'termSlugs'         => [
						'type'        => Types::list_of( Types::string() ),
						'args'        => [
							'taxonomies' => [
								'type'        => Types::list_of( Types::taxonomy_enum() ),
								'description' => __( 'Select which taxonomies to limit the results to', 'wp-graphql' ),
							],
						],
						// Translators: placeholder is the name of the post_type
						'description' => sprintf( __( 'Terms connected to the %1$s', 'wp-graphql' ), $single_name ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) use ( $allowed_taxonomies ) {

							/**
							 * If the $arg for taxonomies is populated, use it as the $allowed_taxonomies
							 * otherwise use the default $allowed_taxonomies passed down
							 */
							$taxonomies = [];
							if ( ! empty( $args['taxonomies'] ) && is_array( $args['taxonomies'] ) ) {
								$taxonomies = $args['taxonomies'];
							} else {
								$connected_taxonomies = get_object_taxonomies( $post, 'names' );
								foreach( $connected_taxonomies as $taxonomy ) {
									if ( in_array( $taxonomy, \WPGraphQL::$allowed_taxonomies ) ) {
										$taxonomies[] = $taxonomy;
									}
								}
							}

							$tax_terms = [];
							if ( ! empty( $taxonomies ) ) {

								$term_query = new \WP_Term_Query( [
									'taxonomy'   => $taxonomies,
									'object_ids' => [ $post->ID ],
								] );

								$tax_terms = $term_query->get_terms();

							}
							$term_slugs = ! empty( $tax_terms ) && is_array( $tax_terms ) ? wp_list_pluck( $tax_terms, 'slug' ) : [];

							return ! empty( $term_slugs ) ? $term_slugs : null;
						},
					],
				];

				/**
				 * Add comment fields to the schema if the post_type supports "comments"
				 *
				 * @since 0.0.5
				 */
				if ( post_type_supports( $post_type_object->name, 'comments' ) ) {
					$fields['comments']     = CommentConnectionDefinition::connection( $post_type_object->graphql_single_name );
					$fields['commentCount'] = [
						'type'        => Types::int(),
						'description' => __( 'The number of comments. Even though WPGraphQL denotes this field as an integer, in WordPress this field should be saved as a numeric string for compatability.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post->comment_count ) ? absint( $post->comment_count ) : null;
						},
					];
				}

				/**
				 * If the post_type is Hierarchical, there should be a children field
				 */
				if ( true === $post_type_object->hierarchical ) {
					$fields[ 'child' . ucfirst( $post_type_object->graphql_plural_name ) ] = PostObjectConnectionDefinition::connection( $post_type_object, 'Children' );
				}

				/**
				 * Add term connections based on the allowed taxonomies that are also
				 * registered to the post_type
				 *
				 * @since 0.0.5
				 */
				if ( ! empty( $allowed_taxonomies ) && is_array( $allowed_taxonomies ) ) {
					foreach ( $allowed_taxonomies as $taxonomy ) {
						// If the taxonomy is in the array of taxonomies registered to the post_type
						if ( in_array( $taxonomy, get_object_taxonomies( $post_type_object->name ), true ) ) {
							$tax_object                                 = get_taxonomy( $taxonomy );
							$fields[ $tax_object->graphql_plural_name ] = TermObjectConnectionDefinition::connection( $tax_object, $post_type_object->graphql_single_name );
						}
					}
				}

				if ( post_type_supports( $post_type_object->name, 'thumbnail' ) ) {
					$fields['featuredImage'] = [
						'type'        => Types::post_object( 'attachment' ),
						'description' => __( 'The featured image for the object', 'wp-graphql' ),
						'resolve'     => function( \WP_Post $post, $args, AppContext $context, ResolveInfo $info ) {
							$thumbnail_id = get_post_thumbnail_id( $post->ID );

							return ! empty( $thumbnail_id ) ? get_post( absint( $thumbnail_id ) ) : null;
						},
					];
				}

				/**
				 * This prepares the fields by sorting them and applying a filter for adjusting the schema.
				 * Because these fields are implemented via a closure the prepare_fields needs to be applied
				 * to the fields directly instead of being applied to all objects extending
				 * the WPObjectType class.
				 *
				 * @since 0.0.5
				 */
				return self::prepare_fields( $fields, $single_name );
			};
		}

		return ! empty( self::$fields[ $single_name ] ) ? self::$fields[ $single_name ] : null;
	}

	/**
	 * Define the args to be used by post object fields.
	 *
	 * @return mixed
	 */
	public static function post_object_format_arg() {
		return [
			'type'        => Types::post_object_field_format_enum(),
			'description' => __( 'Format of the field output', 'wp-graphql' ),
		];
	}
}
