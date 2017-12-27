<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Taxonomy_Field_Walker') ) :

class ACF_Taxonomy_Field_Walker extends Walker {
	
	var $field = null,
		$tree_type = 'category',
		$db_fields = array ( 'parent' => 'parent', 'id' => 'term_id' );
	
	function __construct( $field ) {
	
		$this->field = $field;
		
	}

	function start_el( &$output, $term, $depth = 0, $args = array(), $current_object_id = 0) {
		
		// vars
		$selected = in_array( $term->term_id, $this->field['value'] );
		
		
		// append
		$output .= '<li data-id="' . $term->term_id . '"><label><input type="' . $this->field['field_type'] . '" name="' . $this->field['name'] . '" value="' . $term->term_id . '" ' . ($selected ? 'checked="checked"' : '') . ' /> <span>' . $term->name . '</span></label>';
				
	}
	
	function end_el( &$output, $term, $depth = 0, $args = array() ) {
	
		// append
		$output .= '</li>' .  "\n";
		
	}
	
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		
		// append
		$output .= '<ul class="children acf-bl">' . "\n";
		
	}

	function end_lvl( &$output, $depth = 0, $args = array() ) {
	
		// append
		$output .= '</ul>' . "\n";
		
	}
	
}

endif;

 ?>