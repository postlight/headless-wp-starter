<?php

namespace WPGraphQL\Type\MediaItem;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

/**
 * Class MediaItemType
 *
 * This class isn't a full definition of a new Type, instead it's used to customize
 * the shape of the mediaItemType (via filter), which is instantiated as a PostObjectType.
 *
 * @see     : wp-graphql.php - add_filter( 'graphql_mediaItem_fields', [ '\WPGraphQL\Type\MediaItem\MediaItemType',
 *          'fields' ], 10, 1 );
 *
 * @package WPGraphQL\Type\MediaItem
 */
class MediaItemType {

	/**
	 * Holds the object definition for media details
	 *
	 * @var object $media_details
	 */
	private static $media_details;

	/**
	 * Holds the object definition for media item meta
	 *
	 * @var object $media_item_meta
	 */
	private static $media_item_meta;

	/**
	 * Holds the object definition for media sizes
	 *
	 * @var object $media_sizes
	 */
	private static $media_sizes;

	/**
	 * This customizes the fields for the mediaItem type ( attachment post_type) as the shape of the mediaItem Schema
	 * is different than a standard post
	 *
	 * @see: wp-graphql.php - add_filter( 'graphql_mediaItem_fields' );add_filter( 'graphql_mediaItem_fields', [
	 *       '\WPGraphQL\Type\MediaItem\MediaItemType', 'fields' ], 10, 1 );
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function fields( $fields ) {

		/**
		 * Deprecate fields for the mediaItem type.
		 * These fields can still be queried, but are just not preferred for the mediaItem type
		 *
		 * @since 0.0.6
		 */
		$fields['excerpt']['isDeprecated']      = true;
		$fields['excerpt']['deprecationReason'] = __( 'Use the caption field instead of excerpt', 'wp-graphql' );
		$fields['content']['isDeprecated']      = true;
		$fields['content']['deprecationReason'] = __( 'Use the description field instead of content', 'wp-graphql' );

		/**
		 * Add new fields to the mediaItem type
		 *
		 * @since 0.0.6
		 */
		$new_fields = [
			'caption'      => [
				'type'        => Types::string(),
				'description' => __( 'The caption for the resource', 'wp-graphql' ),
				'resolve'     => function( \WP_Post $post, $args, $context, ResolveInfo $info ) {
					$caption = apply_filters( 'the_excerpt', $post->post_excerpt );

					return ! empty( $caption ) ? $caption : null;
				},
			],
			'altText'      => [
				'type'        => Types::string(),
				'description' => __( 'Alternative text to display when resource is not displayed', 'wp-graphql' ),
				'resolve'     => function( \WP_Post $post, $args, $context, ResolveInfo $info ) {
					return get_post_meta( $post->ID, '_wp_attachment_image_alt', true );
				},
			],
			'description'  => [
				'type'        => Types::string(),
				'description' => __( 'Description of the image (stored as post_content)', 'wp-graphql' ),
				'resolve'     => function( \WP_Post $post, $args, $context, ResolveInfo $info ) {
					return apply_filters( 'the_content', $post->post_content );
				},
			],
			'mediaType'    => [
				'type'        => Types::string(),
				'description' => __( 'Type of resource', 'wp-graphql' ),
				'resolve'     => function( \WP_Post $post, $args, $context, ResolveInfo $info ) {
					return wp_attachment_is_image( $post->ID ) ? 'image' : 'file';
				},
			],
			'sourceUrl'    => [
				'type'        => Types::string(),
				'description' => __( 'Url of the mediaItem', 'wp-graphql' ),
				'resolve'     => function( \WP_Post $post, $args, $context, ResolveInfo $info ) {
					return wp_get_attachment_url( $post->ID );
				},
			],
			'mimeType' => [
				'type'        => Types::string(),
				'description' => __( 'The mime type of the mediaItem', 'wp-graphql' ),
				'resolve'     =>function( \WP_Post $post, $args, $context, ResolveInfo $info ) {
					return ! empty( $post->post_mime_type ) ? $post->post_mime_type : null;
				},
			],
			'mediaDetails' => [
				'type'        => self::media_details(),
				'description' => __( 'Details about the mediaItem', 'wp-graphql' ),
				'resolve'     => function( \WP_Post $post, $args, $context, ResolveInfo $info ) {
					return wp_get_attachment_metadata( $post->ID );
				},
			],

		];

		return array_merge( $fields, $new_fields );

	}

	/**
	 * This defines the media details object type that can be queried on mediaItems
	 *
	 * @return null|WPObjectType
	 * @since 0.0.6
	 */
	private static function media_details() {

		if ( null === self::$media_details ) {
			self::$media_details = new WPObjectType( [
				'name'   => 'MediaDetails',
				'fields' => function() {
					$fields = [
						'width'  => [
							'type'        => Types::int(),
							'description' => __( 'The width of the mediaItem', 'wp-graphql' ),
						],
						'height' => [
							'type'        => Types::int(),
							'description' => __( 'The height of the mediaItem', 'wp-graphql' ),
						],
						'file'   => [
							'type'        => Types::string(),
							'description' => __( 'The height of the mediaItem', 'wp-graphql' ),
						],
						'sizes'  => [
							'type'        => Types::list_of( self::media_sizes() ),
							'description' => __( 'The available sizes of the mediaItem', 'wp-graphql' ),
							'resolve'     => function( $media_details, $args, $context, ResolveInfo $info ) {
								if ( ! empty( $media_details['sizes'] ) ) {
									foreach ( $media_details['sizes'] as $size_name => $size ) {
										$size['name']   = $size_name;
										$sizes[]        = $size;
									}
								}
								return ! empty( $sizes ) ? $sizes : null;
							},
						],
						'meta'   => [
							'type'    => self::media_item_meta(),
							'resolve' => function( $media_details, $args, $context, ResolveInfo $info ) {
								return ! empty( $media_details['image_meta'] ) ? $media_details['image_meta'] : null;
							},
						],
					];

					return WPObjectType::prepare_fields( $fields, 'MediaDetails' );
				},
			] );
		} // End if().

		return ! empty( self::$media_details ) ? self::$media_details : null;

	}

	/**
	 * This defines the media item meta object type that can be queried on mediaItems
	 *
	 * @return null|WPObjectType
	 * @since 0.0.6
	 */
	private static function media_item_meta() {
		if ( null === self::$media_item_meta ) {
			self::$media_item_meta = new WPObjectType( [
				'name'   => 'MediaItemMeta',
				'fields' => [
					'aperture'         => [
						'type' => Types::float(),
					],
					'credit'           => [
						'type' => Types::string(),
					],
					'camera'           => [
						'type' => Types::string(),
					],
					'caption'          => [
						'type' => Types::string(),
					],
					'createdTimestamp' => [
						'type'    => Types::int(),
						'resolve' => function( $meta, $args, $context, ResolveInfo $info ) {
							return ! empty( $meta['created_timestamp'] ) ? $meta['created_timestamp'] : null;
						},
					],
					'copyright'        => [
						'type' => Types::string(),
					],
					'focalLength'      => [
						'type'    => Types::int(),
						'resolve' => function( $meta, $args, $context, ResolveInfo $info ) {
							return ! empty( $meta['focal_length'] ) ? $meta['focal_length'] : null;
						},
					],
					'iso'              => [
						'type' => Types::int(),
					],
					'shutterSpeed'     => [
						'type'    => Types::float(),
						'resolve' => function( $meta, $args, $context, ResolveInfo $info ) {
							return ! empty( $meta['shutter_speed'] ) ? $meta['shutter_speed'] : null;
						},
					],
					'title'            => [
						'type' => Types::string(),
					],
					'orientation'      => [
						'type' => Types::string(),
					],
					'keywords'         => [
						'type' => Types::list_of( Types::string() ),
					],
				],
			] );
		} // End if().

		return ! empty( self::$media_item_meta ) ? self::$media_item_meta : null;
	}

	/**
	 * This defines the sizes object type that can be queried on mediaItems within the mediaDetails
	 *
	 * @return null|WPObjectType
	 * @since 0.0.6
	 */
	private static function media_sizes() {

		if ( null === self::$media_sizes ) {
			self::$media_sizes = new WPObjectType( [
				'name'   => 'MediaSizes',
				'fields' => [
					'name'      => [
						'type'        => Types::string(),
						'description' => __( 'The referenced size name', 'wp-graphql' ),
					],
					'file'      => [
						'type'        => Types::string(),
						'description' => __( 'The file of the for the referenced size', 'wp-graphql' ),
					],
					'width'     => [
						'type'        => Types::string(),
						'description' => __( 'The width of the for the referenced size', 'wp-graphql' ),
					],
					'height'    => [
						'type'        => Types::string(),
						'description' => __( 'The height of the for the referenced size', 'wp-graphql' ),
					],
					'mimeType'  => [
						'type'        => Types::string(),
						'description' => __( 'The mime type of the resource', 'wp-graphql' ),
						'resolve'     => function( $image, $args, $context, ResolveInfo $info ) {
							return ! empty( $image['mime-type'] ) ? $image['mime-type'] : null;
						},
					],
					'sourceUrl' => [
						'type'        => Types::string(),
						'description' => __( 'The url of the for the referenced size', 'wp-graphql' ),
						'resolve'     => function( $image, $args, $context, ResolveInfo $info ) {
							return ! empty( $image['file'] ) ? $image['file'] : null;
						},
					],
				],
			] );
		} // End if().

		return ! empty( self::$media_sizes ) ? self::$media_sizes : null;

	}

}