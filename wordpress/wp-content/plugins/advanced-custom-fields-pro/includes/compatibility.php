<?php 

class acf_compatibility {
	
	/*
	*  __construct
	*
	*  description
	*
	*  @type	function
	*  @date	30/04/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function __construct() {
		
		// fields
		add_filter('acf/validate_field',					array($this, 'validate_field'), 20, 1);
		add_filter('acf/validate_field/type=textarea',		array($this, 'validate_textarea_field'), 20, 1);
		add_filter('acf/validate_field/type=relationship',	array($this, 'validate_relationship_field'), 20, 1);
		add_filter('acf/validate_field/type=post_object',	array($this, 'validate_relationship_field'), 20, 1);
		add_filter('acf/validate_field/type=page_link',		array($this, 'validate_relationship_field'), 20, 1);
		add_filter('acf/validate_field/type=image',			array($this, 'validate_image_field'), 20, 1);
		add_filter('acf/validate_field/type=file',			array($this, 'validate_image_field'), 20, 1);
		add_filter('acf/validate_field/type=wysiwyg',		array($this, 'validate_wysiwyg_field'), 20, 1);
		add_filter('acf/validate_field/type=date_picker',	array($this, 'validate_date_picker_field'), 20, 1);
		add_filter('acf/validate_field/type=taxonomy',		array($this, 'validate_taxonomy_field'), 20, 1);
		add_filter('acf/validate_field/type=date_time_picker',	array($this, 'validate_date_time_picker_field'), 20, 1);
		add_filter('acf/validate_field/type=user',			array($this, 'validate_user_field'), 20, 1);
		
		
		// field groups
		add_filter('acf/validate_field_group',				array($this, 'validate_field_group'), 20, 1);
		
	}
	
	
	/*
	*  validate_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function validate_field( $field ) {
		
		// conditional logic has changed
		if( isset($field['conditional_logic']['status']) ) {
			
			// extract logic
			$logic = acf_extract_var( $field, 'conditional_logic' );
			
			
			// disabled
			if( !empty($logic['status']) ) {
				
				// reset
				$field['conditional_logic'] = array();
				
				
				// vars
				$group = 0;
		 		$all_or_any = $logic['allorany'];
		 		
		 		
		 		// loop over rules
		 		if( !empty($logic['rules']) ) {
			 		
			 		foreach( $logic['rules'] as $rule ) {
				 		
					 	// sperate groups?
					 	if( $all_or_any == 'any' ) {
					 	
						 	$group++;
						 	
					 	}
					 	
					 	
					 	// add to group
					 	$field['conditional_logic'][ $group ][] = $rule;
			 	
				 	}
				 	
		 		}
			 	
			 	
			 	// reset keys
				$field['conditional_logic'] = array_values($field['conditional_logic']);
				
				
			} else {
				
				$field['conditional_logic'] = 0;
				
			}
		 	
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  validate_relationship_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function validate_relationship_field( $field ) {
		
		// force array
		$field['post_type'] = acf_get_array($field['post_type']);
		$field['taxonomy'] = acf_get_array($field['taxonomy']);
		
		
		// remove 'all' from post_type
		if( acf_in_array('all', $field['post_type']) ) {
			
			$field['post_type'] = array();
			
		}
		
		
		// remove 'all' from taxonomy
		if( acf_in_array('all', $field['taxonomy']) ) {
			
			$field['taxonomy'] = array();
			
		}
		
		
		// save_format is now return_format
		if( !empty($field['result_elements']) ) {
			
			$field['elements'] = acf_extract_var( $field, 'result_elements' );
			
		}
		
		
		
		// return
		return $field;
	}
	
	
	/*
	*  validate_textarea_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
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
	
	
	/*
	*  validate_image_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function validate_image_field( $field ) {
		
		// save_format is now return_format
		if( !empty($field['save_format']) ) {
			
			$field['return_format'] = acf_extract_var( $field, 'save_format' );
			
		}
		
		
		// object is now array
		if( $field['return_format'] == 'object' ) {
			
			$field['return_format'] = 'array';
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  validate_wysiwyg_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
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
	
	
	/*
	*  validate_date_picker_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function validate_date_picker_field( $field ) {
		
		// v4 used date_format
		if( !empty($field['date_format']) ) {
			
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
	
	
	/*
	*  validate_taxonomy_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function validate_taxonomy_field( $field ) {
		
		// 5.2.7
		if( isset($field['load_save_terms']) ) {
			
			$field['save_terms'] = $field['load_save_terms'];
			
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  validate_date_time_picker_field
	*
	*  This function will provide compatibility with existing 3rd party fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
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
	
	
	/*
	*  validate_user_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function validate_user_field( $field ) {
		
		// remove 'all' from roles
		if( acf_in_array('all', $field['role']) ) {
			
			$field['role'] = '';
			
		}
		
		
		// field_type removed in favour of multiple
		if( !empty($field['field_type']) ) {
			
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
		
		
		// add missing 'key' (v5.0.0)
		if( empty($field_group['key']) ) {
			
			// update version
			$version = 4;
			
			
			// add missing key
			$field_group['key'] = empty($field_group['id']) ? uniqid('group_') : 'group_' . $field_group['id'];
			
		}
		
		
		// extract options (v5.0.0)
		if( !empty($field_group['options']) ) {
			
			$options = acf_extract_var($field_group, 'options');
			$field_group = array_merge($field_group, $options);
			
		}
		
		
		// location rules changed to groups (v5.0.0)
		if( !empty($field_group['location']['rules']) ) {
			
			// extract location
			$location = acf_extract_var( $field_group, 'location' );
			
			
			// reset location
			$field_group['location'] = array();
			
			
			// vars
			$group = 0;
	 		$all_or_any = $location['allorany'];
	 		
	 		
	 		// loop over rules
	 		if( !empty($location['rules']) ) {
		 		
		 		foreach( $location['rules'] as $rule ) {
			 		
				 	// sperate groups?
				 	if( $all_or_any == 'any' ) $group++;
				 	
				 	
				 	// add to group
				 	$field_group['location'][ $group ][] = $rule;
		 	
			 	}
			 	
	 		}
		 	
		 	
		 	// reset keys
			$field_group['location'] = array_values($field_group['location']);
		 	
		}
		
		
		// some location rules have changed (v5.0.0)
		if( !empty($field_group['location']) ) {
			
			// param changes
		 	$replace = array(
		 		'taxonomy'		=> 'post_taxonomy',
		 		'ef_media'		=> 'attachment',
		 		'ef_taxonomy'	=> 'taxonomy',
		 		'ef_user'		=> 'user_role',
		 		'user_type'		=> 'current_user_role' // 5.2.0
		 	);
		 	
		 	
		 	// remove conflicting param
		 	if( $version == 5 ) {
			 	
			 	unset($replace['taxonomy']);
			 	
		 	}
		 	
		 	
			// loop over location groups
			foreach( $field_group['location'] as $i => $group ) {
				
				// bail early if group is empty
				if( empty($group) ) continue;
				
				
				// loop over group rules
				foreach( $group as $ii => $rule ) {
					
					// migrate param
					if( isset($replace[ $rule['param'] ]) ) {
						
						$rule['param'] = $replace[ $rule['param'] ];
						
					}
									 	
				 	
				 	// update
				 	$group[ $ii ] = $rule;
				 	
				}
				
				
				// update
				$field_group['location'][ $i ] = $group;
				
			}
			
		}
		
		
		// change layout to style (v5.0.0)
		if( !empty($field_group['layout']) ) {
		
			$field_group['style'] = acf_extract_var($field_group, 'layout');
			
		}
		
		
		// change no_box to seamless (v5.0.0)
		if( $field_group['style'] === 'no_box' ) {
		
			$field_group['style'] = 'seamless';
			
		}
		
		
		//return
		return $field_group;
		
	}
	
}

new acf_compatibility();

?>