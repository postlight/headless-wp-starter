<?php 

// vars
$button = __('Upgrade Sites');

?>
<div id="acf-upgrade-wrap" class="wrap">
	
	<h1><?php _e("Advanced Custom Fields Database Upgrade",'acf'); ?></h1>
	
	<p><?php echo sprintf( __("The following sites require a DB upgrade. Check the ones you want to update and then click %s.", 'acf'), '"' . $button . '"'); ?></p>
	
	<p><input type="submit" name="upgrade" value="<?php echo $button; ?>" class="button" id="upgrade-sites"></p>
	
	<table class="wp-list-table widefat">
		
		<thead>
			<tr>
				<td class="manage-column check-column" scope="col"><input type="checkbox" id="sites-select-all"></td>
				<th class="manage-column" scope="col" style="width:33%;"><label for="sites-select-all"><?php _e("Site", 'acf'); ?></label></th>
				<th><?php _e("Description", 'acf'); ?></th>
			</tr>
		</thead>
	
		<tfoot>
		<tr>
			<td class="manage-column check-column" scope="col"><input type="checkbox" id="sites-select-all-2"></td>
			<th class="manage-column" scope="col"><label for="sites-select-all-2"><?php _e("Site", 'acf'); ?></label></th>
			<th><?php _e("Description", 'acf'); ?></th>
		</tr>
		</tfoot>
		
		<tbody id="the-list">
		
		<?php foreach( $sites as $i => $site ): ?>
			
			<tr<?php if( $i % 2 == 0 ): ?> class="alternate"<?php endif; ?>>
				<th class="check-column" scope="row">
				<?php if( $site['updates'] ): ?>
					<input type="checkbox" value="<?php echo $site['blog_id']; ?>" name="checked[]">
				<?php endif; ?>
				</th>
				<td>
					<strong><?php echo $site['name']; ?></strong><br /><?php echo $site['url']; ?>
				</td>
				<td>
				<?php if( $site['updates'] ): ?>
					<span class="response"><?php printf(__('Site requires database upgrade from %s to %s', 'acf'), $site['acf_version'], $plugin_version); ?></span>
				<?php else: ?>
					<?php _e("Site is up to date", 'acf'); ?>
				<?php endif; ?>
				</td>
			</tr>
			
		<?php endforeach; ?>
		
		</tbody>
		
	</table>
	
	<p><input type="submit" name="upgrade" value="<?php echo $button; ?>" class="button" id="upgrade-sites-2"></p>
	
	<p class="show-on-complete"><?php echo sprintf( __('Database Upgrade complete. <a href="%s">Return to network dashboard</a>', 'acf'), network_admin_url() ); ?></p>
	
	<style type="text/css">
		
		/* hide show */
		.show-on-complete {
			display: none;
		}
		
	</style>
	
	<script type="text/javascript">
	(function($) {
		
		var upgrader = {
			
			$buttons: null,
			
			$inputs: null,
			i: 0,
			
			init : function(){
				
				// reference
				var self = this;
				
				
				// vars
				this.$buttons = $('#upgrade-sites, #upgrade-sites-2');
				
				
				// events
				this.$buttons.on('click', function( e ){
					
					// prevent default
					e.preventDefault();
					
					
					// confirm
					var answer = confirm("<?php _e('It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'acf'); ?>");
					
					
					// bail early if no confirm
					if( !answer ) {
						
						return;
						
					}
					
					
					// populate inputs
					self.$inputs = $('#the-list input:checked');
					
					
					// upgrade
					self.upgrade();
					
				});
				
				
				// return
				return this;
				
			},
			
			upgrade: function(){
				
				// reference
				var self = this;
				
				
				// bail early if no sites
				if( !this.$inputs.length ) {
					
					return;
					
				}
				
				
				// complete
				if( this.i >= this.$inputs.length ) {
					
					this.complete();
					return;
					
				}
				
				
				// disable buttons
				this.$buttons.attr('disabled', 'disabled');
				
				
				// vars
				var $input = this.$inputs.eq( this.i ),
					$tr = $input.closest('tr'),
					text = '<?php _e('Upgrade complete', 'acf'); ?>';
				
				
				// add loading
				$tr.find('.response').html('<i class="acf-loading"></i></span> <?php printf(__('Upgrading data to version %s', 'acf'), $plugin_version); ?>');
				
				
				// get results
			    var xhr = $.ajax({
			    	url:		'<?php echo admin_url('admin-ajax.php'); ?>',
					dataType:	'json',
					type:		'post',
					data:		{
						action:		'acf/admin/db_update',
						nonce:		'<?php echo wp_create_nonce('acf_db_update'); ?>',
						blog_id:	$input.val(),
					},
					success: function( json ){
						
						// remove input
						$input.prop('checked', false);
						$input.remove();
						
						// vars
						var response = '';
						
						// success
						if( acf.isAjaxSuccess(json) ) {
							response = acf.getAjaxMessage(json);
						} else {
							response = acf.getAjaxError(json);
						}
						
						// update text
						if( response ) {
							text = '<pre>' + response +  '</pre>';
						}						
					},
					complete: function(){
						
						$tr.find('.response').html( text );
						
						
						// upgrade next site
						self.next();
						
					}
				});
				
			},
			
			next: function(){
				
				this.i++;
						
				this.upgrade();
				
			},
			
			complete: function(){
				
				// enable buttons
				this.$buttons.removeAttr('disabled');
				
				
				// show message
				$('.show-on-complete').show();
				
			}
			
		}.init();
		
	})(jQuery);	
	</script>
	
</div>