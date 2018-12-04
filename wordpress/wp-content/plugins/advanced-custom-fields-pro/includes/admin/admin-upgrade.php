<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Admin_Upgrade') ) :

class ACF_Admin_Upgrade {
	
	/**
	*  __construct
	*
	*  Sets up the class functionality.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	void
	*/
	function __construct() {
		
		// actions
		add_action( 'admin_menu', 			array($this,'admin_menu'), 20 );
		add_action( 'network_admin_menu',	array($this,'network_admin_menu'), 20 );
	}
	
	/**
	*  admin_menu
	*
	*  Setus up logic if DB Upgrade is needed on a single site.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	void
	*  @return	void
	*/
	function admin_menu() {
		
		// check if upgrade is avaialble
		if( acf_has_upgrade() ) {
			
			// add notice
			add_action('admin_notices', array($this, 'admin_notices'));
			
			// add page
			$page = add_submenu_page('index.php', __('Upgrade Database','acf'), __('Upgrade Database','acf'), acf_get_setting('capability'), 'acf-upgrade', array($this,'admin_html') );
			
			// actions
			add_action('load-' . $page, array($this,'admin_load'));
		}
	}
	
	/**
	*  network_admin_menu
	*
	*  Setus up logic if DB Upgrade is needed on a multi site.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	void
	*  @return	void
	*/
	function network_admin_menu() {
		
		// vars
		$has_upgrade = false;
		
		// loop over sites
		$sites = acf_get_sites();
		if( $sites ) {
		foreach( $sites as $site ) {
				
			// switch blog
			switch_to_blog( $site['blog_id'] );
			
			// check for upgrade
			if( acf_has_upgrade() ) {
				$has_upgrade = true;
			}
			
			// restore blog
			restore_current_blog();
		}}
		
		// check if upgrade is avaialble
		if( $has_upgrade ) {
			
			// add notice
			add_action('network_admin_notices', array($this, 'network_admin_notices'));
			
			// add page
			$page = add_submenu_page('index.php', __('Upgrade Database','acf'), __('Upgrade Database','acf'), acf_get_setting('capability'), 'acf-upgrade-network', array($this,'network_admin_html'));
			
			// actions
			add_action('load-' . $page, array($this,'network_admin_load'));
		}
	}
	
	/**
	*  admin_load
	*
	*  Runs during the loading of the admin page.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	function admin_load() {
		
		// remove prompt 
		remove_action('admin_notices', array($this, 'admin_notices'));
		
		// load acf scripts
		acf_enqueue_scripts();
	}
	
	/**
	*  network_admin_load
	*
	*  Runs during the loading of the network admin page.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	function network_admin_load() {
		
		// remove prompt 
		remove_action('network_admin_notices', array($this, 'network_admin_notices'));
		
		// load acf scripts
		acf_enqueue_scripts();
	}
	
	/**
	*  admin_notices
	*
	*  Displays the DB Upgrade prompt.
	*
	*  @date	23/8/18
	*  @since	5.7.3
	*
	*  @param	void
	*  @return	void
	*/
	function admin_notices() {
		
		// vars
		$view = array(
			'button_text'	=> __("Upgrade Database", 'acf'),
			'button_url'	=> admin_url('index.php?page=acf-upgrade'),
			'confirm'		=> true
		);
		
		// view
		acf_get_view('html-notice-upgrade', $view);
	}
	
	/**
	*  network_admin_notices
	*
	*  Displays the DB Upgrade prompt on a multi site.
	*
	*  @date	23/8/18
	*  @since	5.7.3
	*
	*  @param	void
	*  @return	void
	*/
	function network_admin_notices() {
		
		// vars
		$view = array(
			'button_text'	=> __("Review sites & upgrade", 'acf'),
			'button_url'	=> network_admin_url('index.php?page=acf-upgrade-network'),
			'confirm'		=> false
		);
		
		// view
		acf_get_view('html-notice-upgrade', $view);
	}
	
	/**
	*  admin_html
	*
	*  Displays the HTML for the admin page.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	void
	*  @return	void
	*/
	function admin_html() {
		acf_get_view('html-admin-page-upgrade');
	}
	
	/**
	*  network_admin_html
	*
	*  Displays the HTML for the network upgrade admin page.
	*
	*  @date	24/8/18
	*  @since	5.7.4
	*
	*  @param	void
	*  @return	void
	*/
	function network_admin_html() {
		acf_get_view('html-admin-page-upgrade-network');
	}
}

// instantiate
acf_new_instance('ACF_Admin_Upgrade');

endif; // class_exists check

?>