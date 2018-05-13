<?php

class CommentConnectionQueriesTest extends \Codeception\TestCase\WPTestCase {

	public $post_id;
	public $current_time;
	public $current_date;
	public $current_date_gmt;
	public $admin;
	public $created_comment_ids;

	public function setUp() {
		// before
		parent::setUp();

		$this->post_id = $this->factory->post->create();

		$this->current_time        = strtotime( '- 1 day' );
		$this->current_date        = date( 'Y-m-d H:i:s', $this->current_time );
		$this->current_date_gmt    = gmdate( 'Y-m-d H:i:s', $this->current_time );
		$this->admin               = $this->factory()->user->create( [
			'role' => 'administrator',
		] );
		$this->created_comment_ids = $this->create_comments();
	}

	public function tearDown() {
		// then
		parent::tearDown();
	}

	public function createCommentObject( $args = [] ) {
		/**
		 * Set up the $defaults
		 */
		$defaults = [
			'comment_author'   => $this->admin,
			'comment_content'  => 'Test comment content',
			'comment_approved' => 1,
		];

		/**
		 * Combine the defaults with the $args that were
		 * passed through
		 */
		$args = array_merge( $defaults, $args );

		/**
		 * Create the page
		 */
		$comment_id = $this->factory->comment->create( $args );

		/**
		 * Return the $id of the comment_object that was created
		 */
		return $comment_id;
	}

	/**
	 * Creates several comments (with different timestamps) for use in cursor query tests
	 *
	 * @return array
	 */
	public function create_comments() {
		// Create 20 comments
		$created_comments = [];
		for ( $i = 1; $i <= 20; $i ++ ) {
			$date                   = date( 'Y-m-d H:i:s', strtotime( "-1 day +{$i} minutes" ) );
			$created_comments[ $i ] = $this->createCommentObject( [
				'comment_content' => $i,
				'comment_date'    => $date,
			] );
		}

		return $created_comments;
	}

	public function commentsQuery( $variables ) {
		$query = 'query commentsQuery($first:Int $last:Int $after:String $before:String $where:RootCommentsCommentArgs){
			comments( first:$first last:$last after:$after before:$before where:$where ) {
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
						commentId
						content
						date
					}
				}
				nodes {
				  commentId
				}
			}
		}';

		return do_graphql_request( $query, 'commentsQuery', $variables );
	}

	public function testFirstComment() {

		$variables = [
			'first' => 1,
		];

		$results        = $this->commentsQuery( $variables );
		$comments_query = new WP_Comment_Query;
		$comments       = $comments_query->query( [
			'comment_status' => 'approved',
			'number'         => 1,
			'order'          => 'DESC',
			'orderby'        => 'comment_date',
			'comment_parent' => 0,
		] );
		$first_comment  = $comments[0];

		$expected_cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $first_comment->comment_ID );
		$this->assertNotEmpty( $results );
		$this->assertEquals( 1, count( $results['data']['comments']['edges'] ) );
		$this->assertEquals( $first_comment->comment_ID, $results['data']['comments']['edges'][0]['node']['commentId'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['edges'][0]['cursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['pageInfo']['startCursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['pageInfo']['endCursor'] );
		$this->assertEquals( $first_comment->comment_ID, $results['data']['comments']['nodes'][0]['commentId'] );

	}

	public function testForwardPagination() {

		/**
		 * Create the cursor for the comment with the oldest comment_date
		 */
		$first_comment_cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $this->created_comment_ids[20] );

		/**
		 * Set the variables to use in the GraphQL Query
		 */
		$variables = [
			'first' => 1,
			'after' => $first_comment_cursor,
		];

		/**
		 * Run the GraphQL Query
		 */
		$results = $this->commentsQuery( $variables );

		$comments_query  = new WP_Comment_Query;
		$comments        = $comments_query->query( [
			'comment_status' => 'approved',
			'number'         => 1,
			'offset'         => 1,
			'order'          => 'DESC',
			'orderby'        => 'comment_date',
			'comment_parent' => 0,
		] );
		$second_comment  = $comments[0];
		$expected_cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $second_comment->comment_ID );
		$this->assertNotEmpty( $results );
		$this->assertEquals( 1, count( $results['data']['comments']['edges'] ) );
		$this->assertEquals( $second_comment->comment_ID, $results['data']['comments']['edges'][0]['node']['commentId'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['edges'][0]['cursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['pageInfo']['startCursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['pageInfo']['endCursor'] );

	}

	public function testLastComment() {

		$variables = [
			'last' => 1,
		];

		$results = $this->commentsQuery( $variables );

		$comments_query = new WP_Comment_Query;
		$comments       = $comments_query->query( [
			'comment_status' => 'approved',
			'number'         => 1,
			'order'          => 'ASC',
			'orderby'        => 'comment_date',
			'comment_parent' => 0,
		] );
		$last_comment   = $comments[0];

		$expected_cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $last_comment->comment_ID );
		$this->assertNotEmpty( $results );
		$this->assertEquals( 1, count( $results['data']['comments']['edges'] ) );
		$this->assertEquals( $last_comment->comment_ID, $results['data']['comments']['edges'][0]['node']['commentId'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['edges'][0]['cursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['pageInfo']['startCursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['pageInfo']['endCursor'] );


	}

	public function testBackwardPagination() {

		/**
		 * Create the cursor for the comment with the newest comment_date
		 */
		$last_comment_cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $this->created_comment_ids[1] );

		$variables = [
			'last'   => 1,
			'before' => $last_comment_cursor,
		];

		$results = $this->commentsQuery( $variables );

		$comments_query         = new WP_Comment_Query;
		$comments               = $comments_query->query( [
			'comment_status' => 'approved',
			'number'         => 1,
			'offset'         => 1,
			'order'          => 'ASC',
			'orderby'        => 'comment_date',
			'comment_parent' => 0,
		] );
		$second_to_last_comment = $comments[0];

		$expected_cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $second_to_last_comment->comment_ID );

		$this->assertNotEmpty( $results );
		$this->assertEquals( 1, count( $results['data']['comments']['edges'] ) );
		$this->assertEquals( $second_to_last_comment->comment_ID, $results['data']['comments']['edges'][0]['node']['commentId'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['edges'][0]['cursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['pageInfo']['startCursor'] );
		$this->assertEquals( $expected_cursor, $results['data']['comments']['pageInfo']['endCursor'] );

	}

}