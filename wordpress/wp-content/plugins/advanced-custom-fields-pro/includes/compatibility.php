<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Compatibility') ) :

class ACF_Compatibility {
	
	/**
	*  __construct
	*
	*  Sets up the class functionality.
	*
	*  @date	30/04/2014
	*  @since	5.0.0
	*
	*  @param	void
	*  @return	void
	*/
	function __construct() {
		
		// actions
		add_filter('acf/validate_field',						array($this, 'validate_field'), 20, 1);
		add_filter('acf/validate_field/type=textarea',			array($this, 'validate_textarea_field'), 20, 1);
		add_filter('acf/validate_field/type=relationship',		array($this, 'validate_relationship_field'), 20, 1);
		add_filter('acf/validate_field/type=post_object',		array($this, 'validate_relationship_field'), 20, 1);
		add_filter('acf/validate_field/type=page_link',			array($this, 'validate_relationship_field'), 20, 1);
		add_filter('acf/validate_field/type=image',				array($this, 'validate_image_field'), 20, 1);
		add_filter('acf/validate_field/type=file',				array($this, 'validate_image_field'), 20, 1);
		add_filter('acf/validate_field/type=wysiwyg',			array($this, 'validate_wysiwyg_field'), 20, 1);
		add_filter('acf/validate_field/type=date_picker',		array($this, 'validate_date_picker_field'), 20, 1);
		add_filter('acf/validate_field/type=taxonomy',			array($this, 'validate_taxonomy_field'), 20, 1);
		add_filter('acf/validate_field/type=date_time_picker',	array($this, 'validate_date_time_picker_field'), 20, 1);
		add_filter('acf/validate_field/type=user',				array($this, 'validate_user_field'), 20, 1);
		add_filter('acf/validate_field_group',					array($this, 'validate_field_group'), 20, 1);
		
		// location
		add_filter('acf/location/validate_rule/type=post_taxonomy', array($this, 'validate_post_taxonomy_location_rule'), 20, 1);
		add_filter('acf/location/validate_rule/type=post_category', array($this, 'validate_post_taxonomy_location_rule'), 20, 1);
	}
	
	/**
	*  validate_field
	*
	*  Adds compatibility with deprecated settings
	*
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	array $field The field array.
	*  @return	array $field
	*/
	function validate_field( $field ) {
		
		// conditional logic data structure changed to groups in version 5.0.0
		// convert previous data (status, rules, allorany) into groups
		if( isset($field['conditional_logic']['status']) ) {
			
			// check status
			if( $field['conditional_logic']['status'] ) {
				$field['conditional_logic'] = acf_convert_rules_to_groups($field['conditional_logic']['rules'], $field['conditional_logic']['allorany']);
			} else {
				$field['conditional_logic'] = 0;
			}
		}
		
		// return
		return $field;
	}
	
	/**
	*  validate_textarea_field
	*
	*  Adds compatibility with deprecated settings
	*
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	array $field The field array.
	*  @return	array $field
	*/
	function validate_textarea_field( $field ) {
		
		// formatting has been removed
		$formatting = acf_extract_var( $field, 'formatting' );
		if( $formatting === 'br' ) {
			$field['new_lines'] = 'br';
		}
		
		// return
		return $field;
	}
	
	/**
	*  validate_relationship_field
	*
	*  Adds compatibility with deprecated settings
	*
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	array $field The field array.
	*  @return	array $field
	*/
	function validate_relationship_field( $field ) {
		
		// remove 'all' from post_type
		if( acf_in_array('all', $field['post_type']) ) {
			$field['post_type'] = array();
		}
		
		// remove 'all' from taxonomy
		if( acf_in_array('all', $field['taxonomy']) ) {
			$field['taxonomy'] = array();
		}
		
		// result_elements is now elements
		if( isset($field['result_elements']) ) {
			$field['elements'] = acf_extract_var( $field, 'result_elements' );
		}
		
		// return
		return $field;
	}
	
	/**
	*  validate_image_field
	*
	*  Adds compatibility with deprecated settings
	*
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	array $field The field array.
	*  @return	array $field
	*/
	function validate_image_field( $field ) {
		
		// save_format is now return_format
		if( isset($field['save_format']) ) {
			$field['return_format'] = acf_extract_var( $field, 'save_format' );
		}
		
		// object is now array
		if( $field['return_format'] == 'object' ) {
			$field['return_format'] = 'array';
		}
		
		// return
		return $field;
	}
	
	/**
	*  validate_wysiwyg_field
	*
	*  Adds compatibility with deprecated settings
	*
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	array $field The field array.
	*  @return	array $field
	*/
	function validate_wysiwyg_field( $field ) {
		
		// media_upload is now numeric
		if( $field['media_upload'] === 'yes' ) {
			$field['media_upload'] = 1;
		} elseif( $field['media_upload'] === 'no' ) {
			$field['media_upload'] = 0;
		}
		
		// return
		return $field;
	}
	
	/**
	*  validate_date_picker_field
	*
	*  Adds compatibility with deprecated settings
	*
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	array $field The field array.
	*  @return	array $field
	*/
	function validate_date_picker_field( $field ) {
		
		// date_format has changed to display_format
		if( isset($field['date_format']) ) {
			
			// extract vars
			$date_format = $field['date_format'];
			$display_format = $field['display_format'];
			
			// convert from js to php
			$display_format = acf_convert_date_to_php( $display_format );
			
			// append settings
			$field['display_format'] = $display_format;
			$field['save_format'] = $date_format;
			
			// clean up
			unset($field['date_format']);
		}
		
		// return
		return $field;
	}
	
	/**
	*  validate_taxonomy_field
	*
	*  Adds compatibility with deprecated settings
	*
	*  @date	23/04/2014
	*  @since	5.2.7
	*
	*  @param	array $field The field array.
	*  @return	array $field
	*/
	function validate_taxonomy_field( $field ) {
		
		// load_save_terms deprecated in favour of separate save_terms
		if( isset($field['load_save_terms']) ) {
			$field['save_terms'] = acf_extract_var( $field, 'load_save_terms' );
		}
		
		// return
		return $field;
	}
	
	/**
	*  validate_date_time_picker_field
	*
	*  Adds compatibility with deprecated settings
	*
	*  @date	23/04/2014
	*  @since	5.2.7
	*
	*  @param	array $field The field array.
	*  @return	array $field
	*/
	function validate_date_time_picker_field( $field ) {
		
		// 3rd party date time picker
		// https://github.com/soderlind/acf-field-date-time-picker
		if( !empty($field['time_format']) ) {
			
			// extract vars
			$time_format = acf_extract_var( $field, 'time_format' );
			$date_format = acf_extract_var( $field, 'date_format' );
			$get_as_timestamp = acf_extract_var( $field, 'get_as_timestamp' );
			
			// convert from js to php
			$time_format = acf_convert_time_to_php( $time_format );
			$date_format = acf_convert_date_to_php( $date_format );
			
			// append settings
			$field['return_format'] = $date_format . ' ' . $time_format;
			$field['display_format'] = $date_format . ' ' . $time_format;
			
			// timestamp
			if( $get_as_timestamp === 'true' ) {
				$field['return_format'] = 'U';
			}
		}

		// return
		return $field;
	}
	
	/**
	*  validate_user_field
	*
	*  Adds compatibility with deprecated settings
	*
	*  @date	23/04/2014
	*  @since	5.2.7
	*
	*  @param	array $field The field array.
	*  @return	array $field
	*/
	function validate_user_field( $field ) {
		
		// remove 'all' from roles
		if( acf_in_array('all', $field['role']) ) {
			$field['role'] = '';
		}
		
		// field_type removed in favour of multiple
		if( isset($field['field_type']) ) {
			
			// extract vars
			$field_type = acf_extract_var( $field, 'field_type' );
			
			// multiple
			if( $field_type === 'multi_select' ) {
				$field['multiple'] = true;
			}
		}
		
		// return
		return $field;
	}
	
	/*
	*  validate_field_group
	*
	*  This function will provide compatibility with ACF4 field groups
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	$field_group
	*/
	function validate_field_group( $field_group ) {
		
		// vars
		$version = 5;
		
		// field group key was added in version 5.0.0
		// detect ACF4 data and generate key
		if( !$field_group['key'] ) {
			$version = 4;
			$field_group['key'] = uniqid('group_');
		}
		
		// prior to version 5.0.0, settings were saved in an 'options' array
		// extract and merge options into the field group
		if( isset($field_group['options']) ) {
			$options = acf_extract_var($field_group, 'options');
			$field_group = array_merge($field_group, $options);
		}
		
		// location data structure changed to groups in version 4.1.0
		// convert previous data (rules, allorany) into groups
		if( isset($field_group['location']['rules']) ) {
			$field_group['location'] = acf_convert_rules_to_groups($field_group['location']['rules'], $field_group['location']['allorany']);
		}
		
		// some location rule names have changed in version 5.0.0
		// loop over location data and modify rules
		$replace = array(
	 		'taxonomy'		=> 'post_taxonomy',
	 		'ef_media'		=> 'attachment',
	 		'ef_taxonomy'	=> 'taxonomy',
	 		'ef_user'		=> 'user_role',
	 		'user_type'		=> 'current_user_role' // 5.2.0
	 	);
	 	
	 	// only replace 'taxonomy' rule if is an ACF4 field group
	 	if( $version > 4 ) {
		 	unset($replace['taxonomy']);
	 	}
	 	
	 	// loop over location groups
		if( $field_group['location'] ) {
		foreach( $field_group['location'] as $i => $group ) {
			
			// loop over group rules
			if( $group ) {
			foreach( $group as $j => $rule ) {
				
				// migrate param
				if( isset($replace[ $rule['param'] ]) ) {
					$field_group['location'][ $i ][ $j ]['param'] = $replace[ $rule['param'] ];
				}
			}}
		}}
		
		// change layout to style (v5.0.0)
		if( isset($field_group['layout']) ) {
			$field_group['style'] = acf_extract_var($field_group, 'layout');
		}
		
		// change no_box to seamless (v5.0.0)
		if( $field_group['style'] === 'no_box' ) {
			$field_group['style'] = 'seamless';
		}
		
		//return
		return $field_group;
	}
	
	/**
	*  validate_post_taxonomy_location_rule
	*
	*  description
	*
	*  @date	27/8/18
	*  @since	5.7.4
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	function validate_post_taxonomy_location_rule( $rule ) {
		
		// previous versions of ACF (v4.4.12) saved value as term_id
		// convert term_id into "taxonomy:slug" string
		if( is_numeric($rule['value']) ) {
			$term = acf_get_term( $rule['value'] );
			if( $term ) {
				$rule['value'] = acf_encode_term($term);
			}
		}
		
		// return
		return $rule;
	}
	
}

acf_new_instance('ACF_Compatibility');

endif; // class_exists check

?>