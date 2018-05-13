<?php
namespace WPGraphQL\Type\PostObject\Connection;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use WPGraphQL\Type\WPEnumType;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

/**
 * Class PostObjectConnectionArgs
 *
 * This sets up the Query Args for post_object connections, which uses WP_Query, so this defines the allowed
 * input fields that will be passed to the WP_Query
 *
 * @package WPGraphQL\Type
 * @since   0.0.5
 */
class PostObjectConnectionArgs extends WPInputObjectType {

	/**
	 * Stores the date query object
	 *
	 * @var PostObjectConnectionArgsDateQuery obj $date_query
	 * @since  0.5.0
	 * @access private
	 */
	private static $date_query;

	/**
	 * This holds the field definitions
	 *
	 * @var array $fields
	 * @since 0.0.5
	 */
	public static $fields = [];

	/**
	 * This holds the orderby_field input object type
	 *
	 * @var array $orderby_field
	 */
	private static $orderby_field;

	/**
	 * This holds the orderby EnumType definition
	 *
	 * @var EnumType
	 */
	private static $orderby_enum;

	/**
	 * PostObjectConnectionArgs constructor.
	 * @param array $config Array of Config data for the Input Type
	 * @param string $connection The name of the connection the args belong to
	 * @since 0.0.5
	 */
	public function __construct( $config = [], $connection ) {
		$config['name'] = ucfirst( $connection ) . 'QueryArgs';
		$config['queryClass'] = 'WP_Query';
		$config['fields'] = self::fields( $connection );
		parent::__construct( $config );
	}

	/**
	 * fields
	 *
	 * This defines the fields that make up the PostObjectConnectionArgs
	 *
	 * @param string $connection The name of the connection the fields belong to
	 * @return array
	 * @since 0.0.5
	 */
	private static function fields( $connection ) {

		if ( empty( self::$fields[ $connection ] ) ) {
			$fields = [

				/**
				 * Author $args
				 *
				 * @see   : https://codex.wordpress.org/Class_Reference/WP_Query#Author_Parameters
				 * @since 0.0.5
				 */
				'author'       => [
					'type'        => Types::int(),
					'description' => __( 'The user that\'s connected as the author of the object. Use the
							userId for the author object.', 'wp-graphql' ),
				],
				'authorName'   => [
					'type'        => Types::string(),
					'description' => __( 'Find objects connected to the author by the author\'s nicename', 'wp-graphql' ),
				],
				'authorIn'     => [
					'type'        => Types::list_of( Types::id() ),
					'description' => __( 'Find objects connected to author(s) in the array of author\'s userIds', 'wp-graphql' ),
				],
				'authorNotIn'  => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Find objects NOT connected to author(s) in the array of author\'s
							userIds', 'wp-graphql' ),
				],

				/**
				 * Category $args
				 *
				 * @see   : https://codex.wordpress.org/Class_Reference/WP_Query#Category_Parameters
				 * @since 0.0.5
				 */
				'categoryId'   => [
					'type'        => Types::int(),
					'description' => __( 'Category ID', 'wp-graphql' ),
				],
				'categoryName' => [
					'type'        => Types::string(),
					'description' => __( 'Use Category Slug', 'wp-graphql' ),
				],
				'categoryIn'   => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of category IDs, used to display objects from one
										category OR another', 'wp-graphql' ),
				],

				/**
				 * Tag $args
				 *
				 * @see   : https://codex.wordpress.org/Class_Reference/WP_Query#Tag_Parameters
				 * @since 0.0.5
				 */
				'tag'          => [
					'type'        => Types::string(),
					'description' => __( 'Tag Slug', 'wp-graphql' ),
				],
				'tagId'        => [
					'type'        => Types::string(),
					'description' => __( 'Use Tag ID', 'wp-graphql' ),
				],
				'tagIn'        => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of tag IDs, used to display objects from one tag OR
							another', 'wp-graphql' ),
				],
				'tagSlugAnd'   => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'Array of tag slugs, used to display objects from one tag OR
							another', 'wp-graphql' ),
				],
				'tagSlugIn'    => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'Array of tag slugs, used to exclude objects in specified
							tags', 'wp-graphql' ),
				],

				/**
				 * Search Parameter
				 *
				 * @see   : https://codex.wordpress.org/Class_Reference/WP_Query#Search_Parameter
				 * @since 0.0.5
				 */
				'search'       => [
					'name'        => 'search',
					'type'        => Types::string(),
					'description' => __( 'Show Posts based on a keyword search', 'wp-graphql' ),
				],

				/**
				 * Post & Page Parameters
				 *
				 * @see   : https://codex.wordpress.org/Class_Reference/WP_Query#Post_.26_Page_Parameters
				 * @since 0.0.5
				 */
				'id'           => [
					'type'        => Types::int(),
					'description' => __( 'Specific ID of the object', 'wp-graphql' ),
				],
				'name'         => [
					'type'        => Types::string(),
					'description' => __( 'Slug / post_name of the object', 'wp-graphql' ),
				],
				'title'        => [
					'type'        => Types::string(),
					'description' => __( 'Title of the object', 'wp-graphql' ),
				],
				'parent'       => [
					'type'        => Types::string(),
					'description' => __( 'Use ID to return only children. Use 0 to return only top-level
							items', 'wp-graphql' ),
				],
				'parentIn'     => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Specify objects whose parent is in an array', 'wp-graphql' ),
				],
				'parentNotIn'  => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Specify posts whose parent is not in an array', 'wp-graphql' ),
				],
				'in'           => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of IDs for the objects to retrieve', 'wp-graphql' ),
				],
				'notIn'        => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Specify IDs NOT to retrieve. If this is used in the same query as "in",
							it will be ignored', 'wp-graphql' ),
				],
				'nameIn'       => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'Specify objects to retrieve. Use slugs', 'wp-graphql' ),
				],

				/**
				 * Password parameters
				 *
				 * @see   : https://codex.wordpress.org/Class_Reference/WP_Query#Password_Parameters
				 * @since 0.0.2
				 */
				'hasPassword'  => [
					'type'        => Types::boolean(),
					'description' => __( 'True for objects with passwords; False for objects without passwords;
							null for all objects with or without passwords', 'wp-graphql' ),
				],
				'password'     => [
					'type'        => Types::string(),
					'description' => __( 'Show posts with a specific password.', 'wp-graphql' ),
				],

				/**
				 * post_type
				 * NOTE: post_type is intentionally not supported as it's the post_type is the entity entry
				 * point for the queries
				 *
				 * @see   : https://codex.wordpress.org/Class_Reference/WP_Query#Type_Parameters
				 * @since 0.0.2
				 */

				/**
				 * Status parameters
				 *
				 * @see   : https://codex.wordpress.org/Class_Reference/WP_Query#Status_Parameters
				 * @since 0.0.2
				 */
				'status'       => [
					'type' => Types::post_status_enum(),
				],

				/**
				 * List of post status parameters
				 */
				'stati' => [
					'type' => Types::list_of( Types::post_status_enum() ),
				],

				/**
				 * Order & Orderby parameters
				 *
				 * @see   : https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
				 * @since 0.0.2
				 */
				'orderby'      => [
					'type'        => Types::list_of( self::orderby_field() ),
					'description' => __( 'What paramater to use to order the objects by.', 'wp-graphql' ),
				],
				'dateQuery'    => self::date_query(),
				'mimeType'     => [
					'type'        => Types::mime_type_enum(),
					'description' => __( 'Get objects with a specific mimeType property', 'wp-graphql' ),
				],
			];

			self::$fields[ $connection ] = self::prepare_fields( $fields, ucfirst( $connection ) . 'QueryArgs' );
		}
		return ! empty( self::$fields[ $connection ] ) ? self::$fields[ $connection ] : null;
	}

	/**
	 * This returns the definition for the PostObjectConnectionArgsDateQuery
	 *
	 * @return PostObjectConnectionArgsDateQuery object
	 * @since  0.0.5
	 * @access public
	 */
	public static function date_query() {
		return self::$date_query ? : ( self::$date_query = new PostObjectConnectionArgsDateQuery() );
	}

	/**
	 * This returns the orderby field which accepts a field (enum) and an order (enum, ASC/DESC)
	 *
	 * @return InputObjectType object
	 * @access private
	 */
	private static function orderby_field() {
		if ( null === self::$orderby_field ) {

			self::$orderby_field = new WPInputObjectType( [
				'name' => 'OrderByOptions',
				'fields' => self::prepare_fields( [
					'field' => Types::non_null( self::orderby_enum() ),
					'order' => new WPEnumType( [
						'name'   => 'Order',
						'values' => [
							'ASC'  => [ 'value' => 'ASC' ],
							'DESC' => [ 'value' => 'DESC' ],
						],
					] ),
				], 'OrderByOptions' ),
			] );
		}

		return ! empty( self::$orderby_field ) ? self::$orderby_field : null;
	}

	/**
	 * orderby_enum
	 * This returns the orderby enum type for the PostObjectQueryArgs
	 *
	 * @return EnumType
	 * @since 0.0.5
	 */
	private static function orderby_enum() {

		if ( null === self::$orderby_enum ) {
			self::$orderby_enum = new WPEnumType( [
				'name'   => 'OrderBy',
				'values' => [
					'AUTHOR'     => [
						'value'       => 'post_author',
						'description' => __( 'Order by author', 'wp-graphql' ),
					],
					'TITLE'      => [
						'value'       => 'post_title',
						'description' => __( 'Order by title', 'wp-graphql' ),
					],
					'SLUG'       => [
						'value'       => 'post_name',
						'description' => __( 'Order by slug', 'wp-graphql' ),
					],
					'MODIFIED'   => [
						'value'       => 'post_modified',
						'description' => __( 'Order by last modified date', 'wp-graphql' ),
					],
					'DATE'       => [
						'value'       => 'post_date',
						'description' => __( 'Order by publish date', 'wp-graphql' ),
					],
					'PARENT'     => [
						'value'       => 'post_parent',
						'description' => __( 'Order by parent ID', 'wp-graphql' ),
					],
					'IN'         => [
						'value'       => 'post__in',
						'description' => __( 'Preserve the ID order given in the IN array', 'wp-graphql' ),
					],
					'NAME_IN'    => [
						'value'       => 'post_name__in',
						'description' => __( 'Preserve slug order given in the NAME_IN array', 'wp-graphql' ),
					],
					'MENU_ORDER' => [
						'value'       => 'menu_order',
						'description' => __( 'Order by the menu order value', 'wp-graphql' ),
					],
				],
			] );
		}
		return self::$orderby_enum;
	}

}
