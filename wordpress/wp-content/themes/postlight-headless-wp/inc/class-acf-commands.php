<?php
if ( defined( 'WP_CLI' ) && WP_CLI && ! class_exists( 'ACF_Commands' ) ) {
    /**
     * ACF_Commands
     */
    class ACF_Commands extends WP_CLI_Command {
        /**
         * Sync ACF Fields
         *
         * ## OPTIONS
         *
         * @when init
         *
         * @example
         *
         *  wp acf sync
         *
         * @param arr $args Arguments
         * @param arr $assoc_args Associated arguments
         * @return void
         */
        public function sync( $args, $assoc_args ) {

            // vars
            $groups = acf_get_field_groups();
            $sync   = [];

            // bail early if no field groups
            if ( empty( $groups ) ) {
                return;
            }

            // find JSON field groups which have not yet been imported
            foreach ( $groups as $group ) {
                // vars
                $local    = acf_maybe_get( $group, 'local', false );
                $modified = acf_maybe_get( $group, 'modified', 0 );
                $private  = acf_maybe_get( $group, 'private', false );

                // ignore DB / PHP / private field groups
                if ( 'json' !== $local || $private ) {
                    // do nothing
                    true;
                } elseif ( ! $group['ID'] ) {
                    $sync[ $group['key'] ] = $group;
                } elseif ( $modified && $modified > get_post_modified_time( 'U', true, $group['ID'], true ) ) {
                    $sync[ $group['key'] ] = $group;
                }
            }

            // bail if no sync needed
            if ( empty( $sync ) ) {
                WP_CLI::success( 'No ACF Sync Required' );
                return;
            }

            if ( ! empty( $sync ) ) {
                acf_update_setting( 'json', false );

                // vars
                $new_ids = [];

                foreach ( $sync as $key => $v ) {
                    // append fields
                    if ( acf_have_local_fields( $key ) ) {
                        $sync[ $key ]['fields'] = acf_get_local_fields( $key );
                    }
                    // import
                    $field_group = acf_import_field_group( $sync[ $key ] );
                }
            }

            WP_CLI::success( 'ACF SYNC SUCCESS!' );
        }
    }
    WP_CLI::add_command( 'acf', 'ACF_Commands' );
}
