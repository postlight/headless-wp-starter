<?php

class TermObjectQueriesTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function createTermObject( $args = [] ) {
		/**
		 * Create the term
		 */
		$term_id = $this->factory->term->create( $args );

		/**
		 * Return the $id of the term_object that was created
		 */
		return $term_id;

	}

	public function testTermObjectConnectionQuery() {

		$term_id1 = $this->createTermObject( [
			'name'        => 'AAA1 Term',
			'taxonomy'    => 'category',
			'description' => 'just a description',
		] );

		$term_id2 = $this->createTermObject( [
			'name'        => 'AAA2 Term',
			'taxonomy'    => 'category',
			'description' => 'just a description',
		] );

		$term_id3 = $this->createTermObject( [
			'name'        => 'AAA3 Term',
			'taxonomy'    => 'category',
			'description' => 'just a description',
		] );

		$query = '
		{
		  categories(where:{hideEmpty:false}) {
		    edges {
		      node {
		        id
		        categoryId
		        name
		      }
		    }
		  }
		}
		';

		$actual = do_graphql_request( $query );

		$this->assertNotEmpty( $actual['data']['categories']['edges'][0]['node'] );
		$this->assertNotEmpty( $actual['data']['categories']['edges'][0]['node']['categoryId'], $term_id1 );

		$query = '
		query getCategoriesBefore($beforeCursor:String){
			categories(last:1 before:$beforeCursor where:{hideEmpty:false}){
			  edges{
			    node{
			      id
			      categoryId
			      name
			    }
			  }
			}
		}
		';

		/**
		 * Create a cursor for the first term ID
		 */
		$cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $term_id2 );

		/**
		 * Use the cursor in our variables
		 */
		$variables = wp_json_encode( [
			'beforeCursor' => $cursor,
		] );

		/**
		 * Do the request
		 */
		$actual = do_graphql_request( $query, 'getCategoriesBefore', $variables );

		/**
		 * Assert that we should have received just 1 node, $term_id2
		 */
		$this->assertCount( 1, $actual['data']['categories']['edges'] );
		$this->assertEquals( $actual['data']['categories']['edges'][0]['node']['categoryId'], $term_id1 );

		$query = '
		query getCategoriesAfter($afterCursor:String){
			categories(first:1 after:$afterCursor where:{hideEmpty:false}){
			  edges{
			    node{
			      id
			      categoryId
			      name
			    }
			  }
			}
		}
		';

		/**
		 * Create a cursor for the first term ID
		 */
		$cursor = \GraphQLRelay\Connection\ArrayConnection::offsetToCursor( $term_id2 );

		/**
		 * Use the cursor in our variables
		 */
		$variables = wp_json_encode( [
			'afterCursor' => $cursor,
		] );

		/**
		 * Do the request
		 */
		$actual = do_graphql_request( $query, 'getCategoriesAfter', $variables );

		/**
		 * Assert that we should have received just 1 node, $term_id2
		 */
		$this->assertCount( 1, $actual['data']['categories']['edges'] );
		$this->assertEquals( $actual['data']['categories']['edges'][0]['node']['categoryId'], $term_id3 );

	}

	/**
	 * testTermQuery
	 *
	 * This tests creating a single term with data and retrieving said term via a GraphQL query
	 *
	 * @since 0.0.5
	 */
	public function testTermQuery() {

		/**
		 * Create a term
		 */
		$term_id = $this->createTermObject( [
			'name'        => 'A Category',
			'taxonomy'    => 'category',
			'description' => 'just a description',
		] );

		$taxonomy = 'category';

		/**
		 * Create the global ID based on the term_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( $taxonomy, $term_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			category(id: \"{$global_id}\") {
				categoryId
				count
				description
				id
				link
				name
				posts {
					edges {
						node {
							postId
						}
					}
				}
				slug
				taxonomy {
					name
				}
				termGroupId
				termTaxonomyId
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
				'category' => [
					'categoryId'     => $term_id,
					'count'          => null,
					'description'    => 'just a description',
					'id'             => $global_id,
					'link'           => "http://wpgraphql.test/?cat={$term_id}",
					'name'           => 'A Category',
					'posts'          => [
						'edges' => [],
					],
					'slug'           => 'a-category',
					'taxonomy'       => [
						'name' => 'category',
					],
					'termGroupId'    => null,
					'termTaxonomyId' => $term_id,
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * testTermQueryWithAssociatedPostObjects
	 *
	 * Tests a term with associated post objects.
	 *
	 * @since 0.0.5
	 */
	public function testTermQueryWithAssociatedPostObjects() {

		/**
		 * Create a term
		 */
		$term_id = $this->createTermObject( [ 'name' => 'A category', 'taxonomy' => 'category' ] );

		// Create a comment and assign it to term.
		$post_id  = $this->factory->post->create( [ 'post_type' => 'post' ] );
		$page_id  = $this->factory->post->create( [ 'post_type' => 'page' ] );
		$media_id = $this->factory->post->create( [ 'post_type' => 'attachment' ] );

		wp_set_object_terms( $post_id, $term_id, 'category' );
		wp_set_object_terms( $page_id, $term_id, 'category' );
		wp_set_object_terms( $media_id, $term_id, 'category' );

		$taxonomy = 'category';

		/**
		 * Create the global ID based on the term_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( $taxonomy, $term_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			category(id: \"{$global_id}\") {
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
				'category' => [
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

	public function testTermQueryWithParentTerm() {

		$parent_id = $this->createTermObject( [
			'name'     => 'Parent Category',
			'taxonomy' => 'category',
		] );

		$child_id = $this->createTermObject( [
			'name'     => 'Child category',
			'taxonomy' => 'category',
			'parent'   => $parent_id,
		] );

		$global_parent_id = \GraphQLRelay\Relay::toGlobalId( 'category', $parent_id );
		$global_child_id  = \GraphQLRelay\Relay::toGlobalId( 'category', $child_id );

		$query = "
		query {
			category(id: \"{$global_child_id}\"){
				id
				categoryId
				ancestors{
					id
					categoryId
				}
			}
		}
		";

		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'category' => [
					'id'         => $global_child_id,
					'categoryId' => $child_id,
					'ancestors'  => [
						[
							'id'         => $global_parent_id,
							'categoryId' => $parent_id,
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * testTermQueryWhereTermDoesNotExist
	 *
	 * Tests a query for non existant term.
	 *
	 * @since 0.0.5
	 */
	public function testTermQueryWhereTermDoesNotExist() {
		$taxonomy = 'category';

		/**
		 * Create the global ID based on the term_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( $taxonomy, 'doesNotExist' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			category(id: \"{$global_id}\") {
				categoryId
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
				'category' => null,
			],
			'errors' => [
				[
					'message'   => 'No category was found with the ID: doesNotExist',
					'locations' => [
						[
							'line'   => 3,
							'column' => 4,
						],
					],
					'path'      => [
						'category',
					],
					'category'  => 'user',
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

}