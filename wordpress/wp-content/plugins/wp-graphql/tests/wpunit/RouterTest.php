<?php

class RouterTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	public function testRouteEndpoint() {
		/**
		 * Test that the default route is set to "graphql"
		 */
		$this->assertEquals( 'graphql', apply_filters( 'graphql_endpoint', \WPGraphQL\Router::$route ) );
	}

	/**
	 * Test to make sure that the rewrite rules properly include the graphql route
	 */
	public function testGraphQLRewriteRule() {
		global $wp_rewrite;
		$route = apply_filters( 'graphql_endpoint', \WPGraphQL\Router::$route );
		$this->assertArrayHasKey( $route . '/?$', $wp_rewrite->extra_rules_top );
	}

	public function testAddQueryVar() {
		$query_vars = [];
		$actual     = \WPGraphQL\Router::add_query_var( $query_vars );
		$this->assertEquals( $actual, [ apply_filters( 'graphql_endpoint', \WPGraphQL\Router::$route ) ] );
	}

	public function testGetRawData() {
		$router = new \WPGraphQL\Router();
		global $HTTP_RAW_POST_DATA;
		$actual = $router->get_raw_data();
		$this->assertEquals( $actual, $HTTP_RAW_POST_DATA );
	}

	public function testGetRawDataEmptyGlobal() {
		$router = new \WPGraphQL\Router();
		global $HTTP_RAW_POST_DATA;
		$HTTP_RAW_POST_DATA = null;
		$actual             = $router->get_raw_data();
		$this->assertEquals( $actual, $HTTP_RAW_POST_DATA );
	}

	/**
	 * Test the "send_header" method in the Router class
	 *
	 * @see: https://github.com/sebastianbergmann/phpunit/issues/720
	 * @runInSeparateProcess
	 */
	//	public function testSendHeader() {
	//		$router = new \WPGraphQL\Router();
	//		$router::send_header( 'some_key', 'some_value' );
	//		if ( function_exists( 'xdebug_get_headers' ) ) {
	//			$this->assertContains( 'some_key: some_value', xdebug_get_headers() );
	//		}
	//	}
	//
	//	public function testAddRewriteRule() {
	//
	//		global $wp_rewrite;
	//		\WPGraphQL\Router::add_rewrite_rule();
	//		flush_rewrite_rules();
	//
	//		$this->assertContains( 'index.php?' . \WPGraphQL\Router::$route . '=true', $wp_rewrite->extra_rules_top );
	//
	//	}

	/**
	 * @runInSeparateProcess
	 */
	//	public function testSetHeadersNoCache() {
	//
	//		$router = new \WPGraphQL\Router();
	//		$router::set_headers( '200' );
	//
	//		$headers = xdebug_get_headers();
	//
	//		$this->assertContains( 'Access-Control-Allow-Origin: *', $headers );
	//		$this->assertContains( 'Content-Type: application/json ; charset=' . get_option( 'blog_charset' ), $headers );
	//		$this->assertContains( 'X-Robots-Tag: noindex', $headers );
	//		$this->assertContains( 'X-Content-Type-Options: nosniff', $headers );
	//		$this->assertContains( 'Access-Control-Allow-Headers: Authorization, Content-Type', $headers );
	//		$this->assertContains( 'X-hacker: If you\'re reading this, you should visit github.com/wp-graphql and contribute!', $headers );
	//
	//	}

	/**
	 * @runInSeparateProcess
	 */
	//	public function testSetHeadersWithCache() {
	//
	//		add_filter( 'graphql_send_nocache_headers', function() {
	//			return true;
	//		} );
	//
	//		$router = new \WPGraphQL\Router();
	//		$router::set_headers( '200' );
	//		$headers = xdebug_get_headers();
	//		$this->assertContains( 'Cache-Control: no-cache, must-revalidate, max-age=0', $headers );
	//
	//	}

	/**
	 * This tests the WPGraphQL Router resolving HTTP requests.
	 */
//	public function testResolveRequest() {
//
//		/**
//		 * Create a test a query
//		 */
//		$this->factory->post->create( [
//			'post_title'  => 'test',
//			'post_status' => 'publish',
//		] );
//
//		/**
//		 * Filter the request data
//		 */
//		add_filter( 'graphql_request_data', function( $data ) {
//			$data['query']         = 'query getPosts($first:Int){ posts(first:$first){ edges{ node{ id } } } }';
//			$data['variables']     = [ 'first' => 1 ];
//			$data['operationName'] = 'getPosts';
//
//			return $data;
//		} );
//
//		/**
//		 * Set the query var to "graphql" so we can mock like we're visiting the endpoint via
//		 */
//		set_query_var( 'graphql', true );
//		$GLOBALS['wp']->query_vars['graphql'] = true;
//
//		/**
//		 * Instantiate the router
//		 */
//		$router = new \WPGraphQL\Router();
//
//		/**
//		 * Process the request using our filtered data
//		 */
//		$router::resolve_http_request();
//
//		/**
//		 * Make sure the constant gets defined when it's a GraphQL Request
//		 */
//		$this->assertTrue( defined( 'GRAPHQL_HTTP_REQUEST' ) );
//		$this->assertEquals( true, GRAPHQL_HTTP_REQUEST );
//
//		/**
//		 * Make sure the actions we expect to be firing are firing
//		 */
//		$this->assertNotFalse( did_action( 'graphql_process_http_request' ) );
//		$this->assertNotFalse( did_action( 'graphql_process_http_request_response' ) );
//
//	}
//
//	public function testResolveHttpRequestWithJsonVariables() {
//
//		/**
//		 * Create a test a query
//		 */
//		$this->factory->post->create( [
//			'post_title'  => 'test',
//			'post_status' => 'publish',
//		] );
//
//		/**
//		 * Filter the request data
//		 */
//		add_filter( 'graphql_request_data', function( $data ) {
//			$data['query']         = 'query getPosts($first:Int){ posts(first:$first){ edges{ node{ id } } } }';
//			$data['variables']     = wp_json_encode( [ 'first' => 1 ] );
//			$data['operationName'] = 'getPosts';
//
//			return $data;
//		} );
//
//		/**
//		 * Set the query var to "graphql" so we can mock like we're visiting the endpoint via
//		 */
//		set_query_var( 'graphql', true );
//		$GLOBALS['wp']->query_vars['graphql'] = true;
//
//		/**
//		 * Instantiate the router
//		 */
//		$router = new \WPGraphQL\Router();
//
//		/**
//		 * Process the request using our filtered data
//		 */
//		$router::resolve_http_request();
//
//		/**
//		 * Make sure the constant gets defined when it's a GraphQL Request
//		 */
//		$this->assertTrue( defined( 'GRAPHQL_HTTP_REQUEST' ) );
//		$this->assertEquals( true, GRAPHQL_HTTP_REQUEST );
//
//		/**
//		 * Make sure the actions we expect to be firing are firing
//		 */
//		$this->assertNotFalse( did_action( 'graphql_process_http_request' ) );
//		$this->assertNotFalse( did_action( 'graphql_process_http_request_response' ) );
//
//	}
//
//	/**
//	 * This tests the resolve_http_request method for a route that's not the
//	 * /graphql endpoint to make sure that graphql isn't improperly initiated
//	 * when it's not supposed to be.
//	 */
//	public function testResolveHttpRequestWrongQueryVars() {
//
//		set_query_var( 'graphql', false );
//		$GLOBALS['wp']->query_vars['graphql'] = false;
//
//		/**
//		 * Instantiate the router
//		 */
//		$router = new \WPGraphQL\Router();
//
//		/**
//		 * Process the request using our filtered data
//		 */
//		$this->assertNull( $router::resolve_http_request() );
//
//	}
//
//	public function testResolveHttpRequestWithEmptyQuery() {
//
//		/**
//		 * Filter the request data
//		 */
//		add_filter( 'graphql_request_data', function( $data ) {
//			$data['query']         = null;
//			$data['variables']     = null;
//			$data['operationName'] = null;
//
//			return $data;
//		} );
//
//		/**
//		 * Set the query var to "graphql" so we can mock like we're visiting the endpoint via
//		 */
//		set_query_var( 'graphql', true );
//		$GLOBALS['wp']->query_vars['graphql'] = true;
//
//		/**
//		 * Instantiate the router
//		 */
//		$router = new \WPGraphQL\Router();
//
//		/**
//		 * Process the request using our filtered data
//		 */
//		$router::resolve_http_request();
//
//		/**
//		 * Make sure the constant gets defined when it's a GraphQL Request
//		 */
//		$this->assertTrue( defined( 'GRAPHQL_HTTP_REQUEST' ) );
//		$this->assertEquals( true, GRAPHQL_HTTP_REQUEST );
//
//		/**
//		 * Make sure the actions we expect to be firing are firing
//		 */
//		$this->assertNotFalse( did_action( 'graphql_process_http_request' ) );
//		$this->assertNotFalse( did_action( 'graphql_process_http_request_response' ) );
//
//	}

}