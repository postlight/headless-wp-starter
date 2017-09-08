<?php

// global
global $field_group;

?>
<div class="acf-field">
	<div class="acf-label">
		<label><?php _e("Rules",'acf'); ?></label>
		<p class="description"><?php _e("Create a set of rules to determine which edit screens will use these advanced custom fields",'acf'); ?></p>
	</div>
	<div class="acf-input">
		<div class="rule-groups">
			
			<?php foreach( $field_group['location'] as $i => $group ): 
				
				// bail ealry if no group
				if( empty($group) ) return;
				
				
				// view
				acf_get_view('html-location-group', array(
					'group'		=> $group,
					'group_id'	=> "group_{$i}"
				));
			
			endforeach;	?>
			
			<h4><?php _e("or",'acf'); ?></h4>
			
			<a href="#" class="button add-location-group"><?php _e("Add rule group",'acf'); ?></a>
			
		</div>
	</div>
</div>
<script type="text/javascript">
if( typeof acf !== 'undefined' ) {
		
	acf.postbox.render({
		'id': 'acf-field-group-locations',
		'label': 'left'
	});	

}
</script>