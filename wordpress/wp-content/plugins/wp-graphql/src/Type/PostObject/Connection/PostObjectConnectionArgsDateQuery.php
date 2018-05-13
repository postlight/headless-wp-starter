<?php
namespace WPGraphQL\Type\PostObject\Connection;

use GraphQL\Type\Definition\EnumType;
use WPGraphQL\Type\WPEnumType;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

/**
 * Class PostObjectConnectionArgsDateQuery
 *
 * This defines the input fields for date queries
 *
 * @package WPGraphQL\Type
 * @since 0.0.5
 */
class PostObjectConnectionArgsDateQuery extends WPInputObjectType {

	/**
	 * This holds the field definitions
	 * @var array $fields
	 * @since 0.0.5
	 */
	public static $fields;

	/**
	 * Holds the date_after input object definition
	 * @var WPInputObjectType
	 * @since 0.0.5
	 */
	private static $date_after;

	/**
	 * Holds the date_before input object definition
	 * @var WPInputObjectType
	 * @since 0.0.5
	 */
	private static $date_before;

	/**
	 * Holds the column_enum EnumType definition
	 * @var EnumType
	 * @since 0.0.5
	 */
	private static $column_enum;

	/**
	 * DateQueryType constructor.
	 * @since 0.0.5
	 */
	public function __construct( $config = [] ) {
		$config['name'] = 'DateQuery';
		$config['fields'] = self::fields();
		parent::__construct( $config );
	}

	/**
	 * fields
	 *
	 * This defines the fields that make up the DateQueryType
	 *
	 * @return array|null
	 * @since 0.0.5
	 */
	private static function fields() {

		if ( null === self::$fields ) {
			self::$fields = [
				'year'      => [
					'type'        => Types::int(),
					'description' => __( '4 digit year (e.g. 2017)', 'wp-graphql' ),
				],
				'month'     => [
					'type'        => Types::int(),
					'description' => __( 'Month number (from 1 to 12)', 'wp-graphql' ),
				],
				'week'      => [
					'type'        => Types::int(),
					'description' => __( 'Week of the year (from 0 to 53)', 'wp-graphql' ),
				],
				'day'       => [
					'type'        => Types::int(),
					'description' => __( 'Day of the month (from 1 to 31)', 'wp-graphql' ),
				],
				'hour'      => [
					'type'        => Types::int(),
					'description' => __( 'Hour (from 0 to 23)', 'wp-graphql' ),
				],
				'minute'    => [
					'type'        => Types::int(),
					'description' => __( 'Minute (from 0 to 59)', 'wp-graphql' ),
				],
				'second'    => [
					'type'        => Types::int(),
					'description' => __( 'Second (0 to 59)', 'wp-graphql' ),
				],
				'after'     => [
					'type' => self::date_after(),
				],
				'before'    => [
					'type' => self::date_before(),
				],
				'inclusive' => [
					'type'        => Types::boolean(),
					'description' => __( 'For after/before, whether exact value should be matched or not', 'wp-graphql' ),
				],
				'compare'   => [
					'type'        => Types::string(),
					'description' => __( 'For after/before, whether exact value should be matched or not', 'wp-graphql' ),
				],
				'column'    => [
					'type'        => self::column_enum(),
					'description' => __( 'Column to query against', 'wp-graphql' ),
				],
				'relation'  => [
					'type'        => Types::relation_enum(),
					'description' => __( 'OR or AND, how the sub-arrays should be compared', 'wp-graphql' ),
				],
			];
		}
		return self::prepare_fields( self::$fields, 'DateQuery' );
	}

	/**
	 * column_enum
	 * Creates an Enum type with the columns that can be queried against for the DateQuery
	 * @return EnumType|null
	 * @since 0.0.5
	 */
	private static function column_enum() {

		if ( null === self::$column_enum ) {
			self::$column_enum = new WPEnumType( [
				'name'   => 'DateColumn',
				'values' => [
					'DATE'     => [
						'value' => 'post_date',
					],
					'MODIFIED' => [
						'value' => 'post_modified',
					],
				],
			] );
		}
		return self::$column_enum;
	}

	/**
	 * date_after
	 * Creates the date_after input field that allows "after" paramaters
	 * to be entered
	 * @return WPInputObjectType|null
	 * @since 0.0.5
	 */
	private static function date_after() {
		if ( null === self::$date_after ) {
			self::$date_after = new WPInputObjectType( [
				'name'   => 'DateAfter',
				'fields' => self::prepare_fields( [
					'year'  => [
						'type'        => Types::int(),
						'description' => __( '4 digit year (e.g. 2017)', 'wp-graphql' ),
					],
					'month' => [
						'type'        => Types::int(),
						'description' => __( 'Month number (from 1 to 12)', 'wp-graphql' ),
					],
					'day'   => [
						'type'        => Types::int(),
						'description' => __( 'Day of the month (from 1 to 31)', 'wp-graphql' ),
					],
				], 'DateAfter' ),
			] );
		}
		return self::$date_after;
	}

	/**
	 * date_before
	 * Creates the date_before input field that allows "before" paramaters
	 * to be entered
	 * @return WPInputObjectType|null
	 * @since 0.0.5
	 */
	private static function date_before() {

		if ( null === self::$date_before ) {
			self::$date_before = new WPInputObjectType( [
				'name'   => 'DateBefore',
				'fields' => self::prepare_fields( [
					'year'  => [
						'type'        => Types::int(),
						'description' => __( '4 digit year (e.g. 2017)', 'wp-graphql' ),
					],
					'month' => [
						'type'        => Types::int(),
						'description' => __( 'Month number (from 1 to 12)', 'wp-graphql' ),
					],
					'day'   => [
						'type'        => Types::int(),
						'description' => __( 'Day of the month (from 1 to 31)', 'wp-graphql' ),
					],
				], 'DateBefore' ),
			] );
		}
		return self::$date_before;
	}

}
