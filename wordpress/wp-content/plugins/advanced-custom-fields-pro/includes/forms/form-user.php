<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Form_User') ) :

class ACF_Form_User {
	
	/** @var string The current view (new, edit, register) */
	var $view = '';
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// enqueue
		add_action('admin_enqueue_scripts',			array($this, 'admin_enqueue_scripts'));
		add_action('login_form_register', 			array($this, 'login_form_register'));
		
		// render
		add_action('show_user_profile', 			array($this, 'render_edit'));
		add_action('edit_user_profile',				array($this, 'render_edit'));
		add_action('user_new_form',					array($this, 'render_new'));
		add_action('register_form',					array($this, 'render_register'));
		
		// save
		add_action('user_register',					array($this, 'save_user'));
		add_action('profile_update',				array($this, 'save_user'));
	}
	
	
	/**
	*  admin_enqueue_scripts
	*
	*  Checks current screen and enqueues scripts
	*
	*  @date	17/4/18
	*  @since	5.6.9
	*
	*  @param	void
	*  @return	void
	*/
	
	function admin_enqueue_scripts() {
		
		// bail early if not valid screen
		if( !acf_is_screen(array('profile', 'user', 'user-edit')) ) {
			return;
		}
		
		// enqueue
		acf_enqueue_scripts();
	}
	
	
	/**
	*  login_form_register
	*
	*  Customizes and enqueues scripts
	*
	*  @date	17/4/18
	*  @since	5.6.9
	*
	*  @param	void
	*  @return	void
	*/
	
	function login_form_register() {
		
		// customize action prefix so that "admin_head" = "login_head"
		acf_enqueue_scripts(array(
			'context' => 'login'
		));
	}
	
	
	/*
	*  register_user
	*
	*  Called during the user register form
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	void
	*  @return	void
	*/
	
	function render_register() {
		
		// render
		$this->render(array(
			'user_id'	=> 0,
			'view'		=> 'register',
			'el'		=> 'div'
		));
	}
	
	
	/*
	*  render_edit
	*
	*  Called during the user edit form
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	void
	*  @return	void
	*/
	
	function render_edit( $user ) {
		
		// add compatibility with front-end user profile edit forms such as bbPress
		if( !is_admin() ) {
			acf_enqueue_scripts();
		}
		
		// render
		$this->render(array(
			'user_id'	=> $user->ID,
			'view'		=> 'edit',
			'el'		=> 'tr'
		));
	}
	
	
	/*
	*  user_new_form
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_new() {
		
		// Multisite uses a different 'user-new.php' form. Don't render fields here
		if( is_multisite() ) {
			return;
		}
		
		// render
		$this->render(array(
			'user_id'	=> 0,
			'view'		=> 'add',
			'el'		=> 'tr'
		));
	}
	
	
	/*
	*  render
	*
	*  This function will render ACF fields for a given $post_id parameter
	*
	*  @type	function
	*  @date	7/10/13
	*  @since	5.0.0
	*
	*  @param	$user_id (int) this can be set to 0 for a new user
	*  @param	$user_form (string) used for location rule matching. edit | add | register
	*  @param	$el (string)
	*  @return	n/a
	*/
	
	function render( $args = array() ) {
		
		// defaults
		$args = wp_parse_args($args, array(
			'user_id'	=> 0,
			'view'		=> 'edit',
			'el'		=> 'tr',
		));
		
		// vars
		$post_id = 'user_' . $args['user_id'];
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'user_id'	=> $args['user_id'] ? $args['user_id'] : 'new',
			'user_form'	=> $args['view']
		));
		
		// bail early if no field groups
		if( empty($field_groups) ) {
			return;
		}
		
		// form data
		acf_form_data(array(
			'screen'		=> 'user',
			'post_id'		=> $post_id,
			'validation'	=> ($args['view'] == 'register') ? 0 : 1
		));
		
		// elements
		$before = '<table class="form-table"><tbody>';
		$after = '</tbody></table>';
				
		if( $args['el'] == 'div') {
			$before = '<div class="acf-user-' . $args['view'] . '-fields acf-fields -clear">';
			$after = '</div>';
		}
		
		// loop
		foreach( $field_groups as $field_group ) {
			
			// vars
			$fields = acf_get_fields( $field_group );
			
			// title
			if( $field_group['style'] === 'default' ) {
				echo '<h2>' . $field_group['title'] . '</h2>';
			}
			
			// render
			echo $before;
			acf_render_fields( $fields, $post_id, $args['el'], $field_group['instruction_placement'] );
			echo $after;
		}
				
		// actions
		add_action('acf/input/admin_footer', array($this, 'admin_footer'), 10, 1);
	}
	
	
	/*
	*  admin_footer
	*
	*  description
	*
	*  @type	function
	*  @date	27/03/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_footer() {
	
		// script
		?>
<script type="text/javascript">
(function($) {
	
	// vars
	var view = '<?php echo $this->view; ?>';
	
	// add missing spinners
	var $submit = $('input.button-primary');
	if( !$submit.next('.spinner').length ) {
		$submit.after('<span class="spinner"></span>');
	}
	
})(jQuery);	
</script>
<?php
		
	}
	
	
	/*
	*  save_user
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_user( $user_id ) {
		
		// verify nonce
		if( !acf_verify_nonce('user') ) {
			return $user_id;
		}
		
	    // save
	    if( acf_validate_save_post(true) ) {
			acf_save_post( "user_$user_id" );
		}
	}		
}

// instantiate
acf_new_instance('ACF_Form_User');

endif; // class_exists check

?>