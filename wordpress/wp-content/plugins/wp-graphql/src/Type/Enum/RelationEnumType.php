<?php

namespace WPGraphQL\Type\Enum;

use WPGraphQL\Type\WPEnumType;

/**
 * Class RelationEnumType
 *
 * This defines an EnumType with allowed relations for use in various query args.
 *
 * @package WPGraphQL\Type\Enum
 * @since   0.0.5
 */
class RelationEnumType extends WPEnumType {

	/**
	 * This holds the enum values array
	 *
	 * @var array $values
	 */
	private static $values;

	/**
	 * RelationEnumType constructor.
	 *
	 * @since 0.0.5
	 */
	public function __construct() {
		$config = [
			'name'        => 'RelationEnum',
			'description' => __( 'The logical relation between each item in the array when there are more than one.', 'wp-graphql' ),
			'values'      => self::values(),
		];
		parent::__construct( $config );

	}

	/**
	 * values
	 * Returns the values to be used in the Enum
	 *
	 * @return array|null
	 */
	private static function values() {

		if ( null === self::$values ) {

			self::$values = [
				'AND' => [
					'name'  => 'AND',
					'value' => 'AND',
				],
				'OR'  => [
					'name'  => 'OR',
					'value' => 'OR',
				],
			];

		}

		return self::$values;

	}

}
