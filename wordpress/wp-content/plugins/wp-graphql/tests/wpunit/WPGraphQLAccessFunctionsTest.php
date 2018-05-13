<?php

class WPGraphQLAccessFunctionsTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Tests the access function that is available to format strings to GraphQL friendly format
	 */
	public function testGraphQLFormatFieldName() {

		$actual   = graphql_format_field_name( 'This is some field name' );
		$expected = 'thisIsSomeFieldName';

		$this->assertEquals( $expected, $actual );

	}

}