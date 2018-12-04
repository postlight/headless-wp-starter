<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Ajax_Upgrade') ) :

class ACF_Ajax_Upgrade extends ACF_Ajax {
	
	/** @var string The AJAX action name */
	var $action = 'acf/ajax/upgrade';
	
	/**
	*  get_response
	*
	*  The actual logic for this AJAX request.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	mixed The response data to send back or WP_Error.
	*/
	
	function response() {
		
		// switch blog
		if( $this->has('blog_id') ) {
			switch_to_blog( $this->get('blog_id') );
		}
		
		// bail early if no upgrade avaiable
		if( !acf_has_upgrade() ) {
			return new WP_Error( 'upgrade_error', __('No updates available.', 'acf') );
		}
		
		// listen for output
		ob_start();
		
		// run upgrades
		acf_upgrade_all();
		
		// store output
		$error = ob_get_clean();
		
		// return error if output
		if( $error ) {
			return new WP_Error( 'upgrade_error', $error );
		}
		
		// return
		return true;
	}
}

acf_new_instance('ACF_Ajax_Upgrade');

endif; // class_exists check

?>