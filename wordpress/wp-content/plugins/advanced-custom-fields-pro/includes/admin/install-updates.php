<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
*  acf_update_500
*
*  These functions will update the DB for ACF v5.0.0
*
*  @type	function
*  @date	10/09/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_update_500() {
	
	// action for 3rd party
	do_action('acf/update_500');
	
	
	// field groups
	acf_update_500_field_groups();
	
	
	// version
	acf_update_db_version('5.0.0');
	
}

function acf_update_500_field_groups() {
	
	// vars
	$ofgs = get_posts(array(
		'numberposts' 		=> -1,
		'post_type' 		=> 'acf',
		'orderby' 			=> 'menu_order title',
		'order' 			=> 'asc',
		'suppress_filters'	=> true,
	));
	
	
	// check
	if( !$ofgs ) return;
	
	
	// loop
	foreach( $ofgs as $ofg ){
		
		acf_update_500_field_group( $ofg );
	 		
	}
	
}

function acf_update_500_field_group( $ofg ) {
	
	// global
	global $wpdb;
	
	
	// create new field group
	$nfg = array(
		'ID'			=> 0,
		'title'			=> $ofg->post_title,
		'menu_order'	=> $ofg->menu_order,
	);
	
	
	// location rules
	$groups = array();
	
	
	// get all rules
 	$rules = get_post_meta($ofg->ID, 'rule', false);
 	
 	if( is_array($rules) ) {
 	
 		$group_no = 0;
 		
	 	foreach( $rules as $rule ) {
	 		
	 		// if field group was duplicated, it may now be a serialized string!
	 		$rule = maybe_unserialize($rule);
	 		
	 		
		 	// does this rule have a group?
		 	// + groups were added in 4.0.4
		 	if( !isset($rule['group_no']) ) {
		 	
			 	$rule['group_no'] = $group_no;
			 	
			 	// sperate groups?
			 	if( get_post_meta($ofg->ID, 'allorany', true) == 'any' ) {
			 	
				 	$group_no++;
				 	
			 	}
			 	
		 	}
		 	
		 	
		 	// extract vars
		 	$group = acf_extract_var( $rule, 'group_no' );
		 	$order = acf_extract_var( $rule, 'order_no' );
		 	
		 	
		 	// add to group
		 	$groups[ $group ][ $order ] = $rule;
		 	
		 	
		 	// sort rules
		 	ksort( $groups[ $group ] );
 	
	 	}
	 	
	 	// sort groups
		ksort( $groups );
 	}
 	
 	$nfg['location'] = $groups;
 	
 	
	// settings
 	if( $position = get_post_meta($ofg->ID, 'position', true) ) {
 	
		$nfg['position'] = $position;
		
	}
	
 	if( $layout = get_post_meta($ofg->ID, 'layout', true) ) {
 	
		$nfg['layout'] = $layout;
		
	}
	
 	if( $hide_on_screen = get_post_meta($ofg->ID, 'hide_on_screen', true) ) {
 	
		$nfg['hide_on_screen'] = maybe_unserialize($hide_on_screen);
		
	}
	
	
	// Note: acf_update_field_group will call the acf_get_valid_field_group function and apply 'compatibility' changes
	
	
	// save field group
	$nfg = acf_update_field_group( $nfg );
	
	
	// action for 3rd party
	do_action('acf/update_500_field_group', $nfg, $ofg);
	
	
	// trash?
	if( $ofg->post_status == 'trash' ) {
		
		acf_trash_field_group( $nfg['ID'] );
		
	}
	
	
	// get field from postmeta
	$rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s", $ofg->ID, 'field_%'), ARRAY_A);
	
	
	// check
	if( $rows ) {
		
		// loop
		foreach( $rows as $row ) {
			
			// bail early if key already migrated (potential duplicates in DB)
			if( acf_has_done('update_500_field_group_' . $ofg->ID . '_' . $row['meta_key']) ) continue;
			
			
			// vars
			$field = $row['meta_value'];
			$field = maybe_unserialize( $field );
			$field = maybe_unserialize( $field ); // run again for WPML
			
			
			// add parent
			$field['parent'] = $nfg['ID'];
			
			
			// migrate field
			$field = acf_update_500_field( $field );
			
		}
		
 	}
	
	
	// return
	return $nfg;
	
}


function acf_update_500_field( $field ) {
	
	// orig
	$orig = $field;
	
	
	// order_no is now menu_order
	$field['menu_order'] = acf_extract_var( $field, 'order_no' );
	
	
	// correct very old field keys
	if( substr($field['key'], 0, 6) !== 'field_' ) {
	
		$field['key'] = 'field_' . str_replace('field', '', $field['key']);
		
	}
	
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// save field
	$field = acf_update_field( $field );
	
	
	// sub fields
	if( $field['type'] == 'repeater' ) {
		
		// get sub fields
		$sub_fields = acf_extract_var( $orig, 'sub_fields' );
		
		
		// save sub fields
		if( !empty($sub_fields) ) {
			
			$keys = array_keys($sub_fields);
		
			foreach( $keys as $key ) {
			
				$sub_field = acf_extract_var($sub_fields, $key);
				$sub_field['parent'] = $field['ID'];
				
				acf_update_500_field( $sub_field );
				
			}
			
		}
		
	
	} elseif( $field['type'] == 'flexible_content' ) {
		
		// get layouts
		$layouts = acf_extract_var( $orig, 'layouts' );
		
		
		// update layouts
		$field['layouts'] = array();
		
		
		// save sub fields
		if( !empty($layouts) ) {
			
			foreach( $layouts as $layout ) {
				
				// vars
				$layout_key = uniqid();
				
				
				// append layotu key
				$layout['key'] = $layout_key;
				
				
				// extract sub fields
				$sub_fields = acf_extract_var($layout, 'sub_fields');
				
				
				// save sub fields
				if( !empty($sub_fields) ) {
					
					$keys = array_keys($sub_fields);
					
					foreach( $keys as $key ) {
					
						$sub_field = acf_extract_var($sub_fields, $key);
						$sub_field['parent'] = $field['ID'];
						$sub_field['parent_layout'] = $layout_key;
						
						acf_update_500_field( $sub_field );
						
					}
					// foreach
					
				}
				// if
				
				
				// append layout
				$field['layouts'][] = $layout;
			
			}
			// foreach
			
		}
		// if
		
		
		// save field again with less sub field data
		$field = acf_update_field( $field );
		
	}
	
	
	// action for 3rd party
	do_action('acf/update_500_field', $field);
	
	
	// return
	return $field;
	
}


/*
*  acf_update_550
*
*  These functions will update the DB for ACF v5.5.0
*
*  @type	function
*  @date	10/09/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_update_550() { //acf_log('acf_update_550');
	
	// action for 3rd party
	do_action('acf/update_550');
	
	
	// termmeta
	acf_update_550_termmeta();
	
	
	// version
	acf_update_db_version('5.5.0');
	
}


/*
*  acf_update_550_termmeta
*
*  This function will migrate all term meta
*
*  @type	function
*  @date	3/09/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/


function acf_update_550_termmeta() { //acf_log('acf_update_550_termmeta');
	
	// bail early if no table
	if( !acf_isset_termmeta() ) {
		
		update_option('acf_update_550_termmeta', 1); // no longer used
		//echo __('Term meta upgrade not possible (termmeta table does not exist)', 'acf');
		return;
		
	}
	
	
	// vars
	$taxonomies = get_taxonomies(false, 'objects');
	
	
	// bail early if no taxonomies
	if( !$taxonomies ) return;
	
	
	// loop
	foreach( $taxonomies as $taxonomy ) {
		
		acf_update_550_taxonomy( $taxonomy->name );
		
	}
	
	
	// delete trigger
	delete_option('acf_update_550_termmeta');
	
	
	// action for 3rd party
	do_action('acf/update_550_termmeta');
	
}


/*
*  acf_update_550_taxonomy
*
*  This function will migrate term meta for a specific taxonomy
*
*  @type	function
*  @date	3/09/2016
*  @since	5.4.0
*
*  @param	$taxonomy (string)
*  @return	n/a
*/

function acf_update_550_taxonomy( $taxonomy ) { //acf_log('acf_update_550_taxonomy', $taxonomy);
	
	// global
	global $wpdb;
	
	
	// vars
	$search = $taxonomy . '_%';
	$_search = '_' . $search;
	
	
	// escape '_'
	// http://stackoverflow.com/questions/2300285/how-do-i-escape-in-sql-server
	$search = str_replace('_', '\_', $search);
	$_search = str_replace('_', '\_', $_search);
	
	
	// search
	// results show faster query times using 2 LIKE vs 2 wildcards
	$rows = $wpdb->get_results($wpdb->prepare(
		"SELECT * 
		FROM $wpdb->options 
		WHERE option_name LIKE %s 
		OR option_name LIKE %s",
		$search,
		$_search 
	), ARRAY_A);
	
	
	// bail early if no rows
	if( empty($rows) ) return;
	
	
	// vars
	$search = $taxonomy . '_';
	$_search = '_' . $search;
	
	
	// loop
	foreach( $rows as $row ) {
		
		// use regex to find (_)taxonomy_(term_id)_(field_name)
		$matches = null;
		$regexp = '/^(_?)' . $taxonomy . '_(\d+)_(.+)/';
		
		
		// bail early if no match
		if( !preg_match($regexp, $row['option_name'], $matches) ) continue;
		
		
		/*
		Array
		(
		    [0] => category_3_color
		    [1] => 
		    [2] => 3
		    [3] => color
		)
		*/
		
		
		// vars
		$term_id = $matches[2];
		$name = $matches[1] . $matches[3];
		$value = $row['option_value'];
		
		
		// update
		update_metadata( 'term', $term_id, $name, $value );
		
	}
	
	
	// action for 3rd party
	do_action('acf/update_550_taxonomy', $taxonomy);
	
}

?>