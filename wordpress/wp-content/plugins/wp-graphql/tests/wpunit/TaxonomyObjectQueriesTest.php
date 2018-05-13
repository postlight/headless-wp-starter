<?php

class TaxonomyObjectQueriesTest extends \Codeception\TestCase\WPTestCase {

	public $admin;

	public function setUp() {
		parent::setUp();

		$this->admin = $this->factory->user->create( [
			'role' => 'administrator',
		] );
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * testTaxonomyQueryForCategories
	 *
	 * This tests the category taxonomy.
	 *
	 * @since 0.0.5
	 */
	public function testTaxonomyQueryForCategories() {
		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			categories {
				taxonomyInfo {
					connectedPostTypeNames
					connectedPostTypes {
						name
					}
					description
					graphqlPluralName
					graphqlSingleName
					hierarchical
					id
					label
					name
					public
					restBase
					restControllerClass
					showCloud
					showInAdminColumn
					showInGraphql
					showInMenu
					showInNavMenus
					showInQuickEdit
					showInRest
					showUi
				}
			}
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query );

		$global_id = \GraphQLRelay\Relay::toGlobalId( 'taxonomy', 'category' );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'categories' => [
					'taxonomyInfo' => [
						'connectedPostTypeNames' => [ 'post' ],
						'connectedPostTypes'     => [ [ 'name' => 'post' ] ],
						'description'            => '',
						'graphqlPluralName'      => 'categories',
						'graphqlSingleName'      => 'category',
						'hierarchical'           => true,
						'id'                     => $global_id,
						'label'                  => 'Categories',
						'name'                   => 'category',
						'public'                 => true,
						'restBase'               => 'categories',
						'restControllerClass'    => 'WP_REST_Terms_Controller',
						'showCloud'              => true,
						'showInAdminColumn'      => true,
						'showInGraphql'          => true,
						'showInMenu'             => true,
						'showInNavMenus'         => true,
						'showInQuickEdit'        => true,
						'showInRest'             => true,
						'showUi'                 => true,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testTaxonomyQueryForTags
	 *
	 * This tests the post tags taxonomy.
	 *
	 * @since 0.0.5
	 */
	public function testTaxonomyQueryForTags() {
		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			tags {
				taxonomyInfo {
					connectedPostTypeNames
					connectedPostTypes {
						name
					}
					description
					graphqlPluralName
					graphqlSingleName
					hierarchical
					id
					label
					name
					public
					restBase
					restControllerClass
					showCloud
					showInAdminColumn
					showInGraphql
					showInMenu
					showInNavMenus
					showInQuickEdit
					showInRest
					showUi
				}
			}
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query );

		$global_id = \GraphQLRelay\Relay::toGlobalId( 'taxonomy', 'post_tag' );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'tags' => [
					'taxonomyInfo' => [
						'connectedPostTypeNames' => [ 'post' ],
						'connectedPostTypes'     => [ [ 'name' => 'post' ] ],
						'description'            => '',
						'graphqlPluralName'      => 'tags',
						'graphqlSingleName'      => 'tag',
						'hierarchical'           => false,
						'id'                     => $global_id,
						'label'                  => 'Tags',
						'name'                   => 'post_tag',
						'public'                 => true,
						'restBase'               => 'tags',
						'restControllerClass'    => 'WP_REST_Terms_Controller',
						'showCloud'              => true,
						'showInAdminColumn'      => true,
						'showInGraphql'          => true,
						'showInMenu'             => true,
						'showInNavMenus'         => true,
						'showInQuickEdit'        => true,
						'showInRest'             => true,
						'showUi'                 => true,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testTaxonomyQueryCategoryConnections.
	 *
	 * This tests the category taxonomy post object connections.
	 *
	 * @since 0.0.5
	 */
	public function testTaxonomyQueryCategoryConnections() {
		$post_id       = $this->factory->post->create();
		$page_id       = $this->factory->post->create( [ 'post_type' => 'page' ] );
		$attachment_id = $this->factory->post->create( [ 'post_type' => 'attachment' ] );

		$category_id = $this->factory->term->create( [ 'name' => 'Test' ] );

		wp_set_object_terms( $post_id, $category_id, 'category' );
		wp_set_object_terms( $page_id, $category_id, 'category' );
		wp_set_object_terms( $attachment_id, $category_id, 'category' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			categories {
				taxonomyInfo {
					name
				}
			}
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query );

		$global_id = \GraphQLRelay\Relay::toGlobalId( 'taxonomy', 'category' );

		/**
		 * Establish the expectation for the output of the query
		 */
		$expected = [
			'data' => [
				'categories' => [
					'taxonomyInfo' => [
						'name' => 'category',
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testTaxonomyQueryTagsConnections.
	 *
	 * This tests the tags taxonomy post object connections.
	 *
	 * @since 0.0.5
	 */
	public function testTaxonomyQueryTagsConnections() {
		$post_id = $this->factory->post->create();

		$post_tag_id = $this->factory->term->create( [ 'name' => 'Test' ] );

		wp_set_object_terms( $post_id, $post_tag_id, 'post_tag' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			tags {
				taxonomyInfo {
					name
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
				'tags' => [
					'taxonomyInfo' => [
						'name' => 'post_tag',
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

}