<?php

if( ! class_exists('acf_field_checkbox') ) :

class acf_field_checkbox extends acf_field {
	
	
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
		$this->name = 'checkbox';
		$this->label = __("Checkbox",'acf');
		$this->category = 'choice';
		$this->defaults = array(
			'layout'			=> 'vertical',
			'choices'			=> array(),
			'default_value'		=> '',
			'allow_custom'		=> 0,
			'save_custom'		=> 0,
			'toggle'			=> 0,
			'return_format'		=> 'value'
		);
		
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
		
		// reset vars
		$this->_values = array();
		$this->_all_checked = true;
		
		
		// ensure array
		$field['value'] = acf_get_array($field['value']);
		$field['choices'] = acf_get_array($field['choices']);
		
		
		// hiden input
		acf_hidden_input( array('name' => $field['name']) );
		
		
		// vars
		$li = '';
		$ul = array( 
			'class' => 'acf-checkbox-list',
		);
		
		
		// append to class
		$ul['class'] .= ' ' . ($field['layout'] == 'horizontal' ? 'acf-hl' : 'acf-bl');
		$ul['class'] .= ' ' . $field['class'];
		
		
		// checkbox saves an array
		$field['name'] .= '[]';
		
		
		// choices
		if( !empty($field['choices']) ) {
			
			// choices
			$li .= $this->render_field_choices( $field );
			
			
			// toggle
			if( $field['toggle'] ) {
				$li = $this->render_field_toggle( $field ) . $li;
			}
			
		}
		
		
		// custom
		if( $field['allow_custom'] ) {
			$li .= $this->render_field_custom( $field );
		}
		
		
		// return
		echo '<ul ' . acf_esc_attr( $ul ) . '>' . "\n" . $li . '</ul>' . "\n";
		
	}
	
	
	/*
	*  render_field_choices
	*
	*  description
	*
	*  @type	function
	*  @date	15/7/17
	*  @since	5.6.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_field_choices( $field ) {
		
		// walk
		return $this->walk( $field['choices'], $field );
		
	}
	
	
	/*
	*  render_field_toggle
	*
	*  description
	*
	*  @type	function
	*  @date	15/7/17
	*  @since	5.6.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_field_toggle( $field ) {
		
		// vars
		$atts = array(
			'type'	=> 'checkbox',
			'class'	=> 'acf-checkbox-toggle',
			'label'	=> __("Toggle All", 'acf')
		);
		
		
		// custom label
		if( is_string($field['toggle']) ) {
			$atts['label'] = $field['toggle'];
		}
		
		
		// checked
		if( $this->_all_checked ) {
			$atts['checked'] = 'checked';
		}
		
		
		// return
		return '<li>' . acf_get_checkbox_input($atts) . '</li>' . "\n";
		
	}
	
	
	/*
	*  render_field_custom
	*
	*  description
	*
	*  @type	function
	*  @date	15/7/17
	*  @since	5.6.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_field_custom( $field ) {
		
		// vars
		$html = '';
		
		
		// loop
		foreach( $field['value'] as $value ) {
			
			// ignore if already eixsts
			if( isset($field['choices'][ $value ]) ) continue;
			
			
			// vars
			$esc_value = esc_attr($value);
			$text_input = array(
				'name'	=> $field['name'],
				'value'	=> $value,
			);
			
			
			// bail ealry if choice already exists
			if( in_array( $esc_value, $this->_values ) ) continue;
			
			
			// append
			$html .= '<li><input class="acf-checkbox-custom" type="checkbox" checked="checked" />' . acf_get_text_input($text_input) . '</li>' . "\n";
			
		}
		
		
		// append button
		$html .= '<li><a href="#" class="button acf-add-checkbox">' . esc_attr__('Add new choice', 'acf') . '</a></li>' . "\n";
		
		
		// return
		return $html;
		
	}
	
	
	function walk( $choices = array(), $args = array(), $depth = 0 ) {
		
		// bail ealry if no choices
		if( empty($choices) ) return '';
		
		
		// defaults
		$args = wp_parse_args($args, array(
			'id'		=> '',
			'type'		=> 'checkbox',
			'name'		=> '',
			'value'		=> array(),
			'disabled'	=> array(),
		));
		
		
		// vars
		$html = '';
		
		
		// sanitize values for 'selected' matching
		if( $depth == 0 ) {
			$args['value'] = array_map('esc_attr', $args['value']);
			$args['disabled'] = array_map('esc_attr', $args['disabled']);
		}
		
		
		// loop
		foreach( $choices as $value => $label ) {
			
			// open
			$html .= '<li>';
			
			
			// optgroup
			if( is_array($label) ){
				
				$html .= '<ul>' . "\n";
				$html .= $this->walk( $label, $args, $depth+1 );
				$html .= '</ul>';
			
			// option	
			} else {
				
				// vars
				$esc_value = esc_attr($value);
				$atts = array(
					'id'	=> $args['id'] . '-' . str_replace(' ', '-', $value),
					'type'	=> $args['type'],
					'name'	=> $args['name'],
					'value' => $value,
					'label' => $label,
				);
				
				
				// selected
				if( in_array( $esc_value, $args['value'] ) ) {
					$atts['checked'] = 'checked';
				} else {
					$this->_all_checked = false;
				}
				
				
				// disabled
				if( in_array( $esc_value, $args['disabled'] ) ) {
					$atts['disabled'] = 'disabled';
				}
				
				
				// store value added
				$this->_values[] = $esc_value;
				
				
				// append
				$html .= acf_get_checkbox_input($atts);
				
			}
			
			
			// close
			$html .= '</li>' . "\n";
			
		}
		
		
		// return
		return $html;
		
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
		$field['default_value'] = acf_encode_choices($field['default_value'], false);
				
		
		// choices
		acf_render_field_setting( $field, array(
			'label'			=> __('Choices','acf'),
			'instructions'	=> __('Enter each choice on a new line.','acf') . '<br /><br />' . __('For more control, you may specify both a value and label like this:','acf'). '<br /><br />' . __('red : Red','acf'),
			'type'			=> 'textarea',
			'name'			=> 'choices',
		));	
		
		
		// other_choice
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Custom','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_custom',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Allow 'custom' values to be added", 'acf'),
		));
		
		
		// save_other_choice
		acf_render_field_setting( $field, array(
			'label'			=> __('Save Custom','acf'),
			'instructions'	=> '',
			'name'			=> 'save_custom',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Save 'custom' values to the field's choices", 'acf')
		));
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Enter each default value on a new line','acf'),
			'type'			=> 'textarea',
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
		
		
		// layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Toggle','acf'),
			'instructions'	=> __('Prepend an extra checkbox to toggle all choices','acf'),
			'name'			=> 'toggle',
			'type'			=> 'true_false',
			'ui'			=> 1,
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
		
		return acf_get_field_type('select')->update_field( $field );
		
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// bail early if is empty
		if( empty($value) ) return $value;
		
		
		// select -> update_value()
		$value = acf_get_field_type('select')->update_value( $value, $post_id, $field );
		
		
		// save_other_choice
		if( $field['save_custom'] ) {
			
			// get raw $field (may have been changed via repeater field)
			// if field is local, it won't have an ID
			$selector = $field['ID'] ? $field['ID'] : $field['key'];
			$field = acf_get_field( $selector, true );
			
			
			// bail early if no ID (JSON only)
			if( !$field['ID'] ) return $value;
			
			
			// loop
			foreach( $value as $v ) {
				
				// ignore if already eixsts
				if( isset($field['choices'][ $v ]) ) continue;
				
				
				// unslash (fixes serialize single quote issue)
				$v = wp_unslash($v);
				
				
				// sanitize (remove tags)
				$v = sanitize_text_field($v);
				
				
				// append
				$field['choices'][ $v ] = $v;
				
			}
			
			
			// save
			acf_update_field( $field );
			
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
acf_register_field_type( 'acf_field_checkbox' );

endif; // class_exists check

?>