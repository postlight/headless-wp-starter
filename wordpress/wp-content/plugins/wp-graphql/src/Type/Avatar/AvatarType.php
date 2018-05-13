<?php

namespace WPGraphQL\Type\Avatar;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

/**
 * Class AvatarType
 *
 * @package WPGraphQL\Type
 * @since   0.0.5
 */
class AvatarType extends WPObjectType {

	/**
	 * Holds the type name
	 *
	 * @var string $type_name
	 */
	private static $type_name;

	/**
	 * This holds the field definitions
	 *
	 * @var array $fields
	 * @since 0.0.5
	 */
	private static $fields;

	/**
	 * WPObjectType constructor.
	 *
	 * @since 0.0.5
	 */
	public function __construct() {

		/**
		 * Set the type_name
		 *
		 * @since 0.0.5
		 */
		self::$type_name = 'Avatar';

		$config = [
			'name'        => self::$type_name,
			'fields'      => self::fields(),
			'description' => __( 'Avatars are profile images for users. WordPress by default uses the Gravatar service to host and fetch avatars from.', 'wp-graphql' ),
		];

		parent::__construct( $config );

	}

	/**
	 * fields
	 *
	 * This defines the fields for the AvatarType. The fields are passed through a filter so the shape of the schema
	 * can be modified
	 *
	 * @return array|\GraphQL\Type\Definition\FieldDefinition[]
	 * @since 0.0.5
	 */
	private static function fields() {

		if ( null === self::$fields ) {
			self::$fields = function() {
				$fields = [
					'size'         => [
						'type'        => Types::int(),
						'description' => __( 'The size of the avatar in pixels. A value of 96 will match a 96px x 96px gravatar image.', 'wp-graphql' ),
					],
					'height'       => [
						'type'        => Types::int(),
						'description' => __( 'Height of the avatar image.', 'wp-graphql' ),
					],
					'width'        => [
						'type'        => Types::int(),
						'description' => __( 'Width of the avatar image.', 'wp-graphql' ),
					],
					'default'      => [
						'type'        => Types::string(),
						'description' => __( "URL for the default image or a default type. Accepts '404' (return a 404 instead of a default image), 'retro' (8bit), 'monsterid' (monster), 'wavatar' (cartoon face), 'indenticon' (the 'quilt'), 'mystery', 'mm', or 'mysteryman' (The Oyster Man), 'blank' (transparent GIF), or 'gravatar_default' (the Gravatar logo).", 'wp-graphql' ),
					],
					'forceDefault' => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether to always show the default image, never the Gravatar.', 'wp-graphql' ),
						'resolve'     => function( $avatar, array $args, AppContext $context, ResolveInfo $info ) {
							return ( ! empty( $avatar['force_default'] ) && true === $avatar['force_default'] ) ? true : false;
						},
					],
					'rating'       => [
						'type'        => Types::string(),
						'description' => __( "What rating to display avatars up to. Accepts 'G', 'PG', 'R', 'X', and are judged in that order.", 'wp-graphql' ),
					],
					'scheme'       => [
						'type'        => Types::string(),
						'description' => __( 'Type of url scheme to use. Typically HTTP vs. HTTPS.', 'wp-graphql' ),
					],
					'extraAttr'    => [
						'type'        => Types::string(),
						'description' => __( 'HTML attributes to insert in the IMG element. Is not sanitized.', 'wp-graphql' ),
						'resolve'     => function( $avatar, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $avatar['extra_attr'] ) ? $avatar['extra_attr'] : null;
						},
					],
					'foundAvatar'  => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether the avatar was successfully found.', 'wp-graphql' ),
						'resolve'     => function( $avatar, array $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $avatar['found_avatar'] && true === $avatar['found_avatar'] ) ? true : false;
						},
					],
					'url'          => [
						'type'        => Types::string(),
						'description' => __( 'URL for the gravatar image source.', 'wp-graphql' ),
					],
				];

				/**
				 * This prepares the fields by sorting them and applying a filter for adjusting the schema.
				 * Because these fields are implemented via a closure the prepare_fields needs to be applied
				 * to the fields directly instead of being applied to all objects extending
				 * the WPObjectType class.
				 */
				return self::prepare_fields( $fields, self::$type_name );

			};
		}

		return self::$fields;

	}
}
