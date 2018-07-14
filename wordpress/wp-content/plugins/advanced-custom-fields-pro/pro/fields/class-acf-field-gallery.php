<?php

if( ! class_exists('acf_field_gallery') ) :

class acf_field_gallery extends acf_field {
	
	
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
		$this->name = 'gallery';
		$this->label = __("Gallery",'acf');
		$this->category = 'content';
		$this->defaults = array(
			'library'		=> 'all',
			'min'			=> 0,
			'max'			=> 0,
			'min_width'		=> 0,
			'min_height'	=> 0,
			'min_size'		=> 0,
			'max_width'		=> 0,
			'max_height'	=> 0,
			'max_size'		=> 0,
			'mime_types'	=> '',
			'insert'		=> 'append'
		);
		
		
		// actions
		add_action('wp_ajax_acf/fields/gallery/get_attachment',				array($this, 'ajax_get_attachment'));
		add_action('wp_ajax_nopriv_acf/fields/gallery/get_attachment',		array($this, 'ajax_get_attachment'));
		
		add_action('wp_ajax_acf/fields/gallery/update_attachment',			array($this, 'ajax_update_attachment'));
		add_action('wp_ajax_nopriv_acf/fields/gallery/update_attachment',	array($this, 'ajax_update_attachment'));
		
		add_action('wp_ajax_acf/fields/gallery/get_sort_order',				array($this, 'ajax_get_sort_order'));
		add_action('wp_ajax_nopriv_acf/fields/gallery/get_sort_order',		array($this, 'ajax_get_sort_order'));
		
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
		   	'Add Image to Gallery'		=> __('Add Image to Gallery', 'acf'),
			'Maximum selection reached'	=> __('Maximum selection reached', 'acf'),
	   	));
	}
	
	
	/*
	*  ajax_get_attachment
	*
	*  description
	*
	*  @type	function
	*  @date	13/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_get_attachment() {
	
		// options
   		$options = acf_parse_args( $_POST, array(
			'post_id'		=> 0,
			'attachment'	=> 0,
			'id'			=> 0,
			'field_key'		=> '',
			'nonce'			=> '',
		));
   		
		
		// validate
		if( !wp_verify_nonce($options['nonce'], 'acf_nonce') ) die();
		
		
		// bail early if no id
		if( !$options['id'] ) die();
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		
		// bali early if no field
		if( !$field ) die();
		
		
		// render
		$this->render_attachment( $options['id'], $field );
		die;
		
	}
	
	
	/*
	*  ajax_update_attachment
	*
	*  description
	*
	*  @type	function
	*  @date	13/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_update_attachment() {
		
		// validate nonce
		if( !wp_verify_nonce($_POST['nonce'], 'acf_nonce') ) {
		
			wp_send_json_error();
			
		}
		
		
		// bail early if no attachments
		if( empty($_POST['attachments']) ) {
		
			wp_send_json_error();
			
		}
		
		
		// loop over attachments
		foreach( $_POST['attachments'] as $id => $changes ) {
			
			if ( !current_user_can( 'edit_post', $id ) )
				wp_send_json_error();
				
			$post = get_post( $id, ARRAY_A );
		
			if ( 'attachment' != $post['post_type'] )
				wp_send_json_error();
		
			if ( isset( $changes['title'] ) )
				$post['post_title'] = $changes['title'];
		
			if ( isset( $changes['caption'] ) )
				$post['post_excerpt'] = $changes['caption'];
		
			if ( isset( $changes['description'] ) )
				$post['post_content'] = $changes['description'];
		
			if ( isset( $changes['alt'] ) ) {
				$alt = wp_unslash( $changes['alt'] );
				if ( $alt != get_post_meta( $id, '_wp_attachment_image_alt', true ) ) {
					$alt = wp_strip_all_tags( $alt, true );
					update_post_meta( $id, '_wp_attachment_image_alt', wp_slash( $alt ) );
				}
			}
			
			
			// save post
			wp_update_post( $post );
			
			
			/** This filter is documented in wp-admin/includes/media.php */
			// - seems off to run this filter AFTER the update_post function, but there is a reason
			// - when placed BEFORE, an empty post_title will be populated by WP
			// - this filter will still allow 3rd party to save extra image data!
			$post = apply_filters( 'attachment_fields_to_save', $post, $changes );
			
			
			// save meta
			acf_save_post( $id );
						
		}
		
		
		// return
		wp_send_json_success();
			
	}
	
	
	/*
	*  ajax_get_sort_order
	*
	*  description
	*
	*  @type	function
	*  @date	13/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_get_sort_order() {
		
		// vars
		$r = array();
		$order = 'DESC';
   		$args = acf_parse_args( $_POST, array(
			'ids'			=> 0,
			'sort'			=> 'date',
			'field_key'		=> '',
			'nonce'			=> '',
		));
		
		
		// validate
		if( ! wp_verify_nonce($args['nonce'], 'acf_nonce') ) {
		
			wp_send_json_error();
			
		}
		
		
		// reverse
		if( $args['sort'] == 'reverse' ) {
		
			$ids = array_reverse($args['ids']);
			
			wp_send_json_success($ids);
			
		}
		
		
		if( $args['sort'] == 'title' ) {
			
			$order = 'ASC';
			
		}
		
		
		// find attachments (DISTINCT POSTS)
		$ids = get_posts(array(
			'post_type'		=> 'attachment',
			'numberposts'	=> -1,
			'post_status'	=> 'any',
			'post__in'		=> $args['ids'],
			'order'			=> $order,
			'orderby'		=> $args['sort'],
			'fields'		=> 'ids'		
		));
		
		
		// success
		if( !empty($ids) ) {
		
			wp_send_json_success($ids);
			
		}
		
		
		// failure
		wp_send_json_error();
		
	}
	
	
	/*
	*  render_attachment
	*
	*  description
	*
	*  @type	function
	*  @date	13/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_attachment( $id = 0, $field ) {
		
		// vars
		$attachment = wp_prepare_attachment_for_js( $id );
		$compat = get_compat_media_markup( $id );
		$compat = $compat['item'];
		$prefix = 'attachments[' . $id . ']';
		$thumb = '';
		$dimentions = '';
		
		
		// thumb
		if( isset($attachment['thumb']['src']) ) {
			
			// video
			$thumb = $attachment['thumb']['src'];
			
		} elseif( isset($attachment['sizes']['thumbnail']['url']) ) {
			
			// image
			$thumb = $attachment['sizes']['thumbnail']['url'];
			
		} elseif( $attachment['type'] === 'image' ) {
			
			// svg
			$thumb = $attachment['url'];
			
		} else {
			
			// fallback (perhaps attachment does not exist)
			$thumb = wp_mime_type_icon();
				
		}
		
		
		// dimentions
		if( $attachment['type'] === 'audio' ) {
			
			$dimentions = __('Length', 'acf') . ': ' . $attachment['fileLength'];
			
		} elseif( !empty($attachment['width']) ) {
			
			$dimentions = $attachment['width'] . ' x ' . $attachment['height'];
			
		}
		
		if( !empty($attachment['filesizeHumanReadable']) ) {
			
			$dimentions .=  ' (' . $attachment['filesizeHumanReadable'] . ')';
			
		}
		
		?>
		<div class="acf-gallery-side-info">
			<img src="<?php echo $thumb; ?>" alt="<?php echo $attachment['alt']; ?>" />
			<p class="filename"><strong><?php echo $attachment['filename']; ?></strong></p>
			<p class="uploaded"><?php echo $attachment['dateFormatted']; ?></p>
			<p class="dimensions"><?php echo $dimentions; ?></p>
			<p class="actions">
				<a href="#" class="acf-gallery-edit" data-id="<?php echo $id; ?>"><?php _e('Edit', 'acf'); ?></a>
				<a href="#" class="acf-gallery-remove" data-id="<?php echo $id; ?>"><?php _e('Remove', 'acf'); ?></a>
			</p>
		</div>
		<table class="form-table">
			<tbody>
				<?php 
				
				acf_render_field_wrap(array(
					//'key'		=> "{$field['key']}-title",
					'name'		=> 'title',
					'prefix'	=> $prefix,
					'type'		=> 'text',
					'label'		=> __('Title', 'acf'),
					'value'		=> $attachment['title']
				), 'tr');
				
				acf_render_field_wrap(array(
					//'key'		=> "{$field['key']}-caption",
					'name'		=> 'caption',
					'prefix'	=> $prefix,
					'type'		=> 'textarea',
					'label'		=> __('Caption', 'acf'),
					'value'		=> $attachment['caption']
				), 'tr');
				
				acf_render_field_wrap(array(
					//'key'		=> "{$field['key']}-alt",
					'name'		=> 'alt',
					'prefix'	=> $prefix,
					'type'		=> 'text',
					'label'		=> __('Alt Text', 'acf'),
					'value'		=> $attachment['alt']
				), 'tr');
				
				acf_render_field_wrap(array(
					//'key'		=> "{$field['key']}-description",
					'name'		=> 'description',
					'prefix'	=> $prefix,
					'type'		=> 'textarea',
					'label'		=> __('Description', 'acf'),
					'value'		=> $attachment['description']
				), 'tr');
				
				?>
			</tbody>
		</table>
		<?php
		
		echo $compat;
		
	}
	
	
	/*
	*  get_attachments
	*
	*  This function will return an array of attachments for a given field value
	*
	*  @type	function
	*  @date	13/06/2014
	*  @since	5.0.0
	*
	*  @param	$value (array)
	*  @return	$value
	*/
	
	function get_attachments( $value ) {
		
		// bail early if no value
		if( empty($value) ) return false;
		
		
		// force value to array
		$post__in = acf_get_array( $value );
		
		
		// get posts
		$posts = acf_get_posts(array(
			'post_type'	=> 'attachment',
			'post__in'	=> $post__in
		));
		
		
		// return
		return $posts;
				
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
		
		// enqueue
		acf_enqueue_uploader();
		
		
		// vars
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> "acf-gallery {$field['class']}",
			'data-library'		=> $field['library'],
			'data-min'			=> $field['min'],
			'data-max'			=> $field['max'],
			'data-mime_types'	=> $field['mime_types'],
			'data-insert'		=> $field['insert'],
			'data-columns'		=> 4
		);
		
		
		// set gallery height
		$height = acf_get_user_setting('gallery_height', 400);
		$height = max( $height, 200 ); // minimum height is 200
		$atts['style'] = "height:{$height}px";
		
		
		// get posts
		$value = $this->get_attachments( $field['value'] );
		
		?>
<div <?php acf_esc_attr_e($atts); ?>>
	
	<div class="acf-hidden">
		<?php acf_hidden_input(array( 'name' => $field['name'], 'value' => '' )); ?>
	</div>
	
	<div class="acf-gallery-main">
		
		<div class="acf-gallery-attachments">
			
			<?php if( $value ): ?>
			
				<?php foreach( $value as $i => $v ): 
					
					// bail early if no value
					if( !$v ) continue;
					
					
					// vars
					$a = array(
						'ID' 		=> $v->ID,
						'title'		=> $v->post_title,
						'filename'	=> wp_basename($v->guid),
						'type'		=> acf_maybe_get(explode('/', $v->post_mime_type), 0),
						'class'		=> 'acf-gallery-attachment'
					);
					
					
					// thumbnail
					$thumbnail = acf_get_post_thumbnail($a['ID'], 'medium');
					
					
					// remove filename if is image
					if( $a['type'] == 'image' ) $a['filename'] = '';
					
					
					// class
					$a['class'] .= ' -' . $a['type'];
					
					if( $thumbnail['type'] == 'icon' ) {
						
						$a['class'] .= ' -icon';
						
					}
					
					
					?>
					<div class="<?php echo $a['class']; ?>" data-id="<?php echo $a['ID']; ?>">
						<?php acf_hidden_input(array( 'name' => $field['name'].'[]', 'value' => $a['ID'] )); ?>
						<div class="margin">
							<div class="thumbnail">
								<img src="<?php echo $thumbnail['url']; ?>" alt="" title="<?php echo $a['title']; ?>"/>
							</div>
							<?php if( $a['filename'] ): ?>
							<div class="filename"><?php echo acf_get_truncated($a['filename'], 30); ?></div>	
							<?php endif; ?>
						</div>
						<div class="actions">
							<a class="acf-icon -cancel dark acf-gallery-remove" href="#" data-id="<?php echo $a['ID']; ?>" title="<?php _e('Remove', 'acf'); ?>"></a>
						</div>
					</div>
				<?php endforeach; ?>
				
			<?php endif; ?>
			
		</div>
		
		<div class="acf-gallery-toolbar">
			
			<ul class="acf-hl">
				<li>
					<a href="#" class="acf-button button button-primary acf-gallery-add"><?php _e('Add to gallery', 'acf'); ?></a>
				</li>
				<li class="acf-fr">
					<select class="acf-gallery-sort">
						<option value=""><?php _e('Bulk actions', 'acf'); ?></option>
						<option value="date"><?php _e('Sort by date uploaded', 'acf'); ?></option>
						<option value="modified"><?php _e('Sort by date modified', 'acf'); ?></option>
						<option value="title"><?php _e('Sort by title', 'acf'); ?></option>
						<option value="reverse"><?php _e('Reverse current order', 'acf'); ?></option>
					</select>
				</li>
			</ul>
			
		</div>
		
	</div>
	
	<div class="acf-gallery-side">
	<div class="acf-gallery-side-inner">
			
		<div class="acf-gallery-side-data"></div>
						
		<div class="acf-gallery-toolbar">
			
			<ul class="acf-hl">
				<li>
					<a href="#" class="acf-button button acf-gallery-close"><?php _e('Close', 'acf'); ?></a>
				</li>
				<li class="acf-fr">
					<a class="acf-button button button-primary acf-gallery-update" href="#"><?php _e('Update', 'acf'); ?></a>
				</li>
			</ul>
			
		</div>
		
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
		
		// clear numeric settings
		$clear = array(
			'min',
			'max',
			'min_width',
			'min_height',
			'min_size',
			'max_width',
			'max_height',
			'max_size'
		);
		
		foreach( $clear as $k ) {
			
			if( empty($field[$k]) ) $field[$k] = '';
			
		}
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum Selection','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min'
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum Selection','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max'
		));
		
		
		// insert
		acf_render_field_setting( $field, array(
			'label'			=> __('Insert','acf'),
			'instructions'	=> __('Specify where new attachments are added','acf'),
			'type'			=> 'select',
			'name'			=> 'insert',
			'choices' 		=> array(
				'append'		=> __('Append to the end', 'acf'),
				'prepend'		=> __('Prepend to the beginning', 'acf')
			)
		));
		
		
		// library
		acf_render_field_setting( $field, array(
			'label'			=> __('Library','acf'),
			'instructions'	=> __('Limit the media library choice','acf'),
			'type'			=> 'radio',
			'name'			=> 'library',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'all'			=> __('All', 'acf'),
				'uploadedTo'	=> __('Uploaded to post', 'acf')
			)
		));
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum','acf'),
			'instructions'	=> __('Restrict which images can be uploaded','acf'),
			'type'			=> 'text',
			'name'			=> 'min_width',
			'prepend'		=> __('Width', 'acf'),
			'append'		=> 'px',
		));
		
		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'min_height',
			'prepend'		=> __('Height', 'acf'),
			'append'		=> 'px',
			'_append' 		=> 'min_width'
		));
		
		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'min_size',
			'prepend'		=> __('File size', 'acf'),
			'append'		=> 'MB',
			'_append' 		=> 'min_width'
		));	
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum','acf'),
			'instructions'	=> __('Restrict which images can be uploaded','acf'),
			'type'			=> 'text',
			'name'			=> 'max_width',
			'prepend'		=> __('Width', 'acf'),
			'append'		=> 'px',
		));
		
		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'max_height',
			'prepend'		=> __('Height', 'acf'),
			'append'		=> 'px',
			'_append' 		=> 'max_width'
		));
		
		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'max_size',
			'prepend'		=> __('File size', 'acf'),
			'append'		=> 'MB',
			'_append' 		=> 'max_width'
		));	
		
		
		// allowed type
		acf_render_field_setting( $field, array(
			'label'			=> __('Allowed file types','acf'),
			'instructions'	=> __('Comma separated list. Leave blank for all types','acf'),
			'type'			=> 'text',
			'name'			=> 'mime_types',
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
		if( empty($value) ) return false;
		
		
		// get posts
		$posts = $this->get_attachments($value);
		
		
		// update value to include $post
		foreach( array_keys($posts) as $i ) {
			
			$posts[ $i ] = acf_get_attachment( $posts[ $i ] );
			
		}
				
		
		// return
		return $posts;
		
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
		
		if( empty($value) || !is_array($value) ) {
		
			$value = array();
			
		}
		
		
		if( count($value) < $field['min'] ) {
		
			$valid = _n( '%s requires at least %s selection', '%s requires at least %s selections', $field['min'], 'acf' );
			$valid = sprintf( $valid, $field['label'], $field['min'] );
			
		}
		
				
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
		
		// bail early if no value
		if( empty($value) || !is_array($value) ) return false;
		
		
		// loop
		foreach( $value as $i => $v ) {
			
			$value[ $i ] = $this->update_single_value( $v );
			
		}
				
		
		// return
		return $value;
		
	}
	
	
	/*
	*  update_single_value()
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
	
	function update_single_value( $value ) {
		
		// numeric
		if( is_numeric($value) ) return $value;
		
		
		// array?
		if( is_array($value) && isset($value['ID']) ) return $value['ID'];
		
		
		// object?
		if( is_object($value) && isset($value->ID) ) return $value->ID;
		
		
		// return
		return $value;
		
	}

	
}


// initialize
acf_register_field_type( 'acf_field_gallery' );

endif; // class_exists check

?>