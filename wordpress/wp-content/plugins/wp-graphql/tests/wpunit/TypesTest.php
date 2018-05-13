<?php

class TypesTest extends \Codeception\TestCase\WPTestCase
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

	public function testMapInput() {

		/**
		 * Testing with invalid input
		 */
		$actual = \WPGraphQL\Types::map_input( 'string', 'another string' );
		$this->assertEquals( [], $actual );

		/**
		 * Setup some args
		 */
		$map = [
			'stringInput' => 'string_input',
			'intInput' => 'int_input',
			'boolInput' => 'bool_input',
			'inputObject' => 'input_object',
		];

		$input_args = [
			'stringInput' => 'value 2',
			'intInput' => 2,
			'boolInput' => false,
		];

		$args = [
			'stringInput' => 'value',
			'intInput' => 1,
			'boolInput' => true,
			'inputObject' => \WPGraphQL\Types::map_input( $input_args, $map ),
		];

		$expected = [
			'string_input' => 'value',
			'int_input' => 1,
			'bool_input' => true,
			'input_object' => [
				'string_input' => 'value 2',
				'int_input' => 2,
				'bool_input' => false,
			],
		];

		$actual = \WPGraphQL\Types::map_input( $args, $map );

		$this->assertEquals( $expected, $actual );

	}

}