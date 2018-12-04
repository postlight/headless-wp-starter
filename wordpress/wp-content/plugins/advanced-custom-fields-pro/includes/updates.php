<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Updates') ) :

class ACF_Updates {
	
	/** @var string The ACF_Updates version */
	var $version = '2.4';
	
	/** @var array The array of registered plugins */
	var $plugins = array();
	
	/** @var boolean Dev mode */
	var $dev = false;	
	
	/** @var int Counts the number of plugin update checks */
	var $checked = 0;	
	
	/*
	*  __construct
	*
	*  Sets up the class functionality.
	*
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	void
	*  @return	void
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
	*  Registeres a plugin for updates.
	*
	*  @date	8/4/17
	*  @since	5.5.10
	*
	*  @param	array $plugin The plugin array.
	*  @return	void
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
	
	/**
	*  get_plugin_by
	*
	*  Returns a registered plugin for the give key and value.
	*
	*  @date	3/8/18
	*  @since	5.7.2
	*
	*  @param	string $key The array key to compare
	*  @param	string $value The value to compare against
	*  @return	array|false
	*/
	
	function get_plugin_by( $key = '', $value = null ) {
		foreach( $this->plugins as $plugin ) {
			if( $plugin[$key] === $value ) {
				return $plugin;
			}
		}
		return false;
	}
	
	/*
	*  request
	*
	*  Makes a request to the ACF connect server.
	*
	*  @date	8/4/17
	*  @since	5.5.10
	*
	*  @param	string $query The api path. Defaults to 'index.php'
	*  @param	array $body The body to post
	*  @return	array|string|WP_Error
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
	*  Returns update information for the given plugin id.
	*
	*  @date	9/4/17
	*  @since	5.5.10
	*
	*  @param	string $id The plugin id such as 'pro'.
	*  @param	boolean $force_check Bypasses cached result. Defaults to false.
	*  @return	array|WP_Error
	*/
	
	function get_plugin_info( $id = '', $force_check = false ) {
		
		// var
		$transient_name = 'acf_plugin_info_' . $id;
		
		// check cache but allow for $force_check override
		if( !$force_check ) {
			$transient = get_transient( $transient_name );
			if( $transient !== false ) {
				return $transient;
			}
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
		set_transient( $transient_name, $response, $expiration );
		
		// return
		return $response;
	}
	
	/**
	*  get_plugin_update
	*
	*  Returns specific data from the 'update-check' response.
	*
	*  @date	3/8/18
	*  @since	5.7.2
	*
	*  @param	string $basename The plugin basename.
	*  @param	boolean $force_check Bypasses cached result. Defaults to false
	*  @return	array
	*/
	
	function get_plugin_update( $basename = '', $force_check = false ) {
		
		// get updates
		$updates = $this->get_plugin_updates( $force_check );
		
		// check for and return update
		if( is_array($updates) && isset($updates['plugins'][ $basename ]) ) {
			return $updates['plugins'][ $basename ];
		}
		return false;
	}
	
	
	/**
	*  get_plugin_updates
	*
	*  Checks for plugin updates.
	*
	*  @date	8/7/18
	*  @since	5.6.9
	*  @since	5.7.2 Added 'checked' comparison
	*
	*  @param	boolean $force_check Bypasses cached result. Defaults to false.
	*  @return	array|WP_Error.
	*/
	
	function get_plugin_updates( $force_check = false ) {
		
		// var
		$transient_name = 'acf_plugin_updates';
		
		// construct array of 'checked' plugins
		// sort by key to avoid detecting change due to "include order"
		$checked = array();
		foreach( $this->plugins as $basename => $plugin ) {
			$checked[ $basename ] = $plugin['version'];
		}
		ksort($checked);
		
		// $force_check prevents transient lookup
		if( !$force_check ) {
			$transient = get_transient($transient_name);

			// if cached response was found, compare $transient['checked'] against $checked and ignore if they don't match (plugins/versions have changed)
			if( is_array($transient) ) {
				$transient_checked = isset($transient['checked']) ? $transient['checked'] : array();
				if( wp_json_encode($checked) !== wp_json_encode($transient_checked) ) {
					$transient = false;
				}
			}
			if( $transient !== false ) {
				return $transient;
			}
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
		
		// append checked reference
		if( is_array($response) ) {
			$response['checked'] = $checked;
		}
		
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
	*  This function safely gets the expiration value from a response.
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
	*  Deletes transients and allows a fresh lookup.
	*
	*  @date	11/4/17
	*  @since	5.5.10
	*
	*  @param	void
	*  @return	void
	*/
	
	function refresh_plugins_transient() {
		delete_site_transient('update_plugins');
		delete_transient('acf_plugin_updates');
	}
	
	/*
	*  modify_plugins_transient
	*
	*  Called when WP updates the 'update_plugins' site transient. Used to inject ACF plugin update info.
	*
	*  @date	16/01/2014
	*  @since	5.0.0
	*
	*  @param	object $transient
	*  @return	$transient
	*/
	
	function modify_plugins_transient( $transient ) {
		
		// bail early if no response (error)
		if( !isset($transient->response) ) {
			return $transient;
		}
		
		// force-check (only once)
		$force_check = ($this->checked == 0) ? !empty($_GET['force-check']) : false;
		
		// fetch updates (this filter is called multiple times during a single page load)
		$updates = $this->get_plugin_updates( $force_check );
		
		// append
		if( is_array($updates) ) {
			foreach( $updates['plugins'] as $basename => $update ) {
				$transient->response[ $basename ] = (object) $update;
			}
		}
		
		// increase
		$this->checked++;
		
		// return 
        return $transient;
	}
	
	/*
	*  modify_plugin_details
	*
	*  Returns the plugin data visible in the 'View details' popup
	*
	*  @date	17/01/2014
	*  @since	5.0.0
	*
	*  @param	object $result
	*  @param	string $action
	*  @param	object $args
	*  @return	$result
	*/
	
	function modify_plugin_details( $result, $action = null, $args = null ) {
		
		// vars
		$plugin = false;
		
		// only for 'plugin_information' action
		if( $action !== 'plugin_information' ) return $result;
		
		// find plugin via slug
		$plugin = $this->get_plugin_by('slug', $args->slug);
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
*  @date	9/4/17
*  @since	5.5.12
*
*  @param	void
*  @return	object
*/

function acf_updates() {
	global $acf_updates;
	if( !isset($acf_updates) ) {
		$acf_updates = new ACF_Updates();
	}
	return $acf_updates;
}


/*
*  acf_register_plugin_update
*
*  Alias of acf_updates()->add_plugin().
*
*  @type	function
*  @date	12/4/17
*  @since	5.5.10
*
*  @param	array $plugin
*  @return	void
*/

function acf_register_plugin_update( $plugin ) {
	acf_updates()->add_plugin( $plugin );	
}

endif; // class_exists check

?>