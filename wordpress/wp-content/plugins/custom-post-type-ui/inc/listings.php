<?php
/**
 * Custom Post Type UI Registered Content.
 *
 * @package CPTUI
 * @subpackage Listings
 * @author WebDevStudios
 * @since 1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output the content for the "Registered Types/Taxes" page.
 *
 * @since 1.1.0
 *
 * @internal
 */
function cptui_listings() {
		?>
		<div class="wrap cptui-listings">
			<?php
			/**
			 * Fires right inside the wrap div for the listings screen.
			 *
			 * @since 1.3.0
			 */
			do_action( 'cptui_inside_listings_wrap' );
			?>

			<h1><?php esc_html_e( 'Post Types and Taxonomies registered by Custom Post Type UI.', 'custom-post-type-ui' ); ?></h1>
			<?php
			$post_types = cptui_get_post_type_data();
			echo '<h2 id="post-types">' . esc_html__( 'Post Types', 'custom-post-type-ui' ) . '</h2>';
			if ( ! empty( $post_types ) ) {
			?>
			<p><?php printf( esc_html__( 'CPTUI registered post types count total: %d', 'custom-post-type-ui' ), count( $post_types ) ); ?></p>

			<?php

			$post_type_table_heads = array(
				__( 'Post Type', 'custom-post-type-ui' ),
				__( 'Settings', 'custom-post-type-ui' ),
				__( 'Supports', 'custom-post-type-ui' ),
				__( 'Taxonomies', 'custom-post-type-ui' ),
				__( 'Labels', 'custom-post-type-ui' ),
				__( 'Template Hierarchy', 'custom-post-type-ui' ),
			);

			/**
			 * Fires before the listing of registered post type data.
			 *
			 * @since 1.1.0
			 */
			do_action( 'cptui_before_post_type_listing' );
			?>
			<table class="wp-list-table widefat post-type-listing">
				<thead>
				<tr>
					<?php
					foreach ( $post_type_table_heads as $head ) {
						echo '<th>' . esc_html( $head ) . '</th>';
					} ?>
				</tr>
				</thead>
				<tbody>
				<?php
				$counter = 1;
				foreach ( $post_types as $post_type => $post_type_settings ) {

					$rowclass = ( 0 === $counter % 2 ) ? '' : 'alternate';

					$strings = array();
					$supports = array();
					$taxonomies = array();
					$archive = '';
					foreach ( $post_type_settings as $settings_key => $settings_value ) {
						if ( 'labels' === $settings_key ) {
							continue;
						}

						if ( is_string( $settings_value ) ) {
							$strings[ $settings_key ] = $settings_value;
						} else {
							if ( 'supports' === $settings_key ) {
								$supports[ $settings_key ] = $settings_value;
							}

							if ( 'taxonomies' === $settings_key ) {
								$taxonomies[ $settings_key ] = $settings_value;

								// In case they are not associated from the post type settings.
								if ( empty( $taxonomies['taxonomies'] ) ) {
									$taxonomies['taxonomies'] = get_object_taxonomies( $post_type );
								}
							}
						}
						$archive = get_post_type_archive_link( $post_type );
					}
					?>
						<tr class="<?php echo esc_attr( $rowclass ); ?>">
							<?php
							$edit_path          = 'admin.php?page=cptui_manage_post_types&action=edit&cptui_post_type=' . $post_type;
							$post_type_link_url = ( is_network_admin() ) ? network_admin_url( $edit_path ) : admin_url( $edit_path ); ?>
							<td>
								<?php
								printf(
									'<a href="%s">%s</a><br/>
									<a href="%s">%s</a><br/>',
									esc_attr( $post_type_link_url ),
									sprintf(
										esc_html__( 'Edit %s', 'custom-post-type-ui' ),
										esc_html( $post_type )
									),
									esc_attr( admin_url( 'admin.php?page=cptui_tools&action=get_code#' . $post_type ) ),
									esc_html__( 'Get code', 'custom-post-type-ui' )
								);

								if ( $archive ) {
								?>
								<a href="<?php echo esc_attr( get_post_type_archive_link( $post_type ) ); ?>"><?php esc_html_e( 'View frontend archive', 'custom-post-type-ui' ); ?></a>
								<?php } ?>
							</td>
							<td>
								<?php
								foreach ( $strings as $key => $value ) {
									printf( '<strong>%s:</strong> ', esc_html( $key ) );
									if ( in_array( $value, array( '1', '0' ) ) ) {
										echo esc_html( disp_boolean( $value ) );
									} else {
										echo ( ! empty( $value ) ) ? esc_html( $value ) : '""';
									}
									echo '<br/>';
								} ?>
							</td>
							<td>
								<?php
								foreach ( $supports['supports'] as $support ) {
									echo esc_html( $support ) . '<br/>';
								} ?>
							</td>
							<td>
								<?php
								if ( ! empty( $taxonomies['taxonomies'] ) ) {
									foreach ( $taxonomies['taxonomies'] as $taxonomy ) {
										echo esc_html( $taxonomy ) . '<br/>';
									}
								} else {
									printf(
										'<span aria-hidden="true">—</span><span class="screen-reader-text">%s</span>',
										esc_html__( 'No associated taxonomies', 'custom-post-type-ui' )
									);
								}
								?>
							</td>
							<td>
								<?php
								$maybe_empty = array_filter( $post_type_settings['labels'] );
								if ( ! empty( $maybe_empty ) ) {
									foreach ( $post_type_settings['labels'] as $key => $value ) {
										if ( 'parent' === $key && array_key_exists( 'parent_item_colon', $post_type_settings['labels'] ) ) {
											continue;
										}
										printf(
											'%s: %s<br/>',
											esc_html( $key ),
											esc_html( $value )
										);
									}
								} else {
									printf(
										'<span aria-hidden="true">—</span><span class="screen-reader-text">%s</span>',
										esc_html__( 'No custom labels to display', 'custom-post-type-ui' )
									);
								}
								?>
							</td>
							<td>
								<p><strong><?php esc_html_e( 'Archives file name examples.', 'custom-post-type-ui' ); ?></strong><br/>
								archive-<?php echo esc_html( $post_type ); ?>.php<br/>
								archive.php<br/>
								index.php
								</p>

								<p><strong><?php esc_html_e( 'Single Posts file name examples.', 'custom-post-type-ui' ); ?></strong><br/>
								single-<?php echo esc_html( $post_type ); ?>-post_slug.php (WP 4.4+) *<br/>
								single-<?php echo esc_html( $post_type ); ?>.php<br/>
								single.php<br/>
								singular.php (WP 4.3+)<br/>
								index.php
								</p>

								<p>
									<?php esc_html_e( '*Replace "post_slug" with the slug of the actual post slug.', 'custom-post-type-ui' ); ?>
								</p>

								<p><?php
									printf(
										'<a href="https://developer.wordpress.org/themes/basics/template-hierarchy/">%s</a>',
										esc_html__( 'Template hierarchy Theme Handbook', 'custom-post-type-ui' )
									); ?>
								</p>
							</td>
						</tr>

					<?php
					$counter++;
				}
				?>
				</tbody>
				<tfoot>
				<tr>
					<?php
					foreach ( $post_type_table_heads as $head ) {
						echo '<th>' . esc_html( $head ) . '</th>';
					} ?>
				</tr>
				</tfoot>
			</table>
			<?php
				/**
				 * Fires after the listing of registered post type data.
				 *
				 * @since 1.3.0
				 */
				do_action( 'cptui_after_post_type_listing' );
			} else {

				/**
				 * Fires when there are no registered post types to list.
				 *
				 * @since 1.3.0
				 */
				do_action( 'cptui_no_post_types_listing' );
			}

			$taxonomies = cptui_get_taxonomy_data();
			echo '<h2 id="taxonomies">' . esc_html__( 'Taxonomies', 'custom-post-type-ui' ) . '</h2>';
			if ( ! empty( $taxonomies ) ) {
				?>
				<p><?php printf( esc_html__( 'CPTUI registered taxonomies count total: %d', 'custom-post-type-ui' ), count( $taxonomies ) ); ?></p>

				<?php

				$taxonomy_table_heads = array(
					__( 'Taxonomy', 'custom-post-type-ui' ),
					__( 'Settings', 'custom-post-type-ui' ),
					__( 'Post Types', 'custom-post-type-ui' ),
					__( 'Labels', 'custom-post-type-ui' ),
					__( 'Template Hierarchy', 'custom-post-type-ui' ),
				);

				/**
				 * Fires before the listing of registered taxonomy data.
				 *
				 * @since 1.1.0
				 */
				do_action( 'cptui_before_taxonomy_listing' );
				?>
				<table class="wp-list-table widefat taxonomy-listing">
					<thead>
					<tr>
						<?php
						foreach ( $taxonomy_table_heads as $head ) {
							echo '<th>' . esc_html( $head ) . '</th>';
						} ?>
					</tr>
					</thead>
					<tbody>
					<?php
					$counter = 1;
					foreach ( $taxonomies as $taxonomy => $taxonomy_settings ) {

						$rowclass = ( 0 === $counter % 2 ) ? '' : 'alternate';

						$strings = array();
						$object_types = array();
						foreach ( $taxonomy_settings as $settings_key => $settings_value ) {
							if ( 'labels' === $settings_key ) {
								continue;
							}

							if ( is_string( $settings_value ) ) {
								$strings[ $settings_key ] = $settings_value;
							} else {
								if ( 'object_types' === $settings_key ) {
									$object_types[ $settings_key ] = $settings_value;

									// In case they are not associated from the post type settings.
									if ( empty( $object_types['object_types'] ) ) {
										$types = get_taxonomy( $taxonomy );
										$object_types['object_types'] = $types->object_type;
									}
								}
							}
						}
						?>
							<tr class="<?php echo esc_attr( $rowclass ); ?>">
								<?php
								$edit_path          = 'admin.php?page=cptui_manage_taxonomies&action=edit&cptui_taxonomy=' . $taxonomy;
								$taxonomy_link_url  = ( is_network_admin() ) ? network_admin_url( $edit_path ) : admin_url( $edit_path ); ?>
								<td>
									<?php printf(
										'<a href="%s">%s</a><br/>
										<a href="%s">%s</a>',
										esc_attr( $taxonomy_link_url ),
										sprintf(
											esc_html__( 'Edit %s', 'custom-post-type-ui' ),
											esc_html( $taxonomy )
										),
										esc_attr( admin_url( 'admin.php?page=cptui_tools&action=get_code#' . $taxonomy ) ),
										esc_html__( 'Get code', 'custom-post-type-ui' )
									); ?>
								</td>
								<td>
									<?php
									foreach ( $strings as $key => $value ) {
										printf( '<strong>%s:</strong> ', esc_html( $key ) );
										if ( in_array( $value, array( '1', '0' ) ) ) {
											echo esc_html( disp_boolean( $value ) );
										} else {
											echo ( ! empty( $value ) ) ? esc_html( $value ) : '""';
										}
										echo '<br/>';
									} ?>
								</td>
								<td>
									<?php
									if ( ! empty( $object_types['object_types'] ) ) {
										foreach ( $object_types['object_types'] as $type ) {
											echo esc_html( $type ) . '<br/>';
										}
									} ?>
								</td>
								<td>
									<?php
									$maybe_empty = array_filter( $taxonomy_settings['labels'] );
									if ( ! empty( $maybe_empty ) ) {
										foreach ( $taxonomy_settings['labels'] as $key => $value ) {
											printf(
												'%s: %s<br/>',
												esc_html( $key ),
												esc_html( $value )
											);
										}
									} else {
										printf(
											'<span aria-hidden="true">—</span><span class="screen-reader-text">%s</span>',
											esc_html__( 'No custom labels to display', 'custom-post-type-ui' )
										);
									}
									?>
								</td>
								<td>
									<p><strong><?php esc_html_e( 'Archives file name examples.', 'custom-post-type-ui' ); ?></strong><br />
										taxonomy-<?php echo esc_html( $taxonomy ); ?>-term_slug.php *<br />
										taxonomy-<?php echo esc_html( $taxonomy ); ?>.php<br />
										taxonomy.php<br />
										archive.php<br />
										index.php
									</p>

									<p>
										<?php esc_html_e( '*Replace "term_slug" with the slug of the actual taxonomy term.', 'custom-post-type-ui' ); ?>
									</p>
									<p><?php
										printf(
											'<a href="https://developer.wordpress.org/themes/basics/template-hierarchy/">%s</a>',
											esc_html__( 'Template hierarchy Theme Handbook', 'custom-post-type-ui' )
										); ?></p>
								</td>
							</tr>

						<?php
						$counter++;
					}
					?>
					</tbody>
					<tfoot>
					<tr>
						<?php
						foreach ( $taxonomy_table_heads as $head ) {
							echo '<th>' . esc_html( $head ) . '</th>';
						} ?>
					</tr>
					</tfoot>
				</table>
			<?php
				/**
				 * Fires after the listing of registered taxonomy data.
				 *
				 * @since 1.3.0
				 */
				do_action( 'cptui_after_taxonomy_listing' );

			} else {

				/**
				 * Fires when there are no registered taxonomies to list.
				 *
				 * @since 1.3.0
				 */
				do_action( 'cptui_no_taxonomies_listing' );
			}
			?>

		</div>
	<?php
}

/**
 * Displays a message for when no post types are registered.
 *
 * Uses the `cptui_no_post_types_listing` hook.
 *
 * @since 1.3.0
 *
 * @internal
 */
function cptui_no_post_types_to_list() {
	echo '<p>' . sprintf( esc_html__( 'No post types registered for display. Visit %s to get started.', 'custom-post-type-ui' ),
		sprintf( '<a href="%s">%s</a>',
			esc_attr( admin_url( 'admin.php?page=cptui_manage_post_types' ) ),
			esc_html__( 'Add/Edit Post Types', 'custom-post-type-ui' )
		)
	) . '</p>';
}
add_action( 'cptui_no_post_types_listing', 'cptui_no_post_types_to_list' );

/**
 * Displays a message for when no taxonomies are registered.
 *
 * Uses the `cptui_no_taxonomies_listing` hook.
 *
 * @since 1.3.0
 *
 * @internal
 */
function cptui_no_taxonomies_to_list() {
	echo '<p>' . sprintf( esc_html__( 'No taxonomies registered for display. Visit %s to get started.', 'custom-post-type-ui' ),
		sprintf( '<a href="%s">%s</a>',
			esc_attr( admin_url( 'admin.php?page=cptui_manage_taxonomies' ) ),
			esc_html__( 'Add/Edit Taxonomies', 'custom-post-type-ui' )
		)
	) . '</p>';
}
add_action( 'cptui_no_taxonomies_listing', 'cptui_no_taxonomies_to_list' );
