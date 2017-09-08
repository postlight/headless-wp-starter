<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_page_parent') ) :

class acf_location_page_parent extends acf_location {
	
	
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
		$this->name = 'page_parent';
		$this->label = __("Page Parent",'acf');
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
		$post_id = acf_maybe_get( $screen, 'post_id' );
		$page_parent = acf_maybe_get( $screen, 'page_parent' );
		
		
		// no page parent
		if( $page_parent === null ) {
			
			// bail early if no post id
			if( !$post_id ) return false;
			
			
			// get post parent
			$post = get_post( $post_id );
			$page_parent = $post->post_parent;
			
		}
		
		
		// compare
		return $this->compare( $page_parent, $rule );
        
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
		
		return acf_get_location_rule('page')->rule_values( $choices, $rule );
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_page_parent' );

endif; // class_exists check

?>