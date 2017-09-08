<div class="wrap acf-settings-wrap">
	
	<h1><?php _e("Add-ons",'acf'); ?></h1>
	
	<div class="add-ons-list">
		
		<?php if( !empty($json) ): ?>
			
			<?php foreach( $json as $addon ): 
				
				$addon = wp_parse_args($addon, array(
					"title"			=> "",
			        "slug"			=> "",
			        "description"	=> "",
			        "thumbnail"		=> "",
			        "url"			=> "",
			        "btn"			=> __("Download & Install",'acf'),
			        "btn_color"		=> ""
				));
				
				?>
				
				<div class="acf-box add-on add-on-<?php echo $addon['slug']; ?>">
					
					<div class="thumbnail">
						<a target="_blank" href="<?php echo $addon['url']; ?>">
							<img src="<?php echo $addon['thumbnail']; ?>" />
						</a>
					</div>
					<div class="inner">
						<h3><a target="_blank" href="<?php echo $addon['url']; ?>"><?php echo $addon['title']; ?></a></h3>
						<p><?php echo $addon['description']; ?></p>
					</div>
					<div class="footer">
						<?php if( apply_filters("acf/is_add_on_active/slug={$addon['slug']}", false ) ): ?>
							<a class="button" disabled="disabled"><?php _e("Installed",'acf'); ?></a>
						<?php else: ?>
							<a class="button <?php echo $addon['btn_color']; ?>" target="_blank" href="<?php echo $addon['url']; ?>" ><?php _e($addon['btn']); ?></a>
						<?php endif; ?>
						
						<?php if( !empty($addon['footer']) ): ?>
							<p><?php echo $addon['footer']; ?></p>
						<?php endif; ?>
					</div>
					
				</div>
				
			<?php endforeach; ?>
			
		<?php endif; ?>
		
	</div>
	
</div>