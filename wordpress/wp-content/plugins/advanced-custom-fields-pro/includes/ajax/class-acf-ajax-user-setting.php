<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Ajax_User_Setting') ) :

class ACF_Ajax_User_Setting extends ACF_Ajax {
	
	/** @var string The AJAX action name */
	var $action = 'acf/ajax/user_setting';
	
	/** @var bool Prevents access for non-logged in users */
	var $public = true;
	
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
		
		// update
		if( $this->has('value') ) {
			return acf_update_user_setting( $this->get('name'), $this->get('value') );
		
		// get
		} else {
			return acf_get_user_setting( $this->get('name') );
		}
	}
}

acf_new_instance('ACF_Ajax_User_Setting');

endif; // class_exists check

?>