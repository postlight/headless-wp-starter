<?php

namespace WPGraphQL\Type\Settings;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

/**
 * Class SettingsType
 *
 * This sets up the base settings Type for settings queries and mutations
 *
 * @package WPGraphQL\Type\Settings
 */
class SettingsType extends WPObjectType {

	/**
	 * Holds the type name
	 *
	 * @var string $type_name
	 */
	private static $type_name;

	/**
	 * Holds the $fields definition for the SettingsType
	 *
	 * @var array $fields
	 * @access private
	 */
	private static $fields;

	/**
	 * SettingsType constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		/**
		 * Set the type_name
		 *
		 * @since 0.0.5
		 */
		self::$type_name = 'Settings';

		/**
		 * Retrieve all of the allowed settings
		 */
		$settings_array = DataSource::get_allowed_settings();

		$config = [
			'name'        => self::$type_name,
			'fields'      => self::fields( $settings_array ),
			'description' => __( 'All of the registered settings', 'wp-graphql' ),
		];

		parent::__construct( $config );

	}

	/**
	 * This defines the fields for the settings type
	 *
	 * @param $settings_array
	 *
	 * @access private
	 * @return \GraphQL\Type\Definition\FieldDefinition|mixed|null
	 */
	private static function fields( $settings_array ) {

		/**
		 * Define $fields
		 */
		$fields = [];

		if ( ! empty( $settings_array ) && is_array( $settings_array ) ) {

			/**
			 * Loop through the $settings_array and build the setting with
			 * proper fields
			 */
			foreach ( $settings_array as $key => $setting_field ) {

				/**
				 * Determine if the individual setting already has a
				 * REST API name, if not use the option name.
				 * Then, sanitize the field name to be camelcase
				 */
				if ( ! empty( $setting_field['show_in_rest']['name'] ) ) {
					$field_key = $setting_field['show_in_rest']['name'];
				} else {
					$field_key = $key;
				}
				$field_key = lcfirst( $setting_field['group'] . 'Settings' . str_replace( '_', '', ucwords( $field_key, '_' ) ) );

				if ( ! empty( $key ) && ! empty( $field_key ) ) {

					/**
					 * Dynamically build the individual setting and it's fields
					 * then add it to $fields
					 */
					$fields[ $field_key ] = [
						'type'        => Types::get_type( $setting_field['type'] ),
						'description' => $setting_field['description'],

						'resolve'     => function( $root, $args, AppContext $context, ResolveInfo $info ) use ( $setting_field, $field_key, $key ) {
							/**
							 * Check to see if the user querying the email field has the 'manage_options' capability
							 * All other options should be public by default
							 */
							if ( 'admin_email' === $key && ! current_user_can( 'manage_options' ) ) {
								throw new UserError( __( 'Sorry, you do not have permission to view this setting.', 'wp-graphql' ) );
							}

							$option = ! empty( $key ) ? get_option( $key ) : null;

							switch ( $setting_field['type'] ) {
								case 'integer':
									$option = absint( $option );
									break;
								case 'string':
									$option = (string) $option;
									break;
								case 'boolean':
									$option = (boolean) $option;
									break;
								case 'number':
									$option = (float) $option;
									break;
							}

							return $option;
						},
					];

				}

			}

			/**
			 * Pass the fields through a filter to allow for hooking in and adjusting the shape
			 * of the type's schema
			 */
			self::$fields = self::prepare_fields( $fields, self::$type_name );

		}

		return ! empty( self::$fields ) ? self::$fields : null;

	}

}
