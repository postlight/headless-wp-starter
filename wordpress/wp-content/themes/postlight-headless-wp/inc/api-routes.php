<?php
/**
 * Register custom REST API routes.
 */
add_action(
    'rest_api_init',
    function () {
        register_rest_route('postlight/v1', '/preview', [
            'methods'  => 'GET',
            'callback' => 'rest_get_post_preview',
            'args' => [
                'id' => [
                    'validate_callback' => function ( $param, $request, $key ) {
                        return ( is_numeric( $param ) );
                    },
                    'required' => true,
                    'description' => 'Valid WordPress post ID',
                ],
            ],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ] );
    }
);

/**
 * Respond to a REST API request to get a post's latest revision.
 * * Requires a valid _wpnonce on the query string
 * * User must have 'edit_posts' rights
 * * Will return draft revisions of even published posts
 *
 * @param  WP_REST_Request $request Rest request.
 * @return WP_REST_Response
 */
function rest_get_post_preview( WP_REST_Request $request ) {

    $post_id = $request->get_param( 'id' );
    // Revisions are drafts so here we remove the default 'publish' status
    remove_action( 'pre_get_posts', 'set_default_status_to_publish' );
    $check_enabled = [
        'check_enabled' => false,
    ];
    if ( $revisions = wp_get_post_revisions( $post_id, $check_enabled ) ) {
        $last_revision = reset( $revisions );
        $rev_post = wp_get_post_revision( $last_revision->ID );
        $controller = new WP_REST_Posts_Controller( 'post' );
        $data = $controller->prepare_item_for_response( $rev_post, $request );
    } elseif ( $post = get_post( $post_id ) ) { // There are no revisions, just return the saved parent post
        $controller = new WP_REST_Posts_Controller( 'post' );
        $data = $controller->prepare_item_for_response( $post, $request );
    } else {
        $not_found = [
            'status' => 404,
        ];
        $error = new WP_Error(
            'rest_get_post_preview',
            'Post ' . $post_id . ' does not exist',
            $not_found
        );
        return $error;
    }
    $response = $controller->prepare_response_for_collection( $data );
    return new WP_REST_Response( $response );
}
