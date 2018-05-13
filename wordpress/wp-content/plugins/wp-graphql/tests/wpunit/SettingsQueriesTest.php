<?php

class WP_GraphQL_Test_Settings_Queries extends \Codeception\TestCase\WPTestCase {

	public function setUp() {

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

		$this->admin = $this->factory->user->create( [
			'role' => 'administrator',
		] );

		$this->editor = $this->factory->user->create( [
			'role' => 'editor',
		] );

		parent::setUp();

	}

	public function tearDown() {
		parent::tearDown();
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
	public function testAllSettingsQueryAsEditor() {
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
	 * Method for testing the generalSettings
	 *
	 * @access public
	 * @return void
	 */
	public function testAllSettingsQuery() {

		/**
		 * Set the admin user
		 * Set the query
		 * Make the request
		 * Validate the request
		 */
		wp_set_current_user( $this->admin );

		$mock_options = [
			'default_comment_status' => 'closed',
			'default_ping_status' => 'closed',
			'date_format' => 'test date format',
			'blogdescription' => 'test description',
			'admin_email' => 'test@test.com',
			'start_of_week' => 0,
			'time_format' => 'test_time_format',
			'timezone_string' => 'UTC',
			'blogname' => 'test_title',
			'siteurl' => 'http://test.com',
			'posts_per_page' => 20,
			'default_category' => 2,
			'default_post_format' => 'quote',
			'use_smilies' => 0,
			'points' => 5.5,
		];

		foreach ( $mock_options as $mock_option_key => $mock_value ) {
			update_option( $mock_option_key, $mock_value );
		}

		if ( true === is_multisite() ) {
			$query = "
				query {
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
				}
			";
		} else {
			$query = "
				query {
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
				}
			";
		}

		$actual = do_graphql_request( $query );

		$allSettings = $actual['data']['allSettings'];

		$this->assertNotEmpty( $allSettings );
		$this->assertEquals( $mock_options['default_comment_status'], $allSettings['discussionSettingsDefaultCommentStatus'] );
		$this->assertEquals( $mock_options['default_ping_status'], $allSettings['discussionSettingsDefaultPingStatus'] );
		$this->assertEquals( $mock_options['date_format'], $allSettings['generalSettingsDateFormat'] );
		$this->assertEquals( $mock_options['blogdescription'], $allSettings['generalSettingsDescription'] );
		if ( ! is_multisite() ) {
			$this->assertEquals( $mock_options['admin_email'], $allSettings['generalSettingsEmail'] );
		}
		$this->assertEquals( 'en_US', $allSettings['generalSettingsLanguage'] );
		$this->assertEquals( $mock_options['start_of_week'], $allSettings['generalSettingsStartOfWeek'] );
		$this->assertEquals( $mock_options['time_format'], $allSettings['generalSettingsTimeFormat'] );
		$this->assertEquals( $mock_options['timezone_string'], $allSettings['generalSettingsTimezone'] );
		$this->assertEquals( $mock_options['blogname'], $allSettings['generalSettingsTitle'] );
		if ( ! is_multisite() ) {
			$this->assertEquals( $mock_options['siteurl'], $allSettings['generalSettingsUrl'] );
		}
		$this->assertEquals( $mock_options['posts_per_page'], $allSettings['readingSettingsPostsPerPage'] );
		$this->assertEquals( $mock_options['default_category'], $allSettings['writingSettingsDefaultCategory'] );
		$this->assertEquals( $mock_options['default_post_format'], $allSettings['writingSettingsDefaultPostFormat'] );
		$this->assertEquals( $mock_options['use_smilies'], $allSettings['writingSettingsUseSmilies'] );
	}

}