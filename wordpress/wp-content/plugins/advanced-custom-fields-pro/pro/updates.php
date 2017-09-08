<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_pro_updates') ) :

class acf_pro_updates {
	

	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// actions
		add_action('init',	array($this, 'init'), 20);
		
	}
	
	
	/*
	*  init
	*
	*  description
	*
	*  @type	function
	*  @date	10/4/17
	*  @since	5.5.10
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function init() {
		
		// bail early if no show_updates
		if( !acf_get_setting('show_updates') ) return;
		
		
		// bail early if not a plugin (included in theme)
		if( !acf_is_plugin_active() ) return;
		
		
		// register update
		acf_register_plugin_update(array(
			'id'		=> 'pro',
			'key'		=> acf_pro_get_license_key(),
			'slug'		=> acf_get_setting('slug'),
			'basename'	=> acf_get_setting('basename'),
			'version'	=> acf_get_setting('version'),
		));
		
		
		// admin
		if( is_admin() ) {
			
			add_action('in_plugin_update_message-' . acf_get_setting('basename'), array($this, 'modify_plugin_update_message'), 10, 2 );
			
		}
		
		
	}
	
	
	/*
	*  modify_plugin_update_message
	*
	*  Displays an update message for plugin list screens.
	*
	*  @type	function
	*  @date	14/06/2016
	*  @since	5.3.8
	*
	*  @param	$message (string)
	*  @param	$plugin_data (array)
	*  @param	$r (object)
	*  @return	$message
	*/
	
	function modify_plugin_update_message( $plugin_data, $response ) {
		
		// bail ealry if has key
		if( acf_pro_get_license_key() ) return;
		
		
		// display message
		echo '<br />' . sprintf( __('To enable updates, please enter your license key on the <a href="%s">Updates</a> page. If you don\'t have a licence key, please see <a href="%s">details & pricing</a>.', 'acf'), admin_url('edit.php?post_type=acf-field-group&page=acf-settings-updates'), 'https://www.advancedcustomfields.com/pro' );
		
	}
	
}


// initialize
new acf_pro_updates();

endif; // class_exists check


/*
*  acf_pro_get_license
*
*  This function will return the license
*
*  @type	function
*  @date	20/09/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_pro_get_license() {
	
	// get option
	$license = get_option('acf_pro_license');
	
	
	// bail early if no value
	if( !$license ) return false;
	
	
	// decode
	$license = maybe_unserialize(base64_decode($license));
	
	
	// bail early if corrupt
	if( !is_array($license) ) return false;
	
	
	// return
	return $license;
	
}


/*
*  acf_pro_get_license_key
*
*  This function will return the license key
*
*  @type	function
*  @date	20/09/2016
*  @since	5.4.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_pro_get_license_key() {
	
	// vars
	$license = acf_pro_get_license();
	$home_url = home_url();
	
	
	// bail early if empty
	if( !$license || !$license['key'] ) return false;
	
	
	// bail early if url has changed
	if( acf_strip_protocol($license['url']) !== acf_strip_protocol($home_url) ) return false;
	
	
	// return
	return $license['key'];
	
}


/*
*  acf_pro_update_license
*
*  This function will update the DB license
*
*  @type	function
*  @date	20/09/2016
*  @since	5.4.0
*
*  @param	$key (string)
*  @return	n/a
*/

function acf_pro_update_license( $key = '' ) {
	
	// vars
	$value = '';
	
	
	// key
	if( $key ) {
		
		// vars
		$data = array(
			'key'	=> $key,
			'url'	=> home_url()
		);
		
		
		// encode
		$value = base64_encode(maybe_serialize($data));
		
	}
	
	
	// re-register update (key has changed)
	acf_register_plugin_update(array(
		'id'		=> 'pro',
		'key'		=> $key,
		'slug'		=> acf_get_setting('slug'),
		'basename'	=> acf_get_setting('basename'),
		'version'	=> acf_get_setting('version'),
	));
	
	
	// update
	return update_option('acf_pro_license', $value);
	
}

?>