<?php

namespace WPGraphQL\Type\TermObject\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Connection\ArrayConnection;
use WPGraphQL\AppContext;
use WPGraphQL\Data\ConnectionResolver;
use WPGraphQL\Types;

/**
 * Class TermObjectConnectionResolver
 *
 * @package WPGraphQL\Data\Resolvers
 * @since   0.0.5
 */
class TermObjectConnectionResolver extends ConnectionResolver {

	/**
	 * Stores the name of the taxonomy for the connection being resolved
	 *
	 * @var string $taxonomy
	 */
	public static $taxonomy;

	/**
	 * TermObjectConnectionResolver constructor.
	 *
	 * @param $taxonomy
	 */
	public function __construct( $taxonomy ) {
		self::$taxonomy = $taxonomy;
	}

	/**
	 * Returns an array of query_args to use in the WP_Term_Query to fetch the necessary terms for the connection
	 *
	 * @param             $source
	 * @param array       $args
	 * @param AppContext  $context
	 * @param ResolveInfo $info
	 *
	 * @return array
	 */
	public static function get_query_args( $source, array $args, AppContext $context, ResolveInfo $info ) {

		/**
		 * Set the taxonomy for the $args
		 */
		$query_args['taxonomy'] = ! empty( self::$taxonomy ) ? self::$taxonomy : 'category';

		/**
		 * Prepare for later use
		 */
		$last  = ! empty( $args['last'] ) ? $args['last'] : null;
		$first = ! empty( $args['first'] ) ? $args['first'] : null;

		/**
		 * Set the default parent for TermObject Queries to be "0" to only get top level terms, unless
		 * includeChildren is set
		 */
		$query_args['parent'] = 0;

		/**
		 * Set hide_empty as false by default
		 */
		$query_args['hide_empty'] = false;

		/**
		 * Set the number, ensuring it doesn't exceed the amount set as the $max_query_amount
		 */
		$query_args['number'] = min( max( absint( $first ), absint( $last ), 10 ), self::get_query_amount( $source, $args, $context, $info ) ) + 1;

		/**
		 * Orderby Name by default
		 */
		$query_args['orderby'] = 'name';

		/**
		 * Take any of the $args that were part of the GraphQL query and map their
		 * GraphQL names to the WP_Term_Query names to be used in the WP_Term_Query
		 *
		 * @since 0.0.5
		 */
		$input_fields = [];
		if ( ! empty( $args['where'] ) ) {
			$input_fields = self::sanitize_input_fields( $args['where'], $source, $args, $context, $info );
		}

		/**
		 * Merge the default $query_args with the $args that were entered
		 * in the query.
		 *
		 * @since 0.0.5
		 */
		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		/**
		 * If there's no orderby params in the inputArgs, set order based on the first/last argument
		 */
		if ( empty( $query_args['order'] ) ) {
			$query_args['order'] = ! empty( $last ) ? 'DESC' : 'ASC';
		}

		/**
		 * Set the graphql_cursor_offset
		 */
		$query_args['graphql_cursor_offset']  = self::get_offset( $args );
		$query_args['graphql_cursor_compare'] = ( ! empty( $last ) ) ? '>' : '<';

		/**
		 * Pass the graphql $args to the WP_Query
		 */
		$query_args['graphql_args'] = $args;

		/**
		 * If the source of the Query is a Post object, adjust the query args to only query terms
		 * connected to the post object
		 *
		 * @since 0.0.5
		 */
		global $post;
		if ( true === is_object( $source ) ) {
			switch ( true ) {
				case $source instanceof \WP_Post:
					$post = $source;
					$post->shouldOnlyIncludeConnectedItems = isset( $input_fields['shouldOnlyIncludeConnectedItems'] ) ? $input_fields['shouldOnlyIncludeConnectedItems'] : true;
					$query_args['object_ids'] = $source->ID;
					break;
				case $source instanceof \WP_Term:
					$query_args['object_ids'] = $GLOBALS['post']->ID;
					$query_args['parent'] = ! empty( $source->term_id ) ? $source->term_id : 0;
					break;
				default:
					break;
			}
		}

		/**
		 * IF the connection is set to NOT ONLY include connected items (default behavior), unset the $object_ids arg
		 */
		if ( isset( $post->shouldOnlyIncludeConnectedItems ) && false === $post->shouldOnlyIncludeConnectedItems ) {
			unset( $query_args['object_ids'] );
		}

		/**
		 * If the connection is set to output in a flat list, unset the parent
		 */
		if ( isset( $input_fields['shouldOutputInFlatList'] ) && true === $input_fields['shouldOutputInFlatList'] ){
			unset( $query_args['parent'] );
			$connected = wp_get_object_terms( $source->ID, self::$taxonomy, ['fields' => 'ids'] );
			$query_args['include'] = ! empty( $connected ) ? $connected : [];
		}

		/**
		 * Filter the query_args that should be applied to the query. This filter is applied AFTER the input args from
		 * the GraphQL Query have been applied and has the potential to override the GraphQL Query Input Args.
		 *
		 * @param array       $query_args array of query_args being passed to the
		 * @param mixed       $source     source passed down from the resolve tree
		 * @param array       $args       array of arguments input in the field as part of the GraphQL query
		 * @param AppContext  $context    object passed down the resolve tree
		 * @param ResolveInfo $info       info about fields passed down the resolve tree
		 *
		 * @since 0.0.6
		 */
		$query_args = apply_filters( 'graphql_term_object_connection_query_args', $query_args, $source, $args, $context, $info );

		return $query_args;

	}

	/**
	 * This runs the query and returns the response
	 *
	 * @param $query_args
	 *
	 * @return \WP_Term_Query
	 */
	public static function get_query( $query_args ) {
		$query = new \WP_Term_Query( $query_args );
		return $query;
	}

	/**
	 * This gets the connection to return
	 *
	 * @param array|mixed $query   The query that was processed to get data
	 * @param array       $items   The array slice that was returned
	 * @param mixed       $source  The source being passed down the resolve tress
	 * @param array       $args    The input args for the resolving field
	 * @param AppContext  $context The context being passed down the resolve tree
	 * @param ResolveInfo $info    The ResolveInfo passed down the resolve tree
	 *
	 * @return array
	 */
	public static function get_connection( $query, array $items, $source, array $args, AppContext $context, ResolveInfo $info ) {

		/**
		 * Get the $posts from the query
		 */
		$items = ! empty( $items ) && is_array( $items ) ? $items : [];

		/**
		 * Set whether there is or is not another page
		 */
		$has_previous_page = ( ! empty( $args['last'] ) && count( $items ) > self::get_amount_requested( $args ) ) ? true : false;
		$has_next_page     = ( ! empty( $args['first'] ) && count( $items ) > self::get_amount_requested( $args ) ) ? true : false;

		/**
		 * Slice the array to the amount of items that were requested
		 */
		$items = array_slice( $items, 0, self::get_amount_requested( $args ) );

		/**
		 * Get the edges from the $items
		 */
		$edges = self::get_edges( $items, $source, $args, $context, $info );

		/**
		 * Find the first_edge and last_edge
		 */
		$first_edge = $edges ? $edges[0] : null;
		$last_edge  = $edges ? $edges[ count( $edges ) - 1 ] : null;

		/**
		 * Create the connection to return
		 */
		$connection = [
			'edges'    => $edges,
			'debug'    => [
				'queryRequest' => ! empty( $query->request ) ? $query->request : null,
			],
			'pageInfo' => [
				'hasPreviousPage' => $has_previous_page,
				'hasNextPage'     => $has_next_page,
				'startCursor'     => ! empty( $first_edge['cursor'] ) ? $first_edge['cursor'] : null,
				'endCursor'       => ! empty( $last_edge['cursor'] ) ? $last_edge['cursor'] : null,
			],
			'nodes' => $items,
		];

		return $connection;

	}

	/**
	 * Takes an array of items and returns the edges
	 *
	 * @param $items
	 *
	 * @return array
	 */
	public static function get_edges( $items, $source, $args, $context, $info ) {
		$edges = [];

		/**
		 * If we're doing backward pagination we want to reverse the array before
		 * returning it to the edges
		 */
		if ( ! empty( $args['last'] ) ) {
			$items = array_reverse( $items );
		}

		if ( ! empty( $items ) && is_array( $items ) ) {
			foreach ( $items as $item ) {
				$edges[] = [
					'cursor' => ArrayConnection::offsetToCursor( $item->term_id ),
					'node'   => $item,
				];
			}
		}

		return $edges;
	}

	/**
	 * This maps the GraphQL "friendly" args to get_terms $args.
	 * There's probably a cleaner/more dynamic way to approach this, but this was quick. I'd be down
	 * to explore more dynamic ways to map this, but for now this gets the job done.
	 *
	 * @param array       $args     Array of query "where" args
	 * @param mixed       $source   The query results
	 * @param array       $all_args All of the query arguments (not just the "where" args)
	 * @param AppContext  $context  The AppContext object
	 * @param ResolveInfo $info     The ResolveInfo object
	 *
	 * @since  0.0.5
	 * @return array
	 * @access public
	 */
	public static function sanitize_input_fields( array $args, $source, array $all_args, AppContext $context, ResolveInfo $info ) {

		$arg_mapping = [
			'objectIds'           => 'object_ids',
			'hideEmpty'           => 'hide_empty',
			'excludeTree'         => 'exclude_tree',
			'termTaxonomId'       => 'term_taxonomy_id',
			'nameLike'            => 'name__like',
			'descriptionLike'     => 'description__like',
			'padCounts'           => 'pad_counts',
			'childOf'             => 'child_of',
			'cacheDomain'         => 'cache_domain',
			'updateTermMetaCache' => 'update_term_meta_cache',
		];

		/**
		 * Map and sanitize the input args to the WP_Term_Query compatible args
		 */
		$query_args = Types::map_input( $args, $arg_mapping );

		/**
		 * Filter the input fields
		 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
		 * from a GraphQL Query to the get_terms query
		 *
		 * @param array       $query_args Array of mapped query args
		 * @param array       $args       Array of query "where" args
		 * @param string      $taxonomy   The name of the taxonomy
		 * @param mixed       $source     The query results
		 * @param array       $all_args   All of the query arguments (not just the "where" args)
		 * @param AppContext  $context    The AppContext object
		 * @param ResolveInfo $info       The ResolveInfo object
		 *
		 * @since 0.0.5
		 * @return array
		 */
		$query_args = apply_filters( 'graphql_map_input_fields_to_get_terms', $query_args, $args, self::$taxonomy, $source, $all_args, $context, $info );

		return ! empty( $query_args ) && is_array( $query_args ) ? $query_args : [];

	}

}
