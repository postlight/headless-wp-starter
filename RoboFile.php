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
	public function wordpressSetup() {
		$confirm = $this->io()->confirm( 'This will replace your current ' .
		'WordPress install. Are you sure you want to do this?', false );

		if ( ! $confirm ) {
			return;
		}

		$this->_exec( "mysql -uroot -e 'create user wp'" );
		$this->_exec( "mysql -uroot -e 'create database wp'" );
		$this->_exec( "mysql -uroot -e \"grant all privileges on wp . * to wp@localhost identified by 'wp'\"" );
		$this->_exec( "mysql -uroot -e 'flush privileges'" );

		$this->wp( 'core download --version=4.8 --force' );
		$this->wp( 'core config --dbname=wp --dbuser=wp --dbpass=wp --dbhost=127.0.0.1' );
		$this->wp( 'db drop --yes' );
		$this->wp( 'db create --yes' );

		$install_command = implode( ' ', array(
			'core install',
			'--url=localhost:8080',
			'--title="Postlight Headless WP Starter"',
			'--admin_user="admin"',
			'--admin_password="nedstark"',
			'--admin_email="admin@headlesswpstarter.dev"',
			'--skip-email',
		) );

		$this->wp( $install_command );

		$this->wp( 'theme activate postlight-headless-wp' );
		$this->wp( 'theme delete twentyfourteen' );
		$this->wp( 'theme delete twentyfifteen' );
		$this->wp( 'theme delete twentysixteen' );
		$this->wp( 'theme delete twentyseventeen' );

		$this->wp( 'plugin delete akismet' );
		$this->wp( 'plugin delete hello' );

		$plugins_command = implode( ' ', array(
			'plugin activate',
			'acf-to-wp-api',
			'advanced-custom-fields-pro',
			'co-authors-plus',
			'msm-sitemap',
			'query-monitor',
			'wp-migrate-db-pro',
			'wp-migrate-db-pro-cli',
			'wp-migrate-db-pro-media-files',
			'post-meta-inspector',
			'acf-code-field',
		) );

		$this->wp( $plugins_command );

		$this->wp( 'user create nedstark nedstark@headlesswpstarter.dev' );

		$this->wp( 'acf sync' );

		$this->wp( 'rewrite structure "/%year%/%monthnum%/%day%/%postname%/"' );

		$this->io()->success( 'Great. You can now log into WordPress at: http://localhost:8080/wp-admin (admin/nedstark)' );
		$this->server();
	}

	public function server() {
		$this->wp( 'server' );
	}

	public function wordpressUpdatePlugins() {
		$plugin_command = implode( ' ', array(
			'plugin update',
			'co-authors-plus',
			'posts-to-posts',
			'query-monitor',
			'acf-to-wp-api',
		) );

		$this->wp( $plugin_command );
	}

	public function wp( $arg ) {
		$this->taskExec( 'wp' )
		 ->dir( WP_DIR )
		 ->rawArg( $arg )
		 ->run();
	}
}
