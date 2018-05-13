<?php
namespace WPGraphQL\Type\Enum;

use WPGraphQL\Type\WPEnumType;

/**
 * Class PostObjectFieldFormatEnumType
 *
 * This defines an EnumType with allowed formats of post field data.
 *
 * @package WPGraphQL\Type\Enum
 * @since   0.0.18
 */
class PostObjectFieldFormatEnumType extends WPEnumType {

	/**
	 * This holds the enum values array.
	 *
	 * @var array $values
	 */
	private static $values;

	public function __construct() {
		$config = [
			'name'        => 'PostObjectFieldFormat',
			'description' => __( 'The format of post field data.', 'wp-graphql' ),
			'values'      => self::values(),
		];
		parent::__construct( $config );
	}

	/**
	 * Creates a list of formats of post field data.
	 *
	 * @return array
	 */
	private static function values() {

		if ( null === self::$values ) {

			/**
			 * Post object field formats.
			 *
			 * @since 0.0.18
			 */
			self::$values = [
				'RAW' => [
					'name'  => 'RAW',
					'description' => __( 'Provide the field value directly from database', 'wp-graphql' ),
					'value' => 'raw',
				],
				'RENDERED' => [
					'name'  => 'RENDERED',
					'description' => __( 'Apply the default WordPress rendering', 'wp-graphql' ),
					'value' => 'rendered',
				],
			];

		}

		/**
		 * Return the $values
		 */
		return self::$values;
	}
}
