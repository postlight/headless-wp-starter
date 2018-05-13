<?php
namespace WPGraphQL\Type\Union;

use GraphQL\Type\Definition\UnionType;
use WPGraphQL\Types;

/**
 * Class PostObjectUnionType
 *
 * In WordPress, relations can be set to a post object by ID, but in many cases there's no strict control over what
 * post_type the related post can be, so in many cases it's unknown what "post_type" the related post is until the post
 * has been queried and returned. Some examples of such relations are:
 *
 * - Posts can have a parent_id set, and the parent_id can be of any post_type, so it's unknown what type of postObject
 * will be returned until the query has been run.
 *
 * - Attachments (mediaItems) can be uploaded to any post_type as well, so their "uploadedTo" property can return any
 * type of post object.
 *
 * @package WPGraphQL\Type\Union
 * @since 0.0.6
 */
class PostObjectUnionType extends UnionType {

	/**
	 * This holds an array of the possible types that can be resolved by this union
	 * @var array
	 * @since 0.0.6
	 */
	private static $possible_types;

	/**
	 * PostObjectUnionType constructor.
	 * @since 0.0.6
	 */
	public function __construct() {

		self::getPossibleTypes();

		$config = [
			'name' => 'PostObjectUnion',
			'types' => self::$possible_types,
			'resolveType' => function( $value ) {
				return ! empty( $value->post_type ) ? Types::post_object( $value->post_type ) : null;
			},
		];

		parent::__construct( $config );

	}

	/**
	 * This defines the possible types that can be resolved by this union
	 *
	 * @return array An array of possible types that can be resolved by the union
	 * @since 0.0.5
	 */
	public function getPossibleTypes() {

		if ( null === self::$possible_types ) {
			self::$possible_types = [];
		}

		$allowed_post_types = \WPGraphQL::$allowed_post_types;
		if ( ! empty( $allowed_post_types ) && is_array( $allowed_post_types ) ) {
			foreach ( $allowed_post_types as $allowed_post_type ) {
				if ( empty( self::$possible_types[ $allowed_post_type ] ) ) {
					self::$possible_types[ $allowed_post_type ] = Types::post_object( $allowed_post_type );
				}
			}
		}

		/**
		 * Filter the possible_types as it's possible some systems might set things like "parent_id" to a different
		 * object than a post_type, and might want to be able to hook in and add a non postObject type to the possible
		 * resolveTypes.
		 *
		 * @param array $possible_types  An array of possible types that can be resolved for the union
		 * @since 0.0.6
		 */
		self::$possible_types = apply_filters( 'graphql_post_object_union_possible_types', self::$possible_types );

		return ! empty( self::$possible_types ) ? self::$possible_types : null;

	}

}
