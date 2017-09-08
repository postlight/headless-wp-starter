<?php

if( ! class_exists('acf_field_separator') ) :

class acf_field_separator extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
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
		$this->name = 'separator';
		$this->label = __("Separator",'acf');
		$this->category = 'layout';
		
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		
		/* do nothing */
		
	}
	
	
	/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	
	function load_field( $field ) {
		
		// remove name to avoid caching issue
		$field['name'] = '';
		
		
		// remove required to avoid JS issues
		$field['required'] = 0;
		
		
		// set value other than 'null' to avoid ACF loading / caching issue
		$field['value'] = false;
		
		
		// return
		return $field;
		
	}
	
}


// initialize
acf_register_field_type( 'acf_field_separator' );

endif; // class_exists check

?>