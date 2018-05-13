<?php
namespace WPGraphQL\Type\Union;

use GraphQL\Type\Definition\UnionType;
use WPGraphQL\Types;

/**
 * Class TermObjectUnionType
 *
 * In some situations, the type of term cannot be known until query time. The termObjectUnion allows for connections to
 * be queried and resolved to a number of types.
 *
 * @package WPGraphQL\Type\Union
 */
class TermObjectUnionType extends UnionType {

	/**
	 * This holds an array of the possible types that can be resolved by this union
	 * @var array
	 */
	private static $possible_types;

	/**
	 * TermObjectUnionType constructor.
	 */
	public function __construct() {

		self::getPossibleTypes();

		$config = [
			'name' => 'TermObjectUnion',
			'types' => self::$possible_types,
			'resolveType' => function( $value ) {
				return ! empty( $value->taxonomy ) ? Types::term_object( $value->taxonomy ) : null;
			},
		];

		parent::__construct( $config );

	}

	/**
	 * This defines the possible types that can be resolved by this union
	 *
	 * @return array|null An array of possible types that can be resolved by the union
	 */
	public function getPossibleTypes() {

		if ( null === self::$possible_types ) {
			self::$possible_types = [];
		}

		$allowed_taxonomies = \WPGraphQL::$allowed_taxonomies;
		if ( ! empty( $allowed_taxonomies ) && is_array( $allowed_taxonomies ) ) {
			foreach ( $allowed_taxonomies as $allowed_taxonomy ) {
				if ( empty( self::$possible_types[ $allowed_taxonomy ] ) ) {
					self::$possible_types[ $allowed_taxonomy ] = Types::term_object( $allowed_taxonomy );
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
		self::$possible_types = apply_filters( 'graphql_term_object_union_possible_types', self::$possible_types );

		return ! empty( self::$possible_types ) ? self::$possible_types : null;

	}

}
