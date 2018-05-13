<?php

class CommentObjectQueriesTest extends \Codeception\TestCase\WPTestCase {

	public $current_time;
	public $current_date;
	public $current_date_gmt;
	public $admin;

	public function setUp() {
		parent::setUp();

		$this->current_time     = strtotime( '- 1 day' );
		$this->current_date     = date( 'Y-m-d H:i:s', $this->current_time );
		$this->current_date_gmt = gmdate( 'Y-m-d H:i:s', $this->current_time );
		$this->admin            = $this->factory()->user->create( [
			'role' => 'administrator',
		] );
	}

	public function tearDown() {
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
			'comment_date'     => $this->current_date,
			'comment_date_gmt' => $this->current_date_gmt,
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
	 * testCommentQuery
	 *
	 * This tests creating a single comment with data and retrieving said comment via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testCommentQuery() {

		/**
		 * Create a comment
		 */
		$comment_id = $this->createCommentObject( [
			'user_id' => $this->admin,
		] );

		/**
		 * Create the global ID based on the comment_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'comment', $comment_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			comment(id: \"{$global_id}\") {
				agent
				approved
				author{
					...on User {
					  userId
					}
				}
				authorIp
				children {
					edges {
						node {
							id
						}
					}
				}
				commentId
				commentedOn {
					... on Post {
						id
					}
				}
				content
				date
				dateGmt
				id
				karma
				parent {
					id
				}
				type
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
				'comment' => [
					'agent'       => '',
					'approved'    => '1',
					'author'      => [
						'userId' => $this->admin,
					],
					'authorIp'    => '',
					'children'    => [
						'edges' => [],
					],
					'commentId'   => $comment_id,
					'commentedOn' => null,
					'content'     => 'Test comment content',
					'date'        => $this->current_date,
					'dateGmt'     => $this->current_date_gmt,
					'id'          => $global_id,
					'karma'       => 0,
					'parent'      => null,
					'type'        => null,
				],
			],
		];

		$this->assertEqualSets( $expected, $actual );
	}

	/**
	 * testCommentQuery
	 *
	 * This tests creating a single comment with data and retrieving said comment via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testCommentWithCommentAuthor() {

		/**
		 * Create a comment
		 */
		$comment_id = $this->createCommentObject( [
			'comment_author'       => 'Author Name',
			'comment_author_email' => 'test@test.com',
			'comment_author_url'   => 'http://example.com',
		] );

		/**
		 * Create the global ID based on the comment_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'comment', $comment_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			comment(id: \"{$global_id}\") {
				agent
				approved
				author{
					...on CommentAuthor {
					  id
					  name
					  email
					  url
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
				'comment' => [
					'agent'    => '',
					'approved' => '1',
					'author'   => [
						'id'    => \GraphQLRelay\Relay::toGlobalId( 'commentAuthor', get_comment_author_email( $comment_id ) ),
						'name'  => get_comment_author( $comment_id ),
						'email' => get_comment_author_email( $comment_id ),
						'url'   => get_comment_author_url( $comment_id ),
					],
				],
			],
		];

		$this->assertEqualSets( $expected, $actual );
	}

	/**
	 * testCommentQuery
	 *
	 * This tests creating a single comment with data and retrieving said comment via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testCommentQueryWithChildrenAssignedPostAndParent() {

		// Post object to assign comments to.
		$post_id = $this->factory->post->create( [
			'post_content' => 'Post object',
		] );

		// Parent comment.
		$parent_comment = $this->createCommentObject(
			[
				'comment_post_ID' => $post_id,
				'comment_content' => 'Parent comment',
			]
		);

		/**
		 * Create a comment
		 */
		$comment_id = $this->createCommentObject( [
			'comment_post_ID' => $post_id,
			'comment_content' => 'Test comment',
			'comment_parent'  => $parent_comment,
		] );

		// Create child comments.
		$child_1 = $this->createCommentObject( [
			'comment_post_ID' => $post_id,
			'comment_content' => 'Child 1',
			'comment_parent'  => $comment_id,
		] );

		$child_2 = $this->createCommentObject( [
			'comment_post_ID' => $post_id,
			'comment_content' => 'Child 2',
			'comment_parent'  => $comment_id,
		] );

		/**
		 * Create the global ID based on the comment_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'comment', $comment_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			comment(id: \"{$global_id}\") {
				children {
					edges {
						node {
							commentId
							content
						}
					}
				}
				commentId
				commentedOn {
					... on Post {
						content
					}
				}
				content
				parent {
					commentId
					content
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
				'comment' => [
					'children'    => [
						'edges' => [
							[
								'node' => [
									'commentId' => $child_2,
									'content'   => 'Child 2',
								],
							],
							[
								'node' => [
									'commentId' => $child_1,
									'content'   => 'Child 1',
								],
							],
						],
					],
					'commentId'   => $comment_id,
					'commentedOn' => [
						'content' => apply_filters( 'the_content', 'Post object' ),
					],
					'content'     => 'Test comment',
					'parent'      => [
						'commentId' => $parent_comment,
						'content'   => 'Parent comment',
					],
				],
			],
		];

		$this->assertEqualSets( $expected, $actual );
	}


}