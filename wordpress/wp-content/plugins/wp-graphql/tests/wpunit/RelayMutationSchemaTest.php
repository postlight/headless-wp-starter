<?php

class RelayMutationSchemaTest extends \Codeception\TestCase\WPTestCase {

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
	 * This tests to make sure the mutation schema follows the Relay spec
	 *
	 * @see: https://facebook.github.io/relay/graphql/mutations.htm#sec-Introspection
	 */
	public function testRelayMutationSchema() {

		$introspection_query = '
		{
		  __schema {
		    mutationType {
		      fields {
		        type {
		          kind
		          fields {
		            name
		            type {
		              kind
		              ofType {
		                name
		                kind
		              }
		            }
		          }
		        }
		        args {
		          name
		          type {
		            kind
		            ofType {
		              kind
		              inputFields {
		                name
		                type {
		                  kind
		                  ofType {
		                    name
		                    kind
		                  }
		                }
		              }
		            }
		          }
		        }
		      }
		    }
		  }
		}
		';

		/**
		 * Run the introspection query
		 */
		$actual = do_graphql_request( $introspection_query );

		/**
		 * Get the mutationType fields out of the response tree
		 */
		$mutation_type_fields = ! empty( $actual['data']['__schema']['mutationType']['fields'] ) ? $actual['data']['__schema']['mutationType']['fields'] : null;

		/**
		 * Verify that the $mutation_type_fields is not empty
		 */
		$this->assertNotEmpty( $mutation_type_fields );

		/**
		 * If the fields are a populated array
		 */
		if ( ! empty( $mutation_type_fields ) && is_array( $mutation_type_fields ) ) {

			/**
			 * Loop through the fields to ensure they have fields of their own
			 */
			foreach ( $mutation_type_fields as $mutation_type_field ) {

				$type = ! empty( $mutation_type_field['type'] ) ? $mutation_type_field['type'] : null;

				/**
				 * All mutations should declare a Type
				 */
				$this->assertNotEmpty( $type );

				/**
				 * All types should have a "kind"
				 */
				$this->assertArrayHasKey( 'kind', $type );

				/**
				 * All rootMutations should be Object types
				 */
				$this->assertEquals( $type['kind'], 'OBJECT' );

				/**
				 * All rootMutations should have fields
				 */
				$this->assertNotEmpty( $type['fields'] );

				/**
				 * All rootMutations should have a clientMutationId field
				 */
				$this->assertTrue( $this->checkIfClientMutationIdExists( $type['fields'] ) );
			}
		}

	}

	/**
	 * This is a helper that searches an array to see if any nested items contain the "name" attribute
	 * with a value of "clientMutationId"
	 *
	 * @param $array
	 *
	 * @return bool
	 */
	public function checkIfClientMutationIdExists( $array ) {

		$this->assertNotEmpty( $array );

		foreach ( $array as $item ) {
			if ( 'clientMutationId' === $item['name'] ) {
				return true;
			}
		}

		return false;
	}

}