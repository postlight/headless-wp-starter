<?php
namespace WPGraphQL\Type;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Type\Comment\CommentQuery;
use WPGraphQL\Type\Comment\Connection\CommentConnectionDefinition;
use WPGraphQL\Type\Setting\SettingQuery;
use WPGraphQL\Type\Settings\SettingsQuery;
use WPGraphQL\Type\Plugin\Connection\PluginConnectionDefinition;
use WPGraphQL\Type\Plugin\PluginQuery;
use WPGraphQL\Type\PostObject\PostObjectQuery;
use WPGraphQL\Type\PostObject\Connection\PostObjectConnectionDefinition;
use WPGraphQL\Type\TermObject\Connection\TermObjectConnectionDefinition;
use WPGraphQL\Type\TermObject\TermObjectQuery;
use WPGraphQL\Type\Theme\Connection\ThemeConnectionDefinition;
use WPGraphQL\Type\User\Connection\UserConnectionDefinition;
use WPGraphQL\Type\User\UserQuery;
use WPGraphQL\Types;

/**
 * Class RootQueryType
 * The RootQueryType is the primary entry for Queries in the GraphQL Schema.
 * @package WPGraphQL\Type
 * @since 0.0.4
 */
class RootQueryType extends WPObjectType {

	/**
	 * RootQueryType constructor.
	 * @since 0.0.5
	 */
	public function __construct() {

		/**
		 * Run an action when the RootQuery is being generated
		 * @since 0.0.5
		 */
		do_action( 'graphql_root_query' );

		/**
		 * Configure the RootQuery
		 * @since 0.0.5
		 */
		$config = [
			'name' => 'rootQuery',
			'fields' => self::fields(),
		];

		/**
		 * Pass the config to the parent construct
		 * @since 0.0.5
		 */
		parent::__construct( $config );

	}

	public static function fields() {

		/**
		 * Setup data
		 * @since 0.0.5
		 */
		$allowed_post_types = \WPGraphQL::$allowed_post_types;
		$allowed_taxonomies = \WPGraphQL::$allowed_taxonomies;
		$allowed_setting_types = DataSource::get_allowed_settings_by_group();
		$node_definition = DataSource::get_node_definition();

		/**
		 * Creates the node root query field which can be used
		 * to query any node from the system using the globally unique
		 * ID
		 * @since 0.0.5
		 */
		$fields['node'] = $node_definition['nodeField'];

		/**
		 * Creates the comment root query field
		 * @since 0.0.5
		 */
		$fields['comment'] = CommentQuery::root_query();
		$fields['comments'] = CommentConnectionDefinition::connection();

		/**
		 * Creates the plugin root query field
		 * @since 0.0.5
		 */
		$fields['plugin'] = PluginQuery::root_query();
		$fields['plugins'] = PluginConnectionDefinition::connection();

		/**
		 * Create the root query fields for any setting type in
		 * the $allowed_setting_types array.
		 */
		if ( ! empty( $allowed_setting_types ) && is_array( $allowed_setting_types ) ) {
			foreach ( $allowed_setting_types as $group => $setting_type ) {
				$setting_type = str_replace('_', '', strtolower( $group ) );
				$fields[ $setting_type . 'Settings' ] = SettingQuery::root_query( $group, $setting_type );
			}
		}

		/**
		 * Creates the all settings root query field
		 */
		$fields['allSettings'] = SettingsQuery::root_query();

		/**
		 * Creates the theme root query field
		 * @since 0.0.5
		 */
		$fields['theme'] = self::theme();

		/**
		 * Creates the theme root query field to query a collection
		 * of themes
		 * @since 0.0.5
		 */
		$fields['themes'] = ThemeConnectionDefinition::connection();

		/**
		 * Creates the user root query field
		 * @since 0.0.5
		 */
		$fields['user'] = UserQuery::root_query();

		/**
		 * Creates the users root query field to query a collection
		 * of users
		 * @since 0.0.5
		 */
		$fields['users'] = UserConnectionDefinition::connection();

		/**
		 * Creates the viewer root query field
		 * @since 0.0.5
		 */
		$fields['viewer'] = self::viewer();

		/**
		 * Creates the root fields for post objects (of any post_type)
		 * This registers root fields (single and plural) for any post_type that has been registered as an
		 * allowed post_type.
		 * @see \WPGraphQL::$allowed_post_types
		 * @since 0.0.5
		 */
		if ( ! empty( $allowed_post_types ) && is_array( $allowed_post_types ) ) {
			foreach ( $allowed_post_types as $post_type ) {
				/**
				 * Get the post_type object to pass down to the schema
				 * @since 0.0.5
				 */
				$post_type_object = get_post_type_object( $post_type );

				/**
				 * Root query for single posts (of the specified post_type)
				 * @since 0.0.5
				 */
				$fields[ $post_type_object->graphql_single_name ] = PostObjectQuery::root_query( $post_type_object );
				$fields[ $post_type_object->graphql_single_name . 'By' ] = PostObjectQuery::post_object_by( $post_type_object );

				/**
				 * Root query for collections of posts (of the specified post_type)
				 * @since 0.0.5
				 */
				$fields[ $post_type_object->graphql_plural_name ] = PostObjectConnectionDefinition::connection( $post_type_object );
			}
		}

		/**
		 * Creates the root fields for terms of each taxonomy
		 * This registers root fields (single and plural) for terms of any taxonomy that has been registered as an
		 * allowed taxonomy.
		 * @see \WPGraphQL::$allowed_taxonomies
		 * @since 0.0.5
		 */
		if ( ! empty( $allowed_taxonomies ) && is_array( $allowed_taxonomies ) ) {
			foreach ( $allowed_taxonomies as $taxonomy ) {

				/**
				 * Get the taxonomy object
				 * @since 0.0.5
				 */
				$taxonomy_object = get_taxonomy( $taxonomy );

				/**
				 * Root query for single terms (of the specified taxonomy)
				 * @since 0.0.5
				 */
				$fields[ $taxonomy_object->graphql_single_name ] = TermObjectQuery::root_query( $taxonomy_object );

				/**
				 * Root query for collections of terms (of the specified taxonomy)
				 * @since 0.0.5
				 */
				$fields[ $taxonomy_object->graphql_plural_name ] = TermObjectConnectionDefinition::connection( $taxonomy_object );
			}
		}

		/**
		 * Pass the root queries through a filter.
		 * This allows fields to be added or removed.
		 * NOTE: Use this filter with care. Before removing existing fields seriously consider deprecating the field, as
		 * that will allow the field to still be used and not break systems that rely on it, but just not be present
		 * in Schema documentation, etc.
		 * If the behavior of a field needs to be changed, depending on the change, it might be better to consider adding
		 * a new field with the new behavior instead of overriding an existing field. This will allow existing fields
		 * to behave as expected, but will allow introduction of new fields with different behavior at any point.
		 * @since 0.0.5
		 */
		$fields = apply_filters( 'graphql_root_queries', $fields );

		/**
		 * Sort the fields alphabetically by keys
		 * (this makes the schema documentation much nicer to browse)
		 */
		ksort( $fields );

		return $fields;

	}

	/**
	 * theme
	 * This sets up the theme entry point for the root query
	 * @return array
	 * @since 0.0.5
	 */
	public static function theme() {
		return [
			'type' => Types::theme(),
			'description' => __( 'A Theme object', 'wp-graphql' ),
			'args' => [
				'id' => Types::non_null( Types::id() ),
			],
			'resolve' => function( $source, array $args, $context, ResolveInfo $info ) {
				$id_components = Relay::fromGlobalId( $args['id'] );

				return DataSource::resolve_theme( $id_components['id'] );
			},
		];
	}

	/**
	 * viewer
	 * This sets up the viewer entry point for the root query
	 * @return array
	 * @since 0.0.5
	 */
	public static function viewer() {
		return [
			'type' => Types::user(),
			'description' => __( 'Returns the current user', 'wp-graphql' ),
			'resolve' => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
				return ( false !== $context->viewer->ID ) ? DataSource::resolve_user( $context->viewer->ID ) : null;
			},
		];
	}
}
