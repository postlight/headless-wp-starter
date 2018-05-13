<?php
namespace WPGraphQL\Type\EditLock;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

class EditLockType extends WPObjectType {

	/**
	 * Holds the $fields definition for the PostObjectType
	 *
	 * @var $fields
	 */
	private static $type_name;

	/**
	 * Holds the post_type_object
	 *
	 * @var object $post_type_object
	 */
	private static $fields;

	/**
	 * EditLockType constructor.
	 */
	public function __construct() {

		self::$type_name = 'EditLock';

		$config = [
			'name' => self::$type_name,
			'description' => __( 'Info on whether the object is locked by another user editing it', 'wp-graphql' ),
			'fields' => function() {
				return self::fields();
			},
		];

		parent::__construct( $config );

	}

	/**
	 * Configures the fields for the EditLock type
	 * @return mixed|null
	 */
	protected static function fields() {

		if ( null === self::$fields ) {

			$fields = [
				'editTime' => [
					'type'        => Types::string(),
					'description' => __( 'The time when the object was last edited', 'wp-graphql' ),
					'resolve'     => function( $edit_lock, array $args, AppContext $context, ResolveInfo $info ) {
						$time = ( is_array( $edit_lock ) && ! empty( $edit_lock[0] ) ) ? $edit_lock[0] : null;

						return ! empty( $time ) ? date( 'Y-m-d H:i:s', $time ) : null;
					},
				],
				'user'     => [
					'type'        => Types::user(),
					'description' => __( 'The user that most recently edited the object', 'wp-graphql' ),
					'resolve'     => function( $edit_lock, array $args, AppContext $context, ResolveInfo $info ) {
						$user_id = ( is_array( $edit_lock ) && ! empty( $edit_lock[1] ) ) ? $edit_lock[1] : null;

						return ! empty( $user_id ) ? DataSource::resolve_user( $user_id ) : null;
					},
				],
			];

			self::$fields = self::prepare_fields( $fields, self::$type_name );

		}

		return ! empty( self::$fields ) ? self::$fields : null;

	}

}
