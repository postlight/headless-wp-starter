<?php 

if( !class_exists('acf_pro') ):

class acf_pro {
	
	/*
	*  __construct
	*
	*  
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		// update setting
		acf_update_setting( 'pro', true );
		acf_update_setting( 'name', __('Advanced Custom Fields PRO', 'acf') );
		

		// api
		acf_include('pro/api/api-pro.php');
		acf_include('pro/api/api-options-page.php');
		
		
		// updates
		acf_include('pro/core/updates.php');
			
			
		// admin
		if( is_admin() ) {
			
			// options page
			acf_include('pro/admin/options-page.php');
			
			// settings
			acf_include('pro/admin/settings-updates.php');
			
		}
		
		
		// actions
		add_action('init',										array($this, 'register_assets'));
		add_action('acf/include_field_types',					array($this, 'include_field_types'), 5);
		add_action('acf/input/admin_enqueue_scripts',			array($this, 'input_admin_enqueue_scripts'));
		add_action('acf/field_group/admin_enqueue_scripts',		array($this, 'field_group_admin_enqueue_scripts'));
		
	}
	
	
	/*
	*  include_field_types
	*
	*  description
	*
	*  @type	function
	*  @date	21/10/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function include_field_types() {
		
		acf_include('pro/fields/repeater.php');
		acf_include('pro/fields/flexible-content.php');
		acf_include('pro/fields/gallery.php');
		acf_include('pro/fields/clone.php');
		
	}
	
	
	/*
	*  register_assets
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function register_assets() {
		
		// min
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		
		// register scripts
		wp_register_script( 'acf-pro-input', acf_get_dir( "pro/assets/js/acf-pro-input{$min}.js" ), array('acf-input'), acf_get_setting('version') );
		wp_register_script( 'acf-pro-field-group', acf_get_dir( "pro/assets/js/acf-pro-field-group{$min}.js" ), array('acf-field-group'), acf_get_setting('version') );
		
		
		// register styles
		wp_register_style( 'acf-pro-input', acf_get_dir( 'pro/assets/css/acf-pro-input.css' ), false, acf_get_setting('version') ); 
		wp_register_style( 'acf-pro-field-group', acf_get_dir( 'pro/assets/css/acf-pro-field-group.css' ), false, acf_get_setting('version') ); 
		
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// scripts
		wp_enqueue_script('acf-pro-input');
	
	
		// styles
		wp_enqueue_style('acf-pro-input');
		
	}
	
	
	/*
	*  field_group_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function field_group_admin_enqueue_scripts() {
		
		// scripts
		wp_enqueue_script('acf-pro-field-group');
	
	
		// styles
		wp_enqueue_style('acf-pro-field-group');
		
	}
	 
}


// instantiate
new acf_pro();


// end class
endif;

?>