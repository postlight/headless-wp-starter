<?php

namespace WPGraphQL;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use WPGraphQL\Type\Avatar\AvatarType;
use WPGraphQL\Type\Comment\CommentType;
use WPGraphQL\Type\CommentAuthor\CommentAuthorType;
use WPGraphQL\Type\EditLock\EditLockType;
use WPGraphQL\Type\Enum\MimeTypeEnumType;
use WPGraphQL\Type\Enum\PostObjectFieldFormatEnumType;
use WPGraphQL\Type\Enum\PostStatusEnumType;
use WPGraphQL\Type\Enum\MediaItemStatusEnumType;
use WPGraphQL\Type\Enum\PostTypeEnumType;
use WPGraphQL\Type\Enum\RelationEnumType;
use WPGraphQL\Type\Enum\TaxonomyEnumType;
use WPGraphQL\Type\Setting\SettingType;
use WPGraphQL\Type\Settings\SettingsType;
use WPGraphQL\Type\PostObject\Connection\PostObjectConnectionArgs;
use WPGraphQL\Type\RootMutationType;
use WPGraphQL\Type\RootQueryType;
use WPGraphQL\Type\Plugin\PluginType;
use WPGraphQL\Type\PostObject\PostObjectType;
use WPGraphQL\Type\PostType\PostTypeType;
use WPGraphQL\Type\Taxonomy\TaxonomyType;
use WPGraphQL\Type\TermObject\Connection\TermObjectConnectionArgs;
use WPGraphQL\Type\TermObject\TermObjectType;
use WPGraphQL\Type\Theme\ThemeType;
use WPGraphQL\Type\Union\CommentAuthorUnionType;
use WPGraphQL\Type\Union\PostObjectUnionType;
use WPGraphQL\Type\Union\TermObjectUnionType;
use WPGraphQL\Type\User\Connection\UserConnectionArgs;
use WPGraphQL\Type\User\UserType;

/**
 * Class Types - Acts as a registry and factory for Types.
 *
 * Each "type" is static ensuring that it will only be instantiated once and can be re-used
 * throughout the system. The types that are "dynamic" (such as post_types, taxonomies, etc)
 * are added as a sub-property to the Types class based on their unique identifier, and are
 * therefore only instantiated once as well.
 *
 * @since   0.0.5
 * @package WPGraphQL
 */
class Types {

	/**
	 * Stores the avatar type object
	 *
	 * @var AvatarType object $avatar
	 * @since  0.5.0
	 * @access private
	 */
	private static $avatar;

	/**
	 * Stores the comment type object
	 *
	 * @var CommentType object $comment
	 * @since  0.5.0
	 * @access private
	 */
	private static $comment;

	/**
	 * Stores the comment author type object
	 *
	 * @var CommentAuthorType object $comment_author
	 * @since  0.0.21
	 * @access private
	 */
	private static $comment_author;

	/**
	 * Stores the comment author union type config
	 *
	 * @var CommentAuthorUnionType object $comment_author_union
	 * @since  0.0.21
	 * @access private
	 */
	private static $comment_author_union;

	/**
	 * Stores the EditLock definition
	 *
	 * @var EditLockType object $edit_lock
	 * @access private
	 */
	private static $edit_lock;

	/**
	 * Stores the mime type enum object
	 *
	 * @var MimeTypeEnumType object $mime_type_enum
	 * @since  0.5.0
	 * @access private
	 */
	private static $mime_type_enum;

	/**
	 * Stores the plugin type object
	 *
	 * @var PluginType $plugin
	 * @since  0.5.0
	 * @access private
	 */
	private static $plugin;

	/**
	 * Stores the post object type
	 *
	 * @var PostObjectType $post_object
	 * @since  0.5.0
	 * @access private
	 */
	private static $post_object;

	/**
	 * Stores the post object type query args
	 *
	 * @var PostObjectConnectionArgs object $post_object_query_args
	 * @since  0.5.0
	 * @access private
	 */
	private static $post_object_query_args;

	/**
	 * Stores the post object union type config
	 *
	 * @var PostObjectUnionType object $post_object_union
	 * @since  0.0.6
	 * @access private
	 */
	private static $post_object_union;

	/**
	 * Stores the post object field format enum type object
	 *
	 * @var PostObjectFieldFormatEnumType object $post_object_field_format_enum
	 * @since  0.0.18
	 * @access private
	 */
	private static $post_object_field_format_enum;

	/**
	 * Stores the post status enum type object
	 *
	 * @var PostStatusEnumType object $post_status_enum
	 * @since  0.0.5
	 * @access private
	 */
	private static $post_status_enum;

	/**
	 * Stores the media item (attachment) status enum type object
	 *
	 * @var MediaItemStatusEnumType object $media_item_status_enum
	 * @access private
	 */
	private static $media_item_status_enum;

	/**
	 * Stores the post type enum type object
	 *
	 * @var PostTypeEnumType object $post_type_enum
	 * @since  0.5.0
	 * @access private
	 */
	private static $post_type_enum;

	/**
	 * Stores the post type type object
	 *
	 * @var PostTypeType object $post_type
	 * @since  0.5.0
	 * @access private
	 */
	private static $post_type;

	/**
	 * Stores the relation enum type object
	 *
	 * @var RelationEnumType object $relation_enum
	 * @since  0.5.0
	 * @access private
	 */
	private static $relation_enum;

	/**
	 * Stores the root mutation type object
	 *
	 * @var RootMutationType object $root_mutation
	 * @since  0.0.6
	 * @access private
	 */
	private static $root_mutation;

	/**
	 * Stores the root query type object
	 *
	 * @var RootQueryType object $root_query
	 * @since  0.5.0
	 * @access private
	 */
	private static $root_query;

	/**
	 * Stores the setting object type
	 *
	 * @var SettingType object $setting
	 * @access private
	 */
	private static $setting;

	/**
	 * Stores the settings object type
	 *
	 * @var SettingsType object $settings
	 * @access private
	 */
	private static $settings;
	
	/**
	 * Stores the taxonomy type object
	 *
	 * @var TaxonomyType object $taxonomy
	 * @since  0.5.0
	 * @access private
	 */
	private static $taxonomy;

	/**
	 * Stores the taxonomy enum type object
	 *
	 * @var TaxonomyEnumType object $taxonomy_enum
	 * @since  0.5.0
	 * @access private
	 */
	private static $taxonomy_enum;

	/**
	 * Stores the term type object
	 *
	 * @var TermObjectType object $term_object
	 * @since  0.5.0
	 * @access private
	 */
	private static $term_object;

	/**
	 * Stores the term object union definition
	 *
	 * @var TermObjectUnionType object $term_object_union
	 * @access private
	 */
	private static $term_object_union;

	/**
	 * Stores the term object query args type
	 *
	 * @var TermObjectConnectionArgs object $term_object_query_args
	 * @since  0.5.0
	 * @access private
	 */
	private static $term_object_query_args;

	/**
	 * Stores the theme type object
	 *
	 * @var ThemeType object $theme
	 * @since  0.5.0
	 * @access private
	 */
	private static $theme;

	/**
	 * Stores the user type object
	 *
	 * @var UserType object $user
	 * @since  0.5.0
	 * @access private
	 */
	private static $user;

	/**
	 * Stores the user connection query args type object
	 *
	 * @var UserConnectionArgs object $user_connection_query_args
	 * @since  0.5.0
	 * @access private
	 */
	private static $user_connection_query_args;

	/**
	 * This returns the definition for the AvatarType
	 *
	 * @return AvatarType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function avatar() {
		return self::$avatar ? : ( self::$avatar = new AvatarType() );
	}

	/**
	 * This returns the definition for the CommentType
	 *
	 * @return CommentType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function comment() {
		return self::$comment ? : ( self::$comment = new CommentType() );
	}

	/**
	 * This returns the definition for the CommentAuthorType
	 *
	 * @return CommentAuthorType object
	 * @since  0.0.21
	 * @access public
	 */
	public static function comment_author() {
		return self::$comment_author ? : ( self::$comment_author = new CommentAuthorType() );
	}

	/**
	 * This returns the definition for the PostObjectUnionType
	 *
	 * @return CommentAuthorUnionType object
	 * @since  0.0.21
	 * @access public
	 */
	public static function comment_author_union() {
		return self::$comment_author_union ? : ( self::$comment_author_union = new CommentAuthorUnionType() );
	}

	/**
	 * This returns the definition for the EditLock type
	 *
	 * @return EditLockType object
	 * @access public
	 */
	public static function edit_lock() {
		return self::$edit_lock ? : ( self::$edit_lock = new EditLockType() );
	}

	/**
	 * This returns the definition for the MimeTypeEnumType
	 *
	 * @return MimeTypeEnumType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function mime_type_enum() {
		return self::$mime_type_enum ? : ( self::$mime_type_enum = new MimeTypeEnumType() );
	}

	/**
	 * This returns the definition for the SettingType
	 *
	 * @return SettingType object
	 * @access public
	 */
	public static function setting( $setting_type ) {

		if ( null === self::$setting ) {
			self::$setting = [];
		}

		if ( empty( self::$setting[ $setting_type ] ) ) {
			self::$setting[ $setting_type ] = new SettingType( $setting_type );
		}

		return ! empty( self::$setting[ $setting_type ] ) ? self::$setting[ $setting_type ] : null;

	}

	/**
	 * This returns the definition for the SettingsType
	 *
	 * @return SettingsType object
	 * @access public
	 */
	public static function settings() {

		if ( empty( self::$settings ) ) {
			self::$settings = new SettingsType();
		}

		return ! empty( self::$settings ) ? self::$settings : null;
	}

	/**
	 * This returns the definition for the PluginType
	 *
	 * @return PluginType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function plugin() {
		return self::$plugin ? : ( self::$plugin = new PluginType() );
	}

	/**
	 * This returns the definition for the PostObjectType
	 *
	 * @param string $post_type Name of the post type you want to retrieve the PostObjectType for
	 *
	 * @return PostObjectType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function post_object( $post_type ) {

		if ( null === self::$post_object ) {
			self::$post_object = [];
		}

		if ( empty( self::$post_object[ $post_type ] ) ) {
			self::$post_object[ $post_type ] = new PostObjectType( $post_type );
		}

		return ! empty( self::$post_object[ $post_type ] ) ? self::$post_object[ $post_type ] : null;

	}

	/**
	 * This returns the definition for the PostObjectUnionType
	 *
	 * @return PostObjectUnionType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function post_object_union() {
		return self::$post_object_union ? : ( self::$post_object_union = new PostObjectUnionType() );
	}

	/**
	 * This returns the definition for the PostObjectFieldFormatEnumType
	 *
	 * @return PostObjectFieldFormatEnumType object
	 * @since  0.1.18
	 * @access public
	 */
	public static function post_object_field_format_enum() {
		return self::$post_object_field_format_enum ? : ( self::$post_object_field_format_enum = new PostObjectFieldFormatEnumType() );
	}

	/**
	 * This returns the definition for the PostStatusEnumType
	 *
	 * @return PostStatusEnumType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function post_status_enum() {
		return self::$post_status_enum ? : ( self::$post_status_enum = new PostStatusEnumType() );
	}

	/**
	 * This returns the definition for the MediaItemStatusEnumType
	 *
	 * @return MediaItemStatusEnumType object
	 * @access public
	 */
	public static function media_item_status_enum() {
		return self::$media_item_status_enum ? : ( self::$media_item_status_enum = new MediaItemStatusEnumType() );
	}

	/**
	 * This returns the definition for the PostStatusEnumType
	 *
	 * @return PostTypeEnumType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function post_type_enum() {
		return self::$post_type_enum ? : ( self::$post_type_enum = new PostTypeEnumType() );
	}

	/**
	 * This returns the definition for the PostObjectConnectionArgs
	 * @param string $connection The connection the args belong to
	 * @return PostObjectConnectionArgs object
	 * @since  0.0.5
	 * @access public
	 */
	public static function post_object_query_args( $connection ) {

		if ( null === self::$post_object_query_args ) {
			self::$post_object_query_args = [];
		}

		if ( empty( self::$post_object_query_args[ $connection ] ) ) {
			self::$post_object_query_args[ $connection ] = new PostObjectConnectionArgs( [], $connection );
		}

		return ! empty( self::$post_object_query_args[ $connection ] ) ? self::$post_object_query_args[ $connection ] : null;

	}

	/**
	 * This returns the definition for the PostTypeType
	 *
	 * @return PostTypeType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function post_type() {
		return self::$post_type ? : ( self::$post_type = new PostTypeType() );
	}

	/**
	 * This returns the definition for the RelationEnum
	 *
	 * @return RelationEnumType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function relation_enum() {
		return self::$relation_enum ? : ( self::$relation_enum = new RelationEnumType() );
	}

	/**
	 * This returns the definition for the RootMutationType
	 *
	 * @return RootMutationType object
	 * @since  0.0.8
	 * @access public
	 */
	public static function root_mutation() {
		return self::$root_mutation ? : ( self::$root_mutation = new RootMutationType() );
	}


	/**
	 * This returns the definition for the RootQueryType
	 *
	 * @return RootQueryType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function root_query() {
		return self::$root_query ? : ( self::$root_query = new RootQueryType() );
	}

	/**
	 * This returns the definition for the TaxonomyType
	 *
	 * @return TaxonomyType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function taxonomy() {
		return self::$taxonomy ? : ( self::$taxonomy = new TaxonomyType() );
	}

	/**
	 * This returns the definition for the TaxonomyEnumType
	 *
	 * @return TaxonomyEnumType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function taxonomy_enum() {
		return self::$taxonomy_enum ? : ( self::$taxonomy_enum = new TaxonomyEnumType() );
	}

	/**
	 * This returns the definition for the TermObjectType
	 *
	 * @param string $taxonomy Name of the taxonomy you want to get the TermObjectType for
	 *
	 * @return TermObjectType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function term_object( $taxonomy ) {

		if ( null === self::$term_object ) {
			self::$term_object = [];
		}

		if ( empty( self::$term_object[ $taxonomy ] ) ) {
			self::$term_object[ $taxonomy ] = new TermObjectType( $taxonomy );
		}

		return ! empty( self::$term_object[ $taxonomy ] ) ? self::$term_object[ $taxonomy ] : null;

	}

	/**
	 * This returns the definition for the TermObjectConnectionArgs
	 *
	 * @param string $connection
	 * @return TermObjectConnectionArgs object
	 * @since  0.0.5
	 * @access public
	 */
	public static function term_object_query_args( $connection ) {

		if ( null === self::$term_object_query_args ) {
			self::$term_object_query_args = [];
		}

		if ( empty( self::$term_object_query_args[ $connection ] ) ) {
			self::$term_object_query_args[ $connection ] = new TermObjectConnectionArgs( [], $connection );
		}

		return ! empty( self::$term_object_query_args[ $connection ] ) ? self::$term_object_query_args[ $connection ] : null;

	}

	/**
	 * This returns the definition for the termObjectUnionType
	 *
	 * @return TermObjectUnionType object
	 * @access public
	 */
	public static function term_object_union() {
		return self::$term_object_union ? : ( self::$term_object_union = new TermObjectUnionType() );
	}

	/**
	 * This returns the definition for the ThemeType
	 *
	 * @return ThemeType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function theme() {
		return self::$theme ? : ( self::$theme = new ThemeType() );
	}

	/**
	 * This returns the definition for the UserType
	 *
	 * @return UserType object
	 * @since  0.0.5
	 * @access public
	 */
	public static function user() {
		return self::$user ? : ( self::$user = new UserType() );
	}

	/**
	 * This returns the definition for the UserConnectionArgs
	 *
	 * @param string $connection The connection the args are for
	 * @return UserConnectionArgs object
	 * @since  0.0.5
	 * @access public
	 */
	public static function user_connection_query_args( $connection ) {

		if ( null === self::$user_connection_query_args ) {
			self::$user_connection_query_args = [];
		}

		if ( empty( self::$user_connection_query_args ) ) {
			self::$user_connection_query_args[ $connection ] = new UserConnectionArgs( [], $connection );
		}

		return ! empty( self::$user_connection_query_args[ $connection ] ) ? self::$user_connection_query_args[ $connection ] : null;
	}

	/**
	 * This is a wrapper for the GraphQL type to give a consistent experience
	 *
	 * @return \GraphQL\Type\Definition\BooleanType
	 * @since  0.0.5
	 * @access public
	 */
	public static function boolean() {
		return Type::boolean();
	}

	/**
	 * This is a wrapper for the GraphQL type to give a consistent experience
	 *
	 * @return \GraphQL\Type\Definition\FloatType
	 * @since  0.0.5
	 * @access public
	 */
	public static function float() {
		return Type::float();
	}

	/**
	 * This is a wrapper for the GraphQL type to give a consistent experience
	 *
	 * @return \GraphQL\Type\Definition\idType
	 * @since  0.0.5
	 * @access public
	 */
	public static function id() {
		return Type::id();
	}

	/**
	 * This is a wrapper for the GraphQL type to give a consistent experience
	 *
	 * @return \GraphQL\Type\Definition\IntType
	 * @since  0.0.5
	 * @access public
	 */
	public static function int() {
		return Type::int();
	}

	/**
	 * This is a wrapper for the GraphQL type to give a consistent experience
	 *
	 * @return \GraphQL\Type\Definition\StringType
	 * @since  0.0.5
	 * @access public
	 */
	public static function string() {
		return Type::string();
	}

	/**
	 * This is a wrapper for the GraphQL type to give a consistent experience
	 *
	 * @param object $type instance of GraphQL\Type\Definition\Type or callable returning instance
	 *                       of that class
	 *
	 * @return \GraphQL\Type\Definition\ListOfType
	 * @since  0.0.5
	 * @access public
	 */
	public static function list_of( $type ) {
		return new ListOfType( $type );
	}

	/**
	 * This is a wrapper for the GraphQL type to give a consistent experience
	 *
	 * @param object $type instance of GraphQL\Type\Definition\Type or callable returning instance
	 *                       of that class
	 *
	 * @return \GraphQL\Type\Definition\NonNull
	 * @since  0.0.5
	 * @access public
	 */
	public static function non_null( $type ) {
		return new NonNull( $type );
	}

	/**
	 * Resolve the type on the individual setting field
	 * for the settingsType
	 *
	 * @param $type
	 * @access public
	 *
	 * @return \GraphQL\Type\Definition\BooleanType|\GraphQL\Type\Definition\FloatType|\GraphQL\Type\Definition\IntType|\GraphQL\Type\Definition\StringType
	 */
	public static function get_type( $type ) {

		switch ( $type ) {
			case 'integer':
				$type = self::int();
				break;
			case 'float':
			case 'number':
				$type = self::float();
				break;
			case 'boolean':
				$type = self::boolean();
				break;
			case 'string':
			default:
				$type = self::string();
		}

		return $type;
	}

	/**
	 * Maps new input query args and sanitizes the input
	 *
	 * @param array $args The raw query args from the GraphQL query
	 * @param array $map  The mapping of where each of the args should go
	 *
	 * @since  0.5.0
	 * @return array
	 * @access public
	 */
	public static function map_input( $args, $map ) {

		if ( ! is_array( $args ) || ! is_array( $map ) ) {
			return array();
		}

		$query_args = [];

		foreach ( $args as $arg => $value ) {

			if ( is_array( $value ) && ! empty( $value ) ) {
				$value = array_map( function( $value ) {
					if ( is_string( $value ) ) {
						$value = sanitize_text_field( $value );
					}

					return $value;
				}, $value );
			} elseif ( is_string( $value ) ) {
				$value = sanitize_text_field( $value );
			}

			if ( array_key_exists( $arg, $map ) ) {
				$query_args[ $map[ $arg ] ] = $value;
			} else {
				$query_args[ $arg ] = $value;
			}
		}

		return $query_args;

	}

    /**
     * Checks the post_date_gmt or modified_gmt and prepare any post or
     * modified date for single post output.
     *
     * @since 4.7.0
     *
     * @param string      $date_gmt GMT publication time.
     * @param string|null $date     Optional. Local publication time. Default null.
     * @return string|null ISO8601/RFC3339 formatted datetime.
     */
    public static function prepare_date_response( $date_gmt, $date = null ) {
        // Use the date if passed.
        if ( isset( $date ) ) {
            return mysql_to_rfc3339( $date );
        }
        // Return null if $date_gmt is empty/zeros.
        if ( '0000-00-00 00:00:00' === $date_gmt ) {
            return null;
        }
        // Return the formatted datetime.
        return mysql_to_rfc3339( $date_gmt );
    }

}
