<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Ajax_Check_Screen') ) :

class ACF_Ajax_Check_Screen extends ACF_Ajax {
	
	/** @var string The AJAX action name */
	var $action = 'acf/ajax/check_screen';
	
	/** @var bool Prevents access for non-logged in users */
	var $public = false;
	
	/**
	*  get_response
	*
	*  The actual logic for this AJAX request.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	mixed The response data to send back or WP_Error.
	*/
	
	function response() {
		
		// vars
		$args = acf_parse_args($this->request, array(
			'screen'	=> '',
			'post_id'	=> 0,
			'ajax'		=> 1,
			'exists'	=> array()
		));
		
		// vars
		$json = array(
			'results'	=> array(),
			'style'		=> ''
		);
		
		// get field groups
		$field_groups = acf_get_field_groups( $args );
		
		// loop through field groups
		if( $field_groups ) {
		foreach( $field_groups as $i => $field_group ) {
			
			// vars
			$item = array(
				'key'	=> $field_group['key'],
				'title'	=> $field_group['title'],
				'html'	=> ''
			);
			
			// append first field group's style
			if( $i == 0 ) {
				$json['style'] = acf_get_field_group_style( $field_group );
			}
			
			// append html if doesnt already exist on page
			if( !in_array($field_group['key'], $args['exists']) ) {
				
				// load fields
				$fields = acf_get_fields( $field_group );

				// get field HTML
				ob_start();
				
				// render
				acf_render_fields( $fields, $args['post_id'], 'div', $field_group['instruction_placement'] );
				
				$item['html'] = ob_get_clean();
			}
			
			// append
			$json['results'][] = $item;
		}}
		
		
		// return
		return $json;
	}
}

acf_new_instance('ACF_Ajax_Check_Screen');

endif; // class_exists check

?>