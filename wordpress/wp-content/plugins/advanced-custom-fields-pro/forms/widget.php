<?php

/*
*  ACF Widget Form Class
*
*  All the logic for adding fields to widgets
*
*  @class 		acf_form_widget
*  @package		ACF
*  @subpackage	Forms
*/

if( ! class_exists('acf_form_widget') ) :

class acf_form_widget {
	
	
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
		$this->preview_reference = array();
		$this->preview_errors = array();
		
		
		// actions
		add_action('admin_enqueue_scripts',		array($this, 'admin_enqueue_scripts'));
		add_action('in_widget_form', 			array($this, 'edit_widget'), 10, 3);
		add_action('customize_save', 			array($this, 'customize_save'), 1, 1);
		add_action('customize_preview_init',	array($this, 'customize_preview_init'), 1, 1);
		
		
		// filters
		add_filter('widget_update_callback', 	array($this, 'widget_update_callback'), 10, 4);
		
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
		if( acf_is_screen('widgets') || acf_is_screen('customize') ) {
		
			// valid
			
		} else {
			
			return;
			
		}
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action('acf/input/admin_footer', array($this, 'admin_footer'), 1);

	}
	
	
	/*
	*  edit_widget
	*
	*  This function will render the fields for a widget form
	*
	*  @type	function
	*  @date	11/06/2014
	*  @since	5.0.0
	*
	*  @param	$widget (object)
	*  @param	$return (null)
	*  @param	$instance (object)
	*  @return	$post_id (int)
	*/
	
	function edit_widget( $widget, $return, $instance ) {
		
		// vars
		$post_id = 0;
		
		
		// get id
		if( $widget->number !== '__i__' ) {
		
			$post_id = "widget_{$widget->id}";
			
		}
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'widget' => $widget->id_base
		));
		
		
		// render
		if( !empty($field_groups) ) {
			
			// render post data
			acf_form_data(array( 
				'post_id'	=> $post_id, 
				'nonce'		=> 'widget' 
			));
			
			
			foreach( $field_groups as $field_group ) {
				
				$fields = acf_get_fields( $field_group );
				
				acf_render_fields( $post_id, $fields, 'div', $field_group['instruction_placement'] );
				
			}
			
			
			// jQuery selector looks odd, but is necessary due to WP adding an incremental number into the ID
			// - not possible to find number via PHP parameters
			if( $widget->updated ): ?>
			<script type="text/javascript">
			(function($) {
				
				acf.do_action('append', $('[id^="widget"][id$="<?php echo $widget->id; ?>"]') );
				
			})(jQuery);	
			</script>
			<?php endif;
				
		}
		
	}
	
	
	/*
	*  widget_update_callback
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
	
	function widget_update_callback( $instance, $new_instance, $old_instance, $widget ) {
		
		// bail early if empty
		if( empty($_POST['acf']) ) return $instance;
		
		
		// bail early if no nonce
		if( !acf_verify_nonce('widget') ) return $instance;
		
		
		// customizer autosave preview
		if( !empty($_POST['wp_customize']) ) {
			
			return $this->customizer_widget_update_callback($instance, $new_instance, $old_instance, $widget);
			
		}
		
		
		// bail early if not valid
		if( !acf_validate_save_post() ) return $instance;
		
		
		// save
		acf_save_post( "widget_{$widget->id}" );	
		
		
		// return
		return $instance;
		
	}
	
	
	/*
	*  customizer_widget_update_callback
	*
	*  customizer specific verison of above function
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
	
	function customizer_widget_update_callback( $instance, $new_instance, $old_instance, $widget ) {
		
		// bail early if not valid
		if( !acf_validate_save_post() ) {
			
			// vars
			$errors = acf_get_validation_errors();
			$total = count($errors);
			
			
			// message
			$message = __('Validation failed', 'acf') . '. ';
			$message .= sprintf(_n( '1 field requires attention.', '%d fields require attention.', $total, 'acf' ), $total);
			
			
			// add missing WP JS to remove spinning
			// also set state to 'saved' which disables the save button
			$message .= '<script type="text/javascript">';
			$message .= '(function($) {';
			$message .= '$(".customize-control.previewer-loading").removeClass("previewer-loading"); ';
			$message .= 'wp.customize.state("saved").set( true ); ';
			$message .= '})(jQuery);';
			$message .= '</script>';
						
			
			// return JSON error
			wp_send_json_error(array(
				'message'	=> $message
			));
			
		} else {
			
			$instance['acf'] = array(
				'ID'		=> 'widget_' . $widget->id,
				'values'	=> false,
				'reference'	=> array()
			);
			
			
			// append acf $_POST data to instance
			// this allows preview JS data to contain acf values
			$instance['acf']['values'] = $_POST['acf'];
			
			
			// backup name => key reference
			// this will allow the customizer preview to correctly load the field when attempting to run acf_load_value and acf_format_value functions on newly added widgets 
			foreach( $_POST['acf'] as $k => $v ) {
				
				// get field
				$field = acf_get_field( $k );
				
				
				// continue if no field
				if( !$field ) continue;
				
				
				// update
				$instance['acf']['reference'][ $field['name'] ] = $field['key'];
				
			}
			
		}
		
		
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
		
		// get widgets
		$widgets = $this->get_customizer_widgets($customizer);
		
		
		// bail ealry if no widgets
		if( empty($widgets) ) return;
		
		
		// append values
		foreach( $widgets as $widget ) {
			
			// bail early if no acf
			if( empty($widget->acf) ) continue;
			
			
			// append acf_value to preview_values
			$this->preview_values[ $widget->acf['ID'] ] = $widget->acf['values'];
			$this->preview_reference[ $widget->acf['ID'] ] = $widget->acf['reference'];
			
		}
		
		
		// bail ealry if no preview_values
		if( empty($this->preview_values) ) return;
		
		
		// add filter
		add_filter('acf/load_value', 			array($this, 'load_value'), 10, 3);
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
		if( isset($this->preview_reference[ $post_id ][ $field_name ]) ) {
			
			$field_key = $this->preview_reference[ $post_id ][ $field_name ];
			
		}
		
		
		// return
		return $field_key;
		
	}
	
	
	/*
	*  load_value
	*
	*  This function will override a field's value with the preview_value set in previous functions
	*
	*  @type	function
	*  @date	22/03/2016
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// acf/load_value filter is run first (before type, name, key)
		// use this filter to append to the key filter and ensure this is run last
		// could use set_cache in the future to remove this load_filter completley
		// but don't want to clog up cache with multiple widget's values
		
		
		// look for value
		if( isset($this->preview_values[ $post_id ][ $field['key'] ]) ) {
			
			// add filter to override the $value
			add_filter('acf/load_value/key='.$field['key'], array($this, 'load_value_2'), 99, 3);
			
			
			// return null and prevent any DB logic from field type functions
			return null;
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	function load_value_2( $value, $post_id, $field ) {
		
		// look for value
		$value = $this->preview_values[ $post_id ][ $field['key'] ];
			
		
		// remove this filter (only run once)
		remove_filter('acf/load_value/key='.$field['key'], array($this, 'load_value_2'), 99, 3);
		
		
		// return
		return $value;
		
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
		
		// get widgets
		$widgets = $this->get_customizer_widgets($customizer);
		
		
		// bail ealry if no widgets
		if( empty($widgets) ) return;
		
		
		// append values
		foreach( $widgets as $widget ) {
			
			// bail early if no acf_value
			if( !$widget->acf ) continue;
			
			// fake post data
			$_POST['acf'] = $widget->acf['values'];
			
			
			// save
			acf_save_post( $widget->acf['ID'] );
			
			
			// get widget base
			$id_data = $widget->id_data();
			
			
			// remove [acf] data from saved widget array
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
	
	 acf.add_filter('get_fields', function( $fields ){
	 	
	 	// widgets
	 	$fields = $fields.not('#available-widgets .acf-field');
	 	
	 	
	 	// customizer
	 	$fields = $fields.not('.widget-tpl .acf-field');
	 	
	 	
	 	// return
	 	return $fields;
	 	
    });
	
	
	$('#widgets-right').on('click', '.widget-control-save', function( e ){
		
		// vars
		var $form = $(this).closest('form');
		
		
		// bail early if not active
		if( !acf.validation.active ) {
		
			return true;
			
		}
		
		
		// ignore validation (only ignore once)
		if( acf.validation.ignore ) {
		
			acf.validation.ignore = 0;
			return true;
			
		}
		
		
		// bail early if this form does not contain ACF data
		if( !$form.find('#acf-form-data').exists() ) {
		
			return true;
		
		}

		
		// stop WP JS validation
		e.stopImmediatePropagation();
		
		
		// store submit trigger so it will be clicked if validation is passed
		acf.validation.$trigger = $(this);
		
		
		// run validation
		acf.validation.fetch( $form );
		
		
		// stop all other click events on this input
		return false;
		
	});
	
	
	$(document).on('click', '.widget-top', function(){
		
		var $el = $(this).parent().children('.widget-inside');
		
		setTimeout(function(){
			
			acf.get_fields('', $el).each(function(){
				
				acf.do_action('show_field', $(this));	
				
			});
			
		}, 250);
				
	});
	
	$(document).on('widget-added', function( e, $widget ){
		
		// - use delay to avoid rendering issues with customizer (ensures div is visible)
		setTimeout(function(){
			
			acf.do_action('append', $widget );
			
		}, 100);
		
	});
	
	$(document).on('widget-saved widget-updated', function( e, $widget ){
		
		// unlock form
		acf.validation.toggle( $widget, 'unlock' );
		
		
		// submit
		acf.do_action('submit', $widget );
		
	});
	
	<?php if( acf_is_screen('customize') ): ?>
	
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
	
	<?php endif; ?>
		
})(jQuery);	
</script>
<?php
		
	}
	
}

new acf_form_widget();

endif;

?>