<?php
/*
Plugin Name: WP Migrate DB Pro CLI
Plugin URI: http://deliciousbrains.com/wp-migrate-db-pro/
Description: An extension to WP Migrate DB Pro, allows you to execute migrations using a function call or via WP-CLI
Author: Delicious Brains
Version: 1.3
Author URI: http://deliciousbrains.com
Network: True
*/

// Copyright (c) 2013 Delicious Brains. All rights reserved.
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

require_once 'version.php';
$GLOBALS['wpmdb_meta']['wp-migrate-db-pro-cli']['folder'] = basename( plugin_dir_path( __FILE__ ) );

function wp_migrate_db_pro_cli_addon_loaded() {
	// register with wp-cli if it's running, command hasn't already been defined elsewhere, and WPMDBPro is active
	if ( defined( 'WP_CLI' ) && WP_CLI && ! class_exists( 'WPMDBPro_CLI_Command' ) && class_exists( 'WPMDBPro' ) ) {
		require_once dirname( __FILE__ ) . '/class/wpmdbpro-cli-command.php';
	}

	// register plugin with wordpress
	if ( class_exists( 'WPMDBPro_Addon' ) ) {
		require_once dirname( __FILE__ ) . '/class/wpmdbpro-cli-addon.php';
	}

	load_plugin_textdomain( 'wp-migrate-db-pro-cli', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	cli_version_requirement_check();
}
add_action( 'plugins_loaded', 'wp_migrate_db_pro_cli_addon_loaded', 20 );

/**
 * Populate the $wpmdbpro_cli global with an instance of the WPMDBPro_CLI class and return it.
 *
 * @return WPMDBPro_CLI The one true global instance of the WPMDBPro_CLI class.
 */
function wp_migrate_db_pro_cli_addon() {
	global $wpmdbpro_cli;

	if ( ! is_null( $wpmdbpro_cli ) ) {
		return $wpmdbpro_cli;
	}

	if ( function_exists( 'wp_migrate_db_pro' ) ) {
		wp_migrate_db_pro();
	} else {
		return false;
	}

	do_action( 'wp_migrate_db_pro_cli_before_load' );

	require_once dirname( __FILE__ ) . '/class/wpmdbpro-cli.php';
	$wpmdbpro_cli = new WPMDBPro_CLI( __FILE__ );

	do_action( 'wp_migrate_db_pro_cli_after_load' );

	return $wpmdbpro_cli;
}

/**
 * Check if php version meets requirements to run cli addon
 * Display notice on options page if it doesn't.
 *
 * @return void
 */
function cli_version_requirement_check() {
	$required_php_version = $GLOBALS['wpmdb_meta']['wp-migrate-db-pro-cli']['required-php-version'];
	if ( ! version_compare( PHP_VERSION, $required_php_version, '>=' ) ) {
		add_action( 'wpmdb_notices', 'show_php_version_requirement_warning');
	}
}

/**
 * Display php version requirement warning message.
 *
 * @return  void
 */
function show_php_version_requirement_warning() {
	$template_dir_path = plugin_dir_path( __FILE__ ) . 'template/' ;
	$warning_template_path = $template_dir_path . 'php-version-requirement-warning.php';
	include( $warning_template_path );
}
