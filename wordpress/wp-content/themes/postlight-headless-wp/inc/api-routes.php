<?php
/**
 * Register custom REST API routes.
 */
add_action( 'rest_api_init', function () {
	// Define API endpoint arguments
	$slug_arg = array(
		'validate_callback' => function ( $param, $request, $key ) {
			return( is_string( $param ) );
		}
	);
	$post_slug_arg = array_merge( $slug_arg, array( 'description' => 'String representing a valid WordPress post slug' ) );

	$page_slug_arg = array_merge( $slug_arg, array( 'description' => 'String representing a valid WordPress page slug' ) );

	// Register routes
	register_rest_route( 'postlight/v1', '/post', array(
		'methods'  => 'GET',
		'callback' => 'rest_get_post',
		'args' => array(
			'slug' => array_merge( $post_slug_arg, array( 'required' => true ) ),
		)
	) );

	register_rest_route( 'postlight/v1', '/page', array(
		'methods'  => 'GET',
		'callback' => 'rest_get_page',
		'args' => array(
			'slug' => array_merge( $page_slug_arg, array( 'required' => true ) ),
		)
	) );
});

/**
 * Respond to a REST API request to get post data.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function rest_get_post( WP_REST_Request $request ) {
	return rest_get_content( $request, 'post', __FUNCTION__ );
}

/**
 * Respond to a REST API request to get page data.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function rest_get_page( WP_REST_Request $request ) {
	return rest_get_content( $request, 'page', __FUNCTION__ );
}

/**
 * Respond to a REST API request to get post or page data.
 * * Handles changed slugs
 * * Doesn't return posts whose status isn't published
 * * Redirects to the admin when an edit parameter is present
 *
 * @param WP_REST_Request $request
 * @param str $type
 * @param str $function_name
 * @return WP_REST_Response
 */
function rest_get_content( WP_REST_Request $request, $type, $function_name ) {
	if ( ! in_array( $type, array ( 'post', 'page' ) ) ) {
		$type = 'post';
	}
	$slug = $request->get_param( 'slug');
	if ( ! $post = get_content_by_slug( $slug, $type ) ) {
		return new WP_Error(
			$function_name,
			$slug . ' ' . $type . ' does not exist',
			array( 'status' => 404 )
		);
	};

	// Shortcut to WP admin page editor
	$edit = $request->get_param( 'edit' );
	if ( $edit === 'true' ) {
		header( 'Location: /wp-admin/post.php?post=' . $post->ID . '&action=edit' );
		exit;
	}
	$controller = new WP_REST_Posts_Controller( 'post' );
	$data = $controller->prepare_item_for_response( $post, $request );
	$response = $controller->prepare_response_for_collection( $data );

	return new WP_REST_Response( $response );
}

/**
 * Returns a post or page given a slug. Returns false if no post matches.
 *
 * @param str $slug
 * @param str $type Valid values are 'post' or 'page'
 * @return Post
 */
function get_content_by_slug( $slug, $type = 'post' ) {
	if ( ! in_array( $type, array ( 'post', 'page' ) ) ) {
		$type = 'post';
	}
	$args = array(
		'name'        => $slug,
		'post_type'   => $type,
		'post_status' => 'publish',
		'numberposts' => 1
	);

	$post_search_results = get_posts( $args );

	if ( !$post_search_results ) { //maybe the slug changed?
		// check wp_postmeta table for old slug
		$args = array(
			'meta_query' => array(
				array(
					'key' => '_wp_old_slug',
					'value' => $post_slug,
					'compare' => '=',
				)
			)
		);
		$query = new WP_Query( $args );
		$post_search_results = $query->posts;
	}
	if ( isset( $post_search_results[0] ) ) {
		return $post_search_results[0];
	}
	return false;
}
