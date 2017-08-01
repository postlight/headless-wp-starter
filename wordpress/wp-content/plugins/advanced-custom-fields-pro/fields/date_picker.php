<?php

/*
*  ACF Date Picker Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_date_picker
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_date_picker') ) :

class acf_field_date_picker extends acf_field {
	
	
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
		$this->name = 'date_picker';
		$this->label = __("Date Picker",'acf');
		$this->category = 'jquery';
		$this->defaults = array(
			'display_format'	=> 'd/m/Y',
			'return_format'		=> 'd/m/Y',
			'first_day'			=> 1
		);
		$this->l10n = array(
			'closeText'			=> _x('Done',	'Date Picker JS closeText',		'acf'),
			'currentText'		=> _x('Today',	'Date Picker JS currentText',	'acf'),
			'nextText'			=> _x('Next',	'Date Picker JS nextText',		'acf'),
			'prevText'			=> _x('Prev',	'Date Picker JS prevText',		'acf'),
			'weekHeader'		=> _x('Wk',		'Date Picker JS weekHeader',	'acf'),
		);
		
		
		// actions
		add_action('init', array($this, 'init'));
		
		
		// do not delete!
    	parent::__construct();
	}
	
	
	/*
	*  init
	*
	*  This function is run on the 'init' action to set the field's $l10n data. Before the init action, 
	*  access to the $wp_locale variable is not possible.
	*
	*  @type	action (init)
	*  @date	3/09/13
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function init() {
		
		// globals
		global $wp_locale;
		
		
		// append
		$this->l10n = array_merge($this->l10n, array(
			'monthNames'        => array_values( $wp_locale->month ),
			'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
			'dayNames'          => array_values( $wp_locale->weekday ),
			'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
			'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev )
		));
		
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
		
		// bail ealry if no enqueue
	   	if( !acf_get_setting('enqueue_datepicker') ) return;
	   	
	   	
		// script
		wp_enqueue_script('jquery-ui-datepicker');
		
		
		// style
		wp_enqueue_style('acf-datepicker', acf_get_dir('assets/inc/datepicker/jquery-ui.min.css'), '', '1.11.4' );
		
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
		
		// format value
		$hidden_value = '';
		$display_value = '';
		
		if( $field['value'] ) {
			
			$hidden_value = acf_format_date( $field['value'], 'Ymd' );
			$display_value = acf_format_date( $field['value'], $field['display_format'] );
			
		}
		
		
		// vars
		$e = '';
		$div = array(
			'class'					=> 'acf-date-picker acf-input-wrap',
			'data-date_format'		=> acf_convert_date_to_js($field['display_format']),
			'data-first_day'		=> $field['first_day'],
		);
		$hidden = array(
			'id'					=> $field['id'],
			'class' 				=> 'input-alt',
			'type'					=> 'hidden',
			'name'					=> $field['name'],
			'value'					=> $hidden_value,
		);
		$input = array(
			'class' 				=> 'input',
			'type'					=> 'text',
			'value'					=> $display_value,
		);
		
		
		// save_format - compatibility with ACF < 5.0.0
		if( !empty($field['save_format']) ) {
			
			// add custom JS save format
			$div['data-save_format'] = $field['save_format'];
			
			// revert hidden input value to raw DB value
			$hidden['value'] = $field['value'];
			
			// remove formatted value (will do this via JS)
			$input['value'] = '';
			
		}
		
		
		// html
		$e .= '<div ' . acf_esc_attr($div) . '>';
			$e .= '<input ' . acf_esc_attr($hidden). '/>';
			$e .= '<input ' . acf_esc_attr($input). '/>';
		$e .= '</div>';
		
		
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
		
		// global
		global $wp_locale;
		
		
		// display_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Display Format','acf'),
			'instructions'	=> __('The format displayed when editing a post','acf'),
			'type'			=> 'radio',
			'name'			=> 'display_format',
			'other_choice'	=> 1,
			'choices'		=> array(
				'd/m/Y'			=> date_i18n('d/m/Y'),
				'm/d/Y'			=> date_i18n('m/d/Y'),
				'F j, Y'		=> date_i18n('F j, Y'),
			)
		));
				
		
		// save_format - compatibility with ACF < 5.0.0
		if( !empty($field['save_format']) ) {
			
			// save_format
			acf_render_field_setting( $field, array(
				'label'			=> __('Save Format','acf'),
				'instructions'	=> __('The format used when saving a value','acf'),
				'type'			=> 'text',
				'name'			=> 'save_format',
				//'readonly'		=> 1 // this setting was not readonly in v4
			));
			
		} else {
			
			// return_format
			acf_render_field_setting( $field, array(
				'label'			=> __('Return Format','acf'),
				'instructions'	=> __('The format returned via template functions','acf'),
				'type'			=> 'radio',
				'name'			=> 'return_format',
				'other_choice'	=> 1,
				'choices'		=> array(
					'd/m/Y'			=> date_i18n('d/m/Y'),
					'm/d/Y'			=> date_i18n('m/d/Y'),
					'F j, Y'		=> date_i18n('F j, Y'),
					'Ymd'			=> date_i18n('Ymd'),
				)
			));
			
		}
		
		
		// first_day
		acf_render_field_setting( $field, array(
			'label'			=> __('Week Starts On','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'first_day',
			'choices'		=> array_values( $wp_locale->weekday )
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
		
		// save_format - compatibility with ACF < 5.0.0
		if( !empty($field['save_format']) ) {
			
			return $value;
			
		}
		
		
		// return
		return acf_format_date( $value, $field['return_format'] );
		
	}
	
}


// initialize
acf_register_field_type( new acf_field_date_picker() );

endif; // class_exists check

?>