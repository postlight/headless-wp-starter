<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_post') ) :

class acf_location_post extends acf_location {
	
	
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
		$this->name = 'post';
		$this->label = __("Post",'acf');
		$this->category = 'post';
    	
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
		
		
		// bail early if not post
		if( !$post_id ) return false;
		
		
		// compare
		return $this->compare( $post_id, $rule );
		
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
		
		// get post types
		$post_types = acf_get_post_types(array(
			'exclude'	=> array('page', 'attachment')
		));
		
		
		// get posts grouped by post type
		$groups = acf_get_grouped_posts(array(
			'post_type' => $post_types
		));
		
		
		if( !empty($groups) ) {
	
			foreach( array_keys($groups) as $group_title ) {
				
				// vars
				$posts = acf_extract_var( $groups, $group_title );
				
				
				// override post data
				foreach( array_keys($posts) as $post_id ) {
					
					// update
					$posts[ $post_id ] = acf_get_post_title( $posts[ $post_id ] );
					
				};
				
				
				// append to $choices
				$choices[ $group_title ] = $posts;
				
			}
			
		}
			
		
		// return
		return $choices;
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_post' );

endif; // class_exists check

?>