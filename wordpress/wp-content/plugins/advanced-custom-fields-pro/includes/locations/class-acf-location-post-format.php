<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_post_format') ) :

class acf_location_post_format extends acf_location {
	
	
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
		$this->name = 'post_format';
		$this->label = __("Post Format",'acf');
		$this->category = 'post';
    	
	}
	
	
	/*
	*  get_post_type
	*
	*  This function will return the current post_type
	*
	*  @type	function
	*  @date	25/11/16
	*  @since	5.5.0
	*
	*  @param	$options (int)
	*  @return	(mixed)
	*/
	
	function get_post_type( $screen ) {
		
		// vars
		$post_id = acf_maybe_get( $screen, 'post_id' );
		$post_type = acf_maybe_get( $screen, 'post_type' );
		
		
		// post_type
		if( $post_type ) return $post_type;
		
		
		// $post_id
		if( $post_id ) return get_post_type( $post_id );
		
		
		// return
		return false;
		
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
		$post_format = acf_maybe_get( $screen, 'post_format' );
		
		
		// find post format
		if( !$post_format ) {	
			
			// get post id
			$post_id = acf_maybe_get( $screen, 'post_id' );
			$post_type = $this->get_post_type( $screen );
			
			
			// bail early if not a post
			if( !$post_id || !$post_type ) return false;
			
			
			// does post_type support 'post-format'
			if( post_type_supports($post_type, 'post-formats') ) {
				
				// update
				$post_format = get_post_format($post_id);
				$post_format = $post_format ? $post_format : 'standard';
				
			}
			
		}
	    
		
		// compare
		return $this->compare( $post_format, $rule );
		
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
		
		return get_post_format_strings();
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_post_format' );

endif; // class_exists check

?>