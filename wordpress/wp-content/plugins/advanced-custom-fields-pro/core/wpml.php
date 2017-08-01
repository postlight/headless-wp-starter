<?php 

class acf_wpml_compatibility {
	
	var $lang = '';
	
	
	/*
	*  Constructor
	*
	*  This function will construct all the neccessary actions and filters
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		// global
		global $sitepress;
		
		
		// vars
		$this->lang = ICL_LANGUAGE_CODE;
		
		
		// check for custom lang
		if( isset($_POST['_acflang']) ) $this->lang = $_POST['_acflang'];
		
		
		// update settings
		acf_update_setting('default_language', $sitepress->get_default_language());
		acf_update_setting('current_language', $this->lang);
		
		
		// actions
		add_action('acf/verify_ajax',					array($this, 'verify_ajax'));
		add_action('acf/input/admin_footer',			array($this, 'admin_footer'));
		
		
		// bail early if not transaltable
		if( !$this->is_translatable() ) return;
		
		
		// actions
		add_action('acf/update_500',					array($this, 'update_500'), 10);
		add_action('acf/update_500_field_group',		array($this, 'update_500_field_group'), 10, 2);
		add_action('acf/update_field_group',			array($this, 'update_field_group'), 2, 1);
		add_action('icl_make_duplicate',				array($this, 'icl_make_duplicate'), 10, 4);
		add_action('acf/input/form_data',				array($this, 'acf_input_form_data'), 10, 1);
		
		
		// filters
		add_filter('acf/settings/save_json',			array($this, 'settings_save_json'));
		add_filter('acf/settings/load_json',			array($this, 'settings_load_json'));
		
	}
	
	
	/*
	*  is_translatable
	*
	*  This fucntion will return true if the acf-field-group post type is translatable
	*
	*  @type	function
	*  @date	10/04/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function is_translatable() {
		
		// global
		global $sitepress, $sitepress_settings;
		
		
		// vars
		$post_types = acf_maybe_get($sitepress_settings, 'custom_posts_sync_option', array());
		
		
		// return true if acf-field-group is translatable
		if( !empty($post_types['acf-field-group']) ) {
			
			return true;
			
		}
		
		
		// return true if acf is translatable, and acf-field-group does not yet exist
		if( !empty($post_types['acf']) && !isset($post_types['acf-field-group']) ) {
			
			return true;
			
		}
		
		
		// return
		return false;
		
	}
	
	
	/*
	*  update_500
	*
	*  This function will update the WPML settings to allow 'acf-field-group' to be translatable
	*
	*  @type	function
	*  @date	10/04/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function update_500() {
		
		// global
		global $sitepress, $sitepress_settings;
		
		
		// vars
		$icl_settings = array();
		$post_types = $sitepress_settings['custom_posts_sync_option'];
		
		
		// post type has changed from 'acf' to 'acf-field-group'
		if( !empty($post_types['acf']) ) {
			
			$post_types['acf-field-group'] = 1;
			
		}
		
		
		// add to icl settings
		$icl_settings['custom_posts_sync_option'] = $post_types;
		
		
		// save
		$sitepress->save_settings( $icl_settings );
		
	}
	
	
	/*
	*  update_500_field_group
	*
	*  This function will update the icl_translations table data when creating the fiedl groups
	*
	*  @type	function
	*  @date	10/04/2015
	*  @since	5.2.3
	*
	*  @param	$field_group (array)
	*  @return	n/a
	*/
	
	function update_500_field_group($field_group, $ofg) {
		
		// global
		global $wpdb, $sitepress;
		
		
		// get translation rows (old acf4 and new acf5)
		$old_row = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}icl_translations WHERE element_type=%s AND element_id=%d", 
			'post_acf', $ofg->ID
		), ARRAY_A);
		
		$new_row = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}icl_translations WHERE element_type=%s AND element_id=%d", 
			'post_acf-field-group', $field_group['ID']
		), ARRAY_A);
		
		
		// bail ealry if no rows
		if( !$old_row || !$new_row ) {
			
			return;
			
		}
		
		
		// create reference of old trid to new trid
		// trid is a simple int used to find associated objects
		if( empty($this->trid_ref) ) {
			
			$this->trid_ref = array();
			
		}
		
		
		// update trid
		if( isset($this->trid_ref[ $old_row['trid'] ]) ) {
			
			// this field group is a translation of another, update it's trid to match the previously inserted group
			$new_row['trid'] = $this->trid_ref[ $old_row['trid'] ];
			
		} else {
			
			// this field group is the first of it's translations, update the reference for future groups
			$this->trid_ref[ $old_row['trid'] ] = $new_row['trid'];
			
		}
		
		
		// update icl_translations
		// Row is created by WPML, and much easier to tweak it here due to the very complicated and nonsensical WPML logic
		$table = "{$wpdb->prefix}icl_translations";
		$data = array( 'trid' => $new_row['trid'], 'language_code' => $old_row['language_code'] );
		$where = array( 'translation_id' => $new_row['translation_id'] );
		$data_format = array( '%d', '%s' );
		$where_format = array( '%d' );
		
		
		// allow source_language_code to equal NULL
		if( $old_row['source_language_code'] ) {
			
			$data['source_language_code'] = $old_row['source_language_code'];
			$data_format[] = '%s';
			
		}
		
		
		// update wpdb
		$result = $wpdb->update( $table, $data, $where, $data_format, $where_format );
		
	}
	
	
	/*
	*  update_field_group
	*
	*  This function will update the lang when saving a field group
	*
	*  @type	function
	*  @date	10/03/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	n/a
	*/
	
	function update_field_group( $field_group ) {
		
		global $sitepress;
		
		$this->lang = $sitepress->get_language_for_element($field_group['ID'], 'post_acf-field-group');
		
	}

	
	/*
	*  settings_save_json
	*
	*  This function is hooked into the acf/update_field_group action and will save all field group data to a .json file 
	*
	*  @type	function
	*  @date	19/05/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function settings_save_json( $path ) {	
		
		// bail early if dir does not exist
		if( !is_writable($path) ) {
			
			return $path;
			
		}
		
		
		// remove trailing slash
		$path = untrailingslashit( $path );

			
		// ammend
		$path = $path . '/' . $this->lang;
		
		
		// make dir if does not exist
		if( !file_exists($path) ) {
			
			mkdir($path, 0777, true);
			
		}
		
		
		// return
		return $path;
		
	}
	
	
	/*
	*  settings_load_json
	*
	*  description
	*
	*  @type	function
	*  @date	19/05/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function settings_load_json( $paths ) {
		
		if( !empty($paths) ) {
			
			foreach( $paths as $i => $path ) {
				
				// remove trailing slash
				$path = untrailingslashit( $path );
				
				
				// ammend
				$paths[ $i ] = $path . '/' . $this->lang;
			
			}
		}
		
		
		// return
		return $paths;
		
	}
	
	
	
	/*
	*  icl_make_duplicate
	*
	*  description
	*
	*  @type	function
	*  @date	26/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function icl_make_duplicate( $master_post_id, $lang, $postarr, $id ) {
		
		// validate
		if( $postarr['post_type'] != 'acf-field-group' ) {
		
			return;
			
		}
		
		
		// duplicate field group
		acf_duplicate_field_group( $master_post_id, $id );
		
		
		// always translate independately to avoid many many bugs!
		// - translation post gets a new key (post_name) when origional post is saved
		// - local json creates new files due to changed key
		global $iclTranslationManagement;
		
		$iclTranslationManagement->reset_duplicate_flag( $id );

	}
	
	
	/*
	*  admin_footer
	*
	*  description
	*
	*  @type	function
	*  @date	27/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_footer() {
		
		?>
		<script type="text/javascript">
		(function($) {
			
			// add filter
			acf.add_filter('prepare_for_ajax', function( args ){
				
				// append
				args.lang = '<?php echo $this->lang; ?>';
				
				
				// return
				return args;
				
			});
			
		})(jQuery);	
		</script>
		<?php
		
	}
	
	
	/*
	*  verify_ajax
	*
	*  This function will help avoid WPML conflicts when performing an ACF ajax request
	*
	*  @type	function
	*  @date	7/08/2015
	*  @since	5.2.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function verify_ajax() {
		
		// globals
		global $sitepress;
		
		
		// vars
		$lang = acf_maybe_get($_POST, 'lang');
		
		
		// bail early if no lang
		if( !$lang ) return;
		
		
		// switch lang
		// this will allow get_posts to work as expected (load posts from the correct language)
		$sitepress->switch_lang( $_REQUEST['lang'] );
			
		
		// remove post_id
		// this will prevent WPML from setting the current language based on the current post being edited
		// in theory, WPML is correct, however, when adding a new post, the post's lang is not found and will default to 'en'
		unset( $_REQUEST['post_id'] );
		
	}
	
	
	/*
	*  acf_input_form_data
	*
	*  description
	*
	*  @type	function
	*  @date	16/12/16
	*  @since	5.5.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function acf_input_form_data( $data ) {
		
		// bail early if not options
		if( $data['nonce'] !== 'options' ) return;
		
		
		// add hidden input
		acf_hidden_input(array('name' => '_acflang', 'value' => $this->lang));
		
	}
	
}

new acf_wpml_compatibility();

?>