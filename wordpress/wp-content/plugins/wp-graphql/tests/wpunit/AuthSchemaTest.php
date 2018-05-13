<?php

class AuthSchemaTest extends \Codeception\TestCase\WPTestCase {

	public $post;
	public $admin;
	public $editor;
	public $subscriber;
	public $global_id;

	public function setUp() {
		// before
		parent::setUp();

		$this->admin = $this->factory->user->create( [
			'role'       => 'administrator',
			'user_email' => 'schema_admin_test@example.com',
		] );

		$this->editor = $this->factory->user->create( [
			'role'       => 'editor',
			'user_login' => 'schemaEditor',
		] );

		$this->subscriber = $this->factory->user->create( [
			'role'       => 'subscriber',
			'user_login' => 'schemaSubscriber',
		] );

		$this->post = $this->factory->post->create( [
			'post_type'   => 'post',
			'post_status' => 'publish',
		] );

		$this->global_id = \GraphQLRelay\Relay::toGlobalId( 'post', $this->post );

		tests_add_filter( 'graphql_post_fields', function( $fields ) {

			$fields['testIsPrivate'] = [
				'type' => \WPGraphQL\Types::string(),
				'isPrivate' => true,
				'resolve' => function() {
					return 'isPrivateValue';
				}
			];

			$fields['authCallback'] = [
				'type' => \WPGraphQL\Types::string(),
				'auth' => [
					'callback' => function( $field, $field_key,  $source, $args, $context, $info, $field_resolver ) {
						/**
						 * If the current user isn't the user with the login "admin" throw an error
						 */
						if ( 'schema_admin_test@example.com' !== wp_get_current_user()->user_email ) {
							throw new \GraphQL\Error\UserError( __( 'You need the secret!', 'wp-graphql' ) );
						}
						return $field_resolver;
					}
				],
				'resolve' => function() {
					return 'authCallbackValue';
				}
			];

			$fields['authRoles'] = [
				'type' => \WPGraphQL\Types::string(),
				'auth' => [
					'allowedRoles' => [ 'administrator', 'editor' ],
				],
				'resolve' => function() {
					return 'allowedRolesValue';
				}
			];

			$fields['authCaps'] = [
				'type' => \WPGraphQL\Types::string(),
				'auth' => [
					'allowedCaps' => [ 'manage_options', 'graphql_rocks' ],
				],
				'resolve' => function() {
					return 'allowedCapsValue';
				}
			];

			return $fields;

		} );

	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * This tests to make sure a field marked isPrivate will return a null value for the resolver
	 */
	public function testIsPrivate() {

		/**
		 * Set the current user to nobody
		 */
		wp_set_current_user( 0 );

		$request = '
		query getPost( $id:ID! ) {
		  post( id:$id ) {
		    id
		    postId
		    testIsPrivate
		  }
		}
		';

		/**
		 * Run the request
		 */
		$variables = wp_json_encode( [ 'id' => $this->global_id ] );
		$actual    = do_graphql_request( $request, 'getPost', $variables );

		/**
		 * The query should execute, but should contain errors
		 */
		$this->assertArrayHasKey( 'errors', $actual );
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertNull( $actual['data']['post']['testIsPrivate'] );
		$this->assertEquals( $this->post, $actual['data']['post']['postId'] );

		/**
		 * Set the user as an admin
		 */
		wp_set_current_user( $this->admin );

		/**
		 * Run the request
		 */
		$actual = do_graphql_request( $request, 'getPost', $variables );

		/**
		 * The query should execute, and should NOT contain errors but should properly resolve the "isPrivateValue"
		 * for the "testIsPrivate" field
		 */
		$this->assertArrayNotHasKey( 'errors', $actual );
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertEquals( 'isPrivateValue', $actual['data']['post']['testIsPrivate'] );
		$this->assertEquals( $this->post, $actual['data']['post']['postId'] );

	}

	public function testAuthCallback() {

		/**
		 * Set the current user to nobody
		 */
		wp_set_current_user( $this->subscriber );

		$request = '
		query getPost( $id:ID! ) {
		  post( id:$id ) {
		    id
		    postId
		    authCallback
		  }
		}
		';

		/**
		 * Run the request
		 */
		$variables = wp_json_encode( [ 'id' => $this->global_id ] );
		$actual    = do_graphql_request( $request, 'getPost', $variables );

		/**
		 * The query should execute, but should contain errors
		 */
		$this->assertArrayHasKey( 'errors', $actual );
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertNull( $actual['data']['post']['authCallback'] );
		$this->assertEquals( $this->post, $actual['data']['post']['postId'] );

		wp_set_current_user( $this->admin );

		/**
		 * Run the request
		 */
		$actual = do_graphql_request( $request, 'getPost', $variables );

		/**
		 * The query should execute, and should NOT contain errors, but should contain the value
		 * of the authCallback field
		 */
		$this->assertArrayNotHasKey( 'errors', $actual );
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertEquals( 'authCallbackValue', $actual['data']['post']['authCallback'] );
		$this->assertEquals( $this->post, $actual['data']['post']['postId'] );

	}

	public function testAuthRoles() {

		/**
		 * Set the current user to nobody
		 */
		wp_set_current_user( $this->admin );

		$request = '
		query getPost( $id:ID! ) {
		  post( id:$id ) {
		    id
		    postId
		    authRoles
		  }
		}
		';

		/**
		 * Run the request
		 */
		$variables = wp_json_encode( [ 'id' => $this->global_id ] );
		$actual    = do_graphql_request( $request, 'getPost', $variables );

		/**
		 * The query should execute, but should contain errors
		 */
		$this->assertArrayNotHasKey( 'errors', $actual );
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertEquals( 'allowedRolesValue', $actual['data']['post']['authRoles'] );
		$this->assertEquals( $this->post, $actual['data']['post']['postId'] );

		wp_set_current_user( $this->editor );

		$actual = do_graphql_request( $request, 'getPost', $variables );

		/**
		 * The query should execute, but should contain errors
		 */
		$this->assertArrayNotHasKey( 'errors', $actual );
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertEquals( 'allowedRolesValue', $actual['data']['post']['authRoles'] );
		$this->assertEquals( $this->post, $actual['data']['post']['postId'] );

		wp_set_current_user( $this->subscriber );

		$actual = do_graphql_request( $request, 'getPost', $variables );

		/**
		 * The query should execute, but should contain errors
		 */
		$this->assertArrayHasKey( 'errors', $actual );
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertNull( $actual['data']['post']['authRoles'] );
		$this->assertEquals( $this->post, $actual['data']['post']['postId'] );

	}

	public function testAuthCaps() {

		/**
		 * Set the current user to nobody
		 */
		wp_set_current_user( $this->subscriber );

		$request = '
		query getPost( $id:ID! ) {
		  post( id:$id ) {
		    id
		    postId
		    authCaps
		  }
		}
		';

		/**
		 * Run the request
		 */
		$variables = wp_json_encode( [ 'id' => $this->global_id ] );
		$actual    = do_graphql_request( $request, 'getPost', $variables );

		/**
		 * The query should execute, but should contain errors
		 */
		$this->assertArrayHasKey( 'errors', $actual );
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertNull( $actual['data']['post']['authCaps'] );
		$this->assertEquals( $this->post, $actual['data']['post']['postId'] );

		/**
		 * Remove the caps from the user
		 */
		wp_set_current_user( $this->admin );

		/**
		 * Run the request, this time the value should be null and there should be an error
		 */
		$actual = do_graphql_request( $request, 'getPost', $variables );

		/**
		 * The query should execute, but should contain errors
		 */
		$this->assertArrayNotHasKey( 'errors', $actual );
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertEquals( 'allowedCapsValue', $actual['data']['post']['authCaps'] );
		$this->assertEquals( $this->post, $actual['data']['post']['postId'] );

	}

}