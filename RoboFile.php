<?php
define( 'PROJECT_DIR', dirname( __FILE__ ) );
define( 'TMP_DIR', PROJECT_DIR . '/tmp' );
define( 'WP_DIR', PROJECT_DIR . '/wordpress' );

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks {

	public function wordpressSetup( $opts = [
		'wp-user' => 'nedstark',
		'wp-pw' => 'winteriscoming',
		'wp-theme-dir' => 'postlight-headless-wp',
		'wp-theme-name' => 'Postlight Headless WP Starter',
		'wp-email' => 'nedstark@headlesswpstarter.dev',
		'wp-db-name' => 'wp_headless',
		'wp-description' => 'Just another (headless) WordPress site',
		'wp-plugins' => array(),
	] ) {
		$confirm = $this->io()->confirm( 'This will replace your current ' .
		'WordPress install. Are you sure you want to do this?', false );

		if ( ! $confirm ) {
			return;
		}

		$this->_exec( "mysql -uroot -proot -h 0.0.0.0 -e 'create user if not exists " . $opts['wp-db-name'] . "'" );
		$this->_exec( "mysql -uroot -proot -h 0.0.0.0 -e 'create database if not exists " . $opts['wp-db-name'] . "'" );
		$this->_exec( 'mysql -uroot -proot -h 0.0.0.0 -e "grant all privileges on ' . $opts['wp-db-name']
		. ' . * to ' . $opts['wp-db-name'] . "@localhost identified by '" . $opts['wp-db-name'] . "'\"" );
		$this->_exec( "mysql -uroot -proot -h 0.0.0.0 -e 'flush privileges'" );

		$this->wp( 'core download --version=4.9 --locale=en_US --force' );
		$this->wp( 'core config --dbname=' . $opts['wp-db-name'] . ' --dbuser=' . $opts['wp-db-name'] . ' --dbpass=' . $opts['wp-db-name'] . ' --dbhost=0.0.0.0' );
		$this->wp( 'db drop --yes' );
		$this->wp( 'db create --yes' );

		$install_command = implode( ' ', array(
			'core install',
			'--url=localhost:8080',
			'--title="' . $opts['wp-theme-name'] . '"',
			'--admin_user="' . $opts['wp-user'] . '"',
			'--admin_password="' . $opts['wp-pw'] . '"',
			'--admin_email="' . $opts['wp-email'] . '"',
			'--skip-email',
		) );

		$this->wp( $install_command );

		$this->wp( 'theme activate ' . $opts['wp-theme-dir'] );
		$this->wp( 'theme delete twentyfourteen' );
		$this->wp( 'theme delete twentyfifteen' );
		$this->wp( 'theme delete twentysixteen' );
		$this->wp( 'theme delete twentyseventeen' );

		$this->wp( 'plugin delete akismet' );
		$this->wp( 'plugin delete hello' );

		if ( is_array( $opts['wp-plugins'] ) && sizeof( $opts['wp-plugins'] ) > 0 ) {
			$installed_plugin_directories = $opts['wp-plugins'];
		} else {
			$installed_plugins = array_filter( glob( WP_DIR . '/wp-content/plugins/*' ), 'is_dir' );
			$installed_plugin_directories = array_filter( str_replace( WP_DIR . '/wp-content/plugins/', '', $installed_plugins ) );
		}

		if ( sizeof( $installed_plugin_directories ) > 0 ) {
			$plugins_command = 'plugin activate ' . ( implode( ' ', $installed_plugin_directories ) );
			$this->wp( $plugins_command );
		}

		// Sync ACF
		$this->wp( 'acf sync' );

		// Pretty URL structure required for wp-json path to work correctly
		$this->wp( 'rewrite structure "/%year%/%monthnum%/%day%/%postname%/"' );

		// Set the site description
		$this->wp( 'option update blogdescription "' . $opts['wp-description'] . '"' );

		$this->wp( 'post update 1 --post_title="Hello headless WordPress world" '.
			'--post_content=\'Welcome to WordPress. This is your first post. '.
			'<a href="http://localhost:8080/wp-json/postlight/v1/post?slug=hello-world&edit=true" target="_new">Edit</a>'.
			' or delete it, then start writing!\'' );

		// Set up example menu
		$this->wp( 'menu create "My Menu"' );
		$this->wp( 'menu item add-post my-menu 1' );
		$this->wp( 'menu item add-post my-menu 2' );
		$this->wp( 'menu item add-custom my-menu "About the Starter Kit" https://trackchanges.postlight.com/introducing-postlights-wordpress-react-starter-kit-a61e2633c48c' );
		$this->wp( 'menu location assign my-menu main' );

		$this->io()->success( 'Great. You can now log into WordPress at: http://localhost:8080/wp-admin (' . $opts['wp-user'] . '/' . $opts['wp-pw'] . ')' );
	}

	public function server() {
		$this->wp( 'server' );
	}

	public function wordpressImport( $opts = [
		'migratedb-license' => null,
		'migratedb-from' => null,
	] ) {
		if ( isset( $opts['migratedb-license'] ) ) {
			$this->wp( 'migratedb setting update license ' . $opts['migratedb-license'] );
		} else {
			$this->say( 'WP Migrate DB Pro: no license available. Please set migratedb-license in the robo.yml file.' );
			return;
		}

		if ( isset( $opts['migratedb-from'] ) ) {
			$command = 'WPMDB_EXCLUDE_RESIZED_MEDIA=1 wp migratedb pull ';
			$command .= $opts['migratedb-from'];
			$command .= ' --backup=prefix ';
			$command .= ' --media=compare ';
			$this->io()->success( 'About to run data migration from ' . $opts['migratedb-from'] );
			$this->taskExec( $command )->run();
			// Set siteurl and home
			$this->wp( 'option update siteurl http://localhost:8080' );
			$this->wp( 'option update home http://localhost:8080' );
		} else {
			$this->say( 'WP Migrate DB Pro: No source installation specified. Please set migratedb-from in the robo.yml file.' );
			return;
		}
	}

	public function wp( $arg ) {
		$this->taskExec( 'wp' )
		 ->dir( WP_DIR )
		 ->rawArg( $arg )
		 ->run();
	}
}
