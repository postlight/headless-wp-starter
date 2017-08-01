<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_admin_install_network') ) :

class acf_admin_install_network {

	/*
	*  __construct
	*
	*  A good place to add actions / filters
	*
	*  @type	function
	*  @date	11/08/13
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// actions
		add_action('network_admin_menu', array($this,'network_admin_menu'), 20);
		
	}
	
	
	/*
	*  network_admin_menu
	*
	*  This function will chck for available updates and add actions if needed
	*
	*  @type	function
	*  @date	2/04/2015
	*  @since	5.1.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function network_admin_menu() {
		
		// vars
		$prompt = false;
		
		
		// loop through sites and find updates
		$sites = acf_get_sites();
		
		if( $sites ) {
			
			foreach( $sites as $site ) {
				
				// switch blog
				switch_to_blog( $site['blog_id'] );
				
				
				// get site updates
				$updates = acf_get_db_updates();
				
				
				// restore
				restore_current_blog();
				
				
				if( $updates ) {
				
					$prompt = true;
					break;
					
				}
				
			}
			
		}
		
		
		// bail if no prompt
		if( !$prompt ) return;
		
		
		// actions
		add_action('network_admin_notices', array($this, 'network_admin_notices'), 1);
		
		
		// add page
		$page = add_submenu_page('index.php', __('Upgrade Database','acf'), __('Upgrade Database','acf'), acf_get_setting('capability'), 'acf-upgrade-network', array($this,'network_html'));
		
		
		// actions
		add_action('load-' . $page, array($this,'network_load'));
		
	}
	
	
	/*
	*  load
	*
	*  This function will look at the $_POST data and run any functions if needed
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function network_load() {
		
		// hide notice on this page 
		remove_action('network_admin_notices', array($this, 'network_admin_notices'), 1);
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
	}
	
	
	
	/*
	*  network_admin_notices
	*
	*  This function will render the update notice
	*
	*  @type	function
	*  @date	2/04/2015
	*  @since	5.1.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function network_admin_notices() {
			
		// view
		$view = array(
			'button_text'	=> __("Review sites & upgrade", 'acf'),
			'button_url'	=> network_admin_url('index.php?page=acf-upgrade-network'),
			'confirm'		=> false
		);
		
		
		// load view
		acf_get_view('install-notice', $view);
		
	}
	
	
	/*
	*  network_html
	*
	*  This function will render the HTML for the network upgrade page
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function network_html() {
		
		// vars
		$plugin_version = acf_get_setting('version');
		
		
		// loop through sites and find updates
		$sites = acf_get_sites();
		
		if( $sites ) {
			
			foreach( $sites as $i => $site ) {
				
				// switch blog
				switch_to_blog( $site['blog_id'] );
				
				
				// extra info
				$site['name'] = get_bloginfo('name');
				$site['url'] = home_url();
				
				
				// get site updates
				$site['updates'] = acf_get_db_updates();
				
				
				// get site version
				$site['acf_version'] = get_option('acf_version');
				
				
				// no value equals new instal
				if( !$site['acf_version'] ) {
					
					$site['acf_version'] = $plugin_version;
					
				}
				
				
				// update
				$sites[ $i ] = $site;
				
				
				// restore
				restore_current_blog();
				
			}
			
		}
		
		
		// view
		$view = array(
			'sites'				=> $sites,
			'plugin_version'	=> $plugin_version
		);
		
		
		// load view
		acf_get_view('install-network', $view);
		
	}
			
}

// initialize
new acf_admin_install_network();

endif; // class_exists check


/*
*  acf_get_sites
*
*  This function will return an array of site data
*
*  @type	function
*  @date	29/08/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_sites() {
	
	// vars
	$sites = array();
	
	
	// WP >= 4.6
	if( function_exists('get_sites') ) {
		
		$_sites = get_sites(array(
			'number' => 0
		));
		
		foreach( $_sites as $_site ) {
			
	        $_site = get_site( $_site );
	        $sites[] = $_site->to_array();
	        
	    }
		
	// WP < 4.6	
	} else {
		
		$sites = wp_get_sites(array(
			'limit' => 0
		));
		
	}
	
	
	// return
	return $sites;
	
}

?>