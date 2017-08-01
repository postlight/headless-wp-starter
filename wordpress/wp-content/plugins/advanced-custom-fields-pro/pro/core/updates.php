<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_pro_updates') ) :

class acf_pro_updates {
	

	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// filters
		add_filter('acf/get_remote_plugin_info', 			array($this, 'get_remote_plugin_info'), 10, 1);
		add_filter('acf/updates/plugin_details', 			array($this, 'plugin_details'), 10, 3);
		add_filter('acf/updates/plugin_update', 			array($this, 'plugin_update'), 10, 2);
		add_filter('acf/updates/plugin_update_message', 	array($this, 'plugin_update_message'), 10, 3);
			
	}
	
	
	/*
	*  get_remote_plugin_info
	*
	*  This function will return an array of data from the plugin's readme.txt file (remote)
	*  The data returned will be stored in a transient and used to display plugin update info
	*
	*  @type	function
	*  @date	8/06/2016
	*  @since	5.3.8
	*
	*  @param	$info (array)
	*  @return	$info
	*/
	
	function get_remote_plugin_info( $info ) {
		
		// vars
		$info = acf_pro_get_remote_response('get-info');
        
        
        // bail ealry if no info
        if( empty($info) ) return 0;
        
        
        // json decode
        $info = json_decode($info, true);
        
        
        // remove unused data to save DB transient space
		unset( $info['description'] );
		unset( $info['installation'] );
		unset( $info['tags'] );
	
	
        // return
		return $info;
		
	}
	
	
	/*
	*  plugin_details
	*
	*  This function will populate the plugin data visible in the 'View details' popup
	*
	*  @type	function
	*  @date	8/06/2016
	*  @since	5.3.8
	*
	*  @param	$result (bool|object)
	*  @param	$action (string)
	*  @param	$args (object)
	*  @return	$result
	*/
	
	function plugin_details( $result = false, $action = null, $args = null ) {
		
		// vars
		$slug = acf_get_setting('slug');
        $info = acf_pro_get_remote_response('get-info');
        
        
        // bail ealry if no info
        if( empty($info) ) return false;
        
        
        // json decode
        $info = json_decode($info);
        
        
        // sections
        $sections = array(
        	'description' => '',
        	'installation' => '',
        	'changelog' => '',
        	'upgrade_notice' => ''
        );
        
        foreach( $sections as $k => $v ) {
	        
	        $sections[ $k ] = $info->$k;
	        
	        unset( $info->$k );
	        
        }
        
        $info->sections = $sections;
        
        
    	// return        
        return $info;
		
	}
	
	
	/*
	*  plugin_update
	*
	*  This function will return an object of data saved in transient and used by WP do perform an update
	*
	*  @type	function
	*  @date	16/01/2014
	*  @since	5.0.0
	*
	*  @param	$update (object)
	*  @param	$transient (object)
	*  @return	$update
	*/
	
	function plugin_update( $update, $transient ) {
		
		// bail early if no update available
		if( !acf_pro_is_update_available() ) return false;
		
		
		// vars
		$info = acf_get_remote_plugin_info();
		$basename = acf_get_setting('basename');
		$slug = acf_get_setting('slug');
		
		
        // create new object for update
        $obj = new stdClass();
        $obj->slug = $slug;
        $obj->plugin = $basename;
        $obj->new_version = $info['version'];
        $obj->url = $info['homepage'];
        $obj->package = '';
        
        
        // license
		if( acf_pro_is_license_active() ) {
			
			$obj->package = acf_pro_get_remote_url('download', array(
				'k'				=> acf_pro_get_license_key(),
				'wp_url'		=> home_url(),
				'acf_version'	=> acf_get_setting('version'),
				'wp_version'	=> get_bloginfo('version'),
			));
		
		}
		
		
		// return 
        return $obj;
        
	}
	
	
	/*
	*  plugin_update_message
	*
	*  Displays an update message for plugin list screens.
	*
	*  @type	function
	*  @date	14/06/2016
	*  @since	5.3.8
	*
	*  @param	$message (string)
	*  @param	$plugin_data (array)
	*  @param	$r (object)
	*  @return	$message
	*/
	
	function plugin_update_message( $message, $plugin_data, $r ) {
		
		// validate
		if( acf_pro_is_license_active() ) return $message;
		
		
		// vars
		$activate_message = sprintf(
			__('To enable updates, please enter your license key on the <a href="%s">Updates</a> page. If you don\'t have a licence key, please see <a href="%s">details & pricing</a>.', 'acf'),
			admin_url('edit.php?post_type=acf-field-group&page=acf-settings-updates'),
			'https://www.advancedcustomfields.com/pro'
		);
		
		
		// return
		// override $message (if plugin is not active, there is no need to see upgrade_notice)
		return '<br />' . $activate_message;
		
	}
	
}


// initialize
new acf_pro_updates();

endif; // class_exists check

?>