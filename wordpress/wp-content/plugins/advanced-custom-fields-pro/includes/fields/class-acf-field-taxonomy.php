<?php

if( ! class_exists('acf_field_taxonomy') ) :

class acf_field_taxonomy extends acf_field {
	
	// vars
	var $save_post_terms = array();
	
	
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
		$this->name = 'taxonomy';
		$this->label = __("Taxonomy",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'taxonomy' 			=> 'category',
			'field_type' 		=> 'checkbox',
			'multiple'			=> 0,
			'allow_null' 		=> 0,
			//'load_save_terms' 	=> 0, // removed in 5.2.7
			'return_format'		=> 'id',
			'add_term'			=> 1, // 5.2.3
			'load_terms'		=> 0, // 5.2.7	
			'save_terms'		=> 0 // 5.2.7
		);
		
		
		// ajax
		add_action('wp_ajax_acf/fields/taxonomy/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/taxonomy/query',	array($this, 'ajax_query'));
		add_action('wp_ajax_acf/fields/taxonomy/add_term',		array($this, 'ajax_add_term'));
		
		
		// actions
		add_action('acf/save_post', array($this, 'save_post'), 15, 1);
    	
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
			'paged'			=> 0
		));
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		if( !$field ) return false;
		
		
		// bail early if taxonomy does not exist
		if( !taxonomy_exists($field['taxonomy']) ) return false;
		
		
		// vars
   		$results = array();
		$is_hierarchical = is_taxonomy_hierarchical( $field['taxonomy'] );
		$is_pagination = ($options['paged'] > 0);
		$is_search = false;
		$limit = 20;
		$offset = 20 * ($options['paged'] - 1);
		
		
		// args
		$args = array(
			'taxonomy'		=> $field['taxonomy'],
			'hide_empty'	=> false
		);
		
		
		// pagination
		// - don't bother for hierarchial terms, we will need to load all terms anyway
		if( $is_pagination && !$is_hierarchical ) {
			
			$args['number'] = $limit;
			$args['offset'] = $offset;
		
		}
		
		
		// search
		if( $options['s'] !== '' ) {
			
			// strip slashes (search may be integer)
			$s = wp_unslash( strval($options['s']) );
			
			
			// update vars
			$args['search'] = $s;
			$is_search = true;
			
		}
		
		
		// filters
		$args = apply_filters('acf/fields/taxonomy/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/taxonomy/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/taxonomy/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
		// get terms
		$terms = acf_get_terms( $args );
		
		
		// sort into hierachial order!
		if( $is_hierarchical ) {
			
			// update vars
			$limit = acf_maybe_get( $args, 'number', $limit );
			$offset = acf_maybe_get( $args, 'offset', $offset );
			
			
			// get parent
			$parent = acf_maybe_get( $args, 'parent', 0 );
			$parent = acf_maybe_get( $args, 'child_of', $parent );
			
			
			// this will fail if a search has taken place because parents wont exist
			if( !$is_search ) {
				
				// order terms
				$ordered_terms = _get_term_children( $parent, $terms, $field['taxonomy'] );
				
				
				// check for empty array (possible if parent did not exist within original data)
				if( !empty($ordered_terms) ) {
					
					$terms = $ordered_terms;
					
				}
			}
			
			
			// fake pagination
			if( $is_pagination ) {
				
				$terms = array_slice($terms, $offset, $limit);
				
			}
			
		}
		
		
		/// append to r
		foreach( $terms as $term ) {
		
			// add to json
			$results[] = array(
				'id'	=> $term->term_id,
				'text'	=> $this->get_term_title( $term, $field, $options['post_id'] )
			);
			
		}
		
		
		// vars
		$response = array(
			'results'	=> $results,
			'limit'		=> $limit
		);
		
		
		// return
		return $response;
			
	}
	
	
	/*
	*  get_term_title
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
	
	function get_term_title( $term, $field, $post_id = 0 ) {
		
		// get post_id
		if( !$post_id ) $post_id = acf_get_form_data('post_id');
		
		
		// vars
		$title = '';
		
		
		// ancestors
		$ancestors = get_ancestors( $term->term_id, $field['taxonomy'] );
		
		if( !empty($ancestors) ) {
		
			$title .= str_repeat('- ', count($ancestors));
			
		}
		
		
		// title
		$title .= $term->name;
				
		
		// filters
		$title = apply_filters('acf/fields/taxonomy/result', $title, $term, $field, $post_id);
		$title = apply_filters('acf/fields/taxonomy/result/name=' . $field['_name'] , $title, $term, $field, $post_id);
		$title = apply_filters('acf/fields/taxonomy/result/key=' . $field['key'], $title, $term, $field, $post_id);
		
		
		// return
		return $title;
	}
	
	
	/*
	*  get_terms
	*
	*  This function will return an array of terms for a given field value
	*
	*  @type	function
	*  @date	13/06/2014
	*  @since	5.0.0
	*
	*  @param	$value (array)
	*  @return	$value
	*/
	
	function get_terms( $value, $taxonomy = 'category' ) {
		
		// load terms in 1 query to save multiple DB calls from following code
		if( count($value) > 1 ) {
			
			$terms = acf_get_terms(array(
				'taxonomy'		=> $taxonomy,
				'include'		=> $value,
				'hide_empty'	=> false
			));
			
		}
		
		
		// update value to include $post
		foreach( array_keys($value) as $i ) {
			
			$value[ $i ] = get_term( $value[ $i ], $taxonomy );
			
		}
		
		
		// filter out null values
		$value = array_filter($value);
		
		
		// return
		return $value;
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded from
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in te database
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// get valid terms
		$value = acf_get_valid_terms($value, $field['taxonomy']);
		
		
		// load_terms
		if( $field['load_terms'] ) {
			
			// get terms
			$info = acf_get_post_id_info($post_id);
			$term_ids = wp_get_object_terms($info['id'], $field['taxonomy'], array('fields' => 'ids', 'orderby' => 'none'));
			
			
			// bail early if no terms
			if( empty($term_ids) || is_wp_error($term_ids) ) return false;
			
			
			// sort
			if( !empty($value) ) {
				
				$order = array();
				
				foreach( $term_ids as $i => $v ) {
					
					$order[ $i ] = array_search($v, $value);
					
				}
				
				array_multisort($order, $term_ids);
				
			}
			
			
			// update value
			$value = $term_ids;
						
		}
		
		
		// convert back from array if neccessary
		if( $field['field_type'] == 'select' || $field['field_type'] == 'radio' ) {
			
			$value = array_shift($value);
			
		}
		
		
		// return
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
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the $post_id of which the value will be saved
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// vars
		if( is_array($value) ) {
		
			$value = array_filter($value);
			
		}
		
		
		// save_terms
		if( $field['save_terms'] ) {
			
			// vars
			$taxonomy = $field['taxonomy'];
			
			
			// force value to array
			$term_ids = acf_get_array( $value );
			
			
			// convert to int
			$term_ids = array_map('intval', $term_ids);
			
			
			// get existing term id's (from a previously saved field)
			$old_term_ids = isset($this->save_post_terms[ $taxonomy ]) ? $this->save_post_terms[ $taxonomy ] : array();
			
			
			// append
			$this->save_post_terms[ $taxonomy ] = array_merge($old_term_ids, $term_ids);
			
			
			// if called directly from frontend update_field()
			if( !did_action('acf/save_post') ) {
				
				$this->save_post( $post_id );
				
				return $value;
				
			}
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  save_post
	*
	*  This function will save any terms in the save_post_terms array
	*
	*  @type	function
	*  @date	26/11/2014
	*  @since	5.0.9
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function save_post( $post_id ) {
		
		// bail ealry if no terms
		if( empty($this->save_post_terms) ) return;
		
		
		// vars
		$info = acf_get_post_id_info($post_id);
		
		
		// loop
		foreach( $this->save_post_terms as $taxonomy => $term_ids ){
			
			// save
			wp_set_object_terms( $info['id'], $term_ids, $taxonomy, false );
			
		}
		
		
		// reset array ( WP saves twice )
		$this->save_post_terms = array();
		
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
		if( empty($value) ) return false;
		
		
		// force value to array
		$value = acf_get_array( $value );
		
		
		// load posts if needed
		if( $field['return_format'] == 'object' ) {
			
			// get posts
			$value = $this->get_terms( $value, $field["taxonomy"] );
		
		}
		
		
		// convert back from array if neccessary
		if( $field['field_type'] == 'select' || $field['field_type'] == 'radio' ) {
			
			$value = array_shift($value);
			
		}
		

		// return
		return $value;
		
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field( $field ) {
		
		// force value to array
		$field['value'] = acf_get_array( $field['value'] );
		
		
		// vars
		$div = array(
			'class'				=> 'acf-taxonomy-field',
			'data-save'			=> $field['save_terms'],
			'data-type'			=> $field['field_type'],
			'data-taxonomy'		=> $field['taxonomy']
		);
		
		
		// get taxonomy
		$taxonomy = get_taxonomy( $field['taxonomy'] );
		
		
		// bail early if taxonomy does not exist
		if( !$taxonomy ) return;
		
		
		?>
<div <?php acf_esc_attr_e($div); ?>>
	<?php if( $field['add_term'] && current_user_can( $taxonomy->cap->manage_terms) ): ?>
	<div class="acf-actions -hover">
		<a href="#" class="acf-icon -plus acf-js-tooltip small" data-name="add" title="<?php echo esc_attr($taxonomy->labels->add_new_item); ?>"></a>
	</div>
	<?php endif;

	if( $field['field_type'] == 'select' ) {
	
		$field['multiple'] = 0;
		
		$this->render_field_select( $field );
	
	} elseif( $field['field_type'] == 'multi_select' ) {
		
		$field['multiple'] = 1;
		
		$this->render_field_select( $field );
	
	} elseif( $field['field_type'] == 'radio' ) {
		
		$this->render_field_checkbox( $field );
		
	} elseif( $field['field_type'] == 'checkbox' ) {
	
		$this->render_field_checkbox( $field );
		
	}

	?>
</div><?php
		
	}
	
	
	/*
	*  render_field_select()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field_select( $field ) {
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		
		// value
		if( !empty($field['value']) ) {
			
			// get terms
			$terms = $this->get_terms( $field['value'], $field['taxonomy'] );
			
			
			// set choices
			if( !empty($terms) ) {
				
				foreach( array_keys($terms) as $i ) {
					
					// vars
					$term = acf_extract_var( $terms, $i );
					
					
					// append to choices
					$field['choices'][ $term->term_id ] = $this->get_term_title( $term, $field );
				
				}
				
			}
			
		}
		
		
		// render select		
		acf_render_field( $field );
		
	}
	
	
	/*
	*  render_field_checkbox()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field_checkbox( $field ) {
		
		// hidden input
		acf_hidden_input(array(
			'type'	=> 'hidden',
			'name'	=> $field['name'],
		));
		
		
		// checkbox saves an array
		if( $field['field_type'] == 'checkbox' ) {
		
			$field['name'] .= '[]';
			
		}
		
		
		// taxonomy
		$taxonomy_obj = get_taxonomy($field['taxonomy']);
		
		
		// include walker
		acf_include('includes/walkers/class-acf-walker-taxonomy-field.php');
		
		
		// vars
		$args = array(
			'taxonomy'     		=> $field['taxonomy'],
			'show_option_none'	=> sprintf( _x('No %s', 'No terms', 'acf'), strtolower($taxonomy_obj->labels->name) ),
			'hide_empty'   		=> false,
			'style'        		=> 'none',
			'walker'       		=> new ACF_Taxonomy_Field_Walker( $field ),
		);
		
		
		// filter for 3rd party customization
		$args = apply_filters('acf/fields/taxonomy/wp_list_categories', $args, $field);
		$args = apply_filters('acf/fields/taxonomy/wp_list_categories/name=' . $field['_name'], $args, $field);
		$args = apply_filters('acf/fields/taxonomy/wp_list_categories/key=' . $field['key'], $args, $field);
		
		?><div class="categorychecklist-holder">
		
			<ul class="acf-checkbox-list acf-bl">
			
				<?php if( $field['field_type'] == 'radio' && $field['allow_null'] ): ?>
					<li>
						<label class="selectit">
							<input type="radio" name="<?php echo esc_attr($field['name']); ?>" value="" /> <?php _e("None", 'acf'); ?>
						</label>
					</li>
				<?php endif; ?>
				
				<?php wp_list_categories( $args ); ?>
		
			</ul>
			
		</div><?php
		
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
			'label'			=> __('Taxonomy','acf'),
			'instructions'	=> __('Select the taxonomy to be displayed','acf'),
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> acf_get_taxonomies(),
		));
		
		
		// field_type
		acf_render_field_setting( $field, array(
			'label'			=> __('Appearance','acf'),
			'instructions'	=> __('Select the appearance of this field','acf'),
			'type'			=> 'select',
			'name'			=> 'field_type',
			'optgroup'		=> true,
			'choices'		=> array(
				__("Multiple Values",'acf') => array(
					'checkbox' => __('Checkbox', 'acf'),
					'multi_select' => __('Multi Select', 'acf')
				),
				__("Single Value",'acf') => array(
					'radio' => __('Radio Buttons', 'acf'),
					'select' => _x('Select', 'noun', 'acf')
				)
			)
		));
		
		
		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// add_term
		acf_render_field_setting( $field, array(
			'label'			=> __('Create Terms','acf'),
			'instructions'	=> __('Allow new terms to be created whilst editing','acf'),
			'name'			=> 'add_term',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// save_terms
		acf_render_field_setting( $field, array(
			'label'			=> __('Save Terms','acf'),
			'instructions'	=> __('Connect selected terms to the post','acf'),
			'name'			=> 'save_terms',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// load_terms
		acf_render_field_setting( $field, array(
			'label'			=> __('Load Terms','acf'),
			'instructions'	=> __('Load value from posts terms','acf'),
			'name'			=> 'load_terms',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'object'		=>	__("Term Object",'acf'),
				'id'			=>	__("Term ID",'acf')
			),
			'layout'	=>	'horizontal',
		));
		
	}
	
	
	/*
	*  ajax_add_term
	*
	*  description
	*
	*  @type	function
	*  @date	17/04/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_add_term() {
		
		// vars
		$args = acf_parse_args($_POST, array(
			'nonce'				=> '',
			'field_key'			=> '',
			'term_name'			=> '',
			'term_parent'		=> ''
		));
		
		
		// verify nonce
		if( ! wp_verify_nonce($args['nonce'], 'acf_nonce') ) {
		
			die();
			
		}
		
		
		// load field
		$field = acf_get_field( $args['field_key'] );
		
		if( !$field ) {
		
			die();
			
		}
		
		
		// vars
		$taxonomy_obj = get_taxonomy($field['taxonomy']);
		$taxonomy_label = $taxonomy_obj->labels->singular_name;
			
			
		// validate cap
		// note: this situation should never occur due to condition of the add new button
		if( !current_user_can( $taxonomy_obj->cap->manage_terms) ) {
			
			echo '<p><strong>' . __("Error.", 'acf') . '</strong> ' . sprintf( __('User unable to add new %s', 'acf'), $taxonomy_label ) . '</p>';
			die;
			
		}
	
		
		// save?
		if( $args['term_name'] ) {
			
			// exists
			if( term_exists($args['term_name'], $field['taxonomy']) ) {
				
				wp_send_json_error(array(
					'error'	=> sprintf( __('%s already exists', 'acf'), $taxonomy_label )
				));
			
			}
			
			
			// insert
			$extra = array();
			
			if( $args['term_parent'] ) {
				
				$extra['parent'] = $args['term_parent'];
				
			}
			
			$data = wp_insert_term( $args['term_name'], $field['taxonomy'], $extra );
			
			
			// error?
			if( is_wp_error($data) ) {
				
				wp_send_json_error(array(
					'error'	=> $data->get_error_message()
				));
			
			}
			
			
			// ancestors
			$prefix = '';
			$ancestors = get_ancestors( $data['term_id'], $field['taxonomy'] );
			
			if( !empty($ancestors) ) {
			
				$prefix = str_repeat('- ', count($ancestors));
				
			}
		
		
			// success
			wp_send_json_success(array(
				'message'		=> sprintf( __('%s added', 'acf'), $taxonomy_label ),
				'term_id'		=> $data['term_id'],
				'term_name'		=> $args['term_name'],
				'term_label'	=> $prefix . $args['term_name'],
				'term_parent'	=> $args['term_parent']
			));
				
		}
		
		?><form method="post"><?php
		
		acf_render_field_wrap(array(
			'label'			=> __('Name', 'acf'),
			'name'			=> 'term_name',
			'type'			=> 'text'
		));
		
		
		if( is_taxonomy_hierarchical( $field['taxonomy'] ) ) {
			
			$choices = array();
			$response = $this->get_ajax_query($args);
			
			if( $response ) {
				
				foreach( $response['results'] as $v ) { 
					
					$choices[ $v['id'] ] = $v['text'];
					
				}
				
			}
			
			acf_render_field_wrap(array(
				'label'			=> __('Parent', 'acf'),
				'name'			=> 'term_parent',
				'type'			=> 'select',
				'allow_null'	=> 1,
				'ui'			=> 0,
				'choices'		=> $choices
			));
			
		}
		
		
		?><p class="acf-submit"><button class="acf-button button button-primary" type="submit"><?php _e("Add", 'acf'); ?></button><i class="acf-spinner"></i><span></span></p></form><?php
		
		
		// die
		die;	
		
	}
	
		
}


// initialize
acf_register_field_type( 'acf_field_taxonomy' );

endif; // class_exists check

?>