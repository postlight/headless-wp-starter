<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Ajax_Query') ) :

class ACF_Ajax_Query extends ACF_Ajax {
	
	/** @var array The ACF field used for querying */
	var $field = false;
	
	/** @var int The page of results to return */
	var $page = 1;
	
	/** @var int The number of results per page */
	var $per_page = 20;
	
	/** @var string The searched term */
	var $search = '';
	
	/** @var int The number of results found */
	var $count = 0;
	
	/**
	*  response
	*
	*  The actual logic for this AJAX request.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	void
	*/
	
	function response() {
		
		// field
		if( $this->has('field_key') ) {
			$this->field = acf_get_field( $this->get('field_key') );
		}
		
		// pagination
		if( $this->has('paged') ) {
			$this->page = (int) $this->get('paged');
		}
		
		// search
		if( $this->has('s') ) {
			$this->search = $this->get('s');
		}
		
		// get response
		$args = $this->get_args();
		$results = $this->get_results($args);
		$response = $this->get_response($results, $args);
		
		// return
		return $response;
	}
	
	
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
		return array();
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
		return array();
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
	
	function get_result( $item ) {
		return '';
	}
	
	/**
	*  get_response
	*
	*  description
	*
	*  @date	31/7/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function get_response( $results, $args ) {
		return array(
			'results'	=> $results,
			'more'		=> ($this->count >= $this->per_page)
		);
	}
}

endif; // class_exists check

?>