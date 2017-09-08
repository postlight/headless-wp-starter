<?php 

/*
*  acf_esc_html
*
*  This function will encode <script> tags for safe output
*
*  @type	function
*  @date	25/6/17
*  @since	5.6.0
*
*  @param	string (string)
*  @return	(string)
*/

function acf_esc_html( $string = '' ) {
	
	// cast
	$string = (string) $string;
	
	
	// replace
	$string = str_replace('<script', htmlspecialchars('<script'), $string);
	$string = str_replace('</script', htmlspecialchars('</script'), $string);
	
	
	// return
	return $string;
	
}


/*
*  acf_esc_atts
*
*  This function will escape an array of attributes and return as HTML
*
*  @type	function
*  @date	27/6/17
*  @since	5.6.0
*
*  @param	$atts (array)
*  @return	(string)
*/

function acf_esc_atts( $atts = array() ) {
	
	// vars
	$html = '';
	
	
	// loop
	foreach( $atts as $k => $v ) {
		
		// string
		if( is_string($v) ) {
			
			$v = trim($v);
			
		// boolean	
		} elseif( is_bool($v) ) {
			
			$v = $v ? 1 : 0;
			
		// object
		} elseif( is_array($v) || is_object($v) ) {
			
			$v = json_encode($v);
			
		}
		
		
		// append
		$html .= esc_attr( $k ) . '="' . esc_attr( $v ) . '" ';
		
	}
	
	
	// return
	return trim( $html );
	
}


/*
*  acf_esc_atts_e
*
*  This function will echo acf_esc_atts
*
*  @type	function
*  @date	27/6/17
*  @since	5.6.0
*
*  @param	$atts (array)
*  @return	n/a
*/

function acf_esc_atts_e( $atts = array() ) {
	
	echo acf_esc_atts( $atts );
	
}


/*
*  acf_get_text_input
*
*  This function will return HTML for a text input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	(string)
*/

function acf_get_text_input( $atts = array() ) {
	
	$atts['type'] = isset($atts['type']) ? $atts['type'] : 'text';
	return '<input ' . acf_esc_atts( $atts ) . ' />';
	
}


/*
*  acf_text_input
*
*  This function will output HTML for a text input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	n/a
*/

function acf_text_input( $atts = array() ) {
	
	echo acf_get_text_input( $atts );
	
}


/*
*  acf_get_hidden_input
*
*  This function will return HTML for a hidden input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	(string)
*/

function acf_get_hidden_input( $atts = array() ) {
	
	$atts['type'] = 'hidden';
	return acf_get_text_input( $atts );
	
}


/*
*  acf_hidden_input
*
*  This function will output HTML for a generic input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	n/a
*/

function acf_hidden_input( $atts = array() ) {
	
	echo acf_get_hidden_input( $atts ) . "\n";
	
}


/*
*  acf_get_textarea_input
*
*  This function will return HTML for a textarea input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	(string)
*/

function acf_get_textarea_input( $atts = array() ) {
	
	$value = acf_extract_var( $atts, 'value', '' );
	return '<textarea ' . acf_esc_atts( $atts ) . '>' . esc_textarea( $value ) . '</textarea>';
		
}


/*
*  acf_textarea_input
*
*  This function will output HTML for a textarea input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	n/a
*/

function acf_textarea_input( $atts = array() ) {
	
	echo acf_get_textarea_input( $atts );
	
}


/*
*  acf_get_checkbox_input
*
*  This function will return HTML for a checkbox input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	(string)
*/

function acf_get_checkbox_input( $atts = array() ) {
	
	$label = acf_extract_var( $atts, 'label', '' );
	$atts['type'] = acf_maybe_get( $atts, 'type', 'checkbox' );
	return '<label><input ' . acf_esc_attr( $atts ) . '/>' . acf_esc_html( $label ) . '</label>';
		
}


/*
*  acf_checkbox_input
*
*  This function will output HTML for a checkbox input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	n/a
*/

function acf_checkbox_input( $atts = array() ) {
	
	echo acf_get_checkbox_input( $atts );
	
}


/*
*  acf_get_radio_input
*
*  This function will return HTML for a radio input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	(string)
*/

function acf_get_radio_input( $atts = array() ) {
	
	$atts['type'] = 'radio';
	return acf_get_checkbox_input( $atts );
		
}


/*
*  acf_radio_input
*
*  This function will output HTML for a radio input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	n/a
*/

function acf_radio_input( $atts = array() ) {
	
	echo acf_get_radio_input( $atts );
	
}


/*
*  acf_get_select_input
*
*  This function will return HTML for a select input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	(string)
*/

function acf_get_select_input( $atts = array() ) {
	
	// vars
	$value = (array) acf_extract_var( $atts, 'value' );
	$choices = (array) acf_extract_var( $atts, 'choices' );
	
	
	// html
	$html = '';
	$html .= '<select ' . acf_esc_atts( $atts ) . '>' . "\n";
	$html .= acf_walk_select_input( $choices, $value );
	$html .= '</select>' . "\n";
	
	
	// return
	return $html;
		
}


/*
*  acf_walk_select_input
*
*  This function will return the HTML for a select input's choices
*
*  @type	function
*  @date	27/6/17
*  @since	5.6.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_walk_select_input( $choices = array(), $values = array(), $depth = 0 ) {
	
	// bail ealry if no choices
	if( empty($choices) ) return '';
	
	
	// vars
	$html = '';
	
	
	// sanitize values for 'selected' matching
	if( $depth == 0 ) {
		$values = array_map('esc_attr', $values);
	}
	
	
	// loop
	foreach( $choices as $value => $label ) {
		
		// optgroup
		if( is_array($label) ){
			
			$html .= '<optgroup label="' . esc_attr($value) . '">' . "\n";
			$html .= acf_walk_select_input( $label, $values, $depth+1 );
			$html .= '</optgroup>';
		
		// option	
		} else {
			
			// vars
			$atts = array( 'value' => $value );
			$pos = array_search( esc_attr($value), $values );
		
		
			// selected
			if( $pos !== false ) {
				$atts['selected'] = 'selected';
				$atts['data-i'] = $pos;
			}
			
			
			// append
			$html .= '<option ' . acf_esc_attr($atts) . '>' . esc_html($label) . '</option>' . "\n";
			
		}
		
	}
	
	
	// return
	return $html;
	
}


/*
*  acf_select_input
*
*  This function will output HTML for a select input
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$atts
*  @return	n/a
*/

function acf_select_input( $atts = array() ) {
	
	echo acf_get_select_input( $atts );
	
}



/*
function acf_test_esc_html( $string = '' ) {
	
	$s = '';
	
	
	$time_start = microtime(true);
	$s .= wp_kses_post( $string );
	$s .= ' = ('. (microtime(true) - $time_start) .')';
	
	$s .= '-----';

	
	$time_start = microtime(true);
	$s .= str_replace(array('<script', '</script'), array(htmlspecialchars('<script'), htmlspecialchars('</script')), $string);
	$s .= ' = ('. (microtime(true) - $time_start) .')';
	

	$time_start = microtime(true);
	if( strpos($string, '<script') ) {
		$s .= str_replace(array('<script', '</script'), array(htmlspecialchars('<script'), htmlspecialchars('</script')), $string);
	}
	$s .= ' = ('. (microtime(true) - $time_start) .')';
	
	return $s;
	
}
*/



/*
*  acf_esc_attr
*
*  Deprecated since 5.6.0
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$atts (array)
*  @return	n/a
*/

function acf_esc_attr( $atts ) {
	
	return acf_esc_atts( $atts );
	
}


/*
*  acf_esc_attr_e
*
*  Deprecated since 5.6.0
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$atts (array)
*  @return	n/a
*/

function acf_esc_attr_e( $atts ) {
	
	acf_esc_atts_e( $atts );
	
}


 ?>