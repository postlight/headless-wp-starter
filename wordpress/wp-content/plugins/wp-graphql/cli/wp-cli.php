<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WPGraphQL_CLI_Command extends WP_CLI_Command {

	/**
	 * Generate a static schema.
	 *
	 * Defaults to creating a schema.graphql file in the IDL format at the root
	 * of the plugin.
	 *
	 * @todo: Provide alternative formats (AST? INTROSPECTION JSON?) and options for output location/file-type?
	 * @todo: Add Unit Tests
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp graphql generate
	 *
	 * @alias generate
	 * @subcommand generate-static-schema
	 */
	public function generate_static_schema( $args, $assoc_args ) {

		/**
		 * Set the file path for where to save the static schema
		 */
		$file_path = WPGRAPHQL_PLUGIN_DIR . 'schema.graphql';

		/**
		 * Generate the Schema
		 */
		WP_CLI::line( 'Getting the Schema...' );
		$schema = WPGraphQL::get_schema();

		/**
		 * Format the Schema
		 */
		WP_CLI::line( 'Formatting the Schema...' );
		$printed = \GraphQL\Utils\SchemaPrinter::doPrint( $schema );

		/**
		 * Save the Schema to the file
		 */
		WP_CLI::line( 'Saving the Schema...' );
		file_put_contents( $file_path, $printed );

		/**
		 * All done!
		 */
		WP_CLI::success( sprintf( 'All done. Schema output to %s.', $file_path ) );
	}
}

WP_CLI::add_command( 'graphql', 'WPGraphQL_CLI_Command' );
