<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Form_Gutenberg') ) :

class ACF_Form_Gutenberg {
	
	/**
	*  __construct
	*
	*  Setup for class functionality.
	*
	*  @date	13/2/18
	*  @since	5.6.9
	*
	*  @param	n/a
	*  @return	n/a
	*/
		
	function __construct() {
		
		// filters
		add_filter( 'replace_editor', array($this, 'replace_editor'), 99, 2 );
	}
	
	
	/**
	*  replace_editor
	*
	*  Check if Gutenberg is replacing the editor.
	*
	*  @date	13/2/18
	*  @since	5.6.9
	*
	*  @param	boolean $replace True if the editor is being replaced by Gutenberg.
	*  @param	object $post The WP_Post being edited.
	*  @return	boolean
	*/
	
	function replace_editor( $replace, $post ) {
		
		// check if Gutenberg is replacing
		if( $replace ) {
			
			// actions
			add_action('admin_footer', array($this, 'admin_footer'));
		}
		
		// return
		return $replace;
	}
	
	/**
	*  admin_footer
	*
	*  Append missing HTML to Gutenberg editor.
	*
	*  @date	13/2/18
	*  @since	5.6.9
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_footer() {
		
		// edit_form_after_title is not run due to missing action, call this manually
		?>
		<div id="acf-form-after-title">
			<?php acf_get_instance('ACF_Form_Post')->edit_form_after_title(); ?>
		</div>
		<?php
		
		
		// move #acf-form-after-title
		?>
		<script type="text/javascript">
			$('#normal-sortables').before( $('#acf-form-after-title') );
		</script>
		<?php
	}		
}

acf_new_instance('ACF_Form_Gutenberg');

endif;

?>