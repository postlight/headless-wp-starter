<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_updates') ) :

class acf_updates {
	
	// vars
	var $version = '2.3',
		$plugins = array(),
		$force_check = false,
		$dev = 0;
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// vars
		$this->force_check = !empty($_GET['force-check']);
		
		
		// append update information to transient
		add_filter('pre_set_site_transient_update_plugins', array($this, 'modify_plugins_transient'), 10, 1);
		
		
		// modify plugin data visible in the 'View details' popup
	    add_filter('plugins_api', array($this, 'modify_plugin_details'), 10, 3);
	}
	
	
	/*
	*  add_plugin
	*
	*  This function will register a plugin
	*
	*  @type	function
	*  @date	8/4/17
	*  @since	5.5.10
	*
	*  @param	$plugin (array)
	*  @return	n/a
	*/
	
	function add_plugin( $plugin ) {
		
		// validate
		$plugin = wp_parse_args($plugin, array(
			'id'		=> '',
			'key'		=> '',
			'slug'		=> '',
			'basename'	=> '',
			'version'	=> '',
		));
		
		// Check if is_plugin_active() function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if( !function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		// add if is active plugin (not included in theme)
		if( is_plugin_active($plugin['basename']) ) {
			$this->plugins[ $plugin['basename'] ] = $plugin;
		}
	}
		
	
	/*
	*  request
	*
	*  This function will make a request to the ACF update server
	*
	*  @type	function
	*  @date	8/4/17
	*  @since	5.5.10
	*
	*  @param	$query (string)
	*  @param	$body (array)
	*  @return	(mixed)
	*/
	
	function request( $query = 'index.php', $body = null ) {
		
		// vars
		$url = 'https://connect.advancedcustomfields.com/' . $query;
		
		// development mode
		if( $this->dev ) {
			$url = 'http://connect/' . $query;
			acf_log('acf connect: '. $url, $body);
		}
		
		// post
		$raw_response = wp_remote_post( $url, array(
			'timeout'	=> 10,
			'body'		=> $body
		));
		
		// wp error
		if( is_wp_error($raw_response) ) {
			return $raw_response;
		
		// http error
		} elseif( wp_remote_retrieve_response_code($raw_response) != 200 ) {
			return new WP_Error( 'server_error', wp_remote_retrieve_response_message($raw_response) );
		}
		
		// decode response
		$json = json_decode( wp_remote_retrieve_body($raw_response), true );
		
		// allow non json value
		if( $json === null ) {
			return wp_remote_retrieve_body($raw_response);
		}
		
		// return
		return $json;
	}
	
	
	/*
	*  get_plugin_info
	*
	*  This function will get plugin info and save as transient
	*
	*  @type	function
	*  @date	9/4/17
	*  @since	5.5.10
	*
	*  @param	$id (string)
	*  @return	(mixed)
	*/
	
	function get_plugin_info( $id = '' ) {
		
		// var
		$transient_name = 'acf_plugin_info_' . $id;
		
		// ignore cache (only once)
		if( $this->force_check ) {
			$this->force_check = false;
			
		// check cache
		} else {
			$transient = get_transient($transient_name);
			if( $transient !== false ) return $transient;
		}
		
		// connect
		$response = $this->request('v2/plugins/get-info?p='.$id);
		
		// convert string (misc error) to WP_Error object
		if( is_string($response) ) {
			$response = new WP_Error( 'server_error', esc_html($response) );
		}
		
		// allow json to include expiration but force minimum and max for safety
		$expiration = $this->get_expiration($response, DAY_IN_SECONDS, MONTH_IN_SECONDS);
		
		// update transient
		set_transient($transient_name, $response, $expiration );
		
		// return
		return $response;
	}
	
	
	/**
	*  get_plugin_updates
	*
	*  description
	*
	*  @date	8/7/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function get_plugin_updates() {
		
		// var
		$transient_name = 'acf_plugin_updates';
		
		// ignore cache (only once)
		if( $this->force_check ) {
			$this->force_check = false;
			
		// check cache
		} else {
			$transient = get_transient($transient_name);
			if( $transient !== false ) return $transient;
		}
		
		// vars
		$post = array(
			'plugins'		=> wp_json_encode($this->plugins),
			'wp'			=> wp_json_encode(array(
				'wp_name'		=> get_bloginfo('name'),
				'wp_url'		=> home_url(),
				'wp_version'	=> get_bloginfo('version'),
				'wp_language'	=> get_bloginfo('language'),
				'wp_timezone'	=> get_option('timezone_string'),
			)),
			'acf'			=> wp_json_encode(array(
				'acf_version'	=> get_option('acf_version'),
				'acf_pro'		=> (defined('ACF_PRO') && ACF_PRO),
			)),
		);
		
		// request
		$response = $this->request('v2/plugins/update-check', $post);
		
		// allow json to include expiration but force minimum and max for safety
		$expiration = $this->get_expiration($response, DAY_IN_SECONDS, MONTH_IN_SECONDS);
		
		// update transient
		set_transient($transient_name, $response, $expiration );
		
		// return
		return $response;
	}
	
	/**
	*  get_expiration
	*
	*  This function safely gets the expiration value from a response
	*
	*  @date	8/7/18
	*  @since	5.6.9
	*
	*  @param	mixed $response The response from the server. Default false.
	*  @param	int $min The minimum expiration limit. Default 0.
	*  @param	int $max The maximum expiration limit. Default 0.
	*  @return	int
	*/
	
	function get_expiration( $response = false, $min = 0, $max = 0 ) {
		
		// vars
		$expiration = 0;
		
		// check
		if( is_array($response) && isset($response['expiration']) ) {
			$expiration = (int) $response['expiration'];
		}
		
		// min
		if( $expiration < $min ) {
			return $min;
		}
		
		// max
		if( $expiration > $max ) {
			return $max;
		}
		
		// return
		return $expiration;
	}
	
	/*
	*  refresh_plugins_transient
	*
	*  This function will refresh plugin update info to the transient
	*
	*  @type	function
	*  @date	11/4/17
	*  @since	5.5.10
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function refresh_plugins_transient() {
		
		// vars
		$transient = get_site_transient('update_plugins');
		
		// bail early if no transient
		if( empty($transient) ) return;
		
		// update (will trigger modify function)
		$this->force_check = true;
		set_site_transient( 'update_plugins', $transient );
	}
	
	
	/*
	*  modify_plugins_transient
	*
	*  This function will connect to the ACF website and find update information
	*
	*  @type	function
	*  @date	16/01/2014
	*  @since	5.0.0
	*
	*  @param	$transient (object)
	*  @return	$transient
	*/
	
	function modify_plugins_transient( $transient ) {
		
		// bail early if no response (error)
		if( !isset($transient->response) ) {
			return $transient;
		}
		
		// fetch updates (this filter is called multiple times during a single page load)
		$updates = $this->get_plugin_updates();
		
		// append
		if( is_array($updates) ) {
			foreach( $updates['plugins'] as $basename => $update ) {
				$transient->response[ $basename ] = (object) $update;
			}
		}
		
		// return 
        return $transient;
	}
	
	
	/*
	*  modify_plugin_details
	*
	*  This function will populate the plugin data visible in the 'View details' popup
	*
	*  @type	function
	*  @date	17/01/2014
	*  @since	5.0.0
	*
	*  @param	$result (bool|object)
	*  @param	$action (string)
	*  @param	$args (object)
	*  @return	$result
	*/
	
	function modify_plugin_details( $result, $action = null, $args = null ) {
		
		// vars
		$plugin = false;
		
		
		// only for 'plugin_information' action
		if( $action !== 'plugin_information' ) return $result;
		
		
		// find plugin via slug
		foreach( $this->plugins as $p ) {
			
			if( $args->slug == $p['slug'] ) $plugin = $p;
			
		}
		
		
		// bail early if plugin not found
		if( !$plugin ) return $result;
		
		
		// connect
		$response = $this->get_plugin_info($plugin['id']);
		
		
		// bail early if no response
		if( !is_array($response) ) return $result;
		
		
		// remove tags (different context)
    	unset($response['tags']);
    	
    	
		// convert to object
    	$response = (object) $response;
    	
    	
		// sections
        $sections = array(
        	'description'		=> '',
        	'installation'		=> '',
        	'changelog'			=> '',
        	'upgrade_notice'	=> ''
        );
        
        foreach( $sections as $k => $v ) {
	        
	        $sections[ $k ] = $response->$k;
	        
        }
        
        $response->sections = $sections;
    	
    	
    	// return        
        return $response;
        
	}
		
}


/*
*  acf_updates
*
*  The main function responsible for returning the one true acf_updates instance to functions everywhere.
*  Use this function like you would a global variable, except without needing to declare the global.
*
*  Example: <?php $acf_updates = acf_updates(); ?>
*
*  @type	function
*  @date	9/4/17
*  @since	5.5.12
*
*  @param	n/a
*  @return	(object)
*/

function acf_updates() {

	global $acf_updates;
	
	if( !isset($acf_updates) ) {
	
		$acf_updates = new acf_updates();
		
	}
	
	return $acf_updates;
	
}


/*
*  acf_register_plugin_update
*
*  alias of acf_updates()->add_plugin()
*
*  @type	function
*  @date	12/4/17
*  @since	5.5.10
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_register_plugin_update( $plugin ) {
	
	acf_updates()->add_plugin( $plugin );
	
}


endif; // class_exists check

?>