<?php 

/*
*  acf_get_valid_options_page
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

if( ! function_exists('acf_get_valid_options_page') ):

function acf_get_valid_options_page( $page = '' ) {
	
	// allow for string
	if( empty($page) ) {
		
		$page = array(
			'page_title' 	=> __('Options','acf'),
			'menu_title'	=> __('Options','acf'),
			'menu_slug' 	=> 'acf-options',
		);
			
	} elseif( is_string($page) ) {
	
		$page_title = $page;
		
		$page = array(
			'page_title' => $page_title,
			'menu_title' => $page_title
		);
	}
	
	
	// defaults
	$page = wp_parse_args($page, array(
		'page_title' 	=> '',
		'menu_title'	=> '',
		'menu_slug' 	=> '',
		'capability'	=> 'edit_posts',
		'parent_slug'	=> '',
		'position'		=> false,
		'icon_url'		=> false,
		'redirect'		=> true,
		'post_id'		=> 'options',
		'autoload'		=> false,
		'update_button'	=> __('Update', 'acf')
	));
	
	
	// ACF4 compatibility
	$migrate = array(
		'title' 	=> 'page_title',
		'menu'		=> 'menu_title',
		'slug'		=> 'menu_slug',
		'parent'	=> 'parent_slug'
	);
	
	foreach( $migrate as $old => $new ) {
		
		if( !empty($page[ $old ]) ) {
			
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
	
	
	// return
	return $page;
	
}

endif;


/*
*  acf_pro_get_option_page
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

if( ! function_exists('acf_get_options_page') ):

function acf_get_options_page( $slug ) {
	
	// bail early if page doens't exist
	if( empty($GLOBALS['acf_options_pages'][ $slug ]) ) {
		
		return false;
		
	}
	
	
	// vars
	$page = $GLOBALS['acf_options_pages'][ $slug ];
	
					
	// filter for 3rd party customization
	$page = apply_filters('acf/get_options_page', $page, $slug);
	
	
	// return
	return $page;
	
}

endif;


/*
*  acf_pro_get_option_pages
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

if( ! function_exists('acf_get_options_pages') ):

function acf_get_options_pages() {
	
	// global
	global $_wp_last_utility_menu;
		
		
	// bail early if empty
	if( empty($GLOBALS['acf_options_pages']) ) {
		
		return false;
		
	}
	
	
	// vars
	$pages = array();
	$redirects = array();
	$slugs = array_keys($GLOBALS['acf_options_pages']);
	
	
	// get pages
	foreach( $slugs as $slug ) {
	
		// append
		$pages[] = acf_get_options_page( $slug );
		
	}
	

	foreach( $pages as $i => $page ) {
			
		// bail early if is child
		if( !empty($page['parent_slug']) ) {
			
			continue;
			
		}
		
		
		// add missing position
		if( !$page['position']) {
			
			$_wp_last_utility_menu++;
			
			$pages[ $i ]['position'] = $_wp_last_utility_menu;
			
		}
	
		
		// bail early if no redirect
		if( empty($page['redirect']) ) {
			
			continue;
			
		}
		
		
		// vars
		$parent = $page['menu_slug'];
		$child = '';
		
		
		// update children
		foreach( $pages as $j => $sub_page ) {
			
			// bail early if not child
			if( $sub_page['parent_slug'] !== $parent ) {
				
				continue;
				
			}
			
			
			// update $child if empt
			if( empty($child) ) {
				
				$child = $sub_page['menu_slug'];
				
			}
			
			
			// update parent_slug
			$pages[ $j ]['parent_slug'] = $child;
			
		}
		
		
		// finally update parent menu_slug
		if( $child ) {
			
			$pages[ $i ]['menu_slug'] = $child;
			
		}
		
	}	
	
	
	// return
	return $pages;
	
}

endif;


/*
*  acf_update_options_page
*
*  description
*
*  @type	function
*  @date	1/05/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

if( ! function_exists('acf_update_options_page') ):

function acf_update_options_page( $data ) {
	
	// bail early if no menu_slug
	if( empty($data['menu_slug']) ) {
		
		return false;
		
	}
	
	// vars
	$slug = $data['menu_slug'];
	
	
	// bail early if no page found
	if( empty($GLOBALS['acf_options_pages'][ $slug ]) ) {
	
		return false;
		
	}
	
	
	// vars
	$page = $GLOBALS['acf_options_pages'][ $slug ];
	
	
	// merge in data
	$page = array_merge($page, $data);
	
	
	// update
	$GLOBALS['acf_options_pages'][ $slug ] = $page;
	
	
	// return
	return $page;
	
}

endif;


/*
*  acf_add_options_page
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

if( ! function_exists('acf_add_options_page') ):

function acf_add_options_page( $page = '' ) {
	
	// validate
	$page = acf_get_valid_options_page( $page );
	
	
	// instantiate globals
	if( empty($GLOBALS['acf_options_pages']) ) {
	
		$GLOBALS['acf_options_pages'] = array();
		
	}
	
	
	// update if already exists
	if( acf_get_options_page($page['menu_slug']) ) {
		
		return acf_update_options_page( $page );
		
	}
	
	
	// append
	$GLOBALS['acf_options_pages'][ $page['menu_slug'] ] = $page;
	
	
	// return
	return $page;
	
}

endif;


/*
*  acf_add_options_page
*
*  description
*
*  @type	function
*  @date	24/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

if( ! function_exists('acf_add_options_sub_page') ):

function acf_add_options_sub_page( $page = '' ) {
	
	// validate
	$page = acf_get_valid_options_page( $page );
	
	
	// parent
	if( !$page['parent_slug'] ) {
		
		// set parent slug
		$page['parent_slug'] = 'acf-options';
		
	}
	
	
	// create default parent if not yet exists
	if( $page['parent_slug'] === 'acf-options' ) {
		
		if( !acf_get_options_page('acf-options') ) {
			
			acf_add_options_page();
			
		}
		
	}
		
	
	// return
	return acf_add_options_page( $page );
	
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
	
	acf_update_options_page(array(
		'menu_slug'		=> 'acf-options',
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
	
	acf_update_options_page(array(
		'menu_slug'		=> 'acf-options',
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
	
	acf_update_options_page(array(
		'menu_slug'		=> 'acf-options',
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

function register_options_page( $title = false ) {

	acf_add_options_sub_page( $title );
	
}

endif;

?>