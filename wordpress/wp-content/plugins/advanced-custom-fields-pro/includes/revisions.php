<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_revisions') ) :

class acf_revisions {
	
	// vars
	var $cache = array();
	
	
	/*
	*  __construct
	*
	*  A good place to add actions / filters
	*
	*  @type	function
	*  @date	11/08/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		// actions	
		add_action('wp_restore_post_revision', array($this, 'wp_restore_post_revision'), 10, 2 );
		
		
		// filters
		add_filter('wp_save_post_revision_check_for_changes', array($this, 'wp_save_post_revision_check_for_changes'), 10, 3);
		add_filter('_wp_post_revision_fields', array($this, 'wp_preview_post_fields'), 10, 2 );
		add_filter('_wp_post_revision_fields', array($this, 'wp_post_revision_fields'), 10, 2 );
		add_filter('acf/validate_post_id', array($this, 'acf_validate_post_id'), 10, 2 );
		
	}
	
	
	/*
	*  wp_preview_post_fields
	*
	*  This function is used to trick WP into thinking that one of the $post's fields has changed and
	*  will allow an autosave to be updated. 
	*  Fixes an odd bug causing the preview page to render the non autosave post data on every odd attempt
	*
	*  @type	function
	*  @date	21/10/2014
	*  @since	5.1.0
	*
	*  @param	$fields (array)
	*  @return	$fields
	*/
	
	function wp_preview_post_fields( $fields ) {
		
		// bail early if not previewing a post
		if( acf_maybe_get_POST('wp-preview') !== 'dopreview' ) return $fields;
		
		
		// add to fields if ACF has changed
		if( acf_maybe_get_POST('_acf_changed') ) {
			
			$fields['_acf_changed'] = 'different than 1';
			
		}
		
		
		// return
		return $fields;
		
	}
	
	
	/*
	*  wp_save_post_revision_check_for_changes
	*
	*  This filter will return false and force WP to save a revision. This is required due to
	*  WP checking only post_title, post_excerpt and post_content values, not custom fields.
	*
	*  @type	filter
	*  @date	19/09/13
	*
	*  @param	$return (boolean) defaults to true
	*  @param	$last_revision (object) the last revision that WP will compare against
	*  @param	$post (object) the $post that WP will compare against
	*  @return	$return (boolean)
	*/
	
	function wp_save_post_revision_check_for_changes( $return, $last_revision, $post ) {
		
		// if acf has changed, return false and prevent WP from performing 'compare' logic
		if( acf_maybe_get_POST('_acf_changed') ) return false;
		
		
		// return
		return $return;
		
	}
	
	
	/*
	*  wp_post_revision_fields
	*
	*  This filter will add the ACF fields to the returned array
	*  Versions 3.5 and 3.6 of WP feature different uses of the revisions filters, so there are
	*  some hacks to allow both versions to work correctly
	*
	*  @type	filter
	*  @date	11/08/13
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		
	function wp_post_revision_fields( $fields, $post = null ) {
		
		// validate page
		if( acf_is_screen('revision') || acf_is_ajax('get-revision-diffs') ) {
			
			// bail early if is restoring
			if( acf_maybe_get_GET('action') === 'restore' ) return $fields;
			
			// allow
			
		} else {
			
			// bail early (most likely saving a post)
			return $fields;
			
		}
		
		
		// vars
		$append = array();
		$order = array();
		$post_id = acf_maybe_get($post, 'ID');
		
		
		// compatibility with WP < 4.5 (test)
		if( !$post_id ) {
			
			global $post;
			$post_id = $post->ID;
			
		}
		
		
		// get all postmeta
		$meta = get_post_meta( $post_id );
		
		
		// bail early if no meta
		if( !$meta ) return $fields;
		
		
		// loop
		foreach( $meta as $name => $value ) {
			
			// attempt to find key value
			$key = acf_maybe_get( $meta, '_'.$name );
			
			
			// bail ealry if no key
			if( !$key ) continue;
			
			
			// update vars
			$value = $value[0];
			$key = $key[0];
			
					
			// bail early if $key is a not a field_key
			if( !acf_is_field_key($key) ) continue;
			
			
			// get field
			$field = acf_get_field( $key );
			$field_title = $field['label'] . ' (' . $name . ')';
			$field_order = $field['menu_order'];
			$ancestors = acf_get_field_ancestors( $field );
			
			
			// ancestors
			if( !empty($ancestors) ) {
				
				// vars
				$count = count($ancestors);
				$oldest = acf_get_field( $ancestors[$count-1] );
				
				
				// update vars
				$field_title = str_repeat('- ', $count) . $field_title;
				$field_order = $oldest['menu_order'] . '.1';
				
			}
			
			
			// append
			$append[ $name ] = $field_title;
			$order[ $name ] = $field_order;
			
			
			// hook into specific revision field filter and return local value
			add_filter("_wp_post_revision_field_{$name}", array($this, 'wp_post_revision_field'), 10, 4);
			
		}
		
		
		// append 
		if( !empty($append) ) {
			
			// vars
			$prefix = '_';
			
			
			// add prefix
			$append = acf_add_array_key_prefix($append, $prefix);
			$order = acf_add_array_key_prefix($order, $prefix);
			
			
			// sort by name (orders sub field values correctly)
			array_multisort($order, $append);
			
			
			// remove prefix
			$append = acf_remove_array_key_prefix($append, $prefix);
			
			
			// append
			$fields = $fields + $append;
			
		}
		
		
		// return
		return $fields;
	
	}
	
	
	/*
	*  wp_post_revision_field
	*
	*  This filter will load the value for the given field and return it for rendering
	*
	*  @type	filter
	*  @date	11/08/13
	*
	*  @param	$value (mixed) should be false as it has not yet been loaded
	*  @param	$field_name (string) The name of the field
	*  @param	$post (mixed) Holds the $post object to load from - in WP 3.5, this is not passed!
	*  @param	$direction (string) to / from - not used
	*  @return	$value (string)
	*/
	
	function wp_post_revision_field( $value, $field_name, $post = null, $direction = false) {
		
		// bail ealry if is empty
		if( empty($value) ) return $value;
		
		
		// value has not yet been 'maybe_unserialize'
		$value = maybe_unserialize( $value );
		
		
		// vars
		$post_id = $post->ID;
		
		
		// load field
		$field = acf_maybe_get_field( $field_name, $post_id );
		
		
		// default formatting
		if( is_array($value) ) {
			
			$value = implode(', ', $value);
			
		} elseif( is_object($value) ) {
			
			$value = serialize($value);
			
		}
		
		
		// image
		if( $field['type'] == 'image' || $field['type'] == 'file' ) {
			
			$url = wp_get_attachment_url($value);
			$value = $value . ' (' . $url . ')';
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  wp_restore_post_revision
	*
	*  This action will copy and paste the metadata from a revision to the post
	*
	*  @type	action
	*  @date	11/08/13
	*
	*  @param	$parent_id (int) the destination post
	*  @return	$revision_id (int) the source post
	*/
	
	function wp_restore_post_revision( $post_id, $revision_id ) {
		
		// copy postmeta from revision to post (restore from revision)
		acf_copy_postmeta( $revision_id, $post_id );
		
		
		// Make sure the latest revision is also updated to match the new $post data
		// get latest revision
		$revision = acf_get_post_latest_revision( $post_id );
		
		
		// save
		if( $revision ) {
			
			// copy postmeta from revision to latest revision (potentialy may be the same, but most likely are different)
			acf_copy_postmeta( $revision_id, $revision->ID );
			
		}
			
	}
	
	
	/*
	*  acf_validate_post_id
	*
	*  This function will modify the $post_id and allow loading values from a revision
	*
	*  @type	function
	*  @date	6/3/17
	*  @since	5.5.10
	*
	*  @param	$post_id (int)
	*  @param	$_post_id (int)
	*  @return	$post_id (int)
	*/
	
	function acf_validate_post_id( $post_id, $_post_id ) {
		
		// bail early if no preview in URL
		if( !isset($_GET['preview']) ) return $post_id;
		
		
		// bail early if $post_id is not numeric
		if( !is_numeric($post_id) ) return $post_id;
		
		
		// vars
		$k = $post_id;
		$preview_id = 0;
		
		
		// check cache
		if( isset($this->cache[$k]) ) return $this->cache[$k];
		
		
		// validate
		if( isset($_GET['preview_id']) ) {
		
			$preview_id = (int) $_GET['preview_id'];
			
		} elseif( isset($_GET['p']) ) {
			
			$preview_id = (int) $_GET['p'];
			
		} elseif( isset($_GET['page_id']) ) {
			
			$preview_id = (int) $_GET['page_id'];
			
		}
		
		
		// bail early id $preview_id does not match $post_id
		if( $preview_id != $post_id ) return $post_id;
		
		
		// attempt find revision
		$revision = acf_get_post_latest_revision( $post_id );
		
		
		// save
		if( $revision && $revision->post_parent == $post_id) {
			
			$post_id = (int) $revision->ID;
			
		}
		
		
		// set cache
		$this->cache[$k] = $post_id;
		
		
		// return
		return $post_id;
		
	}
			
}

// initialize
acf()->revisions = new acf_revisions();

endif; // class_exists check


/*
*  acf_save_post_revision
*
*  This function will copy meta from a post to it's latest revision
*
*  @type	function
*  @date	26/09/2016
*  @since	5.4.0
*
*  @param	$post_id (int)
*  @return	n/a
*/

function acf_save_post_revision( $post_id = 0 ) {
	
	// get latest revision
	$revision = acf_get_post_latest_revision( $post_id );
	
	
	// save
	if( $revision ) {
		
		acf_copy_postmeta( $post_id, $revision->ID );
		
	}
	
}


/*
*  acf_get_post_latest_revision
*
*  This function will return the latest revision for a given post
*
*  @type	function
*  @date	25/06/2016
*  @since	5.3.8
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_post_latest_revision( $post_id ) {
	
	// vars
	$revisions = wp_get_post_revisions( $post_id );
	
	
	// shift off and return first revision (will return null if no revisions)
	$revision = array_shift($revisions);
	
	
	// return
	return $revision;		
	
}


?>