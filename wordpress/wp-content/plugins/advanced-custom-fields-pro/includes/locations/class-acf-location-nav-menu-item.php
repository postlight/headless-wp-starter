<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_nav_menu_item') ) :

class acf_location_nav_menu_item extends acf_location {
	
	
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
		$this->name = 'nav_menu_item';
		$this->label = __("Menu Item",'acf');
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
		$nav_menu_item = acf_maybe_get( $screen, 'nav_menu_item' );
		
		
		// bail early if not nav_menu_item
		if( !$nav_menu_item ) return false;
		
		
		// global
		global $acf_menu;
		
		
		// append nav_menu data
		$screen['nav_menu'] = $acf_menu;
		
		
        // return
        return acf_get_location_rule('nav_menu')->rule_match( $result, $rule, $screen );
		
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
		
		// get menu choices
		$choices = acf_get_location_rule('nav_menu')->rule_values( $choices, $rule );
		
		
		// append item types?
		// dificult to get these details
			
		
		// return
		return $choices;
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_nav_menu_item' );

endif; // class_exists check

?>