<?php 

// vars
$disabled = false;


// empty
if( empty($field['conditional_logic']) ) {
	
	$disabled = true;
	$field['conditional_logic'] = array(
		
		// group 0
		array(
			
			// rule 0
			array()
		
		)
		
	);
	
}

?>
<tr class="acf-field acf-field-true-false acf-field-setting-conditional_logic" data_type="true_false" data-name="conditional_logic">
	<td class="acf-label">
		<label><?php _e("Conditional Logic",'acf'); ?></label>
	</td>
	<td class="acf-input">
		<?php 
		
		acf_render_field(array(
			'type'			=> 'true_false',
			'name'			=> 'conditional_logic',
			'prefix'		=> $field['prefix'],
			'value'			=> $disabled ? 0 : 1,
			'ui'			=> 1,
			'class'			=> 'conditional-toggle',
		));
		
		?>
		<div class="rule-groups" <?php if($disabled): ?>style="display:none;"<?php endif; ?>>
			
			<?php foreach( $field['conditional_logic'] as $group_id => $group ): 
				
				// validate
				if( empty($group) ) continue;
				
				
				// vars
				// $group_id must be completely different to $rule_id to avoid JS issues
				$group_id = "group_{$group_id}";
				$h4 = ($group_id == "group_0") ? __("Show this field if",'acf') : __("or",'acf');
				
				?>
				<div class="rule-group" data-id="<?php echo $group_id; ?>">
				
					<h4><?php echo $h4; ?></h4>
					
					<table class="acf-table -clear">
						<tbody>
						<?php foreach( $group as $rule_id => $rule ): 
							
							// valid rule
							$rule = wp_parse_args( $rule, array(
								'field'		=>	'',
								'operator'	=>	'==',
								'value'		=>	'',
							));
							
							
							// vars		
							// $group_id must be completely different to $rule_id to avoid JS issues
							$rule_id = "rule_{$rule_id}";
							$prefix = "{$field['prefix']}[conditional_logic][{$group_id}][{$rule_id}]";
							
							?>
							<tr class="rule" data-id="<?php echo $rule_id; ?>">
								<td class="param">
									<?php 
									
									$choices = array();
									$choices[ $rule['field'] ] = $rule['field'];
									
									// create field
									acf_render_field(array(
										'type'		=> 'select',
										'prefix'	=> $prefix,
										'name'		=> 'field',
										'value'		=> $rule['field'],
										'choices'	=> $choices,
										'class'		=> 'conditional-rule-param',
										'disabled'	=> $disabled,
									));										
		
									?>
								</td>
								<td class="operator">
									<?php 	
									
									$choices = array(
										'=='	=>	__("is equal to",'acf'),
										'!='	=>	__("is not equal to",'acf'),
									);
									
									
									// create field
									acf_render_field(array(
										'type'		=> 'select',
										'prefix'	=> $prefix,
										'name'		=> 'operator',
										'value'		=> $rule['operator'],
										'choices' 	=> $choices,
										'class'		=> 'conditional-rule-operator',
										'disabled'	=> $disabled,
									)); 	
									
									?>
								</td>
								<td class="value">
									<?php 
									
									$choices = array();
									$choices[ $rule['value'] ] = $rule['value'];
									
									// create field
									acf_render_field(array(
										'type'		=> 'select',
										'prefix'	=> $prefix,
										'name'		=> 'value',
										'value'		=> $rule['value'],
										'choices'	=> $choices,
										'class'		=> 'conditional-rule-value',
										'disabled'	=> $disabled,
									));
									
									?>
								</td>
								<td class="add">
									<a href="#" class="button add-conditional-rule"><?php _e("and",'acf'); ?></a>
								</td>
								<td class="remove">
									<a href="#" class="acf-icon -minus remove-conditional-rule"></a>
								</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					
				</div>
			<?php endforeach; ?>
			
			<h4><?php _e("or",'acf'); ?></h4>
			
			<a href="#" class="button add-conditional-group"><?php _e("Add rule group",'acf'); ?></a>
			
		</div>
		
	</td>
</tr>