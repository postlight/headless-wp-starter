<?php
namespace WPGraphQL\Type\User;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\Comment\Connection\CommentConnectionDefinition;
use WPGraphQL\Type\PostObject\Connection\PostObjectConnectionDefinition;
use WPGraphQL\Type\WPEnumType;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

/**
 * Class UserType
 * @package WPGraphQL\Type
 * @since 0.0.5
 */
class UserType extends WPObjectType {

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
	 * UserType constructor.
	 * @since 0.0.5
	 */
	public function __construct() {

		/**
		 * Set the type_name
		 * @since 0.0.5
		 */
		self::$type_name = 'User';

		$config = [
			'name' => self::$type_name,
			'description' => __( 'A User object', 'wp-graphql' ),
			'fields' => self::fields(),
			'interfaces' => [ self::node_interface() ],
		];

		parent::__construct( $config );

	}

	/**
	 * fields
	 *
	 * This defines the fields for the UserType. The fields are passed through a filter so the shape of the schema
	 * can be modified
	 *
	 * @return array|\GraphQL\Type\Definition\FieldDefinition[]
	 * @since 0.0.5
	 */
	private static function fields() {

		if ( null === self::$fields ) {
			self::$fields = function() {
				$fields = [
					'id'                => [
						'type'        => Types::non_null( Types::id() ),
						'description' => __( 'The globally unique identifier for the user', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ( ! empty( $info->parentType ) && ! empty( $user->ID ) ) ? Relay::toGlobalId( 'user', $user->ID ) : null;
						},
					],
					'capabilities'      => [
						'type'        => Types::list_of( Types::string() ),
						'description' => __( 'This field is the id of the user. The id of the user matches WP_User->ID field and the value in the ID column for the `users` table in SQL.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							if ( ! empty( $user->allcaps ) ) {
								// Filters list for capabilities the user has.
								$capabilities = array_keys( array_filter( $user->allcaps, function( $cap ) {
									return true === $cap;
								} ) );
							}

							return ! empty( $capabilities ) ? $capabilities : null;
						},
					],
					'capKey'            => [
						'type'        => Types::string(),
						'description' => __( 'User metadata option name. Usually it will be `wp_capabilities`.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->cap_key ) ? $user->cap_key : null;
						},
					],
					'roles'             => [
						'type'        => Types::list_of( Types::string() ),
						'description' => __( 'A list of roles that the user has. Roles can be used for querying for certain types of users, but should not be used in permissions checks.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->roles ) ? $user->roles : null;
						},
					],
					'email'             => [
						'type'        => Types::string(),
						'description' => __( 'Email of the user. This is equivalent to the WP_User->user_email property.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->user_email ) ? $user->user_email : null;
						},
					],
					'firstName'         => [
						'type'        => Types::string(),
						'description' => __( 'First name of the user. This is equivalent to the WP_User->user_first_name property.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->first_name ) ? $user->first_name : null;
						},
					],
					'lastName'          => [
						'type'        => Types::string(),
						'description' => __( 'Last name of the user. This is equivalent to the WP_User->user_last_name property.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->last_name ) ? $user->last_name : null;
						},
					],
					'extraCapabilities' => [
						'type'        => Types::list_of( Types::string() ),
						'description' => __( 'A complete list of capabilities including capabilities inherited from a role. This is equivalent to the array keys of WP_User->allcaps.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->allcaps ) ? array_keys( $user->allcaps ) : null;
						},
					],
					'description'       => [
						'type'        => Types::string(),
						'description' => __( 'Description of the user.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->description ) ? $user->description : null;
						},
					],
					'username'          => [
						'type'        => Types::string(),
						'description' => __( 'Username for the user. This field is equivalent to WP_User->user_login.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, $context, ResolveInfo $info ) {
							return ! empty( $user->user_login ) ? $user->user_login : null;
						},
					],
					'name'              => [
						'type'        => Types::string(),
						'description' => __( 'Display name of the user. This is equivalent to the WP_User->dispaly_name property.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->display_name ) ? $user->display_name : null;
						},
					],
					'registeredDate'    => [
						'type'        => Types::string(),
						'description' => __( 'The date the user registered or was created. The field follows a full ISO8601 date string format.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->user_registered ) ? date( 'c', strtotime( $user->user_registered ) ) : null;
						},
					],
					'nickname'          => [
						'type'        => Types::string(),
						'description' => __( 'Nickname of the user.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->nickname ) ? $user->nickname : null;
						},
					],
					'url'               => [
						'type'        => Types::string(),
						'description' => __( 'A website url that is associated with the user.', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->user_url ) ? $user->user_url : null;
						},
					],
					'slug'              => [
						'type'        => Types::string(),
						'description' => __( 'The slug for the user. This field is equivalent to WP_User->user_nicename', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->user_nicename ) ? $user->user_nicename : null;
						},
					],
					'nicename'          => [
						'type'        => Types::string(),
						'description' => __( 'The nicename for the user. This field is equivalent to WP_User->user_nicename', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->user_nicename ) ? $user->user_nicename : null;
						},
					],
					'locale'            => [
						'type'        => Types::string(),
						'description' => __( 'The preferred language locale set for the user. Value derived from get_user_locale().', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							$user_locale = get_user_locale( $user );

							return ! empty( $user_locale ) ? $user_locale : null;
						},
					],
					'userId'            => [
						'type'        => Types::int(),
						'description' => __( 'The Id of the user. Equivelant to WP_User->ID', 'wp-graphql' ),
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $user->ID ) ? $user->ID : null;
						},
					],
					'avatar'            => [
						'type'        => Types::avatar(),
						'description' => __( 'Avatar object for user. The avatar object can be retrieved in different sizes by specifying the size argument.', 'wp-graphql' ),
						'args'        => [
							'size'         => [
								'type'         => Types::int(),
								'description'  => __( 'The size attribute of the avatar field can be used to fetch avatars of different sizes. The value corresponds to the dimension in pixels to fetch. The default is 96 pixels.', 'wp-graphql' ),
								'defaultValue' => 96,
							],
							'forceDefault' => [
								'type'        => Types::boolean(),
								'description' => __( 'Whether to always show the default image, never the Gravatar. Default false' ),
							],
							'rating'       => [
								'type' => new WPEnumType( [
									'name'        => 'AvatarRatingEnum',
									'description' => __( 'What rating to display avatars up to. Accepts \'G\', \'PG\', \'R\', \'X\', and are judged in that order. Default is the value of the \'avatar_rating\' option', 'wp-graphql' ),
									'values'      => [
										'G'  => [
											'value' => 'G',
										],
										'PG' => [
											'value' => 'PG',
										],
										'R'  => [
											'value' => 'R',
										],
										'X'  => [
											'value' => 'X',
										],
									],
								] ),
							],

						],
						'resolve'     => function( \WP_User $user, $args, AppContext $context, ResolveInfo $info ) {

							$avatar_args = [];
							if ( is_numeric( $args['size'] ) ) {
								$avatar_args['size'] = absint( $args['size'] );
								if ( ! $avatar_args['size'] ) {
									$avatar_args['size'] = 96;
								}
							}

							if ( ! empty( $args['forceDefault'] ) && true === $args['forceDefault'] ) {
								$avatar_args['force_default'] = true;
							}

							if ( ! empty( $args['rating'] ) ) {
								$avatar_args['rating'] = esc_sql( $args['rating'] );
							}

							$avatar = get_avatar_data( $user->ID, $avatar_args );

							return ( ! empty( $avatar ) && true === $avatar['found_avatar'] ) ? $avatar : null;
						},
					],
					'comments'          => CommentConnectionDefinition::connection( 'User' ),
				];

				/**
				 * Get the allowed_post_types so that we can create a connection from users
				 * to post_types
				 *
				 * @since 0.0.5
				 */
				$allowed_post_types = \WPGraphQL::$allowed_post_types;

				/**
				 * Add connection to each of the allowed post_types as users can have connections
				 * to any post_type.
				 *
				 * @since 0.0.5
				 */
				if ( ! empty( $allowed_post_types ) && is_array( $allowed_post_types ) ) {
					foreach ( $allowed_post_types as $post_type ) {
						// @todo: maybe look into narrowing this based on permissions?
						$post_type_object                                 = get_post_type_object( $post_type );
						$fields[ $post_type_object->graphql_plural_name ] = PostObjectConnectionDefinition::connection( $post_type_object, 'User' );
					}
				}

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
