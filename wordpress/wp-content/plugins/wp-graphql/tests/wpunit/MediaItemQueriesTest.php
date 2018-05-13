<?php

class MediaItemQueriesTest extends \Codeception\TestCase\WPTestCase {

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
		$this->subscriber       = $this->factory()->user->create( [
			'role' => 'subscriber',
		] );

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
	 * Data provider for testMediaItemQuery.
	 */
	public function provideImageMeta() {
		return [
			[
				[],
			],
			[
				[
					'caption' => '',
				],
			],
		];
	}

	/**
	 * testPostQuery
	 *
	 * This tests creating a single post with data and retrieving said post via a GraphQL query
	 *
	 * @dataProvider provideImageMeta
	 * @param array $image_meta Image meta to merge into defaults.
	 * @since 0.0.5
	 */
	public function testMediaItemQuery( $image_meta = [] ) {

		/**
		 * Create a post to set as the attachment's parent
		 */
		$post_id = $this->createPostObject( [
			'post_type' => 'post',
		] );

		/**
		 * Create an attachment with a post set as it's parent
		 */
		$image_description = 'some description';
		$attachment_id = $this->createPostObject( [
			'post_type'   => 'attachment',
			'post_parent' => $post_id,
			'post_content' => $image_description,
		] );

		$default_image_meta = [
			'aperture' => 0,
			'credit' => 'some photographer',
			'camera' => 'some camera',
			'caption' => 'some caption',
			'created_timestamp' => strtotime( $this->current_date ),
			'copyright' => 'Copyright WPGraphQL',
			'focal_length' => 0,
			'iso' => 0,
			'shutter_speed' => 0,
			'title' => 'some title',
			'orientation' => 'some orientation',
			'keywords' => [
				'keyword1',
				'keyword2',
			],
		];

		$meta_data = [
			'width' => 300,
			'height' => 300,
			'file' => 'example.jpg',
			'sizes' => [
				'thumbnail' => [
					'file' => 'example-thumbnail.jpg',
					'width' => 150,
					'height' => 150,
					'mime-type' => 'image/jpeg',
					'source_url' => 'example-thumbnail.jpg',
				],
				'full' => [
					'file' => 'example-full.jpg',
					'width' => 1500,
					'height' => 1500,
					'mime-type' => 'image/jpeg',
					'source_url' => 'example-full.jpg',
				],
			],
			'image_meta' => array_merge( $default_image_meta, $image_meta ),
		];

		update_post_meta( $attachment_id, '_wp_attachment_metadata', $meta_data );

		/**
		 * Create the global ID based on the post_type and the created $id
		 */
		$attachment_global_id = \GraphQLRelay\Relay::toGlobalId( 'attachment', $attachment_id );
		$post_global_id = \GraphQLRelay\Relay::toGlobalId( 'post', $post_id );

		/**
		 * Create the query string to pass to the $query
		 */
		$query = "
		query {
			mediaItem(id: \"{$attachment_global_id}\") {
				altText
				author{
				  id
				}
				caption
				commentCount
				commentStatus
				comments{
				  edges{
				    node{
				      id
				    }
				  }
				}
				content
				date
				dateGmt
				description
				desiredSlug
				editLast{
				  userId
				}
				editLock{
				  editTime
				}
				enclosure
				excerpt
				guid
				id
				link
				mediaDetails{
				  file
				  height
				  meta{
				    aperture
				    credit
				    camera
				    caption
				    createdTimestamp
				    copyright
				    focalLength
				    iso
				    shutterSpeed
				    title
				    orientation
				    keywords
				  }
				  sizes{
				    name
				    file
				    width
				    height
				    mimeType
				    sourceUrl
				  }
				  width
				}
				mediaItemId
				mediaType
				menuOrder
				mimeType
				modified
				modifiedGmt
				parent{
				  ...on Post{
				    id
				  }
				}
				pingStatus
				slug
				sourceUrl
				status
				title
				toPing
			}
		}";

		/**
		 * Run the GraphQL query
		 */
		$actual = do_graphql_request( $query );

		$mediaItem = $actual['data']['mediaItem'];

		$this->assertNotEmpty( $mediaItem );

		$this->assertTrue( ( null === $mediaItem['altText'] || is_string( $mediaItem['altText'] ) ) );
		$this->assertTrue( ( null === $mediaItem['author'] || is_string( $mediaItem['author']['id'] ) ) );
		$this->assertTrue( ( null === $mediaItem['caption'] || is_string( $mediaItem['caption'] ) ) );
		$this->assertTrue( ( null === $mediaItem['commentCount'] || is_int( $mediaItem['commentCount'] ) ) );
		$this->assertTrue( ( null === $mediaItem['commentStatus'] || is_string( $mediaItem['commentStatus'] ) ) );
		$this->assertTrue( ( empty( $mediaItem['comments']['edges'] ) || is_string( $mediaItem['comments']['edges'] ) ) );
		$this->assertTrue( ( null === $mediaItem['content'] || is_string( $mediaItem['content'] ) ) );
		$this->assertTrue( ( null === $mediaItem['date'] || is_string( $mediaItem['date'] ) ) );
		$this->assertTrue( ( null === $mediaItem['dateGmt'] || is_string( $mediaItem['dateGmt'] ) ) );
		$this->assertTrue( ( null === $mediaItem['description'] || is_string( $mediaItem['description'] ) ) );
		$this->assertTrue( ( null === $mediaItem['desiredSlug'] || is_string( $mediaItem['desiredSlug'] ) ) );
		$this->assertTrue( ( empty( $mediaItem['editLast'] ) || is_integer( $mediaItem['editLast']['userId'] ) ) );
		$this->assertTrue( ( empty( $mediaItem['editLock'] ) || is_string( $mediaItem['editLock']['editTime'] ) ) );
		$this->assertTrue( ( null === $mediaItem['enclosure'] || is_string( $mediaItem['enclosure'] ) ) );
		$this->assertTrue( ( null === $mediaItem['excerpt'] || is_string( $mediaItem['excerpt'] ) ) );
		$this->assertTrue( ( null === $mediaItem['guid'] || is_string( $mediaItem['guid'] ) ) );
		$this->assertEquals( $attachment_global_id, $mediaItem['id'] );
		$this->assertEquals( $attachment_id, $mediaItem['mediaItemId'] );
		$this->assertTrue( ( null === $mediaItem['mediaType'] || is_string( $mediaItem['mediaType'] ) ) );
		$this->assertTrue( ( null === $mediaItem['menuOrder'] || is_integer( $mediaItem['menuOrder'] ) ) );
		$this->assertTrue( ( null === $mediaItem['mimeType'] || is_string( $mediaItem['mimeType'] ) ) );
		$this->assertTrue( ( null === $mediaItem['modified'] || is_string( $mediaItem['modified'] ) ) );
		$this->assertTrue( ( null === $mediaItem['modifiedGmt'] || is_string( $mediaItem['modifiedGmt'] ) ) );
		$this->assertTrue( ( null === $mediaItem['pingStatus'] || is_string( $mediaItem['pingStatus'] ) ) );
		$this->assertTrue( ( empty( $mediaItem['pinged'] ) || is_array( $mediaItem['pinged'] ) ) );
		$this->assertTrue( ( null === $mediaItem['slug'] || is_string( $mediaItem['slug'] ) ) );
		$this->assertTrue( ( null === $mediaItem['sourceUrl'] || is_string( $mediaItem['sourceUrl'] ) ) );
		$this->assertTrue( ( null === $mediaItem['status'] || is_string( $mediaItem['status'] ) ) );
		$this->assertTrue( ( null === $mediaItem['title'] || is_string( $mediaItem['title'] ) ) );
		$this->assertTrue( ( empty( $mediaItem['toPing'] ) || is_array( $mediaItem['toPing'] ) ) );

		$this->assertEquals(
			[
				'id' => $post_global_id,
			],
			$mediaItem['parent']
		);

		$this->assertNotEmpty( $mediaItem['description'] );
		$this->assertEquals( apply_filters( 'the_content', $image_description ), $mediaItem['description'] );

		$this->assertNotEmpty( $mediaItem['mediaDetails'] );
		$mediaDetails = $mediaItem['mediaDetails'];
		$this->assertEquals( $meta_data['file'], $mediaDetails['file'] );
		$this->assertEquals( $meta_data['height'], $mediaDetails['height'] );
		$this->assertEquals( $meta_data['width'], $mediaDetails['width'] );

		$this->assertNotEmpty( $mediaDetails['meta'] );
		$meta = $mediaDetails['meta'];

		$this->assertEquals( $meta_data['image_meta']['aperture'],  $meta['aperture'] );
		$this->assertEquals( $meta_data['image_meta']['credit'],  $meta['credit'] );
		$this->assertEquals( $meta_data['image_meta']['camera'],  $meta['camera'] );
		$this->assertEquals( $meta_data['image_meta']['caption'],  $meta['caption'] );
		$this->assertEquals( $meta_data['image_meta']['created_timestamp'],  $meta['createdTimestamp'] );
		$this->assertEquals( $meta_data['image_meta']['copyright'],  $meta['copyright'] );
		$this->assertEquals( $meta_data['image_meta']['focal_length'],  $meta['focalLength'] );
		$this->assertEquals( $meta_data['image_meta']['iso'],  $meta['iso'] );
		$this->assertEquals( $meta_data['image_meta']['shutter_speed'],  $meta['shutterSpeed'] );
		$this->assertEquals( $meta_data['image_meta']['title'],  $meta['title'] );
		$this->assertEquals( $meta_data['image_meta']['orientation'],  $meta['orientation'] );

		$this->assertNotEmpty( $meta_data['image_meta']['keywords'] );
		$keywords = $meta_data['image_meta']['keywords'];
		$this->assertEquals( 'keyword1', $keywords[0] );
		$this->assertEquals( 'keyword2', $keywords[1] );

		$this->assertNotEmpty( $meta_data['sizes'] );
		$sizes = $mediaDetails['sizes'];
		$this->assertEquals( 'thumbnail', $sizes[0]['name'] );
		$this->assertEquals( 'example-thumbnail.jpg', $sizes[0]['file'] );
		$this->assertEquals( 150, $sizes[0]['height'] );
		$this->assertEquals( 150, $sizes[0]['width'] );
		$this->assertEquals( 'image/jpeg', $sizes[0]['mimeType'] );
		$this->assertEquals( 'example-thumbnail.jpg', $sizes[0]['sourceUrl'] );

	}

}