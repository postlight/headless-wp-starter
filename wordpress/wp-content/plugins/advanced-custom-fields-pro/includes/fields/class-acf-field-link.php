<?php

if( ! class_exists('acf_field_link') ) :

class acf_field_link extends acf_field {
	
	
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
		$this->name = 'link';
		$this->label = __("Link",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'return_format'	=> 'array',
		);
    	
	}
		
	
	/*
	*  get_link
	*
	*  description
	*
	*  @type	function
	*  @date	16/5/17
	*  @since	5.5.13
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function get_link( $value = '' ) {
		
		// vars
		$link = array(
			'title'		=> '',
			'url'		=> '',
			'target'	=> ''
		);
		
		
		// array (ACF 5.6.0)
		if( is_array($value) ) {
			
			$link = array_merge($link, $value);
		
		// post id (ACF < 5.6.0)
		} elseif( is_numeric($value) ) {
			
			$link['title'] = get_the_title( $value );
			$link['url'] = get_permalink( $value );
		
		// string (ACF < 5.6.0)
		} elseif( is_string($value) ) {
			
			$link['url'] = $value;
			
		}
		
		
		// return
		return $link;
		
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
	
	function render_field( $field ){
		
		// vars
		$div = array(
			'id'	=> $field['id'],
			'class'	=> $field['class'] . ' acf-link',
		);
		
		
		// render scripts/styles
		acf_enqueue_uploader();
		
		
		// get link
		$link = $this->get_link( $field['value'] );
		
		
		// classes
		if( $link['url'] ) {
			$div['class'] .= ' -value';
		}
		
		if( $link['target'] === '_blank' ) {
			$div['class'] .= ' -external';
		}
		
		/*<textarea id="<?php echo esc_attr($field['id']); ?>-textarea"><?php
			echo esc_textarea('<a href="'.$link['url'].'" target="'.$link['target'].'">'.$link['title'].'</a>');
		?></textarea>*/
?>
<div <?php acf_esc_attr_e($div); ?>>
	
	<div class="acf-hidden">
		<a class="link-node" href="<?php echo esc_url($link['url']); ?>" target="<?php echo esc_attr($link['target']); ?>"><?php echo esc_html($link['title']); ?></a>
		<?php foreach( $link as $k => $v ): ?>
			<?php acf_hidden_input(array( 'class' => "input-$k", 'name' => $field['name'] . "[$k]", 'value' => $v )); ?>
		<?php endforeach; ?>
	</div>
	
	<a href="#" class="button" data-name="add" target=""><?php _e('Select Link', 'acf'); ?></a>
	
	<div class="link-wrap">
		<span class="link-title"><?php echo esc_html($link['title']); ?></span>
		<a class="link-url" href="<?php echo esc_url($link['url']); ?>" target="_blank"><?php echo esc_html($link['url']); ?></a>
		<i class="acf-icon -link-ext acf-js-tooltip" title="<?php _e('Opens in a new window/tab', 'acf'); ?>"></i><?php
		?><a class="acf-icon -pencil -clear acf-js-tooltip" data-name="edit" href="#" title="<?php _e('Edit', 'acf'); ?>"></a><?php
		?><a class="acf-icon -cancel -clear acf-js-tooltip" data-name="remove" href="#" title="<?php _e('Remove', 'acf'); ?>"></a>
	</div>
	
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
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf'),
			'instructions'	=> __('Specify the returned value on front end','acf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'array'			=> __("Link Array",'acf'),
				'url'			=> __("Link URL",'acf'),
			)
		));
						
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
		if( empty($value) ) return $value;
		
		
		// get link
		$link = $this->get_link( $value );
		
		
		// format value
		if( $field['return_format'] == 'url' ) {
			
			return $link['url'];
			
		}
		
		
		// return link
		return $link;
		
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
		
		// bail early if not required
		if( !$field['required'] ) return $valid;
		
		
		// URL is required
		if( empty($value) || empty($value['url']) ) {
			
			return false;
			
		}
		
		
		// return
		return $valid;
		
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
	
		// URL is required
		if( empty($value) || empty($value['url']) ) {
			
			return false;
			
		}
		
		
		// return
		return $value;
		
	}
	
}


// initialize
acf_register_field_type( 'acf_field_link' );

endif; // class_exists check

?>