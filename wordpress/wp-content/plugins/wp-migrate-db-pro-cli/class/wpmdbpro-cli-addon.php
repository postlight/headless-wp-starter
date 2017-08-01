<?php

class WPMDBPro_CLI_Addon extends WPMDBPro_Addon {

	function __construct( $plugin_file_path ) {
		parent::__construct( $plugin_file_path );

		$this->plugin_slug          = 'wp-migrate-db-pro-cli';
		$this->plugin_version       = $GLOBALS['wpmdb_meta']['wp-migrate-db-pro-cli']['version'];
		$this->php_version_required = $GLOBALS['wpmdb_meta']['wp-migrate-db-pro-cli']['required-php-version'];

		if ( ! version_compare( PHP_VERSION, $this->php_version_required, '>=' ) ) {
			return;
		}

		if ( ! $this->meets_version_requirements( '1.7' ) ) {
			return;
		}
	}

}
