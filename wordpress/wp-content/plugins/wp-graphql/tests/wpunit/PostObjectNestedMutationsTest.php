<?php

class PostObjectNestedMutationsTest extends \Codeception\TestCase\WPTestCase {

	public $title;
	public $content;
	public $client_mutation_id;
	public $admin;
	public $subscriber;
	public $author;

	public function setUp() {
		// before
		parent::setUp();

		$this->title              = 'some title';
		$this->content            = 'some content';
		$this->client_mutation_id = 'someUniqueId';

		$this->author = $this->factory()->user->create( [
			'role' => 'author',
		] );

		$this->admin = $this->factory()->user->create( [
			'role' => 'administrator',
		] );

		$this->subscriber = $this->factory()->user->create( [
			'role' => 'subscriber',
		] );

	}


	public function tearDown() {
		parent::tearDown();
	}

	public function createPostMutation( $args = [] ) {

		$mutation = '
		mutation CreatePostWithTerms( $input: CreatePostInput! ) {
			createPost( input: $input ) {
			  post {
			    id
			    postId
			    title
			    tags {
			      edges {
			        node {
			          id
			          tagId
			          name
			          description
			          slug
			        }
			      }
			    }
			    categories {
			      edges {
			         node {
			           id
			           categoryId
			           name
			           description
			           slug
			         }
			      }
			    }
			  }
			}
		}
		';

		$default = [
			'clientMutationId' => uniqid(),
			'title' => 'Test Title',
			'status' => 'PUBLISH',
		];

		$input = array_merge( $default, $args );

		$variables = [
			'input' => $input,
		];

		$response = do_graphql_request( $mutation, 'CreatePostWithTerms', $variables );
		return $response;

	}

	public function testCreatePostAndAttachCategories() {

		$tag_slug = uniqid();
		$tag = wp_insert_term( $tag_slug, 'post_tag' );
		$expected_tag_id = \GraphQLRelay\Relay::toGlobalId( 'post_tag', absint( $tag['term_id'] ) );

		$category_slug = uniqid();
		$category = wp_insert_term( $category_slug, 'category' );
		$expected_category_id = \GraphQLRelay\Relay::toGlobalId( 'category', absint( $category['term_id']  ) );

		wp_set_current_user( $this->admin );
		$results = $this->createPostMutation([
			'tags' => [
				'append' => false,
				'nodes' => [
					[
						'slug' => $tag_slug
					],
				]

			],
			'categories' => [
				'append' => false,
				'nodes' => [
					[
						'slug' => $category_slug
					],
				]
			]
		]);

		$this->assertArrayNotHasKey( 'errors', $results );

		$createdPost = $results['data']['createPost']['post'];
		$this->assertEquals( 'Test Title', $createdPost['title'] );
		$this->assertEquals( $expected_tag_id, $createdPost['tags']['edges'][0]['node']['id'] );
		$this->assertEquals( $expected_category_id, $createdPost['categories']['edges'][0]['node']['id'] );


	}

	public function testCreatePostAndAttachTagByID() {

		wp_set_current_user( $this->admin );

		$new_term = $this->factory->term->create([
			'name' => 'Test Term',
			'taxonomy' => 'post_tag'
		]);

		$new_term_global_id = \GraphQLRelay\Relay::toGlobalId( 'post_tag', $new_term );

		$results = $this->createPostMutation([
			'tags' => [
				'append' => false,
				'nodes' => [
					[
						'id' => $new_term_global_id
					],
				]

			],
		]);


		$this->assertArrayNotHasKey( 'errors', $results );


		$createdPost = $results['data']['createPost']['post'];
		$this->assertEquals( 'Test Title', $createdPost['title'] );
		$this->assertEquals( $new_term, $createdPost['tags']['edges'][0]['node']['tagId'] );
		$this->assertEquals( $new_term_global_id, $createdPost['tags']['edges'][0]['node']['id'] );

	}

	public function testCreatePostAndAttachTagByTagID() {

		wp_set_current_user( $this->admin );

		$new_term = $this->factory->term->create([
			'name' => 'Test Term',
			'taxonomy' => 'post_tag'
		]);

		$new_term_global_id = \GraphQLRelay\Relay::toGlobalId( 'post_tag', $new_term );

		$results = $this->createPostMutation([
			'tags' => [
				'append' => false,
				'nodes' => [
					[
						'id' => (int) $new_term
					],
				]

			],
		]);


		$this->assertArrayNotHasKey( 'errors', $results );


		$createdPost = $results['data']['createPost']['post'];
		$this->assertEquals( 'Test Title', $createdPost['title'] );
		$this->assertEquals( $new_term, $createdPost['tags']['edges'][0]['node']['tagId'] );
		$this->assertEquals( $new_term_global_id, $createdPost['tags']['edges'][0]['node']['id'] );

	}

	public function testCreatePostWithInvalidTagId() {

		wp_set_current_user( $this->admin );

		$new_term = $this->factory->term->create([
			'name' => 'Test Term',
			'taxonomy' => 'category'
		]);

		$new_term_global_id = \GraphQLRelay\Relay::toGlobalId( 'category', $new_term );

		$results = $this->createPostMutation([
			'tags' => [
				'append' => false,
				'nodes' => [
					[
						'id' => $new_term_global_id
					],
				],
			],
		]);

		$this->assertArrayNotHasKey( 'errors', $results );

		$createdPost = $results['data']['createPost']['post'];
		$this->assertEquals( 'Test Title', $createdPost['title'] );

		/**
		 * The tags edges _should_ be empty because we tried to add a tag
		 * with an invalid id.
		 */
		$this->assertEmpty( $createdPost['tags']['edges'] );


	}

	public function testCreatePostAndCreateTerms() {

		wp_set_current_user( $this->admin );

		$results = $this->createPostMutation([
			'tags' => [
				'append' => false,
				'nodes' => [
					[
						'name' => 'Test Tag',
						'slug' => 'test-tag',
						'description' => 'Test Tag Description',
					],
				],
			],
			'categories' => [
				'append' => false,
				'nodes' => [
					[
						'slug' => 'test-category',
						'description' => 'Test Category Description',
					],
				],
			],
		]);

		$this->assertArrayNotHasKey( 'errors', $results );


		$createdPost = $results['data']['createPost']['post'];
		$this->assertEquals( 'Test Title', $createdPost['title'] );

		/**
		 * The tags edges _should_ be empty because we tried to add a tag
		 * with an invalid id.
		 */
		$this->assertEquals( 'Test Tag Description', $createdPost['tags']['edges'][0]['node']['description'] );
		$this->assertEquals( 'Test Category Description', $createdPost['categories']['edges'][0]['node']['description'] );


	}

}