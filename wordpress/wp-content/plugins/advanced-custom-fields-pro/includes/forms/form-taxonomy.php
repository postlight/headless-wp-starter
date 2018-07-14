<?php

/*
*  ACF Taxonomy Form Class
*
*  All the logic for adding fields to taxonomy terms
*
*  @class 		acf_form_taxonomy
*  @package		ACF
*  @subpackage	Forms
*/

if( ! class_exists('acf_form_taxonomy') ) :

class acf_form_taxonomy {
	
	var $view = 'add';
	
	
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
		add_action('admin_enqueue_scripts',	array($this, 'admin_enqueue_scripts'));
		
		
		// save
		add_action('create_term',			array($this, 'save_term'), 10, 3);
		add_action('edit_term',				array($this, 'save_term'), 10, 3);
		
		
		// delete
		add_action('delete_term',			array($this, 'delete_term'), 10, 4);
		
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
		if( $pagenow === 'edit-tags.php' || $pagenow === 'term.php' ) {
			
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
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts() {
		
		// validate page
		if( !$this->validate_page() ) {
			
			return;
			
		}
		
		
		// vars
		$screen = get_current_screen();
		$taxonomy = $screen->taxonomy;
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action('admin_footer',					array($this, 'admin_footer'), 10, 1);
		add_action("{$taxonomy}_add_form_fields", 	array($this, 'add_term'), 10, 1);
		add_action("{$taxonomy}_edit_form", 		array($this, 'edit_term'), 10, 2);
		
	}
	
	
	/*
	*  add_term
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function add_term( $taxonomy ) {
		
		// vars
		$post_id = acf_get_term_post_id( $taxonomy, 0 );
		
		
		// update vars
		$this->view = 'add';
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'taxonomy' => $taxonomy
		));
		
		
		// render
		if( !empty($field_groups) ) {
			
			// data
			acf_form_data(array( 
				'screen'	=> 'taxonomy',
				'post_id'	=> $post_id, 
			));
			
			// wrap
			echo '<div id="acf-term-fields" class="acf-fields -clear">';
			
			// loop
			foreach( $field_groups as $field_group ) {
				$fields = acf_get_fields( $field_group );
				acf_render_fields( $fields, $post_id, 'div', 'field' );
			}
			
			// wrap
			echo '</div>';
			
		}
		
	}
	
	
	/*
	*  edit_term
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function edit_term( $term, $taxonomy ) {
		
		// vars
		$post_id = acf_get_term_post_id( $term->taxonomy, $term->term_id );
		
		
		// update vars
		$this->view = 'edit';
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'taxonomy' => $taxonomy
		));
		
		
		// render
		if( !empty($field_groups) ) {
			
			acf_form_data(array( 
				'screen'	=> 'taxonomy',
				'post_id'	=> $post_id,
			));
			
			foreach( $field_groups as $field_group ) {
				
				// title
				if( $field_group['style'] == 'default' ) {
					echo '<h2>' . $field_group['title'] . '</h2>';
				}
				
				// fields
				echo '<table class="form-table">';
					$fields = acf_get_fields( $field_group );
					acf_render_fields( $fields, $post_id, 'tr', 'field' );
				echo '</table>';
				
			}
			
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
	var view = '<?php echo $this->view; ?>';
	
	// add missing spinners
	var $submit = $('input.button-primary');
	if( !$submit.next('.spinner').length ) {
		$submit.after('<span class="spinner"></span>');
	}
	
<?php 
	
// add view
if( $this->view == 'add' ): ?>
	
	// vars
	var $form = $('#addtag');
	var $fields = $('#acf-term-fields');
	var html = $fields.html();
		
	// WP triggers click as primary action
	$submit.on('click', function( e ){
		
		// validate
		var valid = acf.validateForm({
			form: $form,
			event: e,
			lock: false
		});
		
		// if not valid, stop event and allow validation to continue
		if( !valid ) {
			e.preventDefault();
			e.stopImmediatePropagation();
		}
	});
	
	// listen to AJAX add-tag complete
	$(document).ajaxComplete(function(event, xhr, settings) {
		
		// bail early if is other ajax call
		if( settings.data.indexOf('action=add-tag') == -1 ) {
			return;
		}
		
		// bail early if response contains error
		if( xhr.responseText.indexOf('wp_error') !== -1 ) {
			return;
		}
		
		// action for 3rd party customization
		acf.doAction('remove', $fields);
		
		// reset HTML
		$fields.html( html );
		
		// action for 3rd party customization
		acf.doAction('append', $fields);
		
		// reset unload
		acf.unload.reset();
	});
	
<?php endif; ?>
	
})(jQuery);	
</script>
<?php
		
	}
	
	
	/*
	*  save_term
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_term( $term_id, $tt_id, $taxonomy ) {
		
		// vars
		$post_id = acf_get_term_post_id( $taxonomy, $term_id );
		
		
		// verify and remove nonce
		if( !acf_verify_nonce('taxonomy') ) return $term_id;
		
	    
	    // valied and show errors
		acf_validate_save_post( true );
			
			
	    // save
		acf_save_post( $post_id );
			
	}
	
	
	/*
	*  delete_term
	*
	*  description
	*
	*  @type	function
	*  @date	15/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function delete_term( $term, $tt_id, $taxonomy, $deleted_term ) {
		
		// bail early if termmeta table exists
		if( acf_isset_termmeta() ) return $term;
		
		
		// globals
		global $wpdb;
		
		
		// vars
		$search = $taxonomy . '_' . $term . '_%';
		$_search = '_' . $search;
		
		
		// escape '_'
		// http://stackoverflow.com/questions/2300285/how-do-i-escape-in-sql-server
		$search = str_replace('_', '\_', $search);
		$_search = str_replace('_', '\_', $_search);
		
		
		// delete
		$result = $wpdb->query($wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
			$search,
			$_search 
		));
		
	}
			
}

new acf_form_taxonomy();

endif;


?>
