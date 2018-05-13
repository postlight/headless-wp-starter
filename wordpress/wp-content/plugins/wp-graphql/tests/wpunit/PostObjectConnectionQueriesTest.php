<?php

class PostObjectConnectionQueriesTest extends \Codeception\TestCase\WPTestCase {
	public $current_time;
	public $current_date;
	public $current_date_gmt;
	public $created_post_ids;
	public $admin;

	public function setUp() {
		parent::setUp();

		$this->current_time     = strtotime( '- 1 day' );
		$this->current_date     = date( 'Y-m-d H:i:s', $this->current_time );
		$this->current_date_gmt = gmdate( 'Y-m-d H:i:s', $this->current_time );
		$this->admin            = $this->factory()->user->create( [
			'role' => 'administrator',
		] );
		$this->created_post_ids = $this->create_posts();

		$this->app_context = new \WPGraphQL\AppContext();

		$this->app_info = new \GraphQL\Type\Definition\ResolveInfo( array() );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function createPostObject( $args ) {

		/**
		 * Set up the $defaults
		 */
		$defaults = [
			'post_author'  => $this->admin,
			'post_content' => 'Test page content',
			'post_excerpt' => 'Test excerpt',
			'post_status'  => 'publish',
			'post_title'   => 'Test Title',
			'post_type'    => 'post',
			'post_date'    => $this->current_date,
			'has_password' => false,
			'post_password'=> null,
		];

		/**
		 * Combine the defaults with the $args that were
		 * passed through
		 */
		$args = array_merge( $defaults, $args );

		/**
		 * Create the page
		 */
		$post_id = $this->factory->post->create( $args );

		/**
		 * Update the _edit_last and _edit_lock fields to simulate a user editing the page to
		 * test retrieving the fields
		 *
		 * @since 0.0.5
		 */
		update_post_meta( $post_id, '_edit_lock', $this->current_time . ':' . $this->admin );
		update_post_meta( $post_id, '_edit_last', $this->admin );

		/**
		 * Return the $id of the post_object that was created
		 */
		return $post_id;

	}

	/**
	 * Creates several posts (with different timestamps) for use in cursor query tests
	 *
	 * @return array
	 */
	public function create_posts() {

		// Create 20 posts
		$created_posts = [];
		for ( $i = 1; $i <= 200; $i ++ ) {
			// Set the date 1 minute apart for each post
			$date                = date( 'Y-m-d H:i:s', strtotime( "-1 day +{$i} minutes" ) );
			$created_posts[ $i ] = $this->createPostObject( [
				'post_type'   => 'post',
				'post_date'   => $date,
				'post_status' => 'publish',
				'post_title'  => $i,
			] );
		}

		return $created_posts;

	}

	public function postsQuery( $variables ) {

		$query = 'query postsQuery($first:Int $last:Int $after:String $before:String $where:RootPostsQueryArgs){
			posts( first:$first last:$last after:$after before:$before where:$where ) {
				pageInfo {
					hasNextPage
					hasPreviousPage
					startCursor
					endCursor
				}
				edges {
					cursor
					node {
						id
						postId
						title
						date
					}
				}
				nodes {
				  id
				  postId
				}
			}
		}';

		return do_graphql_request( $query, 'postsQuery', $variables );

	}

	public function testFirstPost() {

		/**
		 * Here we're querying the first post in our dataset
		 */
		$variables = [
			'first' => 1,
		];
		$results   = $this->postsQuery( $variables );

		/**
		 * Let's query the first post in our data set so we can test against it
		 */
		$first_post      = new WP_Query( [
			'posts_per_page' => 1,
		] );
		$first_post_id   = $first_post->posts[0]->ID;
		$expected_cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $first_post_id );
		$this->assertNotEmpty( $results );
		$this->assertEquals( 1, count( $results['data']['posts']['edges'] ) );
		$this->assertEquals( $first_post_id, $results['data']['posts']['edges'][0]['node']['postId'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['edges'][0]['cursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['pageInfo']['startCursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['pageInfo']['endCursor'] );
		$this->assertEquals( $first_post_id, $results['data']['posts']['nodes'][0]['postId'] );

		$this->forwardPagination( $expected_cursor );

	}

	public function testLastPost() {
		/**
		 * Here we're trying to query the last post in our dataset
		 */
		$variables = [
			'last' => 1,
		];
		$results   = $this->postsQuery( $variables );

		/**
		 * Let's query the last post in our data set so we can test against it
		 */
		$last_post    = new WP_Query( [
			'posts_per_page' => 1,
			'order'          => 'ASC',
		] );
		$last_post_id = $last_post->posts[0]->ID;

		$expected_cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $last_post_id );

		$this->assertNotEmpty( $results );
		$this->assertEquals( 1, count( $results['data']['posts']['edges'] ) );
		$this->assertEquals( $last_post_id, $results['data']['posts']['edges'][0]['node']['postId'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['edges'][0]['cursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['pageInfo']['startCursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['pageInfo']['endCursor'] );

		$this->backwardPagination( $expected_cursor );

	}

	public function forwardPagination( $cursor ) {

		$variables = [
			'first' => 1,
			'after' => $cursor,
		];

		$results = $this->postsQuery( $variables );

		$second_post     = new WP_Query( [
			'posts_per_page' => 1,
			'paged'          => 2,
		] );
		$second_post_id  = $second_post->posts[0]->ID;
		$expected_cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $second_post_id );
		$this->assertNotEmpty( $results );
		$this->assertEquals( 1, count( $results['data']['posts']['edges'] ) );
		$this->assertEquals( $second_post_id, $results['data']['posts']['edges'][0]['node']['postId'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['edges'][0]['cursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['pageInfo']['startCursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['pageInfo']['endCursor'] );
	}

	public function backwardPagination( $cursor ) {

		$variables = [
			'last'   => 1,
			'before' => $cursor,
		];

		$results = $this->postsQuery( $variables );

		$second_to_last_post    = new WP_Query( [
			'posts_per_page' => 1,
			'paged'          => 2,
			'order'          => 'ASC',
		] );
		$second_to_last_post_id = $second_to_last_post->posts[0]->ID;
		$expected_cursor        = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $second_to_last_post_id );
		$this->assertNotEmpty( $results );
		$this->assertEquals( 1, count( $results['data']['posts']['edges'] ) );
		$this->assertEquals( $second_to_last_post_id, $results['data']['posts']['edges'][0]['node']['postId'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['edges'][0]['cursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['pageInfo']['startCursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['posts']['pageInfo']['endCursor'] );

	}

	public function testMaxQueryAmount() {
		$variables = [
			'first' => 150,
		];
		$results   = $this->postsQuery( $variables );
		$this->assertNotEmpty( $results );

		/**
		 * The max that can be queried by default is 100 items
		 */
		$this->assertCount( 100, $results['data']['posts']['edges'] );
		$this->assertTrue( $results['data']['posts']['pageInfo']['hasNextPage'] );

		/**
		 * Test the filter to make sure it's capping the results properly
		 */
		add_filter( 'graphql_connection_max_query_amount', function() {
			return 20;
		} );

		$variables = [
			'first' => 150,
		];
		$results   = $this->postsQuery( $variables );

		add_filter( 'graphql_connection_max_query_amount', function() {
			return 100;
		} );

		$this->assertCount( 20, $results['data']['posts']['edges'] );
		$this->assertTrue( $results['data']['posts']['pageInfo']['hasNextPage'] );
	}

	public function testPostHasPassword() {
		// Create a test post with a password
		$this->createPostObject( [
			'post_title'    => 'Password protected',
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_password' => 'password',
		] );

		/**
		 * WP_Query posts with a password
		 */
		$wp_query_posts_with_password = new WP_Query( [
			'has_password' => true,
		] );

		/**
		 * GraphQL query posts that have a password
		 */
		$variables = [
			'where' => [
				'hasPassword' => true,
			],
		];

		$request = $this->postsQuery( $variables );

		$this->assertNotEmpty( $request );
		$this->assertArrayNotHasKey( 'errors', $request );

		$edges = $request['data']['posts']['edges'];
		$this->assertNotEmpty( $edges );

		/**
		 * Loop through all the returned posts
		 */
		foreach ( $edges as $edge ) {

			/**
			 * Assert that all posts returned have a password, since we queried for
			 * posts using "has_password => true"
			 */
			$password = get_post( $edge['node']['postId'] )->post_password;
			$this->assertNotEmpty( $password );

		}

	}

	public function testPageWithChildren() {

		$parent_id = $this->factory->post->create( [
			'post_type' => 'page'
		] );

		$child_id = $this->factory->post->create( [
			'post_type'   => 'page',
			'post_parent' => $parent_id
		] );

		$global_id       = \GraphQLRelay\Relay::toGlobalId( 'page', $parent_id );
		$global_child_id = \GraphQLRelay\Relay::toGlobalId( 'page', $child_id );

		$query = '
		{
			page( id: "' . $global_id . '" ) {
				id
				pageId
				childPages {
					edges {
						node {
							id
							pageId
						}
					}
				}
			}
		}
		';

		$actual = do_graphql_request( $query );

		/**
		 * Make sure the query didn't return any errors
		 */
		$this->assertArrayNotHasKey( 'errors', $actual );

		$parent = $actual['data']['page'];
		$child  = $parent['childPages']['edges'][0]['node'];

		/**
		 * Make sure the child and parent data matches what we expect
		 */
		$this->assertEquals( $global_id, $parent['id'] );
		$this->assertEquals( $parent_id, $parent['pageId'] );
		$this->assertEquals( $global_child_id, $child['id'] );
		$this->assertEquals( $child_id, $child['pageId'] );


	}

	public function testSanitizeInputFieldsAuthorArgs() {
		$mock_args = [
			'authorName'  => 'testAuthorName',
			'authorIn'    => [ 1, 2, 3 ],
			'authorNotIn' => [ 4, 5, 6 ],
		];

		$actual = \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver::sanitize_input_fields( $mock_args, null, [], $this->app_context, $this->app_info );

		/**
		 * Make sure the returned values are equal to mock args
		 */
		$this->assertEquals( 'testAuthorName', $actual['author_name'] );
		$this->assertEquals( [ 1, 2, 3 ], $actual['author__in'] );
		$this->assertEquals( [ 4, 5, 6 ], $actual['author__not_in'] );

		/**
		 * Make sure the query didn't return these array values
		 */
		$this->assertArrayNotHasKey( 'authorName', $actual );
		$this->assertArrayNotHasKey( 'authorIn', $actual );
		$this->assertArrayNotHasKey( 'authorNotIn', $actual );
	}

	public function testSanitizeInputFieldsCategoryArgs() {
		$mock_args = [
			'categoryId'   => 1,
			'categoryName' => 'testCategory',
			'categoryIn'   => [ 4, 5, 6 ],
		];

		$actual = \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver::sanitize_input_fields( $mock_args, null, [], $this->app_context, $this->app_info );

		/**
		 * Make sure the returned values are equal to mock args
		 */
		$this->assertEquals( 1, $actual['cat'] );
		$this->assertEquals( 'testCategory', $actual['category_name'] );
		$this->assertEquals( [ 4, 5, 6 ], $actual['category__in'] );

		/**
		 * Make sure the query didn't return these array values
		 */
		$this->assertArrayNotHasKey( 'categoryId', $actual );
		$this->assertArrayNotHasKey( 'categoryName', $actual );
		$this->assertArrayNotHasKey( 'categoryIn', $actual );
	}

	public function testSanitizeInputFieldsTagArgs() {
		$mock_args = [
			'tagId'      => 1,
			'tagIds'     => [ 1, 2, 3 ],
			'tagSlugAnd' => [ 4, 5, 6 ],
			'tagSlugIn'  => [ 6, 7, 8 ],
		];

		$actual = \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver::sanitize_input_fields( $mock_args, null, [], $this->app_context, $this->app_info );

		/**
		 * Make sure the returned values are equal to mock args
		 */
		$this->assertEquals( 1, $actual['tag_id'] );
		$this->assertEquals( [ 1, 2, 3 ], $actual['tag__and'] );
		$this->assertEquals( [ 4, 5, 6 ], $actual['tag_slug__and'] );
		$this->assertEquals( [ 6, 7, 8 ], $actual['tag_slug__in'] );

		/**
		 * Make sure the query didn't return these array values
		 */
		$this->assertArrayNotHasKey( 'tagId', $actual );
		$this->assertArrayNotHasKey( 'tagIds', $actual );
		$this->assertArrayNotHasKey( 'tagSlugAnd', $actual );
		$this->assertArrayNotHasKey( 'tagSlugIn', $actual );
	}

	public function testSanitizeInputFieldsSearchArgs() {
		$mock_args = [
			'search' => 'testSearchString',
			'id'     => 1,
		];

		$actual = \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver::sanitize_input_fields( $mock_args, null, [], $this->app_context, $this->app_info );

		/**
		 * Make sure the returned values are equal to mock args
		 */
		$this->assertEquals( 'testSearchString', $actual['s'] );
		$this->assertEquals( 1, $actual['p'] );

		/**
		 * Make sure the query didn't return these array values
		 */
		$this->assertArrayNotHasKey( 'search', $actual );
		$this->assertArrayNotHasKey( 'id', $actual );
	}

	public function testSanitizeInputFieldsParentArgs() {
		$mock_args = [
			'parent'      => 2,
			'parentIn'    => [ 3, 4, 5 ],
			'parentNotIn' => [ 6, 7, 8 ],
			'in'          => [ 9, 10, 11 ],
			'notIn'       => [ 12, 13, 14 ],
			'nameIn'      => [ 'testPost1', 'testPost2', 'testPost3' ],
		];

		$actual = \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver::sanitize_input_fields( $mock_args, null, [], $this->app_context, $this->app_info );

		/**
		 * Make sure the returned values are equal to mock args
		 */
		$this->assertEquals( 2, $actual['post_parent'] );
		$this->assertEquals( [ 3, 4, 5 ], $actual['post_parent__in'] );
		$this->assertEquals( [ 6, 7, 8 ], $actual['post_parent__not_in'] );
		$this->assertEquals( [ 9, 10, 11 ], $actual['post__in'] );
		$this->assertEquals( [ 12, 13, 14 ], $actual['post__not_in'] );
		$this->assertEquals( [ 'testPost1', 'testPost2', 'testPost3' ], $actual['post_name__in'] );

		/**
		 * Make sure the query didn't return these array values
		 */
		$this->assertArrayNotHasKey( 'parent', $actual );
		$this->assertArrayNotHasKey( 'parentIn', $actual );
		$this->assertArrayNotHasKey( 'parentNotIn', $actual );
		$this->assertArrayNotHasKey( 'in', $actual );
		$this->assertArrayNotHasKey( 'notIn', $actual );
		$this->assertArrayNotHasKey( 'nameIn', $actual );
	}

	public function testSanitizeInputFieldsMiscArgs() {
		$mock_args = [
			'hasPassword' => true,
			'password'    => 'myPostPassword123',
			'status'      => 'publish',
			'dateQuery'   => array(
				array(
					'year'  => 2012,
					'month' => 12,
					'day'   => 12,
				),
			),
		];

		$actual = \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver::sanitize_input_fields( $mock_args, null, [], $this->app_context, $this->app_info );

		/**
		 * Make sure the returned values are equal to mock args
		 */
		$this->assertTrue( $actual['has_password'] );
		$this->assertEquals( 'myPostPassword123', $actual['post_password'] );
		$this->assertEquals( 'publish', $actual['post_status'] );
		$this->assertEquals(
			array(
				array(
					'year'  => 2012,
					'month' => 12,
					'day'   => 12,
				),
			)
			, $actual['date_query'] );

		/**
		 * Make sure the query didn't return these array values
		 */
		$this->assertArrayNotHasKey( 'hasPassword', $actual );
		$this->assertArrayNotHasKey( 'password', $actual );
		$this->assertArrayNotHasKey( 'status', $actual );
		$this->assertArrayNotHasKey( 'dateQuery', $actual );
	}

	public function testSanitizeInputFieldsListOfPostStatusEnum() {
		$mock_args = [
			'stati'      =>  [ 'publish', 'private' ],
		];

		$actual = \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver::sanitize_input_fields( $mock_args, null, [], $this->app_context, $this->app_info );

		/**
		 * Make sure the returned values are equal to mock args
		 */
		$this->assertEquals( ['publish', 'private'], $actual['post_status'] );

		/**
		 * Make sure the query didn't return these array values
		 */
		$this->assertArrayNotHasKey( 'status', $actual );
	}

	/**
	 * @group get_query_args
	 */
	public function testGetQueryArgs() {
		/**
		 * Mock args
		 */
		$mock_args = array(
			'orderby' => 'DESC',
			'where'   => array(
				'orderby' => array(
					array(
						'field' => 'author',
						'order' => 'ASC',
					),
				),
			),
		);

		/**
		 * Create post
		 */
		$test_post = $this->factory->post->create();

		$source = get_post( $test_post );

		/**
		 * New page
		 */
		$actual = new \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver( 'page' );

		$actual = $actual::get_query_args( $source, $mock_args, $this->app_context, $this->app_info );

		/**
		 * Expected result
		 */
		$expected = array(
			'post_type'              => 'page',
			'no_found_rows'          => true,
			'post_status'            => 'publish',
			'posts_per_page'         => 11,
			'post_parent'            => $test_post,
			'graphql_cursor_offset'  => 0,
			'graphql_cursor_compare' => '<',
			'graphql_args'           => array(
				'orderby' => 'DESC',
				'where'   => array(
					'orderby' => array(
						0 => array(
							'field' => 'author',
							'order' => 'ASC',
						)
					),
				),
			),
			'orderby'                => array(
				'author' => 'ASC',
			),
		);

		/**
		 * Make sure the expected result is equal to the response of $actual
		 */
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * @group get_query_args
	 */
	public function testGetQueryArgsAttachment() {

		/**
		 * Mock args
		 */
		$mock_args = array(
			'post_status' => 'publish',
		);

		/**
		 * Create attachment
		 */
		$child_id = $this->factory->post->create( [
			'post_type' => 'attachment',
		] );

		$post_type = 'attachment';

		$source = get_post( $child_id );

		/**
		 * New post type attachment
		 */
		$actual = new \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver( $post_type );

		$actual = $actual->get_query_args( $source, $mock_args, $this->app_context, $this->app_info );

		/**
		 * Make sure the post status is equal to inherit
		 */
		$this->assertEquals( 'inherit', $actual['post_status'] );

		/**
		 * Make sure get_query_args is setting the post id as post_parent
		 */
		$this->assertEquals( $child_id, $actual['post_parent'] );
	}

	/**
	 * @group get_query_args
	 */
	public function testGetQueryArgsPostType() {

		/**
		 * Get post type object
		 */
		$source = get_post_type_object( 'post' );

		$mock_args = array();

		$actual = \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver::get_query_args( $source, $mock_args, $this->app_context, $this->app_info );

		/**
		 * Make sure that post type is equals to post
		 */
		$this->assertEquals( 'post', $actual['post_type'] );
	}

	/**
	 * @group get_query_args
	 */
	public function testGetQueryArgsUser() {
		/**
		 * Create a user
		 */
		$user_id = $this->factory->user->create();

		$source = get_user_by( 'ID', $user_id );

		$mock_args = array();

		$actual = \WPGraphQL\Type\PostObject\Connection\PostObjectConnectionResolver::get_query_args( $source, $mock_args, $this->app_context, $this->app_info );

		/**
		 * Make sure the author is equal to the user previously created
		 */
		$this->assertEquals( $user_id, $actual['author'] );

	}

}