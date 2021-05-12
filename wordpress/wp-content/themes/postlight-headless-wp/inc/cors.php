<?php
/**
 * REST API CORS filter.
 *
 * @package  Postlight_Headless_WP
 */

/**
 * Allow GET requests from origin
 * Thanks to https://joshpress.net/access-control-headers-for-the-wordpress-rest-api/
 */
add_action(
    'rest_api_init',
    function () {
        remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

        add_filter(
            'rest_pre_serve_request',
            function ( $value ) {
                header( 'Access-Control-Allow-Origin: ' . get_frontend_origin() );
                header( 'Access-Control-Allow-Methods: GET' );
                header( 'Access-Control-Allow-Credentials: true' );
                return $value;
            }
        );
    },
    15
);


add_action(
    'rest_api_init',
    function () {
        register_rest_route( 'api', '/api(?:/(?P<id>\d+))?', [
            'methods' => WP_REST_Server::READABLE,
            'args' => [
                 'id'
             ],
         ] );
    },
    15
);


add_action(
    'rest_api_init',
    function () {
        register_rest_route( 'rankings', '/rankings(?:/(?P<id>\d+))?', [
            'methods' => WP_REST_Server::READABLE,
            'args' => [
                 'id'
             ],
         ] );
    },
    15
);

