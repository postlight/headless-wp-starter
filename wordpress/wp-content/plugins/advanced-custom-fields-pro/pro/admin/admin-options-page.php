<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_admin_options_page') ) :

class acf_admin_options_page {
	
	/** @var array Contains the current options page */
	var $page;
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// add menu items
		add_action('admin_menu', array($this,'admin_menu'), 99, 0);
		
	}
	
	
	/*
	*  admin_menu
	*
	*  description
	*
	*  @type	function
	*  @date	24/02/2014
	*  @since	5.0.0
	*
	*  @param	
	*  @return	
	*/
	
	function admin_menu() {
		
		// vars
		$pages = acf_get_options_pages();
		
		
		// bail early if no pages
		if( empty($pages) ) return;
		
		
		// loop
		foreach( $pages as $page ) {
			
			// vars
			$slug = '';
			
			
			// parent
			if( empty($page['parent_slug']) ) {
				
				$slug = add_menu_page( $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], array($this, 'html'), $page['icon_url'], $page['position'] );
			
			// child
			} else {
				
				$slug = add_submenu_page( $page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], array($this, 'html') );
				
			}
			
			
			// actions
			add_action("load-{$slug}", array($this,'admin_load'));
			
		}
		
	}
	
	
	/*
	*  load
	*
	*  description
	*
	*  @type	function
	*  @date	2/02/13
	*  @since	3.6
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_load() {
		
		// globals
		global $plugin_page;
		
		
		// vars
		$this->page = acf_get_options_page( $plugin_page );
		
		
		// get post_id (allow lang modification)
		$this->page['post_id'] = acf_get_valid_post_id($this->page['post_id']);
		
		
		// verify and remove nonce
		if( acf_verify_nonce('options') ) {
		
			// save data
		    if( acf_validate_save_post(true) ) {
		    	
		    	// set autoload
		    	acf_update_setting('autoload', $this->page['autoload']);
		    	
		    	
		    	// save
				acf_save_post( $this->page['post_id'] );
				
				
				// redirect
				wp_redirect( add_query_arg(array('message' => '1')) );
				exit;
				
			}
			
		}
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action( 'acf/input/admin_enqueue_scripts',		array($this,'admin_enqueue_scripts') );
		add_action( 'acf/input/admin_head',					array($this,'admin_head') );
		
		
		// add columns support
		add_screen_option('layout_columns', array('max'	=> 2, 'default' => 2));
		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This function will enqueue the 'post.js' script which adds support for 'Screen Options' column toggle
	*
	*  @type	function
	*  @date	23/03/2016
	*  @since	5.3.2
	*
	*  @param	
	*  @return	
	*/
	
	function admin_enqueue_scripts() {
		
		wp_enqueue_script('post');
		
	}
	
	
	/*
	*  admin_head
	*
	*  This action will find and add field groups to the current edit page
	*
	*  @type	action (admin_head)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_head() {
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'options_page' => $this->page['menu_slug']
		));
		
		
		// notices
		if( !empty($_GET['message']) && $_GET['message'] == '1' ) {
		
			acf_add_admin_notice( $this->page['updated_message'] );
			
		}
		
		
		// add submit div
		add_meta_box('submitdiv', __('Publish','acf'), array($this, 'postbox_submitdiv'), 'acf_options_page', 'side', 'high');
		
		
		
		if( empty($field_groups) ) {
		
			acf_add_admin_notice( sprintf( __('No Custom Field Groups found for this options page. <a href="%s">Create a Custom Field Group</a>', 'acf'), admin_url() . 'post-new.php?post_type=acf-field-group' ), 'error');
		
		} else {
			
			foreach( $field_groups as $i => $field_group ) {
			
				// vars
				$id = "acf-{$field_group['key']}";
				$title = $field_group['title'];
				$context = $field_group['position'];
				$priority = 'high';
				$args = array( 'field_group' => $field_group );
				
				
				// tweaks to vars
				if( $context == 'acf_after_title' ) {
					
					$context = 'normal';
					
				} elseif( $context == 'side' ) {
				
					$priority = 'core';
					
				}
				
				
				// filter for 3rd party customization
				$priority = apply_filters('acf/input/meta_box_priority', $priority, $field_group);
				
				
				// add meta box
				add_meta_box( $id, $title, array($this, 'postbox_acf'), 'acf_options_page', $context, $priority, $args );
				
				
			}
			// foreach
			
		}
		// if
		
	}
	
	
	/*
	*  postbox_submitdiv
	*
	*  This function will render the submitdiv metabox
	*
	*  @type	function
	*  @date	23/03/2016
	*  @since	5.3.2
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function postbox_submitdiv( $post, $args ) {
		
		?>
		<div id="major-publishing-actions">

			<div id="publishing-action">
				<span class="spinner"></span>
				<input type="submit" accesskey="p" value="<?php echo $this->page['update_button']; ?>" class="button button-primary button-large" id="publish" name="publish">
			</div>
			
			<div class="clear"></div>
		
		</div>
		<?php
		
	}
	
	
	/*
	*  render_meta_box
	*
	*  description
	*
	*  @type	function
	*  @date	24/02/2014
	*  @since	5.0.0
	*
	*  @param	$post (object)
	*  @param	$args (array)
	*  @return	n/a
	*/
	
	function postbox_acf( $post, $args ) {
		
		// extract args
		extract( $args ); // all variables from the add_meta_box function
		extract( $args ); // all variables from the args argument
		
		
		// vars
		$o = array(
			'id'			=> $id,
			'key'			=> $field_group['key'],
			'style'			=> $field_group['style'],
			'label'			=> $field_group['label_placement'],
			'editLink'		=> '',
			'editTitle'		=> __('Edit field group', 'acf'),
			'visibility'	=> true
		);
		
		
		// edit_url
		if( $field_group['ID'] && acf_current_user_can_admin() ) {
			
			$o['editLink'] = admin_url('post.php?post=' . $field_group['ID'] . '&action=edit');
				
		}
		
		
		// load fields
		$fields = acf_get_fields( $field_group );
		
		
		// render
		acf_render_fields( $fields, $this->page['post_id'], 'div', $field_group['instruction_placement'] );
		
		
		
?>
<script type="text/javascript">
if( typeof acf !== 'undefined' ) {
		
	acf.newPostbox(<?php echo json_encode($o); ?>);	

}
</script>
<?php
		
	}
	
	
	/*
	*  html
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function html() {
		
		// load view
		acf_get_view(dirname(__FILE__) . '/views/html-options-page.php', $this->page);
				
	}
	
	
}


// initialize
new acf_admin_options_page();

endif;

?>