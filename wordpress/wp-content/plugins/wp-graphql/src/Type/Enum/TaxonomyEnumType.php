<?php

namespace WPGraphQL\Type\Enum;

use WPGraphQL\Type\WPEnumType;

class TaxonomyEnumType extends WPEnumType {

	/**
	 * This holds the enum values array
	 *
	 * @var array $values
	 */
	private static $values;

	/**
	 * TaxonomyEnumType constructor.
	 *
	 * @since 0.0.5
	 */
	public function __construct() {
		$config = [
			'name'        => 'TaxonomyEnum',
			'description' => __( 'Allowed taxonomies', 'wp-graphql' ),
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

			/**
			 * Set an empty array
			 */
			self::$values = [];

			/**
			 * Get the allowed taxonomies
			 */
			$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies();

			/**
			 * Loop through the taxonomies and create an array
			 * of values for use in the enum type.
			 */
			foreach ( $allowed_taxonomies as $taxonomy ) {

				$formatted_taxonomy = strtoupper( get_taxonomy( $taxonomy )->graphql_single_name );

				self::$values[ $formatted_taxonomy ] = [
					'value' => $taxonomy,
				];
			}
		}

		/**
		 * Return the $values
		 */
		return self::$values;

	}

}
