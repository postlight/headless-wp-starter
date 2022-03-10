<?php
/**
 * Admin filters.
 *
 * @package  Postlight_Headless_WP
 */

/**
 * By default, in Add/Edit Post, WordPress moves checked categories to the top of the list and unchecked to the bottom.
 * When you have subcategories that you want to keep below their parents at all times, this makes no sense.
 * This function removes automatic reordering so the categories widget retains its order regardless of checked state.
 * Thanks to https://stackoverflow.com/a/12586404
 *
 * @param arr $args Array of arguments.
 * @return arr
 */
function taxonomy_checklist_checked_ontop_filter( $args ) {
    $args['checked_ontop'] = false;
    return $args;
}

add_filter( 'wp_terms_checklist_args', 'taxonomy_checklist_checked_ontop_filter' );

/**
 * Customize the preview button in the WordPress admin to point to the headless client.
 *
 * @param  str $link The WordPress preview link.
 * @return str The headless WordPress preview link.
 */
function set_headless_preview_link( $link ) {
    $post = get_post();
    $post_status = get_post_status( $post );

    if ( ! $post ) {
        return $link;
    }
    $status      = 'revision';
    $frontend    = get_frontend_origin();
    $parent_id   = $post->post_parent;
    $revision_id = $post->ID;
    $type        = get_post_type( $parent_id );
    $nonce       = wp_create_nonce( 'wp_rest' );
    if ( 0 === $parent_id ) {
        $status = 'draft';
    }

    if ( 'publish' === $post_status ) {
        $post_slug = $post->post_name;
        return "$frontend/$type/$post_slug";
    }

    return "$frontend/_preview/$parent_id/$revision_id/$type/$status/$nonce";
}

add_filter( 'preview_post_link', 'set_headless_preview_link' );

add_filter( 'post_link', 'set_headless_preview_link' );
add_filter( 'page_link', 'set_headless_preview_link' );

/**
 * Includes preview link in post data for a response.
 *
 * @param \WP_REST_Response $response The response object.
 * @param \WP_Post          $post     Post object.
 * @return \WP_REST_Response The response object.
 */
function set_preview_link_in_rest_response( $response, $post ) {
    if ( 'draft' === $post->post_status ) {
        $response->data['preview_link'] = get_preview_post_link( $post );
    }

    return $response;
}

add_filter( 'rest_prepare_post', 'set_preview_link_in_rest_response', 10, 2 );
add_filter( 'rest_prepare_page', 'set_preview_link_in_rest_response', 10, 2 );
