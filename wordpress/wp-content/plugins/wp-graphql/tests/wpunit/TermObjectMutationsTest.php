<?php

class TermObjectMutationsTest extends \Codeception\TestCase\WPTestCase
{

	public $category_name;
	public $tag_name;
	public $description;
	public $description_update;
	public $client_mutation_id;
	public $admin;
	public $subscriber;

    public function setUp()
    {
        parent::setUp();

	    $this->category_name = 'Test Category';
	    $this->tag_name = 'Test Tag';
	    $this->description = 'Test Term Description';
	    $this->description_update = 'Description Update';
	    $this->client_mutation_id = 'someUniqueId';

	    $this->admin = $this->factory()->user->create([
		    'role' => 'administrator',
	    ]);

	    $this->subscriber = $this->factory()->user->create([
		    'role' => 'subscriber',
	    ]);
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

	/**
	 * Function that executes the mutation
	 */
	public function createCategoryMutation() {

		$mutation = '
		mutation createCategory( $clientMutationId:String!, $name:String!, $description:String ) {
		  createCategory(
			input: {
			  clientMutationId: $clientMutationId
			  name: $name
			  description: $description
			}
		  ) {
			clientMutationId
			category {
			  id
			  name
			  description
			}
		  }
		}
		';

		$variables = wp_json_encode([
			'clientMutationId' => $this->client_mutation_id,
			'name' => $this->category_name,
			'description' => $this->description,
		]);

		return do_graphql_request( $mutation, 'createCategory', $variables );

	}

	/**
	 * Function that executes the mutation
	 */
	public function createTagMutation() {

		$mutation = '
		mutation createTag( $clientMutationId:String!, $name:String!, $description:String ) {
		  createTag(
		    input: {
			  clientMutationId: $clientMutationId
			    name: $name
				description: $description
			}
		  ) {
			clientMutationId
			tag {
			  id
			  name
			  description
			}
		  }
		}
		';

		$variables = wp_json_encode([
			'clientMutationId' => $this->client_mutation_id,
			'name' => $this->tag_name,
			'description' => $this->description,
		]);

		return do_graphql_request( $mutation, 'createTag', $variables );

	}

	/**
	 * Update Tag
	 *
	 * @param string $id The ID of the term to update
	 * @return array
	 */
	public function updateTagMutation( $id ) {

		$mutation = '
		mutation updateTag( $clientMutationId:String!, $id:ID! $description:String ) {
		  updateTag(
		    input: {
			  clientMutationId: $clientMutationId
			  description: $description
			  id: $id
			}
		  ) {
			clientMutationId
			tag {
			  id
			  name
			  description
			}
		  }
		}
		';

		$variables = wp_json_encode([
			'clientMutationId' => $this->client_mutation_id,
			'id' => $id,
			'description' => $this->description_update,
		]);

		return do_graphql_request( $mutation, 'updateTag', $variables );

	}

	public function deleteTagMutation( $id ) {

		$mutation = '
		mutation deleteTag( $clientMutationId:String!, $id:ID! ) {
		  deleteTag(
		    input: {
			  id: $id
			  clientMutationId: $clientMutationId
		    }
		  ) {
		    clientMutationId
		    deletedId
		    tag {
		        id
		        name
		    }
		  }
		}
		';

		$variables = wp_json_encode([
			'id' => $id,
			'clientMutationId' => $this->client_mutation_id,
		]);

		return do_graphql_request( $mutation, 'deleteTag', $variables );

	}

	/**
	 * Update Category
	 *
	 * @param string $id The ID of the term to update
	 * @return array
	 */
	public function updateCategoryMutation( $id ) {

		$mutation = '
		mutation updateCategory( $clientMutationId:String!, $id:ID! $description:String ) {
		  updateCategory(
		    input: {
			  clientMutationId: $clientMutationId
			  description: $description
			  id: $id
			}
		  ) {
			clientMutationId
			category {
			  id
			  name
			  description
			}
		  }
		}
		';

		$variables = wp_json_encode([
			'clientMutationId' => $this->client_mutation_id,
			'id' => $id,
			'description' => $this->description_update,
		]);

		return do_graphql_request( $mutation, 'updateCategory', $variables );

	}

	public function deleteCategoryMutation( $id ) {

		$mutation = '
		mutation deleteCategory( $clientMutationId:String!, $id:ID! ) {
		  deleteCategory(
		    input: {
			  id: $id
			  clientMutationId: $clientMutationId
		    }
		  ) {
		    clientMutationId
		    category {
		        id
		        name
		    }
		  }
		}
		';

		$variables = wp_json_encode([
			'id' => $id,
			'clientMutationId' => $this->client_mutation_id,
		]);

		return do_graphql_request( $mutation, 'deleteCategory', $variables );

	}


	/**
	 * Test creating a category
	 */
	public function testCategoryMutations() {

		/**
		 * Set the current user as a subscriber, who doesn't have permission
		 * to create terms
		 */
		wp_set_current_user( $this->admin );

		/**
		 * Run the mutation
		 */
		$actual = $this->createCategoryMutation();

		/**
		 * Assert that the created tag is what it should be
		 */
		$this->assertEquals( $actual['data']['createCategory']['clientMutationId'], $this->client_mutation_id );
		$this->assertNotEmpty( $actual['data']['createCategory']['category']['id'] );
		$id_parts = \GraphQLRelay\Relay::fromGlobalId( $actual['data']['createCategory']['category']['id'] );
		$this->assertEquals( $id_parts['type'], 'category' );
		$this->assertNotEmpty( $id_parts['id'] );
		$this->assertEquals( $actual['data']['createCategory']['category']['name'], $this->category_name );
		$this->assertEquals( $actual['data']['createCategory']['category']['description'], $this->description );

		/**
		 * Try to update a Tag using a category ID, which should return errors
		 */
		$try_to_update_tag = $this->updateTagMutation( $actual['data']['createCategory']['category']['id'] );
		$this->assertNotEmpty( $try_to_update_tag['errors'] );

		/**
		 * Try to update a Tag using a category ID, which should return errors
		 */
		$try_to_delete_tag = $this->deleteTagMutation( $actual['data']['createCategory']['category']['id'] );
		$this->assertNotEmpty( $try_to_delete_tag['errors'] );

		/**
		 * Run the update mutation with the ID of the created tag
		 */
		$updated_category = $this->updateCategoryMutation( $actual['data']['createCategory']['category']['id'] );

		/**
		 * Make some assertions on the response
		 */
		$this->assertEquals( $updated_category['data']['updateCategory']['clientMutationId'], $this->client_mutation_id );
		$this->assertNotEmpty( $updated_category['data']['updateCategory']['category']['id'] );
		$id_parts = \GraphQLRelay\Relay::fromGlobalId( $updated_category['data']['updateCategory']['category']['id'] );
		$this->assertEquals( $id_parts['type'], 'category' );
		$this->assertNotEmpty( $id_parts['id'] );
		$this->assertEquals( $updated_category['data']['updateCategory']['category']['name'], $this->category_name );
		$this->assertEquals( $updated_category['data']['updateCategory']['category']['description'], $this->description_update );

		/**
		 * Delete the tag
		 */
		wp_set_current_user( $this->subscriber );
		$deleted_category = $this->deleteCategoryMutation( $updated_category['data']['updateCategory']['category']['id'] );

		/**
		 * A subscriber shouldn't be able to delete, so we should get an error
		 */
		$this->assertArrayHasKey( 'errors', $deleted_category );

		/**
		 * Set the user back to admin and delete again
		 */
		wp_set_current_user( $this->admin );
		$deleted_category = $this->deleteCategoryMutation( $updated_category['data']['updateCategory']['category']['id'] );

		/**
		 * Make some assertions on the response
		 */
		$this->assertNotEmpty( $deleted_category );
		$this->assertEquals( $deleted_category['data']['deleteCategory']['clientMutationId'], $this->client_mutation_id );
		$id_parts = \GraphQLRelay\Relay::fromGlobalId( $deleted_category['data']['deleteCategory']['category']['id'] );
		$this->assertEquals( $id_parts['type'], 'category' );
		$this->assertNotEmpty( $id_parts['id'] );
		$this->assertEquals( $deleted_category['data']['deleteCategory']['category']['name'], $this->category_name );
	}

	public function testCreateTagThatAlreadyExists() {

		/**
		 * Set the user as the admin
		 */
		wp_set_current_user( $this->admin );

		/**
		 * Run the mutation
		 */
		$actual1 = $this->createCategoryMutation();
		$actual2 = $this->createCategoryMutation();

		/**
		 * Create the term
		 */
		$this->assertNotEmpty( $actual1 );

		/**
		 * Make sure there were no errors
		 */
		$this->assertArrayNotHasKey( 'errors', $actual1 );
		$this->assertArrayHasKey( 'data', $actual1 );

		/**
		 * Try to create the exact same term
		 */
		$this->assertNotEmpty( $actual2 );

		/**
		 * Now we should expect an error
		 */
		$this->assertArrayHasKey( 'errors', $actual2 );
		$this->assertArrayHasKey( 'data', $actual2 );

	}

	public function testTermIdNotReturningAfterCreate() {

		/**
		 * Filter the term response to simulate a failure with the response of a term creation mutation
		 */
		add_filter( 'term_id_filter', '__return_false' );

		/**
		 * Set the user as the admin
		 */
		wp_set_current_user( $this->admin );

		/**
		 * Run the mutation
		 */
		$actual = $this->createCategoryMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$actual = $this->deleteTagMutation( 'someInvalidId' );

		/**
		 * We should get an error because the ID is invalid
		 */
		$this->assertArrayHasKey( 'errors', $actual );

		/**
		 * Cleanup by removing the filter
		 */
		remove_filter( 'term_id_filter', '__return_false' );

		/**
		 * Now let's filter to mimick the response returning a WP_Error to make sure we also respond with an error
		 */
		add_filter( 'get_post_tag', function() {
			return new \WP_Error( 'this is a test error' );
		} );

		/**
		 * Create a term
		 */
		$term = $this->factory()->term->create([
			'taxonomy' => 'post_tag',
			'name' => 'some random name',
		]);

		/**
		 * Now try and delete it.
		 */
		$id = \GraphQLRelay\Relay::toGlobalId( 'post_tag', $term );
		$actual = $this->deleteTagMutation( $id );

		/**
		 * Assert that we have an error because the response to the deletion responded with a WP_Error
		 */
		$this->assertArrayHasKey( 'errors', $actual );


	}

	public function testCreateTagWithNoName() {

		$mutation = '
		mutation createTag( $clientMutationId:String!, $name:String!, $description:String ) {
		  createTag(
		    input: {
			  clientMutationId: $clientMutationId
			    name: $name
				description: $description
			}
		  ) {
			clientMutationId
			tag {
			  id
			  name
			  description
			}
		  }
		}
		';

		$variables = wp_json_encode([
			'clientMutationId' => $this->client_mutation_id,
			'description' => $this->description,
		]);

		$actual = do_graphql_request( $mutation, 'createTag', $variables );

		$this->assertNotEmpty( $actual );
		$this->assertArrayHasKey( 'errors', $actual );

	}

	/**
	 * Test creating a tag
	 */
	public function testTagMutations() {

		/**
		 * Set the current user as a subscriber, who doesn't have permission
		 * to create terms
		 */
		wp_set_current_user( $this->admin );

		/**
		 * Run the mutation
		 */
		$actual = $this->createTagMutation();

		/**
		 * Assert that the created tag is what it should be
		 */
		$this->assertEquals( $actual['data']['createTag']['clientMutationId'], $this->client_mutation_id );
		$this->assertNotEmpty( $actual['data']['createTag']['tag']['id'] );
		$id_parts = \GraphQLRelay\Relay::fromGlobalId( $actual['data']['createTag']['tag']['id'] );
		$this->assertEquals( $id_parts['type'], 'post_tag' );
		$this->assertNotEmpty( $id_parts['id'] );
		$this->assertEquals( $actual['data']['createTag']['tag']['name'], $this->tag_name );
		$this->assertEquals( $actual['data']['createTag']['tag']['description'], $this->description );

		/**
		 * Try to update a Cagegory using a Tag ID, which should return errors
		 */
		$try_to_update_category = $this->updateCategoryMutation( $actual['data']['createTag']['tag']['id'] );
		$this->assertNotEmpty( $try_to_update_category['errors'] );

		/**
		 * Try to update the tag as a user with improper permissions
		 */
		wp_set_current_user( $this->subscriber );
		$try_to_delete_category = $this->updateTagMutation( $actual['data']['createTag']['tag']['id'] );
		$this->assertNotEmpty( $try_to_delete_category['errors'] );
		wp_set_current_user( $this->admin );

		/**
		 * Try to update a Category using a Tag ID, which should return errors
		 */
		$try_to_delete_category = $this->updateCategoryMutation( $actual['data']['createTag']['tag']['id'] );
		$this->assertNotEmpty( $try_to_delete_category['errors'] );



		/**
		 * Run the update mutation with the ID of the created tag
		 */
		$updated_tag = $this->updateTagMutation( $actual['data']['createTag']['tag']['id'] );

		/**
		 * Make some assertions on the response
		 */
		$this->assertEquals( $updated_tag['data']['updateTag']['clientMutationId'], $this->client_mutation_id );
		$this->assertNotEmpty( $updated_tag['data']['updateTag']['tag']['id'] );
		$id_parts = \GraphQLRelay\Relay::fromGlobalId( $updated_tag['data']['updateTag']['tag']['id'] );
		$this->assertEquals( $id_parts['type'], 'post_tag' );
		$this->assertNotEmpty( $id_parts['id'] );
		$this->assertEquals( $updated_tag['data']['updateTag']['tag']['name'], $this->tag_name );
		$this->assertEquals( $updated_tag['data']['updateTag']['tag']['description'], $this->description_update );

		/**
		 * Delete the tag
		 */
		$deleted_tag = $this->deleteTagMutation( $updated_tag['data']['updateTag']['tag']['id'] );

		/**
		 * Make some assertions on the response
		 */
		$this->assertNotEmpty( $deleted_tag );
		$this->assertEquals( $deleted_tag['data']['deleteTag']['clientMutationId'], $this->client_mutation_id );
		$this->assertNotEmpty( $deleted_tag['data']['deleteTag']['deletedId'] );
		$id_parts = \GraphQLRelay\Relay::fromGlobalId( $deleted_tag['data']['deleteTag']['tag']['id'] );
		$this->assertEquals( $id_parts['type'], 'post_tag' );
		$this->assertNotEmpty( $id_parts['id'] );
		$this->assertEquals( $deleted_tag['data']['deleteTag']['tag']['name'], $this->tag_name );

	}

	/**
	 * Test creating a tag without proper capabilitites
	 */
	public function testCreateTagWithoutProperCapabilities() {

		/**
		 * Set the current user as a subscriber, who deosn't have permission
		 * to create terms
		 */
		wp_set_current_user( $this->subscriber );

		/**
		 * Run the mutation
		 */
		$actual = $this->createTagMutation();

		/**
		 * We're asserting that this will properly return an error
		 * because this user doesn't have permissions to create a term as a
		 * subscriber
		 */
		$this->assertNotEmpty( $actual['errors'] );

	}

	/**
	 * Test creating a category without proper capabilitites
	 */
	public function testCreateCategoryWithoutProperCapabilities() {

		/**
		 * Set the current user as a subscriber, who deosn't have permission
		 * to create terms
		 */
		wp_set_current_user( $this->subscriber );

		/**
		 * Run the mutation
		 */
		$actual = $this->createCategoryMutation();

		/**
		 * We're asserting that this will properly return an error
		 * because this user doesn't have permissions to create a term as a
		 * subscriber
		 */
		$this->assertNotEmpty( $actual['errors'] );

	}

	public function testUpdateCategoryParent() {

		wp_set_current_user( $this->admin );

		$parent_term_id = $this->factory()->term->create([
			'taxonomy' => 'category',
			'name' => 'Parent Category',
		]);

		$query = '
		mutation createChildCategory($input: CreateCategoryInput!) {
		  createCategory(input: $input) {
		    category {
		      parent{
		        id
		      }
		    }
		  }
		}
		';

		$parent_id = \GraphQLRelay\Relay::toGlobalId( 'category', $parent_term_id );

		$variables = [
			'input' => [
				'clientMutationId' => 'someId',
				'name' => 'Child Category',
				'parentId' => $parent_id,
			],
		];

		$actual = do_graphql_request( $query, 'createChildCategory', $variables );

		$this->assertArrayNotHasKey( 'errors', $actual );
		$this->assertEquals( $parent_id, $actual['data']['createCategory']['category']['parent']['id'] );

	}


}