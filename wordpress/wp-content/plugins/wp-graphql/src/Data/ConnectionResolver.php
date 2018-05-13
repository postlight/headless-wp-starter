<?php
namespace WPGraphQL\Data;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Connection\ArrayConnection;
use WPGraphQL\AppContext;

/**
 * Class Connections
 *
 * This class is meant to be extended by ConnectionResolvers
 *
 * @package WPGraphQL\Data
 */
abstract class ConnectionResolver implements ConnectionResolverInterface {

	/**
	 * Runs the query for comments
	 *
	 * @param mixed       $source  Data returned from the query
	 * @param array       $args    Args for the query
	 * @param AppContext  $context AppContext object for the query
	 * @param ResolveInfo $info    ResolveInfo object
	 *
	 * @return array
	 * @since  0.5.0
	 * @throws \Exception
	 * @access public
	 */
	public static function resolve( $source, $args, AppContext $context, ResolveInfo $info ) {

		$query_args  = static::get_query_args( $source, $args, $context, $info );
		$query       = static::get_query( $query_args );
		$array_slice = self::get_array_slice( $query, $args );
		$connection  = static::get_connection( $query, $array_slice, $source, $args, $context, $info );
		/**
		 * Filter the connection, and provide heaps of info to make it easy to filter very specific cases
		 *
		 * @param array $connection  The connection to return
		 * @param array $array_slice The Array to create the connection from
		 * @param mixed $query       The query that was run to retrieve the items
		 * @param array $query_args  The args that were used by the query
		 * @param mixed $source      The source being passed down the GraphQL tree
		 * @param array $args        The args that were input for the specific GraphQL query
		 * @param       AppContext   object $context The AppContext that gets passed down the GraphQL tree
		 * @param       ResolveInfo  object $info The ResolveInfo object that gets passed down the GraphQL tree
		 *
		 */
		$connection = apply_filters(
			'graphql_connection_resolver_resolve',
			$connection,
			$array_slice,
			$query,
			$query_args,
			$source,
			$args,
			$context,
			$info
		);

		return $connection;

	}

	/**
	 * Take an array return a connection
	 *
	 * @param mixed $query The query being performed
	 * @param array $array The array of connection items
	 * @param mixed $source The source being passed down the resolve tree
	 * @param array $args The args for the field being resolved
	 * @param AppContext $context The context being passed down the Resolve tree
	 * @param ResolveInfo $info The ResolveInfo for the field being resolved
	 *
	 * @return array
	 */
	public static function get_connection( $query, array $array, $source, array $args, AppContext $context, ResolveInfo $info ) {

		$meta       = self::get_array_meta( $query, $args );
		$connection = ArrayConnection::connectionFromArraySlice( $array, $args, $meta );
		$connection['nodes'] = $array;

		return $connection;

	}

	/**
	 * This returns a slice of the query results based on the posts retrieved and the $args passed to the query
	 *
	 * @param mixed $query The query that was made to fetch the items
	 * @param array $args  array of arguments input in the field as part of the GraphQL query
	 *
	 * @return array
	 */
	public static function get_array_slice( $query, array $args ) {

		$info        = self::get_query_info( $query );
		$items       = $info['items'];
		$array_slice = [];
		if ( ! empty( $items ) && is_array( $items ) ) {
			foreach ( $items as $item ) {
				if ( true === is_object( $item ) ) {
					switch ( true ) {
						case $item instanceof \WP_Comment:
							$array_slice[ $item->comment_ID ] = $item;
							break;
						case $item instanceof \WP_Term:
							$array_slice[ $item->term_id ] = $item;
							break;
						case $item instanceof \WP_Post:
							$array_slice[ $item->ID ] = $item;
							break;
							// the \WP_User_Query doesn't have proper filters to allow for true cursor based pagination
						case $item instanceof \WP_User:
							$array_slice[] = $item;
							break;
						default:
							$array_slice = $items;
					}
				}
			}
		}
		return $array_slice;
	}

	/**
	 * This checks the $args to determine the amount requested, and if
	 *
	 * @param array $args array of arguments input in the field as part of the GraphQL query
	 *
	 * @return int|null
	 * @throws \Exception
	 */
	public static function get_amount_requested( array $args ) {

		/**
		 * Set the default amount
		 */
		$amount_requested = 10;

		/**
		 * If both first & last are used in the input args, throw an exception as that won't
		 * work properly
		 */
		if ( ! empty( $args['first'] ) && ! empty( $args['last'] ) ) {
			throw new UserError( esc_html__( 'first and last cannot be used together. For forward pagination, use first & after. For backward pagination, use last & before.', 'wp-graphql' ) );
		}

		/**
		 * If first is set, and is a positive integer, use it for the $amount_requested
		 * but if it's set to anything that isn't a positive integer, throw an exception
		 */
		if ( ! empty( $args['first'] ) && is_int( $args['first'] ) ) {
			if ( 0 > $args['first'] ) {
				throw new UserError( esc_html__( 'first must be a positive integer.', 'wp-graphql' ) );
			} else {
				$amount_requested = $args['first'];
			}
		}

		/**
		 * If last is set, and is a positive integer, use it for the $amount_requested
		 * but if it's set to anything that isn't a positive integer, throw an exception
		 */
		if ( ! empty( $args['last'] ) && is_int( $args['last'] ) ) {
			if ( 0 > $args['last'] ) {
				throw new UserError( esc_html__( 'last must be a positive integer.', 'wp-graphql' ) );
			} else {
				$amount_requested = $args['last'];
			}
		}

		return max( 0, $amount_requested );

	}

	/**
	 * get_query_amount
	 *
	 * Returns the max between what was requested and what is defined as the $max_query_amount to ensure that
	 * queries don't exceed unwanted limits when querying data.
	 *
	 * @param $source
	 * @param $args
	 * @param $context
	 * @param $info
	 *
	 * @return mixed
	 */
	public static function get_query_amount( $source, $args, $context, $info ) {

		/**
		 * Filter the maximum number of posts per page that should be quried. The default is 100 to prevent queries from
		 * being exceedingly resource intensive, however individual systems can override this for their specific needs.
		 *
		 * This filter is intentionally applied AFTER the query_args filter, as
		 *
		 * @param array       $query_args array of query_args being passed to the
		 * @param mixed       $source     source passed down from the resolve tree
		 * @param array       $args       array of arguments input in the field as part of the GraphQL query
		 * @param AppContext  $context    Object containing app context that gets passed down the resolve tree
		 * @param ResolveInfo $info       Info about fields passed down the resolve tree
		 *
		 * @since 0.0.6
		 */
		$max_query_amount = apply_filters( 'graphql_connection_max_query_amount', 100, $source, $args, $context, $info );

		return min( $max_query_amount, absint( self::get_amount_requested( $args ) ) );

	}

	/**
	 * This returns a meta array to be used in preparing the connection edges
	 *
	 * @param mixed $query The query that was made to fetch the items
	 * @param array $args  array of arguments input in the field as part of the GraphQL query
	 *
	 * @return array
	 */
	public static function get_array_meta( $query, $args ) {

		$info = self::get_query_info( $query );
		$meta = [
			'sliceStart'  => max( 0, absint( self::get_offset( $args ) ) ),
			'arrayLength' => absint( max( 0, $info['total_items'], count( $info['items'] ) ) ),
		];

		return $meta;

	}

	/**
	 * This returns the offset to be used in the $query_args based on the $args passed to the GraphQL query.
	 *
	 * @param $args
	 *
	 * @return int|mixed
	 */
	public static function get_offset( $args ) {

		/**
		 * Defaults
		 */
		$offset = 0;

		/**
		 * Get the $after offset
		 */
		if ( ! empty( $args['after'] ) ) {
			$offset = ArrayConnection::cursorToOffset( $args['after'] );
		} elseif ( ! empty( $args['before'] ) ) {
			$offset = ArrayConnection::cursorToOffset( $args['before'] );
		}

		/**
		 * Return the higher of the two values
		 */
		return max( 0, $offset );

	}

	/**
	 * WordPress has different queries that return date in different shapes. This normalizes the return
	 * for re-use.
	 *
	 * @param mixed $query The query that was made to fetch the items
	 *
	 * @return integer
	 */
	public static function get_query_info( $query ) {

		/**
		 * Set the default values to return
		 */
		$query_info = [
			'total_items' => 0,
			'items'       => [],
		];

		if ( true === is_object( $query ) ) {
			switch ( true ) {
				case $query instanceof \WP_Query:
					$found_posts = $query->posts;
					$query_info['total_items'] = ! empty( $found_posts ) ? count( $found_posts ) : null;
					$query_info['items']       = $found_posts;
					break;
				case $query instanceof \WP_Comment_Query:
					$found_comments = $query->get_comments();
					$query_info['total_items'] = ! empty( $found_comments ) ? count( $found_comments ) : null;
					$query_info['items']       = ! empty( $found_comments ) ? $found_comments : [];
					break;
				case $query instanceof \WP_Term_Query:
					$query_info['total_items'] = ! empty( $query->query_vars['taxonomy'] ) ? wp_count_terms( $query->query_vars['taxonomy'][0], $query->query_vars ) : 0;
					$query_info['items']       = $query->get_terms();
					break;
				case $query instanceof \WP_User_Query:
					$query_info['total_items'] = ! empty( $query->get_total() ) ? $query->get_total() : count( $query->get_results() );
					$query_info['items']       = $query->get_results();
					break;
			}
		}

		/**
		 * Filter the items count after a query has been made
		 *
		 * @param int   $items_count The number of items matching the query
		 * @param mixed $query       The query that was made to fetch the items
		 */
		$query_info = apply_filters( 'graphql_connection_query_info', $query_info, $query );

		/**
		 * Return the $count
		 */
		return $query_info;

	}

}
