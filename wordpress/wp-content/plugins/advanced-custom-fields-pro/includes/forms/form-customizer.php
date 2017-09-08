<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_form_customizer') ) :

class acf_form_customizer {
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
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
		$this->preview_values = array();
		$this->preview_fields = array();
		$this->preview_errors = array();
		
		
		// actions
		add_action('admin_enqueue_scripts',		array($this, 'admin_enqueue_scripts'));
		add_action('customize_preview_init',	array($this, 'customize_preview_init'), 1, 1);
		add_action('customize_save', 			array($this, 'customize_save'), 1, 1);
		
		
		// save
		add_filter('widget_update_callback', 	array($this, 'save_widget'), 10, 4);
		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts() {
		
		// validate screen
		if( !acf_is_screen('customize') ) return;
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action('acf/input/admin_footer', array($this, 'admin_footer'), 1);

	}
		
	
	/*
	*  save_widget
	*
	*  This function will hook into the widget update filter and save ACF data
	*
	*  @type	function
	*  @date	27/05/2015
	*  @since	5.2.3
	*
	*  @param	$instance (array) widget settings
	*  @param	$new_instance (array) widget settings
	*  @param	$old_instance (array) widget settings
	*  @param	$widget (object) widget info
	*  @return	$instance
	*/
	
	function save_widget( $instance, $new_instance, $old_instance, $widget ) {
		
		// bail ealry if not valid (customize + acf values + nonce)
		if( !isset($_POST['wp_customize']) || !isset($new_instance['acf']) || !acf_verify_nonce('widget') ) return $instance;
		
		
		// vars
		$data = array(
			'post_id'	=> "widget_{$widget->id}",
			'values'	=> array(),
			'fields'	=> array()
		);
		
		
		// append values
		$data['values'] = $new_instance['acf'];
		
		
		// append fields (name => key relationship) - used later in 'acf/get_field_reference' for customizer previews
		foreach( $data['values'] as $k => $v ) {
			
			// get field
			$field = acf_get_field( $k );
			
			
			// continue if no field
			if( !$field ) continue;
			
			
			// update
			$data['fields'][ $field['name'] ] = $field['key'];
			
		}
		
		
		// append data to instance
		$instance['acf'] = $data;
		
				
		
		// return
		return $instance;
		
	}
	
	
	/*
	*  get_customizer_widgets
	*
	*  This function will return an array of widget settings
	*
	*  @type	function
	*  @date	22/03/2016
	*  @since	5.3.2
	*
	*  @param	$customizer (object)
	*  @return	$value (mixed)
	*/
	
	function get_customizer_widgets( $customizer ) {
		
		// vars
		$widgets = array();
		$settings = $customizer->settings();
		
		
		// bail ealry if no settings
		if( empty($settings) ) return false;
		
		
		// loop over settings
		foreach( array_keys($settings) as $i ) {
			
			// vars
			$setting = $settings[ $i ];
			
			
			// bail ealry if not widget
			if( substr($setting->id, 0, 7) !== 'widget_' ) continue;
			
			
			// get value
			$value = $setting->post_value();	
			
			
			// set data	
			$setting->acf = acf_maybe_get($value, 'acf');
			
			
			// append
			$widgets[] = $setting;
						
		}
		
		
		// bail ealry if no preview values
		if( empty($widgets) ) return false;
		
		
		// return
		return $widgets;
		
	}
	
		
	/*
	*  customize_preview_init
	*
	*  This function is called when customizer preview is initialized
	*
	*  @type	function
	*  @date	22/03/2016
	*  @since	5.3.2
	*
	*  @param	$customizer (object)
	*  @return	n/a
	*/
	
	function customize_preview_init( $customizer ) {
		
		// get customizer settings (widgets)
		$settings = $customizer->settings();
		
		
		// bail ealry if no settings
		if( empty($settings) ) return;
		
		
		// append values
		foreach( $settings as $setting ) {
			
			// get $_POST value
			$value = $setting->post_value();
			
			
			// bail early if no acf
			if( !isset($value['acf']) ) continue;
			
			
			// append acf_value to preview_values
			$this->preview_values[ $value['acf']['post_id'] ] = $value['acf']['values'];
			$this->preview_fields[ $value['acf']['post_id'] ] = $value['acf']['fields'];
			
		}
		
		
		// bail ealry if no preview_values
		if( empty($this->preview_values) ) return;
		
		
		// add filter
		add_filter('acf/get_field_reference', 	array($this, 'get_field_reference'), 10, 3);
		
	}
	
	
	/*
	*  get_field_reference
	*
	*  This function will return a field_key for a given field name + post_id
	*  Normally, ACF would lookup the DB fro this connection, but a new preview widget has not yet saved anything to the DB
	*
	*  @type	function
	*  @date	12/05/2016
	*  @since	5.3.8
	*
	*  @param	$field_key (string)
	*  @param	$field_name (string)
	*  @param	$post_id (mixed)
	*  @return	$field_key
	*/
	
	function get_field_reference( $field_key, $field_name, $post_id ) {
		
		// look for reference
		if( isset($this->preview_fields[ $post_id ][ $field_name ]) ) {
			
			// update key
			$field_key = $this->preview_fields[ $post_id ][ $field_name ];
			$field_value = $this->preview_values[ $post_id ][ $field_key ];
			
			
			// cache value
			acf_set_cache("get_value/post_id={$post_id}/name={$field_name}", $field_value);
			
		}
		
		
		// return
		return $field_key;
		
	}
		
	
	/*
	*  customize_save
	*
	*  This function is called when customizer saves a widget.
	*  Normally, the widget_update_callback filter would be used, but the customizer disables this and runs a custom action
	*  class-customizer-settings.php will save the widget data via the function set_root_value which uses update_option
	*
	*  @type	function
	*  @date	22/03/2016
	*  @since	5.3.2
	*
	*  @param	$customizer (object)
	*  @return	n/a
	*/
	
	function customize_save( $customizer ) {
		
		// get customizer settings (widgets)
		$settings = $customizer->settings();
		
		
		// bail ealry if no settings
		if( empty($settings) ) return;
		
		
		// append values
		foreach( $settings as $setting ) {
			
			// get $_POST value
			$value = $setting->post_value();
			
			
			// bail early if no acf
			if( !isset($value['acf']) ) continue;
			
			
			// save acf data
			acf_save_post( $value['acf']['post_id'], $value['acf']['values'] );
			
			
			// remove [acf] data from saved widget array
			$id_data = $setting->id_data();
			add_filter('pre_update_option_' . $id_data['base'], array($this, 'pre_update_option'), 10, 3);
			
		}
		
	}
	
	
	/*
	*  pre_update_option
	*
	*  this function will remove the [acf] data from widget insance
	*
	*  @type	function
	*  @date	22/03/2016
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function pre_update_option( $value, $option, $old_value ) {
		
		// bail ealry if no value
		if( empty($value) ) return $value;
		
		
		// loop over widgets 
		// WP saves all widgets (of the same type) as an array of widgets
		foreach( $value as $i => $widget ) {
			
			// bail ealry if no acf
			if( !isset($widget['acf']) ) continue;
			
			
			// remove widget
			unset($value[ $i ]['acf']);
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  admin_footer
	*
	*  This function will add some custom HTML to the footer of the edit page
	*
	*  @type	function
	*  @date	11/06/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_footer() {
		
?>
<script type="text/javascript">
(function($) {
	
	// customizer saves widget on any input change, so unload is not needed
	acf.unload.active = 0;
	
	
	// hack customizer function to remove bug caused by WYSIWYG field using aunique ID
	// customizer compares returned AJAX HTML with the HTML of the widget form.
	// the _getInputsSignature() function is used to generate a string based of input name + id.
	// because ACF generates a unique ID on the WYSIWYG field, this string will not match causing the preview function to bail.
	// an attempt was made to remove the WYSIWYG unique ID, but this caused multiple issues in the wp-admin and altimately doesn't make sense with the tinymce rule that all editors must have a unique ID.
	// source: wp-admin/js/customize-widgets.js
	
	// vars
	var WidgetControl = wp.customize.Widgets.WidgetControl.prototype;
	
	
	// backup functions
	WidgetControl.__getInputsSignature = WidgetControl._getInputsSignature;
	WidgetControl.__setInputState = WidgetControl._setInputState;
	
	
	// modify __getInputsSignature
	WidgetControl._getInputsSignature = function( inputs ) {
		
		// vars
		var signature = this.__getInputsSignature( inputs );
			safe = [];
		
		
		// split
		signature = signature.split(';');
		
		
		// loop
		for( var i in signature ) {
			
			// vars
			var bit = signature[i];
			
			
			// bail ealry if acf is found
			if( bit.indexOf('acf') !== -1 ) continue;
			
			
			// append
			safe.push( bit );
			
		}
		
		
		// update
		signature = safe.join(';');
		
		
		// return
		return signature;
		
	};
	
	
	// modify _setInputState
	// this function deosn't seem to run on widget title/content, only custom fields
	// either way, this function is not needed and will break ACF fields 
	WidgetControl._setInputState = function( input, state ) {
		
		return true;
			
	};
		
})(jQuery);	
</script>
<?php
		
	}
	
}

new acf_form_customizer();

endif;

?>