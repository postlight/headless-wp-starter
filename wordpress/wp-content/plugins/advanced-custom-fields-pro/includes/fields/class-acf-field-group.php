<?php

if( ! class_exists('acf_field__group') ) :

class acf_field__group extends acf_field {
	
	
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
		$this->name = 'group';
		$this->label = __("Group",'acf');
		$this->category = 'layout';
		$this->defaults = array(
			'sub_fields'	=> array(),
			'layout'		=> 'block'
		);
		$this->have_rows = 'single';
		
		
		// field filters
		$this->add_field_filter('acf/prepare_field_for_export', array($this, 'prepare_field_for_export'));
		$this->add_field_filter('acf/prepare_field_for_import', array($this, 'prepare_field_for_import'));
		
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
		
		// vars
		$sub_fields = acf_get_fields( $field );
		
		
		// append
		if( $sub_fields ) {
			
			$field['sub_fields'] = $sub_fields;
			
		}
				
		
		// return
		return $field;
		
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return $value;
		
		
		// modify names
		$field = $this->prepare_field_for_db( $field );
		
		
		// load sub fields
		$value = array();
		
		
		// loop
		foreach( $field['sub_fields'] as $sub_field ) {
			
			// load
			$value[ $sub_field['key'] ] = acf_get_value( $post_id, $sub_field );
			
		}
		
		
		// return
		return $value;
		
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
		
		// bail early if no value
		if( empty($value) ) return false;
		
		
		// modify names
		$field = $this->prepare_field_for_db( $field );
		
		
		// loop 
		foreach( $field['sub_fields'] as $sub_field ) {
			
			// extract value
			$sub_value = acf_extract_var( $value, $sub_field['key'] );
			
			
			// format value
			$sub_value = acf_format_value( $sub_value, $post_id, $sub_field );
			
			
			// append to $row
			$value[ $sub_field['_name'] ] = $sub_value;
			
		}
		
		
		// return
		return $value;
		
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
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the $post_id of which the value will be saved
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( !acf_is_array($value) ) return null;
		
		
		// bail ealry if no sub fields
		if( empty($field['sub_fields']) ) return null;
		
		
		// modify names
		$field = $this->prepare_field_for_db( $field );
		
		
		// loop
		foreach( $field['sub_fields'] as $sub_field ) {
			
			// vars
			$v = false;
			
			
			// key (backend)
			if( isset($value[ $sub_field['key'] ]) ) {
				
				$v = $value[ $sub_field['key'] ];
			
			// name (frontend)
			} elseif( isset($value[ $sub_field['_name'] ]) ) {
				
				$v = $value[ $sub_field['_name'] ];
			
			// empty
			} else {
				
				// input is not set (hidden by conditioanl logic)
				continue;
				
			}
			
			
			// update value
			acf_update_value( $v, $post_id, $sub_field );
			
		}
		
		
		// return
		return '';
		
	}
	
	
	/*
	*  prepare_field_for_db
	*
	*  This function will modify sub fields ready for update / load
	*
	*  @type	function
	*  @date	4/11/16
	*  @since	5.5.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function prepare_field_for_db( $field ) {
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return $field;
		
		
		// loop
		foreach( $field['sub_fields'] as &$sub_field ) {
			
			// prefix name
			$sub_field['name'] = $field['name'] . '_' . $sub_field['_name'];
			
		}
		
		
		// return
		return $field;

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
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return;
		
		
		// load values
		foreach( $field['sub_fields'] as &$sub_field ) {
			
			// add value
			if( isset($field['value'][ $sub_field['key'] ]) ) {
				
				// this is a normal value
				$sub_field['value'] = $field['value'][ $sub_field['key'] ];
				
			} elseif( isset($sub_field['default_value']) ) {
				
				// no value, but this sub field has a default value
				$sub_field['value'] = $sub_field['default_value'];
				
			}
			
			
			// update prefix to allow for nested values
			$sub_field['prefix'] = $field['name'];
			
			
			// restore required
			if( $field['required'] ) $sub_field['required'] = 0;
		
		}
		
		
		// render
		if( $field['layout'] == 'table' ) {
			
			$this->render_field_table( $field );
			
		} else {
			
			$this->render_field_block( $field );
			
		}
		
	}
	
	
	/*
	*  render_field_block
	*
	*  description
	*
	*  @type	function
	*  @date	12/07/2016
	*  @since	5.4.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_field_block( $field ) {
		
		// vars
		$label_placement = ($field['layout'] == 'block') ? 'top' : 'left';
		
		
		// html
		echo '<div class="acf-fields -' . $label_placement . ' -border">';
			
		foreach( $field['sub_fields'] as $sub_field ) {
			
			acf_render_field_wrap( $sub_field );
			
		}
		
		echo '</div>';
		
	}
	
	
	/*
	*  render_field_table
	*
	*  description
	*
	*  @type	function
	*  @date	12/07/2016
	*  @since	5.4.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_field_table( $field ) {
		
?>
<table class="acf-table">
	<thead>
		<tr>
		<?php foreach( $field['sub_fields'] as $sub_field ): 
			
			// prepare field (allow sub fields to be removed)
			$sub_field = acf_prepare_field($sub_field);
			
			
			// bail ealry if no field
			if( !$sub_field ) continue;
			
			
			// vars
			$atts = array();
			$atts['class'] = 'acf-th';
			$atts['data-name'] = $sub_field['_name'];
			$atts['data-type'] = $sub_field['type'];
			$atts['data-key'] = $sub_field['key'];
			
			
			// Add custom width
			if( $sub_field['wrapper']['width'] ) {
			
				$atts['data-width'] = $sub_field['wrapper']['width'];
				$atts['style'] = 'width: ' . $sub_field['wrapper']['width'] . '%;';
				
			}
			
				
			?>
			<th <?php acf_esc_attr_e( $atts ); ?>>
				<?php acf_render_field_label( $sub_field ); ?>
				<?php acf_render_field_instructions( $sub_field ); ?>
			</th>
		<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<tr class="acf-row">
		<?php 
		
		foreach( $field['sub_fields'] as $sub_field ) {
			
			acf_render_field_wrap( $sub_field, 'td' );
			
		}
				
		?>
		</tr>
	</tbody>
</table>
<?php
		
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
		
		// vars
		$args = array(
			'fields'	=> $field['sub_fields'],
			'parent'	=> $field['ID']
		);
		
		
		?><tr class="acf-field acf-field-setting-sub_fields" data-setting="group" data-name="sub_fields">
			<td class="acf-label">
				<label><?php _e("Sub Fields",'acf'); ?></label>	
			</td>
			<td class="acf-input">
				<?php 
				
				acf_get_view('field-group-fields', $args);
				
				?>
			</td>
		</tr>
		<?php
			
			
		// layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> __('Specify the style used to render the selected fields', 'acf'),
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'block'			=> __('Block','acf'),
				'table'			=> __('Table','acf'),
				'row'			=> __('Row','acf')
			)
		));
		
	}
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		// bail early if no $value
		if( empty($value) ) return $valid;
		
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return $valid;
		
		
		// loop
		foreach( $field['sub_fields'] as $sub_field ) {
			
			// get sub field
			$k = $sub_field['key'];
			
			
			// bail early if value not set (conditional logic?)
			if( !isset($value[ $k ]) ) continue;
			
			
			// required
			if( $field['required'] ) {
				$sub_field['required'] = 1;
			}
			
			
			// validate
			acf_validate_value( $value[ $k ], $sub_field, "{$input}[{$k}]" );
			
		}
		
		
		// return
		return $valid;
		
	}
	
	
	/*
	*  duplicate_field()
	*
	*  This filter is appied to the $field before it is duplicated and saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the modified field
	*/

	function duplicate_field( $field ) {
		
		// get sub fields
		$sub_fields = acf_extract_var( $field, 'sub_fields' );
		
		
		// save field to get ID
		$field = acf_update_field( $field );
		
		
		// duplicate sub fields
		acf_duplicate_fields( $sub_fields, $field['ID'] );
		
						
		// return		
		return $field;
		
	}
	
	
	/*
	*  prepare_field_for_export
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_export( $field ) {
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return $field;
		
		
		// prepare
		$field['sub_fields'] = acf_prepare_fields_for_export( $field['sub_fields'] );
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  prepare_field_for_import
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_import( $field ) {
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return $field;
		
		
		// vars
		$sub_fields = $field['sub_fields'];
		
		
		// reset field setting
		$field['sub_fields'] = array();
		
		
		// loop
		foreach( $sub_fields as &$sub_field ) {
			
			$sub_field['parent'] = $field['key'];
			
		}
		
		
		// merge
		array_unshift($sub_fields, $field);
		
		
		// return
		return $sub_fields;
		
	}
		
}


// initialize
acf_register_field_type( 'acf_field__group' );

endif; // class_exists check

?>