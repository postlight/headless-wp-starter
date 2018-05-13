<?php
namespace WPGraphQL\Data;

use GraphQL\Deferred;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;

use WPGraphQL\AppContext;
use WPGraphQL\Type\TermObject\Connection\TermObjectConnectionResolver;
use WPGraphQL\Type\Comment\Connection\CommentConnectionResolver;
use WPGraphQL\Type\Plugin\Connection\PluginConnectionResolver;
use WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver;
use WPGraphQL\Type\Theme\Connection\ThemeConnectionResolver;
use WPGraphQL\Type\User\Connection\UserConnectionResolver;
use WPGraphQL\Types;

/**
 * Class DataSource
 *
 * This class serves as a factory for all the resolvers for queries and mutations. This layer of
 * abstraction over the actual resolve functions allows easier, granular control over versioning as
 * we can change big things behind the scenes if/when needed, and we just need to ensure the
 * call to the DataSource method returns the expected data later on. This should make it easy
 * down the road to version resolvers if/when changes to the WordPress API are rolled out.
 *
 * @package WPGraphQL\Data
 * @since   0.0.4
 */
class DataSource {

	/**
	 * Stores an array of node definitions
	 *
	 * @var array $node_definition
	 * @since  0.0.4
	 * @access protected
	 */
	protected static $node_definition;

	/**
	 * Retrieves a WP_Comment object for the id that gets passed
	 *
	 * @param int $id ID of the comment we want to get the object for
	 *
	 * @return \WP_Comment object
	 * @throws UserError
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_comment( $id ) {

		$comment = \WP_Comment::get_instance( $id );
		if ( empty( $comment ) ) {
			throw new UserError( sprintf( __( 'No comment was found with ID %d', 'wp-graphql' ), absint( $id ) ) );
		}

		return $comment;

	}

	/**
	 * Retrieves a WP_Comment object for the ID that gets passed
	 *
	 * @param string $author_email The ID of the comment the comment author is associated with.
	 *
	 * @return array
	 * @throws
	 */
	public static function resolve_comment_author( $author_email ) {
		global $wpdb;
		$comment_author = $wpdb->get_row( $wpdb->prepare( "SELECT comment_author_email, comment_author, comment_author_url, comment_author_email from $wpdb->comments WHERE comment_author_email = %s LIMIT 1", esc_sql( $author_email ) ) );
		$comment_author = ! empty( $comment_author ) ? ( array ) $comment_author : [];
		$comment_author['is_comment_author'] = true;
		return $comment_author;
	}

	/**
	 * Wrapper for the CommentsConnectionResolver class
	 *
	 * @param             WP_Post  object $source
	 * @param array       $args    Query args to pass to the connection resolver
	 * @param AppContext  $context The context of the query to pass along
	 * @param ResolveInfo $info    The ResolveInfo object
	 *
	 * @return mixed
	 * @since 0.0.5
	 */
	public static function resolve_comments_connection( $source, array $args, $context, ResolveInfo $info ) {
		$resolver = new CommentConnectionResolver();

		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Returns an array of data about the plugin you are requesting
	 *
	 * @param string $name Name of the plugin you want info for
	 *
	 * @return null|array
	 * @throws \Exception
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_plugin( $name ) {

		// Puts input into a url friendly slug format.
		$slug   = sanitize_title( $name );
		$plugin = null;

		// The file may have not been loaded yet.
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		/**
		 * NOTE: This is missing must use and drop in plugins.
		 */
		$plugins = apply_filters( 'all_plugins', get_plugins() );

		/**
		 * Loop through the plugins and find the matching one
		 *
		 * @since 0.0.5
		 */
		foreach ( $plugins as $path => $plugin_data ) {
			if ( sanitize_title( $plugin_data['Name'] ) === $slug ) {
				$plugin         = $plugin_data;
				$plugin['path'] = $path;
				// Exit early when plugin is found.
				break;
			}
		}

		/**
		 * Return the plugin, or throw an exception
		 */
		if ( ! empty( $plugin ) ) {
			return $plugin;
		} else {
			throw new UserError( sprintf( __( 'No plugin was found with the name %s', 'wp-graphql' ), $name ) );
		}
	}

	/**
	 * Wrapper for PluginsConnectionResolver::resolve
	 *
	 * @param \WP_Post    $source  WP_Post object
	 * @param array       $args    Array of arguments to pass to reolve method
	 * @param AppContext  $context AppContext object passed down
	 * @param ResolveInfo $info    The ResolveInfo object
	 *
	 * @return array
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_plugins_connection( $source, array $args, AppContext $context, ResolveInfo $info ) {
		return PluginConnectionResolver::resolve( $source, $args, $context, $info );
	}

	/**
	 * Returns the post object for the ID and post type passed
	 *
	 * @param int    $id        ID of the post you are trying to retrieve
	 * @param string $post_type Post type the post is attached to
	 *
	 * @throws UserError
	 * @since  0.0.5
	 * @return \WP_Post
	 * @access public
	 */
	public static function resolve_post_object( $id, $post_type ) {

		$post_object = \WP_Post::get_instance( $id );
		if ( empty( $post_object ) ) {
			throw new UserError( sprintf( __( 'No %1$s was found with the ID: %2$s', 'wp-graphql' ), $id, $post_type ) );
		}

		/**
		 * Set the resolving post to the global $post. That way any filters that
		 * might be applied when resolving fields can rely on global post and
		 * post data being set up.
		 */
		$GLOBALS['post'] = $post_object;

		return $post_object;

	}

	/**
	 * Wrapper for PostObjectsConnectionResolver
	 *
	 * @param string      $post_type Post type of the post we are trying to resolve
	 * @param             $source
	 * @param array       $args      Arguments to pass to the resolve method
	 * @param AppContext  $context   AppContext object to pass down
	 * @param ResolveInfo $info      The ResolveInfo object
	 *
	 * @return mixed
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_post_objects_connection( $source, array $args, AppContext $context, ResolveInfo $info, $post_type ) {
		$resolver = new PostObjectConnectionResolver( $post_type );

		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Gets the post type object from the post type name
	 *
	 * @param string $post_type Name of the post type you want to retrieve the object for
	 *
	 * @return \WP_Post_Type object
	 * @throws UserError
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_post_type( $post_type ) {

		/**
		 * Get the allowed_post_types
		 */
		$allowed_post_types = \WPGraphQL::get_allowed_post_types();

		/**
		 * If the $post_type is one of the allowed_post_types
		 */
		if ( in_array( $post_type, $allowed_post_types, true ) ) {
			return get_post_type_object( $post_type );
		} else {
			throw new UserError( sprintf( __( 'No post_type was found with the name %s', 'wp-graphql' ), $post_type ) );
		}

	}

	/**
	 * Retrieves the taxonomy object for the name of the taxonomy passed
	 *
	 * @param string $taxonomy Name of the taxonomy you want to retrieve the taxonomy object for
	 *
	 * @return \WP_Taxonomy object
	 * @throws UserError
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_taxonomy( $taxonomy ) {

		/**
		 * Get the allowed_taxonomies
		 */
		$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies();

		/**
		 * If the $post_type is one of the allowed_post_types
		 */
		if ( in_array( $taxonomy, $allowed_taxonomies, true ) ) {
			return get_taxonomy( $taxonomy );
		} else {
			throw new UserError( sprintf( __( 'No taxonomy was found with the name %s', 'wp-graphql' ), $taxonomy ) );
		}

	}

	/**
	 * Get the term object for a term
	 *
	 * @param int    $id       ID of the term you are trying to retrieve the object for
	 * @param string $taxonomy Name of the taxonomy the term is in
	 *
	 * @return mixed
	 * @throws UserError
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_term_object( $id, $taxonomy ) {

		$term_object = \WP_Term::get_instance( $id, $taxonomy );
		if ( empty( $term_object ) ) {
			throw new UserError( sprintf( __( 'No %1$s was found with the ID: %2$s', 'wp-graphql' ), $taxonomy, $id ) );
		}

		return $term_object;

	}

	/**
	 * Wrapper for TermObjectConnectionResolver::resolve
	 *
	 * @param              $source
	 * @param array        $args     Array of args to be passed to the resolve method
	 * @param AppContext   $context  The AppContext object to be passed down
	 * @param ResolveInfo  $info     The ResolveInfo object
	 * @param \WP_Taxonomy $taxonomy The WP_Taxonomy object of the taxonomy the term is connected to
	 *
	 * @return array
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_term_objects_connection( $source, array $args, $context, ResolveInfo $info, $taxonomy ) {
		$resolver = new TermObjectConnectionResolver( $taxonomy );

		return $resolver->resolve( $source, $args, $context, $info );
	}

	/**
	 * Retrieves the theme object for the theme you are looking for
	 *
	 * @param string $stylesheet Directory name for the theme.
	 *
	 * @return \WP_Theme object
	 * @throws UserError
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_theme( $stylesheet ) {
		$theme = wp_get_theme( $stylesheet );
		if ( $theme->exists() ) {
			return $theme;
		} else {
			throw new UserError( sprintf( __( 'No theme was found with the stylesheet: %s', 'wp-graphql' ), $stylesheet ) );
		}
	}

	/**
	 * Wrapper for the ThemesConnectionResolver::resolve method
	 *
	 * @param             $source
	 * @param array       $args    Passes an array of arguments to the resolve method
	 * @param AppContext  $context The AppContext object to be passed down
	 * @param ResolveInfo $info    The ResolveInfo object
	 *
	 * @return array
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_themes_connection( $source, array $args, $context, ResolveInfo $info ) {
		return ThemeConnectionResolver::resolve( $source, $args, $context, $info );
	}

	/**
	 * Gets the user object for the user ID specified
	 *
	 * @param int $id ID of the user you want the object for
	 *
	 * @return Deferred
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_user( $id ) {

		Loader::addOne( 'user', $id );
		$loader = function() use ( $id ) {
			Loader::loadBuffered( 'user' );
			return Loader::loadOne( 'user', $id );
		};
		return new Deferred( $loader );
	}

	/**
	 * Wrapper for the UsersConnectionResolver::resolve method
	 *
	 * @param             $source
	 * @param array       $args    Array of args to be passed down to the resolve method
	 * @param AppContext  $context The AppContext object to be passed down
	 * @param ResolveInfo $info    The ResolveInfo object
	 *
	 * @return array
	 * @since  0.0.5
	 * @access public
	 */
	public static function resolve_users_connection( $source, array $args, $context, ResolveInfo $info ) {
		return UserConnectionResolver::resolve( $source, $args, $context, $info );
	}

	/**
	 * Get all of the allowed settings by group and return the
	 * settings group that matches the group param
	 *
	 * @access public
	 * @param string $group
	 *
	 * @return array $settings_groups[ $group ]
	 */
	public static function get_setting_group_fields( $group ) {

		/**
		 * Get all of the settings, sorted by group
		 */
		$settings_groups = self::get_allowed_settings_by_group();

		return ! empty( $settings_groups[ $group ] ) ? $settings_groups[ $group ] : [];

	}

	/**
	 * Get all of the allowed settings by group
	 *
	 * @access public
	 * @return array $allowed_settings_by_group
	 */
	public static function get_allowed_settings_by_group() {

		/**
		 * Get all registered settings
		 */
		$registered_settings = get_registered_settings();

		/**
		 * Loop through the $registered_settings array and build an array of
		 * settings for each group ( general, reading, discussion, writing, reading, etc. )
		 * if the setting is allowed in REST or GraphQL
		 */
		foreach ( $registered_settings as $key => $setting ) {
			if ( ! isset( $setting['show_in_graphql'] ) ) {
				if ( isset( $setting['show_in_rest'] ) && false !== $setting['show_in_rest'] ) {
					$setting['key'] = $key;
					$allowed_settings_by_group[ $setting['group'] ][ $key ] = $setting;
				}
			} else if ( true === $setting['show_in_graphql'] ) {
				$setting['key'] = $key;
				$allowed_settings_by_group[ $setting['group'] ][ $key ] = $setting;
			}
		};

		/**
		 * Set the setting groups that are allowed
		 */
		$allowed_settings_by_group = ! empty( $allowed_settings_by_group ) && is_array( $allowed_settings_by_group ) ? $allowed_settings_by_group : [];

		/**
		 * Filter the $allowed_settings_by_group to allow enabling or disabling groups in the GraphQL Schema.
		 *
		 * @param array $allowed_settings_by_group
		 */
		$allowed_settings_by_group = apply_filters( 'graphql_allowed_settings_by_group', $allowed_settings_by_group );

		return $allowed_settings_by_group;

	}

	/**
	 * Get all of the $allowed_settings
	 *
	 * @access public
	 * @return array $allowed_settings
	 */
	public static function get_allowed_settings() {

		/**
		 * Get all registered settings
		 */
		$registered_settings = get_registered_settings();

		/**
		 * Loop through the $registered_settings and if the setting is allowed in REST or GraphQL
		 * add it to the $allowed_settings array
		 */
		foreach ( $registered_settings as $key => $setting ) {
			if ( ! isset( $setting['show_in_graphql'] ) ) {
				if ( isset( $setting['show_in_rest'] ) && false !== $setting['show_in_rest'] ) {
					$setting['key'] = $key;
					$allowed_settings[ $key ] = $setting;
				}
			} else if ( true === $setting['show_in_graphql'] ) {
				$setting['key'] = $key;
				$allowed_settings[ $key ] = $setting;
			}
		};

		/**
		 * Verify that we have the allowed settings
		 */
		$allowed_settings = ! empty( $allowed_settings ) && is_array( $allowed_settings ) ? $allowed_settings : [];

		/**
		 * Filter the $allowed_settings to allow some to be enabled or disabled from showing in
		 * the GraphQL Schema.
		 *
		 * @param array $allowed_settings
		 *
		 * @return array
		 */
		$allowed_settings = apply_filters( 'graphql_allowed_setting_groups', $allowed_settings );

		return $allowed_settings;

	}

	/**
	 * We get the node interface and field from the relay library.
	 *
	 * The first method is the way we resolve an ID to its object. The second is the way we resolve
	 * an object that implements node to its type.
	 *
	 * @return array
	 * @throws UserError
	 * @access public
	 */
	public static function get_node_definition() {

		if ( null === self::$node_definition ) {

			$node_definition = Relay::nodeDefinitions(

			// The ID fetcher definition
				function( $global_id ) {

					if ( empty( $global_id ) ) {
						throw new UserError( __( 'An ID needs to be provided to resolve a node.', 'wp-graphql' ) );
					}

					/**
					 * Convert the encoded ID into an array we can work with
					 *
					 * @since 0.0.4
					 */
					$id_components = Relay::fromGlobalId( $global_id );

					/**
					 * If the $id_components is a proper array with a type and id
					 *
					 * @since 0.0.5
					 */
					if ( is_array( $id_components ) && ! empty( $id_components['id'] ) && ! empty( $id_components['type'] ) ) {

						/**
						 * Get the allowed_post_types and allowed_taxonomies
						 *
						 * @since 0.0.5
						 */
						$allowed_post_types = \WPGraphQL::get_allowed_post_types();
						$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies();

						switch ( $id_components['type'] ) {
							case in_array( $id_components['type'], $allowed_post_types, true ):
								$node = self::resolve_post_object( $id_components['id'], $id_components['type'] );
								break;
							case in_array( $id_components['type'], $allowed_taxonomies, true ):
								$node = self::resolve_term_object( $id_components['id'], $id_components['type'] );
								break;
							case 'comment':
								$node = self::resolve_comment( $id_components['id'] );
								break;
							case 'commentAuthor':
								$node = self::resolve_comment_author( $id_components['id'] );
								break;
							case 'plugin':
								$node = self::resolve_plugin( $id_components['id'] );
								break;
							case 'postType':
								$node = self::resolve_post_type( $id_components['id'] );
								break;
							case 'taxonomy':
								$node = self::resolve_taxonomy( $id_components['id'] );
								break;
							case 'theme':
								$node = self::resolve_theme( $id_components['id'] );
								break;
							case 'user':
								$node = self::resolve_user( $id_components['id'] );
								break;
							default:
								/**
								 * Add a filter to allow externally registered node types to resolve based on
								 * the id_components
								 *
								 * @param int    $id   The id of the node, from the global ID
								 * @param string $type The type of node to resolve, from the global ID
								 *
								 * @since 0.0.6
								 */
								$node = apply_filters( 'graphql_resolve_node', null, $id_components['id'], $id_components['type'] );
								break;

						}

						/**
						 * If the $node is not properly resolved, throw an exception
						 *
						 * @since 0.0.6
						 */
						if ( null === $node ) {
							throw new UserError( sprintf( __( 'No node could be found with global ID: %s', 'wp-graphql' ), $global_id ) );
						}

						/**
						 * Return the resolved $node
						 *
						 * @since 0.0.5
						 */
						return $node;

					} else {
						throw new UserError( sprintf( __( 'The global ID isn\'t recognized ID: %s', 'wp-graphql' ), $global_id ) );
					}
				},

				// Type resolver
				function( $node ) {

					if ( true === is_object( $node ) ) {

						switch ( true ) {
							case $node instanceof \WP_Post:
								$type = Types::post_object( $node->post_type );
								break;
							case $node instanceof \WP_Term:
								$type = Types::term_object( $node->taxonomy );
								break;
							case $node instanceof \WP_Comment:
								$type = Types::comment();
								break;
							case $node instanceof \WP_Post_Type:
								$type = Types::post_type();
								break;
							case $node instanceof \WP_Taxonomy:
								$type = Types::taxonomy();
								break;
							case $node instanceof \WP_Theme:
								$type = Types::theme();
								break;
							case $node instanceof \WP_User:
								$type = Types::user();
								break;
							default:
								$type = null;
						}

						// Some nodes might return an array instead of an object
					} elseif ( is_array( $node )  ) {

						switch ( $node ) {
							case array_key_exists( 'PluginURI', $node ):
								$type = Types::plugin();
								break;
							case array_key_exists( 'is_comment_author', $node ):
								$type = Types::comment_author();
								break;
							default:
								$type = null;
						}
					}

					/**
					 * Add a filter to allow externally registered node types to return the proper type
					 * based on the node_object that's returned
					 *
					 * @param mixed|object|array $type The type definition the node should resolve to.
					 * @param mixed|object|array $node The $node that is being resolved
					 *
					 * @since 0.0.6
					 */
					$type = apply_filters( 'graphql_resolve_node_type', $type, $node );

					/**
					 * If the $type is not properly resolved, throw an exception
					 *
					 * @since 0.0.6
					 */
					if ( null === $type ) {
						throw new UserError( __( 'No type was found matching the node', 'wp-graphql' ) );
					}

					/**
					 * Return the resolved $type for the $node
					 *
					 * @since 0.0.5
					 */
					return $type;

				}
			);

			self::$node_definition = $node_definition;

		}

		return self::$node_definition;
	}

	/**
	 * Cached version of get_page_by_path so that we're not making unnecessary SQL all the time
	 *
	 * This is a modified version of the cached function from WordPress.com VIP MU Plugins here.
	 *
	 * @param string $uri
	 * @param string $output Optional. Output type; OBJECT*, ARRAY_N, or ARRAY_A.
	 * @param string $post_type Optional. Post type; default is 'post'.
	 * @return WP_Post|null WP_Post on success or null on failure
	 * @see https://github.com/Automattic/vip-go-mu-plugins/blob/52549ae9a392fc1343b7ac9dba4ebcdca46e7d55/vip-helpers/vip-caching.php#L186
	 * @link http://vip.wordpress.com/documentation/uncached-functions/ Uncached Functions
	 */
	public static function get_post_object_by_uri( $uri, $output = OBJECT, $post_type = 'post' ) {

		if ( is_array( $post_type ) ) {
			$cache_key = sanitize_key( $uri ) . '_' . md5( serialize( $post_type ) );
		} else {
			$cache_key = $post_type . '_' . sanitize_key( $uri );
		}
		$post_id = wp_cache_get( $cache_key, 'get_post_object_by_path' );

		if ( false === $post_id ) {
			$post = get_page_by_path( $uri, $output, $post_type );
			$post_id = $post ? $post->ID : 0;
			if ( 0 === $post_id ) {
				wp_cache_set( $cache_key, $post_id, 'get_post_object_by_path', ( 1 * HOUR_IN_SECONDS + mt_rand( 0, HOUR_IN_SECONDS ) ) ); // We only store the ID to keep our footprint small
			} else {
				wp_cache_set( $cache_key, $post_id, 'get_post_object_by_path', 0 ); // We only store the ID to keep our footprint small
			}
		}
		if ( $post_id ) {
			return get_post( $post_id, $output );
		}
		return null;

	}
}
