<?php

class PluginObjectQueriesTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * testPluginQuery
	 *
	 * This tests creating a single plugin with data and retrieving said plugin via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testPluginQuery() {

		/**
		 * Create the global ID based on the plugin_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'plugin', 'Hello Dolly' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			plugin(id: \"{$global_id}\") {
				author
				authorUri
				description
				id
				name
				pluginUri
				version
			}
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'plugin' => [
					'author' => 'Matt Mullenweg',
					'authorUri' => 'http://ma.tt/',
					'description' => 'This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from <cite>Hello, Dolly</cite> in the upper right of your admin screen on every page.',
					'id' => $global_id,
					'name' => 'Hello Dolly',
					'pluginUri' => 'http://wordpress.org/plugins/hello-dolly/',
					'version' => '1.6',
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testPluginQueryWherePluginDoesNotExist
	 *
	 * Tests a query for non existant plugin.
	 *
	 * @since 0.0.5
	 */
	public function testPluginQueryWherePluginDoesNotExist() {
		/**
		 * Create the global ID based on the plugin_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'plugin', 'doesNotExist' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			plugin(id: \"{$global_id}\") {
				version
			}
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'plugin' => null,
			],
			'errors' => [
				[
					'message' => 'No plugin was found with the name doesNotExist',
					'locations' => [
						[
							'line' => 3,
							'column' => 4,
						],
					],
					'path' => [
						'plugin',
					],
					'category' => 'user',
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

}