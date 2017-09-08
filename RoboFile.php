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
		'wp-plugins' => array(),
	] ) {
		$confirm = $this->io()->confirm( 'This will replace your current ' .
		'WordPress install. Are you sure you want to do this?', false );

		if ( ! $confirm ) {
			return;
		}

		$this->_exec( "mysql -uroot -e 'create user " . $opts['wp-db-name'] . "'" );
		$this->_exec( "mysql -uroot -e 'create database " . $opts['wp-db-name'] . "'" );
		$this->_exec( 'mysql -uroot -e "grant all privileges on ' . $opts['wp-db-name']
		. ' . * to ' . $opts['wp-db-name'] . "@localhost identified by '" . $opts['wp-db-name'] . "'\"" );
		$this->_exec( "mysql -uroot -e 'flush privileges'" );

		$this->wp( 'core download --version=4.8.1 --force' );
		$this->wp( 'core config --dbname=' . $opts['wp-db-name'] . ' --dbuser=' . $opts['wp-db-name'] . ' --dbpass=' . $opts['wp-db-name'] . ' --dbhost=127.0.0.1' );
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

		// Pretty URL structure required for wp-json path to work correctly
		$this->wp( 'rewrite structure "/%year%/%monthnum%/%day%/%postname%/"' );

		$this->io()->success( 'Great. You can now log into WordPress at: http://localhost:8080/wp-admin (' . $opts['wp-user'] . '/' . $opts['wp-pw'] . ')' );
		$this->server();
	}

	public function server() {
		$this->wp( 'server' );
	}

	public function wp( $arg ) {
		$this->taskExec( 'wp' )
		 ->dir( WP_DIR )
		 ->rawArg( $arg )
		 ->run();
	}
}
