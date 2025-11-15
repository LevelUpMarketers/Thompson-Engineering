<?php
/**
 * Utility helpers for storing and retrieving CPB error logs.
 *
 * @package Codex_Plugin_Boilerplate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPB_Error_Log_Helper {

    const SCOPE_SITEWIDE = 'sitewide';
    const SCOPE_PLUGIN   = 'plugin';
    const SCOPE_PAYMENTS = 'payments';

    /**
     * Mapping of scopes to log filenames.
     *
     * @var array<string, string>
     */
    protected static $filenames = array(
        self::SCOPE_SITEWIDE => 'cpb-sitewide-errors.log',
        self::SCOPE_PLUGIN   => 'cpb-plugin-errors.log',
        self::SCOPE_PAYMENTS => 'cpb-payment-activity.log',
    );

    /**
     * Cached log file paths keyed by scope.
     *
     * @var array<string, string>
     */
    protected static $paths = array();

    /**
     * Retrieve the filesystem path for the requested log scope.
     *
     * Ensures the upload directory exists and the log file is seeded.
     *
     * @param string $scope Log scope identifier.
     *
     * @return string Log file path or empty string on failure.
     */
    public static function get_log_file_path( $scope ) {
        $scope = self::normalize_scope( $scope );

        if ( '' === $scope ) {
            return '';
        }

        if ( isset( self::$paths[ $scope ] ) ) {
            return self::$paths[ $scope ];
        }

        $upload_dir = wp_upload_dir();

        if ( ! empty( $upload_dir['error'] ) ) {
            return '';
        }

        $directory = trailingslashit( $upload_dir['basedir'] ) . 'cpb-logs';

        /**
         * Filter the base directory used to store error logs.
         *
         * @since 0.1.0
         *
         * @param string $directory  Absolute directory path.
         * @param array  $upload_dir Upload directory information from {@see wp_upload_dir()}.
         * @param string $scope      Error log scope.
         */
        $directory = apply_filters( 'cpb_error_log_directory', $directory, $upload_dir, $scope );
        $directory = untrailingslashit( $directory );

        if ( '' === $directory ) {
            return '';
        }

        if ( ! wp_mkdir_p( $directory ) ) {
            return '';
        }

        $filename = self::$filenames[ $scope ];
        $path     = trailingslashit( $directory ) . $filename;

        if ( ! file_exists( $path ) ) {
            $handle = @fopen( $path, 'a' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

            if ( ! $handle ) {
                return '';
            }

            fclose( $handle );
        }

        self::$paths[ $scope ] = $path;

        return $path;
    }

    /**
     * Append an error entry to the requested log file.
     *
     * @param string $scope Log scope identifier.
     * @param array  $entry Log details.
     *
     * @return bool
     */
    public static function append_entry( $scope, array $entry ) {
        $scope = self::normalize_scope( $scope );

        if ( '' === $scope ) {
            return false;
        }

        if ( ! self::is_scope_enabled( $scope ) ) {
            return false;
        }

        $path = self::get_log_file_path( $scope );

        if ( '' === $path ) {
            return false;
        }

        $formatted = self::format_entry( $entry );

        if ( '' === $formatted ) {
            return false;
        }

        $result = file_put_contents( $path, $formatted, FILE_APPEND | LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

        return false !== $result;
    }

    /**
     * Generate a formatted log entry.
     *
     * @param array $entry Log data.
     *
     * @return string
     */
    public static function format_entry( array $entry ) {
        $timestamp = isset( $entry['timestamp'] ) ? self::sanitize_line( $entry['timestamp'] ) : gmdate( 'c' );
        $label     = isset( $entry['label'] ) ? self::sanitize_line( $entry['label'] ) : __( 'Notice', 'codex-plugin-boilerplate' );
        $severity  = isset( $entry['severity'] ) ? self::sanitize_line( $entry['severity'] ) : '';
        $message   = isset( $entry['message'] ) ? self::normalize_multiline( $entry['message'] ) : '';
        $file      = isset( $entry['file'] ) ? self::sanitize_line( $entry['file'] ) : '';
        $line      = isset( $entry['line'] ) ? (int) $entry['line'] : 0;
        $stack     = isset( $entry['stack'] ) ? self::normalize_multiline( $entry['stack'] ) : '';
        $scope     = isset( $entry['scope'] ) ? self::sanitize_line( $entry['scope'] ) : '';

        $lines = array(
            '=== Error Logged ===',
            'Timestamp (UTC): ' . $timestamp,
            'Type: ' . $label,
        );

        if ( '' !== $severity ) {
            $lines[] = 'PHP Severity: ' . $severity;
        }

        if ( '' !== $scope ) {
            $lines[] = 'Captured Scope: ' . $scope;
        }

        $lines[] = 'Message:';
        $lines[] = $message ? $message : '—';

        if ( '' !== $file ) {
            $lines[] = 'File: ' . $file;
        }

        if ( $line > 0 ) {
            $lines[] = 'Line: ' . $line;
        }

        if ( '' !== $stack ) {
            $lines[] = 'Stack Trace:';
            $lines[] = $stack;
        }

        $lines[] = '=== End Error Logged ===';
        $lines[] = '';

        return implode( "\n", $lines );
    }

    /**
     * Retrieve the raw contents of a log file.
     *
     * @param string $scope Log scope identifier.
     *
     * @return string
     */
    public static function get_log_contents( $scope ) {
        $path = self::get_log_file_path( $scope );

        if ( '' === $path ) {
            return '';
        }

        $contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_get_contents

        if ( false === $contents ) {
            return '';
        }

        return (string) $contents;
    }

    /**
     * Clear a log file while keeping it available for future writes.
     *
     * @param string $scope Log scope identifier.
     *
     * @return bool
     */
    public static function clear_log( $scope ) {
        $path = self::get_log_file_path( $scope );

        if ( '' === $path ) {
            return false;
        }

        $handle = @fopen( $path, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

        if ( ! $handle ) {
            return false;
        }

        fclose( $handle );

        return true;
    }

    /**
     * Retrieve the suggested download filename for a scope.
     *
     * @param string $scope Log scope identifier.
     *
     * @return string
     */
    public static function get_download_filename( $scope ) {
        $scope = self::normalize_scope( $scope );

        if ( '' === $scope ) {
            return '';
        }

        return self::$filenames[ $scope ];
    }

    /**
     * Retrieve the success message shown after clearing a log scope.
     *
     * @param string $scope Log scope identifier.
     *
     * @return string
     */
    public static function get_clear_success_message( $scope ) {
        $scope = self::normalize_scope( $scope );

        switch ( $scope ) {
            case self::SCOPE_PLUGIN:
                return __( 'CPB error log cleared.', 'codex-plugin-boilerplate' );
            case self::SCOPE_SITEWIDE:
                return __( 'Sitewide error log cleared.', 'codex-plugin-boilerplate' );
            case self::SCOPE_PAYMENTS:
                return __( 'Payment log cleared.', 'codex-plugin-boilerplate' );
        }

        return '';
    }

    /**
     * Normalize the provided scope to a known value.
     *
     * @param string $scope Raw scope value.
     *
     * @return string
     */
    public static function normalize_scope( $scope ) {
        $scope = sanitize_key( $scope );

        if ( isset( self::$filenames[ $scope ] ) ) {
            return $scope;
        }

        return '';
    }

    /**
     * Provide a human-readable label for a scope.
     *
     * @param string $scope Log scope identifier.
     *
     * @return string
     */
    public static function get_scope_label( $scope ) {
        $scope = self::normalize_scope( $scope );

        switch ( $scope ) {
            case self::SCOPE_PLUGIN:
                return __( 'CPB-Related', 'codex-plugin-boilerplate' );
            case self::SCOPE_SITEWIDE:
                return __( 'Sitewide', 'codex-plugin-boilerplate' );
            case self::SCOPE_PAYMENTS:
                return __( 'Payment', 'codex-plugin-boilerplate' );
        }

        return '';
    }

    /**
     * Determine whether the provided scope is permitted to write entries.
     *
     * @param string $scope Normalized log scope identifier.
     *
     * @return bool
     */
    protected static function is_scope_enabled( $scope ) {
        switch ( $scope ) {
            case self::SCOPE_SITEWIDE:
                return CPB_Settings_Helper::is_logging_enabled( CPB_Settings_Helper::FIELD_LOG_SITE_ERRORS );
            case self::SCOPE_PLUGIN:
                return CPB_Settings_Helper::is_logging_enabled( CPB_Settings_Helper::FIELD_LOG_PLUGIN_ERRORS );
            case self::SCOPE_PAYMENTS:
                return CPB_Settings_Helper::is_logging_enabled( CPB_Settings_Helper::FIELD_LOG_PAYMENTS );
        }

        return true;
    }

    /**
     * Convert arbitrary values to a printable string.
     *
     * @param mixed $value Raw value.
     *
     * @return string
     */
    protected static function stringify_value( $value ) {
        if ( is_string( $value ) ) {
            return $value;
        }

        if ( null === $value ) {
            return '';
        }

        if ( is_scalar( $value ) ) {
            return (string) $value;
        }

        if ( $value instanceof \Throwable ) {
            return $value->getMessage();
        }

        if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
            return (string) $value;
        }

        $encoded = wp_json_encode( $value );

        if ( is_string( $encoded ) ) {
            return $encoded;
        }

        $printed = print_r( $value, true );

        return is_string( $printed ) ? $printed : '';
    }

    /**
     * Sanitize a single-line string for storage.
     *
     * @param mixed $value Raw string.
     *
     * @return string
     */
    protected static function sanitize_line( $value ) {
        $value = self::stringify_value( $value );
        $value = wp_strip_all_tags( $value );
        $value = preg_replace( "/[\r\n]+/", ' ', $value );

        return trim( $value );
    }

    /**
     * Normalize multiline text by removing disallowed tags and standardizing newlines.
     *
     * @param mixed $value Raw multiline string.
     *
     * @return string
     */
    protected static function normalize_multiline( $value ) {
        $value = self::stringify_value( $value );
        $value = wp_strip_all_tags( $value );
        $value = preg_replace( "/\\r\\n?/", "\\n", $value );
        $value = preg_replace( "/\\n{3,}/", "\\n\\n", $value );

        return trim( $value );
    }
}

