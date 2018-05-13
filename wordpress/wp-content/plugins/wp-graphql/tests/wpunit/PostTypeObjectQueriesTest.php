<?php

class PostTypeObjectQueriesTest extends \Codeception\TestCase\WPTestCase {

	public $current_time;
	public $current_date;
	public $current_date_gmt;
	public $admin;

	public function setUp() {
		// before
		parent::setUp();

		$this->current_time = strtotime( '- 1 day' );
		$this->current_date = date( 'Y-m-d H:i:s', $this->current_time );
		$this->current_date_gmt = gmdate( 'Y-m-d H:i:s', $this->current_time );
		$this->admin = $this->factory->user->create( [
			'role' => 'administrator',
		] );
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * testPostTypeQueryForPosts
	 *
	 * This tests post type info for posts post type.
	 *
	 * @since 0.0.5
	 */
	public function testPostTypeQueryForPosts() {
		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'postType', 'post' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			posts {
				postTypeInfo {
					canExport
					connectedTaxonomies {
						name
					}
					connectedTaxonomyNames
					deleteWithUser
					description
					excludeFromSearch
					graphqlPluralName
					graphqlSingleName
					hasArchive
					hierarchical
					id
					label
					labels {
						name
						singularName
						addNew
						addNewItem
						editItem
						newItem
						viewItem
						viewItems
						searchItems
						notFound
						notFoundInTrash
						parentItemColon
						allItems
						archives
						attributes
						insertIntoItem
						uploadedToThisItem
						featuredImage
						setFeaturedImage
						removeFeaturedImage
						useFeaturedImage
						menuName
						filterItemsList
						itemsListNavigation
						itemsList
					}
					menuIcon
					menuPosition
					name
					public
					publiclyQueryable
					restBase
					restControllerClass
					showInAdminBar
					showInGraphql
					showInMenu
					showInNavMenus
					showInRest
					showUi
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
				'posts' => [
					'postTypeInfo' => [
						'canExport' => true,
						'connectedTaxonomies' => [
							[
								'name' => 'category'
							],
							[
								'name' => 'post_tag'
							],
						],
						'connectedTaxonomyNames' => [ 'category', 'post_tag' ],
						'deleteWithUser' => true,
						'description' => '',
						'excludeFromSearch' => false,
						'graphqlPluralName' => 'posts',
						'graphqlSingleName' => 'post',
						'hasArchive' => false,
						'hierarchical' => false,
						'id' => $global_id,
						'label' => 'Posts',
						'labels' => [
							'name' => 'Posts',
							'singularName' => 'Post',
							'addNew' => 'Add New',
							'addNewItem' => 'Add New Post',
							'editItem' => 'Edit Post',
							'newItem' => 'New Post',
							'viewItem' => 'View Post',
							'viewItems' => 'View Posts',
							'searchItems' => 'Search Posts',
							'notFound' => 'No posts found.',
							'notFoundInTrash' => 'No posts found in Trash.',
							'parentItemColon' => null,
							'allItems' => 'All Posts',
							'archives' => 'Post Archives',
							'attributes' => 'Post Attributes',
							'insertIntoItem' => 'Insert into post',
							'uploadedToThisItem' => 'Uploaded to this post',
							'featuredImage' => 'Featured Image',
							'setFeaturedImage' => 'Set featured image',
							'removeFeaturedImage' => 'Remove featured image',
							'useFeaturedImage' => null,
							'menuName' => 'Posts',
							'filterItemsList' => 'Filter posts list',
							'itemsListNavigation' => 'Posts list navigation',
							'itemsList' => 'Posts list',
						],
						'menuIcon' => null,
						'menuPosition' => 5,
						'name' => 'post',
						'public' => true,
						'publiclyQueryable' => true,
						'restBase' => 'posts',
						'restControllerClass' => 'WP_REST_Posts_Controller',
						'showInAdminBar' => false,
						'showInGraphql' => true,
						'showInMenu' => true,
						'showInNavMenus' => true,
						'showInRest' => true,
						'showUi' => true,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testPostTypeQueryForPages
	 *
	 * This tests post type info for pages post type.
	 *
	 * @since 0.0.5
	 */
	public function testPostTypeQueryForPages() {
		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'postType', 'page' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			pages {
				postTypeInfo {
					canExport
					connectedTaxonomies {
						name
					}
					connectedTaxonomyNames
					deleteWithUser
					description
					excludeFromSearch
					graphqlPluralName
					graphqlSingleName
					hasArchive
					hierarchical
					id
					label
					menuIcon
					menuPosition
					name
					public
					publiclyQueryable
					restBase
					restControllerClass
					showInAdminBar
					showInGraphql
					showInMenu
					showInNavMenus
					showInRest
					showUi
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
				'pages' => [
					'postTypeInfo' => [
						'canExport' => true,
						'connectedTaxonomies' => null,
						'connectedTaxonomyNames' => null,
						'deleteWithUser' => true,
						'description' => '',
						'excludeFromSearch' => false,
						'graphqlPluralName' => 'pages',
						'graphqlSingleName' => 'page',
						'hasArchive' => false,
						'hierarchical' => true,
						'id' => $global_id,
						'label' => 'Pages',
						'menuIcon' => null,
						'menuPosition' => 20,
						'name' => 'page',
						'public' => true,
						'publiclyQueryable' => false,
						'restBase' => 'pages',
						'restControllerClass' => 'WP_REST_Posts_Controller',
						'showInAdminBar' => false,
						'showInGraphql' => true,
						'showInMenu' => true,
						'showInNavMenus' => true,
						'showInRest' => true,
						'showUi' => true,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * testPostTypeQueryForMedia
	 *
	 * This tests post type info for attachment post type.
	 *
	 * @since 0.0.5
	 */
	public function testPostTypeQueryForMedia() {
		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$global_id = \GraphQLRelay\Relay::toGlobalId( 'postType', 'attachment' );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			mediaItems {
				postTypeInfo {
					canExport
					connectedTaxonomies {
						name
					}
					connectedTaxonomyNames
					deleteWithUser
					description
					excludeFromSearch
					graphqlPluralName
					graphqlSingleName
					hasArchive
					hierarchical
					id
					label
					menuIcon
					menuPosition
					name
					public
					publiclyQueryable
					restBase
					restControllerClass
					showInAdminBar
					showInGraphql
					showInMenu
					showInNavMenus
					showInRest
					showUi
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
				'mediaItems' => [
					'postTypeInfo' => [
						'canExport' => true,
						'connectedTaxonomies' => null,
						'connectedTaxonomyNames' => null,
						'deleteWithUser' => true,
						'description' => '',
						'excludeFromSearch' => false,
						'graphqlPluralName' => 'mediaItems',
						'graphqlSingleName' => 'mediaItem',
						'hasArchive' => false,
						'hierarchical' => false,
						'id' => $global_id,
						'label' => 'Media',
						'menuIcon' => null,
						'menuPosition' => null,
						'name' => 'attachment',
						'public' => true,
						'publiclyQueryable' => true,
						'restBase' => 'media',
						'restControllerClass' => 'WP_REST_Attachments_Controller',
						'showInAdminBar' => false,
						'showInGraphql' => true,
						'showInMenu' => true,
						'showInNavMenus' => null,
						'showInRest' => true,
						'showUi' => true,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

}