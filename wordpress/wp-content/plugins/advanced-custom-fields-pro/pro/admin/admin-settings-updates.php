<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_admin_settings_updates') ) :

class acf_admin_settings_updates {
	
	// vars
	var $view = array();
	
	
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
	
		// actions
		add_action('admin_menu', array($this, 'admin_menu'), 20 );
		
	}
	
	
	/*
	*  show_notice
	*
	*  This function will show a notice (only once)
	*
	*  @type	function
	*  @date	11/4/17
	*  @since	5.5.10
	*
	*  @param	$message (string)
	*  @param	class (string)
	*  @return	n/a
	*/
	
	function show_notice( $message = '', $class = '' ){
		
		// only show one notice
		if( acf_has_done('acf_admin_settings_updates_notice') ) return false;
		
		
		// add notice
    	acf_add_admin_notice( $message, $class );
			
	}
	
	
	/*
	*  show_error
	*
	*  This function will show an error notice (only once)
	*
	*  @type	function
	*  @date	11/4/17
	*  @since	5.5.10
	*
	*  @param	$error (mixed)
	*  @return	n/a
	*/
	
	function show_error( $error = '' ){
		
	    // error object
    	if( is_wp_error($error) ) {
        	
        	$error = __('<b>Error</b>. Could not connect to update server', 'acf') . ' <span class="description">(' . $error->get_error_message() . ')</span>';
        	
    	}
    	
    	
    	// add notice
    	$this->show_notice( $error, 'error' );
			
	}
	
	
	/*
	*  get_changelog_section
	*
	*  This function will find and return a section of content from a plugin changelog
	*
	*  @type	function
	*  @date	11/4/17
	*  @since	5.5.10
	*
	*  @param	$changelog (string)
	*  @param	$h4 (string)
	*  @return	(string)
	*/
	
	function get_changelog_section( $changelog, $h4 = '' ) {
		
		// explode
		$bits = array_filter( explode('<h4>', $changelog) );
		
		
		// loop
		foreach( $bits as $bit ) {
			
			// vars
			$bit = explode('</h4>', $bit);
			$version = trim($bit[0]);
	    	$text = trim($bit[1]);
			
			
			// is relevant?
	    	if( version_compare($h4, $version, '==') ) {
	        	
	        	return '<h4>' . $version . '</h4>' . $text;
	        	
	    	}
	    	
		}
		
		
		// update
		return '';
		
	}
	
	
	/*
	*  admin_menu
	*
	*  This function will add the ACF menu item to the WP admin
	*
	*  @type	action (admin_menu)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_menu() {
		
		// bail early if no show_admin
		if( !acf_get_setting('show_admin') ) return;
		
		
		// bail early if no show_updates
		if( !acf_get_setting('show_updates') ) return;
		
		
		// bail early if not a plugin (included in theme)
		if( !acf_is_plugin_active() ) return;
				
		
		// add page
		$page = add_submenu_page('edit.php?post_type=acf-field-group', __('Updates','acf'), __('Updates','acf'), acf_get_setting('capability'), 'acf-settings-updates', array($this,'html') );
		
		
		// actions
		add_action('load-' . $page, array($this,'load'));
		
	}
	
	
	/*
	*  load
	*
	*  description
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function load() {
		
		// activate
		if( acf_verify_nonce('activate_pro_licence') ) {
		
			$this->activate_pro_licence();
		
		// deactivate	
		} elseif( acf_verify_nonce('deactivate_pro_licence') ) {
		
			$this->deactivate_pro_licence();
			
		}
		
		
		// vars
		$license = acf_pro_get_license_key();
		$this->view = array(
			'license'			=> $license,
			'active'			=> $license ? 1 : 0,
			'current_version'	=> acf_get_setting('version'),
			'remote_version'	=> '',
			'update_available'	=> false,
			'changelog'			=> '',
			'upgrade_notice'	=> ''
		);
		
		
		// vars
		$info = acf_updates()->get_plugin_info('pro');
		
		
		// error
		if( is_wp_error($info) ) {
			
			return $this->show_error( $info );
			
		}
        
        
        // add info to view
        $this->view['remote_version'] = $info['version'];
        
        
        // add changelog if the remote version is '>' than the current version
        $version = acf_get_setting('version');
	 
		
	    // check if remote version is higher than current version
		if( version_compare($info['version'], $version, '>') ) {
			
			// update view
        	$this->view['update_available'] = true;
        	$this->view['changelog'] = $this->get_changelog_section($info['changelog'], $info['version']);
        	$this->view['upgrade_notice'] = $this->get_changelog_section($info['upgrade_notice'], $info['version']);
        	
        	
        	// refresh transient
        	// - avoids new version not available in plugin update list
        	// - only request if license is active
        	if( $license ) {
	        	
	        	acf_updates()->refresh_plugins_transient();	
	        	
        	}

        }
		
	}
	
	
	/*
	*  activate_pro_licence
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
	
	function activate_pro_licence() {
		
		// connect
		$post = array(
			'acf_license'	=> $_POST['acf_pro_licence'],
			'acf_version'	=> acf_get_setting('version'),
			'wp_name'		=> get_bloginfo('name'),
			'wp_url'		=> home_url(),
			'wp_version'	=> get_bloginfo('version'),
			'wp_language'	=> get_bloginfo('language'),
			'wp_timezone'	=> get_option('timezone_string'),
		);
		
		
		// connect
		$response = acf_updates()->request('v2/plugins/activate?p=pro', $post);
		
		
		// error
		if( is_wp_error($response) ) {
			
			return $this->show_error( $response );
			
		}
		
		
		// success
		if( $response['status'] == 1 ) {
			
			// update license
			acf_pro_update_license( $response['license'] );
			
			
			// show message
			$this->show_notice( $response['message'] );
			
		} else {
			
			// show error
			$this->show_error( $response['message'] );
			
		}
		
	}
	
	
	/*
	*  deactivate_pro_licence
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
	
	function deactivate_pro_licence() {
		
		// vars
		$license = acf_pro_get_license_key();
		
		
		// bail early if no key
		if( !$license ) return;
		
		
		// connect
		$post = array(
			'acf_license'	=> $license,
			'wp_url'		=> home_url(),
		);
		
		
		// connect
		$response = acf_updates()->request('v2/plugins/deactivate?p=pro', $post);
		
		
		// error
		if( is_wp_error($response) ) {
			
			return $this->show_error( $response );
			
		}
		
		
		// clear DB
		acf_pro_update_license('');
		
		
		// success
		if( $response['status'] == 1 ) {
			
			// show message
			$this->show_notice( $response['message'] );
			
		} else {
			
			// show error
			$this->show_error( $response['message'] );
			
		}
		
	}
	
	
	/*
	*  html
	*
	*  description
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function html() {
		
		// load view
		acf_get_view( dirname(__FILE__) . '/views/html-settings-updates.php', $this->view);
		
	}
	
}


// initialize
new acf_admin_settings_updates();

endif; // class_exists check

?>