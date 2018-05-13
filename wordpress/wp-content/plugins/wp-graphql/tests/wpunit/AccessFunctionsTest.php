<?php

class AccessFunctionsTest extends \Codeception\TestCase\WPTestCase
{

    public function setUp()
    {
        // before
        parent::setUp();

        // your set up methods here
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    // tests
    public function testMe()
    {
	    $actual = graphql_format_field_name( 'This is some field name' );
	    $expected = 'thisIsSomeFieldName';
	    self::assertEquals( $expected, $actual );
    }

}