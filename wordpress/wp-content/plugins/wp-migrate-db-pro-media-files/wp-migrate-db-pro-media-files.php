<?php
/*
Plugin Name: WP Migrate DB Pro Media Files
Plugin URI: http://deliciousbrains.com/wp-migrate-db-pro/
Description: An extension to WP Migrate DB Pro, allows the migration of media files.
Author: Delicious Brains
Version: 1.4.7
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
$GLOBALS['wpmdb_meta']['wp-migrate-db-pro-media-files']['folder'] = basename( plugin_dir_path( __FILE__ ) );

/**
 * Populate the $wpmdbpro_media_files global with an instance of the WPMDBPro_Media_Files class and return it.
 *
 * @param bool $cli Running in WP-CLI environment.
 *
 * @return WPMDBPro_Media_Files The one true global instance of the WPMDBPro_Media_Files class.
 */
function wp_migrate_db_pro_media_files( $cli = false ) {
	global $wpmdbpro_media_files;

	if ( ! class_exists( 'WPMDBPro_Addon' ) ) {
		return false;
	}

	// Allows hooks to bypass the regular admin / ajax checks to force load the Media Files addon (required for the CLI addon)
	$force_load = apply_filters( 'wp_migrate_db_pro_media_files_force_load', false );

	if ( false === $force_load && ! is_null( $wpmdbpro_media_files ) ) {
		return $wpmdbpro_media_files;
	}

	if ( false === $force_load && ( ! function_exists( 'wp_migrate_db_pro_loaded' ) || ! wp_migrate_db_pro_loaded() ) ) {
		return false;
	}

	load_plugin_textdomain( 'wp-migrate-db-pro-media-files', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	require_once dirname( __FILE__ ) . '/class/wpmdbpro-media-files.php';
	require_once dirname( __FILE__ ) . '/class/wpmdbpro-media-files-base.php';
	require_once dirname( __FILE__ ) . '/class/wpmdbpro-media-files-local.php';
	require_once dirname( __FILE__ ) . '/class/wpmdbpro-media-files-remote.php';

	if ( $cli ) {
		require_once dirname( __FILE__ ) . '/class/cli/wpmdbpro-media-files-cli.php';
		require_once dirname( __FILE__ ) . '/class/cli/wpmdbpro-media-files-cli-bar.php';

		$wpmdbpro_media_files = new WPMDBPro_Media_Files_CLI( __FILE__ );
	} else {
		$wpmdbpro_media_files = new WPMDBPro_Media_Files( __FILE__ );
	}

	return $wpmdbpro_media_files;
}

/**
 * By default load plugin on admin pages, a little later than WP Migrate DB Pro.
 */
add_action( 'admin_init', 'wp_migrate_db_pro_media_files', 20 );

/**
 * Loads up an instance of the WPMDBPro_Media_Files class, allowing media files to be migrated during CLI migrations.
 */
function wp_migrate_db_pro_media_files_before_cli_load() {
	// Force load the Media Files addon
	add_filter( 'wp_migrate_db_pro_media_files_force_load', '__return_true' );

	wp_migrate_db_pro_media_files( true );
}

add_action( 'wp_migrate_db_pro_cli_before_load', 'wp_migrate_db_pro_media_files_before_cli_load' );
