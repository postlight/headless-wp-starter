<?php 

/*
*  acf_is_array
*
*  This function will return true for a non empty array
*
*  @type	function
*  @date	6/07/2016
*  @since	5.4.0
*
*  @param	$array (array)
*  @return	(boolean)
*/

function acf_is_array( $array ) {
	
	return ( is_array($array) && !empty($array) );
	
}


/*
*  acf_is_empty
*
*  This function will return true for an empty var (allows 0 as true)
*
*  @type	function
*  @date	6/07/2016
*  @since	5.4.0
*
*  @param	$value (mixed)
*  @return	(boolean)
*/

function acf_is_empty( $value ) {
	
	return ( empty($value) && !is_numeric($value) );
	
}

/**
*  acf_idify
*
*  Returns an id friendly string
*
*  @date	24/12/17
*  @since	5.6.5
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function acf_idify( $str = '' ) {
	return str_replace(array('][', '[', ']'), array('-', '-', ''), strtolower($str));
}

/**
*  acf_slugify
*
*  Returns a slug friendly string
*
*  @date	24/12/17
*  @since	5.6.5
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function acf_slugify( $str = '' ) {
	return str_replace('_', '-', strtolower($str));
}

/**
*  acf_has_setting
*
*  alias of acf()->has_setting()
*
*  @date	2/2/18
*  @since	5.6.5
*
*  @param	n/a
*  @return	n/a
*/

function acf_has_setting( $name = '' ) {
	return acf()->has_setting( $name );
}


/**
*  acf_raw_setting
*
*  alias of acf()->get_setting()
*
*  @date	2/2/18
*  @since	5.6.5
*
*  @param	n/a
*  @return	n/a
*/

function acf_raw_setting( $name = '' ) {
	return acf()->get_setting( $name );
}


/*
*  acf_update_setting
*
*  alias of acf()->update_setting()
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string)
*  @param	$value (mixed)
*  @return	n/a
*/

function acf_update_setting( $name, $value ) {
	
	// validate name
	$name = acf_validate_setting( $name );
	
	// update
	return acf()->update_setting( $name, $value );
}


/**
*  acf_validate_setting
*
*  Returns the changed setting name if available.
*
*  @date	2/2/18
*  @since	5.6.5
*
*  @param	n/a
*  @return	n/a
*/

function acf_validate_setting( $name = '' ) {
	return apply_filters( "acf/validate_setting", $name );
}


/*
*  acf_get_setting
*
*  alias of acf()->get_setting()
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_get_setting( $name, $value = null ) {
	
	// validate name
	$name = acf_validate_setting( $name );
	
	// check settings
	if( acf_has_setting($name) ) {
		$value = acf_raw_setting( $name );
	}
	
	// filter
	$value = apply_filters( "acf/settings/{$name}", $value );
	
	// return
	return $value;
}


/*
*  acf_append_setting
*
*  This function will add a value into the settings array found in the acf object
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string)
*  @param	$value (mixed)
*  @return	n/a
*/

function acf_append_setting( $name, $value ) {
	
	// vars
	$setting = acf_raw_setting( $name );
	
	// bail ealry if not array
	if( !is_array($setting) ) {
		$setting = array();
	}
	
	// append
	$setting[] = $value;
	
	// update
	return acf_update_setting( $name, $setting );
}


/**
*  acf_get_data
*
*  Returns data.
*
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	string $name
*  @return	mixed
*/

function acf_get_data( $name ) {
	return acf()->get_data( $name );
}


/**
*  acf_set_data
*
*  Sets data.
*
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	string $name
*  @param	mixed $value
*  @return	n/a
*/

function acf_set_data( $name, $value ) {
	return acf()->set_data( $name, $value );
}


/**
*  acf_new_instance
*
*  description
*
*  @date	13/2/18
*  @since	5.6.5
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function acf_new_instance( $class ) {
	return acf()->new_instance( $class );
}


/**
*  acf_get_instance
*
*  description
*
*  @date	13/2/18
*  @since	5.6.5
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function acf_get_instance( $class ) {
	return acf()->get_instance( $class );
}


/*
*  acf_init
*
*  alias of acf()->init()
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_init() {
	
	acf()->init();
	
}


/*
*  acf_get_compatibility
*
*  This function will return true or false for a given compatibility setting
*
*  @type	function
*  @date	20/01/2015
*  @since	5.1.5
*
*  @param	$name (string)
*  @return	(boolean)
*/

function acf_get_compatibility( $name ) {
	
	return apply_filters( "acf/compatibility/{$name}", false );
	
}


/*
*  acf_has_done
*
*  This function will return true if this action has already been done
*
*  @type	function
*  @date	16/12/2015
*  @since	5.3.2
*
*  @param	$name (string)
*  @return	(boolean)
*/

function acf_has_done( $name ) {
	
	// return true if already done
	if( acf_raw_setting("has_done_{$name}") ) {
		return true;
	}
	
	// update setting and return
	acf_update_setting("has_done_{$name}", true);
	return false;
}


/*
*  acf_get_path
*
*  This function will return the path to a file within the ACF plugin folder
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$path (string) the relative path from the root of the ACF plugin folder
*  @return	(string)
*/

function acf_get_path( $path = '' ) {
	
	return ACF_PATH . $path;
	
}


/**
*  acf_get_url
*
*  This function will return the url to a file within the ACF plugin folder
*
*  @date	12/12/17
*  @since	5.6.8
*
*  @param	string $path The relative path from the root of the ACF plugin folder
*  @return	string
*/

function acf_get_url( $path = '' ) {
	
	// define ACF_URL to optimise performance
	if( !defined('ACF_URL') ) {
		define( 'ACF_URL', acf_get_setting('url') );
	}
	
	// return
	return ACF_URL . $path;
	
}


/*
*  acf_get_dir
*
*  Deprecated in 5.6.8. Use acf_get_url() instead.
*
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	string
*  @return	string
*/

function acf_get_dir( $path = '' ) {
	return acf_get_url( $path );
}


/*
*  acf_include
*
*  This function will include a file
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_include( $file ) {
	
	$path = acf_get_path( $file );
	
	if( file_exists($path) ) {
		
		include_once( $path );
		
	}
	
}


/*
*  acf_get_external_path
*
*  This function will return the path to a file within an external folder
*
*  @type	function
*  @date	22/2/17
*  @since	5.5.8
*
*  @param	$file (string)
*  @param	$path (string)
*  @return	(string)
*/

function acf_get_external_path( $file, $path = '' ) {
    
    return plugin_dir_path( $file ) . $path;
    
}


/*
*  acf_get_external_dir
*
*  This function will return the url to a file within an external folder
*
*  @type	function
*  @date	22/2/17
*  @since	5.5.8
*
*  @param	$file (string)
*  @param	$path (string)
*  @return	(string)
*/

function acf_get_external_dir( $file, $path = '' ) {
    
    return acf_plugin_dir_url( $file ) . $path;
	
}


/**
*  acf_plugin_dir_url
*
*  This function will calculate the url to a plugin folder.
*  Different to the WP plugin_dir_url(), this function can calculate for urls outside of the plugins folder (theme include).
*
*  @date	13/12/17
*  @since	5.6.8
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function acf_plugin_dir_url( $file ) {
	
	// vars
	$path = plugin_dir_path( $file );
	$path = wp_normalize_path( $path );
	
	
	// check plugins
	$check_path = wp_normalize_path( realpath(WP_PLUGIN_DIR) );
	if( strpos($path, $check_path) === 0 ) {
		return str_replace( $check_path, plugins_url(), $path );
	}
	
	// check wp-content
	$check_path = wp_normalize_path( realpath(WP_CONTENT_DIR) );
	if( strpos($path, $check_path) === 0 ) {
		return str_replace( $check_path, content_url(), $path );
	}
	
	// check root
	$check_path = wp_normalize_path( realpath(ABSPATH) );
	if( strpos($path, $check_path) === 0 ) {
		return str_replace( $check_path, site_url('/'), $path );
	}
	                
    
    // return
    return plugin_dir_url( $file );
    
}


/*
*  acf_parse_args
*
*  This function will merge together 2 arrays and also convert any numeric values to ints
*
*  @type	function
*  @date	18/10/13
*  @since	5.0.0
*
*  @param	$args (array)
*  @param	$defaults (array)
*  @return	$args (array)
*/

function acf_parse_args( $args, $defaults = array() ) {
	
	// parse args
	$args = wp_parse_args( $args, $defaults );
	
	
	// parse types
	$args = acf_parse_types( $args );
	
	
	// return
	return $args;
	
}


/*
*  acf_parse_types
*
*  This function will convert any numeric values to int and trim strings
*
*  @type	function
*  @date	18/10/13
*  @since	5.0.0
*
*  @param	$var (mixed)
*  @return	$var (mixed)
*/

function acf_parse_types( $array ) {
	
	// return
	return array_map( 'acf_parse_type', $array );
	
}


/*
*  acf_parse_type
*
*  description
*
*  @type	function
*  @date	11/11/2014
*  @since	5.0.9
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_parse_type( $v ) {
		
	// bail early if not string
	if( !is_string($v) ) return $v;
	
	
	// trim
	$v = trim($v);
	
	
	// convert int (string) to int
	if( is_numeric($v) && strval((int)$v) === $v ) {
		
		$v = intval( $v );
		
	}
	
	
	// return
	return $v;
	
}


/*
*  acf_get_view
*
*  This function will load in a file from the 'admin/views' folder and allow variables to be passed through
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$view_name (string)
*  @param	$args (array)
*  @return	n/a
*/

function acf_get_view( $path = '', $args = array() ) {
	
	// allow view file name shortcut
	if( substr($path, -4) !== '.php' ) {
		
		$path = acf_get_path("includes/admin/views/{$path}.php");
		
	}
	
	
	// include
	if( file_exists($path) ) {
		
		extract( $args );
		include( $path );
		
	}
	
}


/*
*  acf_merge_atts
*
*  description
*
*  @type	function
*  @date	2/11/2014
*  @since	5.0.9
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_merge_atts( $atts, $extra = array() ) {
	
	// bail ealry if no $extra
	if( empty($extra) ) return $atts;
	
	
	// trim
	$extra = array_map('trim', $extra);
	$extra = array_filter($extra);
	
	
	// merge in new atts
	foreach( $extra as $k => $v ) {
		
		// append
		if( $k == 'class' || $k == 'style' ) {
			
			$atts[ $k ] .= ' ' . $v;
		
		// merge	
		} else {
			
			$atts[ $k ] = $v;
			
		}
		
	}
	
	
	// return
	return $atts;
	
}


/*
*  acf_nonce_input
*
*  This function will create a basic nonce input
*
*  @type	function
*  @date	24/5/17
*  @since	5.6.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_nonce_input( $nonce = '' ) {
	
	echo '<input type="hidden" name="_acf_nonce" value="' . wp_create_nonce( $nonce ) . '" />';
	
}


/*
*  acf_extract_var
*
*  This function will remove the var from the array, and return the var
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$array (array)
*  @param	$key (string)
*  @return	(mixed)
*/

function acf_extract_var( &$array, $key, $default = null ) {
	
	// check if exists
	// - uses array_key_exists to extract NULL values (isset will fail)
	if( is_array($array) && array_key_exists($key, $array) ) {
		
		// store value
		$v = $array[ $key ];
		
		
		// unset
		unset( $array[ $key ] );
		
		
		// return
		return $v;
		
	}
	
	
	// return
	return $default;
}


/*
*  acf_extract_vars
*
*  This function will remove the vars from the array, and return the vars
*
*  @type	function
*  @date	8/10/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_extract_vars( &$array, $keys ) {
	
	$r = array();
	
	foreach( $keys as $key ) {
		
		$r[ $key ] = acf_extract_var( $array, $key );
		
	}
	
	return $r;
}


/*
*  acf_get_sub_array
*
*  This function will return a sub array of data
*
*  @type	function
*  @date	15/03/2016
*  @since	5.3.2
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_sub_array( $array, $keys ) {
	
	$r = array();
	
	foreach( $keys as $key ) {
		
		$r[ $key ] = $array[ $key ];
		
	}
	
	return $r;
	
}


/**
*  acf_get_post_types
*
*  Returns an array of post type names.
*
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	array $args Optional. An array of key => value arguments to match against the post type objects. Default empty array.
*  @return	array A list of post type names.
*/

function acf_get_post_types( $args = array() ) {
	
	// vars
	$post_types = array();
	
	// extract special arg
	$exclude = acf_extract_var( $args, 'exclude', array() );
	$exclude[] = 'acf-field';
	$exclude[] = 'acf-field-group';
	
	// get post type objects
	$objects = get_post_types( $args, 'objects' );
	
	// loop
	foreach( $objects as $i => $object ) {
		
		// bail early if is exclude
		if( in_array($i, $exclude) ) continue;
		
		// bail early if is builtin (WP) private post type
		// - nav_menu_item, revision, customize_changeset, etc
		if( $object->_builtin && !$object->public ) continue;
		
		// append
		$post_types[] = $i;
	}
	
	// filter
	$post_types = apply_filters('acf/get_post_types', $post_types, $args);
	
	// return
	return $post_types;
}

function acf_get_pretty_post_types( $post_types = array() ) {
	
	// get post types
	if( empty($post_types) ) {
		
		// get all custom post types
		$post_types = acf_get_post_types();
		
	}
	
	
	// get labels
	$ref = array();
	$r = array();
	
	foreach( $post_types as $post_type ) {
		
		// vars
		$label = acf_get_post_type_label($post_type);
		
		
		// append to r
		$r[ $post_type ] = $label;
		
		
		// increase counter
		if( !isset($ref[ $label ]) ) {
			
			$ref[ $label ] = 0;
			
		}
		
		$ref[ $label ]++;
	}
	
	
	// get slugs
	foreach( array_keys($r) as $i ) {
		
		// vars
		$post_type = $r[ $i ];
		
		if( $ref[ $post_type ] > 1 ) {
			
			$r[ $i ] .= ' (' . $i . ')';
			
		}
		
	}
	
	
	// return
	return $r;
	
}



/*
*  acf_get_post_type_label
*
*  This function will return a pretty label for a specific post_type
*
*  @type	function
*  @date	5/07/2016
*  @since	5.4.0
*
*  @param	$post_type (string)
*  @return	(string)
*/

function acf_get_post_type_label( $post_type ) {
	
	// vars
	$label = $post_type;
	
		
	// check that object exists
	// - case exists when importing field group from another install and post type does not exist
	if( post_type_exists($post_type) ) {
		
		$obj = get_post_type_object($post_type);
		$label = $obj->labels->singular_name;
		
	}
	
	
	// return
	return $label;
	
}


/*
*  acf_verify_nonce
*
*  This function will look at the $_POST['_acf_nonce'] value and return true or false
*
*  @type	function
*  @date	15/10/13
*  @since	5.0.0
*
*  @param	$nonce (string)
*  @return	(boolean)
*/

function acf_verify_nonce( $value) {
	
	// vars
	$nonce = acf_maybe_get_POST('_acf_nonce');
	
	
	// bail early nonce does not match (post|user|comment|term)
	if( !$nonce || !wp_verify_nonce($nonce, $value) ) return false;
	
	
	// reset nonce (only allow 1 save)
	$_POST['_acf_nonce'] = false;
	
	
	// return
	return true;
		
}


/*
*  acf_verify_ajax
*
*  This function will return true if the current AJAX request is valid
*  It's action will also allow WPML to set the lang and avoid AJAX get_posts issues
*
*  @type	function
*  @date	7/08/2015
*  @since	5.2.3
*
*  @param	n/a
*  @return	(boolean)
*/

function acf_verify_ajax() {
	
	// vars
	$action = acf_maybe_get_POST('action');
	$nonce = acf_maybe_get_POST('nonce');
	
	
	// bail early if not acf action
	if( !$action || substr($action, 0, 3) !== 'acf' ) {
		return false;
	}
	
	
	// bail early if not acf nonce
	if( !$nonce || !wp_verify_nonce($nonce, 'acf_nonce') ) {
		return false;
	}
	
	
	// action for 3rd party customization
	do_action('acf/verify_ajax');
	
	
	// return
	return true;
	
}


/*
*  acf_get_image_sizes
*
*  This function will return an array of available image sizes
*
*  @type	function
*  @date	23/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_image_sizes() {
	
	// vars
	$sizes = array(
		'thumbnail'	=>	__("Thumbnail",'acf'),
		'medium'	=>	__("Medium",'acf'),
		'large'		=>	__("Large",'acf')
	);
	
	
	// find all sizes
	$all_sizes = get_intermediate_image_sizes();
	
	
	// add extra registered sizes
	if( !empty($all_sizes) ) {
		
		foreach( $all_sizes as $size ) {
			
			// bail early if already in array
			if( isset($sizes[ $size ]) ) {
			
				continue;
				
			}
			
			
			// append to array
			$label = str_replace('-', ' ', $size);
			$label = ucwords( $label );
			$sizes[ $size ] = $label;
			
		}
		
	}
	
	
	// add sizes
	foreach( array_keys($sizes) as $s ) {
		
		// vars
		$data = acf_get_image_size($s);
		
		
		// append
		if( $data['width'] && $data['height'] ) {
			
			$sizes[ $s ] .= ' (' . $data['width'] . ' x ' . $data['height'] . ')';
			
		}
		
	}
	
	
	// add full end
	$sizes['full'] = __("Full Size",'acf');
	
	
	// filter for 3rd party customization
	$sizes = apply_filters( 'acf/get_image_sizes', $sizes );
	
	
	// return
	return $sizes;
	
}

function acf_get_image_size( $s = '' ) {
	
	// global
	global $_wp_additional_image_sizes;
	
	
	// rename for nicer code
	$_sizes = $_wp_additional_image_sizes;
	
	
	// vars
	$data = array(
		'width' 	=> isset($_sizes[$s]['width']) ? $_sizes[$s]['width'] : get_option("{$s}_size_w"),
		'height'	=> isset($_sizes[$s]['height']) ? $_sizes[$s]['height'] : get_option("{$s}_size_h")
	);
	
	
	// return
	return $data;
		
}


/*
*  acf_version_compare
*
*  This function will compare version left v right
*
*  @type	function
*  @date	21/11/16
*  @since	5.5.0
*
*  @param	$compare (string)
*  @param	$version (string)
*  @return	(boolean)
*/

function acf_version_compare( $left = 'wp', $compare = '>', $right = '1' ) {
	
	// global
	global $wp_version;
	
	
	// wp
	if( $left === 'wp' ) $left = $wp_version;
	
	
	// remove '-beta1' or '-RC1'
	$left = acf_get_full_version($left);
	$right = acf_get_full_version($right);
	
	
	// return
	return version_compare( $left, $right, $compare );
	
}


/*
*  acf_get_full_version
*
*  This function will remove any '-beta1' or '-RC1' strings from a version
*
*  @type	function
*  @date	24/11/16
*  @since	5.5.0
*
*  @param	$version (string)
*  @return	(string)
*/

function acf_get_full_version( $version = '1' ) {
	
	// remove '-beta1' or '-RC1'
	if( $pos = strpos($version, '-') ) {
		
		$version = substr($version, 0, $pos);
		
	}
	
	
	// return
	return $version;
	
}


/*
*  acf_get_locale
*
*  This function is a wrapper for the get_locale() function
*
*  @type	function
*  @date	16/12/16
*  @since	5.5.0
*
*  @param	n/a
*  @return	(string)
*/

function acf_get_locale() {
	
	return is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
	
}


/*
*  acf_get_terms
*
*  This function is a wrapper for the get_terms() function
*
*  @type	function
*  @date	28/09/2016
*  @since	5.4.0
*
*  @param	$args (array)
*  @return	(array)
*/

function acf_get_terms( $args ) {
	
	// global
	global $wp_version;
	
	
	// vars
	$terms = array();
	
		
	// WP 4.5+
	if( version_compare($wp_version, '4.5', '>=' ) ) {
		
		$terms = get_terms( $args );

	// WP < 4.5
	} else {
		
		$terms = get_terms( $args['taxonomy'], $args );
	
	}
	
	
	// return
	return $terms;
	
}


/*
*  acf_get_taxonomies
*
*  This function will return an array of available taxonomies
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_taxonomies() {

	// get all taxonomies
	$taxonomies = get_taxonomies( false, 'objects' );
	$ignore = array( 'nav_menu', 'link_category' );
	$r = array();
	
	
	// populate $r
	foreach( $taxonomies as $taxonomy )
	{
		if( in_array($taxonomy->name, $ignore) )
		{
			continue;
		
		}
		
		$r[ $taxonomy->name ] = $taxonomy->name; //"{$taxonomy->labels->singular_name}"; // ({$taxonomy->name})
	}
	
	
	// return
	return $r;
	
}


function acf_get_pretty_taxonomies( $taxonomies = array() ) {
	
	// get post types
	if( empty($taxonomies) ) {
		
		// get all custom post types
		$taxonomies = acf_get_taxonomies();
		
	}
	
	
	// get labels
	$ref = array();
	$r = array();
	
	foreach( array_keys($taxonomies) as $i ) {
		
		// vars
		$taxonomy = acf_extract_var( $taxonomies, $i);
		$obj = get_taxonomy( $taxonomy );
		$name = $obj->labels->singular_name;
		
		
		// append to r
		$r[ $taxonomy ] = $name;
		
		
		// increase counter
		if( !isset($ref[ $name ]) ) {
			
			$ref[ $name ] = 0;
			
		}
		
		$ref[ $name ]++;
	}
	
	
	// get slugs
	foreach( array_keys($r) as $i ) {
		
		// vars
		$taxonomy = $r[ $i ];
		
		if( $ref[ $taxonomy ] > 1 ) {
			
			$r[ $i ] .= ' (' . $i . ')';
			
		}
		
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_get_taxonomy_terms
*
*  This function will return an array of available taxonomy terms
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$taxonomies (array)
*  @return	(array)
*/

function acf_get_taxonomy_terms( $taxonomies = array() ) {
	
	// force array
	$taxonomies = acf_get_array( $taxonomies );
	
	
	// get pretty taxonomy names
	$taxonomies = acf_get_pretty_taxonomies( $taxonomies );
	
	
	// vars
	$r = array();
	
	
	// populate $r
	foreach( array_keys($taxonomies) as $taxonomy ) {
		
		// vars
		$label = $taxonomies[ $taxonomy ];
		$is_hierarchical = is_taxonomy_hierarchical( $taxonomy );
		$terms = acf_get_terms(array(
			'taxonomy'		=> $taxonomy,
			'hide_empty' 	=> false
		));
		
		
		// bail early i no terms
		if( empty($terms) ) continue;
		
		
		// sort into hierachial order!
		if( $is_hierarchical ) {
			
			$terms = _get_term_children( 0, $terms, $taxonomy );
			
		}
		
		
		// add placeholder		
		$r[ $label ] = array();
		
		
		// add choices
		foreach( $terms as $term ) {
		
			$k = "{$taxonomy}:{$term->slug}"; 
			$r[ $label ][ $k ] = acf_get_term_title( $term );
			
		}
		
	}
		
	
	// return
	return $r;
	
}


function acf_get_term_title( $term ) {
	
	// title
	$title = $term->name;
	
	
	// empty
	if( $title === '' ) {
		
		$title = __('(no title)', 'acf');
		
	}
	
	
	// ancestors
	if( is_taxonomy_hierarchical($term->taxonomy) ) {
		
		$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
		
		$title = str_repeat('- ', count($ancestors)) . $title;
		
	}
	
	
	// return
	return $title;
	
}


/*
*  acf_decode_taxonomy_terms
*
*  This function decodes the $taxonomy:$term strings into a nested array
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$terms (array)
*  @return	(array)
*/

function acf_decode_taxonomy_terms( $strings = false ) {
	
	// bail early if no terms
	if( empty($strings) ) return false;
	
	
	// vars
	$terms = array();
	
	
	// loop
	foreach( $strings as $string ) {
		
		// vars
		$data = acf_decode_taxonomy_term( $string );
		$taxonomy = $data['taxonomy'];
		$term = $data['term'];
		
		
		// create empty array
		if( !isset($terms[ $taxonomy ]) ) {
			
			$terms[ $taxonomy ] = array();
			
		}
				
		
		// append
		$terms[ $taxonomy ][] = $term;
		
	}
	
	
	// return
	return $terms;
	
}


/*
*  acf_decode_taxonomy_term
*
*  This function will return the taxonomy and term slug for a given value
*
*  @type	function
*  @date	31/03/2014
*  @since	5.0.0
*
*  @param	$string (string)
*  @return	(array)
*/

function acf_decode_taxonomy_term( $value ) {
	
	// vars
	$data = array(
		'taxonomy'	=> '',
		'term'		=> ''
	);
	
	
	// int
	if( is_numeric($value) ) {
		
		$data['term'] = $value;
			
	// string
	} elseif( is_string($value) ) {
		
		$value = explode(':', $value);
		$data['taxonomy'] = isset($value[0]) ? $value[0] : '';
		$data['term'] = isset($value[1]) ? $value[1] : '';
		
	// error	
	} else {
		
		return false;
		
	}
	
	
	// allow for term_id (Used by ACF v4)
	if( is_numeric($data['term']) ) {
		
		// global
		global $wpdb;
		
		
		// find taxonomy
		if( !$data['taxonomy'] ) {
			
			$data['taxonomy'] = $wpdb->get_var( $wpdb->prepare("SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d LIMIT 1", $data['term']) );
			
		}
		
		
		// find term (may have numeric slug '123')
		$term = get_term_by( 'slug', $data['term'], $data['taxonomy'] );
		
		
		// attempt get term via ID (ACF4 uses ID)
		if( !$term ) $term = get_term( $data['term'], $data['taxonomy'] );
		
		
		// bail early if no term
		if( !$term ) return false;
		
		
		// update
		$data['taxonomy'] = $term->taxonomy;
		$data['term'] = $term->slug;
		
	}
	
	
	// return
	return $data;
	
}


/*
*  acf_get_array
*
*  This function will force a variable to become an array
*
*  @type	function
*  @date	4/02/2014
*  @since	5.0.0
*
*  @param	$var (mixed)
*  @return	(array)
*/

function acf_get_array( $var = false, $delimiter = '' ) {
	
	// array
	if( is_array($var) ) {
		return $var;
	}
	
	
	// bail early if empty
	if( acf_is_empty($var) ) {
		return array();
	}
	
	
	// string 
	if( is_string($var) && $delimiter ) {
		return explode($delimiter, $var);
	}
	
	
	// place in array
	return (array) $var;
	
}


/*
*  acf_get_numeric
*
*  This function will return numeric values
*
*  @type	function
*  @date	15/07/2016
*  @since	5.4.0
*
*  @param	$value (mixed)
*  @return	(mixed)
*/

function acf_get_numeric( $value = '' ) {
	
	// vars
	$numbers = array();
	$is_array = is_array($value);
	
	
	// loop
	foreach( (array) $value as $v ) {
		
		if( is_numeric($v) ) $numbers[] = (int) $v;
		
	}
	
	
	// bail early if is empty
	if( empty($numbers) ) return false;
	
	
	// convert array
	if( !$is_array ) $numbers = $numbers[0];
	
	
	// return
	return $numbers;
	
}


/*
*  acf_get_posts
*
*  This function will return an array of posts making sure the order is correct
*
*  @type	function
*  @date	3/03/2015
*  @since	5.1.5
*
*  @param	$args (array)
*  @return	(array)
*/

function acf_get_posts( $args = array() ) {
	
	// vars
	$posts = array();
	
	
	// defaults
	// leave suppress_filters as true becuase we don't want any plugins to modify the query as we know exactly what 
	$args = wp_parse_args( $args, array(
		'posts_per_page'			=> -1,
		'post_type'					=> '',
		'post_status'				=> 'any',
		'update_post_meta_cache'	=> false,
		'update_post_term_cache' 	=> false
	));
	

	// post type
	if( empty($args['post_type']) ) {
		
		$args['post_type'] = acf_get_post_types();
		
	}
	
	
	// validate post__in
	if( $args['post__in'] ) {
		
		// force value to array
		$args['post__in'] = acf_get_array( $args['post__in'] );
		
		
		// convert to int
		$args['post__in'] = array_map('intval', $args['post__in']);
		
		
		// add filter to remove post_type
		// use 'query' filter so that 'suppress_filters' can remain true
		//add_filter('query', '_acf_query_remove_post_type');
		
		
		// order by post__in
		$args['orderby'] = 'post__in';
		
	}
	
	
	// load posts in 1 query to save multiple DB calls from following code
	$posts = get_posts($args);
	
	
	// remove this filter (only once)
	//remove_filter('query', '_acf_query_remove_post_type');
	
	
	// validate order
	if( $posts && $args['post__in'] ) {
		
		// vars
		$order = array();
		
		
		// generate sort order
		foreach( $posts as $i => $post ) {
			
			$order[ $i ] = array_search($post->ID, $args['post__in']);
			
		}
		
		
		// sort
		array_multisort($order, $posts);
			
	}
	
	
	// return
	return $posts;
	
}


/*
*  _acf_query_remove_post_type
*
*  This function will remove the 'wp_posts.post_type' WHERE clause completely
*  When using 'post__in', this clause is unneccessary and slow.
*
*  @type	function
*  @date	4/03/2015
*  @since	5.1.5
*
*  @param	$sql (string)
*  @return	$sql
*/

function _acf_query_remove_post_type( $sql ) {
	
	// global
	global $wpdb;
	
	
	// bail ealry if no 'wp_posts.ID IN'
	if( strpos($sql, "$wpdb->posts.ID IN") === false ) {
		
		return $sql;
		
	}
	
    
    // get bits
	$glue = 'AND';
	$bits = explode($glue, $sql);
	
    
	// loop through $where and remove any post_type queries
	foreach( $bits as $i => $bit ) {
		
		if( strpos($bit, "$wpdb->posts.post_type") !== false ) {
			
			unset( $bits[ $i ] );
			
		}
		
	}
	
	
	// join $where back together
	$sql = implode($glue, $bits);
    
    
    // return
    return $sql;
    
}


/*
*  acf_get_grouped_posts
*
*  This function will return all posts grouped by post_type
*  This is handy for select settings
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$args (array)
*  @return	(array)
*/

function acf_get_grouped_posts( $args ) {
	
	// vars
	$data = array();
	
	
	// defaults
	$args = wp_parse_args( $args, array(
		'posts_per_page'			=> -1,
		'paged'						=> 0,
		'post_type'					=> 'post',
		'orderby'					=> 'menu_order title',
		'order'						=> 'ASC',
		'post_status'				=> 'any',
		'suppress_filters'			=> false,
		'update_post_meta_cache'	=> false,
	));

	
	// find array of post_type
	$post_types = acf_get_array( $args['post_type'] );
	$post_types_labels = acf_get_pretty_post_types($post_types);
	$is_single_post_type = ( count($post_types) == 1 );
	
	
	// attachment doesn't work if it is the only item in an array
	if( $is_single_post_type ) {
		$args['post_type'] = reset($post_types);
	}
	
	
	// add filter to orderby post type
	if( !$is_single_post_type ) {
		add_filter('posts_orderby', '_acf_orderby_post_type', 10, 2);
	}
	
	
	// get posts
	$posts = get_posts( $args );
	
	
	// remove this filter (only once)
	if( !$is_single_post_type ) {
		remove_filter('posts_orderby', '_acf_orderby_post_type', 10, 2);
	}
	
	
	// loop
	foreach( $post_types as $post_type ) {
		
		// vars
		$this_posts = array();
		$this_group = array();
		
		
		// populate $this_posts
		foreach( $posts as $post ) {
			if( $post->post_type == $post_type ) {
				$this_posts[] = $post;
			}
		}
		
		
		// bail early if no posts for this post type
		if( empty($this_posts) ) continue;
		
		
		// sort into hierachial order!
		// this will fail if a search has taken place because parents wont exist
		if( is_post_type_hierarchical($post_type) && empty($args['s'])) {
			
			// vars
			$post_id = $this_posts[0]->ID;
			$parent_id = acf_maybe_get($args, 'post_parent', 0);
			$offset = 0;
			$length = count($this_posts);
			
			
			// get all posts from this post type
			$all_posts = get_posts(array_merge($args, array(
				'posts_per_page'	=> -1,
				'paged'				=> 0,
				'post_type'			=> $post_type
			)));
			
			
			// find starting point (offset)
			foreach( $all_posts as $i => $post ) {
				if( $post->ID == $post_id ) {
					$offset = $i;
					break;
				}
			}
			
			
			// order posts
			$ordered_posts = get_page_children($parent_id, $all_posts);
			
			
			// compare aray lengths
			// if $ordered_posts is smaller than $all_posts, WP has lost posts during the get_page_children() function
			// this is possible when get_post( $args ) filter out parents (via taxonomy, meta and other search parameters) 
			if( count($ordered_posts) == count($all_posts) ) {
				$this_posts = array_slice($ordered_posts, $offset, $length);
			}
			
		}
		
		
		// populate $this_posts
		foreach( $this_posts as $post ) {
			$this_group[ $post->ID ] = $post;
		}
		
		
		// group by post type
		$label = $post_types_labels[ $post_type ];
		$data[ $label ] = $this_group;
					
	}
	
	
	// return
	return $data;
	
}


function _acf_orderby_post_type( $ordeby, $wp_query ) {
	
	// global
	global $wpdb;
	
	
	// get post types
	$post_types = $wp_query->get('post_type');
	

	// prepend SQL
	if( is_array($post_types) ) {
		
		$post_types = implode("','", $post_types);
		$ordeby = "FIELD({$wpdb->posts}.post_type,'$post_types')," . $ordeby;
		
	}
	
	
	// return
	return $ordeby;
	
}


function acf_get_post_title( $post = 0, $is_search = false ) {
	
	// vars
	$post = get_post($post);
	$title = '';
	$prepend = '';
	$append = '';
	
    
	// bail early if no post
	if( !$post ) return '';
	
	
	// title
	$title = get_the_title( $post->ID );
	
	
	// empty
	if( $title === '' ) {
		
		$title = __('(no title)', 'acf');
		
	}
	
	
	// status
	if( get_post_status( $post->ID ) != "publish" ) {
		
		$append .= ' (' . get_post_status( $post->ID ) . ')';
		
	}
	
	
	// ancestors
	if( $post->post_type !== 'attachment' ) {
		
		// get ancestors
		$ancestors = get_ancestors( $post->ID, $post->post_type );
		$prepend .= str_repeat('- ', count($ancestors));
		
		
		// add parent
/*
		removed in 5.6.5 as not used by the UI
		if( $is_search && !empty($ancestors) ) {
			
			// reverse
			$ancestors = array_reverse($ancestors);
			
			
			// convert id's into titles
			foreach( $ancestors as $i => $id ) {
				
				$ancestors[ $i ] = get_the_title( $id );
				
			}
			
			
			// append
			$append .= ' | ' . __('Parent', 'acf') . ': ' . implode(' / ', $ancestors);
			
		}
*/
		
	}
	
	
	// merge
	$title = $prepend . $title . $append;
	
	
	// return
	return $title;
	
}


function acf_order_by_search( $array, $search ) {
	
	// vars
	$weights = array();
	$needle = strtolower( $search );
	
	
	// add key prefix
	foreach( array_keys($array) as $k ) {
		
		$array[ '_' . $k ] = acf_extract_var( $array, $k );
		
	}


	// add search weight
	foreach( $array as $k => $v ) {
	
		// vars
		$weight = 0;
		$haystack = strtolower( $v );
		$strpos = strpos( $haystack, $needle );
		
		
		// detect search match
		if( $strpos !== false ) {
			
			// set eright to length of match
			$weight = strlen( $search );
			
			
			// increase weight if match starts at begining of string
			if( $strpos == 0 ) {
				
				$weight++;
				
			}
			
		}
		
		
		// append to wights
		$weights[ $k ] = $weight;
		
	}
	
	
	// sort the array with menu_order ascending
	array_multisort( $weights, SORT_DESC, $array );
	
	
	// remove key prefix
	foreach( array_keys($array) as $k ) {
		
		$array[ substr($k,1) ] = acf_extract_var( $array, $k );
		
	}
		
	
	// return
	return $array;
}


/*
*  acf_get_pretty_user_roles
*
*  description
*
*  @type	function
*  @date	23/02/2016
*  @since	5.3.2
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_pretty_user_roles( $allowed = false ) {
	
	// vars
	$editable_roles = get_editable_roles();
	$allowed = acf_get_array($allowed);
	$roles = array();
	
	
	// loop
	foreach( $editable_roles as $role_name => $role_details ) {	
		
		// bail early if not allowed
		if( !empty($allowed) && !in_array($role_name, $allowed) ) continue;
		
		
		// append
		$roles[ $role_name ] = translate_user_role( $role_details['name'] );
		
	}
	
	
	// return
	return $roles;
		
}


/*
*  acf_get_grouped_users
*
*  This function will return all users grouped by role
*  This is handy for select settings
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$args (array)
*  @return	(array)
*/

function acf_get_grouped_users( $args = array() ) {
	
	// vars
	$r = array();
	
	
	// defaults
	$args = wp_parse_args( $args, array(
		'users_per_page'			=> -1,
		'paged'						=> 0,
		'role'         				=> '',
		'orderby'					=> 'login',
		'order'						=> 'ASC',
	));
	
	
	// offset
	$i = 0;
	$min = 0;
	$max = 0;
	$users_per_page = acf_extract_var($args, 'users_per_page');
	$paged = acf_extract_var($args, 'paged');
	
	if( $users_per_page > 0 ) {
		
		// prevent paged from being -1
		$paged = max(0, $paged);
		
		
		// set min / max
		$min = (($paged-1) * $users_per_page) + 1; // 	1, 	11
		$max = ($paged * $users_per_page); // 			10,	20
		
	}
	
	
	// find array of post_type
	$user_roles = acf_get_pretty_user_roles($args['role']);
	
	
	// fix role
	if( is_array($args['role']) ) {
		
		// global
   		global $wp_version, $wpdb;
   		
   		
		// vars
		$roles = acf_extract_var($args, 'role');
		
		
		// new WP has role__in
		if( version_compare($wp_version, '4.4', '>=' ) ) {
			
			$args['role__in'] = $roles;
				
		// old WP doesn't have role__in
		} else {
			
			// vars
			$blog_id = get_current_blog_id();
			$meta_query = array( 'relation' => 'OR' );
			
			
			// loop
			foreach( $roles as $role ) {
				
				$meta_query[] = array(
					'key'     => $wpdb->get_blog_prefix( $blog_id ) . 'capabilities',
					'value'   => '"' . $role . '"',
					'compare' => 'LIKE',
				);
				
			}
			
			
			// append
			$args['meta_query'] = $meta_query;
			
		}
		
	}
	
	
	// get posts
	$users = get_users( $args );
	
	
	// loop
	foreach( $user_roles as $user_role_name => $user_role_label ) {
		
		// vars
		$this_users = array();
		$this_group = array();
		
		
		// populate $this_posts
		foreach( array_keys($users) as $key ) {
			
			// bail ealry if not correct role
			if( !in_array($user_role_name, $users[ $key ]->roles) ) continue;
		
			
			// extract user
			$user = acf_extract_var( $users, $key );
			
			
			// increase
			$i++;
			
			
			// bail ealry if too low
			if( $min && $i < $min ) continue;
			
			
			// bail early if too high (don't bother looking at any more users)
			if( $max && $i > $max ) break;
			
			
			// group by post type
			$this_users[ $user->ID ] = $user;
			
			
		}
		
		
		// bail early if no posts for this post type
		if( empty($this_users) ) continue;
		
		
		// append
		$r[ $user_role_label ] = $this_users;
					
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_json_encode
*
*  This function will return pretty JSON for all PHP versions
*
*  @type	function
*  @date	6/03/2014
*  @since	5.0.0
*
*  @param	$json (array)
*  @return	(string)
*/

function acf_json_encode( $json ) {
	
	// PHP at least 5.4
	if( version_compare(PHP_VERSION, '5.4.0', '>=') ) {
		
		return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		
	}

	
	
	// PHP less than 5.4
	$json = json_encode($json);
	
	
	// http://snipplr.com/view.php?codeview&id=60559
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = "    ";
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;
		
		// If this character is ':' adda space after it
        if($char == ':' && $outOfQuotes) {
            $result .= ' ';
        }
        
        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }
	
	
	// return
    return $result;
	
}


/*
*  acf_str_exists
*
*  This function will return true if a sub string is found
*
*  @type	function
*  @date	1/05/2014
*  @since	5.0.0
*
*  @param	$needle (string)
*  @param	$haystack (string)
*  @return	(boolean)
*/

function acf_str_exists( $needle, $haystack ) {
	
	// return true if $haystack contains the $needle
	if( is_string($haystack) && strpos($haystack, $needle) !== false ) {
		
		return true;
		
	}
	
	
	// return
	return false;
}


/*
*  acf_debug
*
*  description
*
*  @type	function
*  @date	2/05/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_debug() {
	
	// vars
	$args = func_get_args();
	$s = array_shift($args);
	$o = '';
	$nl = "\r\n";
	
	
	// start script
	$o .= '<script type="text/javascript">' . $nl;
	
	$o .= 'console.log("' . $s . '"';
	
	if( !empty($args) ) {
		
		foreach( $args as $arg ) {
			
			if( is_object($arg) || is_array($arg) ) {
				
				$arg = json_encode($arg);
				
			} elseif( is_bool($arg) ) {
				
				$arg = $arg ? 'true' : 'false';
				
			}elseif( is_string($arg) ) {
				
				$arg = '"' . $arg . '"';
				
			}
			
			$o .= ', ' . $arg;
			
		}
	}
	
	$o .= ');' . $nl;
	
	
	// end script
	$o .= '</script>' . $nl;
	
	
	// echo
	echo $o;
}

function acf_debug_start() {
	
	acf_update_setting( 'debug_start', memory_get_usage());
	
}

function acf_debug_end() {
	
	$start = acf_get_setting( 'debug_start' );
	$end = memory_get_usage();
	
	return $end - $start;
	
}


/*
*  acf_encode_choices
*
*  description
*
*  @type	function
*  @date	4/06/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_encode_choices( $array = array(), $show_keys = true ) {
	
	// bail early if not array (maybe a single string)
	if( !is_array($array) ) return $array;
	
	
	// bail early if empty array
	if( empty($array) ) return '';
	
	
	// vars
	$string = '';
	
	
	// if allowed to show keys (good for choices, not for default values)
	if( $show_keys ) {
		
		// loop
		foreach( $array as $k => $v ) { 
			
			// ignore if key and value are the same
			if( strval($k) == strval($v) )  continue;
			
			
			// show key in the value
			$array[ $k ] = $k . ' : ' . $v;
			
		}
	
	}
	
	
	// implode
	$string = implode("\n", $array);

	
	// return
	return $string;
	
}

function acf_decode_choices( $string = '', $array_keys = false ) {
	
	// bail early if already array
	if( is_array($string) ) {
		
		return $string;
	
	// allow numeric values (same as string)
	} elseif( is_numeric($string) ) {
		
		// do nothing
	
	// bail early if not a string
	} elseif( !is_string($string) ) {
		
		return array();
	
	// bail early if is empty string 
	} elseif( $string === '' ) {
		
		return array();
		
	}
	
	
	// vars
	$array = array();
	
	
	// explode
	$lines = explode("\n", $string);
	
	
	// key => value
	foreach( $lines as $line ) {
		
		// vars
		$k = trim($line);
		$v = trim($line);
		
		
		// look for ' : '
		if( acf_str_exists(' : ', $line) ) {
		
			$line = explode(' : ', $line);
			
			$k = trim($line[0]);
			$v = trim($line[1]);
			
		}
		
		
		// append
		$array[ $k ] = $v;
		
	}
	
	
	// return only array keys? (good for checkbox default_value)
	if( $array_keys ) {
		
		return array_keys($array);
		
	}
	
	
	// return
	return $array;
	
}


/*
*  acf_str_replace
*
*  This function will replace an array of strings much like str_replace
*  The difference is the extra logic to avoid replacing a string that has alread been replaced
*  This is very useful for replacing date characters as they overlap with eachother
*
*  @type	function
*  @date	21/06/2016
*  @since	5.3.8
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_str_replace( $string = '', $search_replace = array() ) {
	
	// vars
	$ignore = array();
	
	
	// remove potential empty search to avoid PHP error
	unset($search_replace['']);
	
		
	// loop over conversions
	foreach( $search_replace as $search => $replace ) {
		
		// ignore this search, it was a previous replace
		if( in_array($search, $ignore) ) continue;
		
		
		// bail early if subsctring not found
		if( strpos($string, $search) === false ) continue;
		
		
		// replace
		$string = str_replace($search, $replace, $string);
		
		
		// append to ignore
		$ignore[] = $replace;
		
	}
	
	
	// return
	return $string;
	
}


/*
*  date & time formats
*
*  These settings contain an association of format strings from PHP => JS
*
*  @type	function
*  @date	21/06/2016
*  @since	5.3.8
*
*  @param	n/a
*  @return	n/a
*/

acf_update_setting('php_to_js_date_formats', array(

	// Year
	'Y'	=> 'yy',	// Numeric, 4 digits 								1999, 2003
	'y'	=> 'y',		// Numeric, 2 digits 								99, 03
	
	
	// Month
	'm'	=> 'mm',	// Numeric, with leading zeros  					01–12
	'n'	=> 'm',		// Numeric, without leading zeros  					1–12
	'F'	=> 'MM',	// Textual full   									January – December
	'M'	=> 'M',		// Textual three letters    						Jan - Dec 
	
	
	// Weekday
	'l'	=> 'DD',	// Full name  (lowercase 'L') 						Sunday – Saturday
	'D'	=> 'D',		// Three letter name 	 							Mon – Sun 
	
	
	// Day of Month
	'd'	=> 'dd',	// Numeric, with leading zeros						01–31
	'j'	=> 'd',		// Numeric, without leading zeros 					1–31
	'S'	=> '',		// The English suffix for the day of the month  	st, nd or th in the 1st, 2nd or 15th. 
	
));

acf_update_setting('php_to_js_time_formats', array(
	
	'a' => 'tt',	// Lowercase Ante meridiem and Post meridiem 		am or pm
	'A' => 'TT',	// Uppercase Ante meridiem and Post meridiem 		AM or PM
	'h' => 'hh',	// 12-hour format of an hour with leading zeros 	01 through 12
	'g' => 'h',		// 12-hour format of an hour without leading zeros 	1 through 12
	'H' => 'HH',	// 24-hour format of an hour with leading zeros 	00 through 23
	'G' => 'H',		// 24-hour format of an hour without leading zeros 	0 through 23
	'i' => 'mm',	// Minutes with leading zeros 						00 to 59
	's' => 'ss',	// Seconds, with leading zeros 						00 through 59
	
));


/*
*  acf_split_date_time
*
*  This function will split a format string into seperate date and time
*
*  @type	function
*  @date	26/05/2016
*  @since	5.3.8
*
*  @param	$date_time (string)
*  @return	$formats (array)
*/

function acf_split_date_time( $date_time = '' ) {
	
	// vars
	$php_date = acf_get_setting('php_to_js_date_formats');
	$php_time = acf_get_setting('php_to_js_time_formats');
	$chars = str_split($date_time);
	$type = 'date';
	
	
	// default
	$data = array(
		'date' => '',
		'time' => ''
	);
	
	
	// loop
	foreach( $chars as $i => $c ) {
		
		// find type
		// - allow misc characters to append to previous type
		if( isset($php_date[ $c ]) ) {
			
			$type = 'date';
			
		} elseif( isset($php_time[ $c ]) ) {
			
			$type = 'time';
			
		}
		
		
		// append char
		$data[ $type ] .= $c;
		
	}
	
	
	// trim
	$data['date'] = trim($data['date']);
	$data['time'] = trim($data['time']);
	
	
	// return
	return $data;	
	
}


/*
*  acf_convert_date_to_php
*
*  This fucntion converts a date format string from JS to PHP
*
*  @type	function
*  @date	20/06/2014
*  @since	5.0.0
*
*  @param	$date (string)
*  @return	(string)
*/

function acf_convert_date_to_php( $date = '' ) {
	
	// vars
	$php_to_js = acf_get_setting('php_to_js_date_formats');
	$js_to_php = array_flip($php_to_js);
		
	
	// return
	return acf_str_replace( $date, $js_to_php );
	
}

/*
*  acf_convert_date_to_js
*
*  This fucntion converts a date format string from PHP to JS
*
*  @type	function
*  @date	20/06/2014
*  @since	5.0.0
*
*  @param	$date (string)
*  @return	(string)
*/

function acf_convert_date_to_js( $date = '' ) {
	
	// vars
	$php_to_js = acf_get_setting('php_to_js_date_formats');
		
	
	// return
	return acf_str_replace( $date, $php_to_js );
	
}


/*
*  acf_convert_time_to_php
*
*  This fucntion converts a time format string from JS to PHP
*
*  @type	function
*  @date	20/06/2014
*  @since	5.0.0
*
*  @param	$time (string)
*  @return	(string)
*/

function acf_convert_time_to_php( $time = '' ) {
	
	// vars
	$php_to_js = acf_get_setting('php_to_js_time_formats');
	$js_to_php = array_flip($php_to_js);
		
	
	// return
	return acf_str_replace( $time, $js_to_php );
	
}


/*
*  acf_convert_time_to_js
*
*  This fucntion converts a date format string from PHP to JS
*
*  @type	function
*  @date	20/06/2014
*  @since	5.0.0
*
*  @param	$time (string)
*  @return	(string)
*/

function acf_convert_time_to_js( $time = '' ) {
	
	// vars
	$php_to_js = acf_get_setting('php_to_js_time_formats');
		
	
	// return
	return acf_str_replace( $time, $php_to_js );
	
}


/*
*  acf_update_user_setting
*
*  description
*
*  @type	function
*  @date	15/07/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_update_user_setting( $name, $value ) {
	
	// get current user id
	$user_id = get_current_user_id();
	
	
	// get user settings
	$settings = get_user_meta( $user_id, 'acf_user_settings', true );
	
	
	// ensure array
	$settings = acf_get_array($settings);
	
	
	// delete setting (allow 0 to save)
	if( acf_is_empty($value) ) {
		
		unset($settings[ $name ]);
	
	// append setting	
	} else {
		
		$settings[ $name ] = $value;
		
	}
	
	
	// update user data
	return update_metadata('user', $user_id, 'acf_user_settings', $settings);
	
}


/*
*  acf_get_user_setting
*
*  description
*
*  @type	function
*  @date	15/07/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_user_setting( $name = '', $default = false ) {
	
	// get current user id
	$user_id = get_current_user_id();
	
	
	// get user settings
	$settings = get_user_meta( $user_id, 'acf_user_settings', true );
	
	
	// ensure array
	$settings = acf_get_array($settings);
	
	
	// bail arly if no settings
	if( !isset($settings[$name]) ) return $default;
	
	
	// return
	return $settings[$name];
	
}


/*
*  acf_in_array
*
*  description
*
*  @type	function
*  @date	22/07/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_in_array( $value = '', $array = false ) {
	
	// bail early if not array
	if( !is_array($array) ) return false;
	
	
	// find value in array
	return in_array($value, $array);
	
}


/*
*  acf_get_valid_post_id
*
*  This function will return a valid post_id based on the current screen / parameter
*
*  @type	function
*  @date	8/12/2013
*  @since	5.0.0
*
*  @param	$post_id (mixed)
*  @return	$post_id (mixed)
*/

function acf_get_valid_post_id( $post_id = 0 ) {
	
	// vars
	$_post_id = $post_id;
	
	
	// if not $post_id, load queried object
	if( !$post_id ) {
		
		// try for global post (needed for setup_postdata)
		$post_id = (int) get_the_ID();
		
		
		// try for current screen
		if( !$post_id ) {
			
			$post_id = get_queried_object();
				
		}
		
	}
	
	
	// $post_id may be an object
	if( is_object($post_id) ) {
		
		// post
		if( isset($post_id->post_type, $post_id->ID) ) {
		
			$post_id = $post_id->ID;
			
		// user
		} elseif( isset($post_id->roles, $post_id->ID) ) {
		
			$post_id = 'user_' . $post_id->ID;
		
		// term
		} elseif( isset($post_id->taxonomy, $post_id->term_id) ) {
			
			$post_id = acf_get_term_post_id( $post_id->taxonomy, $post_id->term_id );
		
		// comment
		} elseif( isset($post_id->comment_ID) ) {
		
			$post_id = 'comment_' . $post_id->comment_ID;
		
		// default
		} else {
			
			$post_id = 0;
			
		}
		
	}
	
	
	// allow for option == options
	if( $post_id === 'option' ) {
	
		$post_id = 'options';
		
	}
	
	
	// append language code
	if( $post_id == 'options' ) {
		
		$dl = acf_get_setting('default_language');
		$cl = acf_get_setting('current_language');
		
		if( $cl && $cl !== $dl ) {
			
			$post_id .= '_' . $cl;
			
		}
			
	}
	
	
	
	// filter for 3rd party
	$post_id = apply_filters('acf/validate_post_id', $post_id, $_post_id);
	
	
	// return
	return $post_id;
	
}



/*
*  acf_get_post_id_info
*
*  This function will return the type and id for a given $post_id string
*
*  @type	function
*  @date	2/07/2016
*  @since	5.4.0
*
*  @param	$post_id (mixed)
*  @return	$info (array)
*/

function acf_get_post_id_info( $post_id = 0 ) {
	
	// vars
	$info = array(
		'type'	=> 'post',
		'id'	=> 0
	);
	
	// bail early if no $post_id
	if( !$post_id ) return $info;
	
	
	// check cache
	// - this function will most likely be called multiple times (saving loading fields from post)
	//$cache_key = "get_post_id_info/post_id={$post_id}";
	
	//if( acf_isset_cache($cache_key) ) return acf_get_cache($cache_key);
	
	
	// numeric
	if( is_numeric($post_id) ) {
		
		$info['id'] = (int) $post_id;
	
	// string
	} elseif( is_string($post_id) ) {
		
		// vars
		$glue = '_';
		$type = explode($glue, $post_id);
		$id = array_pop($type);
		$type = implode($glue, $type);
		$meta = array('post', 'user', 'comment', 'term');
		
		
		// check if is taxonomy (ACF < 5.5)
		// - avoid scenario where taxonomy exists with name of meta type
		if( !in_array($type, $meta) && acf_isset_termmeta($type) ) $type = 'term';
		
		
		// meta
		if( is_numeric($id) && in_array($type, $meta) ) {
			
			$info['type'] = $type;
			$info['id'] = (int) $id;
		
		// option	
		} else {
			
			$info['type'] = 'option';
			$info['id'] = $post_id;
			
		}
		
	}
	
	
	// update cache
	//acf_set_cache($cache_key, $info);
	
	
	// filter
	$info = apply_filters("acf/get_post_id_info", $info, $post_id);
	
	// return
	return $info;
	
}


/*

acf_log( acf_get_post_id_info(4) );

acf_log( acf_get_post_id_info('post_4') );

acf_log( acf_get_post_id_info('user_123') );

acf_log( acf_get_post_id_info('term_567') );

acf_log( acf_get_post_id_info('category_204') );

acf_log( acf_get_post_id_info('comment_6') );

acf_log( acf_get_post_id_info('options_lol!') );

acf_log( acf_get_post_id_info('option') );

acf_log( acf_get_post_id_info('options') );

*/


/*
*  acf_isset_termmeta
*
*  This function will return true if the termmeta table exists
*  https://developer.wordpress.org/reference/functions/get_term_meta/
*
*  @type	function
*  @date	3/09/2016
*  @since	5.4.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_isset_termmeta( $taxonomy = '' ) {
	
	// bail ealry if no table
	if( get_option('db_version') < 34370 ) return false;
	
	
	// check taxonomy
	if( $taxonomy && !taxonomy_exists($taxonomy) ) return false;
	
	
	// return
	return true;
		
}


/*
*  acf_get_term_post_id
*
*  This function will return a valid post_id string for a given term and taxonomy
*
*  @type	function
*  @date	6/2/17
*  @since	5.5.6
*
*  @param	$taxonomy (string)
*  @param	$term_id (int)
*  @return	(string)
*/

function acf_get_term_post_id( $taxonomy, $term_id ) {
	
	// WP < 4.4
	if( !acf_isset_termmeta() ) {
		
		return $taxonomy . '_' . $term_id;
		
	}
	
	
	// return
	return 'term_' . $term_id;
	
}


/*
*  acf_upload_files
*
*  This function will walk througfh the $_FILES data and upload each found
*
*  @type	function
*  @date	25/10/2014
*  @since	5.0.9
*
*  @param	$ancestors (array) an internal parameter, not required
*  @return	n/a
*/
	
function acf_upload_files( $ancestors = array() ) {
	
	// vars
	$file = array(
		'name'		=> '',
		'type'		=> '',
		'tmp_name'	=> '',
		'error'		=> '',
		'size' 		=> ''
	);
	
	
	// populate with $_FILES data
	foreach( array_keys($file) as $k ) {
		
		$file[ $k ] = $_FILES['acf'][ $k ];
		
	}
	
	
	// walk through ancestors
	if( !empty($ancestors) ) {
		
		foreach( $ancestors as $a ) {
			
			foreach( array_keys($file) as $k ) {
				
				$file[ $k ] = $file[ $k ][ $a ];
				
			}
			
		}
		
	}
	
	
	// is array?
	if( is_array($file['name']) ) {
		
		foreach( array_keys($file['name']) as $k ) {
				
			$_ancestors = array_merge($ancestors, array($k));
			
			acf_upload_files( $_ancestors );
			
		}
		
		return;
		
	}
	
	
	// bail ealry if file has error (no file uploaded)
	if( $file['error'] ) {
		
		return;
		
	}
	
	
	// assign global _acfuploader for media validation
	$_POST['_acfuploader'] = end($ancestors);
	
	
	// file found!
	$attachment_id = acf_upload_file( $file );
	
	
	// update $_POST
	array_unshift($ancestors, 'acf');
	acf_update_nested_array( $_POST, $ancestors, $attachment_id );
	
}


/*
*  acf_upload_file
*
*  This function will uploade a $_FILE
*
*  @type	function
*  @date	27/10/2014
*  @since	5.0.9
*
*  @param	$uploaded_file (array) array found from $_FILE data
*  @return	$id (int) new attachment ID
*/

function acf_upload_file( $uploaded_file ) {
	
	// required
	//require_once( ABSPATH . "/wp-load.php" ); // WP should already be loaded
	require_once( ABSPATH . "/wp-admin/includes/media.php" ); // video functions
	require_once( ABSPATH . "/wp-admin/includes/file.php" );
	require_once( ABSPATH . "/wp-admin/includes/image.php" );
	 
	 
	// required for wp_handle_upload() to upload the file
	$upload_overrides = array( 'test_form' => false );
	
	
	// upload
	$file = wp_handle_upload( $uploaded_file, $upload_overrides );
	
	
	// bail ealry if upload failed
	if( isset($file['error']) ) {
		
		return $file['error'];
		
	}
	
	
	// vars
	$url = $file['url'];
	$type = $file['type'];
	$file = $file['file'];
	$filename = basename($file);
	

	// Construct the object array
	$object = array(
		'post_title' => $filename,
		'post_mime_type' => $type,
		'guid' => $url
	);

	// Save the data
	$id = wp_insert_attachment($object, $file);

	// Add the meta-data
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	
	/** This action is documented in wp-admin/custom-header.php */
	do_action( 'wp_create_file_in_uploads', $file, $id ); // For replication
	
	// return new ID
	return $id;
	
}


/*
*  acf_update_nested_array
*
*  This function will update a nested array value. Useful for modifying the $_POST array
*
*  @type	function
*  @date	27/10/2014
*  @since	5.0.9
*
*  @param	$array (array) target array to be updated
*  @param	$ancestors (array) array of keys to navigate through to find the child
*  @param	$value (mixed) The new value
*  @return	(boolean)
*/

function acf_update_nested_array( &$array, $ancestors, $value ) {
	
	// if no more ancestors, update the current var
	if( empty($ancestors) ) {
		
		$array = $value;
		
		// return
		return true;
		
	}
	
	
	// shift the next ancestor from the array
	$k = array_shift( $ancestors );
	
	
	// if exists
	if( isset($array[ $k ]) ) {
		
		return acf_update_nested_array( $array[ $k ], $ancestors, $value );
		
	}
		
	
	// return 
	return false;
}


/*
*  acf_is_screen
*
*  This function will return true if all args are matched for the current screen
*
*  @type	function
*  @date	9/12/2014
*  @since	5.1.5
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_is_screen( $id = '' ) {
	
	// bail early if not defined
	if( !function_exists('get_current_screen') ) {
		return false;
	}
	
	// vars
	$current_screen = get_current_screen();
	
	// no screen
	if( !$current_screen ) {
		return false;
	
	// array
	} elseif( is_array($id) ) {
		return in_array($current_screen->id, $id);
	
	// string
	} else {
		return ($id === $current_screen->id);
	}
}


/*
*  acf_maybe_get
*
*  This function will return a var if it exists in an array
*
*  @type	function
*  @date	9/12/2014
*  @since	5.1.5
*
*  @param	$array (array) the array to look within
*  @param	$key (key) the array key to look for. Nested values may be found using '/'
*  @param	$default (mixed) the value returned if not found
*  @return	$post_id (int)
*/

function acf_maybe_get( $array = array(), $key = 0, $default = null ) {
	
	return isset( $array[$key] ) ? $array[$key] : $default;
		
}

function acf_maybe_get_POST( $key = '', $default = null ) {
	
	return isset( $_POST[$key] ) ? $_POST[$key] : $default;
	
}

function acf_maybe_get_GET( $key = '', $default = null ) {
	
	return isset( $_GET[$key] ) ? $_GET[$key] : $default;
	
}


/*
*  acf_get_attachment
*
*  This function will return an array of attachment data
*
*  @type	function
*  @date	5/01/2015
*  @since	5.1.5
*
*  @param	$post (mixed) either post ID or post object
*  @return	(array)
*/

function acf_get_attachment( $attachment ) {
	
	// get post
	if( !$attachment = get_post($attachment) ) {
		return false;
	}
	
	// validate post_type
	if( $attachment->post_type !== 'attachment' ) {
		return false;
	}
	
	// vars
	$sizes_id = 0;
	$meta = wp_get_attachment_metadata( $attachment->ID );
	$attached_file = get_attached_file( $attachment->ID );
	$attachment_url = wp_get_attachment_url( $attachment->ID );
	
	// get mime types
	if( strpos( $attachment->post_mime_type, '/' ) !== false ) {
		list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
	} else {
		list( $type, $subtype ) = array( $attachment->post_mime_type, '' );
	}
	
	// vars
	$response = array(
		'ID'			=> $attachment->ID,
		'id'			=> $attachment->ID,
		'title'       	=> $attachment->post_title,
		'filename'		=> wp_basename( $attached_file ),
		'filesize'		=> 0,
		'url'			=> $attachment_url,
		'link'			=> get_attachment_link( $attachment->ID ),
		'alt'			=> get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
		'author'		=> $attachment->post_author,
		'description'	=> $attachment->post_content,
		'caption'		=> $attachment->post_excerpt,
		'name'			=> $attachment->post_name,
        'status'		=> $attachment->post_status,
        'uploaded_to'	=> $attachment->post_parent,
        'date'			=> $attachment->post_date_gmt,
		'modified'		=> $attachment->post_modified_gmt,
		'menu_order'	=> $attachment->menu_order,
		'mime_type'		=> $attachment->post_mime_type,
        'type'			=> $type,
        'subtype'		=> $subtype,
        'icon'			=> wp_mime_type_icon( $attachment->ID )
	);
	
	// filesize
	if( isset($meta['filesize']) ) {
		$response['filesize'] = $meta['filesize'];
	} elseif( file_exists($attached_file) ) {
		$response['filesize'] = filesize( $attached_file );
	}
	
	// image
	if( $type === 'image' ) {
		
		$sizes_id = $attachment->ID;
		$src = wp_get_attachment_image_src( $attachment->ID, 'full' );
		
		$response['url'] = $src[0];
		$response['width'] = $src[1];
		$response['height'] = $src[2];
	
	// video
	} elseif( $type === 'video' ) {
		
		// dimentions
		$response['width'] = acf_maybe_get($meta, 'width', 0);
		$response['height'] = acf_maybe_get($meta, 'height', 0);
		
		// featured image
		if( $featured_id = get_post_thumbnail_id($attachment->ID) ) {
			$sizes_id = $featured_id;
		}
		
	// audio
	} elseif( $type === 'audio' ) {
		
		// featured image
		if( $featured_id = get_post_thumbnail_id($attachment->ID) ) {
			$sizes_id = $featured_id;
		}				
	}
	
	
	// sizes
	if( $sizes_id ) {
		
		// vars
		$sizes = get_intermediate_image_sizes();
		$data = array();
		
		// loop
		foreach( $sizes as $size ) {
			$src = wp_get_attachment_image_src( $sizes_id, $size );
			$data[ $size ] = $src[0];
			$data[ $size . '-width' ] = $src[1];
			$data[ $size . '-height' ] = $src[2];
		}
		
		// append
		$response['sizes'] = $data;
	}
	
	// return
	return $response;
	
}


/*
*  acf_get_truncated
*
*  This function will truncate and return a string
*
*  @type	function
*  @date	8/08/2014
*  @since	5.0.0
*
*  @param	$text (string)
*  @param	$length (int)
*  @return	(string)
*/

function acf_get_truncated( $text, $length = 64 ) {
	
	// vars
	$text = trim($text);
	$the_length = strlen( $text );
	
	
	// cut
	$return = substr( $text, 0, ($length - 3) );
	
	
	// ...
	if( $the_length > ($length - 3) ) {
	
		$return .= '...';
		
	}
	
	
	// return
	return $return;
	
}


/*
*  acf_get_current_url
*
*  This function will return the current URL
*
*  @type	function
*  @date	23/01/2015
*  @since	5.1.5
*
*  @param	n/a
*  @return	(string)
*/

function acf_get_current_url() {
	
	// vars
	$home = home_url();
	$url = home_url($_SERVER['REQUEST_URI']);
	
	
	// test
	//$home = 'http://acf5/dev/wp-admin';
	//$url = $home . '/dev/wp-admin/api-template/acf_form';
	
	
	// explode url (4th bit is the sub folder)
	$bits = explode('/', $home, 4);
	
	
	/*
	Array (
	    [0] => http:
	    [1] => 
	    [2] => acf5
	    [3] => dev
	)
	*/
	
	
	// handle sub folder
	if( !empty($bits[3]) ) {
		
		$find = '/' . $bits[3];
		$pos = strpos($url, $find);
		$length = strlen($find);
		
		if( $pos !== false ) {
			
		    $url = substr_replace($url, '', $pos, $length);
		    
		}
				
	}
	
	
	// return
	return $url;
	
}


/*
*  acf_current_user_can_admin
*
*  This function will return true if the current user can administrate the ACF field groups
*
*  @type	function
*  @date	9/02/2015
*  @since	5.1.5
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_current_user_can_admin() {
	
	if( acf_get_setting('show_admin') && current_user_can(acf_get_setting('capability')) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_filesize
*
*  This function will return a numeric value of bytes for a given filesize string
*
*  @type	function
*  @date	18/02/2015
*  @since	5.1.5
*
*  @param	$size (mixed)
*  @return	(int)
*/

function acf_get_filesize( $size = 1 ) {
	
	// vars
	$unit = 'MB';
	$units = array(
		'TB' => 4,
		'GB' => 3,
		'MB' => 2,
		'KB' => 1,
	);
	
	
	// look for $unit within the $size parameter (123 KB)
	if( is_string($size) ) {
		
		// vars
		$custom = strtoupper( substr($size, -2) );
		
		foreach( $units as $k => $v ) {
			
			if( $custom === $k ) {
				
				$unit = $k;
				$size = substr($size, 0, -2);
					
			}
			
		}
		
	}
	
	
	// calc bytes
	$bytes = floatval($size) * pow(1024, $units[$unit]); 
	
	
	// return
	return $bytes;
	
}


/*
*  acf_format_filesize
*
*  This function will return a formatted string containing the filesize and unit
*
*  @type	function
*  @date	18/02/2015
*  @since	5.1.5
*
*  @param	$size (mixed)
*  @return	(int)
*/

function acf_format_filesize( $size = 1 ) {
	
	// convert
	$bytes = acf_get_filesize( $size );
	
	
	// vars
	$units = array(
		'TB' => 4,
		'GB' => 3,
		'MB' => 2,
		'KB' => 1,
	);
	
	
	// loop through units
	foreach( $units as $k => $v ) {
		
		$result = $bytes / pow(1024, $v);
		
		if( $result >= 1 ) {
			
			return $result . ' ' . $k;
			
		}
		
	}
	
	
	// return
	return $bytes . ' B';
		
}


/*
*  acf_get_valid_terms
*
*  This function will replace old terms with new split term ids
*
*  @type	function
*  @date	27/02/2015
*  @since	5.1.5
*
*  @param	$terms (int|array)
*  @param	$taxonomy (string)
*  @return	$terms
*/

function acf_get_valid_terms( $terms = false, $taxonomy = 'category' ) {
	
	// force into array
	$terms = acf_get_array($terms);
	
	
	// force ints
	$terms = array_map('intval', $terms);
	
	
	// bail early if function does not yet exist or
	if( !function_exists('wp_get_split_term') || empty($terms) ) {
		
		return $terms;
		
	}
	
	
	// attempt to find new terms
	foreach( $terms as $i => $term_id ) {
		
		$new_term_id = wp_get_split_term($term_id, $taxonomy);
		
		if( $new_term_id ) {
			
			$terms[ $i ] = $new_term_id;
			
		}
		
	}
	
	
	// return
	return $terms;
	
}


/*
*  acf_esc_html_deep
*
*  Navigates through an array and escapes html from the values.
*
*  @type	function
*  @date	10/06/2015
*  @since	5.2.7
*
*  @param	$value (mixed)
*  @return	$value
*/

/*
function acf_esc_html_deep( $value ) {
	
	// array
	if( is_array($value) ) {
		
		$value = array_map('acf_esc_html_deep', $value);
	
	// object
	} elseif( is_object($value) ) {
		
		$vars = get_object_vars( $value );
		
		foreach( $vars as $k => $v ) {
			
			$value->{$k} = acf_esc_html_deep( $v );
		
		}
		
	// string
	} elseif( is_string($value) ) {

		$value = esc_html($value);

	}
	
	
	// return
	return $value;

}
*/


/*
*  acf_validate_attachment
*
*  This function will validate an attachment based on a field's resrictions and return an array of errors
*
*  @type	function
*  @date	3/07/2015
*  @since	5.2.3
*
*  @param	$attachment (array) attachment data. Cahnges based on context
*  @param	$field (array) field settings containing restrictions
*  @param	$context (string) $file is different when uploading / preparing
*  @return	$errors (array)
*/

function acf_validate_attachment( $attachment, $field, $context = 'prepare' ) {
	
	// vars
	$errors = array();
	$file = array(
		'type'		=> '',
		'width'		=> 0,
		'height'	=> 0,
		'size'		=> 0
	);
	
	
	// upload
	if( $context == 'upload' ) {
		
		// vars
		$file['type'] = pathinfo($attachment['name'], PATHINFO_EXTENSION);
		$file['size'] = filesize($attachment['tmp_name']);
		
		if( strpos($attachment['type'], 'image') !== false ) {
			
			$size = getimagesize($attachment['tmp_name']);
			$file['width'] = acf_maybe_get($size, 0);
			$file['height'] = acf_maybe_get($size, 1);
				
		}
	
	// prepare
	} elseif( $context == 'prepare' ) {
		
		$file['type'] = pathinfo($attachment['url'], PATHINFO_EXTENSION);
		$file['size'] = acf_maybe_get($attachment, 'filesizeInBytes', 0);
		$file['width'] = acf_maybe_get($attachment, 'width', 0);
		$file['height'] = acf_maybe_get($attachment, 'height', 0);
	
	// custom
	} else {
		
		$file = array_merge($file, $attachment);
		$file['type'] = pathinfo($attachment['url'], PATHINFO_EXTENSION);
		
	}
	
	
	// image
	if( $file['width'] || $file['height'] ) {
		
		// width
		$min_width = (int) acf_maybe_get($field, 'min_width', 0);
		$max_width = (int) acf_maybe_get($field, 'max_width', 0);
		
		if( $file['width'] ) {
			
			if( $min_width && $file['width'] < $min_width ) {
				
				// min width
				$errors['min_width'] = sprintf(__('Image width must be at least %dpx.', 'acf'), $min_width );
				
			} elseif( $max_width && $file['width'] > $max_width ) {
				
				// min width
				$errors['max_width'] = sprintf(__('Image width must not exceed %dpx.', 'acf'), $max_width );
				
			}
			
		}
		
		
		// height
		$min_height = (int) acf_maybe_get($field, 'min_height', 0);
		$max_height = (int) acf_maybe_get($field, 'max_height', 0);
		
		if( $file['height'] ) {
			
			if( $min_height && $file['height'] < $min_height ) {
				
				// min height
				$errors['min_height'] = sprintf(__('Image height must be at least %dpx.', 'acf'), $min_height );
				
			}  elseif( $max_height && $file['height'] > $max_height ) {
				
				// min height
				$errors['max_height'] = sprintf(__('Image height must not exceed %dpx.', 'acf'), $max_height );
				
			}
			
		}
			
	}
	
	
	// file size
	if( $file['size'] ) {
		
		$min_size = acf_maybe_get($field, 'min_size', 0);
		$max_size = acf_maybe_get($field, 'max_size', 0);
		
		if( $min_size && $file['size'] < acf_get_filesize($min_size) ) {
				
			// min width
			$errors['min_size'] = sprintf(__('File size must be at least %s.', 'acf'), acf_format_filesize($min_size) );
			
		} elseif( $max_size && $file['size'] > acf_get_filesize($max_size) ) {
				
			// min width
			$errors['max_size'] = sprintf(__('File size must must not exceed %s.', 'acf'), acf_format_filesize($max_size) );
			
		}
	
	}
	
	
	// file type
	if( $file['type'] ) {
		
		$mime_types = acf_maybe_get($field, 'mime_types', '');
		
		// lower case
		$file['type'] = strtolower($file['type']);
		$mime_types = strtolower($mime_types);
		
		
		// explode
		$mime_types = str_replace(array(' ', '.'), '', $mime_types);
		$mime_types = explode(',', $mime_types); // split pieces
		$mime_types = array_filter($mime_types); // remove empty pieces
		
		if( !empty($mime_types) && !in_array($file['type'], $mime_types) ) {
			
			// glue together last 2 types
			if( count($mime_types) > 1 ) {
				
				$last1 = array_pop($mime_types);
				$last2 = array_pop($mime_types);
				
				$mime_types[] = $last2 . ' ' . __('or', 'acf') . ' ' . $last1;
				
			}
			
			$errors['mime_types'] = sprintf(__('File type must be %s.', 'acf'), implode(', ', $mime_types) );
			
		}
				
	}
	
	
	// filter for 3rd party customization
	$errors = apply_filters("acf/validate_attachment", $errors, $file, $attachment, $field, $context);
	$errors = apply_filters("acf/validate_attachment/type={$field['type']}", $errors, $file, $attachment, $field, $context );
	$errors = apply_filters("acf/validate_attachment/name={$field['name']}", $errors, $file, $attachment, $field, $context );
	$errors = apply_filters("acf/validate_attachment/key={$field['key']}", $errors, $file, $attachment, $field, $context );
	
	
	// return
	return $errors;
	
}


/*
*  _acf_settings_uploader
*
*  Dynamic logic for uploader setting
*
*  @type	function
*  @date	7/05/2015
*  @since	5.2.3
*
*  @param	$uploader (string)
*  @return	$uploader
*/

add_filter('acf/settings/uploader', '_acf_settings_uploader');

function _acf_settings_uploader( $uploader ) {
	
	// if can't upload files
	if( !current_user_can('upload_files') ) {
		
		$uploader = 'basic';
		
	}
	
	
	// return
	return $uploader;
}


/*
*  acf_translate_keys
*
*  description
*
*  @type	function
*  @date	7/12/2015
*  @since	5.3.2
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

/*
function acf_translate_keys( $array, $keys ) {
	
	// bail early if no keys
	if( empty($keys) ) return $array;
	
	
	// translate
	foreach( $keys as $k ) {
		
		// bail ealry if not exists
		if( !isset($array[ $k ]) ) continue;
		
		
		// translate
		$array[ $k ] = acf_translate( $array[ $k ] );
		
	}
	
	
	// return
	return $array;
	
}
*/


/*
*  acf_translate
*
*  This function will translate a string using the new 'l10n_textdomain' setting
*  Also works for arrays which is great for fields - select -> choices
*
*  @type	function
*  @date	4/12/2015
*  @since	5.3.2
*
*  @param	$string (mixed) string or array containins strings to be translated
*  @return	$string
*/

function acf_translate( $string ) {
	
	// vars
	$l10n = acf_get_setting('l10n');
	$textdomain = acf_get_setting('l10n_textdomain');
	
	
	// bail early if not enabled
	if( !$l10n ) return $string;
	
	
	// bail early if no textdomain
	if( !$textdomain ) return $string;
	
	
	// is array
	if( is_array($string) ) {
		
		return array_map('acf_translate', $string);
		
	}
	
	
	// bail early if not string
	if( !is_string($string) ) return $string;
	
	
	// bail early if empty
	if( $string === '' ) return $string;
	
	
	// allow for var_export export
	if( acf_get_setting('l10n_var_export') ){
		
		// bail early if already translated
		if( substr($string, 0, 7) === '!!__(!!' ) return $string;
		
		
		// return
		return "!!__(!!'" .  $string . "!!', !!'" . $textdomain . "!!')!!";
			
	}
	
	
	// vars
	return __( $string, $textdomain );
	
}


/*
*  acf_maybe_add_action
*
*  This function will determine if the action has already run before adding / calling the function
*
*  @type	function
*  @date	13/01/2016
*  @since	5.3.2
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_maybe_add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	
	// if action has already run, execute it
	// - if currently doing action, allow $tag to be added as per usual to allow $priority ordering needed for 3rd party asset compatibility
	if( did_action($tag) && !doing_action($tag) ) {
			
		call_user_func( $function_to_add );
	
	// if action has not yet run, add it
	} else {
		
		add_action( $tag, $function_to_add, $priority, $accepted_args );
		
	}
	
}


/*
*  acf_is_row_collapsed
*
*  This function will return true if the field's row is collapsed
*
*  @type	function
*  @date	2/03/2016
*  @since	5.3.2
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_is_row_collapsed( $field_key = '', $row_index = 0 ) {
	
	// collapsed
	$collapsed = acf_get_user_setting('collapsed_' . $field_key, '');
	
	
	// cookie fallback ( version < 5.3.2 )
	if( $collapsed === '' ) {
		
		$collapsed = acf_extract_var($_COOKIE, "acf_collapsed_{$field_key}", '');
		$collapsed = str_replace('|', ',', $collapsed);
		
		
		// update
		acf_update_user_setting( 'collapsed_' . $field_key, $collapsed );
			
	}
	
	
	// explode
	$collapsed = explode(',', $collapsed);
	$collapsed = array_filter($collapsed, 'is_numeric');
	
	
	// collapsed class
	return in_array($row_index, $collapsed);
	
}


/*
*  acf_get_attachment_image
*
*  description
*
*  @type	function
*  @date	24/10/16
*  @since	5.5.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_attachment_image( $attachment_id = 0, $size = 'thumbnail' ) {
	
	// vars
	$url = wp_get_attachment_image_src($attachment_id, 'thumbnail');
	$alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
	
	
	// bail early if no url
	if( !$url ) return '';
	
	
	// return
	$value = '<img src="' . $url . '" alt="' . $alt . '" />';
	
}


/*
*  acf_get_post_thumbnail
*
*  This function will return a thumbail image url for a given post
*
*  @type	function
*  @date	3/05/2016
*  @since	5.3.8
*
*  @param	$post (obj)
*  @param	$size (mixed)
*  @return	(string)
*/

function acf_get_post_thumbnail( $post = null, $size = 'thumbnail' ) {
	
	// vars
	$data = array(
		'url'	=> '',
		'type'	=> '',
		'html'	=> ''
	);
	
	
	// post
	$post = get_post($post);
	
    
	// bail early if no post
	if( !$post ) return $data;
	
	
	// vars
	$thumb_id = $post->ID;
	$mime_type = acf_maybe_get(explode('/', $post->post_mime_type), 0);
	
	
	// attachment
	if( $post->post_type === 'attachment' ) {
		
		// change $thumb_id
		if( $mime_type === 'audio' || $mime_type === 'video' ) {
			
			$thumb_id = get_post_thumbnail_id($post->ID);
			
		}
	
	// post
	} else {
		
		$thumb_id = get_post_thumbnail_id($post->ID);
			
	}
	
	
	// try url
	$data['url'] = wp_get_attachment_image_src($thumb_id, $size);
	$data['url'] = acf_maybe_get($data['url'], 0);
	
	
	// default icon
	if( !$data['url'] && $post->post_type === 'attachment' ) {
		
		$data['url'] = wp_mime_type_icon($post->ID);
		$data['type'] = 'icon';
		
	}
	
	
	// html
	$data['html'] = '<img src="' . $data['url'] . '" alt="" />';
	
	
	// return
	return $data;
	
}


/*
*  acf_get_browser
*
*  This functino will return the browser string for major browsers
*
*  @type	function
*  @date	17/01/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	(string)
*/

function acf_get_browser() {
	
	// vars
	$agent = $_SERVER['HTTP_USER_AGENT'];
	
	
	// browsers
	$browsers = array(
		'Firefox'	=> 'firefox',
		'Trident'	=> 'msie',
		'MSIE'		=> 'msie',
		'Edge'		=> 'edge',
		'Chrome'	=> 'chrome',
		'Safari'	=> 'safari',
	);
	
	
	// loop
	foreach( $browsers as $k => $v ) {
		
		if( strpos($agent, $k) !== false ) return $v;
		
	}
	
	
	// return
	return '';
	
}


/*
*  acf_is_ajax
*
*  This function will reutrn true if performing a wp ajax call
*
*  @type	function
*  @date	7/06/2016
*  @since	5.3.8
*
*  @param	n/a
*  @return	(boolean)
*/

function acf_is_ajax( $action = '' ) {
	
	// vars
	$is_ajax = false;
	
	
	// check if is doing ajax
	if( defined('DOING_AJAX') && DOING_AJAX ) {
		
		$is_ajax = true;
		
	}
	
	
	// check $action
	if( $action && acf_maybe_get($_POST, 'action') !== $action ) {
		
		$is_ajax = false;
		
	}
	
	
	// return
	return $is_ajax;
		
}




/*
*  acf_format_date
*
*  This function will accept a date value and return it in a formatted string
*
*  @type	function
*  @date	16/06/2016
*  @since	5.3.8
*
*  @param	$value (string)
*  @return	$format (string)
*/

function acf_format_date( $value, $format ) {
	
	// bail early if no value
	if( !$value ) return $value;
	
	
	// vars
	$unixtimestamp = 0;
	
	
	// numeric (either unix or YYYYMMDD)
	if( is_numeric($value) && strlen($value) !== 8 ) {
		
		$unixtimestamp = $value;
		
	} else {
		
		$unixtimestamp = strtotime($value);
		
	}
	
	
	// return
	return date_i18n($format, $unixtimestamp);
	
}


/*
*  acf_log
*
*  description
*
*  @type	function
*  @date	24/06/2016
*  @since	5.3.8
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_log() {
	
	// vars
	$log = '';
	$args = func_get_args();
	
	
	// loop
	foreach( $args as $i => $arg ) {
		
		if( is_array($arg) || is_object($arg) ) {
			
			$arg = print_r($arg, true);
			
		} elseif( is_bool($arg) ) {
			
			$arg = ( $arg ? 'true' : 'false' ) . ' (bool)';
			
		}
		
		
		// update
		$args[ $i ] = $arg;
		
	}
	
	
	// log
	error_log( implode(' ', $args) );
	
}


/*
*  acf_doing
*
*  This function will tell ACF what task it is doing
*
*  @type	function
*  @date	28/06/2016
*  @since	5.3.8
*
*  @param	$event (string)
*  @param	context (string)
*  @return	n/a
*/

function acf_doing( $event = '', $context = '' ) {
	
	acf_update_setting( 'doing', $event );
	acf_update_setting( 'doing_context', $context );
	
}


/*
*  acf_is_doing
*
*  This function can be used to state what ACF is doing, or to check
*
*  @type	function
*  @date	28/06/2016
*  @since	5.3.8
*
*  @param	$event (string)
*  @param	context (string)
*  @return	(boolean)
*/

function acf_is_doing( $event = '', $context = '' ) {
	
	// vars
	$doing = false;
	
	
	// task
	if( acf_get_setting('doing') === $event ) {
		
		$doing = true;
		
	}
	
	
	// context
	if( $context && acf_get_setting('doing_context') !== $context ) {
		
		$doing = false;
		
	}
	
	
	// return
	return $doing;
		
}


/*
*  acf_is_plugin_active
*
*  This function will return true if the ACF plugin is active
*  - May be included within a theme or other plugin
*
*  @type	function
*  @date	13/07/2016
*  @since	5.4.0
*
*  @param	$basename (int)
*  @return	$post_id (int)
*/


function acf_is_plugin_active() {
	
	// vars
	$basename = acf_get_setting('basename');
	
	
	// ensure is_plugin_active() exists (not on frontend)
	if( !function_exists('is_plugin_active') ) {
		
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
	}
	
	
	// return
	return is_plugin_active($basename);
	
}


/**
*  acf_get_filters
*
*  Returns the registered filters
*
*  @date	2/2/18
*  @since	5.6.5
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function acf_get_filters() {
	
	// get
	$filters = acf_raw_setting('filters');
	
	// array
	$filters = is_array($filters) ? $filters : array();
	
	// return
	return $filters;
}


/**
*  acf_update_filters
*
*  Updates the registered filters
*
*  @date	2/2/18
*  @since	5.6.5
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function acf_update_filters( $filters ) {
	return acf_update_setting('filters', $filters);
}


/*
*  acf_enable_filter
*
*  This function will enable a filter
*
*  @type	function
*  @date	15/07/2016
*  @since	5.4.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_enable_filter( $filter = '' ) {
	
	// get 
	$filters = acf_get_filters();
	
	// append
	$filters[ $filter ] = true;
	
	// update
	acf_update_filters( $filters );
}


/*
*  acf_disable_filter
*
*  This function will disable a filter
*
*  @type	function
*  @date	15/07/2016
*  @since	5.4.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_disable_filter( $filter = '' ) {
	
	// get 
	$filters = acf_get_filters();
	
	// append
	$filters[ $filter ] = false;
	
	// update
	acf_update_filters( $filters );
}


/*
*  acf_enable_filters
*
*  ACF uses filters to modify field group and field data
*  This function will enable them allowing ACF to interact with all data
*
*  @type	function
*  @date	14/07/2016
*  @since	5.4.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_enable_filters() {
	
	// get 
	$filters = acf_get_filters();
	
	// loop
	foreach( array_keys($filters) as $k ) {
		$filters[ $k ] = true;
	}
	
	// update
	acf_update_filters( $filters );	
}


/*
*  acf_disable_filters
*
*  ACF uses filters to modify field group and field data
*  This function will disable them allowing ACF to interact only with raw DB data
*
*  @type	function
*  @date	14/07/2016
*  @since	5.4.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_disable_filters() {
	
	// get 
	$filters = acf_get_filters();
	
	// loop
	foreach( array_keys($filters) as $k ) {
		$filters[ $k ] = false;
	}
	
	// update
	acf_update_filters( $filters );	
}


/*
*  acf_is_filter_enabled
*
*  ACF uses filters to modify field group and field data
*  This function will return true if they are enabled
*
*  @type	function
*  @date	14/07/2016
*  @since	5.4.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_is_filter_enabled( $filter = '' ) {
	
	// get 
	$filters = acf_get_filters();
	
	// return
	return !empty($filters[ $filter ]);
}


/*
*  acf_send_ajax_results
*
*  This function will print JSON data for a Select2 AJAX query
*
*  @type	function
*  @date	19/07/2016
*  @since	5.4.0
*
*  @param	$response (array)
*  @return	n/a
*/

function acf_send_ajax_results( $response ) {
	
	// validate
	$response = wp_parse_args($response, array(
		'results'	=> false,
		'more'		=> false,
		'limit'		=> 0
	));
	
	
	// limit
	if( $response['limit'] && $response['results']) {
		
		// vars
		$total = 0;
		
		foreach( $response['results'] as $result ) {
			
			// parent
			$total++;
			
			
			// children
			if( !empty($result['children']) ) {
				
				$total += count( $result['children'] );
				
			}
			
		}
		
		
		// calc
		if( $total >= $response['limit'] ) {
			
			$response['more'] = true;
			
		}
		
	}
	
	
	// return
	wp_send_json( $response );
	
}


/*
*  acf_is_sequential_array
*
*  This function will return true if the array contains only numeric keys
*
*  @source	http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
*  @type	function
*  @date	9/09/2016
*  @since	5.4.0
*
*  @param	$array (array)
*  @return	(boolean)
*/

function acf_is_sequential_array( $array ) {
	
	// bail ealry if not array
	if( !is_array($array) ) return false;
	
	
	// loop
	foreach( $array as $key => $value ) {
		
		// bail ealry if is string
		if( is_string($key) ) return false;
	
	}
	
	
	// return
	return true;
	
}


/*
*  acf_is_associative_array
*
*  This function will return true if the array contains one or more string keys
*
*  @source	http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
*  @type	function
*  @date	9/09/2016
*  @since	5.4.0
*
*  @param	$array (array)
*  @return	(boolean)
*/

function acf_is_associative_array( $array ) {
	
	// bail ealry if not array
	if( !is_array($array) ) return false;
	
	
	// loop
	foreach( $array as $key => $value ) {
		
		// bail ealry if is string
		if( is_string($key) ) return true;
	
	}
	
	
	// return
	return false;
	
}


/*
*  acf_add_array_key_prefix
*
*  This function will add a prefix to all array keys
*  Useful to preserve numeric keys when performing array_multisort
*
*  @type	function
*  @date	15/09/2016
*  @since	5.4.0
*
*  @param	$array (array)
*  @param	$prefix (string)
*  @return	(array)
*/

function acf_add_array_key_prefix( $array, $prefix ) {
	
	// vars
	$array2 = array();
	
	
	// loop
	foreach( $array as $k => $v ) {
		
		$k2 = $prefix . $k;
	    $array2[ $k2 ] = $v;
	    
	}
	
	
	// return
	return $array2;
	
}


/*
*  acf_remove_array_key_prefix
*
*  This function will remove a prefix to all array keys
*  Useful to preserve numeric keys when performing array_multisort
*
*  @type	function
*  @date	15/09/2016
*  @since	5.4.0
*
*  @param	$array (array)
*  @param	$prefix (string)
*  @return	(array)
*/

function acf_remove_array_key_prefix( $array, $prefix ) {
	
	// vars
	$array2 = array();
	$l = strlen($prefix);
	
	
	// loop
	foreach( $array as $k => $v ) {
		
		$k2 = (substr($k, 0, $l) === $prefix) ? substr($k, $l) : $k;
	    $array2[ $k2 ] = $v;
	    
	}
	
	
	// return
	return $array2;
	
}


/*
*  acf_strip_protocol
*
*  This function will remove the proticol from a url 
*  Used to allow licences to remain active if a site is switched to https 
*
*  @type	function
*  @date	10/01/2017
*  @since	5.5.4
*  @author	Aaron 
*
*  @param	$url (string)
*  @return	(string) 
*/

function acf_strip_protocol( $url ) {
		
	// strip the protical 
	return str_replace(array('http://','https://'), '', $url);

}


/*
*  acf_connect_attachment_to_post
*
*  This function will connect an attacment (image etc) to the post 
*  Used to connect attachements uploaded directly to media that have not been attaced to a post
*
*  @type	function
*  @date	11/01/2017
*  @since	5.5.4
*
*  @param	$attachment_id (int)
*  @param	$post_id (int)
*  @return	(boolean) 
*/

function acf_connect_attachment_to_post( $attachment_id = 0, $post_id = 0 ) {
	
	// bail ealry if $attachment_id is not valid
	if( !$attachment_id || !is_numeric($attachment_id) ) return false;
	
	
	// bail ealry if $post_id is not valid
	if( !$post_id || !is_numeric($post_id) ) return false;
	
	
	// vars 
	$post = get_post( $attachment_id );
	
	
	// check if valid post
	if( $post && $post->post_type == 'attachment' && $post->post_parent == 0 ) {
		
		// update
		wp_update_post( array('ID' => $post->ID, 'post_parent' => $post_id) );
		
		
		// return
		return true;
		
	}
	
	
	// return
	return true;

}


/*
*  acf_encrypt
*
*  This function will encrypt a string using PHP
*  https://bhoover.com/using-php-openssl_encrypt-openssl_decrypt-encrypt-decrypt-data/
*
*  @type	function
*  @date	27/2/17
*  @since	5.5.8
*
*  @param	$data (string)
*  @return	(string)
*/


function acf_encrypt( $data = '' ) {
	
	// bail ealry if no encrypt function
	if( !function_exists('openssl_encrypt') ) return base64_encode($data);
	
	
	// generate a key
	$key = wp_hash('acf_encrypt');
	
	
    // Generate an initialization vector
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    
    
    // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
    $encrypted_data = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    
    
    // The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
    return base64_encode($encrypted_data . '::' . $iv);
	
}


/*
*  acf_decrypt
*
*  This function will decrypt an encrypted string using PHP
*  https://bhoover.com/using-php-openssl_encrypt-openssl_decrypt-encrypt-decrypt-data/
*
*  @type	function
*  @date	27/2/17
*  @since	5.5.8
*
*  @param	$data (string)
*  @return	(string)
*/

function acf_decrypt( $data = '' ) {
	
	// bail ealry if no decrypt function
	if( !function_exists('openssl_decrypt') ) return base64_decode($data);
	
	
	// generate a key
	$key = wp_hash('acf_encrypt');
	
	
    // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    
    
    // decrypt
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
	
}


/*
*  acf_get_post_templates
*
*  This function will return an array of all post templates (including parent theme templates)
*
*  @type	function
*  @date	29/8/17
*  @since	5.6.2
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_post_templates() {
	
	// vars
	$post_types = acf_get_post_types();
	$post_templates = array();
	
	
	// loop
	foreach( $post_types as $post_type ) {
		$post_templates[ $post_type ] = wp_get_theme()->get_page_templates(null, $post_type);
	}
	
	
	// remove empty templates
	$post_templates = array_filter( $post_templates );
	
	
	// return
	return $post_templates;
	
}

?>