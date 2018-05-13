<?php

class MediaItemMutationsTest extends \Codeception\TestCase\WPTestCase
{

	public $altText;
	public $authorId;
	public $caption;
	public $commentStatus;
    public $current_date_gmt;
	public $date;
	public $dateGmt;
	public $description;
	public $filePath;
	public $fileType;
	public $slug;
	public $status;
	public $title;
	public $pingStatus;
	public $parentId;
	public $clientMutationId;
	public $updated_title;
	public $updated_description;
	public $updated_altText;
	public $updated_caption;
	public $updated_commentStatus;
	public $updated_date;
	public $updated_dateGmt;
	public $updated_slug;
	public $updated_status;
	public $updated_pingStatus;
	public $updated_clientMutationId;

	public $create_variables;
	public $update_variables;
	public $delete_variables;

	public $subscriber;
	public $subscriber_name;
	public $author;
	public $author_name;
	public $admin;
	public $admin_name;

	public $attachment_id;
	public $media_item_id;

    public function setUp()
    {
        // before
        parent::setUp();

        // We don't want this funking with our tests
	    remove_image_size( 'twentyseventeen-thumbnail-avatar' );

	    /**
	     * Set up different user roles for permissions testing
	     */
	    $this->subscriber = $this->factory()->user->create( [
		    'role' => 'subscriber',
	    ] );
	    $this->subscriber_name = 'User ' . $this->subscriber;

	    $this->author = $this->factory()->user->create( [
		    'role' => 'author',
	    ] );
	    $this->author_name = 'User ' . $this->author;

	    $this->admin = $this->factory()->user->create( [
		    'role' => 'administrator',
	    ] );
	    $this->admin_name = 'User ' . $this->admin;

	    /**
	     * Populate the mediaItem input fields
	     */
	    $this->altText          = 'A gif of Shia doing Magic.';
	    $this->authorId         = \GraphQLRelay\Relay::toGlobalId( 'user', $this->admin );
	    $this->caption          = 'Shia shows off some magic in this caption.';
	    $this->commentStatus    = 'closed';
	    $this->date             = '2017-08-01 15:00:00';
	    $this->dateGmt          = '2017-08-01T21:00:00';
	    $this->description      = 'This is a magic description.';
	    $this->filePath         = 'http://www.reactiongifs.com/r/mgc.gif';
	    $this->fileType         = 'IMAGE_GIF';
	    $this->slug             = 'magic-shia';
	    $this->status           = 'INHERIT';
	    $this->title            = 'Magic Shia Gif';
	    $this->pingStatus       = 'closed';
	    $this->parentId         = null;
	    $this->clientMutationId = 'someUniqueId';

	    /**
	     * Set up the updateMediaItem variables
	     */
	    $this->updated_title = 'Updated Magic Shia Gif';
	    $this->updated_description = 'This is an updated magic description.';
	    $this->updated_altText = 'Some updated alt text';
	    $this->updated_caption = 'Shia shows off some magic in this updated caption.';
	    $this->updated_commentStatus = 'open';
	    $this->updated_date = '2017-08-01 16:00:00';
	    $this->updated_dateGmt = '2017-08-01T22:00:00';
	    $this->updated_slug = 'updated-shia-magic';
	    $this->updated_status = 'INHERIT';
	    $this->updated_pingStatus = 'open';
	    $this->updated_clientMutationId = 'someUpdatedUniqueId';

	    /**
	     * Create a mediaItem to update and store it's WordPress post ID
	     * and it's WPGraphQL ID for using in our updateMediaItem mutation
	     */
	    $this->attachment_id = $this->factory()->attachment->create( ['post_mime_type' => 'image/gif', 'post_author' => $this->admin] );
	    $this->media_item_id = \GraphQLRelay\Relay::toGlobalId( 'attachment', $this->attachment_id );

	    /**
	     * Set the createMediaItem mutation input variables
	     */
	    $this->create_variables = [
		    'input' => [
			    'filePath'         => $this->filePath,
			    'fileType'         => $this->fileType,
			    'clientMutationId' => $this->clientMutationId,
			    'title'            => $this->title,
			    'description'      => $this->description,
			    'altText'          => $this->altText,
			    'parentId'         => $this->parentId,
			    'caption'          => $this->caption,
			    'commentStatus'    => $this->commentStatus,
			    'date'             => $this->date,
			    'dateGmt'          => $this->dateGmt,
			    'slug'             => $this->slug,
			    'status'           => $this->status,
			    'pingStatus'       => $this->pingStatus,
			    'authorId'         => $this->authorId,
		    ],
	    ];

	    /**
	     * Set the updateMediaItem mutation input variables
	     */
	    $this->update_variables = [
		    'input' => [
			    'id'               => $this->media_item_id,
			    'clientMutationId' => $this->updated_clientMutationId,
			    'title'            => $this->updated_title,
			    'description'      => $this->updated_description,
			    'altText'          => $this->updated_altText,
			    'caption'          => $this->updated_caption,
			    'commentStatus'    => $this->updated_commentStatus,
			    'date'             => $this->updated_date,
			    'dateGmt'          => $this->updated_dateGmt,
			    'slug'             => $this->updated_slug,
			    'status'           => $this->updated_status,
			    'pingStatus'       => $this->updated_pingStatus,
			    'fileType'         => $this->fileType,
		    ]
	    ];

	    /**
	     * Set the deleteMediaItem input variables
	     */
	    $this->delete_variables = [
		    'input' => [
			    'id'               => $this->media_item_id,
			    'clientMutationId' => $this->clientMutationId,
			    'forceDelete'      => true,
		    ]
	    ];
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

	/**
	 * This function tests the createMediaItem mutation
	 * and is reused throughout the createMediaItem tests
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemCreate.php
	 * @access public
	 * @return array $actual
	 */
	public function createMediaItemMutation() {

		/**
		 * Set up the createMediaItem mutation
		 */
		$mutation = '
			mutation createMediaItem( $input: CreateMediaItemInput! ){
			  createMediaItem(input: $input){
			    clientMutationId
			    mediaItem{
			      id
			      mediaItemId
			      mediaType
			      date
			      dateGmt
			      slug
			      status
			      title
			      commentStatus
			      pingStatus
			      altText
			      caption
			      description
			      mimeType
			      parent {
			        ... on Post {
			          id
			        }
			      }
			      sourceUrl
			      mediaDetails {
			          file
			          height
			          meta {
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
			          }
			          width
			          sizes {
			            name
			            file
			            width
			            height
			            mimeType
			            sourceUrl
			          }
			        }
			    }
			  }
			}
		';

		$actual = do_graphql_request( $mutation, 'createMediaItem', $this->create_variables );

		return $actual;
	}

	/**
	 * Set the current user to subscriber (someone who can't create posts)
	 * and test whether they can create posts
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemCreate.php:54
	 * @access public
	 * @return void
	 */
	public function testCreateMediaItemAsSubscriber() {
		wp_set_current_user( $this->subscriber );
		$actual = $this->createMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
	}

	/**
	 * Test with a local file path. This is going to fail because the file
	 * does not exist on the test server.
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemCreate.php:89
	 * @access public
	 * @return void
	 */
	public function testCreateMediaItemFilePath() {
		wp_set_current_user( $this->admin );
		$this->create_variables['input']['filePath'] = 'file:///Users/hdevore/Desktop/Current/colorado_lake.jpeg';
		$actual = $this->createMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$this->create_variables['input']['filePath'] = $this->filePath;
	}

	/**
	 * Set the input variables to an empty array and then
	 * make the request with those empty input variables. We should
	 * get an error back from the source because they are required.
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemCreate.php:211
	 * @access public
	 * @return void
	 */
	public function testCreateMediaItemNoInput() {

		/**
		 * Set up the createMediaItem mutation
		 */
		$mutation = '
		mutation createMediaItem( $input: CreateMediaItemInput! ){
		  createMediaItem(input: $input){
		    clientMutationId
		    mediaItem{
		      id
		    }
		  }
		}
		';

		$empty_variables = '';
		$actual = do_graphql_request( $mutation, 'createMediaItem', $empty_variables );
		$this->assertArrayHasKey( 'errors', $actual );
	}

	/**
	 * Set the current user to subscriber (someone who can't create posts)
	 * and test whether they can create posts with someone else's id
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemCreate.php:61
	 * @access public
	 * @return void
	 */
	public function testCreateMediaItemOtherAuthor() {

		/**
		 * Set up the createMediaItem mutation
		 */
		$mutation = '
		mutation createMediaItem( $input: CreateMediaItemInput! ){
		  createMediaItem(input: $input){
		    clientMutationId
		    mediaItem{
		      id
		    }
		  }
		}
		';

		/**
		 * Set the createMediaItem mutation input variables
		 */
		$variables = [
			'input' => [
				'filePath'         => $this->filePath,
				'fileType'         => $this->fileType,
				'clientMutationId' => $this->clientMutationId,
				'title'            => $this->title,
				'description'      => $this->description,
				'altText'          => $this->altText,
				'parentId'         => $this->parentId,
				'caption'          => $this->caption,
				'commentStatus'    => $this->commentStatus,
				'date'             => $this->date,
				'dateGmt'          => $this->dateGmt,
				'slug'             => $this->slug,
				'status'           => $this->status,
				'pingStatus'       => $this->pingStatus,
				'authorId'         => $this->admin,
			],
		];

		wp_set_current_user( $this->author );
		$actual = do_graphql_request( $mutation, 'createMediaItem', $variables );
		$this->assertArrayHasKey( 'errors', $actual );
	}

	/**
	 * Set the filePath to a URL that isn't valid to test whether the mediaItem will
	 * still get created
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemCreate.php:89
	 * @access public
	 * @return void
	 */
	public function testCreateMediaItemWithInvalidUrl() {
		wp_set_current_user( $this->author );
		$this->create_variables['input']['filePath'] = 'htt://vice.co.um/images/2016/09/16/bill-murray-has-a-couple-of-shifts-at-a-brooklyn-bar-this-weekend-body-image-1473999364.jpg?crop=1xw:1xh;center,center&resize=1440:*';
		$actual = $this->createMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$this->create_variables['input']['filePath'] = $this->filePath;
	}

	/**
	 * Set the filePath to a URL that isn't valid to test whether the mediaItem will
	 * still get created
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemCreate.php:121
	 * @access public
	 * @return void
	 */
	public function testCreateMediaItemWithNoFile() {
		wp_set_current_user( $this->author );
		$this->create_variables['input']['filePath'] = 'https://i-d-images.vice.com/images/2016/09/16/bill-murray-has-a-couple-of-shifts-at-a-brooklyn-bar-this-weekend-body-image-1473999364.jpg?crop=1xw:1xh;center,center&resize=1440:*';
		$actual = $this->createMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$this->create_variables['input']['filePath'] = $this->filePath;
	}

	/**
	 * Create a post as the admin and then attach the media item
	 * it should fail at first when we try as an author but then
	 * succeed as an admin
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemCreate.php:142
	 * @access public
	 * @return void
	 */
	public function testCreateMediaItemAttachToParent() {
		$post = $this->factory()->post->create( [
			'post_author' => $this->admin,
		] );
		$this->create_variables['input']['parentId'] = absint( $post );

		/**
		 * Test the mutation as someone who can't edit the parent post,
		 * this should fail
		 */
		wp_set_current_user( $this->author );
		$actual = $this->createMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );

		wp_set_current_user( $this->admin );
		$actual = $this->createMediaItemMutation();

		$media_item_id = $actual["data"]["createMediaItem"]["mediaItem"]["id"];
		$attachment_id = $actual["data"]["createMediaItem"]["mediaItem"]["mediaItemId"];
		$attachment_url = wp_get_attachment_url( $attachment_id );
		$attachment_details = wp_get_attachment_metadata( $attachment_id );

		$expected = [
			'data' => [
				'createMediaItem' => [
					'clientMutationId' => $this->clientMutationId,
					'mediaItem' => [
						'id'               => $media_item_id,
						'mediaItemId'      => $attachment_id,
						'title'            => $this->title,
						'description'      => apply_filters( 'the_content', $this->description ),
						'altText'          => $this->altText,
						'caption'          => apply_filters( 'the_content', $this->caption ),
						'commentStatus'    => $this->commentStatus,
						'date'             => $this->date,
						'dateGmt'          => $this->dateGmt,
						'slug'             => $this->slug,
						'status'           => strtolower( $this->status ),
						'pingStatus'       => $this->pingStatus,
						'mimeType'         => 'image/gif',
						'parent'           => [
							'id' => \GraphQLRelay\Relay::toGlobalId( 'post', $post ),
						],
						'mediaType'        => 'image',
						'sourceUrl'        => $attachment_url,
						'mediaDetails'     => [
							'file'   => $attachment_details['file'],
							'height' => $attachment_details['height'],
							'meta'   => [
								'aperture' => 0.0,
								'credit'   => '',
								'camera'   => '',
								'caption'  => '',
								'createdTimestamp' => null,
								'copyright' => '',
								'focalLength' => null,
								'iso' => 0,
								'shutterSpeed' => null,
								'title' => '',
								'orientation' => '0',
							],
							'width' => $attachment_details['width'],
							'sizes' => [
								0 => [
									'name' => 'thumbnail',
									'file' => $attachment_details['sizes']['thumbnail']['file'],
									'width' => (int) $attachment_details['sizes']['thumbnail']['width'],
									'height' => (int) $attachment_details['sizes']['thumbnail']['height'],
									'mimeType' => $attachment_details['sizes']['thumbnail']['mime-type'],
									'sourceUrl' => basename( wp_get_attachment_thumb_url( $attachment_id ) ),
								],
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
		$this->create_variables['input']['parentId'] = $this->parentId;

	}

	/**
	 * Create a post as the admin and then try to upload a mediaItem
	 * to that post as an author. It should error out since Authors can't
	 * edit other users posts.
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemCreate.php:151
	 * @access public
	 * @return void
	 */
	public function testCreateMediaItemEditOthersPosts() {
		$post = $this->factory()->post->create( [
			'post_author' => $this->admin,
		] );
		wp_set_current_user( $this->author );
		$this->create_variables['input']['parentId'] = $post;
		$actual = $this->createMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$this->create_variables['input']['parentId'] = $this->parentId;
	}

	/**
	 * Test the MediaItemMutation by setting the default values:
	 *
	 * post_status
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemMutation.php:136
	 *
	 * post_title
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemMutation.php:142
	 *
	 * post_author
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemMutation.php:148
	 *
	 * post_content
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemMutation.php:165
	 *
	 * post_mime_type
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemMutation.php:171
	 *
	 * @access public
	 * @returnn void
	 */
	public function testCreateMediaItemDefaultValues() {
		/**
		 * Set the current user as the admin role so we
		 * can properly test the mutation
		 */
		wp_set_current_user( $this->admin );

		/**
		 * Set up the createMediaItem mutation
		 */
		$default_mutation = '
		mutation createMediaItem( $input: CreateMediaItemInput! ){
		  createMediaItem(input: $input){
		    clientMutationId
		    mediaItem{
		      id
		      mediaItemId
		      status
		      title
		      author {
		        id
		      }
		      description
		      mimeType
		      parent {
		        ... on Post {
		          id
		        }
		      }
		      sourceUrl
		      mediaDetails {
	            file
	            height
	            meta {
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
	            }
	            width
	            sizes {
	              name
	              file
	              width
	              height
	              mimeType
	              sourceUrl
	            }
	          }
		    }
		  }
		}
		';

		/**
		 * Set new input variables without changing defaults
		 */
		$default_variables = [
			'input' => [
				'filePath'         => $this->filePath,
				'clientMutationId' => $this->clientMutationId,
			],
		];

		/**
		 * Do the graphQL request using the above variables for input in the above mutation
		 */
		$actual = do_graphql_request( $default_mutation, 'createMediaItem', $default_variables );

		$media_item_id = $actual["data"]["createMediaItem"]["mediaItem"]["id"];
		$attachment_id = $actual["data"]["createMediaItem"]["mediaItem"]["mediaItemId"];
		$attachment_data = get_post( $attachment_id );
		$attachment_title = $attachment_data->post_title;
		$attachment_url = wp_get_attachment_url( $attachment_id );
		$attachment_details = wp_get_attachment_metadata( $attachment_id );

		$expected = [
			'data' => [
				'createMediaItem' => [
					'clientMutationId' => $this->clientMutationId,
					'mediaItem' => [
						'id'               => $media_item_id,
						'mediaItemId'      => $attachment_id,
						'status'           => strtolower( $this->status ),
						'title'            => $attachment_title,
						'description'      => '',
						'mimeType'         => 'image/gif',
						'author'           => [
							'id' => \GraphQLRelay\Relay::toGlobalId( 'user', $this->admin ),
						],
						'parent'           => null,
						'sourceUrl'        => $attachment_url,
						'mediaDetails'     => [
							'file'   => $attachment_details['file'],
							'height' => $attachment_details['height'],
							'meta'   => [
								'aperture' => 0.0,
								'credit'   => '',
								'camera'   => '',
								'caption'  => '',
								'createdTimestamp' => null,
								'copyright' => '',
								'focalLength' => null,
								'iso' => 0,
								'shutterSpeed' => null,
								'title' => '',
								'orientation' => '0',
							],
							'width' => $attachment_details['width'],
							'sizes' => [
								0 => [
									'name' => 'thumbnail',
									'file' => $attachment_details['sizes']['thumbnail']['file'],
									'width' => (int) $attachment_details['sizes']['thumbnail']['width'],
									'height' => (int) $attachment_details['sizes']['thumbnail']['height'],
									'mimeType' => $attachment_details['sizes']['thumbnail']['mime-type'],
									'sourceUrl' => basename( wp_get_attachment_thumb_url( $attachment_id ) ),
								],
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * This function tests the createMediaItem mutation
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemCreate.php
	 * @access public
	 * @return void
	 */
	public function testCreateMediaItemMutation() {

		/**
		 * Set the current user as the admin role so we
		 * can properly test the mutation
		 */
		wp_set_current_user( $this->admin );

		/**
		 * Create the createMediaItem
		 */
		$actual = $this->createMediaItemMutation();

		$media_item_id = $actual["data"]["createMediaItem"]["mediaItem"]["id"];
		$attachment_id = $actual["data"]["createMediaItem"]["mediaItem"]["mediaItemId"];
		$attachment_url = wp_get_attachment_url( $attachment_id );
		$attachment_details = wp_get_attachment_metadata( $attachment_id );

		$expected = [
			'data' => [
				'createMediaItem' => [
					'clientMutationId' => $this->clientMutationId,
					'mediaItem' => [
						'id'               => $media_item_id,
						'mediaItemId'      => $attachment_id,
						'title'            => $this->title,
						'description'      => apply_filters( 'the_content', $this->description ),
						'altText'          => $this->altText,
						'caption'          => apply_filters( 'the_content', $this->caption ),
						'commentStatus'    => $this->commentStatus,
						'date'             => $this->date,
						'dateGmt'          => $this->dateGmt,
						'slug'             => $this->slug,
						'status'           => strtolower( $this->status ),
						'pingStatus'       => $this->pingStatus,
						'mimeType'         => 'image/gif',
						'parent'           => null,
						'mediaType'        => 'image',
						'sourceUrl'        => $attachment_url,
						'mediaDetails'     => [
							'file'   => $attachment_details['file'],
							'height' => $attachment_details['height'],
							'meta'   => [
								'aperture' => 0.0,
								'credit'   => '',
								'camera'   => '',
								'caption'  => '',
								'createdTimestamp' => null,
								'copyright' => '',
								'focalLength' => null,
								'iso' => 0,
								'shutterSpeed' => null,
								'title' => '',
								'orientation' => '0',
							],
							'width' => $attachment_details['width'],
							'sizes' => [
								0 => [
									'name' => 'thumbnail',
									'file' => $attachment_details['sizes']['thumbnail']['file'],
									'width' => (int) $attachment_details['sizes']['thumbnail']['width'],
									'height' => (int) $attachment_details['sizes']['thumbnail']['height'],
									'mimeType' => $attachment_details['sizes']['thumbnail']['mime-type'],
									'sourceUrl' => basename( wp_get_attachment_thumb_url( $attachment_id ) ),
								],
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );

	}

	/**
	 * This function tests the updateMediaItem mutation
	 * and is reused throughout the updateMediaItem tests
	 *
	 * @access public
	 * @return array $actual
	 */
	public function updateMediaItemMutation() {

		/**
		 * Prepare the updateMediaItem mutation
		 */
		$mutation = '
		mutation updateMediaItem( $input: UpdateMediaItemInput! ){
		  updateMediaItem (input: $input){
		    clientMutationId
		    mediaItem {
		      id
		      mediaItemId
		      date
		      dateGmt
		      slug
		      status
		      title
		      commentStatus
		      pingStatus
		      altText
		      caption
		      description
		      mimeType
		      author {
		        id
		      }
		    }
		  }
		}
		';

		$actual = do_graphql_request( $mutation, 'updateMediaItem', $this->update_variables );

		return $actual;
	}

	/**
	 * Execute the request with a fake mediaItem id. An error
	 * should occur because we didn't pass the id of the mediaItem we
	 * wanted to update.
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemUpdate.php:57
	 * @access public
	 * @return void
	 */
	public function testUpdateMediaItemInvalidId() {
		$this->update_variables['input']['id'] = \GraphQLRelay\Relay::toGlobalId( 'attachment', 123456 );
		$actual = $this->updateMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$this->update_variables['input']['id'] = $this->media_item_id;
	}

	/**
	 * Test whether the mediaItem we're updating is actually a mediaItem
	 *
	 * @souce wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemUpdate.php:67
	 * @access public
	 * @return void
	 */
	public function testUpdateMediaItemUpdatePost() {
		$test_post = $this->factory()->post->create();
		$this->update_variables['input']['id'] = \GraphQLRelay\Relay::toGlobalId( 'post', $test_post );
		$actual = $this->updateMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$this->update_variables['input']['id'] = $this->media_item_id;
	}

	/**
	 * Set the current user to a subscriber (someone who can't create posts)
	 * and test whether they can create posts
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemUpdate.php:74
	 * @access public
	 * @return void
	 */
	public function testUpdateMediaItemAsSubscriber() {
		wp_set_current_user( $this->subscriber );
		$actual = $this->updateMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
	}

	/**
	 * Create a post as the admin and then try to upload a mediaItem
	 * to that post as an author. It should error out since Authors can't
	 * edit other users posts.
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemUpdate.php:91
	 * @access public
	 * @return void
	 */
	public function testUpdateMediaItemEditOthersPosts() {
		$post = $this->factory()->post->create( [
			'post_author' => $this->admin,
		] );
		wp_set_current_user( $this->author );
		$this->update_variables['input']['parentId'] = $post;
		$actual = $this->updateMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$this->update_variables['input']['parentId'] = $this->parentId;
	}

	/**
	 * Create a post as the admin and then try to upload a mediaItem
	 * to that post as an author. It should error out since Authors can't
	 * edit other users posts.
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemUpdate.php:91
	 * @access public
	 * @return void
	 */
	public function testUpdateMediaItemAddOtherAuthorsAsAuthor() {
		wp_set_current_user( $this->author );
		$this->update_variables['input']['authorId'] = \GraphQLRelay\Relay::toGlobalId( 'user', $this->admin );
		$actual = $this->updateMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$this->update_variables['input']['authorId'] = false;
	}

	/**
	 * Create a post as the admin and then try to upload a mediaItem
	 * to that post as an admin. It should be created.
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemUpdate.php:91
	 * @access public
	 * @return void
	 */
	public function testUpdateMediaItemAddOtherAuthorsAsAdmin() {
		wp_set_current_user( $this->admin );
		$this->update_variables['input']['authorId'] = \GraphQLRelay\Relay::toGlobalId( 'user', $this->author );
		$actual = $this->updateMediaItemMutation();
		$actual_created = $actual['data']['updateMediaItem']['mediaItem'];
		$this->assertArrayHasKey( 'id', $actual_created );
		$update_variables['input']['authorId'] = false;
	}

	/**
	 * This function tests the updateMediaItem mutation
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemUpdate.php
	 * @access public
	 * @return void
	 */
	public function testUpdateMediaItemMutation() {

		/**
		 * Set the current user as the admin role so we
		 * successfully run the mutation
		 */
		wp_set_current_user( $this->admin );

		$actual = $this->updateMediaItemMutation();

		/**
		 * Define the expected output.
		 */
		$expected = [
			'data' => [
				'updateMediaItem' => [
					'clientMutationId' => $this->updated_clientMutationId,
					'mediaItem'             => [
						'id'               => $this->media_item_id,
						'title'            => $this->updated_title,
						'description'      => apply_filters( 'the_content', $this->updated_description ),
						'mediaItemId'      => $this->attachment_id,
						'altText'          => $this->updated_altText,
						'caption'          => apply_filters( 'the_content', $this->updated_caption ),
						'commentStatus'    => $this->updated_commentStatus,
						'date'             => $this->updated_date,
						'dateGmt'          => $this->updated_dateGmt,
						'slug'             => $this->updated_slug,
						'status'           => strtolower( $this->updated_status ),
						'pingStatus'       => $this->updated_pingStatus,
						'mimeType'         => 'image/gif',
						'author'           => [
							'id'       => \GraphQLRelay\Relay::toGlobalId( 'user', $this->admin ),
						],
					],
				],
			],
		];

		$attachment = get_post( $this->attachment_id );

		/**
		 * Compare the actual output vs the expected output
		 */
		$this->assertEquals( $actual, $expected );

	}

	/**
	 * This function tests the deletMediaItem mutation
	 * and is reused throughout the deleteMediaItem tests
	 *
	 * @access public
	 * @return array $actual
	 */
	public function deleteMediaItemMutation() {

		/**
		 * Prepare the deleteMediaItem mutation
		 */
		$mutation = '
		mutation deleteMediaItem( $input: DeleteMediaItemInput! ){
		  deleteMediaItem(input: $input) {
		    clientMutationId
		    deletedId
		    mediaItem{
		      id
		      mediaItemId
		    }
		  }
		}
		';

		$actual = do_graphql_request( $mutation, 'deleteMediaItem', $this->delete_variables );

		return $actual;
	}

	/**
	 * Set the mediaItem id to a fake id and the mutation should fail
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemDelete.php:79
	 * @access public
	 * @return void
	 */
	public function testDeleteMediaItemInvalidId() {
		$this->delete_variables['input']['id'] = 12345;
		$actual = $this->deleteMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
		$this->delete_variables['input']['id'] = $this->media_item_id;
	}

	/**
	 * Set the current user as the subscriber role and
	 * the deletion should fail because we're a subscriber.
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/MediaItemDelete.php:86
	 * @access public
	 * @return void
	 */
	public function testDeleteMediaItemAsSubscriber() {
		wp_set_current_user( $this->subscriber );
		$actual = $this->deleteMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );
	}

	/**
	 * Set the force delete input to false and the
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemDelete.php:92
	 * @access public
	 * @return array $actual
	 */
	public function testDeleteMediaItemAlreadyInTrash() {

		$deleted_media_item = $this->factory()->attachment->create( ['post_status' => 'trash'] );
		$post = get_post( $deleted_media_item );

		/**
		 * Prepare the deleteMediaItem mutation
		 */
		$mutation = '
		mutation deleteMediaItem( $input: DeleteMediaItemInput! ){
		  deleteMediaItem(input: $input) {
		    clientMutationId
		    deletedId
		    mediaItem{
		      id
		      mediaItemId
		    }
		  }
		}
		';

		/**
		 * Set the deleteMediaItem input variables
		 */
		$delete_trash_variables = [
			'input' => [
				'id'               => \GraphQLRelay\Relay::toGlobalId( 'attachment', $deleted_media_item ),
				'clientMutationId' => $this->clientMutationId,
				'forceDelete'      => false,
			]
		];

		wp_set_current_user( $this->admin );
		$actual = do_graphql_request( $mutation, 'deleteMediaItem', $delete_trash_variables );
		$this->assertArrayHasKey( 'errors', $actual );

		$delete_trash_variables['input']['forceDelete'] = true;
		$actual = do_graphql_request( $mutation, 'deleteMediaItem', $delete_trash_variables );
		$actual_deleted_item = $actual['data']['deleteMediaItem'];
		$this->assertArrayHasKey( 'deletedId', $actual_deleted_item );

	}

	/**
	 * This funtion tests the deleteMediaItem mutation by trying to delete a post
	 * instead of an attachment
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemDelete.php:103
	 * @access public
	 * @return void
	 */
	public function testDeleteMediaItemAsPost() {

		/**
		 * Set the user to an admin
		 */
		wp_set_current_user( $this->admin );

		/**
		 * Create a post that we can try to delete with the deleteMediaItem mutaton
		 */
		$args = [
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => 'Original Title',
			'post_content' => 'Original Content',
		];

		/**
		 * Create a page to test against and set the post id in the mutation variables
		 */
		$post_to_delete = $this->factory->post->create( $args );
		$this->delete_variables['input']['id'] = \GraphQLRelay\Relay::toGlobalId( 'post', $post_to_delete );

		/**
		 * Define the expected output
		 */
		$actual = $this->deleteMediaItemMutation();

		/*
		 * Compare it to the actual output and reset the id delete variable
		 */
		$this->assertArrayHasKey( 'errors', $actual );
		$this->delete_variables['input']['id'] = $this->media_item_id;

	}

	/**
	 * This function tests the deleteMediaItem mutation
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/MediaItem/Mutation/MediaItemDelete.php
	 * @access public
	 * @return void
	 */
	public function testDeleteMediaItemMutation() {

		/**
		 * Set the user to an admin and try again
		 */
		wp_set_current_user( $this->admin );
		$actual = $this->deleteMediaItemMutation();

		/**
		 * Define the expected output.
		 */
		$expected = [
			'data' => [
				'deleteMediaItem' => [
					'clientMutationId' => $this->clientMutationId,
					'deletedId' => $this->media_item_id,
					'mediaItem' => [
						'id'               => $this->media_item_id,
						'mediaItemId'      => $this->attachment_id,
					],
				],
			],
		];

		/**
		 * Compare the actual output vs the expected output
		 */
		$this->assertEquals( $actual, $expected );

		/**
		 * Try to delete again but we should have errors, because there's nothing to be deleted
		 */
		$actual = $this->deleteMediaItemMutation();
		$this->assertArrayHasKey( 'errors', $actual );

	}
}