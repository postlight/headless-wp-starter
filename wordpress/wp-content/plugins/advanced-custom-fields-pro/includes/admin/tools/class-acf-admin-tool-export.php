<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('ACF_Admin_Tool_Export') ) :

class ACF_Admin_Tool_Export extends ACF_Admin_Tool {
	
	/** @var string View context */
	var $view = '';
	
	
	/** @var array Export data */
	var $json = '';
	
	
	/**
	*  initialize
	*
	*  This function will initialize the admin tool
	*
	*  @date	10/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'export';
		$this->title = __("Export Field Groups", 'acf');
    	
    	
    	// active
    	if( $this->is_active() ) {
			$this->title .= ' - ' . __('Generate PHP', 'acf');
		}
		
	}
	
	
	/**
	*  submit
	*
	*  This function will run when the tool's form has been submit
	*
	*  @date	10/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function submit() {
		
		// vars
		$action = acf_maybe_get_POST('action');
		
		
		// download
		if( $action === 'download' ) {
			
			$this->submit_download();
		
		// generate	
		} elseif( $action === 'generate' ) {
			
			$this->submit_generate();
			
		}
		
	}
	
	
	/**
	*  submit_download
	*
	*  description
	*
	*  @date	17/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function submit_download() {
		
		// vars
		$json = $this->get_selected();
		
		
		// validate
		if( $json === false ) {
			return acf_add_admin_notice( __("No field groups selected", 'acf') , 'error');
		}
		
		
		// headers
		$file_name = 'acf-export-' . date('Y-m-d') . '.json';
		header( "Content-Description: File Transfer" );
		header( "Content-Disposition: attachment; filename={$file_name}" );
		header( "Content-Type: application/json; charset=utf-8" );
		
		
		// return
		echo acf_json_encode( $json );
		die;
		
	}
	
	
	/**
	*  submit_generate
	*
	*  description
	*
	*  @date	17/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function submit_generate() {
		
		// vars
		$keys = $this->get_selected_keys();
		
		
		// validate
		if( !$keys ) {
			return acf_add_admin_notice( __("No field groups selected", 'acf') , 'error');
		}
		
		
		// url
		$url = add_query_arg( 'keys', implode('+', $keys), $this->get_url() );
		
		
		// redirect
		wp_redirect( $url );
		exit;
		
	}
	
	
	/**
	*  load
	*
	*  description
	*
	*  @date	21/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function load() {
		
		// active
    	if( $this->is_active() ) {
	    	
	    	// get selected keys
	    	$selected = $this->get_selected_keys();
	    	
	    	
	    	// add notice
	    	if( $selected ) {
		    	$count = count($selected);
		    	$message = sprintf( _n( 'Exported 1 field group.', 'Exported %s field groups.', $count, 'acf' ), $count );
		    	acf_add_admin_notice( $message );
	    	}
		}

	}
	
	
	/**
	*  html
	*
	*  This function will output the metabox HTML
	*
	*  @date	10/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function html() {
		
		// single (generate PHP)
		if( $this->is_active() ) {
			
			$this->html_single();
		
		// archive	
		} else {
			
			$this->html_archive();
			
		}
		
	}
	
	
	/**
	*  html_field_selection
	*
	*  description
	*
	*  @date	24/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function html_field_selection() {
		
		// vars
		$choices = array();
		$selected = $this->get_selected_keys();
		$field_groups = acf_get_field_groups();
		
		
		// loop
		if( $field_groups ) {
			foreach( $field_groups as $field_group ) {
				$choices[ $field_group['key'] ] = esc_html( $field_group['title'] );
			}	
		}
		
		
		// render
		acf_render_field_wrap(array(
			'label'		=> __('Select Field Groups', 'acf'),
			'type'		=> 'checkbox',
			'name'		=> 'keys',
			'prefix'	=> false,
			'value'		=> $selected,
			'toggle'	=> true,
			'choices'	=> $choices,
		));
		
	}
	
	
	/**
	*  html_panel_selection
	*
	*  description
	*
	*  @date	21/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function html_panel_selection() {
		
		?>
		<div class="acf-panel acf-panel-selection">
			<h3 class="acf-panel-title"><?php _e('Select Field Groups', 'acf') ?> <i class="dashicons dashicons-arrow-right"></i></h3>
			<div class="acf-panel-inside">
				<?php $this->html_field_selection(); ?>
			</div>
		</div>
		<?php
		
	}
	
	
	/**
	*  html_panel_settings
	*
	*  description
	*
	*  @date	21/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function html_panel_settings() {
		
		?>
		<div class="acf-panel acf-panel-settings">
			<h3 class="acf-panel-title"><?php _e('Settings', 'acf') ?> <i class="dashicons dashicons-arrow-right"></i></h3>
			<div class="acf-panel-inside">
				<?php 
			
/*
				acf_render_field_wrap(array(
					'label'		=> __('Empty settings', 'acf'),
					'type'		=> 'select',
					'name'		=> 'minimal',
					'prefix'	=> false,
					'value'		=> '',
					'choices'	=> array(
						'all'		=> 'Include all settings',
						'minimal'	=> 'Ignore empty settings'
					)
				));
*/
				
				?>
			</div>
		</div>
		<?php
			
	}
	
	
	/**
	*  html_archive
	*
	*  description
	*
	*  @date	20/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function html_archive() {
		
		?>
		<p><?php _e('Select the field groups you would like to export and then select your export method. Use the download button to export to a .json file which you can then import to another ACF installation. Use the generate button to export to PHP code which you can place in your theme.', 'acf'); ?></p>
		<div class="acf-fields">
			<?php $this->html_field_selection(); ?>
		</div>
		<p class="acf-submit">
			<button type="submit" name="action" class="button button-primary" value="download"><?php _e('Export File', 'acf'); ?></button>
			<button type="submit" name="action" class="button" value="generate"><?php _e('Generate PHP', 'acf'); ?></button>
		</p>
		<?php
		
	}
	
	
	/**
	*  html_single
	*
	*  description
	*
	*  @date	20/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function html_single() {
		
		?>
		<div class="acf-postbox-columns">
			<div class="acf-postbox-main">
				<?php $this->html_generate(); ?>
			</div>
			<div class="acf-postbox-side">
				<?php $this->html_panel_selection(); ?>
				<p class="acf-submit">
					<button type="submit" name="action" class="button button-primary" value="generate"><?php _e('Generate PHP', 'acf'); ?></button>
				</p>
			</div>
		</div>
		<?php
		
	}
	
	
	/**
	*  html_generate
	*
	*  description
	*
	*  @date	17/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function html_generate() {
		
		// prevent default translation and fake __() within string
		acf_update_setting('l10n_var_export', true);
		
		
		// vars
		$json = $this->get_selected();
		$str_replace = array(
			"  "			=> "\t",
			"'!!__(!!\'"	=> "__('",
			"!!\', !!\'"	=> "', '",
			"!!\')!!'"		=> "')",
			"array ("		=> "array("
		);
		$preg_replace = array(
			'/([\t\r\n]+?)array/'	=> 'array',
			'/[0-9]+ => array/'		=> 'array'
		);


		?>
		<p><?php _e("The following code can be used to register a local version of the selected field group(s). A local field group can provide many benefits such as faster load times, version control & dynamic fields/settings. Simply copy and paste the following code to your theme's functions.php file or include it within an external file.", 'acf'); ?></p>
		<textarea id="acf-export-textarea" readonly="true"><?php
		
		echo "if( function_exists('acf_add_local_field_group') ):" . "\r\n" . "\r\n";
		
		foreach( $json as $field_group ) {
					
			// code
			$code = var_export($field_group, true);
			
			
			// change double spaces to tabs
			$code = str_replace( array_keys($str_replace), array_values($str_replace), $code );
			
			
			// correctly formats "=> array("
			$code = preg_replace( array_keys($preg_replace), array_values($preg_replace), $code );
			
			
			// esc_textarea
			$code = esc_textarea( $code );
			
			
			// echo
			echo "acf_add_local_field_group({$code});" . "\r\n" . "\r\n";
		
		}
		
		echo "endif;";
		
		?></textarea>
		<p class="acf-submit">
			<a class="button" id="acf-export-copy"><?php _e( 'Copy to clipboard', 'acf' ); ?></a>
		</p>
		<script type="text/javascript">
		(function($){
			
			// vars
			var $a = $('#acf-export-copy');
			var $textarea = $('#acf-export-textarea');
			
			
			// remove $a if 'copy' is not supported
			if( !document.queryCommandSupported('copy') ) {
				return $a.remove();
			}
			
			
			// event
			$a.on('click', function( e ){
				
				// prevent default
				e.preventDefault();
				
				
				// select
				$textarea.get(0).select();
				
				
				// try
				try {
					
					// copy
					var copy = document.execCommand('copy');
					if( !copy ) return;
					
					
					// tooltip
					acf.newTooltip({
						text: 		"<?php _e('Copied', 'acf' ); ?>",
						timeout:	250,
						target: 	$(this),
					});
					
				} catch (err) {
					
					// do nothing
					
				}
						
			});
		
		})(jQuery);
		</script>
		<?php
		
	}
	
	
	
	/**
	*  get_selected_keys
	*
	*  This function will return an array of field group keys that have been selected
	*
	*  @date	20/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function get_selected_keys() {
		
		// check $_POST
		if( $keys = acf_maybe_get_POST('keys') ) {
			return (array) $keys;
		}
		
		
		// check $_GET
		if( $keys = acf_maybe_get_GET('keys') ) {
			$keys = str_replace(' ', '+', $keys);
			return explode('+', $keys);
		}
		
		
		// return
		return false;
		
	}
	
	
	/**
	*  get_selected
	*
	*  This function will return the JSON data for given $_POST args
	*
	*  @date	17/10/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	array
	*/
	
	function get_selected() {
		
		// vars
		$selected = $this->get_selected_keys();
		$json = array();
		
		
		// bail early if no keys
		if( !$selected ) return false;
		
		
		// construct JSON
		foreach( $selected as $key ) {
			
			// load field group
			$field_group = acf_get_field_group( $key );
			
			
			// validate field group
			if( empty($field_group) ) continue;
			
			
			// load fields
			$field_group['fields'] = acf_get_fields( $field_group );
	
	
			// prepare for export
			$field_group = acf_prepare_field_group_for_export( $field_group );
			
			
			// add to json array
			$json[] = $field_group;
			
		}
		
		
		// return
		return $json;
		
	}
}

// initialize
acf_register_admin_tool( 'ACF_Admin_Tool_Export' );

endif; // class_exists check

?>