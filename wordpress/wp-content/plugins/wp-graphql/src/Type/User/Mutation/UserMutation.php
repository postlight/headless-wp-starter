<?php

namespace WPGraphQL\Type\User\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Types;

/**
 * Class UserMutation
 *
 * @package WPGraphQL\Type\User\Mutation
 */
class UserMutation {

	/**
	 * Stores the input fields static definition
	 *
	 * @var array $input_fields
	 * @access private
	 */
	private static $input_fields = [];

	/**
	 * Defines the accepted input arguments
	 *
	 * @return array|null
	 * @access public
	 */
	public static function input_fields() {

		if ( empty( self::$input_fields ) ) {

			$input_fields = [
				'password'    => [
					'type'        => Types::string(),
					'description' => __( 'A string that contains the plain text password for the user.', 'wp-graphql' ),
				],
				'nicename'    => [
					'type'        => Types::string(),
					'description' => __( 'A string that contains a URL-friendly name for the user. The default is the user\'s username.', 'wp-graphql' ),
				],
				'websiteUrl'  => [
					'type'        => Types::string(),
					'description' => __( 'A string containing the user\'s URL for the user\'s web site.', 'wp-grapql' ),
				],
				'email'       => [
					'type'        => Types::string(),
					'description' => __( 'A string containing the user\'s email address.', 'wp-graphql' ),
				],
				'displayName' => [
					'type'        => Types::string(),
					'description' => __( 'A string that will be shown on the site. Defaults to user\'s username. It is likely that you will want to change this, for both appearance and security through obscurity (that is if you dont use and delete the default admin user).', 'wp-graphql' ),
				],
				'nickname'    => [
					'type'        => Types::string(),
					'description' => __( 'The user\'s nickname, defaults to the user\'s username.', 'wp-graphql' ),
				],
				'firstName'   => [
					'type'        => Types::string(),
					'description' => __( '	The user\'s first name.', 'wp-graphql' ),
				],
				'lastName'    => [
					'type'        => Types::string(),
					'description' => __( 'The user\'s last name.', 'wp-graphql' ),
				],
				'description' => [
					'type'        => Types::string(),
					'description' => __( 'A string containing content about the user.', 'wp-graphql' ),
				],
				'richEditing' => [
					'type'        => Types::string(),
					'description' => __( 'A string for whether to enable the rich editor or not. False if not empty.', 'wp-graphql' ),
				],
				'registered'  => [
					'type'        => Types::string(),
					'description' => __( 'The date the user registered. Format is Y-m-d H:i:s.', 'wp-graphql' ),
				],
				'roles'       => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'An array of roles to be assigned to the user.', 'wp-graphql' ),
				],
				'jabber'      => [
					'type'        => Types::string(),
					'description' => __( 'User\'s Jabber account.', 'wp-graphql' ),
				],
				'aim'         => [
					'type'        => Types::string(),
					'description' => __( 'User\'s AOL IM account.', 'wp-graphql' ),
				],
				'yim'         => [
					'type'        => Types::string(),
					'description' => __( 'User\'s Yahoo IM account.', 'wp-graphql' ),
				],
				'locale'      => [
					'type'        => Types::string(),
					'description' => __( 'User\'s locale.', 'wp-graphql' ),
				],
			];

			/**
			 * Filters all of the fields available for input
			 *
			 * @var array $input_fields
			 */
			self::$input_fields = apply_filters( 'graphql_user_mutation_input_fields', $input_fields );

		}

		return ( ! empty( self::$input_fields ) ) ? self::$input_fields : null;

	}

	/**
	 * Maps the GraphQL input to a format that the WordPress functions can use
	 *
	 * @param array  $input         Data coming from the GraphQL mutation query input
	 * @param string $mutation_name Name of the mutation being performed
	 *
	 * @access public
	 * @return array
	 */
	public static function prepare_user_object( $input, $mutation_name ) {

		$insert_user_args = [];

		if ( ! empty( $input['password'] ) ) {
			$insert_user_args['user_pass'] = $input['password'];
		} else {
			$insert_user_args['user_pass'] = null;
		}

		if ( ! empty( $input['username'] ) ) {
			$insert_user_args['user_login'] = $input['username'];
		}

		if ( ! empty( $input['nicename'] ) ) {
			$insert_user_args['user_nicename'] = $input['nicename'];
		}

		if ( ! empty( $input['websiteUrl'] ) ) {
			$insert_user_args['user_url'] = esc_url( $input['websiteUrl'] );
		}

		if ( ! empty( $input['email'] ) ) {
			if ( false === is_email( apply_filters( 'pre_user_email', $input['email'] ) ) ) {
				throw new UserError( __( 'The email address you are trying to use is invalid', 'graphql' ) );
			}
			$insert_user_args['user_email'] = $input['email'];
		}

		if ( ! empty( $input['displayName'] ) ) {
			$insert_user_args['display_name'] = $input['displayName'];
		}

		if ( ! empty( $input['nickname'] ) ) {
			$insert_user_args['nickname'] = $input['nickname'];
		}

		if ( ! empty( $input['firstName'] ) ) {
			$insert_user_args['first_name'] = $input['firstName'];
		}

		if ( ! empty( $input['lastName'] ) ) {
			$insert_user_args['last_name'] = $input['lastName'];
		}

		if ( ! empty( $input['description'] ) ) {
			$insert_user_args['description'] = $input['description'];
		}

		if ( ! empty( $input['richEditing'] ) ) {
			$insert_user_args['rich_editing'] = $input['richEditing'];
		}

		if ( ! empty( $input['registered'] ) ) {
			$insert_user_args['user_registered'] = $input['registered'];
		}

		if ( ! empty( $input['roles'] ) ) {
			/**
			 * Pluck the first role out of the array since the insert and update functions only
			 * allow one role to be set at a time. We will add all of the roles passed to the
			 * mutation later on after the initial object has been created or updated.
			 */
			$insert_user_args['role'] = $input['roles'][0];
		}

		if ( ! empty( $input['locale'] ) ) {
			$insert_user_args['locale'] = $input['locale'];
		}

		/**
		 * Filters the mappings for input to arguments
		 *
		 * @var array  $insert_user_args The arguments to ultimately be passed to the WordPress function
		 * @var array  $input            Input data from the GraphQL mutation
		 * @var string $mutation_name    What user mutation is being performed for context
		 */
		$insert_user_args = apply_filters( 'graphql_user_insert_post_args', $insert_user_args, $input, $mutation_name );

		return $insert_user_args;

	}

	/**
	 * This updates additional data related to the user object after the initial mutation has happened
	 *
	 * @param int         $user_id       The ID of the user being mutated
	 * @param array       $input         The input data from the GraphQL query
	 * @param string      $mutation_name Name of the mutation currently being run
	 * @param AppContext  $context       The AppContext passed down the resolve tree
	 * @param ResolveInfo $info          The ResolveInfo passed down the Resolve Tree
	 */
	public static function update_additional_user_object_data( $user_id, $input, $mutation_name, AppContext $context, ResolveInfo $info ) {

		$roles = ! empty( $input['roles'] ) ? $input['roles'] : [];
		self::add_user_roles( $user_id, $roles );

		/**
		 * Run an action after the additional data has been updated. This is a great spot to hook into to
		 * update additional data related to users, such as setting relationships, updating additional usermeta,
		 * or sending emails to Kevin... whatever you need to do with the userObject.
		 *
		 * @param int         $user_id       The ID of the user being mutated
		 * @param array       $input         The input for the mutation
		 * @param string      $mutation_name The name of the mutation (ex: create, update, delete)
		 * @param AppContext  $context       The AppContext passed down the resolve tree
		 * @param ResolveInfo $info          The ResolveInfo passed down the Resolve Tree
		 */
		do_action( 'graphql_user_object_mutation_update_additional_data', $user_id, $input, $mutation_name, $context, $info );

	}

	/**
	 * Method to add user roles to a user object
	 *
	 * @param int   $user_id The ID of the user
	 * @param array $roles   List of roles that need to get added to the user
	 *
	 * @access private
	 */
	private static function add_user_roles( $user_id, $roles ) {

		if ( empty( $roles ) || ! is_array( $roles ) ) {
			return;
		}

		$user = get_user_by( 'ID', $user_id );

		if ( false !== $user ) {
			foreach ( $roles as $role ) {
				self::verify_user_role( $role );
				$user->add_role( $role );
			}
		}

	}

	/**
	 * Method to check if the user role is valid, and if the current user has permission to add, or remove it from a
	 * user.
	 *
	 * @param string $role Name of the role trying to get added to a user object
	 *
	 * @return bool
	 * @throws \Exception
	 * @access private
	 */
	private static function verify_user_role( $role ) {

		/**
		 * The function for this is only loaded on admin pages. See note: https://codex.wordpress.org/Function_Reference/get_editable_roles#Notes
		 */
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/admin.php';
		}

		$editable_roles = get_editable_roles();

		if ( empty( $editable_roles[ $role ] ) ) {
			// Translators: %s is the name of the role that can't be added to the user.
			throw new UserError( sprintf( __( 'Sorry, you are not allowed to give this the following role: %s.', 'wp-graphql' ), $role ) );
		} else {
			return true;
		}

	}

}
