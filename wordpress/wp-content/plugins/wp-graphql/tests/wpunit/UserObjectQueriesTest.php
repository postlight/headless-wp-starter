<?php

class UserObjectQueriesTest extends \Codeception\TestCase\WPTestCase {

	public $current_time;
	public $current_date;

	public function setUp() {
		parent::setUp();

		$this->current_time = strtotime( '- 1 day' );
		$this->current_date = date( 'Y-m-d H:i:s', $this->current_time );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function createUserObject( $args = [] ) {

		/**
		 * Set up the $defaults
		 */
		$defaults = [
			'role' => 'subscriber',
		];

		/**
		 * Combine the defaults with the $args that were
		 * passed through
		 */
		$args = array_merge( $defaults, $args );

		/**
		 * Create the page
		 */
		$user_id = $this->factory->user->create( $args );

		/**
		 * Return the $id of the post_object that was created
		 */
		return $user_id;

	}

	/**
	 * testUserQuery
	 *
	 * This tests creating a single user with data and retrieving said user via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testUserQuery() {

		/**
		 * Create a user
		 */
		$user_id = $this->createUserObject(
			[
				'user_email' => 'test@test.com',
			]
		);
		$user    = get_user_by( 'id', $user_id );

		/**
		 * Create the global ID based on the user_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $user_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			user(id: \"{$global_id}\") {
				avatar {
					size
				}
				capKey
				capabilities
				comments {
					edges {
						node {
							commentId
						}
					}
				}
				description
				email
				extraCapabilities
				firstName
				id
				lastName
				locale
				mediaItems {
					edges {
						node {
							mediaItemId
						}
					}
				}
				name
				nickname
				pages {
					edges {
						node {
							pageId
						}
					}
				}
				posts {
					edges {
						node {
							postId
						}
					}
				}
				registeredDate
				roles
				slug
				url
				userId
				username
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
					'avatar'            => [
						'size' => 96,
					],
					'capKey'            => 'wp_capabilities',
					'capabilities'      => [ 'read', 'level_0', 'subscriber' ],
					'comments'          => [
						'edges' => [],
					],
					'description'       => null,
					'email'             => 'test@test.com',
					'extraCapabilities' => [ 'read', 'level_0', 'subscriber' ],
					'firstName'         => null,
					'id'                => $global_id,
					'lastName'         => null,
					'locale'            => 'en_US',
					'mediaItems'        => [
						'edges' => [],
					],
					'name'              => $user->data->display_name,
					'nickname'          => $user->nickname,
					'pages'             => [
						'edges' => [],
					],
					'posts'             => [
						'edges' => [],
					],
					'registeredDate'    => date( 'c', strtotime( $user->user_registered ) ),
					'roles'             => [ 'subscriber' ],
					'slug'              => $user->data->user_nicename,
					'url'               => null,
					'userId'            => $user_id,
					'username'          => $user->data->user_login,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testUserQueryWithComments
	 *
	 * This tests a single user with comments connection.
	 *
	 * @since 0.0.5
	 */
	public function testUserQueryWithComments() {

		/**
		 * Create a user
		 */
		$user_id = $this->createUserObject();

		$comment_id = $this->factory->comment->create( [ 'user_id' => $user_id ] );

		/**
		 * Create the global ID based on the user_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $user_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			user(id: \"{$global_id}\") {
				comments {
					edges {
						node {
							commentId
						}
					}
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
					'comments' => [
						'edges' => [
							[
								'node' => [
									'commentId' => $comment_id,
								],
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testUserQueryWithPosts
	 *
	 * This tests a single user with posts connection.
	 *
	 * @since 0.0.5
	 */
	public function testUserQueryWithPosts() {

		/**
		 * Create a user
		 */
		$user_id = $this->createUserObject();

		$post_id = $this->factory->post->create( [ 'post_author' => $user_id ] );

		/**
		 * Create the global ID based on the user_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $user_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			user(id: \"{$global_id}\") {
				posts {
					edges {
						node {
							postId
						}
					}
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
					'posts' => [
						'edges' => [
							[
								'node' => [
									'postId' => $post_id,
								],
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testUserQueryWithPages
	 *
	 * This tests a single user with pages connection.
	 *
	 * @since 0.0.5
	 */
	public function testUserQueryWithPages() {

		/**
		 * Create a user
		 */
		$user_id = $this->createUserObject();

		$post_id = $this->factory->post->create( [ 'post_author' => $user_id, 'post_type' => 'page' ] );

		/**
		 * Create the global ID based on the user_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $user_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			user(id: \"{$global_id}\") {
				pages {
					edges {
						node {
							pageId
						}
					}
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
					'pages' => [
						'edges' => [
							[
								'node' => [
									'pageId' => $post_id,
								],
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testUserQueryWithMedia
	 *
	 * This tests a single user with mediaItems connection.
	 *
	 * @since 0.0.5
	 */
	public function testUserQueryWithMedia() {

		/**
		 * Create a user
		 */
		$user_id = $this->createUserObject();

		$post_id = $this->factory->post->create( [ 'post_author' => $user_id, 'post_type' => 'attachment' ] );

		/**
		 * Create the global ID based on the user_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $user_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			user(id: \"{$global_id}\") {
				mediaItems {
					edges {
						node {
							mediaItemId
						}
					}
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
					'mediaItems' => [
						'edges' => [
							[
								'node' => [
									'mediaItemId' => $post_id,
								],
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testUserQueryWhereUserDoesNotExist
	 *
	 * Tests a query for non existant user.
	 *
	 * @since 0.0.5
	 */
	public function testUserQueryWhereUserDoesNotExist() {
		/**
		 * Create the global ID based on the user_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', 'doesNotExist' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			user(id: \"{$global_id}\") {
				userId
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
				'user' => null,
			],
			'errors' => [
				[
					'message'   => 'No user was found with the provided ID',
					'locations' => [
						[
							'line'   => 3,
							'column' => 4,
						],
					],
					'path'      => [
						'user',
					],
					'category' => 'user',
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	public function testUsersQuery() {

		/**
		 * Create a user
		 */
		$user_id = $this->createUserObject(
			[
				'user_email' => 'test@test.com',
			]
		);

		/**
		 * Create the global ID based on the user_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $user_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = '
		query {
			users(first:1) {
				edges{
				  node{
				    id
				    userId
				  }
				}
			}
		}';

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'users' => [
					'edges' => [
						[
							'node' => [
								'id' => $global_id,
								'userId' => $user_id,
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	public function testPageInfoQuery() {

		/**
		 * Let's create 2 admins and 1 subscriber so we can test our "where" arg is working
		 */
		$this->factory->user->create([
			'role' => 'administrator',
		]);

		$this->factory->user->create([
			'role' => 'administrator',
		]);

		$this->factory->user->create([
			'role' => 'subscriber',
		]);

		$query = '
		query{
		  users(first:2 where: {role:ADMINISTRATOR}){
		    pageInfo{
		      hasNextPage
		    }
		    edges{
		      node{
		        id
		      }
		    }
		  }
		}
		';

		$actual = do_graphql_request( $query );

		$this->assertNotEmpty( $actual['data']['users']['pageInfo'] );
		$this->assertNotEmpty( $actual['data']['users']['edges'] );
		$this->assertCount( 2, $actual['data']['users']['edges'] );

		$query = '
		query{   
		  users(first:1 where: {role:SUBSCRIBER}){
		    pageInfo{
		      hasNextPage
		    }
		    edges{
		      node{
		        id
		      }
		    }
		  }
		}
		';

		$actual = do_graphql_request( $query );

		/**
		 * Now let's make sure the subscriber role query worked
		 */
		$this->assertNotEmpty( $actual['data']['users']['pageInfo'] );
		$this->assertNotEmpty( $actual['data']['users']['edges'] );
		$this->assertCount( 1, $actual['data']['users']['edges'] );

	}

}