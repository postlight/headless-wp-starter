<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location') ) :

class acf_location {
	
	
	/** @var string Rule name */
	var $name = '';
	

	/** @var string Rule label */
	var $label = '';
	
	
	/** @var string Rule category */
	var $category = 'post';
	
	
	/** @var bool Rule availability */
	var $public = true;
	
	
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
		
		// initialize
		$this->initialize();
		
		
		// filters
		$this->add_filter('acf/location/rule_match', true, array($this, 'rule_match'), 5, 3);
		$this->add_filter('acf/location/rule_operators', true, array($this, 'rule_operators'), 5, 2);
		$this->add_filter('acf/location/rule_values', true, array($this, 'rule_values'), 5, 2);
		
	}
	
	
	/*
	*  initialize
	*
	*  This function will initialize the location rule
	*
	*  @type	function
	*  @date	27/6/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		/* do nothing */
			
	}
	
	
	/*
	*  add_filter
	*
	*  This function checks if the function is_callable before adding the filter
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	$tag (string)
	*  @param	$function_to_add (string)
	*  @param	$priority (int)
	*  @param	$accepted_args (int)
	*  @return	n/a
	*/
	
	function add_filter( $tag = '', $specific = false, $function_to_add = '', $priority = 10, $accepted_args = 1 ) {
		
		// specific
		if( $specific ) {
			
			$tag .= '/' . $this->name;
			
		}
		
		
		// add
		if( is_callable($function_to_add) ) {
			
			add_filter( $tag, $function_to_add, $priority, $accepted_args );
			
		}
		
	}
	
	
	/*
	*  add_action
	*
	*  This function checks if the function is_callable before adding the action
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	$tag (string)
	*  @param	$function_to_add (string)
	*  @param	$priority (int)
	*  @param	$accepted_args (int)
	*  @return	n/a
	*/
	
	function add_action( $tag = '', $specific = false, $function_to_add = '', $priority = 10, $accepted_args = 1 ) {
		
		// specific
		if( $specific ) {

			$tag .= '/' . $this->name;
			
		}
		
		
		// add
		if( is_callable($function_to_add) ) {
			
			add_action( $tag, $function_to_add, $priority, $accepted_args );
			
		}
			
	}
	
	
	/*
	*  compare
	*
	*  This function will compare a value to a location rule and return a boolean result
	*
	*  @type	function
	*  @date	25/11/16
	*  @since	5.5.0
	*
	*  @param	$value (mixed)
	*  @param	rule (array)
	*  @return	(boolean)
	*/
	
	function compare( $value, $rule ) {
		
		// match
		$match = ( $value == $rule['value'] );
		
		
		// override for "all"
        if( $rule['value'] == 'all' ) $match = true;
		
		
		// reverse if 'not equal to'
        if( $rule['operator'] == '!=' ) {
	        	
        	$match = !$match;
        
        }
        
		
		// return
		return $match;
		
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
	
/*
	function rule_match( $result, $rule, $screen ) {
		
		return $result;
		
	}
*/
	
	
	/*
	*  rule_operators
	*
	*  This function returns the available operators for this rule type
	*
	*  @type	function
	*  @date	30/5/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	(array)
	*/
	
/*
	function rule_operators( $operators, $rule ) {
		
		return $operators;
		
	}
*/
	
	
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
	
/*
	function rule_values( $values, $rule ) {
		
		return $values;
		
	}
*/
	
	
/*
	function rule_listeners() {
		
		// js
		
	}
*/
	
	
}

endif; // class_exists check

?>