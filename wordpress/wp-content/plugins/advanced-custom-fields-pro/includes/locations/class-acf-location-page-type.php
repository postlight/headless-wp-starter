<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_page_type') ) :

class acf_location_page_type extends acf_location {
	
	
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
	
	function initialize() {
		
		// vars
		$this->name = 'page_type';
		$this->label = __("Page Type",'acf');
		$this->category = 'page';
    	
	}
	
	
	/*
	*  rule_match
	*
	*  This function is used to match this location $rule to the current $screen
	*
	*  @type	function
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match( $result, $rule, $screen ) {
		
		// vars
		$post_id = acf_maybe_get( $screen, 'post_id' );
		
		
		// bail early if no post id
		if( !$post_id ) return false;
		
		
		// get post
		$post = get_post( $post_id );
		
		
		// compare   
        if( $rule['value'] == 'front_page') {
        	
        	// vars
	        $front_page = (int) get_option('page_on_front');
	        
	        
	        // compare
	        $match = ( $front_page === $post->ID );
	        
        } elseif( $rule['value'] == 'posts_page') {
        	
        	// vars
	        $posts_page = (int) get_option('page_for_posts');
	        
	        
	        // compare
	        $match = ( $posts_page === $post->ID );
	        
        } elseif( $rule['value'] == 'top_level') {
        	
        	// vars
        	$page_parent = acf_maybe_get( $screen, 'page_parent', $post->post_parent );
        	
        	
        	// compare
			$match = ( $page_parent == 0 );
	            
        } elseif( $rule['value'] == 'parent' ) {
        	
        	// get children
        	$children = get_posts(array(
        		'post_type' 		=> $post->post_type,
        		'post_parent' 		=> $post->ID,
        		'posts_per_page'	=> 1,
				'fields'			=> 'ids',
        	));
        	
	        
	        // compare
	        $match = !empty( $children );
	        
        } elseif( $rule['value'] == 'child') {
        	
        	// vars
        	$page_parent = acf_maybe_get( $screen, 'page_parent', $post->post_parent );
        	
	        
	        // compare
			$match = ( $page_parent > 0 );
	        
        }
        
        
        // reverse if 'not equal to'
        if( $rule['operator'] === '!=' ) {
	        	
        	$match = !$match;
        
        }
        
        
        // return
        return $match;
        
	}
	
	
	/*
	*  rule_operators
	*
	*  This function returns the available values for this rule type
	*
	*  @type	function
	*  @date	30/5/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	(array)
	*/
	
	function rule_values( $choices, $rule ) {
		
		return array(
			'front_page'	=> __("Front Page",'acf'),
			'posts_page'	=> __("Posts Page",'acf'),
			'top_level'		=> __("Top Level Page (no parent)",'acf'),
			'parent'		=> __("Parent Page (has children)",'acf'),
			'child'			=> __("Child Page (has parent)",'acf'),
		);
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_page_type' );

endif; // class_exists check

?>