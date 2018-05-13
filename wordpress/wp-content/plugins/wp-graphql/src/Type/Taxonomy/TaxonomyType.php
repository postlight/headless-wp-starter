<?php
namespace WPGraphQL\Type\Taxonomy;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

/**
 * Class TaxonomyType
 * @package WPGraphQL\Type
 * @since 0.0.5
 */
class TaxonomyType extends WPObjectType {

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
	 * TaxonomyType constructor.
	 * @since 0.0.5
	 */
	public function __construct() {

		/**
		 * Set the type_name
		 * @since 0.0.5
		 */
		self::$type_name = 'Taxonomy';

		$config = [
			'name' => self::$type_name,
			'description' => __( 'A taxonomy object', 'wp-graphql' ),
			'fields' => self::fields(),
			'interfaces' => [ self::node_interface() ],
		];

		parent::__construct( $config );

	}

	/**
	 * fields
	 *
	 * This defines the fields for the TaxonomyType. The fields are passed through a filter so the shape of the schema
	 * can be modified
	 *
	 * @return array|\GraphQL\Type\Definition\FieldDefinition[]
	 * @since 0.0.5
	 */
	private function fields() {

		if ( null === self::$fields ) {

			/**
			 * Get the post_types that are allowed in WPGraphQL
			 *
			 * @since 0.0.6
			 */
			$allowed_post_types = \WPGraphQL::$allowed_post_types;

			self::$fields = function() use ( $allowed_post_types ) {
				$fields = [
					'id'                     => [
						'type'    => Types::non_null( Types::id() ),
						'resolve' => function( $taxonomy, $args, AppContext $context, ResolveInfo $info ) {
							return ( ! empty( $info->parentType ) && ! empty( $taxonomy->name ) ) ? Relay::toGlobalId( 'taxonomy', $taxonomy->name ) : null;
						},
					],
					'name'                   => [
						'type'        => Types::string(),
						'description' => __( 'The display name of the taxonomy. This field is equivalent to WP_Taxonomy->label', 'wp-graphql' ),
					],
					'label'                  => [
						'type'        => Types::string(),
						'description' => __( 'Name of the taxonomy shown in the menu. Usually plural.', 'wp-graphql' ),
					],
					//@todo: add "labels" field
					'description'            => [
						'type'        => Types::string(),
						'description' => __( 'Description of the taxonomy. This field is equivalent to WP_Taxonomy->description', 'wp-graphql' ),
					],
					'public'                 => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether the taxonomy is publicly queryable', 'wp-graphql' ),
					],
					'hierarchical'           => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether the taxonomy is hierarchical', 'wp-graphql' ),
					],
					'showUi'                 => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to generate and allow a UI for managing terms in this taxonomy in the admin', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $taxonomy->show_ui ) ? true : false;
						},
					],
					'showInMenu'             => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to show the taxonomy in the admin menu', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $taxonomy->show_in_menu ) ? true : false;
						},
					],
					'showInNavMenus'         => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether the taxonomy is available for selection in navigation menus.', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $taxonomy->show_in_nav_menus ) ? true : false;
						},
					],
					'showCloud'              => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to show the taxonomy as part of a tag cloud widget. This field is equivalent to WP_Taxonomy->show_tagcloud', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $taxonomy->show_tagcloud ) ? true : false;
						},
					],
					'showInQuickEdit'        => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to show the taxonomy in the quick/bulk edit panel.', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $taxonomy->show_in_quick_edit ) ? true : false;
						},
					],
					'showInAdminColumn'      => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to display a column for the taxonomy on its post type listing screens.', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $taxonomy->show_admin_column ) ? true : false;
						},
					],
					'showInRest'             => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to add the post type route in the REST API `wp/v2` namespace.', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $taxonomy->show_in_rest ) ? true : false;
						},
					],
					'restBase'               => [
						'type'        => Types::string(),
						'description' => __( 'Name of content type to diplay in REST API `wp/v2` namespace.', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : null;
						},
					],
					'restControllerClass'    => [
						'type'        => Types::string(),
						'description' => __( 'The REST Controller class assigned to handling this content type.', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $taxonomy->rest_controller_class ) ? $taxonomy->rest_controller_class : null;
						},
					],
					'showInGraphql'          => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to add the post type to the GraphQL Schema.', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ( true === $taxonomy->show_in_graphql ) ? true : false;
						},
					],
					'graphqlSingleName'      => [
						'type'        => Types::string(),
						'description' => __( 'The singular name of the post type within the GraphQL Schema.', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $taxonomy->graphql_single_name ) ? $taxonomy->graphql_single_name : null;
						},
					],
					'graphqlPluralName'      => [
						'type'        => Types::string(),
						'description' => __( 'The plural name of the post type within the GraphQL Schema.', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $taxonomy->graphql_plural_name ) ? $taxonomy->graphql_plural_name : null;
						},
					],
					'connectedPostTypeNames' => [
						'type'        => Types::list_of( Types::string() ),
						'args'        => [
							'types' => [
								'type'        => Types::list_of( Types::post_type_enum() ),
								'description' => __( 'Select which post types to limit the results to', 'wp-graphql' ),
							],
						],
						'description' => __( 'A list of Post Types associated with the taxonomy', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) use ( $allowed_post_types ) {
							$post_type_names = [];

							/**
							 * If the types $arg is populated, use that to filter the $allowed_post_types,
							 * otherwise use the default $allowed_post_types passed down
							 */
							$allowed_post_types = ! empty( $args['types'] ) && is_array( $args['types'] ) ? $args['types'] : $allowed_post_types;

							$connected_post_types = $taxonomy->object_type;
							if ( ! empty( $connected_post_types ) && is_array( $connected_post_types ) ) {
								foreach ( $connected_post_types as $post_type ) {
									if ( in_array( $post_type, $allowed_post_types, true ) ) {
										$post_type_names[] = $post_type;
									}
								}
							}

							return ! empty( $post_type_names ) ? $post_type_names : null;
						},
					],
					'connectedPostTypes'     => [
						'type'        => Types::list_of( Types::post_type() ),
						'args'        => [
							'types' => [
								'type'        => Types::list_of( Types::post_type_enum() ),
								'description' => __( 'Select which post types to limit the results to', 'wp-graphql' ),
							],
						],
						'description' => __( 'List of Post Types connected to the Taxonomy', 'wp-graphql' ),
						'resolve'     => function( \WP_Taxonomy $taxonomy, array $args, AppContext $context, ResolveInfo $info ) use ( $allowed_post_types ) {

							$post_type_objects = [];

							/**
							 * If the types $arg is populated, use that to filter the $allowed_post_types,
							 * otherwise use the default $allowed_post_types passed down
							 */
							$allowed_post_types = ! empty( $args['types'] ) && is_array( $args['types'] ) ? $args['types'] : $allowed_post_types;

							$connected_post_types = ! empty( $taxonomy->object_type ) ? $taxonomy->object_type : [];
							if ( ! empty( $allowed_post_types ) && is_array( $allowed_post_types ) ) {
								foreach ( $allowed_post_types as $post_type ) {
									if ( in_array( $post_type, $connected_post_types, true ) ) {
										$post_type_object                                            = get_post_type_object( $post_type );
										$post_type_objects[ $post_type_object->graphql_single_name ] = $post_type_object;
									}
								}
							}

							return ! empty( $post_type_objects ) ? $post_type_objects : null;
						},
					],
				];

				/**
				 * This prepares the fields by sorting them and applying a filter for adjusting the schema.
				 * Because these fields are implemented via a closure the prepare_fields needs to be applied
				 * to the fields directly instead of being applied to all objects extending
				 * the WPObjectType class.
				 */
				return self::prepare_fields( $fields, self::$type_name );

			};
		}
		return self::$fields;

	}
}
