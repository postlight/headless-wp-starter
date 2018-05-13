<?php

namespace WPGraphQL;

use GraphQL\Error\FormattedError;
use GraphQL\Error\UserError;

/**
 * Class Router
 * This sets up the /graphql endpoint
 *
 * @package WPGraphQL
 * @since   0.0.1
 */
class Router {

	/**
	 * Sets the route to use as the endpoint
	 *
	 * @var string $route
	 * @access public
	 */
	public static $route = 'graphql';

	/**
	 * Set the default status code to 200.
	 *
	 * @var int
	 */
	public static $http_status_code = 200;

	/**
	 * Router constructor.
	 *
	 * @since  0.0.1
	 * @access public
	 */
	public function __construct() {

		/**
		 * Pass the route through a filter in case the endpoint /graphql should need to be changed
		 *
		 * @since 0.0.1
		 * @return string
		 */
		self::$route = apply_filters( 'graphql_endpoint', 'graphql' );

		/**
		 * Create the rewrite rule for the route
		 *
		 * @since 0.0.1
		 */
		add_action( 'init', [ $this, 'add_rewrite_rule' ], 10 );

		/**
		 * Add the query var for the route
		 *
		 * @since 0.0.1
		 */
		add_filter( 'query_vars', [ $this, 'add_query_var' ], 1, 1 );

		/**
		 * Redirects the route to the graphql processor
		 *
		 * @since 0.0.1
		 */
		add_action( 'parse_request', [ $this, 'resolve_http_request' ], 10 );

	}

	/**
	 * Adds rewrite rule for the route endpoint
	 *
	 * @uses   add_rewrite_rule()
	 * @since  0.0.1
	 * @access public
	 * @return void
	 */
	public static function add_rewrite_rule() {

		add_rewrite_rule(
			self::$route . '/?$',
			'index.php?' . self::$route . '=true',
			'top'
		);

	}

	/**
	 * Adds the query_var for the route
	 *
	 * @param array $query_vars The array of whitelisted query variables
	 *
	 * @access public
	 * @since  0.0.1
	 * @return array
	 */
	public static function add_query_var( $query_vars ) {

		$query_vars[] = self::$route;

		return $query_vars;

	}

	/**
	 * This resolves the http request and ensures that WordPress can respond with the appropriate
	 * JSON response instead of responding with a template from the standard WordPress Template
	 * Loading process
	 *
	 * @since  0.0.1
	 * @access public
	 * @return void
	 */
	public static function resolve_http_request() {

		/**
		 * Access the $wp_query object
		 */
		global $wp_query;

		/**
		 * Ensure we're on the registered route for graphql route
		 */
		if ( empty( $GLOBALS['wp']->query_vars ) || ! is_array( $GLOBALS['wp']->query_vars ) || ! array_key_exists( self::$route, $GLOBALS['wp']->query_vars ) ) {
			return;
		}

		/**
		 * Set is_home to false
		 */
		$wp_query->is_home = false;

		/**
		 * Whether it's a GraphQL HTTP Request
		 *
		 * @since 0.0.5
		 */
		if ( ! defined( 'GRAPHQL_HTTP_REQUEST' ) ) {
			define( 'GRAPHQL_HTTP_REQUEST', true );
		}

		/**
		 * Process the GraphQL query Request
		 */
		self::process_http_request();

		return;

	}

	/**
	 * Sends an HTTP header.
	 *
	 * @since  0.0.5
	 * @access public
	 *
	 * @param string $key   Header key.
	 * @param string $value Header value.
	 */
	public static function send_header( $key, $value ) {

		/**
		 * Sanitize as per RFC2616 (Section 4.2):
		 *
		 * Any LWS that occurs between field-content MAY be replaced with a
		 * single SP before interpreting the field value or forwarding the
		 * message downstream.
		 */
		$value = preg_replace( '/\s+/', ' ', $value );
		header( apply_filters( 'graphql_send_header', sprintf( '%s: %s', $key, $value ), $key, $value ) );
	}

	/**
	 * Sends an HTTP status code.
	 *
	 * @since  0.0.5
	 * @access protected
	 *
	 * @param int $code HTTP status.
	 */
	protected static function set_status( $code ) {
		status_header( $code );
	}

	/**
	 * Returns an array of headers to send with the HTTP response
	 *
	 * @return array
	 */
	protected static function get_response_headers() {

		/**
		 * Filtered list of access control headers.
		 *
		 * @param array $access_control_headers Array of headers to allow.
		 */
		$access_control_allow_headers = apply_filters( 'graphql_access_control_allow_headers', [
			'Authorization',
			'Content-Type'
		] );

		$headers = [
			'Access-Control-Allow-Origin'  => '*',
			'Access-Control-Allow-Headers' => implode( ', ', $access_control_allow_headers ),
			'Content-Type'                 => 'application/json ; charset=' . get_option( 'blog_charset' ),
			'X-Robots-Tag'                 => 'noindex',
			'X-Content-Type-Options'       => 'nosniff',
			'X-hacker'                     => __( 'If you\'re reading this, you should visit github.com/wp-graphql and contribute!', 'wp-graphql' ),
		];

		/**
		 * Send nocache headers on authenticated requests.
		 *
		 * @since 0.0.5
		 *
		 * @param bool $rest_send_nocache_headers Whether to send no-cache headers.
		 */
		$send_no_cache_headers = apply_filters( 'graphql_send_nocache_headers', is_user_logged_in() );
		if ( $send_no_cache_headers ) {
			foreach ( wp_get_nocache_headers() as $no_cache_header_key => $no_cache_header_value ) {
				$headers[ $no_cache_header_key ] = $no_cache_header_value;
			}
		}

		/**
		 * Filter the $headers to send
		 */
		return apply_filters( 'graphql_response_headers_to_send', $headers );
	}

	/**
	 * Set the response headers
	 *
	 * @param int $http_status The status code to send as a header
	 *
	 * @since  0.0.1
	 * @access public
	 * @return void
	 */
	public static function set_headers( $http_status ) {

		if ( false === headers_sent() ) {

			/**
			 * Set the HTTP response status
			 */
			self::set_status( $http_status );

			/**
			 * Get the response headers
			 */
			$headers = self::get_response_headers();

			/**
			 * If there are headers, set them for the response
			 */
			if ( ! empty( $headers ) && is_array( $headers ) ) {

				foreach ( $headers as $key => $value ) {
					self::send_header( $key, $value );
				}
			}

			/**
			 * Fire an action when the headers are set
			 *
			 * @param array $headers The headers sent in the response
			 */
			do_action( 'graphql_response_set_headers', $headers );

		}
	}

	/**
	 * Retrieves the raw request entity (body).
	 *
	 * @since  0.0.5
	 * @access public
	 * @global string $HTTP_RAW_POST_DATA Raw post data.
	 * @return string Raw request data.
	 */
	public static function get_raw_data() {

		global $HTTP_RAW_POST_DATA;

		/*
		 * A bug in PHP < 5.2.2 makes $HTTP_RAW_POST_DATA not set by default,
		 * but we can do it ourself.
		 */
		if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
			$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
		}

		return $HTTP_RAW_POST_DATA;

	}


	/**
	 * This processes the graphql requests that come into the /graphql endpoint via an HTTP request
	 *
	 * @since  0.0.1
	 * @access public
	 * @return mixed
	 */
	public static function process_http_request() {

		/**
		 * This action can be hooked to to enable various debug tools,
		 * such as enableValidation from the GraphQL Config.
		 *
		 * @since 0.0.4
		 */
		do_action( 'graphql_process_http_request' );

		/**
		 * Start the $response array to return for the response content
		 *
		 * @since 0.0.5
		 */
		$response        = [];
		$graphql_results = [];
		$request         = '';
		$operation_name  = '';
		$variables       = [];
		$user            = null;

		try {

			/**
			 * Store the global post so it can be reset after GraphQL execution
			 *
			 * This allows for a GraphQL query to be used in the middle of post content, such as in a Shortcode
			 * without disrupting the flow of the post as the global POST before and after GraphQL execution will be
			 * the same.
			 */
			$global_post = ! empty( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

			/**
			 * Respond to pre-flight requests.
			 *
			 * @see: https://apollographql.slack.com/archives/C10HTKHPC/p1507649812000123
			 * @see: https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Preflighted_requests
			 */
			if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {

				self::$http_status_code = 200;
				self::set_headers( self::$http_status_code );
				exit;

			} else if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'GET' ) {

				$data = [
					'query'         => isset( $_GET['query'] ) ? wp_kses_stripslashes( sanitize_text_field( $_GET['query'] ) ) : '',
					'operationName' => isset( $_GET['operationName'] ) ? wp_kses_stripslashes( sanitize_text_field( $_GET['operationName'] ) ) : '',
					'variables'     => isset( $_GET['variables'] ) ? $_GET['variables'] : '',
				];

				/**
				 * Allow the data to be filtered
				 *
				 * @param array $data An array containing the pieces of the data of the GraphQL request
				 */
				$data = apply_filters( 'graphql_request_data', $data );

				/**
				 * If the variables are already formatted as an array use them.
				 *
				 * Example:
				 * ?query=query getPosts($first:Int){posts(first:$first){edges{node{id}}}}&variables[first]=1
				 */
				if ( is_array( $data['variables'] ) ) {
					$sanitized_variables = [];
					foreach ( $data['variables'] as $key => $value ) {
						$sanitized_variables[ $key ] = sanitize_text_field( $value );
					}
					$decoded_variables = $sanitized_variables;

					/**
					 * If the variables are not an array, let's attempt to decode them and convert them to an array for
					 * use in the executor.
					 */
				} else {
					$decoded_variables = json_decode( wp_kses_stripslashes( $data['variables'] ), true );
				}

				$data['variables'] = ! empty( $decoded_variables ) && is_array( $decoded_variables ) ? $decoded_variables : null;


				/**
				 * Allow the data to be filtered
				 *
				 * @param array $data An array containing the pieces of the data of the GraphQL request
				 */
				$data = apply_filters( 'graphql_request_data', $data );

				/**
				 * Get the pieces of the request from the data
				 */
				$request        = isset( $data['query'] ) ? $data['query'] : '';
				$operation_name = isset( $data['operationName'] ) ? $data['operationName'] : '';
				$variables      = isset( $data['variables'] ) ? $data['variables'] : [];


				if ( false === headers_sent() ) {
					self::prepare_headers( $response, $graphql_results, $request, $operation_name, $variables, $user );
				}

				/**
				 * Process the GraphQL request
				 *
				 * @since 0.0.5
				 */
				$graphql_results = do_graphql_request( $request, $operation_name, $variables );

				/**
				 * Ensure the $graphql_request is returned as a proper, populated array,
				 * otherwise add an error to the result
				 */
				if ( ! empty( $graphql_results ) && is_array( $graphql_results ) ) {
					$response = $graphql_results;
				} else {
					$response['errors'] = __( 'The GraphQL request returned an invalid response', 'wp-graphql' );
				}

				self::after_execute( $response, $operation_name, $request, $variables, $graphql_results );

			} else {

				/**
				 * If headers haven't been sent already, let's set the headers and return the JSON response
				 */
				if ( false === headers_sent() ) {

					self::prepare_headers( $response, $graphql_results, $request, $operation_name, $variables, $user );

					/**
					 * Send the JSON response
					 */
					$server = \WPGraphQL::server();
					$response = $server->executeRequest();

					self::after_execute( $response, $operation_name, $request, $variables, $graphql_results );

				}

			}

		} catch ( \Exception $error ) {

			/**
			 * If there are errors, set the status to 500
			 * and format the captured errors to be output properly
			 *
			 * @since 0.0.4
			 */
			self::$http_status_code = 500;
			$response['errors']     = [ FormattedError::createFromException( $error ) ];
		} // End try().

		/**
		 * Send the response
		 */
		wp_send_json( $response );

	}


	/**
	 * Prepare headers for response
	 *
	 * @param array    $response        The response of the GraphQL Request
	 * @param array    $graphql_results The results of the GraphQL execution
	 * @param string   $request         The GraphQL Request
	 * @param string   $operation_name  The operation name of the GraphQL Request
	 * @param array    $variables       The variables applied to the GraphQL Request
	 * @param \WP_User $user            The current user object
	 */
	protected static function prepare_headers( $response, $graphql_results, $request, $operation_name, $variables, $user ) {

		/**
		 * Filter the $status_code before setting the headers
		 *
		 * @param int      $status_code     The status code to apply to the headers
		 * @param array    $response        The response of the GraphQL Request
		 * @param array    $graphql_results The results of the GraphQL execution
		 * @param string   $request         The GraphQL Request
		 * @param string   $operation_name  The operation name of the GraphQL Request
		 * @param array    $variables       The variables applied to the GraphQL Request
		 * @param \WP_User $user            The current user object
		 */
		$status_code = apply_filters( 'graphql_response_status_code', self::$http_status_code, $response, $graphql_results, $request, $operation_name, $variables, $user );

		/**
		 * Set the response headers
		 */
		self::set_headers( $status_code );

	}

	/**
	 * Apply filters and do actions after GraphQL Execution
	 *
	 * @param array      $result         The result of your GraphQL request
	 * @param string     $operation_name The name of the operation
	 * @param string     $request        The request that GraphQL executed
	 * @param array|null $variables      Variables to passed to your GraphQL query
	 */
	protected static function after_execute( $result, $operation_name, $request, $variables ) {

		/**
		 * Run an action. This is a good place for debug tools to hook in to log things, etc.
		 *
		 * @since 0.0.4
		 *
		 * @param array      $result         The result of your GraphQL request
		 * @param            Schema          object $schema The schema object for the root request
		 * @param string     $operation_name The name of the operation
		 * @param string     $request        The request that GraphQL executed
		 * @param array|null $variables      Variables to passed to your GraphQL query
		 */
		do_action( 'graphql_execute', $result, \WPGraphQL::get_schema(), $operation_name, $request, $variables );

		/**
		 * Filter the $result of the GraphQL execution. This allows for the response to be filtered before
		 * it's returned, allowing granular control over the response at the latest point.
		 *
		 * POSSIBLE USAGE EXAMPLES:
		 * This could be used to ensure that certain fields never make it to the response if they match
		 * certain criteria, etc. For example, this filter could be used to check if a current user is
		 * allowed to see certain things, and if they are not, the $result could be filtered to remove
		 * the data they should not be allowed to see.
		 *
		 * Or, perhaps some systems want the result to always include some additional piece of data in
		 * every response, regardless of the request that was sent to it, this could allow for that
		 * to be hooked in and included in the $result
		 *
		 * @since 0.0.5
		 *
		 * @param array      $result         The result of your GraphQL query
		 * @param            Schema          object $schema The schema object for the root query
		 * @param string     $operation_name The name of the operation
		 * @param string     $request        The request that GraphQL executed
		 * @param array|null $variables      Variables to passed to your GraphQL request
		 */
		$filtered_result = apply_filters( 'graphql_request_results', $result, \WPGraphQL::get_schema(), $operation_name, $request, $variables );

		/**
		 * Run an action after the result has been filtered, as the response is being returned.
		 * This is a good place for debug tools to hook in to log things, etc.
		 *
		 * @param array      $filtered_result The filtered_result of the GraphQL request
		 * @param array      $result          The result of your GraphQL request
		 * @param WPSchema   $schema          The schema object for the root request
		 * @param string     $operation_name  The name of the operation
		 * @param string     $request         The request that GraphQL executed
		 * @param array|null $variables       Variables to passed to your GraphQL query
		 */
		do_action( 'graphql_return_response', $filtered_result, $result, \WPGraphQL::get_schema(), $operation_name, $request, $variables );

		/**
		 * Reset the global post after execution
		 *
		 * This allows for a GraphQL query to be used in the middle of post content, such as in a Shortcode
		 * without disrupting the flow of the post as the global POST before and after GraphQL execution will be
		 * the same.
		 */
		if ( ! empty( $global_post ) ) {
			$GLOBALS['post'] = $global_post;
		}

		/**
		 * Run an action after the HTTP Response is ready to be sent back. This might be a good place for tools
		 * to hook in to track metrics, such as how long the process took from `graphql_process_http_request`
		 * to here, etc.
		 *
		 * @param array  $result         The result of the GraphQL Query
		 * @param array  $filtered_result
		 * @param string $operation_name The name of the operation
		 * @param string $request        The request that GraphQL executed
		 * @param array  $variables      Variables to passed to your GraphQL query
		 *
		 * @since 0.0.5
		 */
		do_action( 'graphql_process_http_request_response', $filtered_result, $result, $operation_name, $request, $variables );

	}

}
