<?php

/*
*  ACF 3rd Party Compatibility Class
*
*  All the logic for 3rd party functionality
*
*  @class 		acf_third_party
*  @package		ACF
*  @subpackage	Core
*/

if( ! class_exists('acf_third_party') ) :

class acf_third_party {
	
	
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
		
		// Tabify Edit Screen - http://wordpress.org/extend/plugins/tabify-edit-screen/
		if( class_exists('Tabify_Edit_Screen') ) {
			add_filter('tabify_posttypes',			array($this, 'tabify_posttypes'));
			add_action('tabify_add_meta_boxes',		array($this, 'tabify_add_meta_boxes'));
		}
		
		// Post Type Switcher - http://wordpress.org/extend/plugins/post-type-switcher/
		if( class_exists('Post_Type_Switcher') ) {
			add_filter('pts_allowed_pages', array($this, 'pts_allowed_pages'));
		}
		
		// Event Espresso - https://wordpress.org/plugins/event-espresso-decaf/
		if( function_exists('espresso_version') ) {
			add_filter('acf/get_post_types', array($this, 'ee_get_post_types'), 10, 2);
		}
		
		// Dark Mode
		if( class_exists('Dark_Mode') ) {
			add_action('doing_dark_mode', array($this, 'doing_dark_mode'));
		}
	}
	
	
	/**
	*  acf_get_post_types
	*
	*  EE post types do not use the native post.php edit page, but instead render their own.
	*  Show the EE post types in lists where 'show_ui' is used.
	*
	*  @date	24/2/18
	*  @since	5.6.9
	*
	*  @param	array $post_types
	*  @param	array $args
	*  @return	array
	*/
	
	function ee_get_post_types( $post_types, $args ) {
		
		if( !empty($args['show_ui']) ) {
			$ee_post_types = get_post_types(array('show_ee_ui' => 1));
			$ee_post_types = array_keys($ee_post_types);
			$post_types = array_merge($post_types, $ee_post_types);
			$post_types = array_unique($post_types);
		}
		
		// return
		return $post_types;
	}
	
	
	/*
	*  tabify_posttypes
	*
	*  This function removes ACF post types from the tabify edit screen (post type selection sidebar)
	*
	*  @type	function
	*  @date	9/10/12
	*  @since	3.5.1
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function tabify_posttypes( $posttypes ) {
		
		// unset
		unset( $posttypes['acf-field-group'] );
		unset( $posttypes['acf-field'] );
		
		
		// return
		return $posttypes;
	}
	
	
	/*
	*  tabify_add_meta_boxes
	*
	*  This function creates dummy metaboxes on the tabify edit screen page
	*
	*  @type	function
	*  @date	9/10/12
	*  @since	3.5.1
	*
	*  @param	$post_type (string)
	*  @return	n/a
	*/
	
	function tabify_add_meta_boxes( $post_type ) {
		
		// get field groups
		$field_groups = acf_get_field_groups();
		
		
		if( !empty($field_groups) ) {
			
			foreach( $field_groups as $field_group ) {
				
				// vars
				$id = "acf-{$field_group['key']}";
				$title = 'ACF: ' . $field_group['title'];

				
				
				// add meta box
				add_meta_box( $id, $title, '__return_true', $post_type );
				
			}
			
		}
		
	}
	
	
	/*
	*  pts_allowed_pages
	*
	*  This filter will prevent PTS from running on the field group page!
	*
	*  @type	function
	*  @date	25/09/2014
	*  @since	5.0.0
	*
	*  @param	$pages (array)
	*  @return	$pages
	*/
	
	function pts_allowed_pages( $pages ) {
		
		// vars
		$post_type = '';
		
		
		// check $_GET becuase it is too early to use functions / global vars
		if( !empty($_GET['post_type']) ) {
			
			$post_type = $_GET['post_type'];
			
		} elseif( !empty($_GET['post']) ) {
			
			$post_type = get_post_type( $_GET['post'] );
			
		}
				
		
		// check post type
		if( $post_type == 'acf-field-group' ) {
			
			$pages = array();
			
		}
		
		
		// return
		return $pages;
		
	}
	
	/**
	*  doing_dark_mode
	*
	*  Runs during 'admin_enqueue_scripts' if dark mode is enabled
	*
	*  @date	13/8/18
	*  @since	5.7.3
	*
	*  @param	void
	*  @return	void
	*/
	
	function doing_dark_mode() {
		wp_enqueue_style('acf-dark', acf_get_url('assets/css/acf-dark.css'), array(), ACF_VERSION );
	}
	
}

new acf_third_party();

endif;

?>