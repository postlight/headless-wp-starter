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
<tr class="acf-field acf-field-true-false acf-field-setting-conditional_logic" data-type="true_false" data-name="conditional_logic">
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
			'class'			=> 'conditions-toggle',
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
								'operator'	=>	'',
								'value'		=>	'',
							));
							
							
							// vars		
							// $group_id must be completely different to $rule_id to avoid JS issues
							$rule_id = "rule_{$rule_id}";
							$prefix = "{$field['prefix']}[conditional_logic][{$group_id}][{$rule_id}]";
							
							// data attributes
							$attributes = array(
								'data-id'		=> $rule_id,
								'data-field'	=> $rule['field'],
								'data-operator'	=> $rule['operator'],
								'data-value'	=> $rule['value']
							);
							
							?>
							<tr class="rule" <?php acf_esc_attr_e($attributes); ?>>
								<td class="param">
									<?php 
										
									acf_render_field(array(
										'type'		=> 'select',
										'prefix'	=> $prefix,
										'name'		=> 'field',
										'class'		=> 'condition-rule-field',
										'disabled'	=> $disabled,
										'value'		=> $rule['field'],
										'choices'	=> array(
											$rule['field'] => $rule['field']
										)
									));										
		
									?>
								</td>
								<td class="operator">
									<?php 	
									
									acf_render_field(array(
										'type'		=> 'select',
										'prefix'	=> $prefix,
										'name'		=> 'operator',
										'class'		=> 'condition-rule-operator',
										'disabled'	=> $disabled,
										'value'		=> $rule['operator'],
										'choices'	=> array(
											$rule['operator'] => $rule['operator']
										)
									)); 	
									
									?>
								</td>
								<td class="value">
									<?php 
										
									// create field
									acf_render_field(array(
										'type'		=> 'select',
										'prefix'	=> $prefix,
										'name'		=> 'value',
										'class'		=> 'condition-rule-value',
										'disabled'	=> $disabled,
										'value'		=> $rule['value'],
										'choices'	=> array(
											$rule['value'] => $rule['value']
										)
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