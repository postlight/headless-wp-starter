<?php

namespace WPGraphQL\Type\Enum;

use GraphQL\Type\Definition\EnumType;

/**
 * Class PostTypeEnumType
 *
 * @package WPGraphQL\Type\Enum
 */
class PostTypeEnumType extends EnumType {

	/**
	 * Holds the values to be used for the Enum
	 * @var array $values
	 */
	private static $values;

	/**
	 * PostTypeEnumType constructor.
	 */
	public function __construct() {

		$config = [
			'name'        => 'PostTypeEnum',
			'description' => __( 'Allowed Post Types', 'wp-graphql' ),
			'values'      => self::values(),
		];

		parent::__construct( $config );

	}

	/**
	 * This returns an array of values to be used by the Enum
	 * @return array|null
	 */
	private static function values() {

		if ( null === self::$values ) {

			/**
			 * Set an empty array
			 */
			self::$values = [];

			/**
			 * Get the allowed taxonomies
			 */
			$allowed_post_types = \WPGraphQL::get_allowed_post_types();

			/**
			 * Loop through the taxonomies and create an array
			 * of values for use in the enum type.
			 */
			foreach ( $allowed_post_types as $post_type ) {

				$formatted_post_type = strtoupper( get_post_type_object( $post_type )->graphql_single_name );

				self::$values[ $formatted_post_type ] = [
					'value' => $post_type,
				];
			}
		}

		/**
		 * Return the $values
		 */
		return ! empty( self::$values ) ? self::$values : null;

	}

}
