<?php
/**
 * Helper utilities for storing and retrieving general plugin settings.
 *
 * @package Codex_Plugin_Boilerplate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPB_Settings_Helper {

    const OPTION_NAME = 'cpb_general_settings';

    const FIELD_OPTION            = 'option';
    const FIELD_LOG_EMAIL         = 'log_email';
    const FIELD_LOG_SMS           = 'log_sms';
    const FIELD_LOG_SITE_ERRORS   = 'log_site_errors';
    const FIELD_LOG_PLUGIN_ERRORS = 'log_plugin_errors';
    const FIELD_LOG_PAYMENTS      = 'log_payments';

    /**
     * Retrieve all stored general settings merged with defaults.
     *
     * @return array
     */
    public static function get_general_settings() {
        $stored   = get_option( self::OPTION_NAME, array() );
        $defaults = self::get_default_settings();

        if ( ! is_array( $stored ) ) {
            $stored = array();
        }

        return wp_parse_args( $stored, $defaults );
    }

    /**
     * Retrieve default general settings.
     *
     * @return array
     */
    public static function get_default_settings() {
        return array(
            self::FIELD_OPTION            => '',
            self::FIELD_LOG_EMAIL         => true,
            self::FIELD_LOG_SMS           => true,
            self::FIELD_LOG_SITE_ERRORS   => true,
            self::FIELD_LOG_PLUGIN_ERRORS => true,
            self::FIELD_LOG_PAYMENTS      => true,
        );
    }

    /**
     * Persist sanitized general settings.
     *
     * @param array $settings Raw settings.
     *
     * @return bool
     */
    public static function save_general_settings( array $settings ) {
        $sanitized = self::sanitize_general_settings( $settings );

        return update_option( self::OPTION_NAME, $sanitized );
    }

    /**
     * Sanitize the provided general settings.
     *
     * @param array $settings Raw settings.
     *
     * @return array
     */
    public static function sanitize_general_settings( array $settings ) {
        $defaults = self::get_default_settings();
        $sanitized = $defaults;

        if ( isset( $settings[ self::FIELD_OPTION ] ) ) {
            $sanitized[ self::FIELD_OPTION ] = sanitize_text_field( wp_unslash( $settings[ self::FIELD_OPTION ] ) );
        }

        $toggle_fields = array(
            self::FIELD_LOG_EMAIL,
            self::FIELD_LOG_SMS,
            self::FIELD_LOG_SITE_ERRORS,
            self::FIELD_LOG_PLUGIN_ERRORS,
            self::FIELD_LOG_PAYMENTS,
        );

        foreach ( $toggle_fields as $field ) {
            $sanitized[ $field ] = ! empty( $settings[ $field ] );
        }

        return $sanitized;
    }

    /**
     * Determine whether a given logging channel is enabled.
     *
     * @param string $channel Channel identifier (one of the FIELD_LOG_* constants).
     *
     * @return bool
     */
    public static function is_logging_enabled( $channel ) {
        $settings = self::get_general_settings();

        if ( ! isset( $settings[ $channel ] ) ) {
            return false;
        }

        return (bool) $settings[ $channel ];
    }
}
