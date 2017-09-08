<div class="rule-group" data-id="<?php echo $group_id; ?>">

	<h4><?php echo ($group_id == 'group_0') ? __("Show this field group if",'acf') : __("or",'acf'); ?></h4>
	
	<table class="acf-table -clear">
		<tbody>
			<?php foreach( $group as $i => $rule ):
				
				// append id
				$rule['id'] = "rule_{$i}";
				$rule['group'] = $group_id;
				
				
				// valid rule
				$rule = acf_get_valid_location_rule($rule);
				
				
				// view
				acf_get_view('html-location-rule', array(
					'rule'	=> $rule
				));
				
			 endforeach; ?>
		</tbody>
	</table>
	
</div>