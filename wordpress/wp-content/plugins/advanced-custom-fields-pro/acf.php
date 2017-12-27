<?php
/*
Plugin Name: Advanced Custom Fields PRO
Plugin URI: https://www.advancedcustomfields.com/
Description: Customise WordPress with powerful, professional and intuitive fields.
Version: 5.6.7
Author: Elliot Condon
Author URI: http://www.elliotcondon.com/
Copyright: Elliot Condon
Text Domain: acf
Domain Path: /lang
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf') ) :

class acf {
	
	/** @var string The plugin version number */
	var $version = '5.6.7';
	
	
	/** @var array The plugin settings array */
	var $settings = array();
	
	
	/*
	*  __construct
	*
	*  A dummy constructor to ensure ACF is only initialized once
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		/* Do nothing here */
		
	}
	
	
	/*
	*  initialize
	*
	*  The real constructor to initialize ACF
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		
	function initialize() {
		
		// vars
		$this->settings = array(
			
			// basic
			'name'				=> __('Advanced Custom Fields', 'acf'),
			'version'			=> $this->version,
						
			// urls
			'file'				=> __FILE__,
			'basename'			=> plugin_basename( __FILE__ ),
			'path'				=> plugin_dir_path( __FILE__ ),
			'dir'				=> plugin_dir_url( __FILE__ ),
			
			// options
			'show_admin'				=> true,
			'show_updates'				=> true,
			'stripslashes'				=> false,
			'local'						=> true,
			'json'						=> true,
			'save_json'					=> '',
			'load_json'					=> array(),
			'default_language'			=> '',
			'current_language'			=> '',
			'capability'				=> 'manage_options',
			'uploader'					=> 'wp',
			'autoload'					=> false,
			'l10n'						=> true,
			'l10n_textdomain'			=> '',
			'google_api_key'			=> '',
			'google_api_client'			=> '',
			'enqueue_google_maps'		=> true,
			'enqueue_select2'			=> true,
			'enqueue_datepicker'		=> true,
			'enqueue_datetimepicker'	=> true,
			'select2_version'			=> 4,
			'row_index_offset'			=> 1,
			'remove_wp_meta_box'		=> true
		);
		
		
		// constants
		$this->define( 'ACF', 			true );
		$this->define( 'ACF_VERSION', 	$this->settings['version'] );
		$this->define( 'ACF_PATH', 		$this->settings['path'] );
		
		
		// api
		include_once( ACF_PATH . 'includes/api/api-helpers.php');
		acf_include('includes/api/api-input.php');
		acf_include('includes/api/api-value.php');
		acf_include('includes/api/api-field.php');
		acf_include('includes/api/api-field-group.php');
		acf_include('includes/api/api-template.php');
		
		
		// fields
		acf_include('includes/fields.php');
		acf_include('includes/fields/class-acf-field.php');
				
		
		// locations
		acf_include('includes/locations.php');
		acf_include('includes/locations/class-acf-location.php');
		
		
		// core
		acf_include('includes/ajax.php');
		acf_include('includes/cache.php');
		acf_include('includes/compatibility.php');
		acf_include('includes/deprecated.php');
		acf_include('includes/input.php');
		acf_include('includes/json.php');
		acf_include('includes/local.php');
		acf_include('includes/loop.php');
		acf_include('includes/media.php');
		acf_include('includes/revisions.php');
		acf_include('includes/third_party.php');
		acf_include('includes/updates.php');
		acf_include('includes/validation.php');
		
		
		// forms
		acf_include('includes/forms/form-attachment.php');
		acf_include('includes/forms/form-comment.php');
		acf_include('includes/forms/form-customizer.php');
		acf_include('includes/forms/form-front.php');
		acf_include('includes/forms/form-nav-menu.php');
		acf_include('includes/forms/form-post.php');
		acf_include('includes/forms/form-taxonomy.php');
		acf_include('includes/forms/form-user.php');
		acf_include('includes/forms/form-widget.php');
		
		
		// admin
		if( is_admin() ) {
			
			acf_include('includes/admin/admin.php');
			acf_include('includes/admin/admin-field-group.php');
			acf_include('includes/admin/admin-field-groups.php');
			acf_include('includes/admin/install.php');
			acf_include('includes/admin/admin-tools.php');
			acf_include('includes/admin/settings-info.php');
			
			
			// network
			if( is_network_admin() ) {
				
				acf_include('includes/admin/install-network.php');
				
			}
		}
		
		
		// pro
		acf_include('pro/acf-pro.php');
		
		
		// actions
		add_action('init',	array($this, 'init'), 5);
		add_action('init',	array($this, 'register_post_types'), 5);
		add_action('init',	array($this, 'register_post_status'), 5);
		add_action('init',	array($this, 'register_assets'), 5);
		
		
		// filters
		add_filter('posts_where',		array($this, 'posts_where'), 10, 2 );
		//add_filter('posts_request',	array($this, 'posts_request'), 10, 1 );
		
	}
	
	
	/*
	*  init
	*
	*  This function will run after all plugins and theme functions have been included
	*
	*  @type	action (init)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function init() {
		
		// bail early if too early
		// ensures all plugins have a chance to add fields, etc
		if( !did_action('plugins_loaded') ) return;
		
		
		// bail early if already init
		if( acf_has_done('init') ) return;
		
		
		// vars
		$major = intval( acf_get_setting('version') );
		
		
		// redeclare dir
		// - allow another plugin to modify dir (maybe force SSL)
		acf_update_setting('dir', plugin_dir_url( __FILE__ ));
		
		
		// textdomain
		$this->load_plugin_textdomain();
		
		
		// include wpml support
		if( defined('ICL_SITEPRESS_VERSION') ) {
			acf_include('includes/wpml.php');
		}
		
		
		// fields
		acf_include('includes/fields/class-acf-field-text.php');
		acf_include('includes/fields/class-acf-field-textarea.php');
		acf_include('includes/fields/class-acf-field-number.php');
		acf_include('includes/fields/class-acf-field-range.php');
		acf_include('includes/fields/class-acf-field-email.php');
		acf_include('includes/fields/class-acf-field-url.php');
		acf_include('includes/fields/class-acf-field-password.php');
		
		acf_include('includes/fields/class-acf-field-image.php');
		acf_include('includes/fields/class-acf-field-file.php');
		acf_include('includes/fields/class-acf-field-wysiwyg.php');
		acf_include('includes/fields/class-acf-field-oembed.php');
		
		acf_include('includes/fields/class-acf-field-select.php');
		acf_include('includes/fields/class-acf-field-checkbox.php');
		acf_include('includes/fields/class-acf-field-radio.php');
		acf_include('includes/fields/class-acf-field-button-group.php');
		acf_include('includes/fields/class-acf-field-true_false.php');
		
		acf_include('includes/fields/class-acf-field-link.php');
		acf_include('includes/fields/class-acf-field-post_object.php');
		acf_include('includes/fields/class-acf-field-page_link.php');
		acf_include('includes/fields/class-acf-field-relationship.php');
		acf_include('includes/fields/class-acf-field-taxonomy.php');
		acf_include('includes/fields/class-acf-field-user.php');
		
		acf_include('includes/fields/class-acf-field-google-map.php');
		acf_include('includes/fields/class-acf-field-date_picker.php');
		acf_include('includes/fields/class-acf-field-date_time_picker.php');
		acf_include('includes/fields/class-acf-field-time_picker.php');
		acf_include('includes/fields/class-acf-field-color_picker.php');
		
		acf_include('includes/fields/class-acf-field-message.php');
		acf_include('includes/fields/class-acf-field-accordion.php');
		acf_include('includes/fields/class-acf-field-tab.php');
		acf_include('includes/fields/class-acf-field-group.php');
		do_action('acf/include_field_types', $major);
		
		
		// locations
		acf_include('includes/locations/class-acf-location-post-type.php');
		acf_include('includes/locations/class-acf-location-post-template.php');
		acf_include('includes/locations/class-acf-location-post-status.php');
		acf_include('includes/locations/class-acf-location-post-format.php');
		acf_include('includes/locations/class-acf-location-post-category.php');
		acf_include('includes/locations/class-acf-location-post-taxonomy.php');
		acf_include('includes/locations/class-acf-location-post.php');
		acf_include('includes/locations/class-acf-location-page-template.php');
		acf_include('includes/locations/class-acf-location-page-type.php');
		acf_include('includes/locations/class-acf-location-page-parent.php');
		acf_include('includes/locations/class-acf-location-page.php');
		acf_include('includes/locations/class-acf-location-current-user.php');
		acf_include('includes/locations/class-acf-location-current-user-role.php');
		acf_include('includes/locations/class-acf-location-user-form.php');
		acf_include('includes/locations/class-acf-location-user-role.php');
		acf_include('includes/locations/class-acf-location-taxonomy.php');
		acf_include('includes/locations/class-acf-location-attachment.php');
		acf_include('includes/locations/class-acf-location-comment.php');
		acf_include('includes/locations/class-acf-location-widget.php');
		acf_include('includes/locations/class-acf-location-nav-menu.php');
		acf_include('includes/locations/class-acf-location-nav-menu-item.php');
		do_action('acf/include_location_rules', $major);
		
		
		// local fields
		do_action('acf/include_fields', $major);
		
		
		// action for 3rd party
		do_action('acf/init');
			
	}
	
	
	/*
	*  load_plugin_textdomain
	*
	*  This function will load the textdomain file
	*
	*  @type	function
	*  @date	3/5/17
	*  @since	5.5.13
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function load_plugin_textdomain() {
		
		// vars
		$domain = 'acf';
		$locale = apply_filters( 'plugin_locale', acf_get_locale(), $domain );
		$mofile = $domain . '-' . $locale . '.mo';
		
		
		// load from the languages directory first
		load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );
		
		
		// redirect missing translations
		$mofile = str_replace('fr_CA', 'fr_FR', $mofile);
		
		
		// load from plugin lang folder
		load_textdomain( $domain, acf_get_path( 'lang/' . $mofile ) );
		
	}
	
	
	/*
	*  register_post_types
	*
	*  This function will register post types and statuses
	*
	*  @type	function
	*  @date	22/10/2015
	*  @since	5.3.2
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function register_post_types() {
		
		// vars
		$cap = acf_get_setting('capability');
		
		
		// register post type 'acf-field-group'
		register_post_type('acf-field-group', array(
			'labels'			=> array(
			    'name'					=> __( 'Field Groups', 'acf' ),
				'singular_name'			=> __( 'Field Group', 'acf' ),
			    'add_new'				=> __( 'Add New' , 'acf' ),
			    'add_new_item'			=> __( 'Add New Field Group' , 'acf' ),
			    'edit_item'				=> __( 'Edit Field Group' , 'acf' ),
			    'new_item'				=> __( 'New Field Group' , 'acf' ),
			    'view_item'				=> __( 'View Field Group', 'acf' ),
			    'search_items'			=> __( 'Search Field Groups', 'acf' ),
			    'not_found'				=> __( 'No Field Groups found', 'acf' ),
			    'not_found_in_trash'	=> __( 'No Field Groups found in Trash', 'acf' ), 
			),
			'public'			=> false,
			'show_ui'			=> true,
			'_builtin'			=> false,
			'capability_type'	=> 'post',
			'capabilities'		=> array(
				'edit_post'			=> $cap,
				'delete_post'		=> $cap,
				'edit_posts'		=> $cap,
				'delete_posts'		=> $cap,
			),
			'hierarchical'		=> true,
			'rewrite'			=> false,
			'query_var'			=> false,
			'supports' 			=> array('title'),
			'show_in_menu'		=> false,
		));
		
		
		// register post type 'acf-field'
		register_post_type('acf-field', array(
			'labels'			=> array(
			    'name'					=> __( 'Fields', 'acf' ),
				'singular_name'			=> __( 'Field', 'acf' ),
			    'add_new'				=> __( 'Add New' , 'acf' ),
			    'add_new_item'			=> __( 'Add New Field' , 'acf' ),
			    'edit_item'				=> __( 'Edit Field' , 'acf' ),
			    'new_item'				=> __( 'New Field' , 'acf' ),
			    'view_item'				=> __( 'View Field', 'acf' ),
			    'search_items'			=> __( 'Search Fields', 'acf' ),
			    'not_found'				=> __( 'No Fields found', 'acf' ),
			    'not_found_in_trash'	=> __( 'No Fields found in Trash', 'acf' ), 
			),
			'public'			=> false,
			'show_ui'			=> false,
			'_builtin'			=> false,
			'capability_type'	=> 'post',
			'capabilities'		=> array(
				'edit_post'			=> $cap,
				'delete_post'		=> $cap,
				'edit_posts'		=> $cap,
				'delete_posts'		=> $cap,
			),
			'hierarchical'		=> true,
			'rewrite'			=> false,
			'query_var'			=> false,
			'supports' 			=> array('title'),
			'show_in_menu'		=> false,
		));
		
	}
	
	
	/*
	*  register_post_status
	*
	*  This function will register custom post statuses
	*
	*  @type	function
	*  @date	22/10/2015
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function register_post_status() {
		
		// acf-disabled
		register_post_status('acf-disabled', array(
			'label'                     => __( 'Inactive', 'acf' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'acf' ),
		));
		
	}
	
	
	/*
	*  register_assets
	*
	*  This function will register scripts and styles
	*
	*  @type	function
	*  @date	22/10/2015
	*  @since	5.3.2
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function register_assets() {
		
		// vars
		$version = acf_get_setting('version');
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		
		
		// scripts
		wp_register_script('acf-input', acf_get_dir("assets/js/acf-input{$min}.js"), array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-resizable'), $version );
		wp_register_script('acf-field-group', acf_get_dir("assets/js/acf-field-group{$min}.js"), array('acf-input'), $version );
		
		
		// styles
		wp_register_style('acf-global', acf_get_dir('assets/css/acf-global.css'), array(), $version );
		wp_register_style('acf-input', acf_get_dir('assets/css/acf-input.css'), array('acf-global'), $version );
		wp_register_style('acf-field-group', acf_get_dir('assets/css/acf-field-group.css'), array('acf-input'), $version );
		
	}
	
	
	/*
	*  posts_where
	*
	*  This function will add in some new parameters to the WP_Query args allowing fields to be found via key / name
	*
	*  @type	filter
	*  @date	5/12/2013
	*  @since	5.0.0
	*
	*  @param	$where (string)
	*  @param	$wp_query (object)
	*  @return	$where (string)
	*/
	
	function posts_where( $where, $wp_query ) {
		
		// global
		global $wpdb;
		
		
		// acf_field_key
		if( $field_key = $wp_query->get('acf_field_key') ) {
		
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_name = %s", $field_key );
			
	    }
	    
	    
	    // acf_field_name
	    if( $field_name = $wp_query->get('acf_field_name') ) {
	    
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_excerpt = %s", $field_name );
			
	    }
	    
	    
	    // acf_group_key
		if( $group_key = $wp_query->get('acf_group_key') ) {
		
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_name = %s", $group_key );
			
	    }
	    
	    
	    // return
	    return $where;
	    
	}
	
	
	/*
	*  define
	*
	*  This function will safely define a constant
	*
	*  @type	function
	*  @date	3/5/17
	*  @since	5.5.13
	*
	*  @param	$name (string)
	*  @param	$value (mixed)
	*  @return	n/a
	*/
	
	function define( $name, $value = true ) {
		
		if( !defined($name) ) define( $name, $value );
		
	}
	
	
	/*
	*  get_setting
	*
	*  This function will return a value from the settings array found in the acf object
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$name (string) the setting name to return
	*  @param	$value (mixed) default value
	*  @return	$value
	*/
	
	function get_setting( $name, $value = null ) {
		
		// check settings
		if( isset($this->settings[ $name ]) ) {
			
			$value = $this->settings[ $name ];
			
		}
		
		
		// filter for 3rd party customization
		if( substr($name, 0, 1) !== '_' ) {
			
			$value = apply_filters( "acf/settings/{$name}", $value );
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  update_setting
	*
	*  This function will update a value into the settings array found in the acf object
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$name (string)
	*  @param	$value (mixed)
	*  @return	n/a
	*/
	
	function update_setting( $name, $value ) {
		
		$this->settings[ $name ] = $value;
		
		return true;
		
	}
	
}


/*
*  acf
*
*  The main function responsible for returning the one true acf Instance to functions everywhere.
*  Use this function like you would a global variable, except without needing to declare the global.
*
*  Example: <?php $acf = acf(); ?>
*
*  @type	function
*  @date	4/09/13
*  @since	4.3.0
*
*  @param	N/A
*  @return	(object)
*/

function acf() {

	global $acf;
	
	if( !isset($acf) ) {
	
		$acf = new acf();
		
		$acf->initialize();
		
	}
	
	return $acf;
	
}


// initialize
acf();


endif; // class_exists check

?>