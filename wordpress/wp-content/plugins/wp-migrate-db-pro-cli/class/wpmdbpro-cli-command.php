<?php

/**
 * Migrate your DB using WP Migrate DB Pro.
 */

//require wpmdbpro-command.php from wp-migrate-db-pro
require_once $GLOBALS['wpmdb_meta']['wp-migrate-db-pro']['abspath'] . '/class/wpmdbpro-command.php';

class WPMDBPro_CLI_Command extends WPMDBPro_Command {

	/**
	 * Push local DB up to remote.
	 *
	 * ## OPTIONS
	 *
	 * <url>
	 * : The URL of the remote site. Should include the URL encoded basic
	 * authentication credentials (if required). e.g. http://user:password@example.com
	 *
	 *     Must include the WordPress directory if WordPress is stored in a subdirectory.
	 *     e.g. http://example.com/wp
	 *
	 * <secret-key>
	 * : The remote site's secret key.
	 *
	 * [--find=<strings>]
	 * : A comma separated list of strings to find when performing a string find
	 * and replace across the database.
	 *
	 *     Table names should be quoted as needed, i.e. when using a comma in the
	 *     find/replace string.
	 *
	 *     The --replace=<strings> argument should be used in conjunction to specify
	 *     the replace values for the strings found using this argument. The number
	 *     of strings specified in this argument should match the number passed into
	 *     --replace=<strings> argument.
	 *
	 *     If omitted, a set of 2 find and replace pairs will be performed by default:
	 *
	 *       1. Strings containing URLs referencing the source site will be replace
	 *          by the destination URL.
	 *
	 *       2. Strings containing root file paths referencing the source site will
	 *          be replaced by the destination root file path.
	 *
	 * [--replace=<strings>]
	 * : A comma separated list of replace value strings to implement when
	 * performing a string find & replace across the database.
	 *
	 *     Should be used in conjunction with the --find=<strings> argument, see it's
	 *     documentation for further explanation of the find & replace functionality.
	 *
	 * [--include-tables=<tables>]
	 * : The comma separated list of tables to migrate. Excluding this parameter
	 * will migrate all tables in your database that begin with your
	 * installation's table prefix, e.g. wp_.
	 *
	 * [--exclude-post-types=<post-types>]
	 * : A comma separated list of post types to exclude. Excluding this parameter
	 * will migrate all post types.
	 *
	 * [--skip-replace-guids]
	 * : Do not perform a find & replace on the guid column in the wp_posts table.
	 *
	 * [--exclude-spam]
	 * : Exclude spam comments.
	 *
	 * [--preserve-active-plugins]
	 * : Preserves the active_plugins option (which plugins are activated/deactivated).
	 *
	 * [--include-transients]
	 * : Include transients (temporary cached data).
	 *
	 * [--backup=<prefix|selected|table_one,table_two,table_etc>]
	 * : Perform a backup of the destination site's database tables before replacing it.
	 *
	 *     Accepted values:
	 *
	 *     * prefix - Backup only tables that begin with your installation's
	 *                table prefix (e.g. wp_)
	 *     * selected - Backup only tables selected for migration (as in --include-tables)
	 *     * A comma separated list of the tables to backup.
	 *
	 * [--media=<compare|compare-and-remove|remove-and-copy>]
	 * : Perform a migration of the media files. Requires the Media Files addon.
	 *
	 *     Accepted values:
	 *
	 *     * compare - compares remote and local media files and only uploads
	 *                 those missing or updated
	 *     * compare-and-remove - compares remote and local media files and only
	 *                            uploads those missing or updated. Removes remote
	 *                            media files that are not found on the local site
	 *     * remove-and-copy - removes all remote media files and uploads all local
	 *                         media files (skips comparison)
	 *
	 * [--media-subsites=<blog-id|subsite-url>]
	 * : Only transfer media files for selected subsites
	 *
	 *     * Only applies to multisite installs
	 *     * Separate multiple subsites with commas
	 *     * Use Blog ID or URL of *local* subsites
	 *
	 * [--subsite=<blog-id|subsite-url>]
	 * : Push the given subsite to the remote single site install.
	 * Requires the Multisite Tools addon.
	 *
	 *     Overrides the --media-subsites option.
	 *
	 * ## EXAMPLES
	 *
	 *     wp migratedb push http://bradt.ca LJPmq3t8h6uuN7aqQ3YSnt7C88Wzzv5BVPlgLbYE \
	 *        --find=http://dev.bradt.ca,/Users/bradt/home/bradt.ca
	 *        --replace=http://bradt.ca,/home/bradt.ca
	 *        --include-tables=wp_posts,wp_postmeta
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @since 1.1
	 */
	public function push( $args, $assoc_args ) {
		$assoc_args['action'] = 'push';
		$profile = $this->_get_profile_data_from_args( $args, $assoc_args );
		if ( is_wp_error( $profile ) ) {
			WP_CLI::error( WPMDBPro_CLI::cleanup_message( $profile->get_error_message() ) );
		}

		$this->_perform_cli_migration( $profile );
	}

	/**
	 * Pull remote DB down to local.
	 *
	 * ## OPTIONS
	 *
	 * <url>
	 * : The URL of the remote site. Should include the URL encoded basic
	 * authentication credentials (if required). e.g. http://user:password@example.com
	 *
	 *     Must include the WordPress directory if WordPress is stored in a subdirectory.
	 *     e.g. http://example.com/wp
	 *
	 * <secret-key>
	 * : The remote site's secret key.
	 *
	 * [--find=<strings>]
	 * : A comma separated list of strings to find when performing a string find
	 * and replace across the database.
	 *
	 *     Table names should be quoted as needed, i.e. when using a comma in the
	 *     find/replace string.
	 *
	 *     The --replace=<strings> argument should be used in conjunction to specify
	 *     the replace values for the strings found using this argument. The number
	 *     of strings specified in this argument should match the number passed into
	 *     --replace=<strings> argument.
	 *
	 *     If omitted, a set of 2 find and replace pairs will be performed by default:
	 *
	 *       1. Strings containing URLs referencing the source site will be replace
	 *          by the destination URL.
	 *
	 *       2. Strings containing root file paths referencing the source site will
	 *          be replaced by the destination root file path.
	 *
	 * [--replace=<strings>]
	 * : A comma separated list of replace value strings to implement when
	 * performing a string find & replace across the database.
	 *
	 *     Should be used in conjunction with the --find=<strings> argument, see it's
	 *     documentation for further explanation of the find & replace functionality.
	 *
	 * [--include-tables=<tables>]
	 * : The comma separated list of tables to migrate. Excluding this parameter
	 * will migrate all tables in your database that begin with your
	 * installation's table prefix, e.g. wp_.
	 *
	 * [--exclude-post-types=<post-types>]
	 * : A comma separated list of post types to exclude. Excluding this parameter
	 * will migrate all post types.
	 *
	 * [--skip-replace-guids]
	 * : Do not perform a find & replace on the guid column in the wp_posts table.
	 *
	 * [--exclude-spam]
	 * : Exclude spam comments.
	 *
	 * [--preserve-active-plugins]
	 * : Preserves the active_plugins option (which plugins are activated/deactivated).
	 *
	 * [--include-transients]
	 * : Include transients (temporary cached data).
	 *
	 * [--backup=<prefix|selected|table_one,table_two,table_etc>]
	 * : Perform a backup of the destination site's database tables before replacing it.
	 *
	 *     Accepted values:
	 *
	 *     * prefix - Backup only tables that begin with your installation's
	 *                table prefix (e.g. wp_)
	 *     * selected - Backup only tables selected for migration (as in --include-tables)
	 *     * A comma separated list of the tables to backup.
	 *
	 * [--media=<compare|compare-and-remove|remove-and-copy>]
	 * : Perform a migration of the media files. Requires the Media Files addon.
	 *
	 *     Accepted values:
	 *
	 *     * compare - compares remote and local media files and only downloads
	 *                 those missing or updated
	 *     * compare-and-remove - compares remote and local media files and only
	 *                            downloads those missing or updated. Removes local
	 *                            media files that are not found on the remote site
	 *     * remove-and-copy - removes all local media files and downloads all remote
	 *                         media files (skips comparison)
	 *
	 * [--media-subsites=<blog-id|subsite-url>]
	 * : Only transfer media files for selected subsites
	 *
	 *     * Only applies to multisite installs
	 *     * Separate multiple subsites with commas
	 *     * Use Blog ID or URL of *remote* subsites
	 *
	 * [--subsite=<blog-id|subsite-url>]
	 * : Pull the remote single site install into the given subsite.
	 * Requires the Multisite Tools addon.
	 *
	 *     Overrides the --media-subsites option.
	 *
	 * ## EXAMPLES
	 *
	 *     wp migratedb pull http://bradt.ca LJPmq3t8h6uuN7aqQ3YSnt7C88Wzzv5BVPlgLbYE \
	 *        --find=http://dev.bradt.ca,/Users/bradt/home/bradt.ca
	 *        --replace=http://bradt.ca,/home/bradt.ca
	 *        --include-tables=wp_posts,wp_postmeta
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @since 1.1
	 */
	public function pull( $args, $assoc_args ) {
		$assoc_args['action'] = 'pull';

		$profile = $this->_get_profile_data_from_args( $args, $assoc_args );
		if ( is_wp_error( $profile ) ) {
			WP_CLI::error( WPMDBPro_CLI::cleanup_message( $profile->get_error_message() ) );
		}

		$this->_perform_cli_migration( $profile );
	}

	/**
	 * Run a migration.
	 *
	 * ## OPTIONS
	 *
	 * <profile>
	 * : ID of the profile to use for the migration.
	 *
	 * ## EXAMPLES
	 *
	 *  wp migratedb migrate 1
	 *
	 * @synopsis <profile>
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @since 1.0
	 */
	public function migrate( $args, $assoc_args ) {
		$profile = $args[0];

		$this->_perform_cli_migration( $profile );
	}

	/**
	 * Returns a list of profiles.
	 *
	 * @since 1.2.4
	 */
	public function profiles() {
		$wpmdb_settings = get_site_option( 'wpmdb_settings' );

		// Display error if no profiles are present
		if ( ! isset( $wpmdb_settings['profiles'] ) || ! is_array( $wpmdb_settings['profiles'] ) || empty( $wpmdb_settings['profiles'] ) ) {
			WP_CLI::error( __( 'There are no saved profiles for this site.', 'wp-migrate-db-pro-cli' ) );
			return;
		}

		// Get profile information in CLI format
		$cli_items = array();
		foreach ( $wpmdb_settings['profiles'] as $key => $profile ) {
			++ $key;

			// Get remote and set empty if action is export/safefile
			$connection_info = $profile['connection_info'];
			$connection_info = explode( "\n", $connection_info );
			$remote = '---';
			if ( is_array( $connection_info ) && 2 === count( $connection_info ) ) {
				$remote = esc_url_raw( $connection_info[0] );
			}

			// Allow actions to be translated for output
			$action_strings = array(
				'push'         => _x( 'push', 'Export data to remote database', 'wp-migrate-db-pro-cli' ),
				'pull'         => _x( 'pull', 'Import data from remote database', 'wp-migrate-db-pro-cli' ),
				'savefile'     => _x( 'export', 'Export file from local database', 'wp-migrate-db-pro-cli' ),
				'find_replace' => _x( 'find & replace', 'Run a find & replace on local database', 'wp-migrate-db-pro-cli' ),
			);

			if ( isset( $action_strings[ $profile['action'] ] ) ) {
				$profile['action'] = $action_strings[ $profile['action'] ];
			} else {
				$profile['action'] = '---';
			}
			$profile['action'] = strtoupper( $profile['action'] );

			//Populate CLI items with saved profile information
			$cli_items[] = array(
				_x( 'ID', 'Profile list column heading', 'wp-migrate-db-pro-cli' )     => $key,
				_x( 'NAME', 'Profile list column heading', 'wp-migrate-db-pro-cli' )   => $profile['name'],
				_x( 'ACTION', 'Profile list column heading', 'wp-migrate-db-pro-cli' ) => $profile['action'],
				_x( 'REMOTE', 'Profile list column heading', 'wp-migrate-db-pro-cli' ) => $remote,
			);
		}

		// Set CLI column headers. Must match `cli_items` keys
		$cli_keys = array_keys( reset( $cli_items ) );
		WP_CLI\Utils\format_items( 'table', $cli_items, $cli_keys );
	}

	/**
	 * Run a migration.
	 *
	 * ## OPTIONS
	 *
	 * <profile>
	 * : ID of the profile to use for the migration.
	 *
	 * ## EXAMPLES
	 *
	 * 	wp migratedb profile 1
	 *
	 * @synopsis <profile>
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @since 1.1
	 */
	public function profile( $args, $assoc_args ) {
		// uses migrate method
		$this->migrate( $args, $assoc_args );
	}

	/**
	 * Update settings for migratedb.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : Either get or update
	 *
	 * <setting_name>
	 * : Name of setting to update or get.
	 * Available settings: push | pull | connection-key | license
	 *
	 * [<value>]
	 * : Value of new setting
	 *
	 * ## EXAMPLES
	 *
	 * wp migratedb setting update license xxx-xxx-xxx-xxxxx
	 *
	 * wp migratedb setting get license
	 *
	 * wp migratedb setting update pull on
	 *
	 * wp migratedb setting get pull
	 *
	 * @param array $args
	 *
	 * @return bool
	 * @since    1.2.5
	 */
	public function setting( $args ) {
		require_once dirname( __FILE__ ) . '/wpmdbpro-cli-settings.php';
		$wpmdb_cli_settings = new WPMDBPro_CLI_Settings( __FILE__ );

		// Handle settings logic in dedicated class
		$wpmdb_cli_settings->handle_setting( $args );
	}

	// overrides _perform_cli_migration from WPMDB_Command
	protected function _perform_cli_migration( $profile ) {
		$wpmdbpro_cli = null;

		if ( function_exists( 'wp_migrate_db_pro_cli_addon' ) ) {
			$wpmdbpro_cli = wp_migrate_db_pro_cli_addon();
		}

		if ( empty( $wpmdbpro_cli ) ) {
			WP_CLI::error( __( 'WP Migrate DB Pro CLI class not available.', 'wp-migrate-db-pro-cli' ) );
			return;
		}

		$result = $wpmdbpro_cli->cli_migration( $profile );

		if ( true === $result ) {
			WP_CLI::success( __( 'Migration successful.', 'wp-migrate-db-pro-cli' ) );
		} elseif ( ! is_wp_error( $result ) ) {
			WP_CLI::success( sprintf( __( 'Export saved to: %s', 'wp-migrate-db-pro-cli' ), $result ) );
		} elseif ( is_wp_error( $result ) ) {
			WP_CLI::error( WPMDBPro_CLI::cleanup_message( $result->get_error_message() ) );
		}
	}

}

/**
 * Deprecated WP Migrate DB Pro command. Use migratedb instead.
 */
class WPMDBCLI_Deprecated extends WPMDBPro_CLI_Command {
	/**
	 * Run a migration.
	 *
	 * ## OPTIONS
	 *
	 * <profile>
	 * : ID of the profile to use for the migration.
	 *
	 * ## EXAMPLES
	 *
	 *    wp wpmdb migrate 1
	 *
	 * @synopsis <profile>
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @since 1.0
	 */
	public function migrate( $args, $assoc_args ) {
		parent::migrate( $args, $assoc_args );
	}
}

WP_CLI::add_command( 'wpmdb', 'WPMDBCLI_Deprecated' ); // deprecated older command
WP_CLI::add_command( 'migratedb', 'WPMDBPro_CLI_Command' );

