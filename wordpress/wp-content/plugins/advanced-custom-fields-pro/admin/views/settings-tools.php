<?php 

// vars
$field = array(
	'label'		=> __('Select Field Groups', 'acf'),
	'type'		=> 'checkbox',
	'name'		=> 'acf_export_keys',
	'prefix'	=> false,
	'value'		=> false,
	'toggle'	=> true,
	'choices'	=> array(),
);

$field_groups = acf_get_field_groups();


// populate choices
if( $field_groups ) {
	
	foreach( $field_groups as $field_group ) {
		
		$field['choices'][ $field_group['key'] ] = $field_group['title'];
		
	}
	
}

?>
<div class="wrap acf-settings-wrap">
	
	<h1><?php _e('Tools', 'acf'); ?></h1>
	
	<div class="acf-box" id="acf-export-field-groups">
		<div class="title">
			<h3><?php _e('Export Field Groups', 'acf'); ?></h3>
		</div>
		<div class="inner">
			<p><?php _e('Select the field groups you would like to export and then select your export method. Use the download button to export to a .json file which you can then import to another ACF installation. Use the generate button to export to PHP code which you can place in your theme.', 'acf'); ?></p>
			
			<form method="post" action="">
			<div class="acf-hidden">
				<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( 'export' ); ?>" />
			</div>
			<table class="form-table">
                <tbody>
	                <?php acf_render_field_wrap( $field, 'tr' ); ?>
					<tr>
						<th></th>
						<td>
							<input type="submit" name="download" class="button button-primary" value="<?php _e('Download export file', 'acf'); ?>" />
							<input type="submit" name="generate" class="button button-primary" value="<?php _e('Generate export code', 'acf'); ?>" />
						</td>
					</tr>
				</tbody>
			</table>
			</form>
            
		</div>
	</div>

	
	<div class="acf-box">
		<div class="title">
			<h3><?php _e('Import Field Groups', 'acf'); ?></h3>
		</div>
		<div class="inner">
			<p><?php _e('Select the Advanced Custom Fields JSON file you would like to import. When you click the import button below, ACF will import the field groups.', 'acf'); ?></p>
			
			<form method="post" action="" enctype="multipart/form-data">
			<div class="acf-hidden">
				<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( 'import' ); ?>" />
			</div>
			<table class="form-table">
                <tbody>
                	<tr>
                    	<th>
                    		<label><?php _e('Select File', 'acf'); ?></label>
                    	</th>
						<td>
							<input type="file" name="acf_import_file">
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<input type="submit" class="button button-primary" value="<?php _e('Import', 'acf'); ?>" />
						</td>
					</tr>
				</tbody>
			</table>
			</form>
			
		</div>
		
		
	</div>
	
</div>