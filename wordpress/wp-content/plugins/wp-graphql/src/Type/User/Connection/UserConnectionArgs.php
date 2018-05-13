<?php
namespace WPGraphQL\Type\User\Connection;

use GraphQL\Type\Definition\EnumType;
use WPGraphQL\Type\WPEnumType;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

/**
 * Class UserConnectionArgs
 *
 * This sets up the Query Args for user connections, which uses WP_User_Query, so this defines the allowed
 * input fields that will be passed to the WP_User_Query
 *
 * @package WPGraphQL\Type
 * @since 0.0.5
 */
class UserConnectionArgs extends WPInputObjectType {

	/**
	 * This holds the field definitions
	 * @var array $fields
	 * @since 0.0.5
	 */
	public static $fields;

	/**
	 * This holds the $roles_enum definition
	 * @var EnumType
	 * @since 0.0.5
	 */
	private static $roles_enum;

	/**
	 * This holds the SearchColumnsEnumType
	 * @var EnumType
	 * @since 0.0.5
	 */
	private static $search_columns_enum;

	/**
	 * UserConnectionArgs constructor.
	 * @param array $config Array of config for the Input Type
	 * @param string $connection The name of the connection the args belong to
	 * @since 0.0.5
	 */
	public function __construct( $config = [], $connection = '' ) {
		$config['name'] = ucfirst( $connection ) . 'UserArgs';
		$config['queryClass'] = 'WP_User_Query';
		$config['fields'] = self::fields( $connection );
		parent::__construct( $config );
	}

	/**
	 * fields
	 *
	 * This defines the fields that make up the UserConnectionArgs
	 *
	 * @param string $connection The name of the connection the Args belong to
	 * @return array
	 * @since 0.0.5
	 */
	private static function fields( $connection ) {

		if ( null === self::$fields ) {
			self::$fields = [];
		}

		if ( empty( self::$fields ) ) {

			$fields                      = [
				'role'              => [
					'type'        => self::roles_enum(),
					'description' => __( 'An array of role names that users must match to be included in results. Note that this is an inclusive list: users must match *each* role.', 'wp-graphql' ),
				],
				'roleIn'            => [
					'type'        => Types::list_of( self::roles_enum() ),
					'description' => __( 'An array of role names. Matched users must have at least one of these roles.', 'wp-graphql' ),
				],
				'roleNotIn'         => [
					'type'        => Types::list_of( self::roles_enum() ),
					'description' => __( 'An array of role names to exclude. Users matching one or more of these roles will not be included in results.', 'wp-graphql' ),
				],
				'include'           => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of comment IDs to include.', 'wp-graphql' ),
				],
				'exclude'           => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of IDs of users whose unapproved comments will be returned by the query regardless of status.', 'wp-graphql' ),
				],
				'search'            => [
					'type'        => Types::string(),
					'description' => __( 'Search keyword. Searches for possible string matches on columns. When `searchColumns` is left empty, it tries to determine which column to search in based on search string.', 'wp-graphql' ),
				],
				'searchColumns'     => [
					'type'        => Types::list_of( self::search_columns_enum() ),
					'description' => __( 'Array of column names to be searched. Accepts \'ID\', \'login\', \'nicename\', \'email\', \'url\'.', 'wp-graphql' ),
				],
				'hasPublishedPosts' => [
					'type'        => Types::list_of( Types::post_type_enum() ),
					'description' => __( 'Pass an array of post types to filter results to users who have published posts in those post types.', 'wp-graphql' ),
				],
				'nicename'          => [
					'type'        => Types::string(),
					'description' => __( 'The user nicename.', 'wp-graphql' ),
				],
				'nicenameIn'        => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'An array of nicenames to include. Users matching one of these nicenames will be included in results.', 'wp-graphql' ),
				],
				'nicenameNotIn'     => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'An array of nicenames to exclude. Users matching one of these nicenames will not be included in results.', 'wp-graphql' ),
				],
				'login'             => [
					'type'        => Types::string(),
					'description' => __( 'The user login.', 'wp-graphql' ),
				],
				'loginIn'           => [
					'type'        => Types::int(),
					'description' => __( 'An array of logins to include. Users matching one of these logins will be included in results.', 'wp-graphql' ),
				],
				'loginNotIn'        => [
					'type'        => Types::int(),
					'description' => __( 'An array of logins to exclude. Users matching one of these logins will not be included in results.', 'wp-graphql' ),
				],
			];
			self::$fields[ $connection ] = self::prepare_fields( $fields, ucfirst( $connection ) . 'UserArgs' );
		}

		return ! empty( self::$fields[ $connection ] ) ? self::$fields[ $connection ]: null;

	}

	/**
	 * search_columns_enum
	 *
	 * Returns the searchColumnsEnum type defintion
	 *
	 * @return EnumType
	 * @since 0.0.5
	 */
	private static function search_columns_enum() {

		if ( null === self::$search_columns_enum ) {
			self::$search_columns_enum = new WPEnumType( [
				'name'   => 'SearchColumnsEnum',
				'values' => [
					'ID'       => [
						'value' => 'ID',
					],
					'LOGIN'    => [
						'value' => 'login',
					],
					'NICENAME' => [
						'value' => 'nicename',
					],
					'EMAIL'    => [
						'value' => 'email',
					],
					'URL'      => [
						'value' => 'url',
					],
				],
			] );
		}
		return self::$search_columns_enum;
	}

	/**
	 * roles_enum
	 *
	 * Returns the userRoleEnum type definition
	 *
	 * @return EnumType
	 * @since 0.0.5
	 */
	private static function roles_enum() {

		if ( null === self::$roles_enum ) {
			global $wp_roles;
			$all_roles      = $wp_roles->roles;
			$editable_roles = apply_filters( 'editable_roles', $all_roles );
			$roles          = [];

			if ( ! empty( $editable_roles ) && is_array( $editable_roles ) ) {
				foreach ( $editable_roles as $key => $role ) {

					$formatted_role = self::format_enum_name( $role['name'] );

					$roles[ $formatted_role ] = [
						'value' => $key,
					];
				}
			}

			if ( ! empty( $roles ) ) {
				self::$roles_enum = new WPEnumType( [
					'name'   => 'UserRoleEnum',
					'values' => $roles,
				] );
			}
		}

		return self::$roles_enum;
	}

}
