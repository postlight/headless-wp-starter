<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Ajax_Query_Terms') ) :

class ACF_Ajax_Query_Terms extends ACF_Ajax_Query {
	
	/** @var string The AJAX action name */
	var $action = 'acf/ajax/query_terms';
	
	/**
	*  get_args
	*
	*  description
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function get_args() {
		
		// defaults
		$args = wp_parse_args($this->get('query'), array(
			'taxonomy'	=> 'category',
			'search'	=> $this->search,
			'number'	=> $this->per_page,
		));
		
		// pagination
		if( $this->page > 0 ) {
			$args['offset'] = $this->per_page * ($this->page - 1);
		}
		
		// return
		return $args;
	}
	
	/**
	*  get_results
	*
	*  description
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function get_results( $args ) {
		
		// vars
		$results = array();
		
		// get terms
		$groups = acf_get_grouped_terms( $args );
		
		// loop
		if( $groups ) {
		foreach( $groups as $label => $terms ) {
			
			// data
			$data = array(
				'text'		=> $label,
				'children'	=> array()
			);
			
			// convert object to string
			foreach( $terms as $id => $term ) {
				$terms[ $id ] = $this->get_result( $term );
			}
			
			
			// order posts by search
			if( $this->search && !isset($args['orderby']) ) {
				$terms = acf_order_by_search( $terms, $this->search );
			}
			
			
			// append to $data
			foreach( $terms as $id => $text ) {
				$this->count++;
				$data['children'][] = array(
					'id'	=> $id,
					'text'	=> $text
				);
			}
						
			// append to $results
			$results[] = $data;
		}}
		
		
		// extract group children for a single taxonomy
		$taxonomies = acf_get_array($args['taxonomy']);
		if( count($taxonomies) == 1 ) {
			$results = $results[0]['children'];
		}
		
		// return
		return $results;
	}
	
	/**
	*  get_result
	*
	*  description
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function get_result( $term ) {
		
		// vars
		$title = $term->name;
		
		// ancestors
		$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
		if( $ancestors ) {
			$prepend = str_repeat('- ', count($ancestors));
			return $prepend . $title;
		}
		
		// return
		return $title;
	}
}

acf_new_instance('ACF_AJAX_Query_Terms');

endif; // class_exists check

?>