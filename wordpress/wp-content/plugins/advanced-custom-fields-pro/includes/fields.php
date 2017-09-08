<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_fields') ) :

class acf_fields {
	
	
	/** @var array Contains an array of field type instances */
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

		/* do nothing */
		
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
	*  @param	$class (string)
	*  @return	n/a
	*/
	
	function register_field_type( $class ) {
		
		if( $class instanceOf acf_field ) {
			
			$this->types[ $class->name ] = $class;
			
		} else {
			
			$instance = new $class();
			$this->types[ $instance->name ] = $instance;
		
		}
		
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
		
		return isset( $this->types[$name] ) ? $this->types[$name] : null;
		
	}
	
	
	/*
	*  is_field_type
	*
	*  This function will return true if a field type exists
	*
	*  @type	function
	*  @date	6/07/2016
	*  @since	5.4.0
	*
	*  @param	$name (string)
	*  @return	(mixed)
	*/
	
	function is_field_type( $name ) {
		
		return isset( $this->types[$name] );
		
	}
	
	
	/*
	*  register_field_type_info
	*
	*  This function will store a basic array of info about the field type
	*  to later be overriden by the avbove register_field_type function
	*
	*  @type	function
	*  @date	29/5/17
	*  @since	5.6.0
	*
	*  @param	$info (array)
	*  @return	n/a
	*/
	
	function register_field_type_info( $info ) {
		
		// convert to object
		$instance = (object) $info;
		$this->types[ $instance->name ] = $instance;
		
	}
	
	
	/*
	*  get_field_types
	*
	*  This function will return an array of all field type infos
	*
	*  @type	function
	*  @date	6/07/2016
	*  @since	5.4.0
	*
	*  @param	$name (string)
	*  @return	(mixed)
	*/
	
	function get_field_types() {
		
		// vars
		$groups = array();
		$l10n = array(
			'basic'			=> __('Basic', 'acf'),
			'content'		=> __('Content', 'acf'),
			'choice'		=> __('Choice', 'acf'),
			'relational'	=> __('Relational', 'acf'),
			'jquery'		=> __('jQuery', 'acf'),
			'layout'		=> __('Layout', 'acf'),
		);
		
			
		// loop
		foreach( $this->types as $type ) {
			
			// bail ealry if not public
			if( !$type->public ) continue;
			
			
			// translate
			$cat = $type->category;
			$cat = isset( $l10n[$cat] ) ? $l10n[$cat] : $cat;
			
			
			// append
			$groups[ $cat ][ $type->name ] = $type->label;
			
		}
		
		
		// filter
		$groups = apply_filters('acf/get_field_types', $groups);
		
		
		// return
		return $groups;
		
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
*  @date	31/5/17
*  @since	5.6.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_register_field_type( $class ) {
	
	return acf()->fields->register_field_type( $class );
	
}


/*
*  acf_get_field_type
*
*  alias of acf()->fields->get_field_type()
*
*  @type	function
*  @date	31/5/17
*  @since	5.6.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_get_field_type( $name ) {
	
	return acf()->fields->get_field_type( $name );
	
}


/*
*  acf_register_field_type_info
*
*  alias of acf()->fields->register_field_type_info()
*
*  @type	function
*  @date	31/5/17
*  @since	5.6.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_register_field_type_info( $info ) {
	
	return acf()->fields->register_field_type_info( $info );
	
}


/*
*  acf_get_field_types
*
*  alias of acf()->fields->get_field_types()
*
*  @type	function
*  @date	31/5/17
*  @since	5.6.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_get_field_types() {
	
	return acf()->fields->get_field_types();
	
}


/*
*  acf_is_field_type
*
*  alias of acf()->fields->is_field_type()
*
*  @type	function
*  @date	31/5/17
*  @since	5.6.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_is_field_type( $name = '' ) {
	
	return acf()->fields->is_field_type( $name );
	
}


/*
*  acf_get_field_type_prop
*
*  This function will return a field type's property
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_field_type_prop( $name = '', $prop = '' ) {
	
	$type = acf_get_field_type( $name );
	return ($type && isset($type->$prop)) ? $type->$prop : null;
	
}


/*
*  acf_get_field_type_label
*
*  This function will return the label of a field type
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_field_type_label( $name = '' ) {
	
	$type = acf_get_field_type( $name );
	return $type ? $type->label : '<span class="acf-tooltip-js" title="'.__('Field type does not exist', 'acf').'">'.__('Unknown', 'acf').'</span>';
	
}


/*
*  acf_field_type_exists (deprecated)
*
*  deprecated in favour of acf_is_field_type()
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$type (string)
*  @return	(boolean)
*/

function acf_field_type_exists( $type = '' ) {
	return acf_is_field_type( $type );
}


/*
*  acf_get_grouped_field_types (deprecated)
*
*  deprecated in favour of acf_get_field_types()
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_grouped_field_types() {
	return acf_get_field_types();
}

?>