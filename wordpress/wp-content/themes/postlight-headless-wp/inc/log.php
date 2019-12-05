<?php
/**
 * Log helper functions.
 *
 * @package  Postlight_Headless_WP
 */

/**
 * Logs messages/variables/data to browser console from within php
 * Thanks to https://codeinphp.github.io/post/outputting-php-to-browser-console/
 *
 * To use this, in your PHP template inside PHP tags, add a line like this:
 *  log_console('$mobile_image_size_inline_style var', $mobile_image_size_inline_style, true);
 *
 * @param str  $name message to be shown for optional data/vars.
 * @param str  $data variable (scalar/mixed) arrays/objects, etc to be logged.
 * @param bool $js_eval whether to apply JS eval() to arrays/objects.
 *
 * @return none
 * @author Sarfraz
 */
function log_console( $name, $data = null, $js_eval = false ) {
    if ( ! $name ) {
        return false;
    }

    $is_evaled = false;
    $type      = ( $data || gettype( $data ) ) ? 'Type: ' . gettype( $data ) : '';

    if ( $js_eval && ( is_array( $data ) || is_object( $data ) ) ) {
        $data      = 'eval(' . preg_replace( '#[\s\r\n\t\0\x0B]+#', '', wp_json_encode( $data ) ) . ')';
        $is_evaled = true;
    } else {
        $data = wp_json_encode( $data );
    }

    // sanitize.
    $data          = $data ? $data : '';
    $search_array  = [ "#'#", '#""#', "#''#", "#\n#", "#\r\n#" ];
    $replace_array = [ '"', '', '', '\\n', '\\n' ];
    $data          = preg_replace( $search_array, $replace_array, $data );
    $data          = ltrim( rtrim( $data, '"' ), '"' );
    $data          = $is_evaled ? $data : ( "'" === $data[0] ) ? $data : "'" . $data . "'";

    $js = <<<JSCODE
\n<script>
 // fallback - to deal with IE (or browsers that don't have console)
 if (! window.console) console = {};
 console.log = console.log || function(name, data){};
 // end of fallback

 console.log('$name');
 console.log('------------------------------------------');
 console.log('$type');
 console.log($data);
 console.log('\\n');
</script>
JSCODE;

    echo $js; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
}

/**
 * Log a value in wp-content/debug.log.
 *
 * To turn on, add the following to wp-config.php:
 *
 * define( 'WP_DEBUG', true );
 * define( 'WP_DEBUG_LOG', true );  // Turn logging to wp-content/debug.log ON
 * define( 'WP_DEBUG_DISPLAY', false ); // Keep JSON response valid
 *
 * @ini_set( 'display_errors', 0 ); // Keep JSON responses valid
 *
 * NOT INTENDED FOR PRODUCTION USE.
 *
 * @param str $message Message.
 * @param str $file Filename, defaults to __FILE__.
 * @param str $line Line number, defaults to __LINE__.
 * @return void
 */
function log_it( $message, $file = __FILE__, $line = __LINE__ ) {
    // phpcs:disable WordPress
    if ( WP_DEBUG === true ) {
        if ( is_array( $message ) || is_object( $message ) ) {
            error_log( $file . 'L' . $line . ' ' . ( print_r( $message, true ) ) );
        } else {
            error_log( $file . 'L' . $line . ' ' . $message );
        }
    }
    // phpcs:enable WordPress
}
