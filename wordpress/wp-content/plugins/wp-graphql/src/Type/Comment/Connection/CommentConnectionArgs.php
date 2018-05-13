<?php
namespace WPGraphQL\Type\Comment\Connection;

use GraphQL\Type\Definition\EnumType;
use WPGraphQL\Type\WPEnumType;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

/**
 * Class CommentConnectionArgs
 *
 * This sets up the Query Args for comments connections, which uses WP_Comment_Query, so this defines the allowed
 * input fields that will be passed to the WP_Comment_Query
 *
 * @package WPGraphQL\Type
 * @since 0.0.5
 */
class CommentConnectionArgs extends WPInputObjectType {

	/**
	 * This holds the field definitions
	 * @var array $fields
	 * @since 0.0.5
	 */
	public static $fields = [];

	/**
	 * Holds the orderby Enum definition
	 * @var EnumType
	 * @since 0.0.5
	 */
	private static $comments_orderby_enum;

	/**
	 * Holds the order Enum definition
	 * @var EnumType
	 */
	private static $comments_order;

	/**
	 * CommentConnectionArgs constructor.
	 * @param array $config
	 * @param string $connection
	 * @since 0.0.5
	 */
	public function __construct( $config = [], $connection ) {
		$config['name'] = ucfirst( $connection ) . 'CommentArgs';
		$config['fields'] = self::fields( $connection );
		parent::__construct( $config );
	}

	/**
	 * fields
	 *
	 * This defines the fields that make up the CommentConnectionArgs
	 *
	 * @return array
	 * @since 0.0.5
	 */
	private static function fields( $connection ) {

		if ( empty( self::$fields[ $connection ] ) ) {
			$fields = [
				'authorEmail'        => [
					'type'        => Types::string(),
					'description' => __( 'Comment author email address.', 'wp-graphql' ),
				],
				'authorUrl'          => [
					'type'        => Types::string(),
					'description' => __( 'Comment author URL.', 'wp-graphql' ),
				],
				'authorIn'           => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of author IDs to include comments for.', 'wp-graphql' ),
				],
				'authorNotIn'        => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of author IDs to exclude comments for.', 'wp-graphql' ),
				],
				'commentIn'          => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of comment IDs to include.', 'wp-graphql' ),
				],
				'commentNotIn'       => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of IDs of users whose unapproved comments will be returned by the
							query regardless of status.', 'wp-graphql' ),
				],
				'includeUnapproved'  => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of author IDs to include comments for.', 'wp-graphql' ),
				],
				'karma'              => [
					'type'        => Types::int(),
					'description' => __( 'Karma score to retrieve matching comments for.', 'wp-graphql' ),
				],
				'orderby'            => [
					'type'        => self::comments_orderby_enum(),
					'description' => __( 'Field to order the comments by.', 'wp-graphql' ),
				],
				'order'              => [
					'type' => self::comment_order(),
				],
				'parent'             => [
					'type'        => Types::int(),
					'description' => __( 'Parent ID of comment to retrieve children of.', 'wp-graphql' ),
				],
				'parentIn'           => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of parent IDs of comments to retrieve children for.', 'wp-graphql' ),
				],
				'parentNotIn'        => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of parent IDs of comments *not* to retrieve children
							for.', 'wp-graphql' ),
				],
				'contentAuthorIn'    => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of author IDs to retrieve comments for.', 'wp-graphql' ),
				],
				'contentAuthorNotIn' => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of author IDs *not* to retrieve comments for.', 'wp-graphql' ),
				],
				'contentId'          => [
					'type'        => Types::int(),
					'description' => __( 'Limit results to those affiliated with a given content object
							ID.', 'wp-graphql' ),
				],
				'contentIdIn'        => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of content object IDs to include affiliated comments
							for.', 'wp-graphql' ),
				],
				'contentIdNotIn'     => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Array of content object IDs to exclude affiliated comments
							for.', 'wp-graphql' ),
				],
				'contentAuthor'      => [
					'type'        => Types::list_of( Types::int() ),
					'description' => __( 'Content object author ID to limit results by.', 'wp-graphql' ),
				],
				'contentStatus'      => [
					'type'        => Types::list_of( Types::post_status_enum() ),
					'description' => __( 'Array of content object statuses to retrieve affiliated comments for.
							Pass \'any\' to match any value.', 'wp-graphql' ),
				],
				'contentType'        => [
					'type'        => Types::list_of( Types::post_type_enum() ),
					'description' => __( 'Content object type or array of types to retrieve affiliated comments for. Pass \'any\' to match any value.', 'wp-graphql' ),
				],
				'contentName'        => [
					'type'        => Types::string(),
					'description' => __( 'Content object name to retrieve affiliated comments for.', 'wp-graphql' ),
				],
				'contentParent'      => [
					'type'        => Types::int(),
					'description' => __( 'Content Object parent ID to retrieve affiliated comments for.', 'wp-graphql' ),
				],
				'search'             => [
					'type'        => Types::string(),
					'description' => __( 'Search term(s) to retrieve matching comments for.', 'wp-graphql' ),
				],
				'status'             => [
					'type'        => Types::string(),
					'description' => __( 'Comment status to limit results by.', 'wp-graphql' ),
				],
				'commentType'        => [
					'type'        => Types::string(),
					'description' => __( 'Include comments of a given type.', 'wp-graphql' ),
				],
				'commentTypeIn'      => [
					'type'        => Types::list_of( Types::string() ),
					'description' => __( 'Include comments from a given array of comment types.', 'wp-graphql' ),
				],
				'commentTypeNotIn'   => [
					'type'        => Types::string(),
					'description' => __( 'Exclude comments from a given array of comment types.', 'wp-graphql' ),
				],
				'userId'             => [
					'type'        => Types::int(),
					'description' => __( 'Include comments for a specific user ID.', 'wp-graphql' ),
				],
			];

			self::$fields[ $connection ] = self::prepare_fields( $fields, ucfirst( $connection ) . 'CommentArgs' );
		}
		return ! empty( self::$fields[ $connection ] ) ? self::$fields[ $connection ] : null;

	}

	/**
	 * comments_orderby_enum
	 *
	 * Defines the orderby Enum values for ordering a comments query
	 *
	 * @return EnumType
	 * @since 0.0.5
	 */
	private static function comments_orderby_enum() {

		if ( null === self::$comments_orderby_enum ) {
			self::$comments_orderby_enum = new WPEnumType( [
				'name'   => 'CommentsOrderby',
				'values' => [
					'COMMENT_AGENT'        => [
						'value' => 'comment_agent',
					],
					'COMMENT_APPROVED'     => [
						'value' => 'comment_approved',
					],
					'COMMENT_AUTHOR'       => [
						'value' => 'comment_author',
					],
					'COMMENT_AUTHOR_EMAIL' => [
						'value' => 'comment_author_email',
					],
					'COMMENT_AUTHOR_IP'    => [
						'value' => 'comment_author_IP',
					],
					'COMMENT_AUTHOR_URL'   => [
						'value' => 'comment_author_url',
					],
					'COMMENT_CONTENT'      => [
						'value' => 'comment_content',
					],
					'COMMENT_DATE'         => [
						'value' => 'comment_date',
					],
					'COMMENT_DATE_GMT'     => [
						'value' => 'comment_date_gmt',
					],
					'COMMENT_ID'           => [
						'value' => 'comment_ID',
					],
					'COMMENT_KARMA'        => [
						'value' => 'comment_karma',
					],
					'COMMENT_PARENT'       => [
						'value' => 'comment_parent',
					],
					'COMMENT_POST_ID'      => [
						'value' => 'comment_post_ID',
					],
					'COMMENT_TYPE'         => [
						'value' => 'comment_type',
					],
					'USER_ID'              => [
						'value' => 'user_id',
					],
					'COMMENT_IN'           => [
						'value' => 'comment__in',
					],
				],
			] );
		}
		return self::$comments_orderby_enum;
	}

	private static function comment_order() {

		if ( null === self::$comments_order ) {
			self::$comments_order = new WPEnumType( [
				'name'         => 'CommentsOrder',
				'values'       => [
					'ASC'  => [
						'value' => 'ASC',
					],
					'DESC' => [
						'value' => 'DESC',
					],
				],
				'defaultValue' => 'DESC',
			] );
		}

		return self::$comments_order;

	}

}
