<?php
$required = $GLOBALS['wpmdb_meta']['wp-migrate-db-pro-cli']['required-php-version'];  
$wpmdb_basename = sprintf( '%s/%s.php', $GLOBALS['wpmdb_meta']['wp-migrate-db-pro-cli']['folder'], 'wp-migrate-db-pro-cli' );
$deactivate = wp_nonce_url( network_admin_url( 'plugins.php?action=deactivate&plugin=' . urlencode( $wpmdb_basename ) ), 'deactivate-plugin_' . $wpmdb_basename );
?>
<div class="updated warning inline-message below-h2">
	<strong><?php _e( 'CLI Addon Disabled', 'wp-migrate-db' ); ?></strong> &mdash; 
	<?php
	printf( __( 'The CLI addon follows the requirements of WP-CLI, which requires %1$s+. You are currently running PHP %2$s. You will need to update PHP to use WP-CLI and the CLI addon. <strong><a href="%3$s">Deactivate</a></strong> the CLI addon to get rid of this message.', 'wp-migrate-db' ), $required, PHP_VERSION, $deactivate );
	?>
</div>