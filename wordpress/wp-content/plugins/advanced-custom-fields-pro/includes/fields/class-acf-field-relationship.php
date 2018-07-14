<?php

if( ! class_exists('acf_field_relationship') ) :

class acf_field_relationship extends acf_field {
	
	
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
		$this->name = 'relationship';
		$this->label = __("Relationship",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'post_type'			=> array(),
			'taxonomy'			=> array(),
			'min' 				=> 0,
			'max' 				=> 0,
			'filters'			=> array('search', 'post_type', 'taxonomy'),
			'elements' 			=> array(),
			'return_format'		=> 'object'
		);
		
		// extra
		add_action('wp_ajax_acf/fields/relationship/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/relationship/query',	array($this, 'ajax_query'));
    	
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	16/12/2015
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// localize
		acf_localize_text(array(
			//'Minimum values reached ( {min} values )'	=> __('Minimum values reached ( {min} values )', 'acf'),
			'Maximum values reached ( {max} values )'	=> __('Maximum values reached ( {max} values )', 'acf'),
			'Loading'									=> __('Loading', 'acf'),
			'No matches found'							=> __('No matches found', 'acf'),
	   	));
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
			'paged'			=> 1,
			'post_type'		=> '',
			'taxonomy'		=> ''
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
		if( !empty($options['post_type']) ) {
			
			$args['post_type'] = acf_get_array( $options['post_type'] );
		
		} elseif( !empty($field['post_type']) ) {
		
			$args['post_type'] = acf_get_array( $field['post_type'] );
			
		} else {
			
			$args['post_type'] = acf_get_post_types();
			
		}
		
		
		// taxonomy
		if( !empty($options['taxonomy']) ) {
			
			// vars
			$term = acf_decode_taxonomy_term($options['taxonomy']);
			
			
			// tax query
			$args['tax_query'] = array();
			
			
			// append
			$args['tax_query'][] = array(
				'taxonomy'	=> $term['taxonomy'],
				'field'		=> 'slug',
				'terms'		=> $term['term'],
			);
			
			
		} elseif( !empty($field['taxonomy']) ) {
			
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
		$args = apply_filters('acf/fields/relationship/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/relationship/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/relationship/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
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
				
				$posts[ $post_id ] = $this->get_post_title( $posts[ $post_id ], $field, $options['post_id'] );
				
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
		
		
		// add as optgroup or results
		if( count($args['post_type']) == 1 ) {
			
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
		
		
		// featured_image
		if( acf_in_array('featured_image', $field['elements']) ) {
			
			// vars
			$class = 'thumbnail';
			$thumbnail = acf_get_post_thumbnail($post->ID, array(17, 17));
			
			
			// icon
			if( $thumbnail['type'] == 'icon' ) {
				
				$class .= ' -' . $thumbnail['type'];
				
			}
			
			
			// append
			$title = '<div class="' . $class . '">' . $thumbnail['html'] . '</div>' . $title;
			
		}
		
		
		// filters
		$title = apply_filters('acf/fields/relationship/result', $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/relationship/result/name=' . $field['_name'], $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/relationship/result/key=' . $field['key'], $title, $post, $field, $post_id);
		
		
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
		
		// vars
		$values = array();
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> "acf-relationship {$field['class']}",
			'data-min'			=> $field['min'],
			'data-max'			=> $field['max'],
			'data-s'			=> '',
			'data-post_type'	=> '',
			'data-taxonomy'		=> '',
			'data-paged'		=> 1,
		);
		
		
		// Lang
		if( defined('ICL_LANGUAGE_CODE') ) {
		
			$atts['data-lang'] = ICL_LANGUAGE_CODE;
			
		}
		
		
		// data types
		$field['post_type'] = acf_get_array( $field['post_type'] );
		$field['taxonomy'] = acf_get_array( $field['taxonomy'] );
		$field['filters'] = acf_get_array( $field['filters'] );
		
		
		// filters
		$filters = array(
			'count'		=> count($field['filters']),
			'search'	=> false,
			'post_type'	=> false,
			'taxonomy'	=> false
		);
		
		foreach( $field['filters'] as $filter ) {
			$filters[ $filter ] = true;
		}
		
		
		// filter - post_type
		if( $filters['post_type'] ) {
			
			// choices
			$choices = array(
				''	=> __('Select post type', 'acf')
			);
			
			
			// get post types
			$post_types = acf_get_pretty_post_types($field['post_type']);
			
			
			// append
			$choices = $choices + $post_types;
			
			
			// set filter
			$filters['post_type'] = $choices;
			
		}
		

		
		// taxonomy filter
		if( $filters['taxonomy'] ) {
			
			// vars
			$groups = array();
			$taxonomies = array();
			$choices = array(
				''	=> __('Select taxonomy', 'acf')
			);
			
			
			// get taxonomies from  setting
			if( !empty($field['taxonomy']) ) {
				
				$term_groups = acf_decode_taxonomy_terms( $field['taxonomy'] );
				$taxonomies = array_keys($term_groups);
				
				// check empty
				$taxonomies = empty($taxonomies) ? false : $taxonomies;
			
			} elseif( !empty($field['post_type']) ) {
				
				// loop
				foreach( $field['post_type'] as $post_type ) {
					
					// get connected taxonomies
					$post_taxonomies = get_object_taxonomies( $post_type );
					

					// loop
					foreach( $post_taxonomies as $name ) {
						$taxonomies[ $name ] = 1;						
					}
							
				}
				
				
				// convert back to array
				$taxonomies = array_keys($taxonomies);
				
				// check empty
				$taxonomies = empty($taxonomies) ? false : $taxonomies;
				
			}
			
			
			// terms
			if( $taxonomies !== false ) {
				$groups = acf_get_taxonomy_terms( $taxonomies );
			}
			
			
			// update $term_groups with specific terms
			if( !empty($field['taxonomy']) ) {
				
				foreach( $groups as $taxonomy => $terms ) {
					
					foreach( $terms as $slug => $name ) {
						
						if( !in_array($slug, $field['taxonomy']) ) {
							
							unset($groups[ $taxonomy ][ $slug ]);
							
						}
						
					}
					
				}
				
			}

			
			// append
			$choices = $choices + $groups;
			
			
			// set filter
			$filters['taxonomy'] = $choices;
			
		}
		
		?>
<div <?php acf_esc_attr_e($atts); ?>>
	
	<?php acf_hidden_input( array('name' => $field['name'], 'value' => '') ); ?>
	
	<?php 
	
	/* filters */	
	if( $filters['count'] ): ?>
	<div class="filters -f<?php echo esc_attr($filters['count']); ?>">
		<?php 
	
		/* search */	
		if( $filters['search'] ): ?>
		<div class="filter -search">
			<span>
				<?php acf_text_input( array('placeholder' => __("Search...",'acf'), 'data-filter' => 's') ); ?>
			</span>
		</div>
		<?php endif; 
		
		
		/* post_type */	
		if( $filters['post_type'] ): ?>
		<div class="filter -post_type">
			<span>
				<?php acf_select_input( array('choices' => $filters['post_type'], 'data-filter' => 'post_type') ); ?>
			</span>
		</div>
		<?php endif; 
		
		
		/* post_type */	
		if( $filters['taxonomy'] ): ?>
		<div class="filter -taxonomy">
			<span>
				<?php acf_select_input( array('choices' => $filters['taxonomy'], 'data-filter' => 'taxonomy') ); ?>
			</span>
		</div>
		<?php endif; ?>		
	</div>
	<?php endif; ?>
	
	<div class="selection">
		<div class="choices">
			<ul class="acf-bl list choices-list"></ul>
		</div>
		<div class="values">
			<ul class="acf-bl list values-list">
			<?php if( !empty($field['value']) ): 
				
				// get posts
				$posts = acf_get_posts(array(
					'post__in' => $field['value'],
					'post_type'	=> $field['post_type']
				));
				
				
				// loop
				foreach( $posts as $post ): ?>
					<li>
						<?php acf_hidden_input( array('name' => $field['name'].'[]', 'value' => $post->ID) ); ?>
						<span data-id="<?php echo esc_attr($post->ID); ?>" class="acf-rel-item">
							<?php echo $this->get_post_title( $post, $field ); ?>
							<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a>
						</span>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
		<?php
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
		
		// vars
		$field['min'] = empty($field['min']) ? '' : $field['min'];
		$field['max'] = empty($field['max']) ? '' : $field['max'];
		
		
		// post_type
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
		
		
		// taxonomy
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
		
		
		// filters
		acf_render_field_setting( $field, array(
			'label'			=> __('Filters','acf'),
			'instructions'	=> '',
			'type'			=> 'checkbox',
			'name'			=> 'filters',
			'choices'		=> array(
				'search'		=> __("Search",'acf'),
				'post_type'		=> __("Post Type",'acf'),
				'taxonomy'		=> __("Taxonomy",'acf'),
			),
		));
		
		
		// filters
		acf_render_field_setting( $field, array(
			'label'			=> __('Elements','acf'),
			'instructions'	=> __('Selected elements will be displayed in each result','acf'),
			'type'			=> 'checkbox',
			'name'			=> 'elements',
			'choices'		=> array(
				'featured_image'	=> __("Featured Image",'acf'),
			),
		));
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum posts','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum posts','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
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
		
		// bail early if no value
		if( empty($value) ) {
		
			return $value;
			
		}
		
		
		// force value to array
		$value = acf_get_array( $value );
		
		
		// convert to int
		$value = array_map('intval', $value);
		
		
		// load posts if needed
		if( $field['return_format'] == 'object' ) {
			
			// get posts
			$value = acf_get_posts(array(
				'post__in' => $value,
				'post_type'	=> $field['post_type']
			));
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		// default
		if( empty($value) || !is_array($value) ) {
		
			$value = array();
			
		}
		
		
		// min
		if( count($value) < $field['min'] ) {
		
			$valid = _n( '%s requires at least %s selection', '%s requires at least %s selections', $field['min'], 'acf' );
			$valid = sprintf( $valid, $field['label'], $field['min'] );
			
		}
		
		
		// return		
		return $valid;
		
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
		
		
		// force value to array
		$value = acf_get_array( $value );
		
					
		// array
		foreach( $value as $k => $v ){
		
			// object?
			if( is_object($v) && isset($v->ID) ) {
			
				$value[ $k ] = $v->ID;
				
			}
			
		}
		
		
		// save value as strings, so we can clearly search for them in SQL LIKE statements
		$value = array_map('strval', $value);
		
	
		// return
		return $value;
		
	}
		
}


// initialize
acf_register_field_type( 'acf_field_relationship' );

endif; // class_exists check

?>