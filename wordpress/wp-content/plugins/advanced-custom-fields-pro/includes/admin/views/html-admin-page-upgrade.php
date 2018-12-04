<?php

/**
*  Admin Database Upgrade
*
*  Shows the databse upgrade process. 
*
*  @date	24/8/18
*  @since	5.7.4
*  @param	void
*/

?>
<style type="text/css">
	
	/* hide steps */
	.step-1,
	.step-2,
	.step-3 {
		display: none;
	}		
	
</style>
<div id="acf-upgrade-wrap" class="wrap">
	
	<h1><?php _e("Upgrade Database", 'acf'); ?></h1>
	
<?php if( acf_has_upgrade() ): ?>

	<p><?php _e('Reading upgrade tasks...', 'acf'); ?></p>
	<p class="step-1"><i class="acf-loading"></i> <?php printf(__('Upgrading data to version %s', 'acf'), ACF_VERSION); ?></p>
	<p class="step-2"></p>
	<p class="step-3"><?php echo sprintf( __('Database upgrade complete. <a href="%s">See what\'s new</a>', 'acf' ), admin_url('edit.php?post_type=acf-field-group&page=acf-settings-info') ); ?></p>
	
	<script type="text/javascript">
	(function($) {
		
		var upgrader = new acf.Model({
			initialize: function(){
				
				// allow user to read message for 1 second
				this.setTimeout( this.upgrade, 1000 );
			},
			upgrade: function(){
				
				// show step 1
				$('.step-1').show();
				
				// vars
				var response = '';
				var success = false;
				
				// send ajax request to upgrade DB
			    $.ajax({
			    	url: acf.get('ajaxurl'),
					dataType: 'json',
					type: 'post',
					data: acf.prepareForAjax({
						action: 'acf/ajax/upgrade'
					}),
					success: function( json ){
						
						// success
						if( acf.isAjaxSuccess(json) ) {
							
							// update
							success = true;
							
							// set response text
							if( jsonText = acf.getAjaxMessage(json) ) {
								response = jsonText;
							}
						
						// error
						} else {
							
							// set response text
							response = '<?php _e('Upgrade failed.', 'acf'); ?>';
							if( jsonText = acf.getAjaxError(json) ) {
								response += ' <pre>' + jsonText +  '</pre>';
							}
						}			
					},
					error: function( jqXHR, textStatus, errorThrown ){
						
						// set response text
						response = '<?php _e('Upgrade failed.', 'acf'); ?>';
						if( errorThrown) {
							response += ' <pre>' + errorThrown +  '</pre>';
						}
					},
					complete: this.proxy(function(){
						
						// remove spinner
						$('.acf-loading').hide();
						
						// display response
						if( response ) {
							$('.step-2').show().html( response );
						}
						
						// display success
						if( success ) {
							$('.step-3').show();
						}
					})
				});
			}
		});
				
	})(jQuery);	
	</script>

<?php else: ?>

	<p><?php _e('No updates available.', 'acf'); ?></p>
	
<?php endif; ?>
</div>