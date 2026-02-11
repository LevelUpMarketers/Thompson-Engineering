<?php
/**
 * Fired during plugin deactivation
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Deactivator {

    public static function deactivate() {
        $cron_array = _get_cron_array();

        if ( empty( $cron_array ) || ! is_array( $cron_array ) ) {
            delete_option( 'teqcidb_demo_cron_last_run' );
            flush_rewrite_rules();

            return;
        }

        foreach ( $cron_array as $timestamp => $hooks ) {
            foreach ( $hooks as $hook => $instances ) {
                if ( 0 !== strpos( $hook, TEQCIDB_Cron_Manager::HOOK_PREFIX ) ) {
                    continue;
                }

                foreach ( $instances as $instance ) {
                    $args = isset( $instance['args'] ) ? (array) $instance['args'] : array();
                    wp_unschedule_event( $timestamp, $hook, $args );
                }
            }
        }

        delete_option( 'teqcidb_demo_cron_last_run' );

        flush_rewrite_rules();
    }
}
