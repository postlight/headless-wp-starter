<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_fields') ) :

class acf_fields {
	
	var $types = array();
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.4.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {

		
		
	}
	
	
	/*
	*  register_field_type
	*
	*  This function will store a field type class
	*
	*  @type	function
	*  @date	6/07/2016
	*  @since	5.4.0
	*
	*  @param	$instance (object)
	*  @return	n/a
	*/
	
	function register_field_type( $instance ) {
		
		// bail ealry if no field name
		if( !$instance->name ) return false;
		
		
		// bail ealry if already exists
		if( isset($this->types[ $instance->name ]) ) return false;
		
		
		// append
		$this->types[ $instance->name ] = $instance;
		
		
		// return
		return true;
		
	}
	
	
	/*
	*  get_field_type
	*
	*  This function will return a field type class
	*
	*  @type	function
	*  @date	6/07/2016
	*  @since	5.4.0
	*
	*  @param	$name (string)
	*  @return	(mixed)
	*/
	
	function get_field_type( $name ) {
		
		// bail ealry if doesn't exist
		if( !isset($this->types[ $name ]) ) return false;
		
		
		// return
		return $this->types[ $name ];
		
	}
	
		
}


// initialize
acf()->fields = new acf_fields();

endif; // class_exists check



/*
*  acf_register_field_type
*
*  alias of acf()->fields->register_field_type()
*
*  @type	function
*  @date	6/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_register_field_type( $instance ) {
	
	return acf()->fields->register_field_type( $instance );
	
}


/*
*  acf_get_field_type
*
*  alias of acf()->fields->get_field_type()
*
*  @type	function
*  @date	6/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_get_field_type( $name ) {
	
	return acf()->fields->get_field_type( $name );
	
}

?>