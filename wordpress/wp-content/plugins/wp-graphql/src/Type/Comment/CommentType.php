<?php

namespace WPGraphQL\Type\Comment;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Type\Comment\Connection\CommentConnectionDefinition;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

class CommentType extends WPObjectType {

	/**
	 * Holds the type name
	 *
	 * @var string $type_name
	 */
	private static $type_name;

	/**
	 * Holds the $fields definition for the CommentType
	 *
	 * @var $fields
	 */
	private static $fields;

	/**
	 * CommentType constructor.
	 *
	 * @since 0.0.5
	 */
	public function __construct() {

		/**
		 * Set the type_name
		 *
		 * @since 0.0.5
		 */
		self::$type_name = 'Comment';

		$config = [
			'name'        => self::$type_name,
			'description' => __( 'A Comment object', 'wp-graphql' ),
			'fields'      => self::fields(),
			'interfaces'  => [ self::node_interface() ],
		];

		parent::__construct( $config );

	}

	/**
	 * This defines the fields that make up the CommentType
	 *
	 * @return mixed
	 * @since 0.0.5
	 */
	private static function fields() {

		if ( null === self::$fields ) {
			self::$fields = function() {
				$fields = [
					'id'          => [
						'type'        => Types::non_null( Types::id() ),
						'description' => __( 'The globally unique identifier for the user', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_ID ) ? Relay::toGlobalId( 'comment', $comment->comment_ID ) : null;
						},
					],
					'commentId'   => [
						'type'        => Types::int(),
						'description' => __( 'ID for the comment, unique among comments.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_ID ) ? $comment->comment_ID : 0;
						},
					],
					'commentedOn' => [
						'type'        => Types::post_object_union(),
						'description' => __( 'The object the comment was added to', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_post_ID ) ? get_post( $comment->comment_post_ID ) : null;
						},
					],
					'author'      => [
						'type'        => Types::comment_author_union(),
						'description' => __( 'The author of the comment', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							/**
							 * If the comment has a user associated, use it to populate the author, otherwise return
							 * the $comment and the Union will use that to hydrate the CommentAuthor Type
							 */
							if ( ! empty( $comment->user_id ) ) {
								return DataSource::resolve_user( absint( $comment->user_id ) );
							} else {
								return DataSource::resolve_comment_author( $comment->comment_author_email );
							}
						},
					],
					'authorIp'    => [
						'type'        => Types::string(),
						'description' => __( 'IP address for the author. This field is equivalent to WP_Comment->comment_author_IP and the value matching the `comment_author_IP` column in SQL.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_author_IP ) ? $comment->comment_author_IP : '';
						},
					],
					'date'        => [
						'type'        => Types::string(),
						'description' => __( 'Date the comment was posted in local time. This field is equivalent to WP_Comment->date and the value matching the `date` column in SQL.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_date ) ? $comment->comment_date : '';
						},
					],
					'dateGmt'     => [
						'type'        => Types::string(),
						'description' => __( 'Date the comment was posted in GMT. This field is equivalent to WP_Comment->date_gmt and the value matching the `date_gmt` column in SQL.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_date_gmt ) ? $comment->comment_date_gmt : '';
						},
					],
					'content'     => [
						'type'        => Types::string(),
						'description' => __( 'Content of the comment. This field is equivalent to WP_Comment->comment_content and the value matching the `comment_content` column in SQL.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_content ) ? $comment->comment_content : '';
						},
					],
					'karma'       => [
						'type'        => Types::int(),
						'description' => __( 'Karma value for the comment. This field is equivalent to WP_Comment->comment_karma and the value matching the `comment_karma` column in SQL.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_karma ) ? $comment->comment_karma : 0;
						},
					],
					'approved'    => [
						'type'        => Types::string(),
						'description' => __( 'The approval status of the comment. This field is equivalent to WP_Comment->comment_approved and the value matching the `comment_approved` column in SQL.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_approved ) ? $comment->comment_approved : '';
						},
					],
					'agent'       => [
						'type'        => Types::string(),
						'description' => __( 'User agent used to post the comment. This field is equivalent to WP_Comment->comment_agent and the value matching the `comment_agent` column in SQL.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_agent ) ? $comment->comment_agent : '';
						},
					],
					'type'        => [
						'type'        => Types::string(),
						'description' => __( 'Type of comment. This field is equivalent to WP_Comment->comment_type and the value matching the `comment_type` column in SQL.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return ! empty( $comment->comment_type ) ? $comment->comment_type : '';
						},
					],
					'parent'      => [
						'type'        => Types::comment(),
						'description' => __( 'Parent comment of current comment. This field is equivalent to the WP_Comment instance matching the WP_Comment->comment_parent ID.', 'wp-graphql' ),
						'resolve'     => function( \WP_Comment $comment, $args, AppContext $context, ResolveInfo $info ) {
							return get_comment( $comment->comment_parent );
						},
					],
				];

				/**
				 * Add a comments_connection to display the child comments
				 *
				 * @since 0.0.5
				 */
				$fields['children'] = CommentConnectionDefinition::connection( 'Children' );

				/**
				 * This prepares the fields by sorting them and applying a filter for adjusting the schema.
				 * Because these fields are implemented via a closure the prepare_fields needs to be applied
				 * to the fields directly instead of being applied to all objects extending
				 * the WPObjectType class.
				 */
				return self::prepare_fields( $fields, self::$type_name );
			};
		}

		return self::$fields;
	}

}
