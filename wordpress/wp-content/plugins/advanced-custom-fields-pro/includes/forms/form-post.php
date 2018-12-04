<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Form_Post') ) :

class ACF_Form_Post {
	
	/** @var string The first field groups style CSS. */
	var $style = '';
	
	/** @var array An arry of postbox data. */
	var $postboxes = array();
	
	/**
	*  __construct
	*
	*  Sets up the class functionality.
	*
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	void
	*  @return	void
	*/
	function __construct() {
		
		// initialize on post edit screens
		add_action('load-post.php',		array($this, 'initialize'));
		add_action('load-post-new.php',	array($this, 'initialize'));
		
		// save
		add_filter('wp_insert_post_empty_content',		array($this, 'wp_insert_post_empty_content'), 10, 2);
		add_action('save_post', 						array($this, 'save_post'), 10, 2);
	}
	
	
	/**
	*  initialize
	*
	*  Sets up Form functionality.
	*
	*  @date	19/9/18
	*  @since	5.7.6
	*
	*  @param	void
	*  @return	void
	*/
	function initialize() {
		
		// globals
		global $typenow;
		
		// restrict specific post types
		$restricted = array('acf-field-group', 'attachment');
		if( in_array($typenow, $restricted) ) {
			return;
		}
		
		// enqueue scripts
		acf_enqueue_scripts(array(
			'uploader'	=> true,
		));
		
		// actions
		add_action('add_meta_boxes',		array($this, 'add_meta_boxes'), 10, 2);
		add_action('edit_form_after_title', array($this, 'edit_form_after_title'));
		add_filter('hidden_meta_boxes', 	array($this, 'hidden_meta_boxes'), 10, 3);
	}
	
	/**
	*  add_meta_boxes
	*
	*  Adds ACF metaboxes for the given $post_type and $post.
	*
	*  @date	19/9/18
	*  @since	5.7.6
	*
	*  @param	string $post_type The post type.
	*  @param	WP_Post $post The post being edited.
	*  @return	void
	*/
	function add_meta_boxes( $post_type, $post ) {
		
		// vars
		$postboxes = array();
		$count = 0;
		
		// get all field groups
		$field_groups = acf_get_field_groups();
		
		// loop
		if( $field_groups ) {
		foreach( $field_groups as $field_group ) {
				
			// vars
			$id = "acf-{$field_group['key']}";			// acf-group_123
			$title = $field_group['title'];				// Group 1
			$context = $field_group['position'];		// normal, side, acf_after_title
			$priority = 'high';							// high, core, default, low
			
			// change priority for sidebar metaboxes
			if( $context == 'side' ) {
				$priority = 'core';
			}
			
			/**
			*  Filters the metabox priority.
			*
			*  @date	23/06/12
			*  @since	3.1.8
			*
			*  @param	string $priority The metabox priority (high, core, default, low).
			*  @param	array $field_group The field group array.
			*/
			$priority = apply_filters('acf/input/meta_box_priority', $priority, $field_group);
			
			// set the visibility for this field group
			$visible = acf_get_field_group_visibility($field_group, array(
				'post_id'	=> $post->ID, 
				'post_type'	=> $post_type
			));
			
			// add meta box
			add_meta_box( $id, $title, array($this, 'render_meta_box'), $post_type, $context, $priority, array('field_group' => $field_group) );
			
			// append to $postboxes
			$this->postboxes[ $id ] = array(
				'id'		=> $id,
				'key'		=> $field_group['key'],
				'style'		=> $field_group['style'],
				'label'		=> $field_group['label_placement'],
				'visible'	=> $visible,
				'edit'		=> acf_get_field_group_edit_link( $field_group['ID'] )
			);
			
			// increase count for visible
			if( $visible ) {
				$count++;
				
				// find first field group's style
				if( $count == 1 ) {
					$this->style = acf_get_field_group_style( $field_group );
				}
			}
		}}
		
		// remove postcustom metabox (removes expensive SQL query)
		if( acf_get_setting('remove_wp_meta_box') ) {
			remove_meta_box( 'postcustom', false, 'normal' ); 
		}
	}
	
	/**
	*  edit_form_after_title
	*
	*  Called after the title adn before the content editor.
	*
	*  @date	19/9/18
	*  @since	5.7.6
	*
	*  @param	void
	*  @return	void
	*/
	function edit_form_after_title() {
		
		// globals
		global $post, $wp_meta_boxes;
		
		// render post data
		acf_form_data(array(
			'screen'	=> 'post',
			'post_id'	=> $post->ID
		));
		
		// render 'acf_after_title' metaboxes
		do_meta_boxes( get_current_screen(), 'acf_after_title', $post );
			
		// clean up $wp_meta_boxes
		unset( $wp_meta_boxes['post']['acf_after_title'] );
		
		// render dynamic field group style
		echo '<style type="text/css" id="acf-style">' . $this->style . '</style>';
	}
	
	/**
	*  hidden_meta_boxes
	*
	*  Appends the id of all metaboxes that are not visible for WP to hide.
	*
	*  @date	21/9/18
	*  @since	5.7.6
	*
	*  @param	array     $hidden       An array of hidden meta boxes.
    *  @param 	WP_Screen $screen       WP_Screen object of the current screen.
    *  @param 	bool      $use_defaults Whether to show the default meta boxes.
    *  @return	array
	*/
	function hidden_meta_boxes( $hidden, $screen, $use_defaults ) {
		
		// loop over visiblity array
		foreach( $this->postboxes as $id => $postbox ) {
			if( !$postbox['visible'] ) {
				$hidden[] = $id;
			}
		}
		
		// return
		return $hidden;
	}
	
	/**
	*  render_meta_box
	*
	*  Renders the ACF metabox HTML.
	*
	*  @date	19/9/18
	*  @since	5.7.6
	*
	*  @param	WP_Post $post The post being edited.
	*  @param	array metabox The add_meta_box() args.
	*  @return	void
	*/
	function render_meta_box( $post, $metabox ) {
		
		// vars
		$id = $metabox['id'];
		$field_group = $metabox['args']['field_group'];
		$postbox = $this->postboxes[ $id ];
		
		// render fields if visible
		if( $postbox['visible'] ) {
			$fields = acf_get_fields( $field_group );
			acf_render_fields( $fields, $post->ID, 'div', $field_group['instruction_placement'] );
		}
		
		// inline javascript
		?>
		<script type="text/javascript">
		if( typeof acf !== 'undefined' ) {
			acf.newPostbox(<?php echo wp_json_encode($postbox); ?>);
		}	
		</script>
		<?php
	}
	
	/**
	*  wp_insert_post_empty_content
	*
	*  Allows WP to insert a new post without title or post_content if ACF data exists.
	*
	*  @date	16/07/2014
	*  @since	5.0.1
	*
	*  @param	bool $maybe_empty Whether the post should be considered "empty".
	*  @param	array $postarr Array of post data.
	*  @return	bool
	*/
	function wp_insert_post_empty_content( $maybe_empty, $postarr ) {
		
		// return false and allow insert if '_acf_changed' exists
		if( $maybe_empty && acf_maybe_get_POST('_acf_changed') ) {
			return false;
		}

		// return
		return $maybe_empty;
	}
	
	/*
	*  allow_save_post
	*
	*  Checks if the $post is allowed to be saved.
	*  Used to avoid triggering "acf/save_post" on dynamically created posts during save.
	*
	*  @type	function
	*  @date	26/06/2016
	*  @since	5.3.8
	*
	*  @param	WP_Post $post The post to check.
	*  @return	bool
	*/
	function allow_save_post( $post ) {
		
		// vars
		$allow = true;
		
		// restrict post types
		$restrict = array( 'auto-draft', 'revision', 'acf-field', 'acf-field-group' );
		if( in_array($post->post_type, $restrict) ) {
			$allow = false;
		}
		
		// disallow if the $_POST ID value does not match the $post->ID
		$form_post_id = (int) acf_maybe_get_POST('post_ID');
		if( $form_post_id && $form_post_id !== $post->ID ) {
			$allow = false;
		}
		
		// revision (preview)
		if( $post->post_type == 'revision' ) {
			
			// allow if doing preview and this $post is a child of the $_POST ID
			if( acf_maybe_get_POST('wp-preview') == 'dopreview' && $form_post_id === $post->post_parent) {
				$allow = true;
			}
		}
		
		// return
		return $allow;
	}
	
	/*
	*  save_post
	*
	*  Triggers during the 'save_post' action to save the $_POST data.
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	1.0.0
	*
	*  @param	int $post_id The post ID
	*  @param	WP_POST $post the post object.
	*  @return	int
	*/
	
	function save_post( $post_id, $post ) {
		
		// bail ealry if no allowed to save this post type
		if( !$this->allow_save_post($post) ) {
			return $post_id;
		}
		
		// verify nonce
		if( !acf_verify_nonce('post') ) {
			return $post_id;
		}
		
		// validate for published post (allow draft to save without validation)
		if( $post->post_status == 'publish' ) {
			
			// show errors
			acf_validate_save_post( true );
		}
		
		// save
		acf_save_post( $post_id );
		
		// save revision
		if( post_type_supports($post->post_type, 'revisions') ) {
			acf_save_post_revision( $post_id );
		}
		
		// return
		return $post_id;
	}
}

acf_new_instance('ACF_Form_Post');

endif;

?>