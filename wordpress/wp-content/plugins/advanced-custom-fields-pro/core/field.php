<?php

if( ! class_exists('acf_field') ) :

class acf_field {
	
	// vars
	var $name = '',
		$label = '',
		$category = 'basic',
		$defaults = array(),
		$l10n = array(),
		$public = true;
	
	
	/*
	*  __construct
	*
	*  This construcor registeres many actions and filters
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// info
		$this->add_filter('acf/get_field_types',					array($this, 'get_field_types'), 10, 1);
		
		
		// value
		$this->add_field_filter('acf/load_value',					array($this, 'load_value'), 10, 3);
		$this->add_field_filter('acf/update_value',					array($this, 'update_value'), 10, 3);
		$this->add_field_filter('acf/format_value',					array($this, 'format_value'), 10, 3);
		$this->add_field_filter('acf/validate_value',				array($this, 'validate_value'), 10, 4);
		$this->add_field_action('acf/delete_value',					array($this, 'delete_value'), 10, 3);
		
		
		// field
		$this->add_field_filter('acf/validate_field',				array($this, 'validate_field'), 10, 1);
		$this->add_field_filter('acf/load_field',					array($this, 'load_field'), 10, 1);
		$this->add_field_filter('acf/update_field',					array($this, 'update_field'), 10, 1);
		$this->add_field_filter('acf/duplicate_field',				array($this, 'duplicate_field'), 10, 1);
		$this->add_field_action('acf/delete_field',					array($this, 'delete_field'), 10, 1);
		$this->add_field_action('acf/render_field',					array($this, 'render_field'), 9, 1);
		$this->add_field_action('acf/render_field_settings',		array($this, 'render_field_settings'), 9, 1);
		$this->add_field_filter('acf/prepare_field',				array($this, 'prepare_field'), 10, 1);
		$this->add_field_filter('acf/translate_field',				array($this, 'translate_field'), 10, 1);
		
		
		// input actions
		$this->add_action("acf/input/admin_enqueue_scripts",		array($this, 'input_admin_enqueue_scripts'), 10, 0);
		$this->add_action("acf/input/admin_head",					array($this, 'input_admin_head'), 10, 0);
		$this->add_action("acf/input/form_data",					array($this, 'input_form_data'), 10, 1);
		$this->add_filter("acf/input/admin_l10n",					array($this, 'input_admin_l10n'), 10, 1);
		$this->add_action("acf/input/admin_footer",					array($this, 'input_admin_footer'), 10, 1);
		
		
		// field group actions
		$this->add_action("acf/field_group/admin_enqueue_scripts", 	array($this, 'field_group_admin_enqueue_scripts'), 10, 0);
		$this->add_action("acf/field_group/admin_head",				array($this, 'field_group_admin_head'), 10, 0);
		$this->add_action("acf/field_group/admin_footer",			array($this, 'field_group_admin_footer'), 10, 0);
		
	}
	
	
	/*
	*  add_filter
	*
	*  This function checks if the function is_callable before adding the filter
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	$tag (string)
	*  @param	$function_to_add (string)
	*  @param	$priority (int)
	*  @param	$accepted_args (int)
	*  @return	n/a
	*/
	
	function add_filter( $tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1 ) {
		
		// bail early if no callable
		if( !is_callable($function_to_add) ) return;
		
		
		// add
		add_filter( $tag, $function_to_add, $priority, $accepted_args );
		
	}
	
	
	/*
	*  add_field_filter
	*
	*  This function will add a field type specific filter
	*
	*  @type	function
	*  @date	29/09/2016
	*  @since	5.4.0
	*
	*  @param	$tag (string)
	*  @param	$function_to_add (string)
	*  @param	$priority (int)
	*  @param	$accepted_args (int)
	*  @return	n/a
	*/
	
	function add_field_filter( $tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1 ) {
		
		// append
		$tag .= '/type=' . $this->name;
		
		
		// add
		$this->add_filter( $tag, $function_to_add, $priority, $accepted_args );
		
	}
	
	
	/*
	*  add_action
	*
	*  This function checks if the function is_callable before adding the action
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	$tag (string)
	*  @param	$function_to_add (string)
	*  @param	$priority (int)
	*  @param	$accepted_args (int)
	*  @return	n/a
	*/
	
	function add_action( $tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1 ) {
		
		// bail early if no callable
		if( !is_callable($function_to_add) ) return;
		
		
		// add
		add_action( $tag, $function_to_add, $priority, $accepted_args );
		
	}
	
	
	/*
	*  add_field_action
	*
	*  This function will add a field type specific filter
	*
	*  @type	function
	*  @date	29/09/2016
	*  @since	5.4.0
	*
	*  @param	$tag (string)
	*  @param	$function_to_add (string)
	*  @param	$priority (int)
	*  @param	$accepted_args (int)
	*  @return	n/a
	*/
	
	function add_field_action( $tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1 ) {
		
		// append
		$tag .= '/type=' . $this->name;
		
		
		// add
		$this->add_action( $tag, $function_to_add, $priority, $accepted_args );
		
	}
	
	
	/*
	*  get_field_types()
	*
	*  This function will append the current field type to the list of available field types
	*
	*  @type	function
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$fields	(array)
	*  @return	$fields
	*/
	
	function get_field_types( $types ) {
		
		// append
		$types[ $this->name ] = array(
			'label'		=> $this->label,
			'name'		=> $this->name,
			'category'	=> $this->category,
			'public'	=> $this->public
		);
		
		
		// return
		return $types;
		
	}
	
	
	/*
	*  validate_field
	*
	*  This function will append default settings to a field
	*
	*  @type	filter ("acf/validate_field/type={$this->name}")
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array)
	*  @return	$field (array)
	*/
	
	function validate_field( $field ) {
		
		// bail early if no defaults
		if( !is_array($this->defaults) ) return $field;
		
		
		// merge in defaults but keep order of $field keys
		foreach( $this->defaults as $k => $v ) {
			
			if( !isset($field[ $k ]) ) $field[ $k ] = $v;
			
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  admin_l10n
	*
	*  This function will append l10n text translations to an array which is later passed to JS
	*
	*  @type	filter ("acf/input/admin_l10n")
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$l10n (array)
	*  @return	$l10n (array)
	*/
	
	function input_admin_l10n( $l10n ) {
		
		// bail early if no defaults
		if( empty($this->l10n) ) return $l10n;
		
		
		// append
		$l10n[ $this->name ] = $this->l10n;
		
		
		// return		
		return $l10n;
		
	}
	
}

endif; // class_exists check


/*
*  acf_get_field_types
*
*  This function will return an array containing info about all field types
*
*  @type	function
*  @date	22/10/16
*  @since	5.5.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_field_types() {
	
	// vars
	$cache_key = 'acf_get_field_types';
	
	
	// check cache
	if( acf_isset_cache($cache_key) ) return acf_get_cache($cache_key);
	
		
	// get types
	$types = apply_filters('acf/get_field_types', array());
	
	
	// update cache
	acf_set_cache($cache_key, $types);
	
	
	// return
	return $types;
	
}


/*
*  acf_get_grouped_field_types
*
*  This function will return a grouped array of fields types (category => name)
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_grouped_field_types() {
	
	// vars
	$types = array();
	$l10n = array(
		'basic'			=> __('Basic', 'acf'),
		'content'		=> __('Content', 'acf'),
		'choice'		=> __('Choice', 'acf'),
		'relational'	=> __('Relational', 'acf'),
		'jquery'		=> __('jQuery', 'acf'),
		'layout'		=> __('Layout', 'acf'),
	);
	
	
	// get field type information
	$types_info = acf_get_field_types();
	
	
	// loop
	foreach( $types_info as $info ) {
		
		// bail early if not public
		if( !$info['public'] ) continue;
		
		
		// vars
		$cat = $info['category'];
		
		
		// default to basic
		if( !$cat ) $cat = 'basic';
		
		
		// translate
		$cat = isset($l10n[ $cat ]) ? $l10n[ $cat ] : $cat;
		
		
		// append
		$types[ $cat ][ $info['name'] ] = $info['label'];
		
	}
	
	
	// return
	return $types;
	
}


/*
*  acf_get_field_type_label
*
*  This function will return the label of a field type
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_field_type_label( $type = '' ) {

	// vars
	$types = acf_get_field_types();
	
	
	// bail early if doesn't exist
	if( !isset($types[ $type ]) ) return '';
	
	
	// return
	return $types[ $type ]['label'];
	
}


/*
*  acf_field_type_exists
*
*  This function will check if the field_type exists
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$type (string)
*  @return	(boolean)
*/

function acf_field_type_exists( $type = '' ) {

	// vars
	$types = acf_get_field_types();
	
	
	// return
	return isset($types[ $type ]);
	
}

?>