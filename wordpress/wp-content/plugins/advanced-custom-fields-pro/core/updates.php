<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_updates') ) :

class acf_updates {
	
	
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
		
		// append plugin information
		// Note: is_admin() was used previously, however this prevents jetpack manage & ManageWP from working
	    add_filter('plugins_api', array($this, 'modify_plugin_details'), 20, 3);
	    
	    
		// append update information
		add_filter('pre_set_site_transient_update_plugins', array($this, 'modify_plugin_update'));
		
		
		// add custom message when PRO not activated but update available
		add_action('in_plugin_update_message-' . acf_get_setting('basename'), array($this, 'modify_plugin_update_message'), 10, 2 );
		
	}
	
	
	/*
	*  modify_plugin_information
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
		$slug = acf_get_setting('slug');
        
		
		// validate
    	if( isset($args->slug) && $args->slug === $slug && acf_is_plugin_active() ) {
	    	
	    	// filter
	    	$result = apply_filters('acf/updates/plugin_details', $result, $action, $args);
		    
    	}
    	
    	
    	// return        
        return $result;
        
	}
	
	
	/*
	*  modify_plugin_update_information
	*
	*  This function will connect to the ACF website and find release details
	*
	*  @type	function
	*  @date	16/01/2014
	*  @since	5.0.0
	*
	*  @param	$transient (object)
	*  @return	$transient
	*/
	
	function modify_plugin_update( $transient ) {
		
		// bail early if no response (dashboard showed an error)
		if( !isset($transient->response) ) return $transient;
		
		
		// vars
		$basename = acf_get_setting('basename');
		$show_updates = acf_get_setting('show_updates');
		
		
		// bail early if not a plugin (included in theme)
		if( !acf_is_plugin_active() ) $show_updates = false;
		
		
		// bail early if no show_updates
		if( !$show_updates ) {
			
			// remove from transient
			unset( $transient->response[ $basename ] );
			
			
			// return
			return $transient;
			
		}
		
		
		// get update
		$update = acf_maybe_get( $transient->response, $basename );
		
		
		// filter
		$update = apply_filters('acf/updates/plugin_update', $update, $transient);
		
		
		// update
		if( $update ) {
			
			$transient->response[ $basename ] = $update;
			
		} else {
			
			unset($transient->response[ $basename ]);
			
		}
		
		
		
		// return 
        return $transient;
        
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

	function modify_plugin_update_message( $plugin_data, $r ) {
		
		// vars
		$message = '';
		$info = acf_get_remote_plugin_info();
		
		
		// check for upgrade notice
		if( $info['upgrade_notice'] ) {
			
			$message = '<div class="acf-plugin-upgrade-notice">' . $info['upgrade_notice'] . '</div>';
			
		}
		
		
		// filter
		$message = apply_filters('acf/updates/plugin_update_message', $message, $plugin_data, $r );
		
		
		// return
		echo $message;
	
	}
		
}

// initialize
acf()->updates = new acf_updates();

endif; // class_exists check



/*
*  acf_get_remote_plugin_info
*
*  This function will return an array of data from the plugin's readme.txt file (remote)
*
*  @type	function
*  @date	8/06/2016
*  @since	5.3.9
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_remote_plugin_info() {
	
	// vars
	$transient_name = 'acf_get_remote_plugin_info';
		
	
	// clear transient if force check is enabled
	if( !empty($_GET['force-check']) ) {
		
		// only allow transient to be deleted once per page load
		if( empty($_GET['acf-ignore-force-check']) ) {
			
			delete_transient( 'acf_get_remote_plugin_info' );
			
		}
		
		
		// update $_GET
		$_GET['acf-ignore-force-check'] = true;
		
	}
	
	
	// get transient
	$transient = get_transient( $transient_name );
	
	// fake
/*
	if( $transient ) {
		
		$transient['upgrade_notice'] .= '<h4>5.3.8.1</h4><ul><li>This update will do this</li><li>and that</li></ul>';
		
	}
*/
	
	
	// bail early if transiente exists
	if( $transient !== false ) return $transient;
	
	
	// allow bypass
	$info = apply_filters( 'acf/get_remote_plugin_info', false );

	if( $info === false ) {
		
		$info = acf_get_wporg_remote_plugin_info();
			
	}
	
	
	// store only relevant changelog / upgrade notice
	foreach( array('changelog', 'upgrade_notice') as $k ) {
		
		// bail early if not set
		if( empty($info[ $k ]) ) continue;
		
		
		// vars
		$new = '';
		$orig = $info[ $k ];
		
		
		// explode
		$bits = array_filter( explode('<h4>', $orig) );
		
		
		// loop
		foreach( $bits as $bit ) {
			
			// vars
			$bit = explode('</h4>', $bit);
			$version = trim($bit[0]);
	    	$text = trim($bit[1]);
			
			
			// is relevant?
	    	if( version_compare($info['version'], $version, '==') ) {
	        	
	        	$new = '<h4>' . $version . '</h4>' . $text;
	        	break;
	        	
	    	}
	    	
		}
		
		
		// update
		$info[ $k ] = $new;
		
	}
	
	
	// allow transient to save empty
	if( empty($info) ) $info = 0;
	
	
	// update transient
	set_transient( $transient_name, $info, DAY_IN_SECONDS );
	
	
	// return
	return $info;
	
}


/*
*  acf_get_wporg_remote_plugin_info
*
*  This function will return an array of data from the wordpress.org plugin's readme.txt file (remote)
*
*  @type	function
*  @date	8/06/2016
*  @since	5.3.9
*
*  @param	n/a
*  @return	(array)
*/


function acf_get_wporg_remote_plugin_info() {
	
	// create basic version of plugin info.
	// this should replicate the data available via plugin_api()
	// doing so allows ACF PRO to load data from external source
	$info = array(
		'name'				=> acf_get_setting('name'),
		'slug'				=> acf_get_setting('slug'),
		'version'			=> acf_get_setting('version'),
		'changelog'			=> '',
		'upgrade_notice'	=> ''
	);
	
	
	// get readme
	$response = wp_safe_remote_get('https://plugins.svn.wordpress.org/advanced-custom-fields/trunk/readme.txt');
	
	
	// bail early if no response
	if( is_wp_error($response) || empty($response['body']) ) return $info;
	
		
	// use regex to find upgrade notice
	$matches = null;
	$regexp = '/(== Upgrade Notice ==)([\s\S]+?)(==|$)/';
	
	
	// bail early if no match
	if( !preg_match($regexp, $response['body'], $matches) ) return $info;
	
	
	// convert to html
	$text = wp_kses_post( trim($matches[2]) );
	
	
	// pretify
	$text = preg_replace('/^= (.*?) =/m', '<h4>$1</h4>', $text);
	$text = preg_replace('/^[\*] (.*?)(\n|$)/m', '<li>$1</li>', $text);
	$text = preg_replace('/\n<li>(.*?)/', "\n" . '<ul><li>$1', $text);
	$text = preg_replace('/(<\/li>)(?!<li>)/', '$1</ul>' . "\n", $text);
	
	
	// update
	$info['upgrade_notice'] = $text;
	
	
	// return
	return $info;
	
}


/*
*  acf_refresh_plugin_updates_transient
*
*  This function will refresh teh WP transient containing plugin update data
*
*  @type	function
*  @date	11/08/2016
*  @since	5.4.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_refresh_plugin_updates_transient() {
	
	// vars
	$transient = get_site_transient('update_plugins');
	
	
	// bail early if no transient
	if( empty($transient) ) return;
	
	
	// update transient
	$transient = acf()->updates->modify_plugin_update( $transient );
	
	
	// update
	set_site_transient( 'update_plugins', $transient );
		
}


?>