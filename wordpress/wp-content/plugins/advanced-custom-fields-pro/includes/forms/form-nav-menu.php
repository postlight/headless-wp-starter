<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_form_nav_menu') ) :

class acf_form_nav_menu {
	
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
		add_action('admin_enqueue_scripts',		array($this, 'admin_enqueue_scripts'));
		add_action('wp_update_nav_menu',		array($this, 'update_nav_menu'));
		add_action('acf/validate_save_post',	array($this, 'acf_validate_save_post'), 5);
		
		
		// filters
		add_filter('wp_edit_nav_menu_walker',	array($this, 'wp_edit_nav_menu_walker'), 10, 2);
		
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
		
		// validate screen
		if( !acf_is_screen('nav-menus') ) return;
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action('admin_footer', array($this, 'admin_footer'), 1);

	}
	
	
	/*
	*  update_nav_menu
	*
	*  description
	*
	*  @type	function
	*  @date	26/5/17
	*  @since	5.6.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function update_nav_menu( $menu_id ) {
		
		// vars
		$post_id = acf_get_term_post_id( 'nav_menu', $menu_id );
		
		
		// verify and remove nonce
		if( !acf_verify_nonce('nav_menu') ) return $menu_id;
		
			   
	    // validate and show errors
		acf_validate_save_post( true );
		
		
	    // save
		acf_save_post( $post_id );
		
		
		// save nav menu items
		$this->update_nav_menu_items( $menu_id );
		
	}
	
	
	/*
	*  update_nav_menu_items
	*
	*  description
	*
	*  @type	function
	*  @date	26/5/17
	*  @since	5.6.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function update_nav_menu_items( $menu_id ) {
			
		// bail ealry if not set
		if( empty($_POST['menu-item-acf']) ) return;
		
		
		// loop
		foreach( $_POST['menu-item-acf'] as $post_id => $values ) {
			
			acf_save_post( $post_id, $values );
				
		}
			
	}
	
	
	/*
	*  wp_edit_nav_menu_walker
	*
	*  description
	*
	*  @type	function
	*  @date	26/5/17
	*  @since	5.6.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function wp_edit_nav_menu_walker( $class, $menu_id = 0 ) {
		
		// global
		global $acf_menu;
		
		
		// set var
		$acf_menu = (int) $menu_id;
		
		
		// include walker
		if( class_exists('Walker_Nav_Menu_Edit') ) {
			acf_include('includes/walkers/class-acf-walker-nav-menu-edit.php');
		}
		
		
		// return
		return 'ACF_Walker_Nav_Menu_Edit';
		
	}
	
	
	/*
	*  acf_validate_save_post
	*
	*  This function will loop over $_POST data and validate
	*
	*  @type	action 'acf/validate_save_post' 5
	*  @date	7/09/2016
	*  @since	5.4.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function acf_validate_save_post() {
		
		// bail ealry if not set
		if( empty($_POST['menu-item-acf']) ) return;
		
		
		// loop
		foreach( $_POST['menu-item-acf'] as $post_id => $values ) {
			
			// vars
			$prefix = 'menu-item-acf['.$post_id.']';
			
			
			// validate
			acf_validate_values( $values, $prefix );
				
		}
				
	}
	
	
	/*
	*  admin_footer
	*
	*  This function will add some custom HTML to the footer of the edit page
	*
	*  @type	function
	*  @date	11/06/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_footer() {
		
		// global
		global $acf_menu;
		
		
		// vars
		$post_id = acf_get_term_post_id( 'nav_menu', $acf_menu );
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'nav_menu' => $acf_menu
		));
		
		
?>
<div id="tmpl-acf-menu-settings" style="display: none;">
	<?php
	
	// data (always needed to save nav menu items)
	acf_form_data(array( 
		'post_id'	=> $post_id, 
		'nonce'		=> 'nav_menu',
	));
	
	
	// render
	if( !empty($field_groups) ) {
		
		// loop
		foreach( $field_groups as $field_group ) {
			
			$fields = acf_get_fields( $field_group );
			
			echo '<div class="acf-menu-settings -'.$field_group['style'].'">';
			
				echo '<h2>' . $field_group['title'] . '</h2>';
			
				echo '<div class="acf-fields -left -clear">';
			
					acf_render_fields( $post_id, $fields, 'div', $field_group['instruction_placement'] );
			
				echo '</div>';
			
			echo '</div>';
			
		}
		
	}
	
	?>
</div>
<script type="text/javascript">
(function($) {
	
	// append html
	$('#post-body-content').append( $('#tmpl-acf-menu-settings').html() );
	
	
	// avoid WP over-writing $_POST data
	// - https://core.trac.wordpress.org/ticket/41502#ticket
	$(document).on('submit', '#update-nav-menu', function() {

		// vars
		var $form = $(this);
		var $input = $('input[name="nav-menu-data"]');
		
		
		// decode json
		var json = $form.serializeArray();
		var json2 = [];
		
		
		// loop
		$.each( json, function( i, pair ) {
			
			// avoid nesting (unlike WP)
			if( pair.name === 'nav-menu-data' ) return;
			
			
			// bail early if is 'acf[' input
			if( pair.name.indexOf('acf[') > -1 ) return;
						
			
			// append
			json2.push( pair );
			
		});
		
		
		// update
		$input.val( JSON.stringify(json2) );
		
	});
		
		
})(jQuery);	
</script>
<?php
		
	}
	
}

new acf_form_nav_menu();

endif;

?>