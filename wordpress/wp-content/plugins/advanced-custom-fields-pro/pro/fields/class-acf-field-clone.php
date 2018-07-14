<?php

if( ! class_exists('acf_field_clone') ) :

class acf_field_clone extends acf_field {
	
	
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
		$this->name = 'clone';
		$this->label = _x('Clone', 'noun', 'acf');
		$this->category = 'layout';
		$this->defaults = array(
			'clone' 		=> '',
			'prefix_label'	=> 0,
			'prefix_name'	=> 0,
			'display'		=> 'seamless',
			'layout'		=> 'block'
		);
		$this->cloning = array();
		$this->have_rows = 'single';
		
		
		// register filter
		acf_enable_filter('clone');
		
		
		// ajax
		add_action('wp_ajax_acf/fields/clone/query', array($this, 'ajax_query'));
		
		
		// filters
		add_filter('acf/get_fields', 		array($this, 'acf_get_fields'), 5, 2);
		add_filter('acf/prepare_field',		array($this, 'acf_prepare_field'), 10, 1);
		add_filter('acf/clone_field',		array($this, 'acf_clone_field'), 10, 2);
    	
	}
	
	
	/*
	*  is_enabled
	*
	*  This function will return true if acf_local functionality is enabled
	*
	*  @type	function
	*  @date	14/07/2016
	*  @since	5.4.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function is_enabled() {
		
		return acf_is_filter_enabled('clone');
		
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
		
		// bail early if not enabled
		if( !$this->is_enabled() ) return $field;
		
		
		// load sub fields
		// - sub field name's will be modified to include prefix_name settings
		$field['sub_fields'] = $this->get_cloned_fields( $field );
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  acf_get_fields
	*
	*  This function will hook into the 'acf/get_fields' filter and inject/replace seamless clones fields
	*
	*  @type	function
	*  @date	17/06/2016
	*  @since	5.3.8
	*
	*  @param	$fields (array)
	*  @param	$parent (array)
	*  @return	$fields
	*/
	
	function acf_get_fields( $fields, $parent ) {
		
		// bail early if empty
		if( empty($fields) ) return $fields;
		
		
		// bail early if not enabled
		if( !$this->is_enabled() ) return $fields;
		
		
		// vars
		$i = 0;
		
		
		// loop
		while( $i < count($fields) ) {
			
			// vars
			$field = $fields[ $i ];
			
			
			// $i
			$i++;
			
			
			// bail ealry if not a clone field
			if( $field['type'] != 'clone' ) continue;
			
			
			// bail ealry if not seamless
			if( $field['display'] != 'seamless' ) continue;
			
			
			// replace this clone field with sub fields
			$i--;
			array_splice($fields, $i, 1, $field['sub_fields']);

		}
		
		
		// return
		return $fields;
		
	}
	
	
	/*
	*  get_cloned_fields
	*
	*  This function will return an array of fields for a given clone field
	*
	*  @type	function
	*  @date	28/06/2016
	*  @since	5.3.8
	*
	*  @param	$field (array)
	*  @param	$parent (array)
	*  @return	(array)
	*/
	
	function get_cloned_fields( $field ) {
		
		// vars
		$fields = array();
		
		
		// bail early if no clone setting
		if( empty($field['clone']) ) return $fields;
		
		
		// bail ealry if already cloning this field (avoid infinite looping)
		if( isset($this->cloning[ $field['key'] ]) ) return $fields;
		
		
		// update local ref
		$this->cloning[ $field['key'] ] = 1;
		
		
		// loop
		foreach( $field['clone'] as $selector ) {
			
			// field group
			if( acf_is_field_group_key($selector) ) {
				
				// vars
				$field_group = acf_get_field_group($selector);
				$field_group_fields = acf_get_fields($field_group);
				
				
				// bail early if no fields
				if( !$field_group_fields ) continue;
				
				
				// append
				$fields = array_merge($fields, $field_group_fields);
				
			// field
			} elseif( acf_is_field_key($selector) ) {
				
				// append
				$fields[] = acf_get_field($selector);
				
			}
			
		}
		
		
		// field has ve been loaded for this $parent, time to remove cloning ref
		unset( $this->cloning[ $field['key'] ] );
		
		
		// clear false values (fields that don't exist)
		$fields = array_filter($fields);
		
		
		// bail early if no sub fields
		if( empty($fields) ) return array();
		
		
		// loop
		// run acf_clone_field() on each cloned field to modify name, key, etc
		foreach( array_keys($fields) as $i ) {
			
			$fields[ $i ] = acf_clone_field( $fields[ $i ], $field );
				
		}
		
		
		// return
		return $fields;
		
	}
	
	
	/*
	*  acf_clone_field
	*
	*  This function is run when cloning a clone field
	*  Important to run the acf_clone_field function on sub fields to pass on settings such as 'parent_layout' 
	*
	*  @type	function
	*  @date	28/06/2016
	*  @since	5.3.8
	*
	*  @param	$field (array)
	*  @param	$clone_field (array)
	*  @return	$field
	*/
	
	function acf_clone_field( $field, $clone_field ) {
		
		// bail early if this field is being cloned by some other kind of field (future proof)
		if( $clone_field['type'] != 'clone' ) return $field;
		
		
		// backup (used later)
		// - backup only once (cloned clone fields can cause issues)
		if( !isset($field['__key']) ) {
			
			$field['__key'] = $field['key'];
			$field['__name'] = $field['_name'];
			$field['__label'] = $field['label'];
			
		}
		
		
		// seamless
		if( $clone_field['display'] == 'seamless' ) {
			
			// modify key 
			// - this will allow sub clone fields to correctly load values for the same cloned field
			// - the original key will later be restored by acf/prepare_field allowing conditional logic JS to work
			$field['key'] = $clone_field['key'] . '_' . $field['key'];
			
			
			// modify prefix allowing clone field to save sub fields
			// - only used for parent seamless fields. Block or sub field's prefix will be overriden which also works
			$field['prefix'] = $clone_field['prefix'] . '[' . $clone_field['key'] . ']';
			
			
			// modify parent
			$field['parent'] = $clone_field['parent'];
			
						
			// label_format
			if( $clone_field['prefix_label'] ) {
				
				$field['label'] = $clone_field['label'] . ' ' . $field['label'];
				
			}
		}
		
		
		// prefix_name
		if( $clone_field['prefix_name'] ) {
			
			// modify the field name
			// - this will allow field to load / save correctly
			$field['name'] = $clone_field['name'] . '_' . $field['_name'];
			
			
			// modify the field _name (orig name)
			// - this will allow fields to correctly understand the modified field
			if( $clone_field['display'] == 'seamless' ) {
				
				$field['_name'] = $clone_field['_name'] . '_' . $field['_name'];
				
			}
		}
		
		
		// required
		if( $clone_field['required'] ) {
			
			$field['required'] = 1;
			
		}
		
		
		// type specific
		// note: seamless clone fields will not be triggered
		if( $field['type'] == 'clone' ) {
			
			$field = $this->acf_clone_clone_field( $field, $clone_field );
			
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  acf_clone_clone_field
	*
	*  This function is run when cloning a clone field
	*  Important to run the acf_clone_field function on sub fields to pass on settings such as 'parent_layout'
	*  Do not delete! Removing this logic causes major issues with cloned clone fields within a flexible content layout.
	*
	*  @type	function
	*  @date	28/06/2016
	*  @since	5.3.8
	*
	*  @param	$field (array)
	*  @param	$clone_field (array)
	*  @return	$field
	*/
	
	function acf_clone_clone_field( $field, $clone_field ) {
		
		// modify the $clone_field name
		// This seems odd, however, the $clone_field is later passed into the acf_clone_field() function
		// Do not delete! 
		// when cloning a clone field, it is important to also change the _name too
		// this allows sub clone fields to appear correctly in get_row() row array
		if( $field['prefix_name'] ) {
			
			$clone_field['name'] = $field['_name'];
			$clone_field['_name'] = $field['_name'];
			
		}
		
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return $field;
		
		
		// loop
		foreach( $field['sub_fields'] as &$sub_field ) {
			
			// clone
			$sub_field = acf_clone_field( $sub_field, $clone_field );
			
		}
		
		
		// return
		return $field;
			
	}
	
	
	/*
	*  prepare_field_for_db
	*
	*  description
	*
	*  @type	function
	*  @date	4/11/16
	*  @since	5.5.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_db( $field ) {
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return $field;
		
		
		// bail early if name == _name
		// this is a parent clone field and does not require any modification to sub field names
		if( $field['name'] == $field['_name'] ) return $field;
		
		
		// this is a sub field
		// _name = 'my_field'
		//  name = 'rep_0_my_field'
		// modify all sub fields to add 'rep_0_' name prefix (prefix_name setting has already been applied)
		$length = strlen($field['_name']);
		$prefix = substr($field['name'], 0, -$length);
		
		
		// bail ealry if _name is not found at the end of name (unknown potential error)
		if( $prefix . $field['_name'] !== $field['name'] ) return $field;
		
		//acf_log('== prepare_field_for_db ==');
		//acf_log('- clone name:', $field['name']);
		//acf_log('- clone _name:', $field['_name']);
		
		// loop
		foreach( $field['sub_fields'] as &$sub_field ) {
			
			$sub_field['name'] = $prefix . $sub_field['name'];
			
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
			
			// add value
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
			$value[ $sub_field['__name'] ] = $sub_value;
			
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
			
			
			// restore original field key
			$sub_field = $this->acf_prepare_field( $sub_field );
			
			
			// update value
			acf_update_value( $v, $post_id, $sub_field );
			
		}
		
		
		// return
		return '';
		
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
			
			
			// restore label
			$sub_field['label'] = $sub_field['__label'];
			
			
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
		$label_placement = $field['layout'] == 'block' ? 'top' : 'left';
		
		
		// html
		echo '<div class="acf-clone-fields acf-fields -'.$label_placement.' -border">';
			
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
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field_settings( $field ) {
		
		// temp enable 'local' to allow .json fields to be displayed
		acf_enable_filter('local');
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Fields', 'acf'),
			'instructions'	=> __('Select one or more fields you wish to clone','acf'),
			'type'			=> 'select',
			'name'			=> 'clone',
			'multiple' 		=> 1,
			'allow_null' 	=> 1,
			'choices'		=> $this->get_clone_setting_choices( $field['clone'] ),
			'ui'			=> 1,
			'ajax'			=> 1,
			'ajax_action'	=> 'acf/fields/clone/query',
			'placeholder'	=> '',
		));
		
		acf_disable_filter('local');
		
		
		// display
		acf_render_field_setting( $field, array(
			'label'			=> __('Display','acf'),
			'instructions'	=> __('Specify the style used to render the clone field', 'acf'),
			'type'			=> 'select',
			'name'			=> 'display',
			'class'			=> 'setting-display',
			'choices'		=> array(
				'group'			=> __('Group (displays selected fields in a group within this field)','acf'),
				'seamless'		=> __('Seamless (replaces this field with selected fields)','acf'),
			),
		));
		
		
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
		
		
		// prefix_label
		$instructions = __('Labels will be displayed as %s', 'acf');
		$instructions = sprintf($instructions, '<code class="prefix-label-code-1"></code>');
		acf_render_field_setting( $field, array(
			'label'			=> __('Prefix Field Labels','acf'),
			'message'	=> $instructions,
			//'instructions_placement'	=> 'field',
			'name'			=> 'prefix_label',
			'class'			=> 'setting-prefix-label',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// prefix_name
		$instructions = __('Values will be saved as %s', 'acf');
		$instructions = sprintf($instructions, '<code class="prefix-name-code-1"></code>');
		acf_render_field_setting( $field, array(
			'label'			=> __('Prefix Field Names','acf'),
			'message'	=> $instructions,
			//'instructions_placement'	=> 'field',
			'name'			=> 'prefix_name',
			'class'			=> 'setting-prefix-name',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
	}
	
	
	/*
	*  get_clone_setting_choices
	*
	*  This function will return an array of choices data for Select2
	*
	*  @type	function
	*  @date	17/06/2016
	*  @since	5.3.8
	*
	*  @param	$value (mixed)
	*  @return	(array)
	*/
	
	function get_clone_setting_choices( $value ) {
		
		// vars
		$choices = array();
		
		
		// bail early if no $value
		if( empty($value) ) return $choices;
		
		
		// force value to array
		$value = acf_get_array( $value );
			
			
		// loop
		foreach( $value as $v ) {
			
			$choices[ $v ] = $this->get_clone_setting_choice( $v );
			
		}
		
		
		// return
		return $choices;
		
	}
	
	
	/*
	*  get_clone_setting_choice
	*
	*  This function will return the label for a given clone choice
	*
	*  @type	function
	*  @date	17/06/2016
	*  @since	5.3.8
	*
	*  @param	$selector (mixed)
	*  @return	(string)
	*/
	
	function get_clone_setting_choice( $selector = '' ) {
		
		// bail early no selector
		if( !$selector ) return '';
		
		
		// ajax_fields
		if( isset($_POST['fields'][ $selector ]) ) {
			
			return $this->get_clone_setting_field_choice( $_POST['fields'][ $selector ] );
						
		}
		
		
		// field
		if( acf_is_field_key($selector) ) {
			
			return $this->get_clone_setting_field_choice( acf_get_field($selector) );
			
		}
		
		
		// group
		if( acf_is_field_group_key($selector) ) {
			
			return $this->get_clone_setting_group_choice( acf_get_field_group($selector) );
			
		} 
		
		
		// return
		return $selector;
		
	}
	
	
	/*
	*  get_clone_setting_field_choice
	*
	*  This function will return the text for a field choice
	*
	*  @type	function
	*  @date	20/07/2016
	*  @since	5.4.0
	*
	*  @param	$field (array)
	*  @return	(string)
	*/
	
	function get_clone_setting_field_choice( $field ) {
		
		// bail early if no field
		if( !$field ) return __('Unknown field', 'acf');
		
		
		// title
		$title = $field['label'] ? $field['label'] : __('(no title)', 'acf');
					
		
		// append type
		$title .= ' (' . $field['type'] . ')';
		
		
		// ancestors
		// - allow for AJAX to send through ancestors count
		$ancestors = isset($field['ancestors']) ? $field['ancestors'] : count(acf_get_field_ancestors($field));
		$title = str_repeat('- ', $ancestors) . $title;
		
		
		// return
		return $title;
		
	}
	
	
	/*
	*  get_clone_setting_group_choice
	*
	*  This function will return the text for a group choice
	*
	*  @type	function
	*  @date	20/07/2016
	*  @since	5.4.0
	*
	*  @param	$field_group (array)
	*  @return	(string)
	*/
	
	function get_clone_setting_group_choice( $field_group ) {
		
		// bail early if no field group
		if( !$field_group ) return __('Unknown field group', 'acf');
		
		
		// return
		return sprintf( __('All fields from %s field group', 'acf'), $field_group['title'] );
		
	}
	
	
	/*
	*  ajax_query
	*
	*  description
	*
	*  @type	function
	*  @date	17/06/2016
	*  @since	5.3.8
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query() {
		
		// validate
		if( !acf_verify_ajax() ) die();
		
		
		// disable field to allow clone fields to appear selectable
		acf_disable_filter('clone');
		
		
   		// options
   		$options = acf_parse_args($_POST, array(
			'post_id'	=> 0,
			'paged'		=> 0,
			's'			=> '',
			'title'		=> '',
			'fields'	=> array()
		));
		
		
		// vars
		$results = array();
		$s = false;
		$i = -1;
		$limit = 20;
		$range_start = $limit * ($options['paged']-1); 	//	0,	20,	40
		$range_end = $range_start + ($limit-1);			//	19,	39,	59
		
		
		// search
		if( $options['s'] !== '' ) {
			
			// strip slashes (search may be integer)
			$s = wp_unslash( strval($options['s']) );
			
		}		
		
		
		// load groups
		$field_groups = acf_get_field_groups();
		$field_group = false;
		
		
		// bail early if no field groups
		if( empty($field_groups) ) die();
		
		
		// move current field group to start
		foreach( array_keys($field_groups) as $j ) {
			
			// check ID
			if( $field_groups[ $j ]['ID'] !== $options['post_id'] ) continue;
			
			
			// extract field group and move to start
			$field_group = acf_extract_var($field_groups, $j);
			
			
			// field group found, stop looking
			break;
			
		}
		
		
		// if field group was not found, this is a new field group (not yet saved)
		if( !$field_group ) {
			
			$field_group = array(
				'ID'	=> $options['post_id'],
				'title'	=> $options['title'],
				'key'	=> '',
			);
			
		}
		
		
		// move current field group to start of list
		array_unshift($field_groups, $field_group);
		
		
		// loop
		foreach( $field_groups as $field_group ) {
			
			// vars
			$fields = false;
			$ignore_s = false;
			$data = array(
				'text'		=> $field_group['title'],
				'children'	=> array()
			);
			
			
			// get fields
			if( $field_group['ID'] == $options['post_id'] ) {
				
				$fields = $options['fields'];
				
			} else {
				
				$fields = acf_get_fields( $field_group );
				$fields = acf_prepare_fields_for_import( $fields );
			
			}
			
			
			// bail early if no fields
			if( !$fields ) continue;
			
			
			// show all children for field group search match
			if( $s !== false && stripos($data['text'], $s) !== false ) {
				
				$ignore_s = true;
				
			}
			
			
			// populate children
			$children = array();
			$children[] = $field_group['key'];
			foreach( $fields as $field ) { $children[] = $field['key']; }
			
			
			// loop
			foreach( $children as $child ) {
				
				// bail ealry if no key (fake field group or corrupt field)
				if( !$child ) continue;
				
				
				// vars
				$text = false;
				
				
				// bail early if is search, and $text does not contain $s
				if( $s !== false && !$ignore_s ) {
					
					// get early
					$text = $this->get_clone_setting_choice( $child );
					
					
					// search
					if( stripos($text, $s) === false ) continue;
					
				}
				
				
				// $i
				$i++;
				
				
				// bail early if $i is out of bounds
				if( $i < $range_start || $i > $range_end ) continue;
				
				
				
				// load text
				if( $text === false ) $text = $this->get_clone_setting_choice( $child );
				
				
				// append
				$data['children'][] = array(
					'id'	=> $child,
					'text'	=> $text
				);
				
			}
			
			
			// bail early if no children
			// - this group contained fields, but none shown on this page
			if( empty($data['children']) ) continue;
			
			
			// append
			$results[] = $data;
			
			
			// end loop if $i is out of bounds
			// - no need to look further
			if( $i > $range_end ) break;
				
		}
		
		
		// return
		acf_send_ajax_results(array(
			'results'	=> $results,
			'limit'		=> $limit
		));
		
	}
	
	
	/*
	*  acf_prepare_field
	*
	*  This function will restore a field's key ready for input
	*
	*  @type	function
	*  @date	6/09/2016
	*  @since	5.4.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function acf_prepare_field( $field ) {
		
		// bail ealry if not cloned
		if( empty($field['_clone']) ) return $field;
		
		
		// restore key
		if( isset($field['__key']) ) {
			$field['key'] = $field['__key'];
		}
		
		
		// return
		return $field;
		
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
		foreach( array_keys($field['sub_fields']) as $i ) {
			
			// get sub field
			$sub_field = $field['sub_fields'][ $i ];
			$k = $sub_field['key'];
			
			
			// bail early if valu enot set (conditional logic?)
			if( !isset($value[ $k ]) ) continue;
			
			
			// validate
			acf_validate_value( $value[ $k ], $sub_field, "{$input}[{$k}]" );
			
		}
		
		
		// return
		return $valid;
		
	}
	
}


// initialize
acf_register_field_type( 'acf_field_clone' );

endif; // class_exists check

?>
