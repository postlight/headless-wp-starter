<?php

/**
 * Logs messages/variables/data to browser console from within php
 * Thanks to https://codeinphp.github.io/post/outputting-php-to-browser-console/
 *
 * To use this, in your PHP template inside PHP tags, add a line like this:
 *  logConsole('$mobile_image_size_inline_style var', $mobile_image_size_inline_style, true);
 *
 * @param $name: message to be shown for optional data/vars
 * @param $data: variable (scalar/mixed) arrays/objects, etc to be logged
 * @param $js_eval: whether to apply JS eval() to arrays/objects
 *
 * @return none
 * @author Sarfraz
 */
function log_console( $name, $data = null, $js_eval = false ) {
	if ( ! $name ) {
		return false;
	}

	$is_evaled = false;
	$type = ( $data || gettype( $data ) ) ? 'Type: ' . gettype( $data ) : '';

	if ( $js_eval && ( is_array( $data ) || is_object( $data ) ) ) {
		$data = 'eval(' . preg_replace( '#[\s\r\n\t\0\x0B]+#', '', wp_json_encode( $data ) ) . ')';
		$is_evaled = true;
	} else {
		$data = wp_json_encode( $data );
	}

	# sanitalize
	$data = $data ? $data : '';
	$search_array = array( "#'#", '#""#', "#''#", "#\n#", "#\r\n#" );
	$replace_array = array( '"', '', '', '\\n', '\\n' );
	$data = preg_replace( $search_array,  $replace_array, $data );
	$data = ltrim( rtrim( $data, '"' ), '"' );
	$data = $is_evaled ? $data : ( "'" === $data[0] ) ? $data : "'" . $data . "'";

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

	echo $js;
}

/**
 * Log a value in wp-content/debug.log.
 * @param  str $message
 * @param str $file Filename, defaults to ''
 * @param str $line Line number, defaults to ''
 * @return null
 */
function log_it( $message, $file = '', $line = '' ) {
	if ( WP_DEBUG === true ) {
		if ( is_array( $message ) || is_object( $message ) ) {
			error_log( $file . 'L' . $line . ' ' . ( print_r( $message, true ) ) );
		} else {
			error_log( $file . 'L' . $line . ' ' . $message );
		}
	}
}
