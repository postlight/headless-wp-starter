<?php

namespace WPGraphQL\Type\Enum;

use WPGraphQL\Type\WPEnumType;

/**
 * Class MediaItemStatusEnumType
 *
 * This defines an EnumType with allowed post stati for attachments in WordPress.
 * Attachments do not have the same status capabilities as other post types, see here
 * for reference: https://github.com/WordPress/WordPress/blob/master/wp-includes/post.php#L3072
 *
 * @package WPGraphQL\Type\Enum
 */
class MediaItemStatusEnumType extends WPEnumType {

	/**
	 * This holds the enum values array
	 *
	 * @var array $values
	 */
	private static $values;

	public function __construct() {
		$config = [
			'name'        => 'MediaItemStatus',
			'description' => __( 'The status of the media item object.', 'wp-graphql' ),
			'values'      => self::values(),
		];
		parent::__construct( $config );
	}

	/**
	 * values
	 * Creates a list of post_stati that can be used to query by.
	 *
	 * @return array
	 */
	private static function values() {

		/**
		 * Set the default, if no values are built dynamically
		 *
		 */
		self::$values = [
			'INHERIT' => [
				'value' => 'inherit',
			],
		];

		/**
		 * Get the dynamic list of post_stati
		 */
		$post_stati = [
			'inherit',
			'private',
			'trash',
			'auto-draft',
		];

		/**
		 * If there are $post_stati, create the $values based on them
		 */
		if ( ! empty( $post_stati ) && is_array( $post_stati ) ) {
			/**
			 * Reset the array
			 */
			self::$values = [];
			/**
			 * Loop through the post_stati
			 */
			foreach ( $post_stati as $status ) {

				$formatted_status = strtoupper( preg_replace( '/[^A-Za-z0-9]/i', '_', $status ) );

				self::$values[ $formatted_status ] = [
					'name'        => $formatted_status,
					'description' => sprintf( __( 'Objects with the %1$s status', 'wp-graphql' ), $status ),
					'value'       => $status,
				];
			}
		}

		/**
		 * Return the $values
		 */
		return self::$values;

	}

}
