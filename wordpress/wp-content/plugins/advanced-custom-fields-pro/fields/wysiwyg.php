<?php

/*
*  ACF WYSIWYG Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_wysiwyg
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_wysiwyg') ) :

class acf_field_wysiwyg extends acf_field {
	
	
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
	
	function __construct() {
		
		// vars
		$this->name = 'wysiwyg';
		$this->label = __("Wysiwyg Editor",'acf');
		$this->category = 'content';
		$this->defaults = array(
			'tabs'			=> 'all',
			'toolbar'		=> 'full',
			'media_upload' 	=> 1,
			'default_value'	=> '',
			'delay'			=> 0
		);
    	
    	
    	// add acf_the_content filters
    	$this->add_filters();
		
		
		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  add_filters
	*
	*  This function will add filters to 'acf_the_content'
	*
	*  @type	function
	*  @date	20/09/2016
	*  @since	5.4.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function add_filters() {
		
		// globals
   		global $wp_version;
   		
   		
		// wp-includes/class-wp-embed.php
		if(	!empty($GLOBALS['wp_embed']) ) {
		
			add_filter( 'acf_the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
			add_filter( 'acf_the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
			
		}
		
		
		// wp-includes/default-filters.php
		add_filter( 'acf_the_content', 'capital_P_dangit', 11 );
		add_filter( 'acf_the_content', 'wptexturize' );
		add_filter( 'acf_the_content', 'convert_smilies', 20 );
		
		
		// Removed in 4.4
		if( version_compare($wp_version, '4.4', '<' ) ) {
			
			add_filter( 'acf_the_content', 'convert_chars' );
			
		}
		
		
		add_filter( 'acf_the_content', 'wpautop' );
		add_filter( 'acf_the_content', 'shortcode_unautop' );
		
		
		// should only be for the_content (causes double image on attachment page)
		//add_filter( 'acf_the_content', 'prepend_attachment' ); 
		
		
		// Added in 4.4
		if( function_exists('wp_make_content_images_responsive') ) {
			
			add_filter( 'acf_the_content', 'wp_make_content_images_responsive' );
			
		}
		
		
		add_filter( 'acf_the_content', 'do_shortcode', 11);
		
	}
	
	
	/*
	*  get_toolbars
	*
	*  This function will return an array of toolbars for the WYSIWYG field
	*
	*  @type	function
	*  @date	18/04/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	(array)
	*/
	
   	function get_toolbars() {
   		
   		// global
   		global $wp_version;
   		
   		
   		// vars
   		$editor_id = 'acf_content';
   		
   		
   		// toolbars
   		$toolbars = array();
   		$mce_buttons = 'formatselect, bold, italic, bullist, numlist, blockquote, alignleft, aligncenter, alignright, link, unlink, wp_more, spellchecker, fullscreen, wp_adv';
   		$mce_buttons_2 = 'strikethrough, hr, forecolor, pastetext, removeformat, charmap, outdent, indent, undo, redo, wp_help';
   		$teeny_mce_buttons = 'bold, italic, underline, blockquote, strikethrough, bullist, numlist, alignleft, aligncenter, alignright, undo, redo, link, unlink, fullscreen';
   		
   		
   		// WP < 3.9
   		if( acf_version_compare('wp', '<', '3.9') ) {
	   		
	   		$mce_buttons = 'bold, italic, strikethrough, bullist, numlist, blockquote, justifyleft, justifycenter, justifyright, link, unlink, wp_more, spellchecker, fullscreen, wp_adv';
	   		$mce_buttons_2 = 'formatselect, underline, justifyfull, forecolor, pastetext, pasteword, removeformat, charmap, outdent, indent, undo, redo, wp_help';
	   		$teeny_mce_buttons = 'bold, italic, underline, blockquote, strikethrough, bullist, numlist, justifyleft, justifycenter, justifyright, undo, redo, link, unlink, fullscreen';
	   	
	   	// WP < 4.7	
	   	} elseif( acf_version_compare('wp', '<', '4.7') ) {
			
			$mce_buttons = 'bold, italic, strikethrough, bullist, numlist, blockquote, hr, alignleft, aligncenter, alignright, link, unlink, wp_more, spellchecker, fullscreen, wp_adv';
	   		$mce_buttons_2 = 'formatselect, underline, alignjustify, forecolor, pastetext, removeformat, charmap, outdent, indent, undo, redo, wp_help';
	   		//$teeny_mce_buttons = 'bold, italic, underline, blockquote, strikethrough, bullist, numlist, alignleft, aligncenter, alignright, undo, redo, link, unlink, fullscreen';
			
		}
	   	
	   	
	   	// explode
	   	$mce_buttons = explode(', ', $mce_buttons);
	   	$mce_buttons_2 = explode(', ', $mce_buttons_2);
   		$teeny_mce_buttons = explode(', ', $teeny_mce_buttons);
   			
   		
		// Full
   		$toolbars['Full'] = array(
   			1 => apply_filters('mce_buttons',	$mce_buttons,	$editor_id),
   			2 => apply_filters('mce_buttons_2', $mce_buttons_2,	$editor_id),
   			3 => apply_filters('mce_buttons_3', array(),		$editor_id),
   			4 => apply_filters('mce_buttons_4', array(),		$editor_id)
   		);
	   	
	   	
   		// Basic
   		$toolbars['Basic'] = array(
   			1 => apply_filters('teeny_mce_buttons', $teeny_mce_buttons, $editor_id)
   		);
   		
   		
   		// Filter for 3rd party
   		$toolbars = apply_filters( 'acf/fields/wysiwyg/toolbars', $toolbars );
   		
   		
   		// return
	   	return $toolbars;
	   	
   	}
   	
   	
   	/*
   	*  input_admin_footer
   	*
   	*  description
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$post_id (int)
   	*  @return	$post_id (int)
   	*/
   	
   	function input_admin_footer() {
	   	
	   	// vars
		$json = array();
		$toolbars = $this->get_toolbars();

		
		// bail ealry if no toolbars
		if( empty($toolbars) ) {
			
			return;
			
		}
		
			
		// loop through toolbars
		foreach( $toolbars as $label => $rows ) {
			
			// vars
			$label = sanitize_title( $label );
			$label = str_replace('-', '_', $label);
			
			
			// append to $json
			$json[ $label ] = array();
			
			
			// convert to strings
			if( !empty($rows) ) {
				
				foreach( $rows as $i => $row ) { 
					
					$json[ $label ][ $i ] = implode(',', $row);
					
				}
				
			}
			
		}
		

?>
<script type="text/javascript">
	if( acf ) acf.fields.wysiwyg.toolbars = <?php echo json_encode($json); ?>;
</script>
<?php
	
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
		
		// global
   		global $wp_version;
   		
   		
		// enqueue
		acf_enqueue_uploader();
		
		
		// vars
		$id = uniqid('acf-editor-');
		$default_editor = 'html';
		$show_tabs = true;
		$button = '';
		
		
		// get height
		$height = acf_get_user_setting('wysiwyg_height', 300);
		$height = max( $height, 300 ); // minimum height is 300
		
		
		// detect mode
		if( !user_can_richedit() ) {
			
			$show_tabs = false;
			
		} elseif( $field['tabs'] == 'visual' ) {
			
			// case: visual tab only
			$default_editor = 'tinymce';
			$show_tabs = false;
			
		} elseif( $field['tabs'] == 'text' ) {
			
			// case: text tab only
			$show_tabs = false;
			
		} elseif( wp_default_editor() == 'tinymce' ) {
			
			// case: both tabs
			$default_editor = 'tinymce';
			
		}
		
		
		// must be logged in tp upload
		if( !current_user_can('upload_files') ) {
			
			$field['media_upload'] = 0;
			
		}
		
		
		// mode
		$switch_class = ($default_editor === 'html') ? 'html-active' : 'tmce-active';
		
		
		// filter value for editor
		remove_filter( 'acf_the_editor_content', 'format_for_editor', 10, 2 );
		remove_filter( 'acf_the_editor_content', 'wp_htmledit_pre', 10, 1 );
		remove_filter( 'acf_the_editor_content', 'wp_richedit_pre', 10, 1 );
		
		
		// WP 4.3
		if( version_compare($wp_version, '4.3', '>=' ) ) {
			
			add_filter( 'acf_the_editor_content', 'format_for_editor', 10, 2 );
			
			$button = 'data-wp-editor-id="' . $id . '"';
			
		// WP < 4.3
		} else {
			
			$function = ($default_editor === 'html') ? 'wp_htmledit_pre' : 'wp_richedit_pre';
			
			add_filter('acf_the_editor_content', $function, 10, 1);
			
			$button = 'onclick="switchEditors.switchto(this);"';
			
		}
		
		
		// filter
		$field['value'] = apply_filters( 'acf_the_editor_content', $field['value'], $default_editor );
		
		
		// attr
		$wrap = array(
			'id'			=> 'wp-' . $id . '-wrap',
			'class'			=> 'acf-editor-wrap wp-core-ui wp-editor-wrap ' . $switch_class,
			'data-toolbar'	=> $field['toolbar']
		);
		
		
		// delay
		if( $field['delay'] ) {
			
			$wrap['class'] .= ' delay';
			
		}
		
		?>
		<div <?php echo acf_esc_attr($wrap); ?>>
			<div id="wp-<?php echo $id; ?>-editor-tools" class="wp-editor-tools hide-if-no-js">
				<?php if( $field['media_upload'] ): ?>
				<div id="wp-<?php echo $id; ?>-media-buttons" class="wp-media-buttons">
					<?php do_action( 'media_buttons', $id ); ?>
				</div>
				<?php endif; ?>
				<?php if( user_can_richedit() && $show_tabs ): ?>
					<div class="wp-editor-tabs">
						<button id="<?php echo $id; ?>-tmce" class="wp-switch-editor switch-tmce" <?php echo $button; ?> type="button"><?php echo __('Visual', 'acf'); ?></button>
						<button id="<?php echo $id; ?>-html" class="wp-switch-editor switch-html" <?php echo $button; ?> type="button"><?php echo _x( 'Text', 'Name for the Text editor tab (formerly HTML)', 'acf' ); ?></button>
					</div>
				<?php endif; ?>
			</div>
			<div id="wp-<?php echo $id; ?>-editor-container" class="wp-editor-container">
				<?php if( $field['delay'] ): ?>
					<div class="acf-editor-toolbar"><?php _e('Click to initialize TinyMCE', 'acf'); ?></div>
				<?php endif; ?>
				<textarea id="<?php echo $id; ?>" class="wp-editor-area" name="<?php echo $field['name']; ?>" <?php if($height): ?>style="height:<?php echo $height; ?>px;"<?php endif; ?>><?php echo $field['value']; ?></textarea>
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
		$toolbars = $this->get_toolbars();
		$choices = array();
		
		if( !empty($toolbars) ) {
		
			foreach( $toolbars as $k => $v ) {
				
				$label = $k;
				$name = sanitize_title( $label );
				$name = str_replace('-', '_', $name);
				
				$choices[ $name ] = $label;
			}
		}
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Appears when creating a new post','acf'),
			'type'			=> 'textarea',
			'name'			=> 'default_value',
		));
		
		
		// tabs
		acf_render_field_setting( $field, array(
			'label'			=> __('Tabs','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'tabs',
			'choices'		=> array(
				'all'			=>	__("Visual & Text",'acf'),
				'visual'		=>	__("Visual Only",'acf'),
				'text'			=>	__("Text Only",'acf'),
			)
		));
		
		
		// toolbar
		acf_render_field_setting( $field, array(
			'label'			=> __('Toolbar','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'toolbar',
			'choices'		=> $choices
		));
		
		
		// media_upload
		acf_render_field_setting( $field, array(
			'label'			=> __('Show Media Upload Buttons?','acf'),
			'instructions'	=> '',
			'name'			=> 'media_upload',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// delay
		acf_render_field_setting( $field, array(
			'label'			=> __('Delay initialization?','acf'),
			'instructions'	=> __('TinyMCE will not be initalized until field is clicked','acf'),
			'name'			=> 'delay',
			'type'			=> 'true_false',
			'ui'			=> 1,
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
		
		
		// apply filters
		$value = apply_filters( 'acf_the_content', $value );
		
		
		// follow the_content function in /wp-includes/post-template.php
		$value = str_replace(']]>', ']]&gt;', $value);
		
	
		return $value;
	}
	
}


// initialize
acf_register_field_type( new acf_field_wysiwyg() );

endif; // class_exists check

?>