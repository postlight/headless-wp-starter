<?php

class WP_GraphQL_Test_Settings_Mutations extends \Codeception\TestCase\WPTestCase  {

	public $clientMutationId;
	public $defaultCategory;
	public $discussionSettingsDefaultCommentStatus;
	public $discussionSettingsDefaultPingStatus;
	public $generalSettingsDateFormat;
	public $generalSettingsDescription;
	public $generalSettingsEmail;
	public $generalSettingsLanguage;
	public $generalSettingsStartOfWeek;
	public $generalSettingsTimeFormat;
	public $generalSettingsTitle;
	public $readingSettingsPostsPerPage;
	public $writingSettingsDefaultCategory;
	public $writingSettingsUseSmilies;
	public $writingSettingsDefaultPostFormat;

	public $update_variables;

	public $subscriber;
	public $subscriber_name;
	public $author;
	public $author_name;
	public $admin;
	public $admin_name;

	public function setUp() {

		$this->subscriber = $this->factory->user->create( [
			'role' => 'subscriber',
		] );
		$this->subscriber_name = 'User ' . $this->subscriber;

		$this->author = $this->factory->user->create( [
			'role' => 'author',
		] );
		$this->author_name = 'User ' . $this->author;

		$this->admin = $this->factory->user->create( [
			'role' => 'administrator',
		] );
		$this->admin_name = 'User ' . $this->admin;

		/**
		 * Set up the updateSettings variables
		 */
		$this->clientMutationId                       = 'testMutationId';
		$this->discussionSettingsDefaultCommentStatus = 'closed';
		$this->discussionSettingsDefaultPingStatus    = 'closed';
		$this->generalSettingsDateFormat              = 'Y-m-d';
		$this->generalSettingsDescription             = 'Local Testing Site';
		$this->generalSettingsEmail                   = 'hdevore@medianewsgroup.com';
		$this->generalSettingsLanguage                = 'en_US';
		$this->generalSettingsStartOfWeek             = 2;
		$this->generalSettingsTimeFormat              = 'g:i:s a';
		$this->generalSettingsTimezone                = 'America/Denver';
		$this->generalSettingsTitle                   = 'WPGraphQL Site Title';
		$this->readingSettingsPostsPerPage            = 20;
		$this->writingSettingsDefaultCategory         = $this->factory()->category->create();
		$this->writingSettingsDefaultPostFormat       = 'quote';
		$this->writingSettingsUseSmilies              = false;

		/**
		 * Set the createMediaItem mutation input variables
		 */
		if ( is_multisite() ) {
			$this->update_variables = [
				'input' => [
					'clientMutationId'                       => $this->clientMutationId,
					'discussionSettingsDefaultCommentStatus' => $this->discussionSettingsDefaultCommentStatus,
					'discussionSettingsDefaultPingStatus'    => $this->discussionSettingsDefaultPingStatus,
					'generalSettingsDateFormat'              => $this->generalSettingsDateFormat,
					'generalSettingsDescription'             => $this->generalSettingsDescription,
					'generalSettingsLanguage'                => $this->generalSettingsLanguage,
					'generalSettingsStartOfWeek'             => $this->generalSettingsStartOfWeek,
					'generalSettingsTimeFormat'              => $this->generalSettingsTimeFormat,
					'generalSettingsTimezone'                => $this->generalSettingsTimezone,
					'generalSettingsTitle'                   => $this->generalSettingsTitle,
					'readingSettingsPostsPerPage'            => $this->readingSettingsPostsPerPage,
					'writingSettingsDefaultCategory'         => $this->writingSettingsDefaultCategory,
					'writingSettingsDefaultPostFormat'       => $this->writingSettingsDefaultPostFormat,
					'writingSettingsUseSmilies'              => $this->writingSettingsUseSmilies,
				],
			];
		} else {
			$this->update_variables = [
				'input' => [
					'clientMutationId'                       => $this->clientMutationId,
					'discussionSettingsDefaultCommentStatus' => $this->discussionSettingsDefaultCommentStatus,
					'discussionSettingsDefaultPingStatus'    => $this->discussionSettingsDefaultPingStatus,
					'generalSettingsDateFormat'              => $this->generalSettingsDateFormat,
					'generalSettingsDescription'             => $this->generalSettingsDescription,
					'generalSettingsEmail'                   => $this->generalSettingsEmail,
					'generalSettingsLanguage'                => $this->generalSettingsLanguage,
					'generalSettingsStartOfWeek'             => $this->generalSettingsStartOfWeek,
					'generalSettingsTimeFormat'              => $this->generalSettingsTimeFormat,
					'generalSettingsTimezone'                => $this->generalSettingsTimezone,
					'generalSettingsTitle'                   => $this->generalSettingsTitle,
					'readingSettingsPostsPerPage'            => $this->readingSettingsPostsPerPage,
					'writingSettingsDefaultCategory'         => $this->writingSettingsDefaultCategory,
					'writingSettingsDefaultPostFormat'       => $this->writingSettingsDefaultPostFormat,
					'writingSettingsUseSmilies'              => $this->writingSettingsUseSmilies,
				],
			];
		}

		/**
		* Manually Register a setting for testing
	    *
        * This registers a setting as a number to see if it gets the correct type
        * associated with it and returned through WPGraphQL
	    */
		register_setting( 'Zool', 'points', array(
			'type'         => 'number',
			'description'  => __( 'Test how many points we have in Zool.' ),
			'show_in_graphql' => true,
			'default' => 4.5,
		) );

		parent::setUp();

	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * This function tests the updateSettings mutation
	 * and is reused throughout the updateSettings tests
	 *
	 * @access public
	 * @return array $actual
	 */
	public function updateSettingsMutation() {

		/**
		 * Prepare the updateSettings mutation
		 */
		if ( is_multisite() ) {
			$mutation = '
				mutation updateSettings( $input: UpdateSettingsInput! ){
				  updateSettings( input: $input ) {
				    clientMutationId
				    allSettings {
				      discussionSettingsDefaultCommentStatus
				      discussionSettingsDefaultPingStatus
				      generalSettingsDateFormat
				      generalSettingsDescription
				      generalSettingsLanguage
				      generalSettingsStartOfWeek
				      generalSettingsTimeFormat
				      generalSettingsTimezone
				      generalSettingsTitle
				      readingSettingsPostsPerPage
				      writingSettingsDefaultCategory
				      writingSettingsDefaultPostFormat
				      writingSettingsUseSmilies
				    }
				    discussionSettings {
				      defaultCommentStatus
				      defaultPingStatus
				    }
				    generalSettings {
				      dateFormat
				      description
				      language
				      startOfWeek
				      timeFormat
				      timezone
				      title
				    }
				    readingSettings {
				      postsPerPage
				    }
				    writingSettings {
				      defaultCategory
				      defaultPostFormat
				      useSmilies
				    }
				  }
				}
			';

		} else {
			$mutation = '
				mutation updateSettings( $input: UpdateSettingsInput! ){
				  updateSettings( input: $input ) {
				    clientMutationId
				    allSettings {
				      discussionSettingsDefaultCommentStatus
				      discussionSettingsDefaultPingStatus
				      generalSettingsDateFormat
				      generalSettingsDescription
				      generalSettingsEmail
				      generalSettingsLanguage
				      generalSettingsStartOfWeek
				      generalSettingsTimeFormat
				      generalSettingsTimezone
				      generalSettingsTitle
				      generalSettingsUrl
				      readingSettingsPostsPerPage
				      writingSettingsDefaultCategory
				      writingSettingsDefaultPostFormat
				      writingSettingsUseSmilies
				    }
				    discussionSettings {
				      defaultCommentStatus
				      defaultPingStatus
				    }
				    generalSettings {
				      dateFormat
				      description
				      email
				      language
				      startOfWeek
				      timeFormat
				      timezone
				      title
				      url
				    }
				    readingSettings {
				      postsPerPage
				    }
				    writingSettings {
				      defaultCategory
				      defaultPostFormat
				      useSmilies
				    }
				  }
				}
			';

		}

		$actual = do_graphql_request( $mutation, 'updateSettings', $this->update_variables );

		return $actual;
	}

	/**
	 * This function tests whether a user can update settings if they don't have the right credentials
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/Settings/Mutation/SettingsUpdate.php:51
	 * @access public
	 * @return void
	 */
	public function testUpdateSettingsAsAuthor() {
		/**
		 * Set the current user as the author role so we
		 * receive an auth error back
		 */
		wp_set_current_user( $this->author );

		$actual = $this->updateSettingsMutation();

		$this->assertArrayHasKey( 'errors', $actual );

	}

	/**
	 * Method for testing whether a user can query settings
	 * if they don't have the 'manage_options' capability
	 *
	 * They should not be able to query for the admin email
	 * so we should receive an error back
	 *
	 * @access public
	 * @return void
	 */
	public function testSettingsQueryAsEditor() {
		/**
		 * Set the editor user
		 * Set the query
		 * Make the request
		 * Validate the request has errors
		 */
		wp_set_current_user( $this->editor );
		$query = "
			query {
				allSettings {
				    generalSettingsEmail
				}
		    }
	    ";
		$actual = do_graphql_request( $query );

		$this->assertArrayHasKey( 'errors', $actual );

	}

	/**
	 * This function tests whether we receive an error or success
	 * when trying to update the site's URL
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/Settings/Mutation/SettingsUpdate.php:63
	 * @access public
	 * @return void
	 */
	public function testUpdateSettingsSiteURLMutation() {
		/**
		 * Set the current user as the admin role so we
		 * successfully run the mutation
		 */
		wp_set_current_user( $this->admin );

		$this->update_variables['input']['generalSettingsUrl'] = 'http://exampleTEST.org';

		$actual = $this->updateSettingsMutation();

		$this->assertArrayHasKey( 'errors', $actual );

	}

	/**
	 * This function tests the updateSettings mutation
	 *
	 * @source wp-content/plugins/wp-graphql/src/Type/Settings/Mutation/SettingsUpdate.php
	 * @access public
	 * @return void
	 */
	public function testUpdateSettingsMutation() {

		/**
		 * Set the current user as the admin role so we
		 * successfully run the mutation
		 */
		wp_set_current_user( $this->admin );

		$actual = $this->updateSettingsMutation();

		/**
		 * Define the expected output.
		 */

		if ( is_multisite() ) {
			$expected = [
				'data' => [
					'updateSettings' => [
						'clientMutationId'   => $this->clientMutationId,
						'allSettings'        => [
							'discussionSettingsDefaultCommentStatus' => $this->discussionSettingsDefaultCommentStatus,
							'discussionSettingsDefaultPingStatus'    => $this->discussionSettingsDefaultPingStatus,
							'generalSettingsDateFormat'              => $this->generalSettingsDateFormat,
							'generalSettingsDescription'             => $this->generalSettingsDescription,
							'generalSettingsLanguage'                => $this->generalSettingsLanguage,
							'generalSettingsStartOfWeek'             => $this->generalSettingsStartOfWeek,
							'generalSettingsTimeFormat'              => $this->generalSettingsTimeFormat,
							'generalSettingsTimezone'                => $this->generalSettingsTimezone,
							'generalSettingsTitle'                   => $this->generalSettingsTitle,
							'readingSettingsPostsPerPage'            => $this->readingSettingsPostsPerPage,
							'writingSettingsDefaultCategory'         => $this->writingSettingsDefaultCategory,
							'writingSettingsDefaultPostFormat'       => $this->writingSettingsDefaultPostFormat,
							'writingSettingsUseSmilies'              => $this->writingSettingsUseSmilies,
						],
						'discussionSettings' => [
							'defaultCommentStatus' => $this->discussionSettingsDefaultCommentStatus,
							'defaultPingStatus'    => $this->discussionSettingsDefaultPingStatus,
						],
						'generalSettings'    => [
							'dateFormat'  => $this->generalSettingsDateFormat,
							'description' => $this->generalSettingsDescription,
							'language'    => $this->generalSettingsLanguage,
							'startOfWeek' => $this->generalSettingsStartOfWeek,
							'timeFormat'  => $this->generalSettingsTimeFormat,
							'timezone'    => $this->generalSettingsTimezone,
							'title'       => $this->generalSettingsTitle,
						],
						'readingSettings'    => [
							'postsPerPage' => $this->readingSettingsPostsPerPage,
						],
						'writingSettings'    => [
							'defaultCategory'   => $this->writingSettingsDefaultCategory,
							'defaultPostFormat' => $this->writingSettingsDefaultPostFormat,
							'useSmilies'        => $this->writingSettingsUseSmilies,
						],
					],
				],
			];
		} else {
			$expected = [
				'data' => [
					'updateSettings' => [
						'clientMutationId' => $this->clientMutationId,
						'allSettings'             => [
							'discussionSettingsDefaultCommentStatus'         => $this->discussionSettingsDefaultCommentStatus,
							'discussionSettingsDefaultPingStatus'            => $this->discussionSettingsDefaultPingStatus,
							'generalSettingsDateFormat'                      => $this->generalSettingsDateFormat,
							'generalSettingsDescription'                     => $this->generalSettingsDescription,
							'generalSettingsEmail'                           => $this->generalSettingsEmail,
							'generalSettingsLanguage'                        => $this->generalSettingsLanguage,
							'generalSettingsStartOfWeek'                     => $this->generalSettingsStartOfWeek,
							'generalSettingsTimeFormat'                      => $this->generalSettingsTimeFormat,
							'generalSettingsTimezone'                        => $this->generalSettingsTimezone,
							'generalSettingsTitle'                           => $this->generalSettingsTitle,
							'generalSettingsUrl'                             => 'http://wpgraphql.test',
							'readingSettingsPostsPerPage'                    => $this->readingSettingsPostsPerPage,
							'writingSettingsDefaultCategory'                 => $this->writingSettingsDefaultCategory,
							'writingSettingsDefaultPostFormat'               => $this->writingSettingsDefaultPostFormat,
							'writingSettingsUseSmilies'                      => $this->writingSettingsUseSmilies,
						],
						'discussionSettings'    => [
							'defaultCommentStatus' => $this->discussionSettingsDefaultCommentStatus,
							'defaultPingStatus'    => $this->discussionSettingsDefaultPingStatus,
						],
						'generalSettings'       => [
							'dateFormat'  => $this->generalSettingsDateFormat,
							'description' => $this->generalSettingsDescription,
							'email'       => $this->generalSettingsEmail,
							'language'    => $this->generalSettingsLanguage,
							'startOfWeek' => $this->generalSettingsStartOfWeek,
							'timeFormat'  => $this->generalSettingsTimeFormat,
							'timezone'    => $this->generalSettingsTimezone,
							'title'       => $this->generalSettingsTitle,
							'url'         => 'http://wpgraphql.test',
						],
						'readingSettings'       => [
							'postsPerPage' => $this->readingSettingsPostsPerPage,
						],
						'writingSettings'       => [
							'defaultCategory'   => $this->writingSettingsDefaultCategory,
							'defaultPostFormat' => $this->writingSettingsDefaultPostFormat,
							'useSmilies'        => $this->writingSettingsUseSmilies,
						],
					],
				],
			];
		}

		/**
		 * Compare the actual output vs the expected output
		 */
		$this->assertEquals( $actual, $expected );

	}

}