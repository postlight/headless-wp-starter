<?php

namespace WPGraphQL\Data;

use GraphQL\Error\UserError;

/**
 * Class Loader
 *
 * This class sets up general patterns for loading data in an optimized way. Used in conjunction with GraphQL Deferred
 * resolvers
 *
 * @package WPGraphQL\Data
 */
class Loader {

	/**
	 * Holds the queue of items to be loaded
	 *
	 * @var array $buffer
	 * @access protected
	 */
	protected static $buffer = [];

	/**
	 * Holds the collection of items that have already been loaded
	 *
	 * @var array $loaded
	 * @access protected
	 */
	protected static $loaded = [];

	/**
	 * Add an item to the buffer
	 *
	 * @param string  $type The type of object to add
	 * @param integer $id   The ID of the item to be loaded
	 *
	 * @access public
	 */
	public static function addOne( $type, $id ) {

		if ( empty( self::$buffer[ $type ] ) ) {
			self::$buffer[ $type ] = [];
		}

		if ( ! in_array( $id, self::$buffer[ $type ], true ) ) {
			array_push( self::$buffer[ $type ], absint( $id ) );
		}

	}

	/**
	 * Add many items to the buffer
	 *
	 * @param string $type The type of objects to add
	 * @param array  $ids  Array of IDs to be added to the buffer
	 *
	 * @access public
	 */
	public static function addMany( $type, array $ids ) {
		if ( ! empty( $ids ) && is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				self::addOne( $type, $id );
			}
		}
	}

	/**
	 * Load an individual item from the loaded items
	 *
	 * @param string $type The type of object to load
	 * @param int    $id   The ID of the item to load
	 *
	 * @return mixed
	 * @access public
	 */
	public static function loadOne( $type, $id ) {
		$loaded = ! empty( self::$loaded[ $type ][ $id ] ) ? self::$loaded[ $type ][ $id ] : null;

		if ( ! empty( $loaded ) ) {
			return $loaded;
		} else {
			throw new UserError( sprintf( __( 'No %1$s was found with the provided ID', 'wp-graphql' ), $type, $id ) );
		}
	}

	/**
	 * Load many items from the already loaded items
	 *
	 * @param string $type The type of objects to load
	 * @param array  $ids  Array of items to load
	 *
	 * @access public
	 */
	public static function loadMany( $type, array $ids ) {
		$load = [];
		if ( ! empty( $ids ) && is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				$load[ $type ][] = self::$loaded[ $id ];
			}
		}
	}

	/**
	 * Should be implemented by extending loader
	 *
	 * @param string $type The type of objects to load
	 *
	 * @return array
	 * @access public
	 */
	public static function loadBuffered( $type ) {

		switch ( $type ) {
			case 'user':
				return self::load_users();
				break;
			default:
				return [];
		}

	}

	/**
	 * Loads users from the buffer
	 *
	 * @return mixed
	 * @access protected
	 */
	protected static function load_users() {

		$type = 'user';

		if ( ! empty( self::$buffer[ $type ] ) ) {
			$query = new \WP_User_Query( [
				'include'     => self::$buffer[ $type ],
				'orderby'     => 'include',
				'count_total' => false,
				'fields'      => 'all_with_meta'
			] );
			if ( ! empty( $query->get_results() ) && is_array( $query->get_results() ) ) {
				foreach ( $query->get_results() as $user ) {
					if ( $user instanceof \WP_User ) {
						self::$loaded[ $type ][ $user->ID ] = $user;
					}
				}
			}
		}

		self::reset_buffer( $type );

		return ! empty( self::$loaded[ $type ] ) ? self::$loaded[ $type ] : [];
	}

	/**
	 * Resets the buffer for a given type
	 *
	 * @param string $type The buffer type to reset
	 *
	 * @access protected
	 */
	protected static function reset_buffer( $type ) {
		self::$buffer[ $type ] = [];
	}


}