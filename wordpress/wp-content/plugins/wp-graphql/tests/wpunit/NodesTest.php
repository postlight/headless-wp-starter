<?php

class NodesTest extends \Codeception\TestCase\WPTestCase {

	public $admin;

	public function setUp() {
		// before
		parent::setUp();

		$this->admin = $this->factory()->user->create( [
			'role' => 'administrator'
		] );
	}

	public function tearDown() {

		// then
		parent::tearDown();
	}

	public function testNodeQueryWithVariables() {

		/**
		 * Set up the $args
		 */
		$args = array(
			'post_status'  => 'publish',
			'post_content' => 'Test page content',
			'post_title'   => 'Test Page Title',
			'post_type'    => 'page',
			'post_author'  => $this->admin,
		);

		/**
		 * Create the page
		 */
		$page_id = $this->factory->post->create( $args );

		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'page', $page_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = '
		query getPageByNode( $id:ID! ) { 
			node( id:$id ) { 
				__typename 
				...on Page {
					pageId
				}
			} 
		}';

		$variables = wp_json_encode( [
			'id' => $global_id,
		] );

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query, 'getPageByNode', $variables );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Page',
					'pageId'     => $page_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * testPageNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testPageNodeQuery() {

		/**
		 * Set up the $args
		 */
		$args = array(
			'post_status'  => 'publish',
			'post_content' => 'Test page content',
			'post_title'   => 'Test Page Title',
			'post_type'    => 'page',
			'post_author'  => $this->admin,
		);

		/**
		 * Create the page
		 */
		$page_id = $this->factory->post->create( $args );

		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'page', $page_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename 
				...on Page {
					pageId
				}
			} 
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query, '', '' );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Page',
					'pageId'     => $page_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testPostNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testPostNodeQuery() {

		$args = array(
			'post_status'  => 'publish',
			'post_content' => 'Test post content',
			'post_title'   => 'Test post Title',
			'post_type'    => 'post',
			'post_author'  => $this->admin,
		);

		$post_id = $this->factory->post->create( $args );

		$global_id = \GraphQLRelay\Relay::toGlobalId( 'post', $post_id );

		$query  = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename
				... on Post {
					postId
				}
			} 
		}";
		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Post',
					'postId'     => $post_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testAttachmentNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testAttachmentNodeQuery() {

		$args = array(
			'post_status'  => 'inherit',
			'post_content' => 'Test attachment content',
			'post_title'   => 'Test attachment Title',
			'post_type'    => 'attachment',
			'post_author'  => $this->admin,
		);

		$attachment_id = $this->factory->post->create( $args );
		$global_id     = \GraphQLRelay\Relay::toGlobalId( 'attachment', $attachment_id );
		$query         = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename
				...on MediaItem {
					mediaItemId
				}
			} 
		}";
		$actual        = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename'  => 'MediaItem',
					'mediaItemId' => $attachment_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testPluginNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testPluginNodeQuery() {

		$plugin_name = 'Hello Dolly';
		$global_id   = \GraphQLRelay\Relay::toGlobalId( 'plugin', $plugin_name );
		$query       = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename
				... on Plugin {
					name
				}
			} 
		}";

		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Plugin',
					'name'       => $plugin_name,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testThemeNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testThemeNodeQuery() {

		$theme_slug = 'twentyseventeen';
		$global_id  = \GraphQLRelay\Relay::toGlobalId( 'theme', $theme_slug );
		$query      = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename 
				...on Theme{ 
					slug 
				} 
			} 
		}";
		$actual     = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Theme',
					'slug'       => $theme_slug,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testUserNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testUserNodeQuery() {

		$user_args = array(
			'role'       => 'editor',
			'user_email' => 'graphqliscool@wpgraphql.com',
		);

		$user_id = $this->factory->user->create( $user_args );

		$global_id = \GraphQLRelay\Relay::toGlobalId( 'user', $user_id );
		$query     = "
		query { 
			node(id: \"{$global_id}\") { 
				__typename 
				...on User{ 
					userId 
				} 
			} 
		}
		";
		$actual    = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'User',
					'userId'     => $user_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testCommentNodeQuery
	 *
	 * @since 0.0.5
	 */
	public function testCommentNodeQuery() {

		$user_args = array(
			'role'       => 'editor',
			'user_email' => 'graphqliscool@wpgraphql.com',
		);

		$user_id = $this->factory->user->create( $user_args );

		$comment_args = array(
			'user_id'         => $user_id,
			'comment_content' => 'GraphQL is really awesome, dude!',
		);
		$comment_id   = $this->factory->comment->create( $comment_args );

		$global_id = \GraphQLRelay\Relay::toGlobalId( 'comment', $comment_id );
		$query     = "
		query { 
			node(id: \"{$global_id}\") {
				__typename 
				...on Comment{ 
					commentId 
				} 
			} 
		}
		";
		$actual    = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'__typename' => 'Comment',
					'commentId'  => $comment_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Tests querying for a single post node
	 */
	public function testSuccessfulPostTypeResolver() {

		$query = "
		{
		  node(id:\"cG9zdFR5cGU6cG9zdA==\"){
			...on PostType {
			  name
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'name' => 'post',
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * Tests querying for a single post node where the post id doesn't exist
	 */
	public function testUnsuccessfulPostTypeResolver() {

		$query = "
		{
		  node(id:\"cG9zdFR5cGU6dGVzdA==\"){
			...on PostType {
			  name
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$this->assertArrayHasKey( 'errors', $actual );

	}

	/**
	 * Tests querying for a single taxonomy node
	 */
	public function testSuccessfulTaxonomyResolver() {

		$query = "
		{
		  node(id:\"dGF4b25vbXk6Y2F0ZWdvcnk=\"){
			...on Taxonomy {
			  name
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'node' => [
					'name' => 'category',
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * Tests querying for a single taxonomy node where the ID doesn't exist
	 */
	public function testUnsuccessfulTaxonomyResolver() {

		$query = "
		{
		  node(id:\"dGF4b25vbXk6dGVzdA==\"){
			...on Taxonomy {
			  name
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$this->assertArrayHasKey( 'errors', $actual );

	}

	/**
	 * Tests querying for a single comment node where the comment ID doesn't exist
	 */
	public function testUnsuccessfulCommentResolver() {

		$query = "
		{
		  node(id:\"Y29tbWVudDo5OTk5\"){
			...on Comment {
			  id
			}
		  }
		}
		";

		$actual = do_graphql_request( $query );

		$this->assertArrayHasKey( 'errors', $actual );

	}

}