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
	*  This function will initialize the field type
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// initialize
		$this->initialize();
		
		
		// register info
		acf_register_field_type_info(array(
			'label'		=> $this->label,
			'name'		=> $this->name,
			'category'	=> $this->category,
			'public'	=> $this->public
		));
		
		
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
	*  initialize
	*
	*  This function will initialize the field type
	*
	*  @type	function
	*  @date	27/6/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		/* do nothing */
			
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

?>