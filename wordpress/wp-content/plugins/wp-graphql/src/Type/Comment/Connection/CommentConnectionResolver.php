<?php
namespace WPGraphQL\Type\Comment\Connection;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Connection\ArrayConnection;
use WPGraphQL\AppContext;
use WPGraphQL\Data\ConnectionResolver;
use WPGraphQL\Types;

/**
 * Class CommentConnectionResolver - Connects the comments to other objects
 *
 * @package WPGraphQL\Data\Resolvers
 */
class CommentConnectionResolver extends ConnectionResolver {

	/**
	 * This prepares the $query_args for use in the connection query. This is where default $args are set, where dynamic
	 * $args from the $source get set, and where mapping the input $args to the actual $query_args occurs.
	 *
	 * @param             $source
	 * @param array       $args
	 * @param AppContext  $context
	 * @param ResolveInfo $info
	 *
	 * @return mixed
	 */
	public static function get_query_args( $source, array $args, AppContext $context, ResolveInfo $info ) {

		/**
		 * Prepare for later use
		 */
		$last  = ! empty( $args['last'] ) ? $args['last'] : null;
		$first = ! empty( $args['first'] ) ? $args['first'] : null;

		/**
		 * Don't calculate the total rows, it's not needed and can be expensive
		 */
		$query_args['no_found_rows'] = true;

		/**
		 * Set the default comment_status for Comment Queries to be "approved"
		 */
		$query_args['comment_status'] = 'approved';

		/**
		 * Set the default comment_parent for Comment Queries to be "0" to only get top level comments
		 */
		$query_args['parent'] = 0;

		/**
		 * Set the number, ensuring it doesn't exceed the amount set as the $max_query_amount
		 *
		 * @since 0.0.6
		 */
		$query_args['number'] = min( max( absint( $first ), absint( $last ), 10 ), self::get_query_amount( $source, $args, $context, $info ) ) + 1;

		/**
		 * Set the default order
		 */
		$query_args['orderby'] = 'comment_date';

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
		 * Throw an exception if the query is attempted to be queried by
		 */
		if ( 'comment__in' === $query_args['orderby'] && empty( $query_args['comment__in'] ) ) {
			throw new UserError( __( 'In order to sort by comment__in, an array of IDs must be passed as the commentIn argument', 'wp-graphql' ) );
		}

		/**
		 * If there's no orderby params in the inputArgs, set order based on the first/last argument
		 */
		if ( empty( $query_args['order'] ) ) {
			$query_args['order'] = ! empty( $last ) ? 'ASC' : 'DESC';
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
		 * Handle setting dynamic $query_args based on the source (higher level query)
		 */
		if ( true === is_object( $source ) ) {
			switch ( true ) {
				case $source instanceof \WP_Post:
					$query_args['post_id'] = absint( $source->ID );
					break;
				case $source instanceof \WP_User:
					$query_args['user_id'] = absint( $source->ID );
					break;
				case $source instanceof \WP_Comment:
					$query_args['parent'] = absint( $source->comment_ID );
					break;
				default:
					break;
			}
		}

		/**
		 * Filter the query_args that should be applied to the query. This filter is applied AFTER the input args from
		 * the GraphQL Query have been applied and has the potential to override the GraphQL Query Input Args.
		 *
		 * @param array       $query_args array of query_args being passed to the
		 * @param mixed       $source     source passed down from the resolve tree
		 * @param array       $args       array of arguments input in the field as part of the GraphQL query
		 * @param AppContext  $context    object passed down zthe resolve tree
		 * @param ResolveInfo $info       info about fields passed down the resolve tree
		 *
		 * @since 0.0.6
		 */
		$query_args = apply_filters( 'graphql_comment_connection_query_args', $query_args, $source, $args, $context, $info );

		return $query_args;
	}

	/**
	 *
	 * @param $query_args
	 *
	 * @return \WP_Comment_Query
	 */
	public static function get_query( $query_args ) {
		$query = new \WP_Comment_Query;
		$query->query( $query_args );
		return $query;
	}

	/**
	 * @param array       $query The query that was processed to retrieve connection data
	 * @param array       $items The array of connected items
	 * @param mixed       $source The source being passed down the resolve tree
	 * @param array       $args The Input args for the field
	 * @param AppContext  $context The AppContext passed down the resolve tree
	 * @param ResolveInfo $info The ResolveInfo passed down the resolve tree
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
		$connection = [
			'edges'    => $edges,
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
					'cursor' => ArrayConnection::offsetToCursor( $item->comment_ID ),
					'node'   => $item,
				];
			}
		}

		return $edges;
	}

	/**
	 * This sets up the "allowed" args, and translates the GraphQL-friendly keys to
	 * WP_Comment_Query friendly keys.
	 *
	 * There's probably a cleaner/more dynamic way to approach this, but this was quick. I'd be
	 * down to explore more dynamic ways to map this, but for now this gets the job done.
	 *
	 * @param array       $args     The array of query arguments
	 * @param mixed       $source   The query results
	 * @param array       $all_args Array of all of the original arguments (not just the "where"
	 *                              args)
	 * @param AppContext  $context  The AppContext object
	 * @param ResolveInfo $info     The ResolveInfo object for the query
	 *
	 * @since  0.0.5
	 * @access private
	 * @return array
	 */
	public static function sanitize_input_fields( array $args, $source, array $all_args, AppContext $context, ResolveInfo $info ) {

		$arg_mapping = [
			'authorEmail'        => 'author_email',
			'authorIn'           => 'author__in',
			'authorNotIn'        => 'author__not_in',
			'authorUrl'          => 'author_url',
			'commentIn'          => 'comment__in',
			'commentNotIn'       => 'comment__not_in',
			'contentAuthor'      => 'post_author',
			'contentAuthorIn'    => 'post_author__in',
			'contentAuthorNotIn' => 'post_author__not_in',
			'contentId'          => 'post_id',
			'contentIdIn'        => 'post__in',
			'contentIdNotIn'     => 'post__not_in',
			'contentName'        => 'post_name',
			'contentParent'      => 'post_parent',
			'contentStatus'      => 'post_status',
			'contentType'        => 'post_type',
			'includeUnapproved'  => 'includeUnapproved',
			'parentIn'           => 'parent__in',
			'parentNotIn'        => 'parent__not_in',
			'userId'             => 'user_id',
		];

		/**
		 * Map and sanitize the input args to the WP_Comment_Query compatible args
		 */
		$query_args = Types::map_input( $args, $arg_mapping );

		/**
		 * Filter the input fields
		 *
		 * This allows plugins/themes to hook in and alter what $args should be allowed to be passed
		 * from a GraphQL Query to the get_terms query
		 *
		 * @since 0.0.5
		 */
		$query_args = apply_filters( 'graphql_map_input_fields_to_wp_comment_query', $query_args, $args, $source, $all_args, $context, $info );

		return ! empty( $query_args ) && is_array( $query_args ) ? $query_args : [];

	}
}
