<?php

/*
*  ACF oEmbed Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_oembed
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_oembed') ) :

class acf_field_oembed extends acf_field {
	
	
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
		$this->name = 'oembed';
		$this->label = __("oEmbed",'acf');
		$this->category = 'content';
		$this->defaults = array(
			'width'		=> '',
			'height'	=> '',
		);
		$this->width = 640;
		$this->height = 390;
		
		
		// extra
		add_action('wp_ajax_acf/fields/oembed/search',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/oembed/search',	array($this, 'ajax_query'));
		
		
		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  prepare_field
	*
	*  This function will prepare the field for input
	*
	*  @type	function
	*  @date	14/2/17
	*  @since	5.5.8
	*
	*  @param	$field (array)
	*  @return	(int)
	*/
	
	function prepare_field( $field ) {
		
		// defaults
		if( !$field['width'] ) $field['width'] = $this->width;
		if( !$field['height'] ) $field['height'] = $this->height;
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  wp_oembed_get
	*
	*  description
	*
	*  @type	function
	*  @date	24/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function wp_oembed_get( $url = '', $width = 0, $height = 0 ) {
		
		// vars
		$embed = '';
		$res = array(
			'width'		=> $width,
			'height'	=> $height
		);
		
		
		// get emebed
		$embed = @wp_oembed_get( $url, $res );
		
		
		// try shortcode
		if( !$embed ) {
			
			 // global
			global $wp_embed;
			
			
			// get emebed
			$embed = $wp_embed->shortcode($res, $url);
		
		}
				
		
		// return
		return $embed;
	}
	
	
	/*
	*  ajax_query
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query() {
		
		// validate
		if( !acf_verify_ajax() ) die();
		
		
		// get choices
		$response = $this->get_ajax_query( $_POST );
		
		
		// return
		wp_send_json($response);
			
	}
	
	
	/*
	*  get_ajax_query
	*
	*  This function will return an array of data formatted for use in a select2 AJAX response
	*
	*  @type	function
	*  @date	15/10/2014
	*  @since	5.0.9
	*
	*  @param	$options (array)
	*  @return	(array)
	*/
	
	function get_ajax_query( $args = array() ) {
		
   		// defaults
   		$args = acf_parse_args($args, array(
			's'				=> '',
			'field_key'		=> '',
		));
		
		
		// load field
		$field = acf_get_field( $args['field_key'] );
		if( !$field ) return false;
		
		
		// prepare field to correct width and height
		$field = $this->prepare_field($field);
		
		
		// vars
		$response = array(
			'url'	=> $args['s'],
			'html'	=> $this->wp_oembed_get($args['s'], $field['width'], $field['height'])
		);
		
		
		// return
		return $response;
			
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
		
		// atts
		$atts = array(
			'class' => 'acf-oembed',
		);
		
		
		// value
		if( $field['value'] ) $atts['class'] .= ' has-value';
		
?>
<div <?php acf_esc_attr_e($atts) ?>>
	<div class="acf-hidden">
		<input type="hidden" data-name="value-input" name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr($field['value']); ?>" />
	</div>
	<div class="title acf-soh">
		
		<div class="title-value">
			<h4 data-name="value-title"><?php echo $field['value']; ?></h4>
		</div>
		
		<div class="title-search">
			
			<input data-name="search-input" type="text" placeholder="<?php _e("Enter URL", 'acf'); ?>" autocomplete="off" />
		</div>
		
		<a data-name="clear-button" href="#" class="acf-icon -cancel grey acf-soh-target"></a>
		
	</div>
	<div class="canvas">
		
		<div class="canvas-loading">
			<i class="acf-loading"></i>
		</div>
		
		<div class="canvas-error">
			<p><strong><?php _e("Error.", 'acf'); ?></strong> <?php _e("No embed found for the given URL.", 'acf'); ?></p>
		</div>
		
		<div class="canvas-media" data-name="value-embed">
			<?php if( $field['value'] ) echo $this->wp_oembed_get($field['value'], $field['width'], $field['height']); ?>
		</div>
		
		<i class="acf-icon -picture hide-if-value"></i>
		
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
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field_settings( $field ) {
		
		// width
		acf_render_field_setting( $field, array(
			'label'			=> __('Embed Size','acf'),
			'type'			=> 'text',
			'name'			=> 'width',
			'prepend'		=> __('Width', 'acf'),
			'append'		=> 'px',
			'placeholder'	=> $this->width
		));
		
		
		// height
		acf_render_field_setting( $field, array(
			'label'			=> __('Embed Size','acf'),
			'type'			=> 'text',
			'name'			=> 'height',
			'prepend'		=> __('Height', 'acf'),
			'append'		=> 'px',
			'placeholder'	=> $this->height,
			'_append' 		=> 'width'
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
		
		
		// prepare field to correct width and height
		$field = $this->prepare_field($field);
		
		
		// get oembed
		$value = $this->wp_oembed_get($value, $field['width'], $field['height']);
		
		
		// return
		return $value;
		
	}
	
}


// initialize
acf_register_field_type( new acf_field_oembed() );

endif; // class_exists check

?>