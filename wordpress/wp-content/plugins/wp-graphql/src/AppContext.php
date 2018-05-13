<?php
namespace WPGraphQL;

/**
 * Class AppContext
 * Creates an object that contains all of the context for the GraphQL query
 * This class gets instantiated and populated in the main WPGraphQL class
 *
 * @package WPGraphQL
 */
class AppContext {

	/**
	 * Stores the url string for the current site
	 *
	 * @var string $root_url
	 * @access public
	 */
	public $root_url;

	/**
	 * Stores the WP_User object of the current user
	 *
	 * @var \WP_User $viewer
	 * @access public
	 */
	public $viewer;

	/**
	 * Stores everything from the $_REQUEST global
	 *
	 * @var \mixed $request
	 * @access public
	 */
	public $request;

	/**
	 * Stores additional $config properties
	 * @var \mixed $config
	 * @access public
	 */
	public $config;

	/**
	 * AppContext constructor.
	 */
	public function __construct() {

		$this->config = apply_filters( 'graphql_app_context_config', $this->config );

	}

}
