<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_post_status') ) :

class acf_location_post_status extends acf_location {
	
	
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
		$this->name = 'post_status';
		$this->label = __("Post Status",'acf');
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
		$post_status = acf_maybe_get( $screen, 'post_status' );
		
		
	    // find post format
		if( !$post_status ) {	
			
			// get post id
			$post_id = acf_maybe_get( $screen, 'post_id' );
			
			
			// bail early if not a post
			if( !$post_id ) return false;
			
			
			// update
			$post_status = get_post_status( $post_id );
			
		}
		
			
	    // auto-draft = draft
	    if( $post_status == 'auto-draft' )  {
	    
		    $post_status = 'draft';
		    
	    }
	    
		
		// match
		return $this->compare( $post_status, $rule );
		
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
		
		// globals
		global $wp_post_statuses;
		
		
		// append
		if( !empty($wp_post_statuses) ) {
			
			foreach( $wp_post_statuses as $status ) {
				
				$choices[ $status->name ] = $status->label;
				
			}
			
		}
		
		
		// return choices
		return $choices;
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_post_status' );

endif; // class_exists check

?>