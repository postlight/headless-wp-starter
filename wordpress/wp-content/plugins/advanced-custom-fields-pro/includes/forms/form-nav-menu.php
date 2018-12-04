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
		add_action('wp_nav_menu_item_custom_fields',	array($this, 'wp_nav_menu_item_custom_fields'), 10, 5);
		
		// filters
		add_filter('wp_get_nav_menu_items',		array($this, 'wp_get_nav_menu_items'), 10, 3);
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
	
	
	/**
	*  wp_nav_menu_item_custom_fields
	*
	*  description
	*
	*  @date	30/7/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function wp_nav_menu_item_custom_fields( $item_id, $item, $depth, $args, $id = '' ) {
		
		// vars
		$prefix = "menu-item-acf[$item_id]";
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'nav_menu_item' 		=> $item->type,
			'nav_menu_item_id'		=> $item_id,
			'nav_menu_item_depth'	=> $depth
		));
		
		// render
		if( !empty($field_groups) ) {
			
			// open
			echo '<div class="acf-menu-item-fields acf-fields -clear">';
			
			// loop
			foreach( $field_groups as $field_group ) {
				
				// load fields
				$fields = acf_get_fields( $field_group );
				
				// bail if not fields
				if( empty($fields) ) continue;
				
				// change prefix
				acf_prefix_fields( $fields, $prefix );
				
				// render
				acf_render_fields( $fields, $item_id, 'div', $field_group['instruction_placement'] );
			}
			
			// close
			echo '</div>';
			
			// Trigger append for newly created menu item (via AJAX)
			if( acf_is_ajax('add-menu-item') ): ?>
			<script type="text/javascript">
			(function($) {
				acf.doAction('append', $('#menu-item-settings-<?php echo $item_id; ?>') );
			})(jQuery);
			</script>
			<?php endif;
		}
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
	
	
	/**
	*  wp_get_nav_menu_items
	*
	*  WordPress does not provide an easy way to find the current menu being edited.
	*  This function listens to when a menu's items are loaded and stores the menu.
	*  Needed on nav-menus.php page for new menu with no items
	*
	*  @date	23/2/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function wp_get_nav_menu_items( $items, $menu, $args ) {
		acf_set_data('nav_menu_id', $menu->term_id);
		return $items;
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
		
		// update data (needed for ajax location rules to work)
		acf_set_data('nav_menu_id', $menu_id);
		
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
		
		// vars
		$nav_menu_id = acf_get_data('nav_menu_id');
		$post_id = acf_get_term_post_id( 'nav_menu', $nav_menu_id );
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'nav_menu' => $nav_menu_id
		));
		
?>
<div id="tmpl-acf-menu-settings" style="display: none;">
	<?php
	
	// data (always needed to save nav menu items)
	acf_form_data(array( 
		'screen'	=> 'nav_menu',
		'post_id'	=> $post_id,
		'ajax'		=> 1
	));
	
	
	// render
	if( !empty($field_groups) ) {
		
		// loop
		foreach( $field_groups as $field_group ) {
			
			$fields = acf_get_fields( $field_group );
			
			echo '<div class="acf-menu-settings -'.$field_group['style'].'">';
			
				echo '<h2>' . $field_group['title'] . '</h2>';
			
				echo '<div class="acf-fields -left -clear">';
			
					acf_render_fields( $fields, $post_id, 'div', $field_group['instruction_placement'] );
			
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

acf_new_instance('acf_form_nav_menu');

endif;

?>