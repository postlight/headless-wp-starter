<?php

class ThemeObjectQueriesTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * testThemeQuery
	 *
	 * This tests creating a single theme with data and retrieving said theme via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testThemeQuery() {

		/**
		 * Create a theme
		 */
		$theme_slug = 'twentyseventeen';

		/**
		 * Create the global ID based on the theme_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'theme', $theme_slug );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			theme(id: \"{$global_id}\") {
				author
				authorUri
				description
				id
				name
				screenshot
				slug
				tags
				themeUri
				version
			}
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query );

		$screenshot = $actual['data']['theme']['screenshot'];
		$this->assertTrue( is_string( $screenshot ) || null === $screenshot );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'theme' => [
					'author'      => null,
					'authorUri'   => 'https://wordpress.org/',
					'description' => null,
					'id'          => $global_id,
					'name'        => 'Twenty Seventeen',
					'screenshot'  => $screenshot,
					'slug'        => 'twentyseventeen',
					'tags'        => null,
					'themeUri'    => 'https://wordpress.org/themes/twentyseventeen/',
					'version'     => null,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testThemeQueryWhereThemeDoesNotExist
	 *
	 * Tests a query for non existant theme.
	 *
	 * @since 0.0.5
	 */
	public function testThemeQueryWhereThemeDoesNotExist() {
		/**
		 * Create the global ID based on the theme_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'theme', 'doesNotExist' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			theme(id: \"{$global_id}\") {
				slug
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
			'data'   => [
				'theme' => null,
			],
			'errors' => [
				[
					'message'   => 'No theme was found with the stylesheet: doesNotExist',
					'locations' => [
						[
							'line'   => 3,
							'column' => 4,
						],
					],
					'path'      => [
						'theme',
					],
					'category'  => 'user',
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

}