<?php 

/*
*  acf_get_field_reference()
*
*  This function will find the $field_key that is related to the $field_name.
*  This is know as the field value reference
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$field_name (mixed) the name of the field. eg 'sub_heading'
*  @param	$post_id (int) the post_id of which the value is saved against
*  @return	$reference (string)	a string containing the field_key
*/

function acf_get_field_reference( $field_name, $post_id ) {
	
	// vars
	$field_key = acf_get_metadata( $post_id, $field_name, true );
	
	
	// filter
	$field_key = apply_filters('acf/get_field_reference', $field_key, $field_name, $post_id);
	
	
	// return
	return $field_key;
	
}


/*
*  get_field()
*
*  This function will return a custom field value for a specific field name/key + post_id.
*  There is a 3rd parameter to turn on/off formating. This means that an image field will not use 
*  its 'return option' to format the value but return only what was saved in the database
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the value as described above
*  @return	(mixed)
*/
 
function get_field( $selector, $post_id = false, $format_value = true ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// create dummy field
	if( !$field ) {
		
		$field = acf_get_valid_field(array(
			'name'	=> $selector,
			'key'	=> '',
			'type'	=> '',
		));
		
		
		// prevent formatting
		$format_value = false;
		
	}
	
	
	// get value for field
	$value = acf_get_value( $post_id, $field );
	
	
	// format value
	if( $format_value ) {
		
		// get value for field
		$value = acf_format_value( $value, $post_id, $field );
		
	}
	
	
	// return
	return $value;
	 
}


/*
*  the_field()
*
*  This function is the same as echo get_field().
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	n/a
*/

function the_field( $selector, $post_id = false, $format_value = true ) {
	
	$value = get_field($selector, $post_id, $format_value);
	
	if( is_array($value) ) {
		
		$value = @implode( ', ', $value );
		
	}
	
	echo $value;
	
}


/*
*  get_field_object()
*
*  This function will return an array containing all the field data for a given field_name
*
*  @type	function
*  @since	3.6
*  @date	3/02/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @param	$load_value (boolean) whether or not to load the field value
*  @return	$field (array)
*/

function get_field_object( $selector, $post_id = false, $format_value = true, $load_value = true ) {
	
	// compatibilty
	if( is_array($format_value) ) extract( $format_value );
	
	
	// get valid post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field key
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// bail early if no field found
	if( !$field ) return false;
	
	
	// load value
	if( $load_value ) {
	
		$field['value'] = acf_get_value( $post_id, $field );
		
	}
	
	
	// format value
	if( $format_value ) {
		
		// get value for field
		$field['value'] = acf_format_value( $field['value'], $post_id, $field );
		
	}
	
	
	// return
	return $field;
	
}


/*
*  get_fields()
*
*  This function will return an array containing all the custom field values for a specific post_id.
*  The function is not very elegant and wastes a lot of PHP memory / SQL queries if you are not using all the values.
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @return	(array)	associative array where field name => field value
*/

function get_fields( $post_id = false, $format_value = true ) {
	
	// vars
	$fields = get_field_objects( $post_id, $format_value );
	$meta = array();
	
	
	// bail early
	if( !$fields ) return false;
	
	
	// populate
	foreach( $fields as $k => $field ) {
		
		$meta[ $k ] = $field['value'];
		
	}
	
	
	// return
	return $meta;	
	
}


/*
*  get_field_objects()
*
*  This function will return an array containing all the custom field objects for a specific post_id.
*  The function is not very elegant and wastes a lot of PHP memory / SQL queries if you are not using all the fields / values.
*
*  @type	function
*  @since	3.6
*  @date	29/01/13
*
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @param	$load_value (boolean) whether or not to load the field value
*  @return	(array)	associative array where field name => field
*/

function get_field_objects( $post_id = false, $format_value = true, $load_value = true ) {
	
	// global
	global $wpdb;
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	$info = acf_get_post_id_info( $post_id );
	
	
	// vars
	$meta = array();
	$fields = array();
	
				
	// get field_names
	if( $info['type'] == 'post' ) {
		
		$meta = get_post_meta( $info['id'] );
	
	} elseif( $info['type'] == 'user' ) {
		
		$meta = get_user_meta( $info['id'] );
		
	} elseif( $info['type'] == 'comment' ) {
		
		$meta = get_comment_meta( $info['id'] );
		
	} elseif( $info['type'] == 'term' ) {
		
		$meta = get_term_meta( $info['id'] );
		
	} else {
		
		$rows = $wpdb->get_results($wpdb->prepare(
			"SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
			$post_id . '_%' ,
			'_' . $post_id . '_%' 
		), ARRAY_A);
		
		if( !empty($rows) ) {
			
			foreach( $rows as $row ) {
				
				// vars
				$name = $row['option_name'];
				$prefix = $post_id . '_';
				$_prefix = '_' . $prefix;
				
				
				// remove prefix from name
				if( strpos($name, $prefix) === 0 ) {
					
					$name = substr($name, strlen($prefix));
					
				} elseif( strpos($name, $_prefix) === 0 ) {
					
					$name = '_' . substr($name, strlen($_prefix));
					
				}
				
				$meta[ $name ][] = $row['option_value'];
				
			}
			
		}
		
	}
	
	
	// bail early if no meta
	if( empty($meta) ) return false;
	
	
	// populate vars
	foreach( $meta as $k => $v ) {
		
		// does a field key exist for this value?
		if( !isset($meta["_{$k}"]) ) continue;
		
		
		// get field
		$field_key = $meta["_{$k}"][0];
		$field = acf_maybe_get_field( $field_key );
		
		
		// bail early if no field, or if the field's name is different to $k
		// - solves problem where sub fields (and clone fields) are incorrectly allowed
		if( !$field || $field['name'] !== $k ) continue;
		
		
		// load value
		if( $load_value ) {
		
			$field['value'] = acf_get_value( $post_id, $field );
			
		}
		
		
		// format value
		if( $format_value ) {
			
			// get value for field
			$field['value'] = acf_format_value( $field['value'], $post_id, $field );
			
		}
		
					
		// append to $value
		$fields[ $field['name'] ] = $field;
		
	}
 	
 	 	
	// no value
	if( empty($fields) ) return false;
	
	
	// return
	return $fields;
}


/*
*  have_rows
*
*  This function will instantiate a global variable containing the rows of a repeater or flexible content field,
*  after which, it will determine if another row exists to loop through
*
*  @type	function
*  @date	2/09/13
*  @since	4.3.0
*
*  @param	$field_name (string) the field name
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function have_rows( $selector, $post_id = false ) {
	
	// reference
	$_post_id = $post_id;
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// vars
	$key = "selector={$selector}/post_id={$post_id}";
	$active_loop = acf_get_loop('active');
	$previous_loop = acf_get_loop('previous');
	$new_parent_loop = false;
	$new_child_loop = false;
	$sub_field = false;
	$sub_exists = false;
	$change = false;
	
	
	// no active loops
	if( !$active_loop ) {
		
		// create a new loop
		$new_parent_loop = true;
	
	// loop has changed
	} elseif( $active_loop['key'] != $key ) {
		
		// detect change
		if( $post_id != $active_loop['post_id'] ) {
			
			$change = 'post_id';
				
		} elseif( $selector != $active_loop['selector'] ) {
			
			$change = 'selector';
				
		} else {
			
			// key has changed due to a technicallity, however, the post_id and selector are the same
			
		}
		
		
		// attempt to find sub field
		$sub_field = acf_get_sub_field($selector, $active_loop['field']);
			
		if( $sub_field ) {
			
			$sub_exists = isset( $active_loop['value'][ $active_loop['i'] ][ $sub_field['key'] ] );
			
		}
		
		
		// If post_id has changed, this is most likely an archive loop
		if( $change == 'post_id' ) {
			
			if( empty($_post_id) && $sub_exists ) {
				
				// case: Change in $post_id was due to this being a nested loop and not specifying the $post_id
				// action: move down one level into a new loop
				$new_child_loop = true;
			
			} elseif( $previous_loop && $previous_loop['post_id'] == $post_id ) {
				
				// case: Change in $post_id was due to a nested loop ending
				// action: move up one level through the loops
				acf_remove_loop('active');
			
			} else {
				
				// case: Chang in $post_id is the most obvious, used in an WP_Query loop with multiple $post objects
				// action: leave this current loop alone and create a new parent loop
				$new_parent_loop = true;
				
			}
			
		} elseif( $change == 'selector' ) {
			
			if( $sub_exists ) {
				
				// case: Change in $field_name was due to this being a nested loop
				// action: move down one level into a new loop
				$new_child_loop = true;
				
			} elseif( $previous_loop && $previous_loop['selector'] == $selector && $previous_loop['post_id'] == $post_id ) {
				
				// case: Change in $field_name was due to a nested loop ending
				// action: move up one level through the loops
				acf_remove_loop('active');
				
			} else {
				
				// case: Chang in $field_name is the most obvious, this is a new loop for a different field within the $post
				// action: leave this current loop alone and create a new parent loop
				$new_parent_loop = true;
				
			}
			
		}
	
	// loop is the same	
	} else {
		
		// do nothing
		
	}
	
	
	// add loop
	if( $new_parent_loop || $new_child_loop ) {
		
		// vars
		$field = null;
		$value = null;
		$name = '';
		
		
		// parent loop
		if( $new_parent_loop ) {
			
			$field = get_field_object( $selector, $post_id, false );
			$value = acf_extract_var( $field, 'value' );
			$name = $field['name'];
			
		// child loop
		} else {
			
			$field = $sub_field;
			$value = $active_loop['value'][ $active_loop['i'] ][ $sub_field['key'] ];
			$name = $active_loop['name'] . '_' . $active_loop['i'] . '_' . $sub_field['name'];
			$post_id = $active_loop['post_id'];
			
		}
		
		
		// bail early if value is either empty or a non array
		if( !acf_is_array($value) ) return false;
		
		
		// allow for non repeatable data (group)
		if( acf_get_field_type_prop($field['type'], 'have_rows') === 'single' ) {
			$value = array( $value );
		}
		
		
		// add loop
		$active_loop = acf_add_loop(array(
			'selector'	=> $selector,
			'name'		=> $name, // used by update_sub_field
			'value'		=> $value,
			'field'		=> $field,
			'i'			=> -1,
			'post_id'	=> $post_id,
			'key'		=> $key
		));
		
	}
	
	
	// return true if next row exists
	if( $active_loop && isset($active_loop['value'][ $active_loop['i']+1 ]) ) {
		
		return true;
		
	}
	
	
	// no next row!
	acf_remove_loop('active');
	
	
	// return
	return false;
  
}


/*
*  the_row
*
*  This function will progress the global repeater or flexible content value 1 row
*
*  @type	function
*  @date	2/09/13
*  @since	4.3.0
*
*  @param	N/A
*  @return	(array) the current row data
*/

function the_row( $format = false ) {
	
	// vars
	$i = acf_get_loop('active', 'i');
	
	
	// increase
	$i++;
	
	
	// update
	acf_update_loop('active', 'i', $i);
	
	
	// return
	return get_row( $format );
	
}

function get_row( $format = false ) {
	
	// vars
	$loop = acf_get_loop('active');
	
	
	// bail early if no loop
	if( !$loop ) return false;
	
	
	// get value
	$value = acf_maybe_get( $loop['value'], $loop['i'] );
	
	
	// bail early if no current value
	// possible if get_row_layout() is called before the_row()
	if( !$value ) return false;
	
	
	// format
	if( $format ) {
		
		// vars
		$field = $loop['field'];
		
		
		// single row
		if( acf_get_field_type_prop($field['type'], 'have_rows') === 'single' ) {
			
			// format value
			$value = acf_format_value( $value, $loop['post_id'], $field );
		
		// multiple rows
		} else {
			
			// format entire value
			// - solves problem where cached value is incomplete
			// - no performance issues here thanks to cache
			$value = acf_format_value( $loop['value'], $loop['post_id'], $field );
			$value = acf_maybe_get( $value, $loop['i'] );
			
		}
		
	}
	
	
	// return
	return $value;
	
}

function get_row_index() {
	
	// vars
	$i = acf_get_loop('active', 'i');
	$offset = acf_get_setting('row_index_offset');
	
	
	// return
	return $offset + $i;
	
}

function the_row_index() {
	
	echo get_row_index();
	
}


/*
*  get_row_sub_field
*
*  This function is used inside a 'has_sub_field' while loop to return a sub field object
*
*  @type	function
*  @date	16/05/2016
*  @since	5.3.8
*
*  @param	$selector (string)
*  @return	(array)
*/

function get_row_sub_field( $selector ) {
	
	// vars
	$row = acf_get_loop('active');
	
	
	// bail early if no row
	if( !$row ) return false;
	
	
	// attempt to find sub field
	$sub_field = acf_get_sub_field($selector, $row['field']);
	
	
	// bail early if no field
	if( !$sub_field ) return false;
	
	
	// update field's name based on row data
	$sub_field['name'] = "{$row['name']}_{$row['i']}_{$sub_field['name']}";
	
	
	// return
	return $sub_field;
	
}


/*
*  get_row_sub_value
*
*  This function is used inside a 'has_sub_field' while loop to return a sub field value
*
*  @type	function
*  @date	16/05/2016
*  @since	5.3.8
*
*  @param	$selector (string)
*  @return	(mixed)
*/

function get_row_sub_value( $selector ) {
	
	// vars
	$row = acf_get_loop('active');
	
	
	// bail early if no row
	if( !$row ) return null;
	
	
	// return value
	if( isset($row['value'][ $row['i'] ][ $selector ]) ) {
		
		return $row['value'][ $row['i'] ][ $selector ];
		
	}
	
	
	// return
	return null;
	
}


/*
*  reset_rows
*
*  This function will find the current loop and unset it from the global array.
*  To bo used when loop finishes or a break is used
*
*  @type	function
*  @date	26/10/13
*  @since	5.0.0
*
*  @param	$hard_reset (boolean) completely wipe the global variable, or just unset the active row
*  @return	(boolean)
*/

function reset_rows() {
	
	// remove last loop
	acf_remove_loop('active');
	
	
	// return
	return true;
	
}


/*
*  has_sub_field()
*
*  This function is used inside a while loop to return either true or false (loop again or stop).
*  When using a repeater or flexible content field, it will loop through the rows until 
*  there are none left or a break is detected
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$field_name (string) the field name
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function has_sub_field( $field_name, $post_id = false ) {
	
	// vars
	$r = have_rows( $field_name, $post_id );
	
	
	// if has rows, progress through 1 row for the while loop to work
	if( $r ) {
		
		the_row();
		
	}
	
	
	// return
	return $r;
	
}

function has_sub_fields( $field_name, $post_id = false ) {
	
	return has_sub_field( $field_name, $post_id );
	
}


/*
*  get_sub_field()
*
*  This function is used inside a 'has_sub_field' while loop to return a sub field value
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$field_name (string) the field name
*  @return	(mixed)
*/

function get_sub_field( $selector = '', $format_value = true ) {
	
	// get sub field
	$sub_field = get_sub_field_object( $selector, $format_value );
	
	
	// bail early if no sub field
	if( !$sub_field ) return false;
	
	
	// return 
	return $sub_field['value'];
	
}


/*
*  the_sub_field()
*
*  This function is the same as echo get_sub_field
*
*  @type	function
*  @since	1.0.3
*  @date	29/01/13
*
*  @param	$field_name (string) the field name
*  @return	n/a
*/

function the_sub_field( $field_name, $format_value = true ) {
	
	$value = get_sub_field( $field_name, $format_value );
	
	if( is_array($value) ) {
		
		$value = implode(', ',$value);
		
	}
	
	echo $value;
}


/*
*  get_sub_field_object()
*
*  This function is used inside a 'has_sub_field' while loop to return a sub field object
*
*  @type	function
*  @since	3.5.8.1
*  @date	29/01/13
*
*  @param	$child_name (string) the field name
*  @return	(array)	
*/

function get_sub_field_object( $selector, $format_value = true, $load_value = true ) {
	
	// vars
	$row = acf_get_loop('active');
	
	
	// bail early if no row
	if( !$row ) return false;
	
	
	// attempt to find sub field
	$sub_field = get_row_sub_field($selector);
	
	
	// bail early if no sub field
	if( !$sub_field ) return false;
	
	
	// load value
	if( $load_value ) {
	
		$sub_field['value'] = get_row_sub_value( $sub_field['key'] );
		
	}
	
	
	// format value
	if( $format_value ) {
		
		// get value for field
		$sub_field['value'] = acf_format_value( $sub_field['value'], $row['post_id'], $sub_field );
		
	}
	
		
	// return
	return $sub_field;
	
}


/*
*  get_row_layout()
*
*  This function will return a string representation of the current row layout within a 'have_rows' loop
*
*  @type	function
*  @since	3.0.6
*  @date	29/01/13
*
*  @param	n/a
*  @return	(string)
*/

function get_row_layout() {
	
	// vars
	$row = get_row();
	
	
	// return
	if( isset($row['acf_fc_layout']) ) {
		
		return $row['acf_fc_layout'];
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_shortcode()
*
*  This function is used to add basic shortcode support for the ACF plugin
*  eg. [acf field="heading" post_id="123" format_value="1"]
*
*  @type	function
*  @since	1.1.1
*  @date	29/01/13
*
*  @param	$field (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @param	$format_value (boolean) whether or not to format the field value
*  @return	(string)
*/

function acf_shortcode( $atts ) {
	
	// extract attributs
	extract( shortcode_atts( array(
		'field'			=> '',
		'post_id'		=> false,
		'format_value'	=> true
	), $atts ) );
	
	
	// get value and return it
	$value = get_field( $field, $post_id, $format_value );
	
	
	// array
	if( is_array($value) ) {
		
		$value = @implode( ', ', $value );
		
	}
	
	
	// return
	return $value;
	
}

add_shortcode('acf', 'acf_shortcode');


/*
*  update_field()
*
*  This function will update a value in the database
*
*  @type	function
*  @since	3.1.9
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$value (mixed) the value to save in the database
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function update_field( $selector, $value, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id, false );
	
	
	// create dummy field
	if( !$field ) {
		
		$field = acf_get_valid_field(array(
			'name'	=> $selector,
			'key'	=> '',
			'type'	=> '',
		));
		
	}
	
	
	// save
	return acf_update_value( $value, $post_id, $field );
		
}


/*
*  update_sub_field
*
*  This function will update a value of a sub field in the database
*
*  @type	function
*  @date	2/04/2014
*  @since	5.0.0
*
*  @param	$selector (mixed) the sub field name or key, or an array of ancestors
*  @param	$value (mixed) the value to save in the database
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function update_sub_field( $selector, $value, $post_id = false ) {
	
	// vars
	$sub_field = false;
	
	
	// get sub field
	if( is_array($selector) ) {
		
		$post_id = acf_get_valid_post_id( $post_id );
		$sub_field = acf_maybe_get_sub_field( $selector, $post_id, false );
		
	} else {
		
		$post_id = acf_get_loop('active', 'post_id');
		$sub_field = get_row_sub_field( $selector );
		
	}
	
	
	// bail early if no sub field
	if( !$sub_field ) return false;


	// update
	return acf_update_value( $value, $post_id, $sub_field );
		
}


/*
*  delete_field()
*
*  This function will remove a value from the database
*
*  @type	function
*  @since	3.1.9
*  @date	29/01/13
*
*  @param	$selector (string) the field name or key
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function delete_field( $selector, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// delete
	return acf_delete_value( $post_id, $field );
	
}


/*
*  delete_sub_field
*
*  This function will delete a value of a sub field in the database
*
*  @type	function
*  @date	2/04/2014
*  @since	5.0.0
*
*  @param	$selector (mixed) the sub field name or key, or an array of ancestors
*  @param	$value (mixed) the value to save in the database
*  @param	$post_id (mixed) the post_id of which the value is saved against
*  @return	(boolean)
*/

function delete_sub_field( $selector, $post_id = false ) {
	
	return update_sub_field( $selector, null, $post_id );
		
}


/*
*  add_row
*
*  This function will add a row of data to a field
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$selector (string)
*  @param	$row (array)
*  @param	$post_id (mixed)
*  @return	(boolean)
*/

function add_row( $selector, $row = false, $post_id = false ) {
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id, false );
	
	
	// bail early if no field
	if( !$field ) return false;
	
	
	// get raw value
	$value = acf_get_value( $post_id, $field );
	
	
	// ensure array
	$value = acf_get_array($value);
	
	
	// append
	$value[] = $row;
	
	
	// update value
	acf_update_value( $value, $post_id, $field );
	
	
	// return
	return count($value);
		
}


/*
*  add_sub_row
*
*  This function will add a row of data to a field
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$selector (string)
*  @param	$row (array)
*  @param	$post_id (mixed)
*  @return	(boolean)
*/

function add_sub_row( $selector, $row = false, $post_id = false ) {
	
	// vars
	$sub_field = false;
	
	
	// get sub field
	if( is_array($selector) ) {
		
		$post_id = acf_get_valid_post_id( $post_id );
		$sub_field = acf_maybe_get_sub_field( $selector, $post_id, false );
	
	} else {
		
		$post_id = acf_get_loop('active', 'post_id');
		$sub_field = get_row_sub_field( $selector );
		
	}
	
	
	// bail early if no sub field
	if( !$sub_field ) return false;
	
		
	// get raw value
	$value = acf_get_value( $post_id, $sub_field );
	
	
	// ensure array
	$value = acf_get_array( $value );
	
	
	// append
	$value[] = $row;


	// update
	acf_update_value( $value, $post_id, $sub_field );
	
	
	// return
	return count($value);
	
}


/*
*  update_row
*
*  This function will update a row of data to a field
*
*  @type	function
*  @date	19/10/2015
*  @since	5.2.3
*
*  @param	$selector (string)
*  @param	$i (int)
*  @param	$row (array)
*  @param	$post_id (mixed)
*  @return	(boolean)
*/

function update_row( $selector, $i = 1, $row = false, $post_id = false ) {
	
	// vars
	$offset = acf_get_setting('row_index_offset');
	$i = $i - $offset;
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id, false );
	
	
	// bail early if no field
	if( !$field ) return false;
	
	
	// get raw value
	$value = acf_get_value( $post_id, $field );
	
	
	// ensure array
	$value = acf_get_array($value);
	
	
	// update
	$value[ $i ] = $row;
	
	
	// update value
	acf_update_value( $value, $post_id, $field );
	
	
	// return
	return true;
	
}


/*
*  update_sub_row
*
*  This function will add a row of data to a field
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$selector (string)
*  @param	$row (array)
*  @param	$post_id (mixed)
*  @return	(boolean)
*/

function update_sub_row( $selector, $i = 1, $row = false, $post_id = false ) {
	
	// vars
	$sub_field = false;
	$offset = acf_get_setting('row_index_offset');
	$i = $i - $offset;
	
	
	// get sub field
	if( is_array($selector) ) {
		
		$post_id = acf_get_valid_post_id( $post_id );
		$sub_field = acf_maybe_get_sub_field( $selector, $post_id, false );
	
	} else {
		
		$post_id = acf_get_loop('active', 'post_id');
		$sub_field = get_row_sub_field( $selector );
		
	}
	
	
	// bail early if no sub field
	if( !$sub_field ) return false;
	
		
	// get raw value
	$value = acf_get_value( $post_id, $sub_field );
	
	
	// ensure array
	$value = acf_get_array( $value );
	
	
	// append
	$value[ $i ] = $row;


	// update
	acf_update_value( $value, $post_id, $sub_field );
	
	
	// return
	return true;
		
}


/*
*  delete_row
*
*  This function will delete a row of data from a field
*
*  @type	function
*  @date	19/10/2015
*  @since	5.2.3
*
*  @param	$selector (string)
*  @param	$i (int)
*  @param	$post_id (mixed)
*  @return	(boolean)
*/

function delete_row( $selector, $i = 1, $post_id = false ) {
	
	// vars
	$offset = acf_get_setting('row_index_offset');
	$i = $i - $offset;
	
	
	// filter post_id
	$post_id = acf_get_valid_post_id( $post_id );
	
	
	// get field
	$field = acf_maybe_get_field( $selector, $post_id );
	
	
	// bail early if no field
	if( !$field ) return false;
	
	
	// get value
	$value = acf_get_value( $post_id, $field );
	
	
	// ensure array
	$value = acf_get_array($value);
	
	
	// bail early if index doesn't exist
	if( !isset($value[ $i ]) ) return false;
	
		
	// unset
	unset( $value[ $i ] );
	
	
	// update
	acf_update_value( $value, $post_id, $field );
	
	
	// return
	return true;
	
}


/*
*  delete_sub_row
*
*  This function will add a row of data to a field
*
*  @type	function
*  @date	16/10/2015
*  @since	5.2.3
*
*  @param	$selector (string)
*  @param	$row (array)
*  @param	$post_id (mixed)
*  @return	(boolean)
*/

function delete_sub_row( $selector, $i = 1, $post_id = false ) {
	
	// vars
	$sub_field = false;
	$offset = acf_get_setting('row_index_offset');
	$i = $i - $offset;
	
	
	// get sub field
	if( is_array($selector) ) {
		
		$post_id = acf_get_valid_post_id( $post_id );
		$sub_field = acf_maybe_get_sub_field( $selector, $post_id, false );
	
	} else {
		
		$post_id = acf_get_loop('active', 'post_id');
		$sub_field = get_row_sub_field( $selector );
		
	}
	
	
	// bail early if no sub field
	if( !$sub_field ) return false;
	
		
	// get raw value
	$value = acf_get_value( $post_id, $sub_field );
	
	
	// ensure array
	$value = acf_get_array( $value );
	
	
	// bail early if index doesn't exist
	if( !isset($value[ $i ]) ) return false;
	
	
	// append
	unset( $value[ $i ] );


	// update
	acf_update_value( $value, $post_id, $sub_field );
	
	
	// return
	return true;
		
}


/*
*  Depreceated Functions
*
*  These functions are outdated
*
*  @type	function
*  @date	4/03/2014
*  @since	1.0.0
*
*  @param	n/a
*  @return	n/a
*/

function create_field( $field ) {

	acf_render_field( $field );
	
}

function render_field( $field ) {

	acf_render_field( $field );
	
}

function reset_the_repeater_field() {
	
	return reset_rows();
	
}

function the_repeater_field( $field_name, $post_id = false ) {
	
	return has_sub_field( $field_name, $post_id );
	
}

function the_flexible_field( $field_name, $post_id = false ) {
	
	return has_sub_field( $field_name, $post_id );
	
}

function acf_filter_post_id( $post_id ) {
	
	return acf_get_valid_post_id( $post_id );
	
}

?>
