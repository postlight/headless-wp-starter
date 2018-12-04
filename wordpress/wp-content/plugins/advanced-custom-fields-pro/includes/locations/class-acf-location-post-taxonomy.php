<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_location_post_taxonomy') ) :

class acf_location_post_taxonomy extends acf_location {
	
	
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
		$this->name = 'post_taxonomy';
		$this->label = __("Post Taxonomy",'acf');
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
		$post_terms = acf_maybe_get( $screen, 'post_terms' );
		
		// bail early if not a post
		if( !$post_id ) return false;
		
		// get selected term from rule
		$term = acf_get_term( $rule['value'] );
		
		// bail early if no term
		if( !$term || is_wp_error($term) ) return false;
		
		// if ajax, find the terms for the correct category
		if( $post_terms !== null ) {
			$post_terms = acf_maybe_get( $post_terms, $term->taxonomy, array() );
		
		// if not ajax, load post's terms
		} else {
			$post_terms = wp_get_post_terms( $post_id, $term->taxonomy, array('fields' => 'ids') );
		}
		
		// If no terms, this is a new post and should be treated as if it has the "Uncategorized" (1) category ticked
		if( !$post_terms && $term->taxonomy == 'category' ) {
			$post_terms = array( 1 );
		}
		
		// compare term IDs and slugs
		if( in_array($term->term_id, $post_terms) || in_array($term->slug, $post_terms) ) {
			$result = true;
		}
		
		 // reverse if 'not equal to'
        if( $rule['operator'] == '!=' ) {
			$result = !$result;
        }
        
        // return
        return $result;
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
		
		// get
		$choices = acf_get_taxonomy_terms();
		
			
		// unset post_format
		if( isset($choices['post_format']) ) {
		
			unset( $choices['post_format']) ;
			
		}
		
		
		// return
		return $choices;
		
	}
	
}

// initialize
acf_register_location_rule( 'acf_location_post_taxonomy' );

endif; // class_exists check

?>