<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_taxonomy') ) :

class acf_location_taxonomy extends acf_location {
	
	
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
		$this->name = 'taxonomy';
		$this->label = __("Taxonomy",'acf');
		$this->category = 'forms';
    	
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
		$taxonomy = acf_maybe_get( $screen, 'taxonomy' );
		
		
		// bail early if not taxonomy
		if( !$taxonomy ) return false;
				
		
        // return
        return $this->compare( $taxonomy, $rule );
		
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
		$choices = array( 'all' => __('All', 'acf') );
		$choices = array_merge( $choices, acf_get_taxonomy_labels() );
		
		
		// return
		return $choices;
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_taxonomy' );

endif; // class_exists check

?>