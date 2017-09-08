<?php

/*
*  ACF Admin Field Groups Class
*
*  All the logic for editing a list of field groups
*
*  @class 		acf_admin_field_groups
*  @package		ACF
*  @subpackage	Admin
*/

if( ! class_exists('acf_admin_field_groups') ) :

class acf_admin_field_groups {
	
	// vars
	var $url = 'edit.php?post_type=acf-field-group',
		$sync = array();
		
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
	
		// actions
		add_action('current_screen',		array($this, 'current_screen'));
		add_action('trashed_post',			array($this, 'trashed_post'));
		add_action('untrashed_post',		array($this, 'untrashed_post'));
		add_action('deleted_post',			array($this, 'deleted_post'));
		
	}
	
	
	/*
	*  current_screen
	*
	*  This function is fired when loading the admin page before HTML has been rendered.
	*
	*  @type	action (current_screen)
	*  @date	21/07/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function current_screen() {
		
		// validate screen
		if( !acf_is_screen('edit-acf-field-group') ) {
		
			return;
			
		}
		

		// customize post_status
		global $wp_post_statuses;
		
		
		// modify publish post status
		$wp_post_statuses['publish']->label_count = _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'acf' );
		
		
		// reorder trash to end
		$wp_post_statuses['trash'] = acf_extract_var( $wp_post_statuses, 'trash' );

		
		// check stuff
		$this->check_duplicate();
		$this->check_sync();
		
		
		// actions
		add_action('admin_enqueue_scripts',							array($this, 'admin_enqueue_scripts'));
		add_action('admin_footer',									array($this, 'admin_footer'));
		
		
		// columns
		add_filter('manage_edit-acf-field-group_columns',			array($this, 'field_group_columns'), 10, 1);
		add_action('manage_acf-field-group_posts_custom_column',	array($this, 'field_group_columns_html'), 10, 2);
		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This function will add the already registered css
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_enqueue_scripts() {
		
		wp_enqueue_script('acf-input');
		
	}
	
	
	/*
	*  check_duplicate
	*
	*  This function will check for any $_GET data to duplicate
	*
	*  @type	function
	*  @date	17/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function check_duplicate() {
		
		// message
		if( $ids = acf_maybe_get_GET('acfduplicatecomplete') ) {
			
			// explode
			$ids = explode(',', $ids);
			$total = count($ids);
			
			if( $total == 1 ) {
				
				acf_add_admin_notice( sprintf(__('Field group duplicated. %s', 'acf'), '<a href="' . get_edit_post_link($ids[0]) . '">' . get_the_title($ids[0]) . '</a>') );
				
			} else {
				
				acf_add_admin_notice( sprintf(_n( '%s field group duplicated.', '%s field groups duplicated.', $total, 'acf' ), $total) );
				
			}
			
		}
		
		
		// vars
		$ids = array();
		
		
		// check single
		if( $id = acf_maybe_get_GET('acfduplicate') ) {
			
			$ids[] = $id;
		
		// check multiple
		} elseif( acf_maybe_get_GET('action2') === 'acfduplicate' ) {
			
			$ids = acf_maybe_get_GET('post');
			
		}
		
		
		// sync
		if( !empty($ids) ) {
			
			// validate
			check_admin_referer('bulk-posts');
			
			
			// vars
			$new_ids = array();
			
			
			// loop
			foreach( $ids as $id ) {
				
				// duplicate
				$field_group = acf_duplicate_field_group( $id );
				
				
				// increase counter
				$new_ids[] = $field_group['ID'];
				
			}
			
			
			// redirect
			wp_redirect( admin_url( $this->url . '&acfduplicatecomplete=' . implode(',', $new_ids)) );
			exit;
				
		}
		
	}
	
	
	/*
	*  check_sync
	*
	*  This function will check for any $_GET data to sync
	*
	*  @type	function
	*  @date	9/12/2014
	*  @since	5.1.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function check_sync() {
		
		// message
		if( $ids = acf_maybe_get_GET('acfsynccomplete') ) {
			
			// explode
			$ids = explode(',', $ids);
			$total = count($ids);
			
			if( $total == 1 ) {
				
				acf_add_admin_notice( sprintf(__('Field group synchronised. %s', 'acf'), '<a href="' . get_edit_post_link($ids[0]) . '">' . get_the_title($ids[0]) . '</a>') );
				
			} else {
				
				acf_add_admin_notice( sprintf(_n( '%s field group synchronised.', '%s field groups synchronised.', $total, 'acf' ), $total) );
				
			}
			
		}
		
		
		// vars
		$groups = acf_get_field_groups();
		
		
		// bail early if no field groups
		if( empty($groups) ) return;
		
		
		// find JSON field groups which have not yet been imported
		foreach( $groups as $group ) {
			
			// vars
			$local = acf_maybe_get($group, 'local', false);
			$modified = acf_maybe_get($group, 'modified', 0);
			$private = acf_maybe_get($group, 'private', false);
			
			
			// ignore DB / PHP / private field groups
			if( $local !== 'json' || $private ) {
				
				// do nothing
				
			} elseif( !$group['ID'] ) {
				
				$this->sync[ $group['key'] ] = $group;
				
			} elseif( $modified && $modified > get_post_modified_time('U', true, $group['ID'], true) ) {
				
				$this->sync[ $group['key'] ]  = $group;
				
			}
						
		}
		
		
		// bail if no sync needed
		if( empty($this->sync) ) return;
		
		
		// maybe sync
		$sync_keys = array();
		
		
		// check single
		if( $key = acf_maybe_get_GET('acfsync') ) {
			
			$sync_keys[] = $key;
		
		// check multiple
		} elseif( acf_maybe_get_GET('action2') === 'acfsync' ) {
			
			$sync_keys = acf_maybe_get_GET('post');
			
		}
		
		
		// sync
		if( !empty($sync_keys) ) {
			
			// validate
			check_admin_referer('bulk-posts');
			
			
			// disable filters to ensure ACF loads raw data from DB
			acf_disable_filters();
			acf_enable_filter('local');
			
			
			// disable JSON
			// - this prevents a new JSON file being created and causing a 'change' to theme files - solves git anoyance
			acf_update_setting('json', false);
			
			
			// vars
			$new_ids = array();
				
			
			// loop
			foreach( $sync_keys as $key ) {
				
				// append fields
				if( acf_have_local_fields($key) ) {
					
					$this->sync[ $key ]['fields'] = acf_get_local_fields( $key );
					
				}
				
				
				// import
				$field_group = acf_import_field_group( $this->sync[ $key ] );
									
				
				// append
				$new_ids[] = $field_group['ID'];
				
			}
			
			
			// redirect
			wp_redirect( admin_url( $this->url . '&acfsynccomplete=' . implode(',', $new_ids)) );
			exit;
			
		}
		
		
		// filters
		add_filter('views_edit-acf-field-group', array($this, 'list_table_views'));
		
	}
	
	
	/*
	*  list_table_views
	*
	*  This function will add an extra link for JSON in the field group list table
	*
	*  @type	function
	*  @date	3/12/2014
	*  @since	5.1.5
	*
	*  @param	$views (array)
	*  @return	$views
	*/
	
	function list_table_views( $views ) {
		
		// vars
		$class = '';
		$total = count($this->sync);
		
		// active
		if( acf_maybe_get_GET('post_status') === 'sync' ) {
			
			// actions
			add_action('admin_footer', array($this, 'sync_admin_footer'), 5);
			
			
			// set active class
			$class = ' class="current"';
			
			
			// global
			global $wp_list_table;
			
			
			// update pagination
			$wp_list_table->set_pagination_args( array(
				'total_items' => $total,
				'total_pages' => 1,
				'per_page' => $total
			));
			
		}
		
		
		// add view
		$views['json'] = '<a' . $class . ' href="' . admin_url($this->url . '&post_status=sync') . '">' . __('Sync available', 'acf') . ' <span class="count">(' . $total . ')</span></a>';
		
		
		// return
		return $views;
		
	}
	
	
	/*
	*  trashed_post
	*
	*  This function is run when a post object is sent to the trash
	*
	*  @type	action (trashed_post)
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function trashed_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'acf-field-group' ) {
		
			return;
		
		}
		
		
		// trash field group
		acf_trash_field_group( $post_id );
		
	}
	
	
	/*
	*  untrashed_post
	*
	*  This function is run when a post object is restored from the trash
	*
	*  @type	action (untrashed_post)
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function untrashed_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'acf-field-group' ) {
		
			return;
			
		}
		
		
		// trash field group
		acf_untrash_field_group( $post_id );
		
	}
	
	
	/*
	*  deleted_post
	*
	*  This function is run when a post object is deleted from the trash
	*
	*  @type	action (deleted_post)
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function deleted_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'acf-field-group' ) {
		
			return;
			
		}
		
		
		// trash field group
		acf_delete_field_group( $post_id );
		
	}
	
	
	/*
	*  field_group_columns
	*
	*  This function will customize the columns for the field group table
	*
	*  @type	filter (manage_edit-acf-field-group_columns)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$columns (array)
	*  @return	$columns (array)
	*/
	
	function field_group_columns( $columns ) {
		
		return array(
			'cb'	 				=> '<input type="checkbox" />',
			'title' 				=> __('Title', 'acf'),
			'acf-fg-description'	=> __('Description', 'acf'),
			'acf-fg-status' 		=> '<i class="acf-icon -dot-3 small acf-js-tooltip" title="' . esc_attr__('Status', 'acf') . '"></i>',
			'acf-fg-count' 			=> __('Fields', 'acf'),
		);
		
	}
	
	
	/*
	*  field_group_columns_html
	*
	*  This function will render the HTML for each table cell
	*
	*  @type	action (manage_acf-field-group_posts_custom_column)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$column (string)
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function field_group_columns_html( $column, $post_id ) {
		
		// vars
		$field_group = acf_get_field_group( $post_id );
		
		
		// render
		$this->render_column( $column, $field_group );
	    
	}
	
	function render_column( $column, $field_group ) {
		
		// description
		if( $column == 'acf-fg-description' ) {
			
			if( $field_group['description'] ) {
				
				echo '<span class="acf-description">' . acf_esc_html($field_group['description']) . '</span>';
				
			}
        
        // status
	    } elseif( $column == 'acf-fg-status' ) {
			
			if( isset($this->sync[ $field_group['key'] ]) ) {
				
				echo '<i class="acf-icon -sync grey small acf-js-tooltip" title="' . esc_attr__('Sync available', 'acf') .'"></i> ';
				
			}
			
			if( $field_group['active'] ) {
				
				//echo '<i class="acf-icon -check small acf-js-tooltip" title="' . esc_attr__('Active', 'acf') .'"></i> ';
				
			} else {
				
				echo '<i class="acf-icon -minus yellow small acf-js-tooltip" title="' . esc_attr__('Inactive', 'acf') . '"></i> ';
				
			}
	    
        // fields
	    } elseif( $column == 'acf-fg-count' ) {
			
			echo esc_html( acf_get_field_count( $field_group ) );
        
        }
		
	}
	
	
	/*
	*  admin_footer
	*
	*  This function will render extra HTML onto the page
	*
	*  @type	action (admin_footer)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_footer() {
		
		// vars
		$url_home = 'https://www.advancedcustomfields.com';
		$url_support = 'https://support.advancedcustomfields.com';
		$icon = '<i aria-hidden="true" class="dashicons dashicons-external"></i>';
		
?>
<script type="text/html" id="tmpl-acf-column-2">
<div class="acf-column-2">
	<div class="acf-box">
		<div class="inner">
			<h2><?php echo acf_get_setting('name'); ?></h2>
			<p><?php _e('Customise WordPress with powerful, professional and intuitive fields.'); ?></p>
			
			<h3><?php _e("Changelog",'acf'); ?></h3>
			<p><?php 
			
			$acf_changelog = admin_url('edit.php?post_type=acf-field-group&page=acf-settings-info&tab=changelog');
			$acf_version = acf_get_setting('version');
			printf( __('See what\'s new in <a href="%s">version %s</a>.','acf'), esc_url($acf_changelog), $acf_version );
			
			?></p>
			<h3><?php _e("Resources",'acf'); ?></h3>
			<ul>
				<li><a href="<?php echo esc_url( $url_home ); ?>" target="_blank"><?php echo $icon; ?> <?php _e("Website",'acf'); ?></a></li>
				<li><a href="<?php echo esc_url( $url_home . '/resources/' ); ?>" target="_blank"><?php echo $icon; ?> <?php _e("Documentation",'acf'); ?></a></li>
				<li><a href="<?php echo esc_url( $url_support ); ?>" target="_blank"><?php echo $icon; ?> <?php _e("Support",'acf'); ?></a></li>
				<?php if( !acf_get_setting('pro') ): ?>
				<li><a href="<?php echo esc_url( $url_home . '/pro/' ); ?>" target="_blank"><?php echo $icon; ?> <?php _e("Pro",'acf'); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="footer">
			<p><?php printf( __('Thank you for creating with <a href="%s">ACF</a>.','acf'), esc_url($url_home) ); ?></p>
		</div>
	</div>
</div>
<div class="acf-clear"></div>
</script>
<script type="text/javascript">
(function($){
	
	// wrap
	$('#wpbody .wrap').attr('id', 'acf-field-group-wrap');
	
	
	// wrap form
	$('#posts-filter').wrap('<div class="acf-columns-2" />');
	
	
	// add column main
	$('#posts-filter').addClass('acf-column-1');
	
	
	// add column side
	$('#posts-filter').after( $('#tmpl-acf-column-2').html() );
	
	
	// modify row actions
	$('#the-list tr').each(function(){
		
		// vars
		var $tr = $(this),
			id = $tr.attr('id'),
			description = $tr.find('.column-acf-fg-description').html();
		
		
		// replace Quick Edit with Duplicate (sync page has no id attribute)
		if( id ) {
			
			// vars
			var post_id	= id.replace('post-', '');
			var url = '<?php echo esc_url( admin_url( $this->url . '&acfduplicate=__post_id__&_wpnonce=' . wp_create_nonce('bulk-posts') ) ); ?>';
			var $span = $('<span class="acf-duplicate-field-group"><a title="<?php _e('Duplicate this item', 'acf'); ?>" href="' + url.replace('__post_id__', post_id) + '"><?php _e('Duplicate', 'acf'); ?></a> | </span>');
			
			
			// replace
			$tr.find('.column-title .row-actions .inline').replaceWith( $span );
			
		}
		
		
		// add description to title
		$tr.find('.column-title .row-title').after( description );
		
	});
	
	
	// modify bulk actions
	$('#bulk-action-selector-bottom option[value="edit"]').attr('value','acfduplicate').text('<?php _e( 'Duplicate', 'acf' ); ?>');
	
	
	// clean up table
	$('#adv-settings label[for="acf-fg-description-hide"]').remove();
	
	
	// mobile compatibility
	var status = $('.acf-icon.-dot-3').first().attr('title');
	$('td.column-acf-fg-status').attr('data-colname', status);
	
	
	// no field groups found
	$('#the-list tr.no-items td').attr('colspan', 4);
	
	
	// search
	$('.subsubsub').append(' | <li><a href="#" class="acf-toggle-search"><?php _e('Search', 'acf'); ?></a></li>');
	
	
	// events
	$(document).on('click', '.acf-toggle-search', function( e ){
		
		// prevent default
		e.preventDefault();
		
		
		// toggle
		$('.search-box').slideToggle();
		
	});
	
})(jQuery);
</script>
<?php
		
	}
	
	
	/*
	*  sync_admin_footer
	*
	*  This function will render extra HTML onto the page
	*
	*  @type	action (admin_footer)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function sync_admin_footer() {
		
		// vars
		$i = -1;
		$columns = array(
			'acf-fg-description',
			'acf-fg-status',
			'acf-fg-count'
		);
		$nonce = wp_create_nonce('bulk-posts');
		
?>
<script type="text/html" id="tmpl-acf-json-tbody">
<?php foreach( $this->sync as $field_group ): 
	
	// vars
	$i++; 
	$key = $field_group['key'];
	$title = $field_group['title'];
	$url = admin_url( $this->url . '&post_status=sync&acfsync=' . $key . '&_wpnonce=' . $nonce );
	
	?>
	<tr <?php if($i%2 == 0): ?>class="alternate"<?php endif; ?>>
		<th class="check-column" scope="row">
			<label for="cb-select-<?php echo esc_attr($key); ?>" class="screen-reader-text"><?php echo esc_html(sprintf(__('Select %s', 'acf'), $title)); ?></label>
			<input type="checkbox" value="<?php echo esc_attr($key); ?>" name="post[]" id="cb-select-<?php echo esc_attr($key); ?>">
		</th>
		<td class="post-title page-title column-title">
			<strong>
				<span class="row-title"><?php echo esc_html($title); ?></span><span class="acf-description"><?php echo esc_html($key); ?>.json</span>
			</strong>
			<div class="row-actions">
				<span class="import"><a title="<?php echo esc_attr( __('Synchronise field group', 'acf') ); ?>" href="<?php echo esc_url($url); ?>"><?php _e( 'Sync', 'acf' ); ?></a></span>
			</div>
		</td>
		<?php foreach( $columns as $column ): ?>
			<td class="column-<?php echo esc_attr($column); ?>"><?php $this->render_column( $column, $field_group ); ?></td>
		<?php endforeach; ?>
	</tr>
<?php endforeach; ?>
</script>
<script type="text/html" id="tmpl-acf-bulk-actions">
	<?php // source: bulk_actions() wp-admin/includes/class-wp-list-table.php ?>
	<select name="action2" id="bulk-action-selector-bottom"></select>
	<?php submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => "doaction2" ) ); ?>
</script>
<script type="text/javascript">
(function($){
	
	// update table HTML
	$('#the-list').html( $('#tmpl-acf-json-tbody').html() );
	
	
	// bulk may not exist if no field groups in DB
	if( !$('#bulk-action-selector-bottom').exists() ) {
		
		$('.tablenav.bottom .actions.alignleft').html( $('#tmpl-acf-bulk-actions').html() );
		
	}
	
	
	// set only options
	$('#bulk-action-selector-bottom').html('<option value="-1"><?php _e('Bulk Actions'); ?></option><option value="acfsync"><?php _e('Sync', 'acf'); ?></option>');
		
})(jQuery);
</script>
<?php
		
	}
			
}

new acf_admin_field_groups();

endif;

?>