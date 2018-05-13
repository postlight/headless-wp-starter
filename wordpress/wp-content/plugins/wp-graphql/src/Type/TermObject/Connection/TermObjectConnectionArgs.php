<?php
namespace WPGraphQL\Type\TermObject\Connection;

use WPGraphQL\Type\WPEnumType;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

/**
 * Class TermObjectConnectionArgs
 *
 * This sets up the Query Args for term object connections, which uses get_terms, so this defines the allowed
 * input fields that will be passed to get_terms
 *
 * @package WPGraphQL\Type
 * @since 0.0.5
 */
class TermObjectConnectionArgs extends WPInputObjectType {

	/**
	 * This holds the field definitions
	 * @var array $fields
	 * @since 0.0.5
	 */
	public static $fields = [];

	/**
	 * Holds the orderby enum definition
	 * @var $orderby_enum
	 */
	protected static $orderby_enum;

	/**
	 * TermObjectConnectionArgs constructor.
	 *
	 * @param array $config Array of config details for the Input Object Type
	 * @param string $connection The name of the connection the args belong to
	 *
	 * @since 0.0.5
	 */
	public function __construct( $config = [], $connection ) {
		$config['name'] = ucfirst( $connection ) . 'TermArgs';
		$config['queryClass'] = 'WP_Term_Query';
		$config['fields'] = self::fields( $connection );
		parent::__construct( $config );
	}

	/**
	 * fields
	 *
	 * This defines the fields that make up the TermObjectConnectionArgs
	 *
	 * @param string $connection The name of the connection that the fields args belong to
	 * @return array
	 * @since 0.0.5
	 */
	private static function fields( $connection ) {

		if ( empty( self::$fields[ $connection ] ) ) {
			self::$fields[ $connection ] = [
				'objectIds'           => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of object IDs. Results will be limited to terms associated with these objects.', 'wp-graphql' ),
				],
				'orderby'             => [
					'type'        => self::orderby_enum(),
					'description' => __( 'Field(s) to order terms by. Defaults to \'name\'.', 'wp-graphql' ),
				],
				'hideEmpty'           => [
					'type'        => Types::boolean(),
					'description' => __( 'Whether to hide terms not assigned to any posts. Accepts true or false. Default true', 'wp-graphql' ),
				],
				'include'             => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of term ids to include. Default empty array.', 'wp-graphql' ),
				],
				'exclude'             => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of term ids to exclude. If $include is non-empty, $exclude is ignored. Default empty array.', 'wp-graphql' ),
				],
				'excludeTree'         => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of term ids to exclude along with all of their descendant terms. If $include is non-empty, $exclude_tree is ignored. Default empty array.', 'wp-graphql' ),
				],
				'name'                => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'Array of names to return term(s) for. Default empty.', 'wp-graphql' ),
				],
				'slug'                => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'Array of slugs to return term(s) for. Default empty.', 'wp-graphql' ),
				],
				'termTaxonomId'       => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of term taxonomy IDs, to match when querying terms.', 'wp-graphql' ),
				],
				'hierarchical'        => [
					'type'        => Types::boolean(),
					'description' => __( 'Whether to include terms that have non-empty descendants (even if $hide_empty is set to true). Default true.', 'wp-graphql' ),
				],
				'search'              => [
					'type'        => Types::string(),
					'description' => __( 'Search criteria to match terms. Will be SQL-formatted with wildcards before and after. Default empty.', 'wp-graphql' ),
				],
				'nameLike'            => [
					'type'        => Types::string(),
					'description' => __( 'Retrieve terms with criteria by which a term is LIKE `$name__like`. Default empty.', 'wp-graphql' ),
				],
				'descriptionLike'     => [
					'type'        => Types::string(),
					'description' => __( 'Retrieve terms where the description is LIKE `$description__like`. Default empty.', 'wp-graphql' ),
				],
				'padCounts'           => [
					'type'        => Types::boolean(),
					'description' => __( 'Whether to pad the quantity of a term\'s children in the quantity of each term\'s "count" object variable. Default false.', 'wp-graphql' ),
				],
				'childOf'             => [
					'type'        => Types::int(),
					'description' => __( 'Term ID to retrieve child terms of. If multiple taxonomies are passed, $child_of is ignored. Default 0.', 'wp-graphql' ),
				],
				'parent'              => [
					'type'        => Types::int(),
					'description' => __( 'Parent term ID to retrieve direct-child terms of. Default empty.', 'wp-graphql' ),
				],
				'childless'           => [
					'type'        => Types::boolean(),
					'description' => __( 'True to limit results to terms that have no children. This parameter has no effect on non-hierarchical taxonomies. Default false.', 'wp-graphql' ),
				],
				'cacheDomain'         => [
					'type'        => Types::string(),
					'description' => __( 'Unique cache key to be produced when this query is stored in an object cache. Default is \'core\'.', 'wp-graphql' ),
				],
				'updateTermMetaCache' => [
					'type'        => Types::boolean(),
					'description' => __( 'Whether to prime meta caches for matched terms. Default true.', 'wp-graphql' ),
				],
			];

			/**
			 * Add these fields to non-root connections
			 *
			 * @todo: possibly consider only adding these args to certain connections, like non-Root connections?
			 */
			self::$fields[ $connection ]['shouldOnlyIncludeConnectedItems'] = [
				'type'        => Types::boolean(),
				'description' => __( 'Default false. If true, only the items connected to the source item will be returned. If false, all items will be returned regardless of connection to the source', 'wp-graphql' ),
			];

			self::$fields[ $connection ]['shouldOutputInFlatList'] = [
				'type'        => Types::boolean(),
				'description' => __( 'Default false. If true, the connection will be output in a flat list instead of the hierarchical list. So child terms will be output in the same level as the parent terms', 'wp-graphql' ),
			];

		}

		return ! empty( self::$fields[ $connection ] ) ? self::prepare_fields( self::$fields[ $connection ], ucfirst( $connection ) . 'TermArgs' ) : null;
	}

	/**
	 * Sets up the definition of the TermsOrderby enum
	 * @return null|WPEnumType
	 */
	protected static function orderby_enum() {

		if ( null === self::$orderby_enum ) {

			self::$orderby_enum = new WPEnumType( [
				'name'   => 'TermsOrderby',
				'values' => [
					'NAME'        => [
						'value' => 'name',
					],
					'SLUG'        => [
						'value' => 'slug',
					],
					'TERM_GROUP'  => [
						'value' => 'term_group',
					],
					'TERM_ID'     => [
						'value' => 'term_id',
					],
					'TERM_ORDER'  => [
						'value' => 'term_order',
					],
					'DESCRIPTION' => [
						'value' => 'description',
					],
					'COUNT'       => [
						'value' => 'count',
					],
				],
			] );

		}

		return ! empty( self::$orderby_enum ) ? self::$orderby_enum : null;
	}

}
