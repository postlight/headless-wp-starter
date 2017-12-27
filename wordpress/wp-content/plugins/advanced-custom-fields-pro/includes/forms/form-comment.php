<?php

/*
*  ACF Comment Form Class
*
*  All the logic for adding fields to comments
*
*  @class 		acf_form_comment
*  @package		ACF
*  @subpackage	Forms
*/

if( ! class_exists('acf_form_comment') ) :

class acf_form_comment {
	
	
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
		add_action( 'admin_enqueue_scripts',			array( $this, 'admin_enqueue_scripts' ) );
		
		
		// render
		add_filter('comment_form_field_comment',		array($this, 'comment_form_field_comment'), 999, 1);
		
		//add_action( 'comment_form_logged_in_after',		array( $this, 'add_comment') );
		//add_action( 'comment_form',						array( $this, 'add_comment') );

		
		// save
		add_action( 'edit_comment', 					array( $this, 'save_comment' ), 10, 1 );
		add_action( 'comment_post', 					array( $this, 'save_comment' ), 10, 1 );
		
	}
	
	
	/*
	*  validate_page
	*
	*  This function will check if the current page is for a post/page edit form
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	(boolean)
	*/
	
	function validate_page() {
		
		// global
		global $pagenow;
		
		
		// validate page
		if( $pagenow == 'comment.php' ) {
			
			return true;
			
		}
		
		
		// return
		return false;		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_enqueue_scripts() {
		
		// validate page
		if( ! $this->validate_page() ) {
		
			return;
			
		}
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action('admin_footer',				array($this, 'admin_footer'), 10, 1);
		add_action('add_meta_boxes_comment', 	array($this, 'edit_comment'), 10, 1);

	}
	
	
	/*
	*  edit_comment
	*
	*  This function is run on the admin comment.php page and will render the ACF fields within custom metaboxes to look native
	*
	*  @type	function
	*  @date	19/10/13
	*  @since	5.0.0
	*
	*  @param	$comment (object)
	*  @return	n/a
	*/
	
	function edit_comment( $comment ) {
		
		// vars
		$post_id = "comment_{$comment->comment_ID}";

		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'comment' => get_post_type( $comment->comment_post_ID )
		));
		
		
		// render
		if( !empty($field_groups) ) {
		
			// render post data
			acf_form_data(array( 
				'post_id'	=> $post_id, 
				'nonce'		=> 'comment' 
			));
			
			
			foreach( $field_groups as $field_group ) {
				
				// load fields
				$fields = acf_get_fields( $field_group );
				
				
				// vars
				$o = array(
					'id'			=> 'acf-'.$field_group['ID'],
					'key'			=> $field_group['key'],
					//'style'			=> $field_group['style'],
					'label'			=> $field_group['label_placement'],
					'edit_url'		=> '',
					'edit_title'	=> __('Edit field group', 'acf'),
					//'visibility'	=> $visibility
				);
				
				
				// edit_url
				if( $field_group['ID'] && acf_current_user_can_admin() ) {
					
					$o['edit_url'] = admin_url('post.php?post=' . $field_group['ID'] . '&action=edit');
						
				}
				
				?>
				<div id="acf-<?php echo $field_group['ID']; ?>" class="stuffbox">
					<h3 class="hndle"><?php echo $field_group['title']; ?></h3>
					<div class="inside">
						<?php acf_render_fields( $post_id, $fields, 'div', $field_group['instruction_placement'] ); ?>
						<script type="text/javascript">
						if( typeof acf !== 'undefined' ) {
								
							acf.postbox.render(<?php echo json_encode($o); ?>);
						
						}
						</script>
					</div>
				</div>
				<?php
				
			}
		
		}
		
	}
	
	
	/*
	*  comment_form_field_comment
	*
	*  description
	*
	*  @type	function
	*  @date	18/04/2016
	*  @since	5.3.8
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function comment_form_field_comment( $html ) {
		
		// global
		global $post;
		
		
		// vars
		$post_id = false;

		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'comment' => $post->post_type
		));
		
		
		// bail early if no field groups
		if( !$field_groups ) return $html;
		
		
		// ob
		ob_start();
			
			// render post data
			acf_form_data(array( 
				'post_id'	=> $post_id, 
				'nonce'		=> 'comment' 
			));
			
			echo '<div class="acf-comment-fields acf-fields -clear">';
			
			foreach( $field_groups as $field_group ) {
				
				$fields = acf_get_fields( $field_group );
				
				acf_render_fields( $post_id, $fields, 'p', $field_group['instruction_placement'] );
				
			}
			
			echo '</div>';
		
		
		// append
		$html .= ob_get_contents();
		ob_end_clean();
		
		
		// return
		return $html;
		
	}
	
	
	/*
	*  save_comment
	*
	*  This function will save the comment data
	*
	*  @type	function
	*  @date	19/10/13
	*  @since	5.0.0
	*
	*  @param	comment_id (int)
	*  @return	n/a
	*/
	
	function save_comment( $comment_id ) {
		
		// bail early if not valid nonce
		if( !acf_verify_nonce('comment') ) {
			return $comment_id;
		}
		
		
		// kses
    	if( isset($_POST['acf']) ) {
	    	$_POST['acf'] = wp_kses_post_deep( $_POST['acf'] );
    	}
		
	    
	    // validate and save
	    if( acf_validate_save_post(true) ) {
			acf_save_post( "comment_{$comment_id}" );
		}
		
	}
	
	
	/*
	*  admin_footer
	*
	*  description
	*
	*  @type	function
	*  @date	27/03/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_footer() {
		
?>
<script type="text/javascript">
(function($) {
	
	// vars
	var $spinner = $('#publishing-action .spinner');
	
	
	// create spinner if not exists (may exist in future WP versions)
	if( !$spinner.exists() ) {
		
		// create spinner
		$spinner = $('<span class="spinner"></span>');
		
		
		// append
		$('#publishing-action').prepend( $spinner );
		
	}
	
})(jQuery);	
</script>
<?php
		
	}
			
}

new acf_form_comment();

endif;

?>