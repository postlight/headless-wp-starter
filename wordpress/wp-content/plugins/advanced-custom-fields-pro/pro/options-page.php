<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_options_page') ) :

class acf_options_page {
	
	/** @var array Contains an array of optiions page settings */
	var $pages = array();
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		/* do nothing */
		
	}
	
	
	/*
	*  validate_page
	*
	*  description
	*
	*  @type	function
	*  @date	28/2/17
	*  @since	5.5.8
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_page( $page ) {
		
		// default
		if( empty($page) ) {
			
			$page_title =  __('Options', 'acf');
			$page = array(
				'page_title'	=> $page_title,
				'menu_title'	=> $page_title,
				'menu_slug' 	=> 'acf-options'
			);
		
		// string		
		} elseif( is_string($page) ) {
		
			$page_title = $page;
			$page = array(
				'page_title'	=> $page_title,
				'menu_title'	=> $page_title
			);
		}
		
		
		// defaults
		$page = wp_parse_args($page, array(
			'page_title' 		=> '',
			'menu_title'		=> '',
			'menu_slug' 		=> '',
			'capability'		=> 'edit_posts',
			'parent_slug'		=> '',
			'position'			=> false,
			'icon_url'			=> false,
			'redirect'			=> true,
			'post_id'			=> 'options',
			'autoload'			=> false,
			'update_button'		=> __('Update', 'acf'),
			'updated_message'	=> __("Options Updated", 'acf'),
		));
		
		
		// ACF4 compatibility
		$migrate = array(
			'title' 	=> 'page_title',
			'menu'		=> 'menu_title',
			'slug'		=> 'menu_slug',
			'parent'	=> 'parent_slug'
		);
		
		foreach( $migrate as $old => $new ) {
			if( !empty($page[$old]) ) {
				$page[ $new ] = acf_extract_var( $page, $old );
			}
		}
		
		
		// page_title (allows user to define page with just page_title or title)
		if( empty($page['menu_title']) ) {
			$page['menu_title'] = $page['page_title'];
		}
		
		
		// menu_slug
		if( empty($page['menu_slug']) ) {
			$page['menu_slug'] = 'acf-options-' . sanitize_title( $page['menu_title'] );
		}
		
		
		// filter
		$page = apply_filters('acf/validate_options_page', $page);
		
		
		// return
		return $page;
		
	}
	
	
	/*
	*  add_page
	*
	*  This function will store an options page settings
	*
	*  @type	function
	*  @date	9/6/17
	*  @since	5.6.0
	*
	*  @param	$page (array)
	*  @return	n/a
	*/
	
	function add_page( $page ) {
		
		// validate
		$page = $this->validate_page( $page );
		$slug = $page['menu_slug'];
		
		
		// bail ealry if already exists
		if( isset($this->pages[$slug]) ) return false;
		
		
		// append
		$this->pages[$slug] = $page;
		
		
		// return
		return $page;
		
	}
	
	
	/*
	*  add_sub_page
	*
	*  description
	*
	*  @type	function
	*  @date	9/6/17
	*  @since	5.6.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function add_sub_page( $page ) {
		
		// validate
		$page = $this->validate_page( $page );
		
		
		// default parent
		if( !$page['parent_slug'] ) {
			$page['parent_slug'] = 'acf-options';
		}
		
		
		// create default parent if not yet exists
		if( $page['parent_slug'] == 'acf-options' && !$this->get_page('acf-options') ) {
			
			$this->add_page( '' );
			
		}
			
		
		// return
		return $this->add_page( $page );
		
	}
	
	
	/*
	*  update_page
	*
	*  This function will update an options page settings
	*
	*  @type	function
	*  @date	9/6/17
	*  @since	5.6.0
	*
	*  @param	$slug (string)
	*  @param	$data (array)
	*  @return	(array)
	*/
	
	function update_page( $slug = '', $data = array() ) {
		
		// vars
		$page = $this->get_page( $slug );
		
		
		// bail early if no page
		if( !$page ) return false;
		
		
		// loop
		$page = array_merge( $page, $data );
		
		
		// set
		$this->pages[ $slug ] = $page;
		
		
		// return
		return $page;
		
	}
	
	
	/*
	*  get_page
	*
	*  This function will return an options page settings
	*
	*  @type	function
	*  @date	6/07/2016
	*  @since	5.4.0
	*
	*  @param	$slug (string)
	*  @return	(mixed)
	*/
	
	function get_page( $slug ) {
		
		return isset( $this->pages[$slug] ) ? $this->pages[$slug] : null;
		
	}
	
	
	/*
	*  get_pages
	*
	*  This function will return all options page settings
	*
	*  @type	function
	*  @date	6/07/2016
	*  @since	5.4.0
	*
	*  @param	$slug (string)
	*  @return	(mixed)
	*/
	
	function get_pages() {
		
		return $this->pages;
		
	}
    
}


/*
*  acf_options_page
*
*  This function will return the options page instance
*
*  @type	function
*  @date	9/6/17
*  @since	5.6.0
*
*  @param	n/a
*  @return	(object)
*/

function acf_options_page() {
	
	global $acf_options_page;
	
	if( !isset($acf_options_page) ) {
		
		$acf_options_page = new acf_options_page();
		
	}
	
	return $acf_options_page;
	
}


// initialize
acf_options_page();


endif; // class_exists check


/*
*  acf_add_options_page
*
*  alias of acf_options_page()->add_page()
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$page (mixed)
*  @return	(array)
*/

if( !function_exists('acf_add_options_page') ):

function acf_add_options_page( $page = '' ) {
	
	return acf_options_page()->add_page( $page );
	
}

endif;


/*
*  acf_add_options_sub_page
*
*  alias of acf_options_page()->add_sub_page()
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$page (mixed)
*  @return	(array)
*/

if( !function_exists('acf_add_options_sub_page') ):

function acf_add_options_sub_page( $page = '' ) {
	
	return acf_options_page()->add_sub_page( $page );
	
}

endif;


/*
*  acf_update_options_page
*
*  alias of acf_options_page()->update_page()
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$slug (string)
*  @param	$page (mixed)
*  @return	(array)
*/

if( !function_exists('acf_update_options_page') ):

function acf_update_options_page( $slug = '', $data = array() ) {
	
	return acf_options_page()->update_page( $slug, $data );
	
}

endif;


/*
*  acf_get_options_page
*
*  This function will return an options page settings
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$slug (string)
*  @return	(array)
*/

if( !function_exists('acf_get_options_page') ):

function acf_get_options_page( $slug ) {
	
	// vars
	$page = acf_options_page()->get_page( $slug );
	
	
	// bail early if no page
	if( !$page ) return false;
	
	
	// filter
	$page = apply_filters('acf/get_options_page', $page, $slug);
	
	
	// return
	return $page;
	
}

endif;


/*
*  acf_get_options_pages
*
*  This function will return all options page settings
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

if( !function_exists('acf_get_options_pages') ):

function acf_get_options_pages() {
	
	// global
	global $_wp_last_utility_menu;
	
	
	// vars
	$pages = acf_options_page()->get_pages();
	
	
	// bail early if no pages
	if( empty($pages) ) return false;
	
	
	// apply filter to each page
	foreach( $pages as $slug => &$page ) {
		
		$page = acf_get_options_page( $slug );
	
	}
	
	
	// calculate parent => child redirectes
	foreach( $pages as $slug => &$page ) {
			
		// bail early if is child
		if( $page['parent_slug'] ) continue;
		
		
		// add missing position
		if( !$page['position']) {
			
			$_wp_last_utility_menu++;
			$page['position'] = $_wp_last_utility_menu;
			
		}
		
		
		// bail early if no redirect
		if( !$page['redirect'] ) continue;
		
		
		// vars
		$parent = $page['menu_slug'];
		$child = '';
		
		
		// update children
		foreach( $pages as &$sub_page ) {
			
			// bail early if not child of this parent
			if( $sub_page['parent_slug'] !== $parent ) continue;
			
			
			// set child (only once)
			if( !$child ) {
				$child = $sub_page['menu_slug'];
			}
			
			
			// update parent_slug to the first child
			$sub_page['parent_slug'] = $child;
			
		}
		
		
		// finally update parent menu_slug
		if( $child ) {
			$page['menu_slug'] = $child;
		}
		
	}	
	
	
	// filter
	$pages = apply_filters('acf/get_options_pages', $pages);
	
	
	// return
	return $pages;
	
}

endif;


/*
*  acf_set_options_page_title
*
*  This function is used to customize the options page admin menu title
*
*  @type	function
*  @date	13/07/13
*  @since	4.0.0
*
*  @param	$title (string)
*  @return	n/a
*/

if( ! function_exists('acf_set_options_page_title') ):

function acf_set_options_page_title( $title = 'Options' ) {
	
	acf_update_options_page('acf-options', array(
		'page_title'	=> $title,
		'menu_title'	=> $title
	));
	
}

endif;


/*
*  acf_set_options_page_menu
*
*  This function is used to customize the options page admin menu name
*
*  @type	function
*  @date	13/07/13
*  @since	4.0.0
*
*  @param	$title (string)
*  @return	n/a
*/

if( ! function_exists('acf_set_options_page_menu') ):

function acf_set_options_page_menu( $title = 'Options' ) {
	
	acf_update_options_page('acf-options', array(
		'menu_title'	=> $title
	));
	
}

endif;


/*
*  acf_set_options_page_capability
*
*  This function is used to customize the options page capability. Defaults to 'edit_posts'
*
*  @type	function
*  @date	13/07/13
*  @since	4.0.0
*
*  @param	$title (string)
*  @return	n/a
*/

if( ! function_exists('acf_set_options_page_capability') ):

function acf_set_options_page_capability( $capability = 'edit_posts' ) {
	
	acf_update_options_page('acf-options', array(
		'capability'	=> $capability
	));
	
}

endif;


/*
*  register_options_page()
*
*  This is an old function which is now referencing the new 'acf_add_options_sub_page' function
*
*  @type	function
*  @since	3.0.0
*  @date	29/01/13
*
*  @param	{string}	$title
*  @return	N/A
*/

if( ! function_exists('register_options_page') ):

function register_options_page( $page = '' ) {

	acf_add_options_sub_page( $page );
	
}

endif;

?>