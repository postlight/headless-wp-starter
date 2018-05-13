<?php
namespace WPGraphQL\Type\Settings;

use WPGraphQL\Types;

/**
 * Class SettingsQuery
 *
 * @package WPGraphQL\Type\Settings
 */
class SettingsQuery {

	/**
	 * Holds the root_query field definition
	 *
	 * @var array $root_query
	 * @access private
	 */
	private static $root_query;

	/**
	 * Method that returns the root query field definition
	 * for all settings
	 *
	 * @access public
	 *
	 * @return array $root_query
	 */
	public static function root_query() {

		if ( null === self::$root_query ) {
			self::$root_query = [];
		}

		self::$root_query = [
			'type'        => Types::settings(),
			'resolve'     => function () {
				return true;
			},
		];

		return self::$root_query;
	}
}
