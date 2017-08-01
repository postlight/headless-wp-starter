<?php
/**
 * Plugin Name: ACF to WP API
 * Description: Puts all ACF fields from posts, pages, custom post types, attachments and taxonomy terms, into the WP-API output under the 'acf' key
 * Author: Chris Hutchinson
 * Author URI: http://www.chrishutchinson.me
 * Version: 1.4.0
 * Plugin URI: https://wordpress.org/plugins/acf-to-wp-api/
 */

class ACFtoWPAPI {

	/**
	 * @var object 	$plugin 			All base plugin configuration is stored here
	 */
	protected $plugin;

	/**
	 * @var string 	$apiVersion 		Stores the version number of the REST API
	 */
	protected $apiVersion;

	/**
	 * Constructor
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @since 1.4.0 	Improved API version checking
	 * @since 1.3.3 	Compatibility fix for V2.0Beta9
	 * @since 1.3.0 	Updated to support version 2 of the WP-API
	 * @since 1.0.0
	 */
	function __construct() {
		// Setup defaults
		$this->plugin = new StdClass;
		$this->plugin->title = 'ACF to WP API';
		$this->plugin->name = 'acf-to-wp-api';
    $this->plugin->folder = WP_PLUGIN_DIR . '/' . $this->plugin->name;
    $this->plugin->url = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "", plugin_basename(__FILE__));
		$this->plugin->version = '1.4.0';

		$this->apiVersion = $this->_getAPIVersion();

		// Version One
		if($this->_isAPIVersionOne()) {
			$this->_versionOneSetup();
		}

		// Version Two
		if($this->_isAPIVersionTwo()) {
			$this->_versionTwoSetup();	
		}
	}
	/**
	 * Die and dump
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param mixed 	$data 	The data to be dumped to the screen
	 * 
	 * @return void
	 *
	 * @since 1.3.0
	 */
	private function dd($data) {
		if( WP_DEBUG ) {
			echo '<pre>';
			print_r($data);
			echo '</pre>';
			die();
		}
	}

	/**
	 * Adds the required filters and hooks for version 1 of the REST API
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	private function _versionOneSetup() {
		// Filters
		add_filter( 'json_prepare_post', array( $this, 'addACFDataPost'), 10, 3 ); // Posts
		add_filter( 'json_prepare_term', array( $this, 'addACFDataTerm'), 10, 3 ); // Taxonomy Terms
		add_filter( 'json_prepare_user', array( $this, 'addACFDataUser'), 10, 3 ); // Users
		add_filter( 'json_prepare_comment', array( $this, 'addACFDataComment'), 10, 3 ); // Comments

		// Endpoints
		add_filter( 'json_endpoints', array( $this, 'registerRoutes' ), 10, 3 );
	}

	/**
	 * Adds the required filters and hooks for version 2 of the REST API
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	private function _versionTwoSetup() {
		// Actions
		add_action( 'rest_api_init', array( $this, 'addACFDataPostV2' ) ); // Posts
		add_action( 'rest_api_init', array( $this, 'addACFDataTermV2' ) ); // Taxonomy Terms
		add_action( 'rest_api_init', array( $this, 'addACFDataUserV2' ) ); // Users
		add_action( 'rest_api_init', array( $this, 'addACFDataCommentV2' ) ); // Comments

		add_action( 'rest_api_init', array( $this, 'addACFOptionRouteV2') );
	}

	/**
	 * Returns the WP REST API version, assumes version 2
	 * if can't find any other version
	 * 
	 * @return string The version number, set by WP REST API
	 * 
	 * @since 1.3.2
	 */
	private function _getAPIVersion() {
		$version = 2;

		if ( defined('REST_API_VERSION') ) {
			$version = REST_API_VERSION;
		} else {
			$version = get_option( 'rest_api_plugin_version', get_option( 'json_api_plugin_version', null ) );
		}
		
		return $version;
	}

	/**
	 * Gets the version number of the WP REST API
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @return int 	The base version number
	 *
	 * @since 1.3.0
	 */
	private function _getAPIBaseVersion() {
		$version = $this->apiVersion;

		if( is_null( $version ) ) {
			return false;
		}

		$baseNumber = (int) substr( $version, 0, 1 );

		if( $baseNumber > 0 ) {
			return $baseNumber;
		}

		return false;
	}

	/**
	 * Check if the current API base version is version 1
	 *
	 * @return boolean 	True if the current API version is 1
	 *
	 * @since 1.3.0
	 */
	private function _isAPIVersionOne() {
		if($this->_getAPIBaseVersion() === 1) { 
			return true;
		}

		return false;
	}

	/**
	 * Check if the current API base version is version 2
	 *
	 * @return boolean 	True if the current API version is 2
	 *
	 * @since 1.3.0
	 */
	private function _isAPIVersionTwo() {
		if($this->_getAPIBaseVersion() === 2) { 
			return true;
		}

		return false;
	}

	/**
	 * Add data to users
	 *
	 * @param array 	$data 		The current ACF data
	 * @param int 		$user 		The ID of the user
	 * @param string	$context 	The context the data is being requested in
	 *
	 * @since 1.1.0
	 */
	function addACFDataUser( $data, $user, $context ) {
		$data['acf'] = $this->_getData( $user->ID, 'user' );
		return $data;
	}

	/**
	 * Add data to terms
	 *
	 * @param array 	$data 		The current ACF data
	 * @param int 		$term 		The ID of the term
	 * @param string	$context 	The context the data is being requested in
	 *
	 * @since 1.1.0
	 */
	function addACFDataTerm( $data, $term, $context = null ) {
		$data['acf'] = get_fields( $term, 'term' );
		return $data;
	}

	/**
	 * Add data to Posts, Custom Post Types, Pages & Attachments
	 *
	 * @param array 	$data 		The current ACF data
	 * @param int 		$post 		The ID of the record
	 * @param string	$context 	The context the data is being requested in
	 *
	 * @since 1.1.0
	 */
	function addACFDataPost( $data, $post, $context ) {
		$data['acf'] = $this->_getData( $post['ID'] );
		return $data;
	}

	/**
	 * Registers the `acf` field against posts
	 *
	 * @return void
	 *
	 * @since 1.3.2 	Adds support for pages and public custom post types
	 * @since 1.3.0
	 */
	function addACFDataPostV2() {
		// Posts
		register_rest_field( 'post',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataPostV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );

		// Pages
		register_rest_field( 'page',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataPostV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );

		// Public custom post types
		$types = get_post_types(array(
			'public' => true,
			'_builtin' => false
		));
		foreach($types as $key => $type) {
			register_rest_field( $type,
		        'acf',
		        array(
		            'get_callback'    => array( $this, 'addACFDataPostV2cb' ),
		            'update_callback' => null,
		            'schema'          => null,
		        )
		    );
		}
	}
	
	/**
	 * Returns the ACF data to be added to the JSON response posts
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$object 		The object to get data for
	 * @param string 	$fieldName 		The name of the field being completed
	 * @param object 	$request 		The WP_REST_REQUEST object
	 * 
	 * @return array 	The data for this object type
	 *
	 * @see ACFtoWPAPI::addACFDataPostV2()
	 *
	 * @since 1.3.0
	 */
	function addACFDataPostV2cb($object, $fieldName, $request) {
		return $this->_getData($object['id']);
	}

	/**
	 * Registers the `acf` field against taxonomy terms
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	function addACFDataTermV2() {
		register_rest_field( 'term',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataTermV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	/**
	 * Returns the ACF data to be added to the JSON response for taxonomy terms
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$object 		The object to get data for
	 * @param string 	$fieldName 		The name of the field being completed
	 * @param object 	$request 		The WP_REST_REQUEST object
	 * 
	 * @return array 	The data for this object type
	 *
	 * @see ACFtoWPAPI::addACFDataTermV2()
	 *
	 * @since 1.3.0
	 */
	function addACFDataTermV2cb($object, $fieldName, $request) {
		return $this->_getData($object['id'], 'term', $object);
	}

	/**
	 * Registers the `acf` field against users
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	function addACFDataUserV2() {
		register_rest_field( 'user',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataUserV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	/**
	 * Returns the ACF data to be added to the JSON response for users
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$object 		The object to get data for
	 * @param string 	$fieldName 		The name of the field being completed
	 * @param object 	$request 		The WP_REST_REQUEST object
	 * 
	 * @return array 	The data for this object type
	 *
	 * @see ACFtoWPAPI::addACFDataUserV2()
	 *
	 * @since 1.3.0
	 */
	function addACFDataUserV2cb($object, $fieldName, $request) {
		return $this->_getData( $object['id'], 'user' );
	}

	/**
	 * Registers the `acf` field against comments
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	function addACFDataCommentV2() {
		register_rest_field( 'comment',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataCommentV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	/**
	 * Returns the ACF data to be added to the JSON response for comments
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$object 		The object to get data for
	 * @param string 	$fieldName 		The name of the field being completed
	 * @param object 	$request 		The WP_REST_REQUEST object
	 * 
	 * @return array 	The data for this object type
	 *
	 * @see ACFtoWPAPI::addACFDataCommentV2()
	 *
	 * @since 1.3.0
	 */
	function addACFDataCommentV2cb( $object, $fieldName, $request ) {
		return $this->_getData( $object['id'], 'comment' );
	}

	/**
	 * Returns an array of Advanced Custom Fields data for the given record
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 * 
	 * @param int 		$id 		The ID of the object to get
	 * @param string 	$type 		The type of the object to get
	 * @param array 	$object 	The full object being requested, only required for specific $types
	 *
	 * @return array 	The Advanced Custom Fields data for the supplied record
	 * 
	 * @since 1.3.0
	 */
	private function _getData($id, $type = 'post', $object = array()) {
		switch($type) {
			case 'post':
			default:
				return get_fields($id);
				break;
			case 'term':
				return get_fields($object['taxonomy'] . '_' . $id);
				break;
			case 'user':
				return get_fields('user_' . $id);
				break;
			case 'comment':
				return get_fields('comment_' . $id);
			 	break;
			case 'options':
				return get_fields('option');
				break;
		}
	}

	/**
	 * Registers the routes for all and single options
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @return void
	 *
	 * @since 1.3.1 	Switched to array() notation (over [] notation) to support PHP < 5.4
	 * @since 1.3.0
	 */
	function addACFOptionRouteV2() {
		register_rest_route( 'wp/v2/acf', '/options', array(
			'methods' => array(
				'GET'
			),
			'callback' => array( $this, 'addACFOptionRouteV2cb' )
		) );

		register_rest_route( 'wp/v2/acf', '/options/(?P<option>.+)', array(
			'methods' => array(
				'GET'
			),
			'callback' => array( $this, 'addACFOptionRouteV2cb' )
		) );
	}

	/**
	 * The callback for the `wp/v2/acf/options` endpoint
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param WP_REST_Request 	$request 	The WP_REST_Request object
	 *
	 * @return array|string 	The single requested option, or all options 
	 *
	 * @see ACFtoWPAPI::addACFOptionRouteV2()
	 *
	 * @since 1.3.0
	 */
	function addACFOptionRouteV2cb( WP_REST_Request $request ) {
		if($request['option']) {
			return get_field($request['option'], 'option');
		}

		return get_fields('option');
	}

	/**
	 * Returns data for comments (WP API v1)
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$data 		The response data to be extended
	 * @param object 	$comment 	The comment being requested
	 * @param string	$context 	The context the data is being requested in
	 *
	 * @return array 	The extended $data array, with ACF data
	 *
	 * @since 1.1.0
	 *
	 */
	function addACFDataComment($data, $comment, $context) {
		$data['acf'] = $this->_getData('comment_' . $comment->comment_ID);
		return $data;
	}

	/**
	 * Returns data for options (WP API v1)
	 *
	 * @author github.com/kokarn
	 *
	 * @return array 	The options data
	 *
	 * @since 1.1.0
	 *
	 */
	function getACFOptions() {
		return get_fields('options');
	}

	/**
	 * Returns a single option based on the supplied name (WP API v1)
	 *
	 * @author github.com/asquel
	 *
	 * @param string 	$name 	The option name being requested
	 *
	 * @return mixed 	The data for the supplied option	
	 *
	 * @since 1.3.0
	 */
	function getACFOption($name) {
		return get_field($name, 'option');
	}

	/**
	 * Registers additional routes (WP API v1)
	 *
	 * @author github.com/kokarn
	 *
	 * @return array 	The routes data
	 *
	 * @since 1.1.0
	 *
	 */
	function registerRoutes( $routes ) {
		$routes['/option'] = array(
			array( array( $this, 'getACFOptions' ), WP_JSON_Server::READABLE )
		);
		$routes['/options'] = array(
			array( array( $this, 'getACFOptions' ), WP_JSON_Server::READABLE )
		);

		$routes['/options/(?P<name>[\w-]+)'] = array(
			array( array( $this, 'getACFOption' ), WP_JSON_Server::READABLE ),
		);

		return $routes;
	}

}

$ACFtoWPAPI = new ACFtoWPAPI();
