<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Ajax') ) :

class ACF_Ajax {
	
	/** @var string The AJAX action name */
	var $action = '';
	
	/** @var array The $_REQUEST data */
	var $request;
	
	/** @var bool Prevents access for non-logged in users */
	var $public = false;
	
	/**
	*  __construct
	*
	*  Sets up the class functionality.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	void
	*/
	
	function __construct() {
		
		// initialize
		$this->initialize();
		
		// add actions
		$this->add_actions();
	}
	
	/**
	*  initialize
	*
	*  Allows easy access to modifying properties without changing constructor.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	void
	*/
	
	function initialize() {
		/* do nothing */
	}
	
	/**
	*  has
	*
	*  Returns true if the request has data for the given key
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	string $key The data key
	*  @return	boolean
	*/
	
	function has( $key = '' ) {
		return isset($this->request[$key]);
	}
	
	/**
	*  get
	*
	*  Returns request data for the given key
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	string $key The data key
	*  @return	mixed
	*/
	
	function get( $key = '' ) {
		return isset($this->request[$key]) ? $this->request[$key] : null;
	}
	
	/**
	*  set
	*
	*  Sets request data for the given key
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	string $key The data key
	*  @return	mixed
	*/
	
	function set( $key = '', $value ) {
		$this->request[$key] = $value;
	}
	
	/**
	*  add_actions
	*
	*  Adds the ajax actions for this response.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	void
	*/
	
	function add_actions() {
		
		// add action for logged-in users
		add_action( "wp_ajax_{$this->action}", array($this, 'request') );
		
		// add action for non logged-in users
		if( $this->public ) {
			add_action( "wp_ajax_nopriv_{$this->action}", array($this, 'request') );
		}
	}
	
	/**
	*  request
	*
	*  Callback for ajax action. Sets up properties and calls the get_response() function.
	*
	*  @date	1/8/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	void
	*/
	
	function request() {
		
		// verify ajax request
		if( !acf_verify_ajax() ) {
			wp_send_json_error();
		}
		
		// store data for has() and get() functions
		$this->request = wp_unslash($_REQUEST);
		
		// send response
		$this->send( $this->response() );
	}
	
	/**
	*  response
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
		return true;
	}
	
	/**
	*  send
	*
	*  Sends back JSON based on the $response as either success or failure.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	mixed $response The response to send back.
	*  @return	void
	*/
	
	function send( $response ) {
		
		// return error
		if( is_wp_error($response) ) {
			wp_send_json_error(array( 'error' => $response->get_error_message() ));
		
		// return success
		} else {
			wp_send_json_success($response);
		}
	}
}

endif; // class_exists check

?>