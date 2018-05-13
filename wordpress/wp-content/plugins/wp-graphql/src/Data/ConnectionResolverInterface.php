<?php
namespace WPGraphQL\Data;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class Connections
 *
 * This class provides some helper methods to make creating connections easier.
 *
 * @package WPGraphQL\Data
 */
interface ConnectionResolverInterface {

	public static function get_query( $query_args );

	/**
	 * Placeholder class that should be implemented by the extending class.
	 *
	 * This prepares the $query_args for use in the connection query. This is where default $args are set, where dynamic
	 * $args from the $source get set, and where mapping the input $args to the actual $query_args occurs.
	 *
	 * @param             $source
	 * @param array       $args
	 * @param AppContext  $context
	 * @param ResolveInfo $info
	 *
	 * @return array
	 */
	public static function get_query_args( $source, array $args, AppContext $context, ResolveInfo $info );

	/**
	 * Placeholder class that should be implemented by the extending class.
	 *
	 * This validates, sanitizes and maps the input $args and maps them to the appropriate WP Query class
	 * (WP_Query, WP_Comment_Query, etc) ensuring the args being processed by the query are "safe"
	 * to process.
	 *
	 * @param $args
	 * @param $source
	 * @param $all_args
	 * @param $context
	 * @param $info
	 *
	 * @return array
	 */
	public static function sanitize_input_fields( array $args, $source, array $all_args, AppContext $context, ResolveInfo $info );

}