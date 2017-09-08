<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_user_role') ) :

class acf_location_user_role extends acf_location {
	
	
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
		$this->name = 'user_role';
		$this->label = __("User Role",'acf');
		$this->category = 'user';
    	
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
		$user_id = acf_maybe_get( $screen, 'user_id' );
		$user_role = acf_maybe_get( $screen, 'user_role' );
		
		
		// if user_role is supplied (3rd party compatibility)
		if( $user_role ) {
			
			// do nothing
		
		// user_id (expected)	
		} elseif( $user_id ) {
			
			// new user
			if( $user_id == 'new' ) {
				
				// set to default role
				$user_role = get_option('default_role');
			
			// existing user
			} elseif( user_can($user_id, $rule['value']) ) {
				
				// set to value and allow match
				$user_role = $rule['value'];
				
			}
		
		// else
		} else {
			
			// not a user	
			return false;
			
		}
		
		
		// match
		return $this->compare( $user_role, $rule );
		
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
		
		// global
		global $wp_roles;
		
		
		// vars
		$choices = array( 'all' => __('All', 'acf') );
		$choices = array_merge( $choices, $wp_roles->get_names() );
		
		
		// return
		return $choices;
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_user_role' );

endif; // class_exists check

?>