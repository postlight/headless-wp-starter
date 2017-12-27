<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_updates') ) :

class acf_updates {
	
	// vars
	var $version = '2.2',
		$plugins = array(),
		$updates = false,
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
		
		
		// bail early if not active plugin (included in theme)
		if( !is_plugin_active($plugin['basename']) ) return;
		
		
		// add custom message in plugin update row
		// removed: decided this message will have a negative impact on user
		// if( is_admin() ) {
		//	
		//	 add_action('in_plugin_update_message-' . $plugin['basename'], array($this, 'modify_plugin_update_message'), 10, 2 );
		//	
		// }
		
			
		// append
		$this->plugins[ $plugin['basename'] ] = $plugin;
		
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
		
		
		// test
		if( $this->dev ) $url = 'http://connect/' . $query;
		
		
		// log
		//acf_log('acf connect: '. $url, $body);
		
		
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
		$transient_name = 'acf_plugin_info_'.$id;
		
		
		// delete transient (force-check is used to refresh)
		if( !empty($_GET['force-check']) ) {
		
			delete_transient($transient_name);
			
		}
	
	
		// try transient
		$transient = get_transient($transient_name);
		if( $transient !== false ) return $transient;
		
		
		// connect
		$response = $this->request('v2/plugins/get-info?p='.$id);
		
		
		// ensure response is expected JSON array (not string)
		if( is_string($response) ) {
			$response = new WP_Error( 'server_error', esc_html($response) );
		}
		
		
		// update transient
		set_transient($transient_name, $response, HOUR_IN_SECONDS );
		
		
		// return
		return $response;
		
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
		if( !isset($transient->response) ) return $transient;
		
		
		// fetch updates once (this filter is called multiple times during a single page load)
		if( !$this->updates ) {
			
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
					
			
			// connect
			$this->updates = $this->request('v2/plugins/update-check', $post);
			
		}
		
		
		// append
		if( is_array($this->updates) ) {
			
			foreach( $this->updates['plugins'] as $basename => $update ) {
				
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
	
	
	/*
	*  modify_plugin_update_message
	*
	*  Displays an update message for plugin list screens.
	*  Shows only the version updates from the current until the newest version
	*
	*  @type	function
	*  @date	14/06/2016
	*  @since	5.3.8
	*
	*  @param	$plugin_data (array)
	*  @param	$r (object)
	*  @return	n/a
	*/

/*
	function modify_plugin_update_message( $plugin_data, $response ) {
		
		// show notice if exists in transient data
		if( isset($response->notice) ) {
			
			echo '<div class="acf-plugin-upgrade-notice">' . $response->notice . '</div>';
			
		}
	
	}
*/
		
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