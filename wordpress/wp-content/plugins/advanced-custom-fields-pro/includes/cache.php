<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_cache') ) :

class acf_cache {
	
	// vars
	var $reference = array(),
		$active = true;
		
		
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
		
		// prevent ACF from persistent cache
		wp_cache_add_non_persistent_groups('acf');
		
	}
	
	
	/*
	*  is_active
	*
	*  This function will return true if caching is enabled
	*
	*  @type	function
	*  @date	26/6/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	(bool)
	*/
	
	function is_active() {
		
		return $this->active;
		
	}
	
	
	/*
	*  enable
	*
	*  This function will enable ACF caching
	*
	*  @type	function
	*  @date	26/6/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function enable() {
		
		$this->active = true;
		
	}
	
	
	/*
	*  disable
	*
	*  This function will disable ACF caching
	*
	*  @type	function
	*  @date	26/6/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function disable() {
		
		$this->active = false;
		
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
	*  isset_cache
	*
	*  This function will return true if a cached data exists for the given key
	*
	*  @type	function
	*  @date	30/06/2016
	*  @since	5.4.0
	*
	*  @param	$key (string)
	*  @return	(boolean)
	*/
	
	function isset_cache( $key = '' ) {
		
		// bail early if not active
		if( !$this->is_active() ) return false;
		
		
		// vars
		$key = $this->get_key($key);
		$found = false;
		
		
		// get cache
		$cache = wp_cache_get($key, 'acf', false, $found);
		
		
		// return
		return $found;
		
	}
	
	
	/*
	*  get_cache
	*
	*  This function will return cached data for a given key
	*
	*  @type	function
	*  @date	30/06/2016
	*  @since	5.4.0
	*
	*  @param	$key (string)
	*  @return	(mixed)
	*/
	
	function get_cache( $key = '' ) {
		
		// bail early if not active
		if( !$this->is_active() ) return false;
		
		
		// vars
		$key = $this->get_key($key);
		$found = false;
		
		
		// get cache
		$cache = wp_cache_get($key, 'acf', false, $found);
		
		
		// return
		return $cache;
		
	}
	
	
	/*
	*  set_cache
	*
	*  This function will set cached data for a given key
	*
	*  @type	function
	*  @date	30/06/2016
	*  @since	5.4.0
	*
	*  @param	$key (string)
	*  @param	$data (mixed)
	*  @return	n/a
	*/
	
	function set_cache( $key = '', $data = '' ) {
		
		// bail early if not active
		if( !$this->is_active() ) return false;
		
		
		// set
		wp_cache_set($key, $data, 'acf');
		
		
		// return
		return $key;
		
	}
	
	
	/*
	*  set_cache_reference
	*
	*  This function will set a reference to cached data for a given key
	*
	*  @type	function
	*  @date	30/06/2016
	*  @since	5.4.0
	*
	*  @param	$key (string)
	*  @param	$reference (string)
	*  @return	n/a
	*/
	
	function set_cache_reference( $key = '', $reference = '' ) {
		
		// bail early if not active
		if( !$this->is_active() ) return false;
		
		
		// add
		$this->reference[ $key ] = $reference;	
		
		
		// resturn
		return $key;
		
	}
	
	
	/*
	*  delete_cache
	*
	*  This function will delete cached data for a given key
	*
	*  @type	function
	*  @date	30/06/2016
	*  @since	5.4.0
	*
	*  @param	$key (string)
	*  @return	n/a
	*/
	
	function delete_cache( $key = '' ) {
		
		// bail early if not active
		if( !$this->is_active() ) return false;
		
		
		// delete
		return wp_cache_delete( $key, 'acf' );
		
	}
	
}


// initialize
acf()->cache = new acf_cache();

endif; // class_exists check


/*
*  acf_is_cache_active
*
*  alias of acf()->cache->is_active()
*
*  @type	function
*  @date	26/6/17
*  @since	5.6.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_is_cache_active() {
	
	return acf()->cache->is_active();
	
}


/*
*  acf_disable_cache
*
*  alias of acf()->cache->disable()
*
*  @type	function
*  @date	26/6/17
*  @since	5.6.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_disable_cache() {
	
	return acf()->cache->disable();
	
}


/*
*  acf_enable_cache
*
*  alias of acf()->cache->enable()
*
*  @type	function
*  @date	26/6/17
*  @since	5.6.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_enable_cache() {
	
	return acf()->cache->enable();
	
}


/*
*  acf_isset_cache
*
*  alias of acf()->cache->isset_cache()
*
*  @type	function
*  @date	30/06/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_isset_cache( $key = '' ) {
	
	return acf()->cache->isset_cache( $key );
	
}


/*
*  acf_get_cache
*
*  alias of acf()->cache->get_cache()
*
*  @type	function
*  @date	30/06/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_get_cache( $key = '' ) {
	
	return acf()->cache->get_cache( $key );
	
}


/*
*  acf_set_cache
*
*  alias of acf()->cache->set_cache()
*
*  @type	function
*  @date	30/06/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_set_cache( $key = '', $data ) {
	
	return acf()->cache->set_cache( $key, $data );
	
}


/*
*  acf_set_cache_reference
*
*  alias of acf()->cache->set_cache_reference()
*
*  @type	function
*  @date	30/06/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_set_cache_reference( $key = '', $reference = '' ) {
	
	return acf()->cache->set_cache_reference( $key, $reference );
	
}


/*
*  acf_delete_cache
*
*  alias of acf()->cache->delete_cache()
*
*  @type	function
*  @date	30/06/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_delete_cache( $key = '' ) {
	
	return acf()->cache->delete_cache( $key );
	
}

?>