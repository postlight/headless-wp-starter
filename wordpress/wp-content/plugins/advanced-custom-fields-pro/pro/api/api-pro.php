<?php 

/*
*  acf_pro_get_view
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

function acf_pro_get_view( $view_name = '', $args = array() ) {
	
	// vars
	$path = acf_get_path("pro/admin/views/{$view_name}.php");
	
	
	if( file_exists($path) ) {
		
		include( $path );
		
	}
	
}


/*
*  acf_pro_get_remote_url
*
*  description
*
*  @type	function
*  @date	16/01/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_pro_get_remote_url( $action = '', $args = array() ) {
	
	// defaults
	$args['a'] = $action;
	$args['p'] = 'pro';
	
	
	// vars
	$url = "https://connect.advancedcustomfields.com/index.php?" . build_query($args);
	//$url = "http://connect/index.php?" . build_query($args);
	
	
	// return
	return $url;
	
}


/*
*  acf_pro_get_remote_response
*
*  description
*
*  @type	function
*  @date	16/01/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_pro_get_remote_response( $action = '', $post = array() ) {
	
	// vars
	$url = acf_pro_get_remote_url( $action );
	
	
	// connect
	$request = wp_remote_post( $url, array(
		'body' => $post
	));
	
	
	// error
	if( is_wp_error($request) ) {
		
	    // loop
	    foreach( $request->errors as $k => $v ) {
		    
			// bail early if no error
		    if( empty($v[0]) ) continue;
		    
		    
		    // save
			acf_update_setting('remote_response_error', $k . ': ' . $v[0]);
			
		    
		    // only run once
		    break;
		    
	    }
	    
	// success
	} elseif( wp_remote_retrieve_response_code($request) === 200) {
    	
        return $request['body'];
    
    }
    
    
    // return
    return 0;
    
}


/*
*  acf_pro_is_update_available
*
*  This function will return true if an update is available
*
*  @type	function
*  @date	14/05/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	(boolean)
*/

function acf_pro_is_update_available() {
	
	// vars
	$info = acf_get_remote_plugin_info();
	$version = acf_get_setting('version');
	 
	
	// return false if no info
	if( empty($info['version']) ) return false;
	
    
    // return false if the external version is '<=' the current version
	if( version_compare($info['version'], $version, '<=') ) {
		
    	return false;
    
    }
    
	
	// return
	return true;
	
}


/*
*  acf_pro_get_remote_info
*
*  This function will return remote plugin data
*
*  @type	function
*  @date	16/01/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	(mixed)
*/

function acf_pro_get_remote_info() {
	
	// clear transient if force check is enabled
	if( !empty($_GET['force-check']) ) {
		
		// only allow transient to be deleted once per page load
		if( empty($_GET['acf-ignore-force-check']) ) {
			
			delete_transient( 'acf_pro_get_remote_info' );
			
		}
		
		
		// update $_GET
		$_GET['acf-ignore-force-check'] = true;
		
	}
	
	
	// get transient
	$transient = get_transient( 'acf_pro_get_remote_info' );

	if( $transient !== false ) {
	
		return $transient;
	
	}

	
	// vars
	$info = acf_pro_get_remote_response('get-info');
	$timeout = 12 * HOUR_IN_SECONDS;
	
	
    // decode
    if( !empty($info) ) {
    	
		$info = json_decode($info, true);
		
		// fake info version
        //$info['version'] = '6.0.0';
        
    } else {
	    
	    $info = 0; // allow transient to be returned, but empty to validate
	    $timeout = 2 * HOUR_IN_SECONDS;
	    
    }
        
        
	// update transient
	set_transient('acf_pro_get_remote_info', $info, $timeout );
	
	
	// return
	return $info;
}


/*
*  acf_pro_get_license
*
*  This function will return the license
*
*  @type	function
*  @date	20/09/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_pro_get_license() {
	
	// get option
	$license = get_option('acf_pro_license');
	
	
	// bail early if no value
	if( !$license ) return false;
	
	
	// decode
	$license = maybe_unserialize(base64_decode($license));
	
	
	// bail early if corrupt
	if( !acf_is_array( $license )) return false;
	
	
	// return
	return $license;
	
}


/*
*  acf_pro_get_license_key
*
*  This function will return the license key
*
*  @type	function
*  @date	20/09/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_pro_get_license_key() {
	
	// vars
	$license = acf_pro_get_license();
	
	
	// bail early if empty
	if( !$license ) return false;
	
	
	// return
	return $license['key'];
	
}


/*
*  acf_pro_is_license_active
*
*  This function will return true if the current license is active
*
*  @type	function
*  @date	20/09/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_pro_is_license_active() {
	
	// vars
	$license = acf_pro_get_license();
	$url = home_url();
	
	// bail early if empty
	if( !$license ) return false;
	
	
	// bail early if no key
	if( !$license['key'] ) return false;
	

	// strip proticol from urls
	$license['url'] = acf_strip_protocol( $license['url'] );
	$url = acf_strip_protocol( $url );


	// bail early if url does not match
	if( $license['url'] !== $url ) {
		
		// add notice (only once) - removed due to feedback 
		// if( !acf_has_done('acf_pro_is_license_active_notice') ) {
		// 	
		// 	acf_add_admin_notice( __('Error validating ACF PRO license URL (website does not match). Please re-activate your license','acf'), 'error' );
		// 	
		// }
		
		return false;
		
	}
	
	
	// return
	return true;
	
}


/*
*  acf_pro_update_license
*
*  This function will update the DB license
*
*  @type	function
*  @date	20/09/2016
*  @since	5.4.0
*
*  @param	$key (string)
*  @return	n/a
*/

function acf_pro_update_license( $key = '' ) {
	
	// vars
	$value = '';
	
	
	// key
	if( $key ) {
		
		// vars
		$data = array(
			'key'	=> $key,
			'url'	=> home_url()
		);
		
		
		// encode
		$value = base64_encode(maybe_serialize($data));
		
	}
	
	
	// update
	return update_option('acf_pro_license', $value);
	
}

?>