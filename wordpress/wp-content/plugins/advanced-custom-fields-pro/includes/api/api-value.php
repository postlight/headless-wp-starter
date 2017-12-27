<?php

/*
*  acf_get_metadata
*
*  This function will get a value from the DB
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (mixed)
*  @param	$name (string)
*  @param	$hidden (boolean)
*  @return	$return (mixed)
*/

function acf_get_metadata( $post_id = 0, $name = '', $hidden = false ) {
	
	// vars
	$value = null;
	$prefix = $hidden ? '_' : '';
	
	
	// get post_id info
	$info = acf_get_post_id_info($post_id);
	
	
	// bail early if no $post_id (acf_form - new_post)
	if( !$info['id'] ) return $value;
	
	
	// option
	if( $info['type'] === 'option' ) {
		
		$name = $prefix . $post_id . '_' . $name;
		$value = get_option( $name, null );
		
	// meta
	} else {
		
		$name = $prefix . $name;
		$meta = get_metadata( $info['type'], $info['id'], $name, false );
		
		if( isset($meta[0]) ) {
		
		 	$value = $meta[0];
		 	
	 	}
		
	}
	
	
	// return
	return $value;
	
}


/*
*  acf_update_metadata
*
*  This function will update a value from the DB
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (mixed)
*  @param	$name (string)
*  @param	$value (mixed)
*  @param	$hidden (boolean)
*  @return	$return (boolean)
*/

function acf_update_metadata( $post_id = 0, $name = '', $value = '', $hidden = false ) {
	
	// vars
	$return = false;
	$prefix = $hidden ? '_' : '';
	
	
	// get post_id info
	$info = acf_get_post_id_info($post_id);
	
	
	// bail early if no $post_id (acf_form - new_post)
	if( !$info['id'] ) return $return;
	
	
	// option
	if( $info['type'] === 'option' ) {
		
		$name = $prefix . $post_id . '_' . $name;
		$return = acf_update_option( $name, $value );
		
	// meta
	} else {
		
		$name = $prefix . $name;
		$return = update_metadata( $info['type'], $info['id'], $name, $value );
		
	}
	
	
	// return
	return $return;
	
}


/*
*  acf_delete_metadata
*
*  This function will delete a value from the DB
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$post_id (mixed)
*  @param	$name (string)
*  @param	$hidden (boolean)
*  @return	$return (boolean)
*/

function acf_delete_metadata( $post_id = 0, $name = '', $hidden = false ) {
	
	// vars
	$return = false;
	$prefix = $hidden ? '_' : '';
	
	
	// get post_id info
	$info = acf_get_post_id_info($post_id);
	
	
	// bail early if no $post_id (acf_form - new_post)
	if( !$info['id'] ) return $return;
	
	
	// option
	if( $info['type'] === 'option' ) {
		
		$name = $prefix . $post_id . '_' . $name;
		$return = delete_option( $name );
		
	// meta
	} else {
		
		$name = $prefix . $name;
		$return = delete_metadata( $info['type'], $info['id'], $name );
		
	}
	
	
	// return
	return $return;
	
}


/*
*  acf_update_option
*
*  This function is a wrapper for the WP update_option but provides logic for a 'no' autoload
*
*  @type	function
*  @date	4/01/2014
*  @since	5.0.0
*
*  @param	$option (string)
*  @param	$value (mixed)
*  @param	autoload (mixed)
*  @return	(boolean)
*/

function acf_update_option( $option = '', $value = '', $autoload = null ) {
	
	// vars
	$deprecated = '';
	$return = false;
	
	
	// autoload
	if( $autoload === null ){
		
		$autoload = acf_get_setting('autoload') ? 'yes' : 'no';
		
	}
	
	
	// for some reason, update_option does not use stripslashes_deep.
	// update_metadata -> https://core.trac.wordpress.org/browser/tags/3.4.2/wp-includes/meta.php#L82: line 101 (does use stripslashes_deep)
	// update_option -> https://core.trac.wordpress.org/browser/tags/3.5.1/wp-includes/option.php#L0: line 215 (does not use stripslashes_deep)
	$value = stripslashes_deep($value);
		
		
	// add or update
	if( get_option($option) !== false ) {
	
	    $return = update_option( $option, $value );
	    
	} else {
		
		$return = add_option( $option, $value, $deprecated, $autoload );
		
	}
	
	
	// return
	return $return;
	
}


/*
*  acf_get_value
*
*  This function will load in a field's value
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @param	$field (array)
*  @return	(mixed)
*/

function acf_get_value( $post_id = 0, $field ) {
	
	// allow filter to short-circuit load_value logic
	//$value = apply_filters( "acf/pre_load_value", null, $post_id, $field );
    //if( $value !== null ) {
	//    return $value;
    //}
        
        
	// vars
	$cache_key = "get_value/post_id={$post_id}/name={$field['name']}";
	
	
	// return early if cache is found
	if( acf_isset_cache($cache_key) ) {
		return acf_get_cache($cache_key);
	}
	
	
	// load value
	$value = acf_get_metadata( $post_id, $field['name'] );
	
	
	// if value was duplicated, it may now be a serialized string!
	$value = maybe_unserialize( $value );
	
	
	// no value? try default_value
	if( $value === null && isset($field['default_value']) ) {
		$value = $field['default_value'];
	}
	
	
	// filter for 3rd party customization
	$value = apply_filters( "acf/load_value", $value, $post_id, $field );
	$value = apply_filters( "acf/load_value/type={$field['type']}", $value, $post_id, $field );
	$value = apply_filters( "acf/load_value/name={$field['_name']}", $value, $post_id, $field );
	$value = apply_filters( "acf/load_value/key={$field['key']}", $value, $post_id, $field );
	
	
	// update cache
	acf_set_cache($cache_key, $value);

	
	// return
	return $value;
	
}


/*
*  acf_format_value
*
*  This function will format the value for front end use
*
*  @type	function
*  @date	3/07/2014
*  @since	5.0.0
*
*  @param	$value (mixed)
*  @param	$post_id (mixed)
*  @param	$field (array)
*  @return	$value
*/

function acf_format_value( $value, $post_id, $field ) {
	
	// vars
	$cache_key = "format_value/post_id={$post_id}/name={$field['name']}";
	
	
	// return early if cache is found
	if( acf_isset_cache($cache_key) ) {
		
		return acf_get_cache($cache_key);
		
	}
	
	
	// apply filters
	$value = apply_filters( "acf/format_value", $value, $post_id, $field );
	$value = apply_filters( "acf/format_value/type={$field['type']}", $value, $post_id, $field );
	$value = apply_filters( "acf/format_value/name={$field['_name']}", $value, $post_id, $field );
	$value = apply_filters( "acf/format_value/key={$field['key']}", $value, $post_id, $field );
	
	
	// update cache
	acf_set_cache($cache_key, $value);
	
	
	// return
	return $value;
	
} 


/*
*  acf_update_value
*
*  updates a value into the db
*
*  @type	action
*  @date	23/01/13
*
*  @param	$value (mixed)
*  @param	$post_id (mixed)
*  @param	$field (array)
*  @return	(boolean)
*/

function acf_update_value( $value = null, $post_id = 0, $field ) {
	
	// strip slashes
	if( acf_get_setting('stripslashes') ) {
		
		$value = stripslashes_deep($value);
		
	}
	
	
	// filter for 3rd party customization
	$value = apply_filters( "acf/update_value", $value, $post_id, $field );
	$value = apply_filters( "acf/update_value/type={$field['type']}", $value, $post_id, $field );
	$value = apply_filters( "acf/update_value/name={$field['_name']}", $value, $post_id, $field );
	$value = apply_filters( "acf/update_value/key={$field['key']}", $value, $post_id, $field );
	
	
	// allow null to delete
	if( $value === null ) {
		
		return acf_delete_value( $post_id, $field );
		
	}
	
	
	// update value
	$return = acf_update_metadata( $post_id, $field['name'], $value );
	
	
	// update reference
	acf_update_metadata( $post_id, $field['name'], $field['key'], true );
	
	
	// clear cache
	acf_delete_cache("get_value/post_id={$post_id}/name={$field['name']}");
	acf_delete_cache("format_value/post_id={$post_id}/name={$field['name']}");

	
	// return
	return $return;
	
}


/*
*  acf_delete_value
*
*  This function will delete a value from the database
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$post_id (mixed)
*  @param	$field (array)
*  @return	(boolean)
*/

function acf_delete_value( $post_id = 0, $field ) {
	
	// action for 3rd party customization
	do_action("acf/delete_value", $post_id, $field['name'], $field);
	do_action("acf/delete_value/type={$field['type']}", $post_id, $field['name'], $field);
	do_action("acf/delete_value/name={$field['_name']}", $post_id, $field['name'], $field);
	do_action("acf/delete_value/key={$field['key']}", $post_id, $field['name'], $field);
	
	
	// delete value
	$return = acf_delete_metadata( $post_id, $field['name'] );
	
	
	// delete reference
	acf_delete_metadata( $post_id, $field['name'], true );
	
	
	// clear cache
	acf_delete_cache("get_value/post_id={$post_id}/name={$field['name']}");
	acf_delete_cache("format_value/post_id={$post_id}/name={$field['name']}");
	
	
	// return
	return $return;
	
}


/*
*  acf_copy_postmeta
*
*  This function will copy postmeta from one post to another.
*  Very useful for saving and restoring revisions
*
*  @type	function
*  @date	25/06/2016
*  @since	5.3.8
*
*  @param	$from_post_id (int)
*  @param	$to_post_id (int)
*  @return	n/a
*/

function acf_copy_postmeta( $from_post_id, $to_post_id ) {
	
	// get all postmeta
	$meta = get_post_meta( $from_post_id );
	
	
	// bail early if no meta
	if( !$meta ) return;

	
	// loop
	foreach( $meta as $name => $value ) {
		
		// attempt to find key value
		$key = acf_maybe_get( $meta, '_'.$name );
		
		
		// bail ealry if no key
		if( !$key ) continue;
		
		
		// update vars
		$value = $value[0];
		$key = $key[0];
		
		
		// bail early if $key is a not a field_key
		if( !acf_is_field_key($key) ) continue;
		
		
		// get_post_meta will return array before running maybe_unserialize
		$value = maybe_unserialize( $value );
		
		
		// add in slashes
		// - update_post_meta will unslash the value, so we must first slash it to avoid losing backslashes
		// - https://codex.wordpress.org/Function_Reference/update_post_meta#Character_Escaping
		if( is_string($value) ) {
			
			$value =  wp_slash($value);
			
		}
		
		
		// update value
		acf_update_metadata( $to_post_id, $name, $value );
		acf_update_metadata( $to_post_id, $name, $key, true );
					
	}

}


/*
*  acf_preview_value
*
*  This function will return a human freindly 'preview' for a given field value
*
*  @type	function
*  @date	24/10/16
*  @since	5.5.0
*
*  @param	$value (mixed)
*  @param	$post_id (mixed)
*  @param	$field (array)
*  @return	(string)
*/

function acf_preview_value( $value, $post_id, $field ) {
	
	// apply filters
	$value = apply_filters( "acf/preview_value", $value, $post_id, $field );
	$value = apply_filters( "acf/preview_value/type={$field['type']}", $value, $post_id, $field );
	$value = apply_filters( "acf/preview_value/name={$field['_name']}", $value, $post_id, $field );
	$value = apply_filters( "acf/preview_value/key={$field['key']}", $value, $post_id, $field );
	
	
	// return
	return $value;
	
} 

?>