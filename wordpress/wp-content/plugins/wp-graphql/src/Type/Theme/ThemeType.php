<?php
namespace WPGraphQL\Type\Theme;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

/**
 * Class ThemeType
 * @package WPGraphQL\Type
 * @since 0.0.5
 */
class ThemeType extends WPObjectType {

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
	 * ThemeType constructor.
	 * @since 0.0.5
	 */
	public function __construct() {

		/**
		 * Set the type_name
		 * @since 0.0.5
		 */
		self::$type_name = 'Theme';

		$config = [
			'name' => self::$type_name,
			'description' => __( 'A theme object', 'wp-graphql' ),
			'fields' => self::fields(),
			'interfaces' => [ self::node_interface() ],
		];

		parent::__construct( $config );

	}

	/**
	 * fields
	 *
	 * This defines the fields for the ThemeType. The fields are passed through a filter so the shape of the schema
	 * can be modified
	 *
	 * @return array|\GraphQL\Type\Definition\FieldDefinition[]
	 * @since 0.0.5
	 */
	private static function fields() {

		if ( null === self::$fields ) {
			self::$fields = function() {
				$fields = [
					'id'          => [
						'type'    => Types::non_null( Types::id() ),
						'resolve' => function( \WP_Theme $theme, $args, AppContext $context, ResolveInfo $info ) {
							$stylesheet = $theme->get_stylesheet();

							return ( ! empty( $info->parentType ) && ! empty( $stylesheet ) ) ? Relay::toGlobalId( 'theme', $stylesheet ) : null;
						},
					],
					'slug'        => [
						'type'        => Types::string(),
						'description' => __( 'The theme slug is used to internally match themes. Theme slugs can have subdirectories like: my-theme/sub-theme. This field is equivalent to WP_Theme->get_stylesheet().', 'wp-graphql' ),
						'resolve'     => function( \WP_Theme $theme, $args, AppContext $context, ResolveInfo $info ) {
							$stylesheet = $theme->get_stylesheet();

							return ! empty( $stylesheet ) ? $stylesheet : null;
						},
					],
					'name'        => [
						'type'        => Types::string(),
						'description' => __( 'Display name of the theme. This field is equivalent to WP_Theme->get( "Name" ).', 'wp-graphql' ),
						'resolve'     => function( \WP_Theme $theme, $args, AppContext $context, ResolveInfo $info ) {
							$name = $theme->get( 'Name' );

							return ! empty( $name ) ? $name : null;
						},
					],
					'screenshot'  => [
						'type'        => Types::string(),
						'description' => __( 'The URL of the screenshot for the theme. The screenshot is intended to give an overview of what the theme looks like. This field is equivalent to WP_Theme->get_screenshot().', 'wp-graphql' ),
						'resolve'     => function( \WP_Theme $theme, $args, AppContext $context, ResolveInfo $info ) {
							$screenshot = $theme->get_screenshot();

							return ! empty( $screenshot ) ? $screenshot : null;
						},
					],
					'themeUri'    => [
						'type'        => Types::string(),
						'description' => __( 'A URI if the theme has a website associated with it. The Theme URI is handy for directing users to a theme site for support etc. This field is equivalent to WP_Theme->get( "ThemeURI" ).', 'wp-graphql' ),
						'resolve'     => function( \WP_Theme $theme, $args, AppContext $context, ResolveInfo $info ) {
							$theme_uri = $theme->get( 'ThemeURI' );

							return ! empty( $theme_uri ) ? $theme_uri : null;
						},
					],
					'description' => [
						'type'        => Types::string(),
						'description' => __( 'The description of the theme. This field is equivalent to WP_Theme->get( "Description" ).', 'wp-graphql' ),
					],
					'author'      => [
						'type'        => Types::string(),
						'description' => __( 'Name of the theme author(s), could also be a company name. This field is equivalent to WP_Theme->get( "Author" ).', 'wp-graphql' ),
					],
					'authorUri'   => [
						'type'        => Types::string(),
						'description' => __( 'URI for the author/company website. This field is equivalent to WP_Theme->get( "AuthorURI" ).', 'wp-graphql' ),
						'resolve'     => function( \WP_Theme $theme, $args, AppContext $context, ResolveInfo $info ) {
							$author_uri = $theme->get( 'AuthorURI' );

							return ! empty( $author_uri ) ? $author_uri : null;
						},
					],
					'tags'        => [
						'type'        => Types::list_of( Types::string() ),
						'description' => __( 'URI for the author/company website. This field is equivalent to WP_Theme->get( "Tags" ).', 'wp-graphql' ),
					],
					'version'     => [
						'type'        => Types::float(),
						'description' => __( 'The current version of the theme. This field is equivalent to WP_Theme->get( "Version" ).', 'wp-graphql' ),
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
