<?php

namespace WPGraphQL\Type\Union;

use GraphQL\Type\Definition\UnionType;
use WPGraphQL\Types;

/**
 * Class CommentAuthorUnionType
 *
 * In some situations, the type of term cannot be known until query time. The commentAuthorUnion allows for a
 * comment author to be queried and resolved to a number of types. Currently will return a user or commentAuthor.
 *
 * @package WPGraphQL\Type\Union
 */
class CommentAuthorUnionType extends UnionType {

	/**
	 * This holds an array of the possible types that can be resolved by this union
	 *
	 * @var array
	 */
	private static $possible_types;

	/**
	 * CommentAuthorUnionType constructor.
	 */
	public function __construct() {

		self::getPossibleTypes();

		$config = [
			'name'        => 'CommentAuthorUnion',
			'types'       => self::$possible_types,
			'resolveType' => function( $source ) {
				if ( $source instanceof \WP_User ) {
					$type = Types::user();
				} else {
					$type = Types::comment_author();
				}
				return $type;
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

		self::$possible_types = [
			'user'          => Types::user(),
			'commentAuthor' => Types::comment_author(),
		];

		/**
		 * Filter the possible_types as it's possible some systems might set things like "parent_id" to a different
		 * object than a post_type, and might want to be able to hook in and add a non postObject type to the possible
		 * resolveTypes.
		 *
		 * @param array $possible_types An array of possible types that can be resolved for the union
		 *
		 * @since 0.0.6
		 */
		self::$possible_types = apply_filters( 'graphql_comment_author_union_possible_types', self::$possible_types );

		return ! empty( self::$possible_types ) ? self::$possible_types : null;

	}

}
