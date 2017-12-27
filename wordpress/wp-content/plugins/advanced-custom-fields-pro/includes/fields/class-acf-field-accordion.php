<?php

if( ! class_exists('acf_field__accordion') ) :

class acf_field__accordion extends acf_field {
	
	
	/**
	*  initialize
	*
	*  This function will setup the field type data
	*
	*  @date	30/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'accordion';
		$this->label = __("Accordion",'acf');
		$this->category = 'layout';
		$this->defaults = array(
			'open'			=> 0,
			'multi_expand'	=> 0,
			'endpoint'		=> 0
		);
		
	}
	
	
	/**
	*  render_field
	*
	*  Create the HTML interface for your field
	*
	*  @date	30/10/17
	*  @since	5.6.3
	*
	*  @param	array $field
	*  @return	n/a
	*/
	
	function render_field( $field ) {
		
		// vars
		$atts = array(
			'class'				=> 'acf-fields',
			'data-open'			=> $field['open'],
			'data-multi_expand'	=> $field['multi_expand'],
			'data-endpoint'		=> $field['endpoint']
		);
		
		?>
		<div <?php acf_esc_attr_e($atts); ?>></div>
		<?php
		
	}
	
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field_settings( $field ) {
		
/*
		// message
		$message = '';
		$message .= '<p>' . __( 'Accordions help you organize fields into panels that open and close.', 'acf') . '</p>';
		$message .= '<p>' . __( 'All fields following this accordion (or until another accordion is defined) will be grouped together.','acf') . '</p>';
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Instructions','acf'),
			'instructions'	=> '',
			'name'			=> 'notes',
			'type'			=> 'message',
			'message'		=> $message,
		));
*/
		
		// active
		acf_render_field_setting( $field, array(
			'label'			=> __('Open','acf'),
			'instructions'	=> __('Display this accordion as open on page load.','acf'),
			'name'			=> 'open',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// multi_expand
		acf_render_field_setting( $field, array(
			'label'			=> __('Multi-expand','acf'),
			'instructions'	=> __('Allow this accordion to open without closing others.','acf'),
			'name'			=> 'multi_expand',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// endpoint
		acf_render_field_setting( $field, array(
			'label'			=> __('Endpoint','acf'),
			'instructions'	=> __('Define an endpoint for the previous accordion to stop. This accordion will not be visible.','acf'),
			'name'			=> 'endpoint',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
					
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
acf_register_field_type( 'acf_field__accordion' );

endif; // class_exists check

?>