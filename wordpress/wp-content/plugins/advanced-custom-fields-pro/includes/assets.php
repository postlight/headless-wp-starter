<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Assets') ) :

class ACF_Assets {
	
	/** @var array Storage for translations */
	var $text = array();
	
	/** @var array Storage for data */
	var $data = array();
	
	
	/**
	*  __construct
	*
	*  description
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	void
	*  @return	void
	*/
		
	function __construct() {
		
		// actions
		add_action('init',	array($this, 'register_scripts'));
	}
	
	
	/**
	*  add_text
	*
	*  description
	*
	*  @date	13/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function add_text( $text ) {
		foreach( (array) $text as $k => $v ) {
			$this->text[ $k ] = $v;
		}
	}
	
	
	/**
	*  add_data
	*
	*  description
	*
	*  @date	13/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function add_data( $data ) {
		foreach( (array) $data as $k => $v ) {
			$this->data[ $k ] = $v;
		}
	}
	
	
	/**
	*  register_scripts
	*
	*  description
	*
	*  @date	13/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function register_scripts() {
		
		// vars
		$version = acf_get_setting('version');
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		
		// scripts
		wp_register_script('acf-input', acf_get_url("assets/js/acf-input{$min}.js"), array('jquery', 'jquery-ui-sortable', 'jquery-ui-resizable'), $version );
		wp_register_script('acf-field-group', acf_get_url("assets/js/acf-field-group{$min}.js"), array('acf-input'), $version );
		
		// styles
		wp_register_style('acf-global', acf_get_url('assets/css/acf-global.css'), array(), $version );
		wp_register_style('acf-input', acf_get_url('assets/css/acf-input.css'), array('acf-global'), $version );
		wp_register_style('acf-field-group', acf_get_url('assets/css/acf-field-group.css'), array('acf-input'), $version );
		
		// action
		do_action('acf/register_scripts', $version, $min);
	}
	
	
	/**
	*  enqueue_scripts
	*
	*  Enqueue scripts for input
	*
	*  @date	13/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function enqueue_scripts( $args = array() ) {
		
		// run only once
		if( acf_has_done('enqueue_scripts') ) {
			return;
		}
		
		// defaults
		$args = wp_parse_args($args, array(
			
			// force tinymce editor to be enqueued
			'uploader'			=> false,
			
			// priority used for action callbacks, defaults to 20 which runs after defaults
			'priority'			=> 20,
			
			// action prefix 
			'context'			=> is_admin() ? 'admin' : 'wp'
		));
		
		// define actions
		$actions = array(
			'admin_enqueue_scripts'			=> $args['context'] . '_enqueue_scripts',
			'admin_print_scripts'			=> $args['context'] . '_print_scripts',
			'admin_head'					=> $args['context'] . '_head',
			'admin_footer'					=> $args['context'] . '_footer',
			'admin_print_footer_scripts'	=> $args['context'] . '_print_footer_scripts',
		);
		
		// fix customizer actions where head and footer are not available
		if( $args['context'] == 'customize_controls' ) {
			$actions['admin_head'] = $actions['admin_print_scripts'];
			$actions['admin_footer'] = $actions['admin_print_footer_scripts'];
		}
		
		// add actions
		foreach( $actions as $function => $action ) {
			acf_maybe_add_action( $action, array($this, $function), $args['priority'] );
		}
		
		// enqueue uploader
		// WP requires a lot of JS + inline scripes to create the media modal and should be avoioded when possible.
		// - priority must be less than 10 to allow WP to enqueue
		if( $args['uploader'] ) {
			add_action($actions['admin_footer'], 'acf_enqueue_uploader', 5);
		}
		
		// enqueue
		wp_enqueue_script('acf-input');
		wp_enqueue_style('acf-input');
		
		// localize text
		acf_localize_text(array(
			
			// unload
			'The changes you made will be lost if you navigate away from this page'	=> __('The changes you made will be lost if you navigate away from this page', 'acf'),
			
			// media
			'Select.verb'			=> _x('Select', 'verb', 'acf'),
			'Edit.verb'				=> _x('Edit', 'verb', 'acf'),
			'Update.verb'			=> _x('Update', 'verb', 'acf'),
			'Uploaded to this post'	=> __('Uploaded to this post', 'acf'),
			'Expand Details' 		=> __('Expand Details', 'acf'),
			'Collapse Details' 		=> __('Collapse Details', 'acf'),
			'Restricted'			=> __('Restricted', 'acf'),
			'All images'			=> __('All images', 'acf'),
			
			// validation
			'Validation successful'			=> __('Validation successful', 'acf'),
			'Validation failed'				=> __('Validation failed', 'acf'),
			'1 field requires attention'	=> __('1 field requires attention', 'acf'),
			'%d fields require attention'	=> __('%d fields require attention', 'acf'),
			
			// tooltip
			'Are you sure?'			=> __('Are you sure?','acf'),
			'Yes'					=> __('Yes','acf'),
			'No'					=> __('No','acf'),
			'Remove'				=> __('Remove','acf'),
			'Cancel'				=> __('Cancel','acf'),
			
			// conditions
			'Has any value'				=> __('Has any value', 'acf'),
			'Has no value'				=> __('Has no value', 'acf'),
			'Value is equal to'			=> __('Value is equal to', 'acf'),
			'Value is not equal to'		=> __('Value is not equal to', 'acf'),
			'Value matches pattern'		=> __('Value matches pattern', 'acf'),
			'Value contains'			=> __('Value contains', 'acf'),
			'Value is greater than'		=> __('Value is greater than', 'acf'),
			'Value is less than'		=> __('Value is less than', 'acf'),
			'Selection is greater than'	=> __('Selection is greater than', 'acf'),
			'Selection is less than'	=> __('Selection is less than', 'acf'),
		));
		
		// action
		do_action('acf/enqueue_scripts');
	}
	
	
	/**
	*  admin_enqueue_scripts
	*
	*  description
	*
	*  @date	16/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_enqueue_scripts() {
		
		// vars
		$text = array();
		
		// actions
		do_action('acf/admin_enqueue_scripts');
		do_action('acf/input/admin_enqueue_scripts');
		
		// only include translated strings
		foreach( $this->text as $k => $v ) {
			if( str_replace('.verb', '', $k) !== $v ) {
				$text[ $k ] = $v;
			}
		}
		
		// localize text
		if( $text ) {
			wp_localize_script( 'acf-input', 'acfL10n', $text );
		}
	}
	
	
	/**
	*  admin_print_scripts
	*
	*  description
	*
	*  @date	18/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_print_scripts() {
		do_action('acf/admin_print_scripts');
	}
	
	
	/**
	*  admin_head
	*
	*  description
	*
	*  @date	16/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_head() {

		// actions
		do_action('acf/admin_head');
		do_action('acf/input/admin_head');
	}
	
	
	/**
	*  admin_footer
	*
	*  description
	*
	*  @date	16/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_footer() {
		
		// global
		global $wp_version;
		
		// get data
		$data = wp_parse_args($this->data, array(
			'screen'		=> acf_get_form_data('screen'),
			'post_id'		=> acf_get_form_data('post_id'),
			'nonce'			=> wp_create_nonce( 'acf_nonce' ),
			'admin_url'		=> admin_url(),
			'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
			'validation'	=> acf_get_form_data('validation'),
			'wp_version'	=> $wp_version,
			'acf_version'	=> acf_get_setting('version'),
			'browser'		=> acf_get_browser(),
			'locale'		=> acf_get_locale(),
			'rtl'			=> is_rtl()
		));
		
		// get l10n (old)
		$l10n = apply_filters( 'acf/input/admin_l10n', array() );
		
		// todo: force 'acf-input' script enqueue if not yet included
		// - fixes potential timing issue if acf_enqueue_assest() was called during body
		
		// localize data
		?>
<script type="text/javascript">
acf.data = <?php echo wp_json_encode($data); ?>;
acf.l10n = <?php echo wp_json_encode($l10n); ?>;
</script>
<?php 
		
		// actions
		do_action('acf/admin_footer');
		do_action('acf/input/admin_footer');
		
		// trigger prepare
		?>
<script type="text/javascript">
acf.doAction('prepare');
</script>
<?php
	
	}
	
	
	/**
	*  admin_print_footer_scripts
	*
	*  description
	*
	*  @date	18/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_print_footer_scripts() {
		do_action('acf/admin_print_footer_scripts');
	}
	
	/*
	*  enqueue_uploader
	*
	*  This function will render a WP WYSIWYG and enqueue media
	*
	*  @type	function
	*  @date	27/10/2014
	*  @since	5.0.9
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function enqueue_uploader() {
		
		// run only once
		if( acf_has_done('enqueue_uploader') ) {
			return;
		}
		
		// bail early if doing ajax
		if( acf_is_ajax() ) {
			return;
		}
		
		// enqueue media if user can upload
		if( current_user_can('upload_files') ) {
			wp_enqueue_media();
		}
		
		// create dummy editor
		?>
		<div id="acf-hidden-wp-editor" class="acf-hidden">
			<?php wp_editor( '', 'acf_content' ); ?>
		</div>
		<?php
	}
}

// instantiate
acf_new_instance('ACF_Assets');

endif; // class_exists check


/**
*  acf_localize_text
*
*  description
*
*  @date	13/4/18
*  @since	5.6.9
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function acf_localize_text( $text ) {
	return acf_get_instance('ACF_Assets')->add_text( $text );
}


/**
*  acf_localize_data
*
*  description
*
*  @date	13/4/18
*  @since	5.6.9
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function acf_localize_data( $data ) {
	return acf_get_instance('ACF_Assets')->add_data( $data );
}


/*
*  acf_enqueue_scripts
*
*  
*
*  @type	function
*  @date	6/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_enqueue_scripts( $args = array() ) {
	return acf_get_instance('ACF_Assets')->enqueue_scripts( $args );
}


/*
*  acf_enqueue_uploader
*
*  This function will render a WP WYSIWYG and enqueue media
*
*  @type	function
*  @date	27/10/2014
*  @since	5.0.9
*
*  @param	n/a
*  @return	n/a
*/

function acf_enqueue_uploader() {
	return acf_get_instance('ACF_Assets')->enqueue_uploader();
}

?>