<?php

if( ! class_exists('acf_field_flexible_content') ) :

class acf_field_flexible_content extends acf_field {
	
	
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
		$this->name = 'flexible_content';
		$this->label = __("Flexible Content",'acf');
		$this->category = 'layout';
		$this->defaults = array(
			'layouts'		=> array(),
			'min'			=> '',
			'max'			=> '',
			'button_label'	=> __("Add Row",'acf'),
		);
		
		
		// ajax
		$this->add_action('wp_ajax_acf/fields/flexible_content/layout_title',			array($this, 'ajax_layout_title'));
		$this->add_action('wp_ajax_nopriv_acf/fields/flexible_content/layout_title',	array($this, 'ajax_layout_title'));
		
		
		// filters
		$this->add_filter('acf/prepare_field_for_export',	array($this, 'prepare_any_field_for_export'));
		$this->add_filter('acf/clone_field', 				array($this, 'clone_any_field'), 10, 2);
		$this->add_filter('acf/validate_field',					array($this, 'validate_any_field'));
		
		
		// field filters
		$this->add_field_filter('acf/get_sub_field', 			array($this, 'get_sub_field'), 10, 3);
		$this->add_field_filter('acf/prepare_field_for_export', array($this, 'prepare_field_for_export'));
		$this->add_field_filter('acf/prepare_field_for_import', array($this, 'prepare_field_for_import'));
		
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	16/12/2015
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// localize
		acf_localize_text(array(
			
			// identifiers
		   	'layout'													=> __('layout', 'acf'),
			'layouts'													=> __('layouts', 'acf'),
			
			// min / max
			'This field requires at least {min} {label} {identifier}'	=> __('This field requires at least {min} {label} {identifier}', 'acf'),
			'This field has a limit of {max} {label} {identifier}'		=> __('This field has a limit of {max} {label} {identifier}', 'acf'),
			
			// popup badge
			'{available} {label} {identifier} available (max {max})'	=> __('{available} {label} {identifier} available (max {max})', 'acf'),
			'{required} {label} {identifier} required (min {min})'		=> __('{required} {label} {identifier} required (min {min})', 'acf'),
			
			// field settings
			'Flexible Content requires at least 1 layout'				=> __('Flexible Content requires at least 1 layout', 'acf')
	   	));
	}
	
	
	/*
	*  get_valid_layout
	*
	*  This function will fill in the missing keys to create a valid layout
	*
	*  @type	function
	*  @date	3/10/13
	*  @since	1.1.0
	*
	*  @param	$layout (array)
	*  @return	$layout (array)
	*/
	
	function get_valid_layout( $layout = array() ) {
		
		// parse
		$layout = wp_parse_args($layout, array(
			'key'			=> uniqid(),
			'name'			=> '',
			'label'			=> '',
			'display'		=> 'block',
			'sub_fields'	=> array(),
			'min'			=> '',
			'max'			=> '',
		));
		
		
		// return
		return $layout;
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
		
		// bail early if no field layouts
		if( empty($field['layouts']) ) {
			
			return $field;
			
		}
		
		
		// vars
		$sub_fields = acf_get_fields($field);
		
		
		// loop through layouts, sub fields and swap out the field key with the real field
		foreach( array_keys($field['layouts']) as $i ) {
			
			// extract layout
			$layout = acf_extract_var( $field['layouts'], $i );
			
			
			// validate layout
			$layout = $this->get_valid_layout( $layout );
			
			
			// append sub fields
			if( !empty($sub_fields) ) {
				
				foreach( array_keys($sub_fields) as $k ) {
					
					// check if 'parent_layout' is empty
					if( empty($sub_fields[ $k ]['parent_layout']) ) {
					
						// parent_layout did not save for this field, default it to first layout
						$sub_fields[ $k ]['parent_layout'] = $layout['key'];
						
					}
					
					
					// append sub field to layout, 
					if( $sub_fields[ $k ]['parent_layout'] == $layout['key'] ) {
					
						$layout['sub_fields'][] = acf_extract_var( $sub_fields, $k );
						
					}
					
				}
				
			}
			
			
			// append back to layouts
			$field['layouts'][ $i ] = $layout;
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_sub_field
	*
	*  This function will return a specific sub field
	*
	*  @type	function
	*  @date	29/09/2016
	*  @since	5.4.0
	*
	*  @param	$sub_field 
	*  @param	$selector (string)
	*  @param	$field (array)
	*  @return	$post_id (int)
	*/

	function get_sub_field( $sub_field, $selector, $field ) {
		
		// bail early if no layouts
		if( empty($field['layouts']) ) return false;
		
		
		// vars
		$active = get_row_layout();
		
		
		// loop
		foreach( $field['layouts'] as $layout ) {
			
			// bail early if active layout does not match
			if( $active && $active !== $layout['name'] ) continue;
			
			
			// bail early if no sub fields
			if( empty($layout['sub_fields']) ) continue;
			
			
			// loop
			foreach( $layout['sub_fields'] as $sub_field ) {
				
				// check name and key
				if( $sub_field['name'] == $selector || $sub_field['key'] == $selector ) {
					
					// return
					return $sub_field;
					
				}
				
			}
			
		}
		
		
		// return
		return false;
		
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
	
		// defaults
		if( empty($field['button_label']) ) {
		
			$field['button_label'] = $this->defaults['button_label'];
			
		}
		
		
		// sort layouts into names
		$layouts = array();
		
		foreach( $field['layouts'] as $k => $layout ) {
		
			$layouts[ $layout['name'] ] = $layout;
			
		}
		
		
		// vars
		$div = array(
			'class'		=> 'acf-flexible-content',
			'data-min'	=> $field['min'],
			'data-max'	=> $field['max']
		);
		
		// empty
		if( empty($field['value']) ) {
			$div['class'] .= ' -empty';
		}
		
		
		// no value message
		$no_value_message = __('Click the "%s" button below to start creating your layout','acf');
		$no_value_message = apply_filters('acf/fields/flexible_content/no_value_message', $no_value_message, $field);

?>
<div <?php acf_esc_attr_e( $div ); ?>>
	
	<?php acf_hidden_input(array( 'name' => $field['name'] )); ?>
	
	<div class="no-value-message">
		<?php printf( $no_value_message, $field['button_label'] ); ?>
	</div>
	
	<div class="clones">
		<?php foreach( $layouts as $layout ): ?>
			<?php $this->render_layout( $field, $layout, 'acfcloneindex', array() ); ?>
		<?php endforeach; ?>
	</div>
	
	<div class="values">
		<?php if( !empty($field['value']) ): 
			
			foreach( $field['value'] as $i => $value ):
				
				// validate
				if( empty($layouts[ $value['acf_fc_layout'] ]) ) continue;
				
				
				// render
				$this->render_layout( $field, $layouts[ $value['acf_fc_layout'] ], $i, $value );
				
			endforeach;
			
		endif; ?>
	</div>
	
	<div class="acf-actions">
		<a class="acf-button button button-primary" href="#" data-name="add-layout"><?php echo $field['button_label']; ?></a>
	</div>
	
	<script type="text-html" class="tmpl-popup"><?php 
		?><ul><?php foreach( $layouts as $layout ): 
			
			$atts = array(
				'href'			=> '#',
				'data-layout'	=> $layout['name'],
				'data-min' 		=> $layout['min'],
				'data-max' 		=> $layout['max'],
			);
			
			?><li><a <?php acf_esc_attr_e( $atts ); ?>><?php echo $layout['label']; ?></a></li><?php 
		
		endforeach; ?></ul>
	</script>
	
</div>
<?php
		
	}
	
	
	/*
	*  render_layout
	*
	*  description
	*
	*  @type	function
	*  @date	19/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_layout( $field, $layout, $i, $value ) {
		
		// vars
		$order = 0;
		$el = 'div';
		$sub_fields = $layout['sub_fields'];
		$prefix = $field['name'] . '[' . $i .  ']';
		
		
		// div
		$div = array(
			'class'			=> 'layout',
			'data-id'		=> $i,
			'data-layout'	=> $layout['name']
		);
		
		
		// clone
		if( is_numeric($i) ) {
			
			$order = $i + 1;
			
		} else {
			
			$div['class'] .= ' acf-clone';
			
		}
		
		
		// display
		if( $layout['display'] == 'table' ) {
			
			$el = 'td';
			
		}
		
		
		// title
		$title = $this->get_layout_title( $field, $layout, $i, $value );
		
		
		// remove row
		reset_rows();
		
?>
<div <?php echo acf_esc_attr($div); ?>>
			
	<?php acf_hidden_input(array( 'name' => $prefix.'[acf_fc_layout]', 'value' => $layout['name'] )); ?>
	
	<div class="acf-fc-layout-handle" title="<?php _e('Drag to reorder','acf'); ?>" data-name="collapse-layout"><?php echo $title; ?></div>
	
	<div class="acf-fc-layout-controls">
		<a class="acf-icon -plus small light acf-js-tooltip" href="#" data-name="add-layout" title="<?php _e('Add layout','acf'); ?>"></a>
		<a class="acf-icon -minus small light acf-js-tooltip" href="#" data-name="remove-layout" title="<?php _e('Remove layout','acf'); ?>"></a>
		<a class="acf-icon -collapse small acf-js-tooltip" href="#" data-name="collapse-layout" title="<?php _e('Click to toggle','acf'); ?>"></a>
	</div>
	
<?php if( !empty($sub_fields) ): ?>
	
	<?php if( $layout['display'] == 'table' ): ?>
	<table class="acf-table">
		
		<thead>
			<tr>
				<?php foreach( $sub_fields as $sub_field ): 
					
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
					<th <?php echo acf_esc_attr( $atts ); ?>>
						<?php echo acf_get_field_label( $sub_field ); ?>
						<?php if( $sub_field['instructions'] ): ?>
							<p class="description"><?php echo $sub_field['instructions']; ?></p>
						<?php endif; ?>
					</th>
					
				<?php endforeach; ?> 
			</tr>
		</thead>
		
		<tbody>
			<tr class="acf-row">
	<?php else: ?>
	<div class="acf-fields <?php if($layout['display'] == 'row'): ?>-left<?php endif; ?>">
	<?php endif; ?>
	
		<?php
			
		// loop though sub fields
		foreach( $sub_fields as $sub_field ) {
			
			// add value
			if( isset($value[ $sub_field['key'] ]) ) {
				
				// this is a normal value
				$sub_field['value'] = $value[ $sub_field['key'] ];
				
			} elseif( isset($sub_field['default_value']) ) {
				
				// no value, but this sub field has a default value
				$sub_field['value'] = $sub_field['default_value'];
				
			}
			
			
			// update prefix to allow for nested values
			$sub_field['prefix'] = $prefix;
			
			
			// render input
			acf_render_field_wrap( $sub_field, $el );
		
		}
		
		?>
			
	<?php if( $layout['display'] == 'table' ): ?>
			</tr>
		</tbody>
	</table>
	<?php else: ?>
	</div>
	<?php endif; ?>

<?php endif; ?>

</div>
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
		
		// load default layout
		if( empty($field['layouts']) ) {
		
			$field['layouts'] = array(
				array()
			);
			
		}
		
		
		// loop through layouts
		foreach( $field['layouts'] as $layout ) {
			
			// get valid layout
			$layout = $this->get_valid_layout( $layout );
			
			
			// vars
			$layout_prefix = "{$field['prefix']}[layouts][{$layout['key']}]";
			
			
?><tr class="acf-field acf-field-setting-fc_layout" data-name="fc_layout" data-setting="flexible_content" data-id="<?php echo $layout['key']; ?>">
	<td class="acf-label">
		<label><?php _e("Layout",'acf'); ?></label>
		<ul class="acf-bl acf-fl-actions">
			<li><a class="reorder-layout" href="#" title="<?php _e("Reorder Layout",'acf'); ?>"><?php _e("Reorder",'acf'); ?></a></li>
			<li><a class="delete-layout" href="#" title="<?php _e("Delete Layout",'acf'); ?>"><?php _e("Delete",'acf'); ?></a></li>
			<li><a class="duplicate-layout" href="#" title="<?php _e("Duplicate Layout",'acf'); ?>"><?php _e("Duplicate",'acf'); ?></a></li>
			<li><a class="add-layout" href="#" title="<?php _e("Add New Layout",'acf'); ?>"><?php _e("Add New",'acf'); ?></a></li>
		</ul>
	</td>
	<td class="acf-input">
		<?php 
			
		acf_hidden_input(array(
			'id'		=> acf_idify( $layout_prefix . '[key]' ),
			'name'		=> $layout_prefix . '[key]',
			'class'		=> 'layout-key',
			'value'		=> $layout['key']
		));
		
		?>
		<ul class="acf-fc-meta acf-bl">
			<li class="acf-fc-meta-label">
				<?php 
				
				acf_render_field(array(
					'type'		=> 'text',
					'name'		=> 'label',
					'class'		=> 'layout-label',
					'prefix'	=> $layout_prefix,
					'value'		=> $layout['label'],
					'prepend'	=> __('Label','acf')
				));
				
				?>
			</li>
			<li class="acf-fc-meta-name">
				<?php 
						
				acf_render_field(array(
					'type'		=> 'text',
					'name'		=> 'name',
					'class'		=> 'layout-name',
					'prefix'	=> $layout_prefix,
					'value'		=> $layout['name'],
					'prepend'	=> __('Name','acf')
				));
				
				?>
			</li>
			<li class="acf-fc-meta-display">
				<div class="acf-input-prepend"><?php _e('Layout','acf'); ?></div>
				<div class="acf-input-wrap select">
					<?php 
					
					acf_render_field(array(
						'type'		=> 'select',
						'name'		=> 'display',
						'prefix'	=> $layout_prefix,
						'value'		=> $layout['display'],
						'choices'	=> array(
							'table'			=> __('Table','acf'),
							'block'			=> __('Block','acf'),
							'row'			=> __('Row','acf')
						),
					));
					
					?>
				</div>
			</li>
			<li class="acf-fc-meta-min">
				<?php
						
				acf_render_field(array(
					'type'		=> 'text',
					'name'		=> 'min',
					'prefix'	=> $layout_prefix,
					'value'		=> $layout['min'],
					'prepend'	=> __('Min','acf')
				));
				
				?>
			</li>
			<li class="acf-fc-meta-max">
				<?php 
				
				acf_render_field(array(
					'type'		=> 'text',
					'name'		=> 'max',
					'prefix'	=> $layout_prefix,
					'value'		=> $layout['max'],
					'prepend'	=> __('Max','acf')
				));
				
				?>
			</li>
		</ul>
		<?php 
		
		// vars
		$args = array(
			'fields'	=> $layout['sub_fields'],
			'parent'	=> $field['ID']
		);
		
		acf_get_view('field-group-fields', $args);
		
		?>
	</td>
</tr>
<?php
	
		}
		// endforeach
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Button Label','acf'),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'button_label',
		));
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum Layouts','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum Layouts','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
		));
				
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
		
		// bail early if no value
		if( empty($value) || empty($field['layouts']) ) {
			
			return $value;
			
		}
		
		
		// value must be an array
		$value = acf_get_array( $value );
		
		
		// vars
		$rows = array();
		
		
		// sort layouts into names
		$layouts = array();
		foreach( $field['layouts'] as $k => $layout ) {
		
			$layouts[ $layout['name'] ] = $layout['sub_fields'];
			
		}
		
		
		// loop through rows
		foreach( $value as $i => $l ) {
			
			// append to $values
			$rows[ $i ] = array();
			$rows[ $i ]['acf_fc_layout'] = $l;
			
			
			// bail early if layout deosnt contain sub fields
			if( empty($layouts[ $l ]) ) {
				
				continue;
				
			}
			
			
			// get layout
			$layout = $layouts[ $l ];
			
			
			// loop through sub fields
			foreach( array_keys($layout) as $j ) {
				
				// get sub field
				$sub_field = $layout[ $j ];
				
				
				// bail ealry if no name (tab)
				if( acf_is_empty($sub_field['name']) ) continue;
				
				
				// update full name
				$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
				
				
				// get value
				$sub_value = acf_get_value( $post_id, $sub_field );
				
				
				// add value
				$rows[ $i ][ $sub_field['key'] ] = $sub_value;
				
			}
			// foreach
			
		}
		// foreach
		
		
		
		// return
		return $rows;
		
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
		if( empty($value) || empty($field['layouts']) ) {
			
			return false;
			
		}
		
		
		// sort layouts into names
		$layouts = array();
		foreach( $field['layouts'] as $k => $layout ) {
		
			$layouts[ $layout['name'] ] = $layout['sub_fields'];
			
		}
		
		
		// loop over rows
		foreach( array_keys($value) as $i ) {
			
			// get layout name
			$l = $value[ $i ]['acf_fc_layout'];
			
			
			// bail early if layout deosnt exist
			if( empty($layouts[ $l ]) ) continue;
			
			
			// get layout
			$layout = $layouts[ $l ];
			
			
			// loop through sub fields
			foreach( array_keys($layout) as $j ) {
				
				// get sub field
				$sub_field = $layout[ $j ];
				
				
				// bail ealry if no name (tab)
				if( acf_is_empty($sub_field['name']) ) continue;
				
				
				// extract value
				$sub_value = acf_extract_var( $value[ $i ], $sub_field['key'] );
				
				
				// update $sub_field name
				$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
					
				
				// format value
				$sub_value = acf_format_value( $sub_value, $post_id, $sub_field );
				
				
				// append to $row
				$value[ $i ][ $sub_field['_name'] ] = $sub_value;
				
			}
			
		}
		
		
		// return
		return $value;
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
		
		// vars
		$count = 0;
		
		
		// check if is value (may be empty string)
		if( is_array($value) ) {
			
			// remove acfcloneindex
			if( isset($value['acfcloneindex']) ) {
				unset($value['acfcloneindex']);
			}
			
			// count
			$count = count($value);
		}
		
		
		// validate required
		if( $field['required'] && !$count ) {
			$valid = false;
		}
		
		
		// validate min
		$min = (int) $field['min'];
		if( $min && $count < $min ) {
			
			// vars
			$error = __('This field requires at least {min} {label} {identifier}', 'acf');
			$identifier = _n('layout', 'layouts', $min);
			
 			// replace
 			$error = str_replace('{min}', $min, $error);
 			$error = str_replace('{label}', '', $error);
 			$error = str_replace('{identifier}', $identifier, $error);
 			
 			// return
			return $error;
		}
		
		
		// find layouts
		$layouts = array();
		foreach( array_keys($field['layouts']) as $i ) {
			
			// vars
			$layout = $field['layouts'][ $i ];
			
			// add count
			$layout['count'] = 0;
			
			// append
			$layouts[ $layout['name'] ] = $layout;
		}
		
		
		// validate value
		if( $count ) {
			
			// loop rows
			foreach( $value as $i => $row ) {	
				
				// get layout
				$l = $row['acf_fc_layout'];
				
				// bail if layout doesn't exist
				if( !isset($layouts[ $l ]) ) {
					continue;
				}
				
				// increase count
				$layouts[ $l ]['count']++;
				
				// bail if no sub fields
				if( empty($layouts[ $l ]['sub_fields']) ) {
					continue;
				}
				
				// loop sub fields
				foreach( $layouts[ $l ]['sub_fields'] as $sub_field ) {
					
					// get sub field key
					$k = $sub_field['key'];
					
					// bail if no value
					if( !isset($value[ $i ][ $k ]) ) {
						continue;
					}
					
					// validate
					acf_validate_value( $value[ $i ][ $k ], $sub_field, "{$input}[{$i}][{$k}]" );
				}
				// end loop sub fields
				
			}
			// end loop rows
		}
		
		
		// validate layouts
		foreach( $layouts as $layout ) {
			
			// validate min / max
			$min = (int) $layout['min'];
			$count = $layout['count'];
			$label = $layout['label'];
			
			if( $min && $count < $min ) {
				
				// vars
				$error = __('This field requires at least {min} {label} {identifier}', 'acf');
				$identifier = _n('layout', 'layouts', $min);
				
	 			// replace
	 			$error = str_replace('{min}', $min, $error);
	 			$error = str_replace('{label}', '"' . $label . '"', $error);
	 			$error = str_replace('{identifier}', $identifier, $error);
	 			
	 			// return
				return $error;				
			}
		}
		
		
		// return
		return $valid;
	}
	
	
	/*
	*  get_layout
	*
	*  This function will return a specific layout by name from a field
	*
	*  @type	function
	*  @date	15/2/17
	*  @since	5.5.8
	*
	*  @param	$name (string)
	*  @param	$field (array)
	*  @return	(array)
	*/
	
	function get_layout( $name = '', $field ) {
		
		// bail early if no layouts
		if( !isset($field['layouts']) ) return false;
		
		
		// loop
		foreach( $field['layouts'] as $layout ) {
			
			// match
			if( $layout['name'] === $name ) return $layout;
			
		}
		
		
		// return
		return false;
		
	}
	
	
	/*
	*  delete_row
	*
	*  This function will delete a value row
	*
	*  @type	function
	*  @date	15/2/17
	*  @since	5.5.8
	*
	*  @param	$i (int)
	*  @param	$field (array)
	*  @param	$post_id (mixed)
	*  @return	(boolean)
	*/
	
	function delete_row( $i = 0, $field, $post_id ) {
		
		// vars
		$value = acf_get_metadata( $post_id, $field['name'] );
		
		
		// bail early if no value
		if( !is_array($value) || !isset($value[ $i ]) ) return false;
		
		
		// get layout
		$layout = $this->get_layout($value[ $i ], $field);
		
		
		// bail early if no layout
		if( !$layout || empty($layout['sub_fields']) ) return false;
		
		
		// loop
		foreach( $layout['sub_fields'] as $sub_field ) {
			
			// modify name for delete
			$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
			
			
			// delete value
			acf_delete_value( $post_id, $sub_field );
			
		}
		
		
		// return
		return true;
		
	}
	
	
	/*
	*  update_row
	*
	*  This function will update a value row
	*
	*  @type	function
	*  @date	15/2/17
	*  @since	5.5.8
	*
	*  @param	$i (int)
	*  @param	$field (array)
	*  @param	$post_id (mixed)
	*  @return	(boolean)
	*/
	
	function update_row( $row, $i = 0, $field, $post_id ) {
		
		// bail early if no layout reference
		if( !is_array($row) || !isset($row['acf_fc_layout']) ) return false;
		
		
		// get layout
		$layout = $this->get_layout($row['acf_fc_layout'], $field);
		
		
		// bail early if no layout
		if( !$layout || empty($layout['sub_fields']) ) return false;
		
		
		// loop
		foreach( $layout['sub_fields'] as $sub_field ) {
			
			// value
			$value = null;
			

			// find value (key)
			if( isset($row[ $sub_field['key'] ]) ) {
				
				$value = $row[ $sub_field['key'] ];
			
			// find value (name)	
			} elseif( isset($row[ $sub_field['name'] ]) ) {
				
				$value = $row[ $sub_field['name'] ];
				
			// value does not exist	
			} else {
				
				continue;
				
			}
			
			
			// modify name for save
			$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
								
			
			// update field
			acf_update_value( $value, $post_id, $sub_field );
				
		}
		
		
		// return
		return true;
		
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
		
		// bail early if no layouts
		if( empty($field['layouts']) ) return $value;
		
		
		// vars
		$new_value = array();
		$old_value = acf_get_metadata( $post_id, $field['name'] );
		$old_value = is_array($old_value) ? $old_value : array();
		
		
		// update
		if( !empty($value) ) { $i = -1;
			
			// remove acfcloneindex
			if( isset($value['acfcloneindex']) ) {
			
				unset($value['acfcloneindex']);
				
			}
			
			
			// loop through rows
			foreach( $value as $row ) {	$i++;
				
				// bail early if no layout reference
				if( !is_array($row) || !isset($row['acf_fc_layout']) ) continue;
				
				
				// delete old row if layout has changed
				if( isset($old_value[ $i ]) && $old_value[ $i ] !== $row['acf_fc_layout'] ) {
					
					$this->delete_row( $i, $field, $post_id );
					
				}
				
				
				// update row
				$this->update_row( $row, $i, $field, $post_id );
				
				
				// append to order
				$new_value[] = $row['acf_fc_layout'];
				
			}
			
		}
		
		
		// vars
		$old_count = empty($old_value) ? 0 : count($old_value);
		$new_count = empty($new_value) ? 0 : count($new_value);
		
		
		// remove old rows
		if( $old_count > $new_count ) {
			
			// loop
			for( $i = $new_count; $i < $old_count; $i++ ) {
				
				$this->delete_row( $i, $field, $post_id );
				
			}
			
		}
		
		
		// save false for empty value
		if( empty($new_value) ) $new_value = '';
		
		
		// return
		return $new_value;
		
	}
	
	
	/*
	*  delete_value
	*
	*  description
	*
	*  @type	function
	*  @date	1/07/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function delete_value( $post_id, $key, $field ) {
		
		// vars
		$old_value = acf_get_metadata( $post_id, $field['name'] );
		$old_value = is_array($old_value) ? $old_value : array();
		
		
		// bail early if no rows or no sub fields
		if( empty($old_value) ) return;
				
		
		// loop
		foreach( array_keys($old_value) as $i ) {
				
			$this->delete_row( $i, $field, $post_id );
			
		}
			
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
		
		// loop
		if( !empty($field['layouts']) ) {
			
			foreach( $field['layouts'] as &$layout ) {
		
				unset($layout['sub_fields']);
				
			}
			
		}
		
		
		// return		
		return $field;
	}
	
	
	/*
	*  delete_field
	*
	*  description
	*
	*  @type	function
	*  @date	4/04/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function delete_field( $field ) {
		
		if( !empty($field['layouts']) ) {
			
			// loop through layouts
			foreach( $field['layouts'] as $layout ) {
				
				// loop through sub fields
				if( !empty($layout['sub_fields']) ) {
				
					foreach( $layout['sub_fields'] as $sub_field ) {
					
						acf_delete_field( $sub_field['ID'] );
						
					}
					// foreach
					
				}
				// if
				
			}
			// foreach
			
		}
		// if
		
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
		
		// vars
		$sub_fields = array();
		
		
		if( !empty($field['layouts']) ) {
			
			// loop through layouts
			foreach( $field['layouts'] as $layout ) {
				
				// extract sub fields
				$extra = acf_extract_var( $layout, 'sub_fields' );
				
				
				// merge
				if( !empty($extra) ) {
					
					$sub_fields = array_merge($sub_fields, $extra);
					
				}
				
			}
			// foreach
			
		}
		// if
		
		
		// save field to get ID
		$field = acf_update_field( $field );
		
		
		// duplicate sub fields
		acf_duplicate_fields( $sub_fields, $field['ID'] );
		
		
		// return		
		return $field;
		
	}
	
	
	/*
	*  ajax_layout_title
	*
	*  description
	*
	*  @type	function
	*  @date	2/03/2016
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_layout_title() {
		
		// options
   		$options = acf_parse_args( $_POST, array(
			'post_id'		=> 0,
			'i'				=> 0,
			'field_key'		=> '',
			'nonce'			=> '',
			'layout'		=> '',
			'value'			=> array()
		));
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		if( !$field ) die();
		
		
		// vars
		$layout = $this->get_layout( $options['layout'], $field );
		if( !$layout ) die();
		
		
		// title
		$title = $this->get_layout_title( $field, $layout, $options['i'], $options['value'] );
		
		
		// echo
		echo $title;
		die;
		
	}
	
	
	function get_layout_title( $field, $layout, $i, $value ) {
		
		// vars
		$rows = array();
		$rows[ $i ] = $value;
		
		
		// add loop
		acf_add_loop(array(
			'selector'	=> $field['name'],
			'name'		=> $field['name'],
			'value'		=> $rows,
			'field'		=> $field,
			'i'			=> $i,
			'post_id'	=> 0,
		));
		
		
		// vars
		$title = $layout['label'];
		
		
		// filters
		$title = apply_filters('acf/fields/flexible_content/layout_title', 							$title, $field, $layout, $i);
		$title = apply_filters('acf/fields/flexible_content/layout_title/name='.$field['_name'],	$title, $field, $layout, $i);
		$title = apply_filters('acf/fields/flexible_content/layout_title/key='.$field['key'],		$title, $field, $layout, $i);
		
		
		// remove loop
		acf_remove_loop();
		
		
		// prepend order
		$order = is_numeric($i) ? $i+1 : 0;
		$title = '<span class="acf-fc-layout-order">' . $order . '</span> ' . $title;
		
		
		// return
		return $title;
		
	}
	
	
	/*
	*  clone_any_field
	*
	*  This function will update clone field settings based on the origional field
	*
	*  @type	function
	*  @date	28/06/2016
	*  @since	5.3.8
	*
	*  @param	$clone (array)
	*  @param	$field (array)
	*  @return	$clone
	*/
	
	function clone_any_field( $field, $clone_field ) {
		
		// remove parent_layout
		// - allows a sub field to be rendered as a normal field
		unset($field['parent_layout']);
		
		
		// attempt to merger parent_layout
		if( isset($clone_field['parent_layout']) ) {
			
			$field['parent_layout'] = $clone_field['parent_layout'];
			
		}
		
		
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
		
		// loop
		if( !empty($field['layouts']) ) {
			
			foreach( $field['layouts'] as &$layout ) {
		
				$layout['sub_fields'] = acf_prepare_fields_for_export( $layout['sub_fields'] );
				
			}
			
		}
		
		
		// return
		return $field;
		
	}
	
	function prepare_any_field_for_export( $field ) {
		
		// remove parent_layout
		unset( $field['parent_layout'] );
		
		
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
		
		// bail early if no layouts
		if( empty($field['layouts']) ) return $field;
		
		
		// var
		$extra = array();
		
		
		// loop
		foreach( array_keys($field['layouts']) as $i ) {
			
			// extract layout
			$layout = acf_extract_var( $field['layouts'], $i );
			
			
			// get valid layout (fixes ACF4 export code bug undefined index 'key')
			if( empty($layout['key']) ) $layout['key'] = uniqid();
			
			
			// extract sub fields
			$sub_fields = acf_extract_var( $layout, 'sub_fields');
			
			
			// validate sub fields
			if( !empty($sub_fields) ) {
				
				// loop over sub fields
				foreach( array_keys($sub_fields) as $j ) {
					
					// extract sub field
					$sub_field = acf_extract_var( $sub_fields, $j );
					
					
					// attributes
					$sub_field['parent'] = $field['key'];
					$sub_field['parent_layout'] = $layout['key'];
					
					
					// append to extra
					$extra[] = $sub_field;
					
				}
				
			}
			
			
			// append to layout
			$field['layouts'][ $i ] = $layout;
		
		}
		
		
		// extra
		if( !empty($extra) ) {
			
			array_unshift($extra, $field);
			
			return $extra;
			
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  validate_any_field
	*
	*  This function will add compatibility for the 'column_width' setting
	*
	*  @type	function
	*  @date	30/1/17
	*  @since	5.5.6
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function validate_any_field( $field ) {
		
		// width has changed
		if( isset($field['column_width']) ) {
			
			$field['wrapper']['width'] = acf_extract_var($field, 'column_width');
			
		}
		
		
		// return
		return $field;
		
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
		
		// translate
		$field['button_label'] = acf_translate( $field['button_label'] );
		
		
		// loop
		if( !empty($field['layouts']) ) {
			
			foreach( $field['layouts'] as &$layout ) {
		
				$layout['label'] = acf_translate($layout['label']);
				
			}
			
		}
		
		
		// return
		return $field;
		
	}
	
}


// initialize
acf_register_field_type( 'acf_field_flexible_content' );

endif; // class_exists check

?>