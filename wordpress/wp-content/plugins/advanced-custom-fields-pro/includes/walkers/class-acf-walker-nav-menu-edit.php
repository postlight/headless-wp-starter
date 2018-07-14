<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Walker_Nav_Menu_Edit') ) :

class ACF_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
	
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
		
		/* do nothing */
		
	}
	
	
	/**
	 * Start the element output.
	 *
	 * @see Walker_Nav_Menu::start_el()
	 * @since 3.0.0
	 *
	 * @global int $_wp_nav_menu_max_depth
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 * @param int    $id     Not used.
	 */
	 
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			
		// get origional html
		$html = '';
		parent::start_el( $html, $item, $depth, $args, $id );
		
		
		// explode at <fieldset>
		$search = '<fieldset class="field-move';
		$pos = strpos($html, $search);
		$before = substr($html, 0, $pos);
		$after = substr($html, $pos);
		
		
		// inject
		$html = $before . $this->get_fields($item) . $after;
		
		
		// append
		$output .= $html;
	}
	
	
	/*
	*  get_fields
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
	
	function get_fields( $item ) {
		
		// ob
		ob_start();
			
			
		// vars
		$prefix = 'menu-item-acf['.$item->ID.']';
		$post_id = $item->ID;
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'nav_menu_item' => $item->type
		));
		
		
		// render
		if( !empty($field_groups) ) {
			
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
				acf_render_fields( $fields, $post_id, 'div', $field_group['instruction_placement'] );
				
			}
			
			echo '</div>';
			
			
			// Trigger append for newly created menu item (via AJAX)
			if( acf_is_ajax('add-menu-item') ): ?>
			<script type="text/javascript">
			(function($) {
				acf.doAction('append', jQuery('#menu-item-settings-<?php echo $post_id; ?>') );
			})(jQuery);
			</script>
			<?php endif;
			
		}
		
		
		// return
		return ob_get_clean();
		
	}
		
}

endif;

 ?>