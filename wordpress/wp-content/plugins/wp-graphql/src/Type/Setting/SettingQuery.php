<?php
namespace WPGraphQL\Type\Setting;

use WPGraphQL\Types;

/**
 * Class SettingQuery
 *
 * @package WPGraphQL\Type\Setting
 */
class SettingQuery {

	/**
	 * Holds the root_query field definition
	 *
	 * @var array $root_query
	 * @access private
	 */
	private static $root_query;

	/**
	 * Method that returns the root query field definition
	 * for the requested setting type
	 *
	 * @access public
	 * @param string $group
	 * @param array $setting_type
	 *
	 * @return array $root_query
	 */
	public static function root_query( $group, $setting_type ) {

		if ( null === self::$root_query ) {
			self::$root_query = [];
		}

		if ( ! empty( $setting_type ) && empty( self::$root_query[ $group ] ) ) {
			self::$root_query = [
				'type'        => Types::setting( $group ),
				'resolve'     => function () use ( $setting_type ) {
					return $setting_type;
				},
			];

		}

		return self::$root_query;
	}
}
