<?php

/*
*  ACF Radio Button Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_radio
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_radio') ) :

class acf_field_radio extends acf_field {
	
	
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
	
	function __construct() {
		
		// vars
		$this->name = 'radio';
		$this->label = __("Radio Button",'acf');
		$this->category = 'choice';
		$this->defaults = array(
			'layout'			=> 'vertical',
			'choices'			=> array(),
			'default_value'		=> '',
			'other_choice'		=> 0,
			'save_other_choice'	=> 0,
			'allow_null' 		=> 0,
			'return_format'		=> 'value'
		);
		
		
		// do not delete!
    	parent::__construct();
		
	}
	
		
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field( $field ) {

		// vars
		$i = 0;
		$e = '';
		$ul = array( 
			'class'				=> 'acf-radio-list',
			'data-allow_null'	=> $field['allow_null'],
			'data-other_choice'	=> $field['other_choice']
		);
		
		
		// append to class
		$ul['class'] .= ' ' . ($field['layout'] == 'horizontal' ? 'acf-hl' : 'acf-bl');
		$ul['class'] .= ' ' . $field['class'];
		
		
		// select value
		$checked = '';
		$value = strval($field['value']);
		
		
		// selected choice
		if( isset($field['choices'][ $value ]) ) {
			
			$checked = $value;
			
		// custom choice
		} elseif( $field['other_choice'] && $value !== '' ) {
			
			$checked = 'other';
			
		// allow null	
		} elseif( $field['allow_null'] ) {
			
			// do nothing
			
		// select first input by default	
		} else {
			
			$checked = key($field['choices']);
			
		}
		
		
		// ensure $checked is a string (could be an int)
		$checked = strval($checked); 
		
				
		// other choice
		if( $field['other_choice'] ) {
			
			// vars
			$input = array(
				'type'		=> 'text',
				'name'		=> $field['name'],
				'value'		=> '',
				'disabled'	=> 'disabled',
				'class'		=> 'acf-disabled'
			);
			
			
			// select other choice if value is not a valid choice
			if( $checked === 'other' ) {
				
				unset($input['disabled']);
				$input['value'] = $field['value'];
				
			}
			
			
			// append other choice
			$field['choices']['other'] = '</label><input type="text" ' . acf_esc_attr($input) . ' /><label>';
		
		}
		
		
		// bail early if no choices
		if( empty($field['choices']) ) return;
		
		
		// hiden input
		$e .= acf_get_hidden_input( array('name' => $field['name']) );
		
		
		// open
		$e .= '<ul ' . acf_esc_attr($ul) . '>';
		
		
		// foreach choices
		foreach( $field['choices'] as $value => $label ) {
			
			// ensure value is a string
			$value = strval($value);
			$class = '';
			
			
			// increase counter
			$i++;
			
			
			// vars
			$atts = array(
				'type'	=> 'radio',
				'id'	=> $field['id'], 
				'name'	=> $field['name'],
				'value'	=> $value
			);
			
			
			// checked
			if( $value === $checked ) {
				
				$atts['checked'] = 'checked';
				$class = ' class="selected"';
				
			}
			
			
			// deisabled
			if( isset($field['disabled']) && acf_in_array($value, $field['disabled']) ) {
			
				$atts['disabled'] = 'disabled';
				
			}
			
			
			// id (use crounter for each input)
			if( $i > 1 ) {
			
				$atts['id'] .= '-' . $value;
				
			}
			
			
			// append
			$e .= '<li><label' . $class . '><input ' . acf_esc_attr( $atts ) . '/>' . $label . '</label></li>';
			
		}
		
		
		// close
		$e .= '</ul>';
		
		
		// return
		echo $e;
		
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		// encode choices (convert from array)
		$field['choices'] = acf_encode_choices($field['choices']);
		
		
		// choices
		acf_render_field_setting( $field, array(
			'label'			=> __('Choices','acf'),
			'instructions'	=> __('Enter each choice on a new line.','acf') . '<br /><br />' . __('For more control, you may specify both a value and label like this:','acf'). '<br /><br />' . __('red : Red','acf'),
			'type'			=> 'textarea',
			'name'			=> 'choices',
		));
		
		
		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// other_choice
		acf_render_field_setting( $field, array(
			'label'			=> __('Other','acf'),
			'instructions'	=> '',
			'name'			=> 'other_choice',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Add 'other' choice to allow for custom values", 'acf'),
		));
		
		
		// save_other_choice
		acf_render_field_setting( $field, array(
			'label'			=> __('Save Other','acf'),
			'instructions'	=> '',
			'name'			=> 'save_other_choice',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Save 'other' values to the field's choices", 'acf')
		));
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Appears when creating a new post','acf'),
			'type'			=> 'text',
			'name'			=> 'default_value',
		));
		
		
		// layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal', 
			'choices'		=> array(
				'vertical'		=> __("Vertical",'acf'), 
				'horizontal'	=> __("Horizontal",'acf')
			)
		));
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf'),
			'instructions'	=> __('Specify the returned value on front end','acf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'value'			=> __('Value','acf'),
				'label'			=> __('Label','acf'),
				'array'			=> __('Both (Array)','acf')
			)
		));
		
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field( $field ) {
		
		// decode choices (convert to array)
		$field['choices'] = acf_decode_choices($field['choices']);
		
		
		// return
		return $field;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*  @todo	Fix bug where $field was found via json and has no ID
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// bail early if no value (allow 0 to be saved)
		if( !$value && !is_numeric($value) ) return $value;
		
		
		// save_other_choice
		if( $field['save_other_choice'] ) {
			
			// value isn't in choices yet
			if( !isset($field['choices'][ $value ]) ) {
				
				// get raw $field (may have been changed via repeater field)
				// if field is local, it won't have an ID
				$selector = $field['ID'] ? $field['ID'] : $field['key'];
				$field = acf_get_field( $selector, true );
				
				
				// bail early if no ID (JSON only)
				if( !$field['ID'] ) return $value;
				
				
				// unslash (fixes serialize single quote issue)
				$value = wp_unslash($value);
				
				
				// update $field
				$field['choices'][ $value ] = $value;
				
				
				// save
				acf_update_field( $field );
				
			}
			
		}		
		
		
		// return
		return $value;
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	5.2.9
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded from
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in te database
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// must be single value
		if( is_array($value) ) {
			
			$value = array_pop($value);
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  translate_field
	*
	*  This function will translate field settings
	*
	*  @type	function
	*  @date	8/03/2016
	*  @since	5.3.2
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function translate_field( $field ) {
		
		return acf_get_field_type('select')->translate_field( $field );
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		return acf_get_field_type('select')->format_value( $value, $post_id, $field );
		
	}
	
}


// initialize
acf_register_field_type( new acf_field_radio() );

endif; // class_exists check

?>