<?php

class DataSourceTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * Tests retrieving a post by slug
	 */
	public function testGetPostObjectByMethod() {

		$slug = 'some-sample-slug';

		$args = [
			'post_content' => 'Some sample content',
			'post_title'   => 'Some sample post here',
			'post_name'    => $slug,
		];

		$post_id = $this->factory->post->create( $args );

		$expected = get_post( $post_id );

		$actual = \WPGraphQL\Data\DataSource::get_post_object_by_uri( $slug );

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * Tests retrieving a post by slug when two posts have the same slug in different post types
	 */
	public function testGetPostObjectByMethodForMultiplePosts() {

		$slug = 'some-other-slug';

		$args = [
			'post_content' => 'Sample duplicate content',
			'post_title'   => 'Some Duplicate Content',
			'post_name'    => $slug,
		];

		$post_id = $this->factory->post->create( $args );

		$args['post_type'] = 'page';

		$page_id = $this->factory->post->create( $args );

		$expected = get_post( $page_id );

		$actual = \WPGraphQL\Data\DataSource::get_post_object_by_uri( $slug, OBJECT, [ 'post', 'page' ] );

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * Tests retrieving a post object by slug that doesn't exist
	 */
	public function testGetPostObjectByMethodForNonExistantSlug() {

		$actual = \WPGraphQL\Data\DataSource::get_post_object_by_uri( 'non-existent-uri' );
		$this->assertEquals( null, $actual );

	}


}