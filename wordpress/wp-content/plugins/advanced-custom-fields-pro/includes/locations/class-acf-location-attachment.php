<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_attachment') ) :

class acf_location_attachment extends acf_location {
	
	
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
		$this->name = 'attachment';
		$this->label = __("Attachment",'acf');
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
		$attachment = acf_maybe_get( $screen, 'attachment' );
		
				
		// validate
		if( !$attachment ) return false;
		
		
		// get attachment mime type
		$mime_type = get_post_mime_type( $attachment );
		
		
		// no specific mime
		if( !strpos($rule['value'], '/') ) {
			
			// explode into [0] => type, [1] => mime
			$bits = explode('/', $mime_type);
			
			
			// if type matches, fake the $mime_type to match
			if( $rule['value'] === $bits[0] ) {
				
				$mime_type = $rule['value'];
				
			}
		}
		
		
		// match
		return $this->compare( $mime_type, $rule );
		
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
		$mimes = get_allowed_mime_types();
		$choices = array(
			'all' => __('All', 'acf')
		);
		
		
		// loop
		foreach( $mimes as $type => $mime ) {
			
			$group = current( explode('/', $mime) );
			$choices[ $group ][ $group ] = sprintf( __('All %s formats', 'acf'), $group);
			$choices[ $group ][ $mime ] = "$type ($mime)";
			
		}
		
		
		// return
		return $choices;
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_attachment' );

endif; // class_exists check

?>