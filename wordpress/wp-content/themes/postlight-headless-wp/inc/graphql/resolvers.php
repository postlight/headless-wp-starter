<?php
/**
 * Add GraphQL resolvers
 *
 * @package  Postlight_Headless_WP
 */

if ( function_exists( 'register_graphql_field' ) ) {
	// Add header menu resolver.
	require_once 'resolvers/class-headermenutype.php';
}
