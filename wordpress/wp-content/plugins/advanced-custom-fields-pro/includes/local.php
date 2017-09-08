<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_local') ) :

class acf_local {
	
	// vars
	var $temp_groups = array(),
		$temp_fields = array(),
		$groups = array(),
		$fields = array(),
		$reference = array(),
		$parents = array();
		
		
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.4.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// register filter
		acf_enable_filter('local');
		
		
		// actions
		add_action('acf/include_fields', 	array($this, 'acf_include_fields'), 5, 0);
		
		
		// filters
		add_filter('acf/get_field_groups',	array($this, 'acf_get_field_groups'), 20, 1);
		
	}
	
	
	/*
	*  get_key
	*
	*  This function will check for references and modify the key
	*
	*  @type	function
	*  @date	30/06/2016
	*  @since	5.4.0
	*
	*  @param	$key (string)
	*  @return	$key
	*/
	
	function get_key( $key = '' ) {
		
		// check for reference
		if( isset($this->reference[ $key ]) ) {
			
			$key = $this->reference[ $key ];
				
		}
		
		
		// return
		return $key;
		
	}
	
	
	/*
	*  reset
	*
	*  This function will remove (reset) all field group and fields
	*
	*  @type	function
	*  @date	2/06/2016
	*  @since	5.3.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function reset() {
		
		// vars
		$this->temp_groups = array();
		$this->temp_fields = array();
		$this->groups = array();
		$this->fields = array();
		$this->reference = array();
		$this->parents = array();
		
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
		
		// bail early if no local allowed
		if( !acf_get_setting('local') ) return false;
		
		
		// return
		return acf_is_filter_enabled('local');
		
	}
	
	
	/*
	*  is_ready
	*
	*  This function will return true when ACF has included all field types and is ready to import
	*  Importing fields too early will cause issues where sub fields have not been extracted correctly
	*
	*  @type	function
	*  @date	13/3/17
	*  @since	5.5.10
	*
	*  @param	n/a
	*  @return	(boolean)
	*/
	
	function is_ready() {
		
		return did_action('acf/include_fields');
		
	}
	
	
	/*
	*  acf_include_fields
	*
	*  This function include any $temp data
	*
	*  @type	function
	*  @date	8/2/17
	*  @since	5.5.6
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function acf_include_fields() {
		
		// field groups
		if( !empty($this->temp_groups) ) {
			
			// loop
			foreach( $this->temp_groups as $i => $temp ) {
				
				// add
				$this->add_field_group($temp);
				
				
				// unset
				unset($this->temp_groups[ $i ]);
				
			}
			
		}
		
		
		// fields
		if( !empty($this->temp_fields) ) {
			
			// prepare
			$this->temp_fields = acf_prepare_fields_for_import( $this->temp_fields );
			
			
			// loop
			foreach( $this->temp_fields as $i => $temp ) {
				
				// add
				$this->add_field($temp);
				
				
				// unset
				unset($this->temp_fields[ $i ]);
				
			}
			
		}
		
	}
	
	
	/*
	*  add_parent_reference
	*
	*  This function will add a child reference for a given parent
	*
	*  @type	function
	*  @date	14/07/2016
	*  @since	5.4.0
	*
	*  @param	$parent_key (string)
	*  @param	$field_key (string)
	*  @return	(mixed)
	*/
	
	function add_parent_reference( $parent_key = '', $field_key = '' ) {
		
		// create array if doesn't exist
		if( empty($this->parents[ $parent_key ]) ) {
			
			$this->parents[ $parent_key ] = array();
			
		} 
		
		
		// append
		$this->parents[ $parent_key ][ $field_key ] = $field_key;
		
	}
	
	
	/*
	*  remove_parent_reference
	*
	*  This function will remove a child reference for a given parent
	*
	*  @type	function
	*  @date	14/07/2016
	*  @since	5.4.0
	*
	*  @param	$parent_key (string)
	*  @param	$field_key (string)
	*  @return	(mixed)
	*/
	
	function remove_parent_reference( $parent_key = '', $field_key = '' ) {
		
		// bail early if no parent
		if( empty($this->parents[ $parent_key ]) ) return false;
		
		
		// remove
		unset( $this->parents[ $parent_key ][ $field_key ] );
		
	}
	
	
	/*
	*  maybe_add_field
	*
	*  This function will either import or add to temp
	*
	*  @type	function
	*  @date	9/2/17
	*  @since	5.5.6
	*
	*  @param	$field (array)
	*  @return	n/a
	*/
	
	function maybe_add_field( $field ) {
		
		// add
		if( $this->is_ready() ) {
			
			$this->add_field( $field );
		
		// add to temp
		} else {
			
			$this->temp_fields[] = $field;
			
		}
		
	}
	
	
	/*
	*  add_field
	*
	*  This function will add a $field
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	n/a
	*/
	
	function add_field( $field ) {
		
		// defaults
		$field = wp_parse_args($field, array(
			'key'		=> '',
			'name'		=> '',
			'parent'	=> 0
		));
		
		
		// add parent reference
		$this->add_parent_reference( $field['parent'], $field['key'] );
		
		
		// add in menu order
		$field['menu_order'] = $this->count_fields( $field['parent'] ) - 1;
		
		
		// add field
		$this->fields[ $field['key'] ] = $field;
		
		
		// add reference for field name
		$this->reference[ $field['name'] ] = $field['key'];
		
	}
	
	
	/*
	*  is_field
	*
	*  This function will return true if a field exists for a given key
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	(bolean)
	*/
	
	function is_field( $key = '' ) {
		
		// vars
		$key = $this->get_key($key);
		
		
		// bail early if not enabled
		if( !$this->is_enabled() ) return false;
		
		
		// return
		return isset( $this->fields[ $key ] );
				
	}
	
	function is_field_key( $key ) {
		
		// bail early if not enabled
		if( !$this->is_enabled() ) return false;
		
		
		// return
		return isset( $this->fields[ $key ] );
		
	}
	
	function is_field_name( $name ) {
		
		// bail early if not enabled
		if( !$this->is_enabled() ) return false;
		
		
		// return
		return isset( $this->reference[ $name ] );
		
	}
	
	
	/*
	*  get_field
	*
	*  This function will return a local field for a given key
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	(bolean)
	*/
	
	function get_field( $key = '' ) {
		
		// vars
		$key = $this->get_key($key);
		
		
		// bail early if no group
		if( !$this->is_field($key) ) return false;
		
		
		// return
		return $this->fields[ $key ];
		
	}
	
	
	/*
	*  remove_field
	*
	*  This function will remove a $field
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	n/a
	*/
	
	function remove_field( $key = '' ) {
		
		// get field
		$field = $this->get_field( $key );
		
		
		// bail early if no field
		if( !$field ) return false;
		
		
		// remove parent reference
		$this->remove_parent_reference( $field['parent'], $field['key'] );
		
		
		// remove field
		unset( $this->fields[ $field['key'] ] );
		
		
		// remove reference for field name
		unset( $this->reference[ $field['name'] ] );
		
		
		// remove children
		if( $this->have_fields($key) ) {
			
			$this->remove_fields( $key );
			
		}
		
	}
	
	
	/*
	*  maybe_add_field_group
	*
	*  This function will either import or add to temp
	*
	*  @type	function
	*  @date	9/2/17
	*  @since	5.5.6
	*
	*  @param	$field_group (array)
	*  @return	n/a
	*/
	
	function maybe_add_field_group( $field_group ) {
		
		// add
		if( $this->is_ready() ) {
			
			$this->add_field_group( $field_group );
		
		// add to temp
		} else {
			
			$this->temp_groups[] = $field_group;
			
		}
		
	}
	
	
	/*
	*  add_field_group
	*
	*  This function will add a $field group to the local placeholder
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	n/a
	*/
	
	function add_field_group( $field_group ) {
		
		// vars
		$fields = acf_extract_var($field_group, 'fields');
		
		
		// validate
		$field_group = acf_get_valid_field_group($field_group);
		
		
		// don't allow overrides
		if( $this->is_field_group($field_group['key']) ) return;
		
		
		// add local (may be set to json)
		if( empty($field_group['local']) ) $field_group['local'] = 'php';
		
		
		// add field group
		$this->groups[ $field_group['key'] ] = $field_group;
		
		
		// bail ealry if no fields
		if( !$fields ) return;
		
		
		// format fields
		$fields = acf_prepare_fields_for_import( $fields );
		
		
		// add fields
		foreach( $fields as $field ) {
			
			// add parent
			if( empty($field['parent']) ) $field['parent'] = $field_group['key'];
			
			
			// add field
			$this->add_field( $field );
			
		}
		
	}
	
	
	/*
	*  is_field_group
	*
	*  This function will return true if a field group exists for a given key
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	(bolean)
	*/
	
	function is_field_group( $key = '' ) {
		
		// bail early if not enabled
		if( !$this->is_enabled() ) return false;
		
		
		// return
		return isset( $this->groups[ $key ] );
				
	}
	
	
	/*
	*  get_field_group
	*
	*  This function will return a local field group for a given key
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	(bolean)
	*/
	
	function get_field_group( $key = '' ) {
		
		// bail early if no group
		if( !$this->is_field_group($key) ) return false;
		
		
		// return
		return $this->groups[ $key ];
		
	}
	
	
	/*
	*  count_field_groups
	*
	*  This function will return the number of field groups
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	(bolean)
	*/
	
	
	function count_field_groups() {
		
		// return
		return count( $this->groups );
		
	}
	
	
	/*
	*  have_field_groups
	*
	*  This function will true if local field groups exist
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	(int)
	*/
	
	function have_field_groups() {
		
		// bail early if not enabled
		if( !$this->is_enabled() ) return 0;
		
		
		// return
		return $this->count_field_groups();
		
	}
	
	
	/*
	*  get_field_groups
	*
	*  This function will return an array of field groups
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	(bolean)
	*/
	
	function get_field_groups() {
		
		// bail early if no parent
		if( !$this->have_field_groups() ) return false;
		
		
		// vars
		$field_groups = array();
		
		
		// append
		foreach( array_keys($this->groups) as $field_group_key ) {
			
			$field_groups[] = acf_get_field_group( $field_group_key );
			
		}
		
		
		// return
		return $field_groups;
		
	}
	
	
	
	/*
	*  count_fields
	*
	*  This function will return the number of fields for a given parent
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	(bolean)
	*/
	
	
	function count_fields( $parent_key = '' ) {
		
		// check
		if( isset($this->parents[ $parent_key ]) ) {
			
			return count($this->parents[ $parent_key ]);
			
		} 
		
		
		// return
		return 0;
		
	}
	
	
	/*
	*  have_fields
	*
	*  This function will true if local fields exist
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	(int)
	*/
	
	function have_fields( $parent_key = '' ) {
	
		// bail early if not enabled
		if( !$this->is_enabled() ) return 0;
		
		
		// return
		return $this->count_fields( $parent_key );
		
	}
	
	
	/*
	*  get_fields
	*
	*  This function will return an array of fields for a given 'parent' key (field group key or field key)
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	(bolean)
	*/
	
	function get_fields( $parent_key = '' ) {
		
		// bail early if no parent
		if( !$this->have_fields($parent_key) ) return false;
		
		
		// vars
		$fields = array();
		
		
		// append
		foreach( $this->parents[ $parent_key ] as $field_key ) {
			
			$fields[] = acf_get_field( $field_key );
			
		}
		
		
		// return
		return $fields;
		
	}
	
	
	/*
	*  remove_fields
	*
	*  This function will remove the field reference for a field group
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$key (string)
	*  @return	(bolean)
	*/
	
	function remove_fields( $parent_key = '' ) {
		
		// bail early if no parent
		if( !$this->have_fields($parent_key) ) return false;
		
		
		// loop
		foreach( $this->parents[ $parent_key ] as $field_key ) {
			
			$this->remove_field( $field_key );
		
		}
		
		
		// return
		return true;
	}
	
	
	/*
	*  acf_get_field_groups
	*
	*  This function will override and add field groups to the `acf_get_field_groups()` results
	*
	*  @type	filter (acf/get_field_groups)
	*  @date	5/12/2013
	*  @since	5.0.0
	*
	*  @param	$field_groups (array)
	*  @return	$field_groups
	*/
	
	function acf_get_field_groups( $field_groups ) {
		
		// bail early if no local field groups
		if( !$this->have_field_groups() ) return $field_groups;
		
		
		// vars
		$ignore = array();
		$added = false;
		
		
		// populate ignore list
		if( !empty($field_groups) ) {
			
			foreach( $field_groups as $k => $group ) {

				$ignore[] = $group['key'];
				
			}
			
		}
		
		
		// append field groups
		$groups = $this->get_field_groups();
		
		foreach( $groups as $group ) {
			
			// is ignore
			if( in_array($group['key'], $ignore) ) continue;
			
			
			// append
			$field_groups[] = $group;
			$added = true;
			
		}
		
		
		// order field groups based on menu_order, title
		if( $added ) {
			
			$menu_order = array();
			$title = array();
			
			foreach( $field_groups as $key => $row ) {
				
			    $menu_order[ $key ] = $row['menu_order'];
			    $title[ $key ] = $row['title'];
			}
			
			
			// sort the array with menu_order ascending
			array_multisort( $menu_order, SORT_ASC, $title, SORT_ASC, $field_groups );
				
		}
		
		
		// return
		return $field_groups;
		
	}
	
}


// initialize
acf()->local = new acf_local();

endif; // class_exists check


/*
*  Helpers
*
*  alias of acf()->local->functions
*
*  @type	function
*  @date	11/06/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_local() {
	
	return acf()->local;
	
}

function acf_disable_local() {
	
	acf_disable_filter('local');
	
}

function acf_enable_local() {
	
	acf_enable_filter('local');
	
}

function acf_reset_local() {
	
	return acf_local()->reset();
	
}


// field group
function acf_add_local_field_group( $field_group ) {
	
	return acf_local()->maybe_add_field_group( $field_group );
	
}

function acf_remove_local_field_group( $key = '' ) {
	
	return false;
	
}

function acf_is_local_field_group( $key = '' ) {
	
	return acf_local()->is_field_group( $key );
	
}

function acf_get_local_field_group( $key = '' ) {
	
	return acf_local()->get_field_group( $key );
	
}


// field groups
function acf_count_local_field_groups() {
	
	return acf_local()->count_field_groups();
	
}

function acf_have_local_field_groups() {
	
	return acf_local()->have_field_groups();
	
}

function acf_get_local_field_groups() {
	
	return acf_local()->get_field_groups();
	
}


// field
function acf_add_local_field( $field ) {
	
	return acf_local()->maybe_add_field( $field );
	
}

function acf_remove_local_field( $key = '' ) {
	
	return acf_local()->remove_field( $key );
	
}

function acf_is_local_field( $key = '' ) {
	
	return acf_local()->is_field( $key );
	
}

function acf_is_local_field_key( $key = '' ) {
	
	return acf_local()->is_field_key( $key );
	
}

function acf_is_local_field_name( $name = '' ) {
	
	return acf_local()->is_field_name( $name );
	
}

function acf_get_local_field( $key = '' ) {
	
	return acf_local()->get_field( $key );
	
}


// fields
function acf_count_local_fields( $key = '' ) {
	
	return acf_local()->count_fields( $key );
	
}

function acf_have_local_fields( $key = '' ) {
	
	return acf_local()->have_fields( $key );
	
}

function acf_get_local_fields( $key = '' ) {
	
	return acf_local()->get_fields( $key );
	
}

function acf_remove_local_fields( $key = '' ) {
	
	return acf_local()->remove_fields( $key );
	
}


// deprecated
function register_field_group( $field_group ) {
	
	acf_add_local_field_group( $field_group );
	
}


?>