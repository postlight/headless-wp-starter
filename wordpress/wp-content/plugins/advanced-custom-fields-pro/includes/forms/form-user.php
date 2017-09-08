<?php

/*
*  ACF User Form Class
*
*  All the logic for adding fields to users
*
*  @class 		acf_form_user
*  @package		ACF
*  @subpackage	Forms
*/

if( ! class_exists('acf_form_user') ) :

class acf_form_user {
	
	var $form = '#createuser';
	
	
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
		
		// actions
		add_action('admin_enqueue_scripts',			array($this, 'admin_enqueue_scripts'));
		add_action('login_form_register', 			array($this, 'admin_enqueue_scripts'));
		
		// render
		add_action('show_user_profile', 			array($this, 'edit_user'));
		add_action('edit_user_profile',				array($this, 'edit_user'));
		add_action('user_new_form',					array($this, 'user_new_form'));
		add_action('register_form',					array($this, 'register_user'));
		
		// save
		//add_action('edit_user_profile_update',	array($this, 'save_user'));
		//add_action('personal_options_update',		array($this, 'save_user'));
		add_action('user_register',					array($this, 'save_user'));
		add_action('profile_update',				array($this, 'save_user'));
		
	}
	
	
	/*
	*  validate_page
	*
	*  This function will check if the current page is for a post/page edit form
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	N/A
	*  @return	(boolean)
	*/
	
	function validate_page() {
		
		// global
		global $pagenow;
		
		
		// validate page
		if( in_array( $pagenow, array('profile.php', 'user-edit.php', 'user-new.php', 'wp-login.php') ) ) {
			
			return true;
		
		}
		
		
		// return
		return false;
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts() {
		
		// validate page
		if( ! $this->validate_page() ) {
		
			return;
			
		}
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action('acf/input/admin_footer', array($this, 'admin_footer'), 10, 1);
		
	}
	
	
	/*
	*  register_user
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
	
	function register_user() {
		
		// update vars
		$this->form = '#registerform';
		
		
		// render
		$this->render( 0, 'register', 'div' );
		
	}
	
	
	/*
	*  edit_user
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
	
	function edit_user( $user ) {
		
		// update vars
		$this->form = '#your-profile';
		
		
		// render
		$this->render( $user->ID, 'edit', 'tr' );
		
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
	
	function user_new_form() {
		
		// update vars
		$this->form = '#createuser';
		
		
		// render
		$this->render( 0, 'add', 'tr' );
		
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
	
	function render( $user_id, $user_form, $el = 'tr' ) {
		
		// vars
		$post_id = "user_{$user_id}";
		$show_title = true;
		
		
		// show title
		//if( $user_form === 'register' ) $show_title = false;
		
		
		// args
		$args = array(
			'user_id'	=> 'new',
			'user_form'	=> $user_form
		);
		
		if( $user_id ) $args['user_id'] = $user_id;
		
		
		// get field groups
		$field_groups = acf_get_field_groups( $args );
		
		
		// bail early if no field groups
		if( empty($field_groups) ) return;
		
		
		// form data
		acf_form_data(array( 
			'post_id'	=> $post_id, 
			'nonce'		=> 'user' 
		));
		
		
		// loop
		foreach( $field_groups as $field_group ) {
			
			// vars
			$fields = acf_get_fields( $field_group );
			
			
			// title
			if( $show_title && $field_group['style'] === 'default' ) {
				
				echo '<h2>' . $field_group['title'] . '</h2>';
					
			}
			
			
			// table start
			if( $el == 'tr' ) echo '<table class="form-table"><tbody>';
			
			
			// render fields
			acf_render_fields( $post_id, $fields, $el, $field_group['instruction_placement'] );
			
			
			// table end
			if( $el == 'tr' ) echo '</tbody></table>';
			
		}
		
		
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
		
?>
<style type="text/css">

<?php if( is_admin() ): ?>

/* override for user css */
.acf-field input[type="text"],
.acf-field input[type="password"],
.acf-field input[type="number"],
.acf-field input[type="search"],
.acf-field input[type="email"],
.acf-field input[type="url"],
.acf-field select {
    max-width: 25em;
}

.acf-field textarea {
	max-width: 500px;
}


/* allow sub fields to display correctly */
.acf-field .acf-field input[type="text"],
.acf-field .acf-field input[type="password"],
.acf-field .acf-field input[type="number"],
.acf-field .acf-field input[type="search"],
.acf-field .acf-field input[type="email"],
.acf-field .acf-field input[type="url"],
.acf-field .acf-field textarea,
.acf-field .acf-field select {
    max-width: none;
}

<?php else: ?>

#registerform h2 {
	margin: 1em 0;
}

#registerform .acf-field .acf-label {
	margin-bottom: 0;
}

#registerform .acf-field .acf-label label {
	font-weight: normal;
	font-size: 14px;
}

#registerform p.submit {
	text-align: right;
}

<?php endif; ?>

</style>
<script type="text/javascript">
(function($) {
	
	// vars
	var $spinner = $('<?php echo $this->form; ?> p.submit .spinner');
	
	
	// create spinner if not exists (may exist in future WP versions)
	if( !$spinner.exists() ) {
		
		// create spinner (use .acf-spinner becuase .spinner CSS not included on register page)
		$spinner = $('<span class="acf-spinner"></span>');
		
		
		// append
		$('<?php echo $this->form; ?> p.submit').append( $spinner );
		
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
		
		// verify and remove nonce
		if( ! acf_verify_nonce('user') ) {
			
			return $user_id;
		
		}
		
	    
	    // save data
	    if( acf_validate_save_post(true) ) {
	    	
			acf_save_post( "user_{$user_id}" );
		
		}
			
	}
			
}

new acf_form_user();

endif;

?>