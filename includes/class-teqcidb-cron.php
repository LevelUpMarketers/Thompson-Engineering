<?php
/**
 * Cron management utilities for Thompson Engineering QCI Database.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Cron_Manager {

    const HOOK_PREFIX                         = 'teqcidb_';
    const DEMO_HOOK                           = 'teqcidb_demo_cron_event';
    const EXPIRATION_DISPATCH_HOOK            = 'teqcidb_expiration_reminder_dispatch';
    const EXPIRATION_SEND_HOOK                = 'teqcidb_send_expiration_reminder_email';
    const EXPIRATION_SENT_KEYS_OPTION         = 'teqcidb_expiration_reminder_sent_keys';

    /**
     * Bootstraps the cron manager.
     */
    public function register() {
        add_action( 'init', array( $this, 'maintain_demo_event' ) );
        add_action( 'init', array( $this, 'maintain_expiration_dispatch_event' ) );

        add_action( self::DEMO_HOOK, array( $this, 'handle_demo_event' ) );
        add_action( self::EXPIRATION_DISPATCH_HOOK, array( $this, 'dispatch_expiration_reminders' ) );
        add_action( self::EXPIRATION_SEND_HOOK, array( $this, 'send_expiration_reminder_email' ) );
    }

    /**
     * Ensures the demo cron event remains scheduled without duplicating entries.
     */
    public function maintain_demo_event() {
        $args = array( 'demo' => true );

        $has_valid_event = $this->prune_demo_event_duplicates( $args );

        if ( function_exists( 'wp_get_scheduled_event' ) ) {
            $event = wp_get_scheduled_event( self::DEMO_HOOK, $args );

            if ( $event && ! empty( $event->schedule ) ) {
                wp_unschedule_event( $event->timestamp, self::DEMO_HOOK, $args );
                $event           = false;
                $has_valid_event = false;
            } else {
                $has_valid_event = (bool) $event;
            }
        }

        if ( ! $has_valid_event ) {
            $this->schedule_demo_event( $args );
        }
    }

    /**
     * Ensures the daily expiration-reminder dispatch event remains scheduled.
     */
    public function maintain_expiration_dispatch_event() {
        $scheduled = wp_next_scheduled( self::EXPIRATION_DISPATCH_HOOK );

        if ( false !== $scheduled ) {
            return;
        }

        $start_time = current_time( 'timestamp' ) + HOUR_IN_SECONDS;
        wp_schedule_event( $start_time, 'daily', self::EXPIRATION_DISPATCH_HOOK );
    }

    /**
     * Scans students for expiration windows and schedules one-off reminder sends.
     */
    public function dispatch_expiration_reminders() {
        global $wpdb;

        $students_table = $wpdb->prefix . 'teqcidb_students';
        $like           = $wpdb->esc_like( $students_table );
        $found          = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $students_table ) {
            return;
        }

        $rows = $wpdb->get_results(
            "SELECT wpuserid, expiration_date FROM $students_table WHERE expiration_date IS NOT NULL AND expiration_date != '' AND expiration_date != '0000-00-00'",
            ARRAY_A
        );

        if ( ! is_array( $rows ) || empty( $rows ) ) {
            return;
        }

        $timezone  = wp_timezone();
        $today     = new DateTimeImmutable( 'now', $timezone );
        $today     = $today->setTime( 0, 0, 0 );
        $templates = $this->get_expiration_reminder_templates();
        $offset    = 0;

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $student_wpid = isset( $row['wpuserid'] ) ? absint( $row['wpuserid'] ) : 0;
            $raw_date     = isset( $row['expiration_date'] ) ? sanitize_text_field( (string) $row['expiration_date'] ) : '';

            if ( $student_wpid <= 0 || '' === $raw_date ) {
                continue;
            }

            $expiration = DateTimeImmutable::createFromFormat( 'Y-m-d', $raw_date, $timezone );

            if ( ! $expiration instanceof DateTimeImmutable ) {
                continue;
            }

            $expiration = $expiration->setTime( 0, 0, 0 );
            $days_until = (int) $today->diff( $expiration )->format( '%r%a' );

            if ( ! isset( $templates[ $days_until ] ) ) {
                continue;
            }

            $template_id = $templates[ $days_until ];
            $args        = array(
                'student_wpid' => $student_wpid,
                'template_id'  => $template_id,
                'days_before'  => $days_until,
                'target_date'  => $expiration->format( 'Y-m-d' ),
            );

            if ( false !== wp_next_scheduled( self::EXPIRATION_SEND_HOOK, $args ) ) {
                continue;
            }

            wp_schedule_single_event( current_time( 'timestamp' ) + ( $offset * 30 ), self::EXPIRATION_SEND_HOOK, $args );
            $offset++;
        }
    }

    /**
     * Sends one expiration reminder email for a single student/template pair.
     *
     * @param array $args Cron event arguments.
     */
    public function send_expiration_reminder_email( $args = array() ) {
        $args = is_array( $args ) ? $args : array();

        $student_wpid = isset( $args['student_wpid'] ) ? absint( $args['student_wpid'] ) : 0;
        $template_id  = isset( $args['template_id'] ) ? sanitize_key( (string) $args['template_id'] ) : '';
        $target_date  = isset( $args['target_date'] ) ? sanitize_text_field( (string) $args['target_date'] ) : '';

        if ( $student_wpid <= 0 || '' === $template_id ) {
            return;
        }

        global $wpdb;

        $students_table = $wpdb->prefix . 'teqcidb_students';
        $like           = $wpdb->esc_like( $students_table );
        $found          = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $students_table ) {
            return;
        }

        $student = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT wpuserid, first_name, last_name, email, company, phone_cell, phone_office, their_representative, expiration_date FROM $students_table WHERE wpuserid = %d LIMIT 1",
                $student_wpid
            ),
            ARRAY_A
        );

        if ( ! is_array( $student ) ) {
            return;
        }

        $recipient = isset( $student['email'] ) ? sanitize_email( (string) $student['email'] ) : '';

        if ( '' === $recipient || ! is_email( $recipient ) ) {
            return;
        }

        if ( '' === $target_date ) {
            $target_date = isset( $student['expiration_date'] ) ? sanitize_text_field( (string) $student['expiration_date'] ) : '';
        }

        $send_key = $template_id . '|' . $student_wpid . '|' . $target_date;

        if ( $this->has_sent_expiration_reminder_key( $send_key ) ) {
            return;
        }

        $stored_settings  = TEQCIDB_Email_Template_Helper::get_template_settings( $template_id );
        $from_name        = TEQCIDB_Email_Template_Helper::resolve_from_name( isset( $stored_settings['from_name'] ) ? $stored_settings['from_name'] : '' );
        $from_email       = TEQCIDB_Email_Template_Helper::resolve_from_email( isset( $stored_settings['from_email'] ) ? $stored_settings['from_email'] : '' );
        $subject_template = isset( $stored_settings['subject'] ) ? sanitize_text_field( (string) $stored_settings['subject'] ) : '';
        $body_template    = isset( $stored_settings['body'] ) ? wp_kses_post( (string) $stored_settings['body'] ) : '';
        $cc               = TEQCIDB_Email_Template_Helper::sanitize_recipient_list( isset( $stored_settings['cc'] ) ? $stored_settings['cc'] : '' );
        $bcc              = TEQCIDB_Email_Template_Helper::sanitize_recipient_list( isset( $stored_settings['bcc'] ) ? $stored_settings['bcc'] : '' );

        if ( '' === $subject_template && '' === $body_template ) {
            return;
        }

        $tokens  = $this->build_expiration_reminder_tokens( $student );
        $subject = $this->replace_tokens( $subject_template, $tokens );
        $body    = $this->replace_tokens( $body_template, $tokens );

        $rendered_body = wp_kses_post( $body );

        if ( '' !== $rendered_body ) {
            $rendered_body = nl2br( $rendered_body );
        }

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        $from_header = TEQCIDB_Email_Template_Helper::build_from_header( $from_name, $from_email );

        if ( '' !== $from_header ) {
            $headers[] = $from_header;
        }

        if ( '' !== $cc ) {
            $headers[] = 'Cc: ' . $cc;
        }

        if ( '' !== $bcc ) {
            $headers[] = 'Bcc: ' . $bcc;
        }

        $sent = wp_mail( $recipient, $subject, $rendered_body, $headers );

        if ( ! $sent ) {
            return;
        }

        TEQCIDB_Email_Log_Helper::log_email(
            array(
                'template_id'    => $template_id,
                'template_title' => TEQCIDB_Email_Template_Helper::get_template_label( $template_id ),
                'recipient'      => $recipient,
                'from_name'      => $from_name,
                'from_email'     => $from_email,
                'subject'        => $subject,
                'body'           => $rendered_body,
                'context'        => __( 'Automated expiration reminder email', 'teqcidb' ),
                'triggered_by'   => __( 'Scheduled expiration reminder cron', 'teqcidb' ),
            )
        );

        $this->mark_expiration_reminder_sent_key( $send_key );
    }

    /**
     * Return reminder template IDs keyed by days-until-expiration.
     *
     * @return array<int,string>
     */
    private function get_expiration_reminder_templates() {
        return array(
            45 => 'teqcidb-email-expiration-reminder-45-day',
        );
    }

    /**
     * Build reminder merge tokens using a specific student record.
     *
     * @param array $student Student row.
     *
     * @return array
     */
    private function build_expiration_reminder_tokens( array $student ) {
        $tokens = TEQCIDB_Student_Helper::get_latest_preview_data();

        $tokens['student_first_name'] = isset( $student['first_name'] ) ? sanitize_text_field( (string) $student['first_name'] ) : '';
        $tokens['student_last_name']  = isset( $student['last_name'] ) ? sanitize_text_field( (string) $student['last_name'] ) : '';
        $tokens['student_email']      = isset( $student['email'] ) ? sanitize_email( (string) $student['email'] ) : '';
        $tokens['student_company']    = isset( $student['company'] ) ? sanitize_text_field( (string) $student['company'] ) : '';
        $tokens['student_phone_cell'] = isset( $student['phone_cell'] ) ? sanitize_text_field( (string) $student['phone_cell'] ) : '';
        $tokens['student_phone_office'] = isset( $student['phone_office'] ) ? sanitize_text_field( (string) $student['phone_office'] ) : '';

        $expiration_date = isset( $student['expiration_date'] ) ? sanitize_text_field( (string) $student['expiration_date'] ) : '';
        $date            = DateTimeImmutable::createFromFormat( 'Y-m-d', $expiration_date, wp_timezone() );
        $tokens['student_certification_expiration'] = $date ? $date->format( 'm-d-Y' ) : '';

        $representative = isset( $student['their_representative'] ) ? json_decode( (string) $student['their_representative'], true ) : array();

        if ( is_array( $representative ) ) {
            $tokens['student_representative'] = trim(
                sprintf(
                    '%s %s',
                    isset( $representative['first_name'] ) ? sanitize_text_field( (string) $representative['first_name'] ) : '',
                    isset( $representative['last_name'] ) ? sanitize_text_field( (string) $representative['last_name'] ) : ''
                )
            );
        }

        $tokens['representative_first_name'] = isset( $tokens['representative_first_name'] ) ? $tokens['representative_first_name'] : '';
        $tokens['representative_last_name']  = isset( $tokens['representative_last_name'] ) ? $tokens['representative_last_name'] : '';
        $tokens['individuals_registered']    = '';

        return $tokens;
    }

    /**
     * Replace merge tokens in email template content.
     *
     * @param string $content Content with merge tags.
     * @param array  $tokens  Merge token values.
     *
     * @return string
     */
    private function replace_tokens( $content, array $tokens ) {
        if ( ! is_string( $content ) || '' === $content || empty( $tokens ) ) {
            return (string) $content;
        }

        foreach ( $tokens as $key => $value ) {
            if ( ! is_scalar( $value ) ) {
                continue;
            }

            $content = str_replace( '{' . $key . '}', (string) $value, $content );
        }

        return $content;
    }

    /**
     * Check if a reminder send key has already been delivered.
     *
     * @param string $key Unique send key.
     *
     * @return bool
     */
    private function has_sent_expiration_reminder_key( $key ) {
        $sent = get_option( self::EXPIRATION_SENT_KEYS_OPTION, array() );

        return is_array( $sent ) && in_array( $key, $sent, true );
    }

    /**
     * Persist a reminder send key to prevent duplicate sends.
     *
     * @param string $key Unique send key.
     */
    private function mark_expiration_reminder_sent_key( $key ) {
        $sent = get_option( self::EXPIRATION_SENT_KEYS_OPTION, array() );

        if ( ! is_array( $sent ) ) {
            $sent = array();
        }

        if ( in_array( $key, $sent, true ) ) {
            return;
        }

        $sent[] = $key;

        if ( count( $sent ) > 5000 ) {
            $sent = array_slice( $sent, -5000 );
        }

        update_option( self::EXPIRATION_SENT_KEYS_OPTION, $sent, false );
    }

    /**
     * Removes duplicate demo events so only one sample cron remains.
     *
     * @param array $args Demo event arguments.
     *
     * @return bool Whether a valid demo event remains scheduled.
     */
    private function prune_demo_event_duplicates( $args ) {
        $cron_array = _get_cron_array();

        if ( empty( $cron_array ) || ! is_array( $cron_array ) ) {
            return false;
        }

        $events = array();

        foreach ( $cron_array as $timestamp => $hooks ) {
            if ( empty( $hooks[ self::DEMO_HOOK ] ) ) {
                continue;
            }

            foreach ( $hooks[ self::DEMO_HOOK ] as $instance ) {
                $event_args = isset( $instance['args'] ) ? (array) $instance['args'] : array();

                $events[] = array(
                    'timestamp' => (int) $timestamp,
                    'args'      => $event_args,
                    'schedule'  => isset( $instance['schedule'] ) ? $instance['schedule'] : '',
                );
            }
        }

        if ( empty( $events ) ) {
            return false;
        }

        usort(
            $events,
            function ( $a, $b ) {
                if ( $a['timestamp'] === $b['timestamp'] ) {
                    return 0;
                }

                return ( $a['timestamp'] < $b['timestamp'] ) ? -1 : 1;
            }
        );

        $keep = array_shift( $events );

        foreach ( $events as $event ) {
            wp_unschedule_event( $event['timestamp'], self::DEMO_HOOK, $event['args'] );
        }

        $has_valid_event = true;

        if ( $keep['timestamp'] < time() ) {
            wp_unschedule_event( $keep['timestamp'], self::DEMO_HOOK, $keep['args'] );
            $has_valid_event = false;
        } elseif ( ! empty( $keep['schedule'] ) ) {
            wp_unschedule_event( $keep['timestamp'], self::DEMO_HOOK, $keep['args'] );
            $has_valid_event = false;
        }

        return $has_valid_event;
    }

    /**
     * Schedules the demo event approximately six months in the future.
     *
     * @param array $args Demo event arguments.
     */
    private function schedule_demo_event( $args ) {
        $timestamp = time() + ( 6 * MONTH_IN_SECONDS );

        wp_schedule_single_event( $timestamp, self::DEMO_HOOK, $args );
    }

    /**
     * Handles the demo cron event when it runs.
     *
     * @param array $args Event arguments.
     */
    public function handle_demo_event( $args = array() ) {
        update_option(
            'teqcidb_demo_cron_last_run',
            array(
                'timestamp' => current_time( 'timestamp' ),
                'args'      => $args,
            )
        );
    }

    /**
     * Returns cron events created by the plugin.
     *
     * @return array
     */
    public static function get_plugin_cron_events() {
        $cron_array = _get_cron_array();
        $events     = array();

        if ( empty( $cron_array ) || ! is_array( $cron_array ) ) {
            return $events;
        }

        foreach ( $cron_array as $timestamp => $hooks ) {
            foreach ( $hooks as $hook => $instances ) {
                if ( 0 !== strpos( $hook, self::HOOK_PREFIX ) ) {
                    continue;
                }

                foreach ( $instances as $signature => $data ) {
                    $events[] = array(
                        'hook'      => $hook,
                        'timestamp' => (int) $timestamp,
                        'schedule'  => isset( $data['schedule'] ) ? $data['schedule'] : '',
                        'interval'  => isset( $data['interval'] ) ? (int) $data['interval'] : 0,
                        'args'      => isset( $data['args'] ) ? (array) $data['args'] : array(),
                        'signature' => $signature,
                    );
                }
            }
        }

        usort(
            $events,
            function ( $a, $b ) {
                if ( $a['timestamp'] === $b['timestamp'] ) {
                    return strcmp( $a['hook'], $b['hook'] );
                }

                return ( $a['timestamp'] < $b['timestamp'] ) ? -1 : 1;
            }
        );

        return $events;
    }

    /**
     * Provides metadata for known cron hooks.
     *
     * @return array
     */
    public static function get_known_hooks() {
        return array(
            self::DEMO_HOOK => array(
                'name'        => __( 'Demo Cron Event', 'teqcidb' ),
                'description' => __( 'Demonstrates how Thompson Engineering QCI Database cron jobs appear in the Cron Jobs tab.', 'teqcidb' ),
            ),
            self::EXPIRATION_DISPATCH_HOOK => array(
                'name'        => __( 'Expiration Reminder Dispatch', 'teqcidb' ),
                'description' => __( 'Runs daily to find students approaching expiration and schedule reminder email events.', 'teqcidb' ),
            ),
            self::EXPIRATION_SEND_HOOK => array(
                'name'        => __( 'Expiration Reminder Email Send', 'teqcidb' ),
                'description' => __( 'Sends a scheduled expiration reminder email for a specific student and template type.', 'teqcidb' ),
            ),
        );
    }

    /**
     * Retrieves display data for a cron hook.
     *
     * @param string $hook Hook name.
     *
     * @return array
     */
    public static function get_hook_display_data( $hook ) {
        $known = self::get_known_hooks();

        if ( isset( $known[ $hook ] ) ) {
            return $known[ $hook ];
        }

        $readable = trim( str_replace( array( '_', '-' ), ' ', $hook ) );
        $readable = preg_replace( '/^' . preg_quote( self::HOOK_PREFIX, '/' ) . '/i', '', $readable );
        $readable = ucwords( $readable );

        return array(
            'name'        => $readable,
            'description' => __( 'Cron event scheduled by Thompson Engineering QCI Database.', 'teqcidb' ),
        );
    }

    /**
     * Formats a timestamp for display.
     *
     * @param int $timestamp Unix timestamp.
     *
     * @return string
     */
    public static function format_timestamp( $timestamp ) {
        if ( empty( $timestamp ) ) {
            return __( 'Not scheduled', 'teqcidb' );
        }

        return wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
    }

    /**
     * Determines if the cron event is recurring.
     *
     * @param string $schedule Schedule slug.
     *
     * @return bool
     */
    public static function is_recurring( $schedule ) {
        return ! empty( $schedule );
    }

    /**
     * Returns the schedule label for the cron event.
     *
     * @param string $schedule Schedule slug.
     * @param int    $interval Interval in seconds.
     *
     * @return string
     */
    public static function get_schedule_label( $schedule, $interval ) {
        if ( empty( $schedule ) ) {
            return __( 'One-off event', 'teqcidb' );
        }

        $schedules = wp_get_schedules();

        if ( isset( $schedules[ $schedule ]['display'] ) ) {
            return $schedules[ $schedule ]['display'];
        }

        if ( $interval > 0 ) {
            return sprintf(
                /* translators: %s: number of seconds */
                __( 'Custom schedule (%s seconds)', 'teqcidb' ),
                number_format_i18n( $interval )
            );
        }

        return __( 'Recurring event', 'teqcidb' );
    }

    /**
     * Creates a human-friendly countdown string for a timestamp.
     *
     * @param int $timestamp Unix timestamp.
     *
     * @return string
     */
    public static function get_countdown( $timestamp ) {
        if ( empty( $timestamp ) ) {
            return __( 'Not scheduled', 'teqcidb' );
        }

        $now = current_time( 'timestamp' );
        $diff = $timestamp - $now;

        if ( 0 === $diff ) {
            return __( 'Running now', 'teqcidb' );
        }

        $direction = $diff > 0 ? 'until' : 'ago';
        $diff      = abs( $diff );

        if ( 0 === $diff ) {
            return __( 'Due now', 'teqcidb' );
        }

        $units = array(
            array( 'label' => _n_noop( '%s week', '%s weeks', 'teqcidb' ), 'seconds' => WEEK_IN_SECONDS ),
            array( 'label' => _n_noop( '%s day', '%s days', 'teqcidb' ), 'seconds' => DAY_IN_SECONDS ),
            array( 'label' => _n_noop( '%s hour', '%s hours', 'teqcidb' ), 'seconds' => HOUR_IN_SECONDS ),
            array( 'label' => _n_noop( '%s minute', '%s minutes', 'teqcidb' ), 'seconds' => MINUTE_IN_SECONDS ),
            array( 'label' => _n_noop( '%s second', '%s seconds', 'teqcidb' ), 'seconds' => 1 ),
        );

        $parts = array();

        foreach ( $units as $unit ) {
            if ( $diff < $unit['seconds'] ) {
                continue;
            }

            $value = floor( $diff / $unit['seconds'] );
            $diff -= $value * $unit['seconds'];

            if ( $value > 0 ) {
                $parts[] = sprintf( translate_nooped_plural( $unit['label'], $value, 'teqcidb' ), number_format_i18n( $value ) );
            }

            if ( count( $parts ) >= 3 ) {
                break;
            }
        }

        if ( empty( $parts ) ) {
            $parts[] = sprintf( _n( '%s second', '%s seconds', $diff, 'teqcidb' ), number_format_i18n( max( 1, $diff ) ) );
        }

        $glue      = _x( ', ', 'Countdown delimiter', 'teqcidb' );
        $countdown = implode( $glue, $parts );

        if ( 'until' === $direction ) {
            return sprintf(
                /* translators: %s: countdown string */
                __( 'In %s', 'teqcidb' ),
                $countdown
            );
        }

        return sprintf(
            /* translators: %s: countdown string */
            __( '%s ago', 'teqcidb' ),
            $countdown
        );
    }
}
