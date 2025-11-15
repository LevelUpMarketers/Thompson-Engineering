<?php
/**
 * Capture PHP and WordPress notices for the CPB log screens.
 *
 * @package Codex_Plugin_Boilerplate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPB_Error_Logger {

    /**
     * Previously registered PHP error handler.
     *
     * @var callable|null
     */
    protected $previous_error_handler = null;

    /**
     * Previously registered exception handler.
     *
     * @var callable|null
     */
    protected $previous_exception_handler = null;

    /**
     * Normalized plugin directory path for scope checks.
     *
     * @var string
     */
    protected $plugin_dir = '';

    public function __construct() {
        if ( defined( 'CPB_PLUGIN_DIR' ) ) {
            $this->plugin_dir = wp_normalize_path( CPB_PLUGIN_DIR );
        }
    }

    /**
     * Register all logging hooks.
     */
    public function register() {
        $this->previous_error_handler     = set_error_handler( array( $this, 'handle_error' ) );
        $this->previous_exception_handler = set_exception_handler( array( $this, 'handle_exception' ) );

        register_shutdown_function( array( $this, 'handle_shutdown' ) );

        add_action( 'doing_it_wrong_run', array( $this, 'handle_doing_it_wrong' ), 10, 3 );
        add_action( 'deprecated_function_run', array( $this, 'handle_deprecated_function' ), 10, 3 );
        add_action( 'deprecated_argument_run', array( $this, 'handle_deprecated_argument' ), 10, 4 );
        add_action( 'deprecated_hook_run', array( $this, 'handle_deprecated_hook' ), 10, 4 );
        add_action( 'deprecated_file_included', array( $this, 'handle_deprecated_file' ), 10, 4 );
    }

    /**
     * PHP error handler proxy.
     *
     * @param int    $errno   Error number.
     * @param string $errstr  Error message.
     * @param string $errfile File path.
     * @param int    $errline Line number.
     *
     * @return bool
     */
    public function handle_error( $errno, $errstr, $errfile = '', $errline = 0 ) {
        if ( 0 === error_reporting() ) {
            // Respect suppressed errors (@ operator).
            return $this->call_previous_error_handler( $errno, $errstr, $errfile, $errline );
        }

        $this->log_php_error( $errno, $errstr, $errfile, $errline );

        return $this->call_previous_error_handler( $errno, $errstr, $errfile, $errline );
    }

    /**
     * Exception handler proxy.
     *
     * @param Throwable $exception Captured exception/throwable.
     */
    public function handle_exception( $exception ) {
        $message = sprintf(
            /* translators: %s: exception class name */
            __( 'Uncaught %s encountered.', 'codex-plugin-boilerplate' ),
            is_object( $exception ) ? get_class( $exception ) : __( 'exception', 'codex-plugin-boilerplate' )
        );

        $this->log_event(
            array(
                'label'    => __( 'Exception', 'codex-plugin-boilerplate' ),
                'severity' => 'E_EXCEPTION',
                'message'  => $message . ' ' . $exception->getMessage(),
                'file'     => $exception->getFile(),
                'line'     => $exception->getLine(),
                'stack'    => $exception->getTraceAsString(),
            )
        );

        if ( $this->previous_exception_handler ) {
            call_user_func( $this->previous_exception_handler, $exception );
        }
    }

    /**
     * Shutdown handler to catch fatal errors.
     */
    public function handle_shutdown() {
        $error = error_get_last();

        if ( empty( $error ) ) {
            return;
        }

        $fatal_types = array(
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
        );

        if ( isset( $error['type'] ) && in_array( $error['type'], $fatal_types, true ) ) {
            $this->log_php_error( $error['type'], $error['message'], $error['file'], $error['line'], true );
        }
    }

    /**
     * Log "doing it wrong" warnings.
     *
     * @param string $function Function name.
     * @param string $message  Warning message.
     * @param string $version  Version details.
     */
    public function handle_doing_it_wrong( $function, $message, $version ) {
        $formatted = sprintf(
            /* translators: 1: function name, 2: version, 3: message */
            __( 'Function %1$s was called incorrectly (since %2$s): %3$s', 'codex-plugin-boilerplate' ),
            $function,
            $version,
            $message
        );

        $this->log_event(
            array(
                'label'    => __( 'Incorrect Usage', 'codex-plugin-boilerplate' ),
                'severity' => 'doing_it_wrong',
                'message'  => $formatted,
                'stack'    => $this->get_stack_summary(),
            )
        );
    }

    /**
     * Log deprecated function usage.
     *
     * @param string $function Function name.
     * @param string $replacement Replacement suggestion.
     * @param string $version Version deprecated.
     */
    public function handle_deprecated_function( $function, $replacement, $version ) {
        $message = $this->format_deprecated_message( __( 'Function', 'codex-plugin-boilerplate' ), $function, $replacement, $version );

        $this->log_event(
            array(
                'label'    => __( 'Deprecated Function', 'codex-plugin-boilerplate' ),
                'severity' => 'deprecated_function',
                'message'  => $message,
                'stack'    => $this->get_stack_summary(),
            )
        );
    }

    /**
     * Log deprecated argument usage.
     */
    public function handle_deprecated_argument( $function, $message, $version, $replacement = null ) {
        $summary = sprintf(
            /* translators: 1: function name, 2: version, 3: message */
            __( 'Argument used by %1$s is deprecated since %2$s: %3$s', 'codex-plugin-boilerplate' ),
            $function,
            $version,
            $message
        );

        if ( $replacement ) {
            $summary .= ' ' . sprintf(
                /* translators: %s: replacement suggestion */
                __( 'Use %s instead.', 'codex-plugin-boilerplate' ),
                $replacement
            );
        }

        $this->log_event(
            array(
                'label'    => __( 'Deprecated Argument', 'codex-plugin-boilerplate' ),
                'severity' => 'deprecated_argument',
                'message'  => $summary,
                'stack'    => $this->get_stack_summary(),
            )
        );
    }

    /**
     * Log deprecated hook usage.
     */
    public function handle_deprecated_hook( $hook, $message, $version, $replacement = null ) {
        $summary = sprintf(
            /* translators: 1: hook name, 2: version, 3: message */
            __( 'Hook %1$s is deprecated since %2$s: %3$s', 'codex-plugin-boilerplate' ),
            $hook,
            $version,
            $message
        );

        if ( $replacement ) {
            $summary .= ' ' . sprintf(
                /* translators: %s: replacement hook */
                __( 'Use %s instead.', 'codex-plugin-boilerplate' ),
                $replacement
            );
        }

        $this->log_event(
            array(
                'label'    => __( 'Deprecated Hook', 'codex-plugin-boilerplate' ),
                'severity' => 'deprecated_hook',
                'message'  => $summary,
                'stack'    => $this->get_stack_summary(),
            )
        );
    }

    /**
     * Log deprecated file usage.
     */
    public function handle_deprecated_file( $file, $replacement, $version, $message ) {
        $summary = sprintf(
            /* translators: 1: file path, 2: version, 3: message */
            __( 'File %1$s is deprecated since %2$s: %3$s', 'codex-plugin-boilerplate' ),
            $file,
            $version,
            $message
        );

        if ( $replacement ) {
            $summary .= ' ' . sprintf(
                /* translators: %s: replacement file */
                __( 'Use %s instead.', 'codex-plugin-boilerplate' ),
                $replacement
            );
        }

        $this->log_event(
            array(
                'label'    => __( 'Deprecated File', 'codex-plugin-boilerplate' ),
                'severity' => 'deprecated_file',
                'message'  => $summary,
                'stack'    => $this->get_stack_summary(),
            )
        );
    }

    /**
     * Send a PHP error to the logging system.
     *
     * @param int    $errno   Error number.
     * @param string $errstr  Message.
     * @param string $errfile File path.
     * @param int    $errline Line number.
     * @param bool   $is_fatal Whether triggered from shutdown handler.
     */
    protected function log_php_error( $errno, $errstr, $errfile, $errline, $is_fatal = false ) {
        $label = $this->map_error_label( $errno, $is_fatal );
        $stack = $this->get_stack_summary();

        $this->log_event(
            array(
                'label'    => $label,
                'severity' => $this->map_error_constant( $errno ),
                'message'  => $errstr,
                'file'     => $errfile,
                'line'     => $errline,
                'stack'    => $stack,
            )
        );
    }

    /**
     * Map PHP error numbers to human-readable labels.
     *
     * @param int  $errno   Error number.
     * @param bool $is_fatal Whether triggered during shutdown.
     *
     * @return string
     */
    protected function map_error_label( $errno, $is_fatal = false ) {
        $map = array(
            E_ERROR             => __( 'Fatal Error', 'codex-plugin-boilerplate' ),
            E_WARNING           => __( 'Warning', 'codex-plugin-boilerplate' ),
            E_PARSE             => __( 'Parse Error', 'codex-plugin-boilerplate' ),
            E_NOTICE            => __( 'Notice', 'codex-plugin-boilerplate' ),
            E_CORE_ERROR        => __( 'Core Error', 'codex-plugin-boilerplate' ),
            E_CORE_WARNING      => __( 'Core Warning', 'codex-plugin-boilerplate' ),
            E_COMPILE_ERROR     => __( 'Compile Error', 'codex-plugin-boilerplate' ),
            E_COMPILE_WARNING   => __( 'Compile Warning', 'codex-plugin-boilerplate' ),
            E_USER_ERROR        => __( 'User Error', 'codex-plugin-boilerplate' ),
            E_USER_WARNING      => __( 'User Warning', 'codex-plugin-boilerplate' ),
            E_USER_NOTICE       => __( 'User Notice', 'codex-plugin-boilerplate' ),
            E_STRICT            => __( 'Strict Notice', 'codex-plugin-boilerplate' ),
            E_RECOVERABLE_ERROR => __( 'Recoverable Error', 'codex-plugin-boilerplate' ),
            E_DEPRECATED        => __( 'Deprecated Notice', 'codex-plugin-boilerplate' ),
            E_USER_DEPRECATED   => __( 'User Deprecated Notice', 'codex-plugin-boilerplate' ),
        );

        if ( isset( $map[ $errno ] ) ) {
            return $map[ $errno ];
        }

        return $is_fatal ? __( 'Fatal Error', 'codex-plugin-boilerplate' ) : __( 'Notice', 'codex-plugin-boilerplate' );
    }

    /**
     * Convert PHP error numbers to their constant names when possible.
     *
     * @param int $errno Error number.
     *
     * @return string
     */
    protected function map_error_constant( $errno ) {
        $constants = array(
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_STRICT            => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        );

        return isset( $constants[ $errno ] ) ? $constants[ $errno ] : 'E_UNKNOWN';
    }

    /**
     * Write an event to one or more log scopes.
     *
     * @param array $entry Entry data.
     */
    protected function log_event( array $entry ) {
        $entry['timestamp'] = gmdate( 'c' );

        $file       = isset( $entry['file'] ) ? $entry['file'] : '';
        $message    = isset( $entry['message'] ) ? $entry['message'] : '';
        $stack      = isset( $entry['stack'] ) ? $entry['stack'] : '';
        $is_plugin  = $this->is_plugin_related( $file, $message, $stack );
        if ( CPB_Settings_Helper::is_logging_enabled( CPB_Settings_Helper::FIELD_LOG_SITE_ERRORS ) ) {
            $site_entry           = $entry;
            $site_entry['scope'] = CPB_Error_Log_Helper::get_scope_label( CPB_Error_Log_Helper::SCOPE_SITEWIDE );
            CPB_Error_Log_Helper::append_entry( CPB_Error_Log_Helper::SCOPE_SITEWIDE, $site_entry );
        }

        if ( $is_plugin && CPB_Settings_Helper::is_logging_enabled( CPB_Settings_Helper::FIELD_LOG_PLUGIN_ERRORS ) ) {
            $plugin_entry          = $entry;
            $plugin_entry['scope'] = CPB_Error_Log_Helper::get_scope_label( CPB_Error_Log_Helper::SCOPE_PLUGIN );
            CPB_Error_Log_Helper::append_entry( CPB_Error_Log_Helper::SCOPE_PLUGIN, $plugin_entry );
        }
    }

    /**
     * Determine whether the error is related to this plugin.
     *
     * @param string $file    File path.
     * @param string $message Message text.
     * @param string $stack   Stack trace.
     *
     * @return bool
     */
    protected function is_plugin_related( $file, $message, $stack ) {
        if ( $file ) {
            $file = wp_normalize_path( $file );

            if ( $this->plugin_dir && 0 === strpos( $file, $this->plugin_dir ) ) {
                return true;
            }
        }

        $message = $this->stringify_for_match( $message );
        $stack   = $this->stringify_for_match( $stack );

        $keywords = array(
            'codex-plugin-boilerplate',
            'cpb_',
            'CPB_',
        );

        foreach ( $keywords as $keyword ) {
            if ( false !== stripos( $message, $keyword ) ) {
                return true;
            }

            if ( $stack && false !== stripos( $stack, $keyword ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize arbitrary values to strings for keyword matching.
     *
     * @param mixed $value Potentially non-string value.
     *
     * @return string
     */
    protected function stringify_for_match( $value ) {
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

        return is_string( $encoded ) ? $encoded : '';
    }

    /**
     * Format deprecated notices consistently.
     *
     * @param string      $type        Type label.
     * @param string      $subject     Deprecated element.
     * @param string|null $replacement Replacement suggestion.
     * @param string      $version     Version.
     *
     * @return string
     */
    protected function format_deprecated_message( $type, $subject, $replacement, $version ) {
        $message = sprintf(
            /* translators: 1: deprecated type, 2: name, 3: version */
            __( '%1$s %2$s is deprecated since %3$s.', 'codex-plugin-boilerplate' ),
            $type,
            $subject,
            $version
        );

        if ( $replacement ) {
            $message .= ' ' . sprintf(
                /* translators: %s: replacement */
                __( 'Use %s instead.', 'codex-plugin-boilerplate' ),
                $replacement
            );
        }

        return $message;
    }

    /**
     * Retrieve a summary of the current call stack.
     *
     * @return string
     */
    protected function get_stack_summary() {
        if ( function_exists( 'wp_debug_backtrace_summary' ) ) {
            return wp_debug_backtrace_summary( null, 0, false );
        }

        $trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
        $lines = array();

        foreach ( $trace as $frame ) {
            if ( isset( $frame['file'], $frame['line'] ) ) {
                $lines[] = $frame['file'] . ':' . $frame['line'];
            } elseif ( isset( $frame['function'] ) ) {
                $lines[] = $frame['function'];
            }
        }

        return implode( ' <- ', $lines );
    }

    /**
     * Forward errors to the previously registered handler when available.
     *
     * @param int    $errno   Error number.
     * @param string $errstr  Error message.
     * @param string $errfile File path.
     * @param int    $errline Line number.
     *
     * @return bool
     */
    protected function call_previous_error_handler( $errno, $errstr, $errfile, $errline ) {
        if ( $this->previous_error_handler ) {
            return (bool) call_user_func( $this->previous_error_handler, $errno, $errstr, $errfile, $errline );
        }

        return false;
    }
}

