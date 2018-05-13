<?php

class AvatarObjectQueriesTest extends \Codeception\TestCase\WPTestCase {

	public $admin;

	public function setUp() {
		parent::setUp();

		$this->admin = $this->factory()->user->create( [
			'role'       => 'admin',
			'user_email' => 'test@test.com'
		] );
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * testPostQuery
	 *
	 * This tests creating a single post with data and retrieving said post via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testAvatarQuery() {
		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $this->admin );

		// Override avatar url to match $this->avatar_test_url()
		add_filter( 'get_avatar_url', array( $this, 'avatar_test_url' ), 10, 1 );

		/**
		 * Create the query string to pass to the $query
		 * Set the size to 0 to make sure that it defaults back to 96 as it has to be a positive
		 * integer
		 */
		$query = "
		query {
			user(id: \"{$global_id}\") {
				avatar(size:0 rating:G forceDefault:true) {
					default,
					extraAttr,
					forceDefault,
					foundAvatar,
					height,
					rating,
					scheme,
					size,
					url,
					width
				}
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
				'user' => [
					'avatar' => [
						'default'      => 'mm',
						'extraAttr'    => null,
						'forceDefault' => true,
						'foundAvatar'  => true,
						'height'       => 96,
						'rating'       => 'g',
						'scheme'       => null,
						'size'         => 96,
						'url'          => 'http://test-url.com',
						'width'        => 96,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );

		// Clean up filter usage.
		remove_filter( 'get_avatar_url', array( $this, 'avatar_test_url' ) );
	}

	/**
	 * testPostQuery
	 *
	 * This tests creating a single post with data and retrieving said post via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testAvatarQueryWithSizeInput() {
		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $this->admin );

		// Override avatar url to match $this->avatar_test_url()
		add_filter( 'get_avatar_url', array( $this, 'avatar_test_url' ), 10, 1 );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			user(id: \"{$global_id}\") {
				avatar(size: 48) {
					default,
					extraAttr,
					forceDefault,
					foundAvatar,
					height,
					rating,
					scheme,
					size,
					url,
					width
				}
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
				'user' => [
					'avatar' => [
						'default'      => 'mm',
						'extraAttr'    => null,
						'forceDefault' => false,
						'foundAvatar'  => true,
						'height'       => 48,
						'rating'       => 'g',
						'scheme'       => null,
						'size'         => 48,
						'url'          => 'http://test-url.com',
						'width'        => 48,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );

		// Clean up filter usage.
		remove_filter( 'get_avatar_url', array( $this, 'avatar_test_url' ) );
	}

	/**
	 * testPostQuery
	 *
	 * This tests creating a single post with data and retrieving said post via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testAvatarQueryNotFound() {
		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $this->admin );

		// Override avatar url to match $this->avatar_test_url()
		add_filter( 'get_avatar_url', array( $this, 'avatar_test_url' ), 10, 1 );
		add_filter( 'get_avatar_data', array( $this, 'fake_unfound_avatar' ) );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			user(id: \"{$global_id}\") {
				avatar(size: 48) {
					default,
					extraAttr,
					forceDefault,
					foundAvatar,
					height,
					rating,
					scheme,
					size,
					url,
					width
				}
			}
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query );

		/**
		 * The avatar should be empty.
		 */
		$expected = [
			'data' => [
				'user' => [
					'avatar' => null
				],
			],
		];

		$this->assertEquals( $expected, $actual );

		// Clean up filter usage.
		remove_filter( 'get_avatar_url', array( $this, 'avatar_test_url' ) );
		remove_filter( 'get_avatar_data', array( $this, 'fake_unfound_avatar' ) );
	}

	public function avatar_test_url( $url ) {
		return 'http://test-url.com';
	}

	public function fake_unfound_avatar( $args ) {
		$args['found_avatar'] = false;

		return $args;
	}

}