<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_page_template') ) :

class acf_location_page_template extends acf_location {
	
	
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
	
	function initialize() {
		
		// vars
		$this->name = 'page_template';
		$this->label = __("Page Template",'acf');
		$this->category = 'page';
    	
	}
	
	
	/*
	*  rule_match
	*
	*  This function is used to match this location $rule to the current $screen
	*
	*  @type	function
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match( $result, $rule, $screen ) {
		
		// vars
		$post_type = acf_maybe_get( $screen, 'post_type' );
		
		
		// lookup post_type
		if( !$post_type ) {
			
			$post_id = acf_maybe_get( $screen, 'post_id' );
			
			if( !$post_id ) return false;
			
			$post_type = get_post_type( $post_id );
			
		}
		
		
		// page template 'default' rule is only for 'page' post type
		// prevents 'Default Template' field groups appearing on all post types that allow for post templates (WP 4.7)
		if( $rule['value'] === 'default' ) {
			
			// bail ealry if not page
			if( $post_type !== 'page' ) return false;
			
		}
		
		
		// return
		return acf_get_location_rule('post_template')->rule_match( $result, $rule, $screen );
		
	}
	
	
	/*
	*  rule_operators
	*
	*  This function returns the available values for this rule type
	*
	*  @type	function
	*  @date	30/5/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	(array)
	*/
	
	function rule_values( $choices, $rule ) {
		
		// vars
		$choices = array(
			'default' => apply_filters( 'default_page_template_title',  __('Default Template', 'acf') )
		);
		
		
		// get templates and merge them in
		$templates = wp_get_theme()->get_page_templates();
		$choices = array_merge($choices, $templates);
		
		
		// return choices
		return $choices;
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_page_template' );

endif; // class_exists check

?>