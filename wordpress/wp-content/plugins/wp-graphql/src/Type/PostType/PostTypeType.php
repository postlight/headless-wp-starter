<?php
namespace WPGraphQL\Type\PostType;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

/**
 * Class PostTypeType
 * @package WPGraphQL\Type
 * @since 0.0.5
 */
class PostTypeType extends WPObjectType {

	/**
	 * Holds the type name
	 * @var string $type_name
	 */
	private static $type_name;

	/**
	 * This holds the field definitions
	 * @var array $fields
	 * @since 0.0.5
	 */
	private static $fields;

	/**
	 * Holds the object definition for labels details
	 *
	 * @var object $labels_details
	 */
	private static $labels_details;

	/**
	 * PostTypeType constructor.
	 * @since 0.0.5
	 */
	public function __construct() {

		/**
		 * Set the type_name
		 * @since 0.0.5
		 */
		self::$type_name = 'PostType';

		$config = [
			'name' => self::$type_name,
			'description' => __( 'An Post Type object', 'wp-graphql' ),
			'fields' => self::fields(),
			'interfaces' => [ self::node_interface() ],
		];

		parent::__construct( $config );

	}

	/**
	 * fields
	 *
	 * This defines the fields that make up the PostTypeType
	 *
	 * @return array
	 * @since 0.0.5
	 */
	private static function fields() {

		if ( null === self::$fields ) {
			/**
			 * Get the taxonomies that are allowed in WPGraphQL
			 *
			 * @since 0.0.6
			 */
			$allowed_taxonomies = \WPGraphQL::$allowed_taxonomies;

			self::$fields = function() use ( $allowed_taxonomies ) {
				$fields = [
					'id'                     => [
						'type'    => Types::non_null( Types::id() ),
						'resolve' => function( \WP_Post_Type $post_type, $args, AppContext $context, ResolveInfo $info ) {
							return ( ! empty( $post_type->name ) && ! empty( $post_type->name ) ) ? Relay::toGlobalId( 'postType', $post_type->name ) : null;
						},
					],
					'name'                   => [
						'type'        => Types::string(),
						'description' => __( 'The internal name of the post type. This should not be used for display purposes.', 'wp-graphql' ),
					],
					'label'                  => [
						'type'        => Types::string(),
						'description' => __( 'Display name of the content type.', 'wp-graphql' ),
					],
					'labels'                 => [
						'type'        => self::labels_details(),
						'description' => __( 'Details about the post type labels.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, $args, $context, ResolveInfo $info ) {
							return get_post_type_labels( $post_type );
						},
					],
					'description'            => [
						'type'        => Types::string(),
						'description' => __( 'Description of the content type.', 'wp-graphql' ),
					],
					'public'                 => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether a post type is intended for use publicly either via the admin interface or by front-end users. While the default settings of exclude_from_search, publicly_queryable, show_ui, and show_in_nav_menus are inherited from public, each does not rely on this relationship and controls a very specific intention.', 'wp-graphql' ),
					],
					'hierarchical'           => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether the post type is hierarchical, for example pages.', 'wp-graphql' ),
					],
					'excludeFromSearch'      => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to exclude posts with this post type from front end search results.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->exclude_from_search ) ? true : false;
						},
					],
					'publiclyQueryable'      => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether queries can be performed on the front end for the post type as part of parse_request().', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->publicly_queryable ) ? true : false;
						},
					],
					'showUi'                 => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to generate and allow a UI for managing this post type in the admin.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->show_ui ) ? true : false;
						},
					],
					'showInMenu'             => [
						'type'        => Types::boolean(),
						'description' => __( 'Where to show the post type in the admin menu. To work, $show_ui must be true. If true, the post type is shown in its own top level menu. If false, no menu is shown. If a string of an existing top level menu (eg. "tools.php" or "edit.php?post_type=page"), the post type will be placed as a sub-menu of that.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->show_in_menu ) ? true : false;
						},
					],
					'showInNavMenus'         => [
						'type'        => Types::boolean(),
						'description' => __( 'Makes this post type available for selection in navigation menus.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->show_in_nav_menus ) ? true : false;
						},
					],
					'showInAdminBar'         => [
						'type'        => Types::boolean(),
						'description' => __( 'Makes this post type available via the admin bar.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return empty( true === $post_type->show_in_admin_bar ) ? true : false;
						},
					],
					'menuPosition'           => [
						'type'        => Types::int(),
						'description' => __( 'The position of this post type in the menu. Only applies if show_in_menu is true.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post_type->menu_position ) ? $post_type->menu_position : null;
						},
					],
					'menuIcon'               => [
						'type'        => Types::string(),
						'description' => __( 'The name of the icon file to display as a menu icon.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post_type->menu_icon ) ? $post_type->menu_icon : null;
						},
					],
					'hasArchive'             => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether this content type should have archives. Content archives are generated by type and by date.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->has_archive ) ? true : false;
						},
					],
					'canExport'              => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether this content type should can be exported.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->can_export ) ? true : false;
						},
					],
					'deleteWithUser'         => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether delete this type of content when the author of it is deleted from the system.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->delete_with_user ) ? true : false;
						},
					],
					'showInRest'             => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to add the post type route in the REST API `wp/v2` namespace.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->show_in_rest ) ? true : false;
						},
					],
					'restBase'               => [
						'type'        => Types::string(),
						'description' => __( 'Name of content type to diplay in REST API `wp/v2` namespace.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post_type->rest_base ) ? $post_type->rest_base : null;
						},
					],
					'restControllerClass'    => [
						'type'        => Types::string(),
						'description' => __( 'The REST Controller class assigned to handling this content type.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post_type->rest_controller_class ) ? $post_type->rest_controller_class : null;
						},
					],
					'showInGraphql'          => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to add the post type to the GraphQL Schema.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $post_type->show_in_graphql ) ? true : false;
						},
					],
					'graphqlSingleName'      => [
						'type'        => Types::string(),
						'description' => __( 'The singular name of the post type within the GraphQL Schema.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post_type->graphql_single_name ) ? $post_type->graphql_single_name : null;
						},
					],
					'graphqlPluralName'      => [
						'type'        => Types::string(),
						'description' => __( 'The plural name of the post type within the GraphQL Schema.', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $post_type->graphql_plural_name ) ? $post_type->graphql_plural_name : null;
						},
					],
					'connectedTaxonomyNames' => [
						'type'        => Types::list_of( Types::string() ),
						'args'        => [
							'taxonomies' => [
								'type'        => Types::list_of( Types::taxonomy_enum() ),
								'description' => __( 'Select which taxonomies to limit the results to', 'wp-graphql' ),
							],
						],
						'description' => __( 'A list of Taxonomies associated with the post type', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type_object, array $args, $context, ResolveInfo $info ) use ( $allowed_taxonomies ) {

							$object_taxonomies = get_object_taxonomies( $post_type_object->name );

							$taxonomy_names = [];

							/**
							 * If the $arg for taxonomies is populated, use it as the $allowed_taxonomies
							 * otherwise use the default $allowed_taxonomies passed down
							 */
							$allowed_taxonomies = ! empty( $args['taxonomies'] ) && is_array( $args['taxonomies'] ) ? $args['taxonomies'] : $allowed_taxonomies;

							if ( ! empty( $object_taxonomies ) && is_array( $object_taxonomies ) ) {
								foreach ( $object_taxonomies as $taxonomy ) {
									if ( in_array( $taxonomy, $allowed_taxonomies, true ) ) {
										$taxonomy_names[] = $taxonomy;
									}
								}
							}

							return ! empty( $taxonomy_names ) ? $taxonomy_names : null;
						},
					],
					'connectedTaxonomies'    => [
						'type'        => Types::list_of( Types::taxonomy() ),
						'args'        => [
							'taxonomies' => [
								'type'        => Types::list_of( Types::taxonomy_enum() ),
								'description' => __( 'Select which taxonomies to limit the results to', 'wp-graphql' ),
							],
						],
						'description' => __( 'List of Taxonomies connected to the Post Type', 'wp-graphql' ),
						'resolve'     => function( \WP_Post_Type $post_type_object, array $args, AppContext $context, ResolveInfo $info ) use ( $allowed_taxonomies ) {

							$tax_objects = [];

							/**
							 * If the $arg for taxonomies is populated, use it as the $allowed_taxonomies
							 * otherwise use the default $allowed_taxonomies passed down
							 */
							$allowed_taxonomies = ! empty( $args['taxonomies'] ) && is_array( $args['taxonomies'] ) ? $args['taxonomies'] : $allowed_taxonomies;

							if ( ! empty( $allowed_taxonomies ) && is_array( $allowed_taxonomies ) ) {
								foreach ( $allowed_taxonomies as $taxonomy ) {
									if ( in_array( $taxonomy, get_object_taxonomies( $post_type_object->name ), true ) ) {
										$tax_object                                      = get_taxonomy( $taxonomy );
										$tax_objects[ $tax_object->graphql_single_name ] = $tax_object;
									}
								}
							}

							return ! empty( $tax_objects ) ? $tax_objects : null;
						},
					],
				];


				/**
				 * Pass the fields through a filter to allow for hooking in and adjusting the shape
				 * of the type's schema
				 *
				 * @since 0.0.5
				 */
				return self::prepare_fields( $fields, self::$type_name );

			};
		}
		return self::$fields;
	}

	/**
	 * This defines the labels details object type that can be queried on mediaItems
	 *
	 * @return null|WPObjectType
	 * @since 0.0.6
	 */
	private static function labels_details() {
		if ( null === self::$labels_details ) {
			self::$labels_details = new WPObjectType( [
				'name'   => 'LabelsDetails',
				'fields' => function() {
					$fields = [
						'name' => [
							'type' => Types::string(),
							'description' => __( 'General name for the post type, usually plural.', 'wp-graphql' ),
						],
						'singularName' => [
							'type' => Types::string(),
							'description' => __( 'Name for one object of this post type.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->singular_name ) ? $labels->singular_name : null;
							},
						],
						'addNew' => [
							'type' => Types::string(),
							'description' => __( 'Default is ‘Add New’ for both hierarchical and non-hierarchical types.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->add_new ) ? $labels->add_new : null;
							},
						],
						'addNewItem' => [
							'type' => Types::string(),
							'description' => __( 'Label for adding a new singular item.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->add_new_item ) ? $labels->add_new_item : null;
							},
						],
						'editItem' => [
							'type' => Types::string(),
							'description' => __( 'Label for editing a singular item.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->edit_item ) ? $labels->edit_item : null;
							},
						],
						'newItem' => [
							'type' => Types::string(),
							'description' => __( 'Label for the new item page title.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->new_item ) ? $labels->new_item : null;
							},
						],
						'viewItem' => [
							'type' => Types::string(),
							'description' => __( 'Label for viewing a singular item.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->view_item ) ? $labels->view_item : null;
							},
						],
						'viewItems' => [
							'type' => Types::string(),
							'description' => __( 'Label for viewing post type archives.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->view_items ) ? $labels->view_items : null;
							},
						],
						'searchItems' => [
							'type' => Types::string(),
							'description' => __( 'Label for searching plural items.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->search_items ) ? $labels->search_items : null;
							},
						],
						'notFound' => [
							'type' => Types::string(),
							'description' => __( 'Label used when no items are found.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->not_found ) ? $labels->not_found : null;
							},
						],
						'notFoundInTrash' => [
							'type' => Types::string(),
							'description' => __( 'Label used when no items are in the trash.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->not_found_in_trash ) ? $labels->not_found_in_trash : null;
							},
						],
						'parentItemColon' => [
							'type' => Types::string(),
							'description' => __( 'Label used to prefix parents of hierarchical items.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->parent_item_colon ) ? $labels->parent_item_colon : null;
							},
						],
						'allItems' => [
							'type' => Types::string(),
							'description' => __( 'Label to signify all items in a submenu link.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->all_items ) ? $labels->all_items : null;
							},
						],
						'archives' => [
							'type' => Types::string(),
							'description' => __( 'Label for archives in nav menus', 'wp-graphql' ),
						],
						'attributes' => [
							'type' => Types::string(),
							'description' => __( 'Label for the attributes meta box.', 'wp-graphql' ),
						],
						'insertIntoItem' => [
							'type' => Types::string(),
							'description' => __( 'Label for the media frame button.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->insert_into_item ) ? $labels->insert_into_item : null;
							},
						],
						'uploadedToThisItem' => [
							'type' => Types::string(),
							'description' => __( 'Label for the media frame filter.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->uploaded_to_this_item ) ? $labels->uploaded_to_this_item : null;
							},
						],
						'featuredImage' => [
							'type' => Types::string(),
							'description' => __( 'Label for the Featured Image meta box title.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->featured_image ) ? $labels->featured_image : null;
							},
						],
						'setFeaturedImage' => [
							'type' => Types::string(),
							'description' => __( 'Label for setting the featured image.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->set_featured_image ) ? $labels->set_featured_image : null;
							},
						],
						'removeFeaturedImage' => [
							'type' => Types::string(),
							'description' => __( 'Label for removing the featured image.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->remove_featured_image ) ? $labels->remove_featured_image : null;
							},
						],
						'useFeaturedImage' => [
							'type' => Types::string(),
							'description' => __( 'Label in the media frame for using a featured image.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->use_featured_item ) ? $labels->use_featured_item : null;
							},
						],
						'menuName' => [
							'type' => Types::string(),
							'description' => __( 'Label for the menu name.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->menu_name ) ? $labels->menu_name : null;
							},
						],
						'filterItemsList' => [
							'type' => Types::string(),
							'description' => __( 'Label for the table views hidden heading.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->filter_items_list ) ? $labels->filter_items_list : null;
							},
						],
						'itemsListNavigation' => [
							'type' => Types::string(),
							'description' => __( 'Label for the table pagination hidden heading.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->items_list_navigation ) ? $labels->items_list_navigation : null;
							},
						],
						'itemsList' => [
							'type' => Types::string(),
							'description' => __( 'Label for the table hidden heading.', 'wp-graphql' ),
							'resolve' => function( $labels ) {
								return ! empty( $labels->items_list ) ? $labels->items_list : null;
							},
						],
					];
					return self::prepare_fields( $fields, 'LabelsDetails' );
				},
			] );
		} // End if().
		return ! empty( self::$labels_details ) ? self::$labels_details : null;
	}

}
