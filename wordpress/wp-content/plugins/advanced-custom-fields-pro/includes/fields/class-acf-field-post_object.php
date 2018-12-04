<?php

if( ! class_exists('acf_field_post_object') ) :

class acf_field_post_object extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'post_object';
		$this->label = __("Post Object",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'post_type'		=> array(),
			'taxonomy'		=> array(),
			'allow_null' 	=> 0,
			'multiple'		=> 0,
			'return_format'	=> 'object',
			'ui'			=> 1,
		);
		
		
		// extra
		add_action('wp_ajax_acf/fields/post_object/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/post_object/query',	array($this, 'ajax_query'));
		
	}
	
	
	/*
	*  ajax_query
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query() {
		
		// validate
		if( !acf_verify_ajax() ) die();
		
		
		// get choices
		$response = $this->get_ajax_query( $_POST );
		
		
		// return
		acf_send_ajax_results($response);
			
	}
	
	
	/*
	*  get_ajax_query
	*
	*  This function will return an array of data formatted for use in a select2 AJAX response
	*
	*  @type	function
	*  @date	15/10/2014
	*  @since	5.0.9
	*
	*  @param	$options (array)
	*  @return	(array)
	*/
	
	function get_ajax_query( $options = array() ) {
		
   		// defaults
   		$options = acf_parse_args($options, array(
			'post_id'		=> 0,
			's'				=> '',
			'field_key'		=> '',
			'paged'			=> 1
		));
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		if( !$field ) return false;
		
		
		// vars
   		$results = array();
		$args = array();
		$s = false;
		$is_search = false;
		
		
   		// paged
   		$args['posts_per_page'] = 20;
   		$args['paged'] = $options['paged'];
   		
   		
   		// search
		if( $options['s'] !== '' ) {
			
			// strip slashes (search may be integer)
			$s = wp_unslash( strval($options['s']) );
			
			
			// update vars
			$args['s'] = $s;
			$is_search = true;
			
		}
		
				
		// post_type
		if( !empty($field['post_type']) ) {
		
			$args['post_type'] = acf_get_array( $field['post_type'] );
			
		} else {
			
			$args['post_type'] = acf_get_post_types();
			
		}
		
		
		// taxonomy
		if( !empty($field['taxonomy']) ) {
			
			// vars
			$terms = acf_decode_taxonomy_terms( $field['taxonomy'] );
			
			
			// append to $args
			$args['tax_query'] = array();
			
			
			// now create the tax queries
			foreach( $terms as $k => $v ) {
			
				$args['tax_query'][] = array(
					'taxonomy'	=> $k,
					'field'		=> 'slug',
					'terms'		=> $v,
				);
				
			}
			
		}
		
		
		// filters
		$args = apply_filters('acf/fields/post_object/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/post_object/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/post_object/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
		// get posts grouped by post type
		$groups = acf_get_grouped_posts( $args );
		
		
		// bail early if no posts
		if( empty($groups) ) return false;
		
		
		// loop
		foreach( array_keys($groups) as $group_title ) {
			
			// vars
			$posts = acf_extract_var( $groups, $group_title );
			
			
			// data
			$data = array(
				'text'		=> $group_title,
				'children'	=> array()
			);
			
			
			// convert post objects to post titles
			foreach( array_keys($posts) as $post_id ) {
				
				$posts[ $post_id ] = $this->get_post_title( $posts[ $post_id ], $field, $options['post_id'], $is_search );
				
			}
			
			
			// order posts by search
			if( $is_search && empty($args['orderby']) ) {
				
				$posts = acf_order_by_search( $posts, $args['s'] );
				
			}
			
			
			// append to $data
			foreach( array_keys($posts) as $post_id ) {
				
				$data['children'][] = $this->get_post_result( $post_id, $posts[ $post_id ]);
				
			}
			
			
			// append to $results
			$results[] = $data;
			
		}
		
		
		// optgroup or single
		$post_type = acf_get_array( $args['post_type'] );
		if( count($post_type) == 1 ) {
			$results = $results[0]['children'];
		}
		
		
		// vars
		$response = array(
			'results'	=> $results,
			'limit'		=> $args['posts_per_page']
		);
		
		
		// return
		return $response;
			
	}
	
	
	/*
	*  get_post_result
	*
	*  This function will return an array containing id, text and maybe description data
	*
	*  @type	function
	*  @date	7/07/2016
	*  @since	5.4.0
	*
	*  @param	$id (mixed)
	*  @param	$text (string)
	*  @return	(array)
	*/
	
	function get_post_result( $id, $text ) {
		
		// vars
		$result = array(
			'id'	=> $id,
			'text'	=> $text
		);
		
		
		// look for parent
		$search = '| ' . __('Parent', 'acf') . ':';
		$pos = strpos($text, $search);
		
		if( $pos !== false ) {
			
			$result['description'] = substr($text, $pos+2);
			$result['text'] = substr($text, 0, $pos);
			
		}
		
		
		// return
		return $result;
			
	}
	
	
	/*
	*  get_post_title
	*
	*  This function returns the HTML for a result
	*
	*  @type	function
	*  @date	1/11/2013
	*  @since	5.0.0
	*
	*  @param	$post (object)
	*  @param	$field (array)
	*  @param	$post_id (int) the post_id to which this value is saved to
	*  @return	(string)
	*/
	
	function get_post_title( $post, $field, $post_id = 0, $is_search = 0 ) {
		
		// get post_id
		if( !$post_id ) $post_id = acf_get_form_data('post_id');
		
		
		// vars
		$title = acf_get_post_title( $post, $is_search );
			
		
		// filters
		$title = apply_filters('acf/fields/post_object/result', $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/post_object/result/name=' . $field['_name'], $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/post_object/result/key=' . $field['key'], $title, $post, $field, $post_id);
		
		
		// return
		return $title;
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		
		// load posts
		$posts = $this->get_posts( $field['value'], $field );
		
		if( $posts ) {
				
			foreach( array_keys($posts) as $i ) {
				
				// vars
				$post = acf_extract_var( $posts, $i );
				
				
				// append to choices
				$field['choices'][ $post->ID ] = $this->get_post_title( $post, $field );
				
			}
			
		}

		
		// render
		acf_render_field( $field );
		
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by Post Type','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'post_type',
			'choices'		=> acf_get_pretty_post_types(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All post types",'acf'),
		));
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by Taxonomy','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> acf_get_taxonomy_terms(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All taxonomies",'acf'),
		));
		
		
		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'name'			=> 'multiple',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Format','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'object'		=> __("Post Object",'acf'),
				'id'			=> __("Post ID",'acf'),
			),
			'layout'	=>	'horizontal',
		));
				
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// ACF4 null
		if( $value === 'null' ) return false;
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		// numeric
		$value = acf_get_numeric($value);
		
		
		// bail early if no value
		if( empty($value) ) return false;
		
		
		// load posts if needed
		if( $field['return_format'] == 'object' ) {
			
			$value = $this->get_posts( $value, $field );
			
		}
		
		
		// convert back from array if neccessary
		if( !$field['multiple'] && acf_is_array($value) ) {
		
			$value = current($value);
			
		}
		
		
		// return value
		return $value;
		
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// validate
		if( empty($value) ) {
		
			return $value;
			
		}
		
		
		// format
		if( is_array($value) ) {
			
			// array
			foreach( $value as $k => $v ){
			
				// object?
				if( is_object($v) && isset($v->ID) ) {
					
					$value[ $k ] = $v->ID;
				
				}
			
			}
			
			
			// save value as strings, so we can clearly search for them in SQL LIKE statements
			$value = array_map('strval', $value);
			
		} elseif( is_object($value) && isset($value->ID) ) {
			
			// object
			$value = $value->ID;
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  get_posts
	*
	*  This function will return an array of posts for a given field value
	*
	*  @type	function
	*  @date	13/06/2014
	*  @since	5.0.0
	*
	*  @param	$value (array)
	*  @return	$value
	*/
	
	function get_posts( $value, $field ) {
		
		// numeric
		$value = acf_get_numeric($value);
		
		
		// bail early if no value
		if( empty($value) ) return false;
		
		
		// get posts
		$posts = acf_get_posts(array(
			'post__in'	=> $value,
			'post_type'	=> $field['post_type']
		));
		
		
		// return
		return $posts;
		
	}
	
}


// initialize
acf_register_field_type( 'acf_field_post_object' );

endif; // class_exists check

?>