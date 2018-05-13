<?php

use WPGraphQL\Data\Config;

class DataConfigTest extends \Codeception\TestCase\WPTestCase {
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		if ( ! defined( 'GRAPHQL_REQUEST' ) ) {
			define( 'GRAPHQL_REQUEST', true );
		}
	}

	/**
	 * Create n posts, making sure that publish dates are offset by at least one
	 * second. The posts can either simulate being published in the same order as
	 * the post IDs (e.g., 1, 2, 3, ... 10) or out of order (e.g., 10, 9, ... 1).
	 */
	private function create_posts( $count, $operator, $offset_multiplier = 1 ) {
		$iterable = array_keys( array_fill( 0, $count, null ) );

		// Make sure starting timestamp is sufficiently in the past so that posts
		// do not receive a post_status of "future".
		$timestamp = time() - 1000;

		$posts = array_map( function ( $offset ) use ( $timestamp, $offset_multiplier ) {
			return $this->factory->post->create_and_get(
				array(
					'post_date' => date( 'Y-m-d H:i:s', $timestamp + ( $offset * $offset_multiplier ) ),
				)
			);
		}, $iterable );

		// Sort posts either ASC (">") or DESC ("<") by post_date.
		usort( $posts, function ( $one, $two ) use ( $operator ) {
			if ( '<' === $operator ) {
				return strcmp( $two->post_date, $one->post_date );
			}

			return strcmp( $one->post_date, $two->post_date );
		} );

		return $posts;
	}

	/**
	 * Data provider for testGraphqlWpQueryCursorPaginationSupportMethod
	 */
	public function get_create_posts_args() {
		return array(
			array( '<', 1 ),
			array( '<', -1 ),
			array( '>', 1 ),
			array( '>', -1 ),
		);
	}

	/**
	 * Tests WP_Query pagination support.
	 *
	 * @dataProvider get_create_posts_args
	 */
	public function testGraphqlWpQueryCursorPaginationSupportMethod( $operator, $offset_multiplier ) {
		$posts = $this->create_posts( 15, $operator, $offset_multiplier );

		// Simulate a GraphQL request for:
		// posts(
		//   after: '[id of tenth post]',
		//   first: 10
		// )
		$query = new WP_Query(
			array(
				'graphql_cursor_offset' => $posts[9]->ID,
				'graphql_cursor_compare' => $operator,
				'order' => '<' === $operator ? 'DESC' : 'ASC',
				'orderby' => 'date',
				'posts_per_page' => 11,
			)
		);

		$this->assertTrue( $query->have_posts() );
		$this->assertEquals( 5, count( $query->posts ) );

		// Make sure each of the returned posts matches the expected "second page"
		// of the posts we created (indexes 10-14).
		foreach ( $query->posts as $index => $post ) {
			$this->assertEquals( $posts[ $index + 10 ]->ID, $post->ID );
		}
	}
}
