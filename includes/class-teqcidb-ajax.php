<?php
/**
 * Handle Ajax operations with configurable minimum execution time.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Ajax {

    const AUTHORIZENET_COMMUNICATOR_QUERY_VAR = 'teqcidb_authorizenet_communicator';
    const AUTHORIZENET_COMMUNICATOR_PATH      = 'teqcidb-authorize-communicator';
    const CLASS_PAGE_QUERY_VAR                = 'teqcidb_class_page';
    const CLASS_PAGE_PATH_PREFIX              = 'teqcidb-class';

    public function register() {
        add_action( 'wp_ajax_teqcidb_save_student', array( $this, 'save_student' ) );
        add_action( 'wp_ajax_nopriv_teqcidb_save_student', array( $this, 'save_student' ) );
        add_action( 'wp_ajax_teqcidb_login_user', array( $this, 'login_user' ) );
        add_action( 'wp_ajax_nopriv_teqcidb_login_user', array( $this, 'login_user' ) );
        add_action( 'wp_ajax_teqcidb_update_profile', array( $this, 'update_profile' ) );
        add_action( 'wp_ajax_teqcidb_save_class', array( $this, 'save_class' ) );
        add_action( 'wp_ajax_teqcidb_save_quiz_question', array( $this, 'save_quiz_question' ) );
        add_action( 'wp_ajax_teqcidb_save_studenthistory', array( $this, 'save_studenthistory' ) );
        add_action( 'wp_ajax_teqcidb_create_studenthistory', array( $this, 'create_studenthistory' ) );
        add_action( 'wp_ajax_teqcidb_delete_student', array( $this, 'delete_student' ) );
        add_action( 'wp_ajax_teqcidb_delete_studenthistory', array( $this, 'delete_studenthistory' ) );
        add_action( 'wp_ajax_teqcidb_read_student', array( $this, 'read_student' ) );
        add_action( 'wp_ajax_teqcidb_read_class', array( $this, 'read_class' ) );
        add_action( 'wp_ajax_teqcidb_save_general_settings', array( $this, 'save_general_settings' ) );
        add_action( 'wp_ajax_teqcidb_save_api_settings', array( $this, 'save_api_settings' ) );
        add_action( 'wp_ajax_teqcidb_upload_legacy_student', array( $this, 'upload_legacy_records' ) );
        add_action( 'wp_ajax_teqcidb_upload_legacy_records', array( $this, 'upload_legacy_records' ) );
        add_action( 'wp_ajax_teqcidb_save_email_template', array( $this, 'save_email_template' ) );
        add_action( 'wp_ajax_teqcidb_send_test_email', array( $this, 'send_test_email' ) );
        add_action( 'wp_ajax_teqcidb_clear_email_log', array( $this, 'clear_email_log' ) );
        add_action( 'wp_ajax_teqcidb_clear_error_log', array( $this, 'clear_error_log' ) );
        add_action( 'wp_ajax_teqcidb_download_error_log', array( $this, 'download_error_log' ) );
        add_action( 'wp_ajax_teqcidb_search_students', array( $this, 'search_students' ) );
        add_action( 'wp_ajax_teqcidb_assign_student_representative', array( $this, 'assign_student_representative' ) );
        add_action( 'wp_ajax_teqcidb_get_accept_hosted_token', array( $this, 'get_accept_hosted_token' ) );
        add_action( 'wp_ajax_teqcidb_record_registration_payment', array( $this, 'record_registration_payment' ) );
        add_action( 'init', array( __CLASS__, 'register_authorizenet_communicator_rewrite' ) );
        add_action( 'init', array( __CLASS__, 'register_class_page_rewrite' ) );
        add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_authorizenet_communicator' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_class_page' ) );
        add_action( 'wp_ajax_nopriv_teqcidb_authorizenet_iframe_communicator', array( $this, 'authorizenet_iframe_communicator' ) );
        add_action( 'wp_ajax_teqcidb_authorizenet_iframe_communicator', array( $this, 'authorizenet_iframe_communicator' ) );
    }


    /**
     * Register rewrite support for the public Authorize.Net communicator endpoint.
     */
    public static function register_authorizenet_communicator_rewrite() {
        add_rewrite_tag( '%' . self::AUTHORIZENET_COMMUNICATOR_QUERY_VAR . '%', '1' );
        add_rewrite_rule(
            '^' . self::AUTHORIZENET_COMMUNICATOR_PATH . '/?$',
            'index.php?' . self::AUTHORIZENET_COMMUNICATOR_QUERY_VAR . '=1',
            'top'
        );
    }

    /**
     * Register rewrite support for lightweight class pages.
     */
    public static function register_class_page_rewrite() {
        add_rewrite_tag( '%' . self::CLASS_PAGE_QUERY_VAR . '%', '([^&]+)' );
        add_rewrite_rule(
            '^' . self::CLASS_PAGE_PATH_PREFIX . '/([^/]+)/?$',
            'index.php?' . self::CLASS_PAGE_QUERY_VAR . '=$matches[1]',
            'top'
        );
    }

    /**
     * Register custom query vars.
     *
     * @param array<int, string> $vars Existing vars.
     *
     * @return array<int, string>
     */
    public function register_query_vars( $vars ) {
        $vars[] = self::AUTHORIZENET_COMMUNICATOR_QUERY_VAR;
        $vars[] = self::CLASS_PAGE_QUERY_VAR;

        return $vars;
    }

    /**
     * Retrieve the public communicator URL.
     *
     * @return string
     */
    public static function get_authorizenet_communicator_url() {
        return home_url( '/' . self::AUTHORIZENET_COMMUNICATOR_PATH . '/' );
    }

    /**
     * Output communicator markup when the custom route is requested.
     */
    public function maybe_render_authorizenet_communicator() {
        if ( '1' !== (string) get_query_var( self::AUTHORIZENET_COMMUNICATOR_QUERY_VAR ) ) {
            return;
        }

        $this->authorizenet_iframe_communicator();
    }

    /**
     * Output lightweight class page markup when the class route is requested.
     */
    public function maybe_render_class_page() {
        $route_token = get_query_var( self::CLASS_PAGE_QUERY_VAR, '' );

        if ( '' === $route_token ) {
            return;
        }

        $route_token = sanitize_title( $route_token );

        if ( '' === $route_token ) {
            status_header( 404 );
            nocache_headers();
            exit;
        }

        global $wpdb;

        $table     = $wpdb->prefix . 'teqcidb_classes';
        $class_url = $this->generate_class_page_relative_url( $route_token );
        $class_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, classname, uniqueclassid FROM $table WHERE classurl = %s LIMIT 1",
                $class_url
            ),
            ARRAY_A
        );

        if ( empty( $class_row ) ) {
            status_header( 404 );
            nocache_headers();
            exit;
        }

        status_header( 200 );
        nocache_headers();
        header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );

        $class_name            = isset( $class_row['classname'] ) ? sanitize_text_field( $class_row['classname'] ) : '';
        $class_id              = isset( $class_row['id'] ) ? absint( $class_row['id'] ) : 0;
        $class_page_stylesheet = TEQCIDB_PLUGIN_URL . 'assets/css/shortcodes/class-page.css';

        echo '<!doctype html><html><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" /><title>' . esc_html__( 'Class Page', 'teqcidb' ) . '</title><link rel="stylesheet" href="' . esc_url( $class_page_stylesheet ) . '" /></head><body class="teqcidb-class-route">';
        echo '<main class="teqcidb-class-route__main">';
        echo '<header class="teqcidb-class-route__header">';
        echo '<h1 class="teqcidb-class-route__title">' . esc_html__( 'Class Page', 'teqcidb' ) . '</h1>';

        if ( '' !== $class_name ) {
            echo '<p class="teqcidb-class-route__subtitle">' . esc_html( $class_name ) . '</p>';
        }

        echo '</header>';

        if ( ! is_user_logged_in() ) {
            $request_uri       = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : $class_url;
            $redirect_url      = esc_url_raw( home_url( $request_uri ) );
            $login_action_url  = wp_login_url( $redirect_url );
            $lost_password_url = wp_lostpassword_url( $redirect_url );

            echo '<article class="teqcidb-auth-card">';
            echo '<h2 class="teqcidb-auth-title">' . esc_html__( 'Already a registered QCI Student or Alternate Contact/Representative? Log in below!', 'teqcidb' ) . '</h2>';
            echo '<p class="teqcidb-auth-description">' . esc_html__( 'Log in below to access this class page and continue to your quiz and class resources.', 'teqcidb' ) . '</p>';
            echo '<form class="teqcidb-login-form" method="post" action="' . esc_url( $login_action_url ) . '">';
            echo '<div class="teqcidb-form-field">';
            echo '<label for="teqcidb-login-username">' . esc_html__( 'Username or Email Address', 'teqcidb' ) . '</label>';
            echo '<input type="text" id="teqcidb-login-username" name="log" autocomplete="username" placeholder="' . esc_attr__( 'Your username or email', 'teqcidb' ) . '" required />';
            echo '</div>';
            echo '<div class="teqcidb-form-field">';
            echo '<label for="teqcidb-login-password">' . esc_html__( 'Password', 'teqcidb' ) . '</label>';
            echo '<input type="password" id="teqcidb-login-password" name="pwd" autocomplete="current-password" placeholder="' . esc_attr__( 'Your password', 'teqcidb' ) . '" required />';
            echo '</div>';
            echo '<div class="teqcidb-form-field teqcidb-form-checkbox">';
            echo '<label for="teqcidb-login-remember">';
            echo '<input type="checkbox" id="teqcidb-login-remember" name="rememberme" value="forever" />';
            echo '<span>' . esc_html__( 'Remember me', 'teqcidb' ) . '</span>';
            echo '</label>';
            echo '</div>';
            echo '<input type="hidden" name="redirect_to" value="' . esc_attr( $redirect_url ) . '" />';
            echo '<button class="teqcidb-button teqcidb-button-primary" type="submit">' . esc_html__( 'Log In', 'teqcidb' ) . '</button>';
            echo '<p><a class="teqcidb-auth-link" href="' . esc_url( $lost_password_url ) . '">' . esc_html__( 'Forgot your password? Reset it here!', 'teqcidb' ) . '</a></p>';
            echo '</form>';
            echo '</article>';
            echo '</main></body></html>';
            exit;
        }

        $feedback_message = __( 'Welcome! Please wait for your QCI instructor to enable the Quiz below or tell you that you may start your quiz.', 'teqcidb' );
        $current_user_id  = get_current_user_id();

        if ( $class_id > 0 && $current_user_id > 0 ) {
            $quizzes_table  = $wpdb->prefix . 'teqcidb_quizzes';
            $attempts_table = $wpdb->prefix . 'teqcidb_quiz_attempts';
            $students_table = $wpdb->prefix . 'teqcidb_students';

            $quiz_id = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $quizzes_table WHERE FIND_IN_SET( CAST( %d AS CHAR ), REPLACE( class_id, ' ', '' ) ) > 0 ORDER BY updated_at DESC, id DESC LIMIT 1",
                    $class_id
                )
            );

            if ( $quiz_id > 0 ) {
                $attempt = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT status, updated_at FROM $attempts_table WHERE quiz_id = %d AND user_id = %d ORDER BY updated_at DESC, id DESC LIMIT 1",
                        $quiz_id,
                        $current_user_id
                    ),
                    ARRAY_A
                );

                if ( is_array( $attempt ) && isset( $attempt['status'] ) ) {
                    $attempt_status = (int) $attempt['status'];

                    if ( 1 === $attempt_status ) {
                        $feedback_message = __( 'Whoops - it looks like you\'ve failed this class! Please contact <a href="tel:2516662443">Ilka Porter at (251) 666-2443</a> or <a href="mailto:QCI@thompsonengineering.com">QCI@thompsonengineering.com</a> for further instructions.', 'teqcidb' );
                    } elseif ( 0 === $attempt_status ) {
                        $qci_number = (string) $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT qcinumber FROM $students_table WHERE wpuserid = %d ORDER BY id DESC LIMIT 1",
                                $current_user_id
                            )
                        );

                        $dashboard_url = $this->get_student_dashboard_certificates_url();
                        $feedback_message = sprintf(
                            /* translators: 1: opening anchor tag to student dashboard certificates tab, 2: closing anchor tag, 3: student QCI number. */
                            __( 'Congratulations! Looks like you\'ve passed this class! Please %1$svisit your QCI Dashboard%2$s for resources and information such as your QCI Certificate, Wallet Card, and important QCI expiration dates. Your QCI Number is: <strong>%3$s</strong>.', 'teqcidb' ),
                            '<a href="' . esc_url( $dashboard_url ) . '">',
                            '</a>',
                            esc_html( '' !== $qci_number ? $qci_number : __( 'Not available', 'teqcidb' ) )
                        );
                    } elseif ( 2 === $attempt_status ) {
                        $elapsed_since_save = $this->format_elapsed_duration_from_datetime( isset( $attempt['updated_at'] ) ? $attempt['updated_at'] : '' );
                        $feedback_message   = sprintf(
                            /* translators: %s: elapsed time since last quiz save. */
                            __( 'Welcome back! Looks like you\'ve already started this quiz. The last save was %s.', 'teqcidb' ),
                            $elapsed_since_save
                        );
                    }
                }
            }
        }

        $allowed_feedback_html = array(
            'a'      => array(
                'href' => array(),
            ),
            'strong' => array(),
        );

        echo '<section class="teqcidb-class-route__panel">';
        echo '<h2 class="teqcidb-class-route__section-title">' . esc_html__( 'Welcome to Your Class Session', 'teqcidb' ) . '</h2>';
        echo '<p class="teqcidb-class-route__section-description">' . esc_html__( 'You are logged in. Class quiz and resources content will appear here in upcoming updates.', 'teqcidb' ) . '</p>';
        echo '<div class="teqcidb-class-route__feedback">' . wp_kses( $feedback_message, $allowed_feedback_html ) . '</div>';
        echo '</section>';

        echo '<section class="teqcidb-class-route__quiz">';
        echo '<h2 class="teqcidb-class-route__section-title">' . esc_html__( 'Class Quiz', 'teqcidb' ) . '</h2>';
        echo '<p class="teqcidb-class-route__section-description">' . esc_html__( 'Quiz instructions and questions will be rendered in this section.', 'teqcidb' ) . '</p>';
        echo '<ul class="teqcidb-class-route__quiz-list"><li class="teqcidb-class-route__quiz-item">' . esc_html__( 'Quiz content coming soon.', 'teqcidb' ) . '</li></ul>';
        echo '</section>';

        echo '<section class="teqcidb-class-route__resources">';
        echo '<h2 class="teqcidb-class-route__section-title">' . esc_html__( 'Class Resources', 'teqcidb' ) . '</h2>';
        echo '<p class="teqcidb-class-route__section-description">' . esc_html__( 'Resources assigned to this class will be listed here.', 'teqcidb' ) . '</p>';
        echo '<ul class="teqcidb-class-route__resource-list"><li class="teqcidb-class-route__resource-item">' . esc_html__( 'No resources loaded yet.', 'teqcidb' ) . '</li></ul>';
        echo '</section>';
        echo '</main></body></html>';
        exit;
    }


    private function format_elapsed_duration_from_datetime( $datetime_value ) {
        $timestamp = strtotime( (string) $datetime_value );

        if ( ! $timestamp ) {
            return __( 'just now', 'teqcidb' );
        }

        $now_timestamp = current_time( 'timestamp' );

        if ( $now_timestamp <= $timestamp ) {
            return __( 'just now', 'teqcidb' );
        }

        $start = new DateTimeImmutable( '@' . $timestamp );
        $end   = new DateTimeImmutable( '@' . $now_timestamp );
        $diff  = $start->diff( $end );

        $months  = ( $diff->y * 12 ) + $diff->m;
        $weeks   = intdiv( (int) $diff->d, 7 );
        $days    = (int) $diff->d % 7;
        $hours   = (int) $diff->h;
        $minutes = (int) $diff->i;
        $seconds = (int) $diff->s;

        return sprintf(
            /* translators: 1: months, 2: weeks, 3: days, 4: hours, 5: minutes, 6: seconds elapsed since last save. */
            __( '%1$s Months, %2$s Weeks, %3$s Days, %4$s Hours, %5$s Minutes, %6$s Seconds ago', 'teqcidb' ),
            $months,
            $weeks,
            $days,
            $hours,
            $minutes,
            $seconds
        );
    }

    private function get_student_dashboard_certificates_url() {
        global $wpdb;

        $base_url      = home_url( '/' );
        $posts_table   = $wpdb->posts;
        $shortcode_tag = '[' . TEQCIDB_Shortcode_Student_Dashboard::SHORTCODE_TAG;

        $post_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM $posts_table WHERE post_status = %s AND post_type IN ('page','post') AND post_content LIKE %s ORDER BY ID ASC LIMIT 1",
                'publish',
                '%' . $wpdb->esc_like( $shortcode_tag ) . '%'
            )
        );

        if ( $post_id > 0 ) {
            $permalink = get_permalink( $post_id );

            if ( is_string( $permalink ) && '' !== $permalink ) {
                $base_url = $permalink;
            }
        }

        return add_query_arg( 'tab', 'certificates-dates', $base_url );
    }

    private function maybe_delay( $start, $minimum_time = TEQCIDB_MIN_EXECUTION_TIME ) {
        if ( $minimum_time <= 0 ) {
            return;
        }

        $elapsed = microtime( true ) - $start;

        if ( $elapsed < $minimum_time ) {
            $remaining    = $minimum_time - $elapsed;
            $microseconds = (int) ceil( max( 0, $remaining ) * 1000000 );

            if ( $microseconds > 0 ) {
                usleep( $microseconds );
            }
        }
    }


    /**
     * Create an Accept Hosted payment token for a selected class.
     */
    public function get_accept_hosted_token() {
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Please log in before starting checkout.', 'teqcidb' ),
                )
            );
        }

        $class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;

        if ( $class_id <= 0 ) {
            wp_send_json_error(
                array(
                    'message' => __( 'The selected class could not be found.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'teqcidb_classes';
        $row        = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, classname, classcost, classstartdate, classhide FROM $table_name WHERE id = %d",
                $class_id
            ),
            ARRAY_A
        );

        if ( ! is_array( $row ) || ( isset( $row['classhide'] ) && 1 === (int) $row['classhide'] ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'The selected class is not available for registration.', 'teqcidb' ),
                )
            );
        }

        $raw_cost = isset( $row['classcost'] ) ? (string) $row['classcost'] : '';
        $amount   = (float) preg_replace( '/[^0-9.]/', '', $raw_cost );

        if ( $amount <= 0 ) {
            wp_send_json_error(
                array(
                    'message' => __( 'This class does not have a valid payment amount configured.', 'teqcidb' ),
                )
            );
        }

        $current_user = wp_get_current_user();

        $class_fragment  = strtoupper( substr( base_convert( (string) $class_id, 10, 36 ), -3 ) );
        $user_fragment   = strtoupper( substr( base_convert( (string) get_current_user_id(), 10, 36 ), -3 ) );
        $time_fragment   = strtoupper( base_convert( (string) time(), 10, 36 ) );
        $random_fragment = strtoupper( wp_generate_password( 4, false, false ) );
        $invoice_number  = substr( 'TQ' . $class_fragment . $user_fragment . $time_fragment . $random_fragment, 0, 20 );

        $service = new TEQCIDB_AuthorizeNet_Service();
        $token   = $service->create_accept_hosted_token(
            array(
                'amount'         => $amount,
                'invoice_number' => $invoice_number,
                'description'    => isset( $row['classname'] ) ? (string) $row['classname'] : '',
                'first_name'     => $current_user instanceof WP_User ? (string) $current_user->first_name : '',
                'last_name'      => $current_user instanceof WP_User ? (string) $current_user->last_name : '',
                'email'          => $current_user instanceof WP_User ? (string) $current_user->user_email : '',
                'customer_id'    => (string) get_current_user_id(),
            )
        );

        if ( is_wp_error( $token ) ) {
            wp_send_json_error(
                array(
                    'message' => $token->get_error_message(),
                )
            );
        }

        wp_send_json_success(
            array(
                'token'      => isset( $token['token'] ) ? (string) $token['token'] : '',
                'postUrl'    => isset( $token['post_url'] ) ? esc_url_raw( $token['post_url'] ) : '',
                'classId'    => $class_id,
            )
        );
    }


    /**
     * Ensure the payment history table exists and contains expected columns.
     */
    private function ensure_payment_history_table_schema() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name      = $wpdb->prefix . 'teqcidb_paymenthistory';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wpuserid bigint(20) unsigned DEFAULT NULL,
            uniquestudentid varchar(255) NOT NULL DEFAULT '',
            email varchar(191) NOT NULL DEFAULT '',
            uniqueclassid varchar(255) NOT NULL DEFAULT '',
            totalpaid decimal(10,2) DEFAULT NULL,
            transid varchar(100) NOT NULL DEFAULT '',
            transtime varchar(80) NOT NULL DEFAULT '',
            multiplestudents longtext,
            invoicenumber varchar(50) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY wpuserid (wpuserid),
            KEY uniquestudentid (uniquestudentid),
            KEY uniqueclassid (uniqueclassid),
            KEY transid (transid),
            KEY invoicenumber (invoicenumber)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Format payment transaction time in U.S. Eastern time.
     *
     * @param string $gateway_datetime_raw Raw gateway datetime value.
     *
     * @return string
     */
    private function format_payment_history_time( $gateway_datetime_raw ) {
        $timezone = new DateTimeZone( 'America/New_York' );

        if ( is_string( $gateway_datetime_raw ) && '' !== trim( $gateway_datetime_raw ) ) {
            try {
                $date = new DateTimeImmutable( trim( $gateway_datetime_raw ), new DateTimeZone( 'UTC' ) );
                $date = $date->setTimezone( $timezone );
            } catch ( Exception $exception ) {
                $date = new DateTimeImmutable( 'now', $timezone );
            }
        } else {
            $date = new DateTimeImmutable( 'now', $timezone );
        }

        $day = (int) $date->format( 'j' );

        if ( $day >= 11 && $day <= 13 ) {
            $suffix = 'th';
        } else {
            switch ( $day % 10 ) {
                case 1:
                    $suffix = 'st';
                    break;
                case 2:
                    $suffix = 'nd';
                    break;
                case 3:
                    $suffix = 'rd';
                    break;
                default:
                    $suffix = 'th';
                    break;
            }
        }

        $month = strtolower( $date->format( 'M.' ) );

        return sprintf(
            '%1$s %2$d%3$s, %4$s %5$s',
            $month,
            $day,
            $suffix,
            $date->format( 'Y' ),
            $date->format( 'g:i:s a' )
        );
    }

    /**
     * Record a successful registration payment in teqcidb_paymenthistory.
     */
    public function record_registration_payment() {
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Please log in before saving payment history.', 'teqcidb' ),
                )
            );
        }

        $this->ensure_payment_history_table_schema();

        $user_id        = get_current_user_id();
        $class_id       = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;
        $total_paid_raw = isset( $_POST['total_paid'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['total_paid'] ) ) : '';
        $trans_id       = isset( $_POST['trans_id'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['trans_id'] ) ) : '';
        $invoice_number = isset( $_POST['invoice_number'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['invoice_number'] ) ) : '';
        $gateway_time   = isset( $_POST['gateway_datetime'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['gateway_datetime'] ) ) : '';
        $multiple_raw   = isset( $_POST['multiple_students'] ) ? wp_unslash( (string) $_POST['multiple_students'] ) : '';

        global $wpdb;

        $students_table = $wpdb->prefix . 'teqcidb_students';
        $classes_table  = $wpdb->prefix . 'teqcidb_classes';
        $history_table  = $wpdb->prefix . 'teqcidb_paymenthistory';

        $student_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT uniquestudentid, is_a_representative, email FROM $students_table WHERE wpuserid = %d LIMIT 1",
                $user_id
            ),
            ARRAY_A
        );

        $uniquestudentid   = is_array( $student_row ) && ! empty( $student_row['uniquestudentid'] ) ? sanitize_text_field( (string) $student_row['uniquestudentid'] ) : '';
        $is_representative = is_array( $student_row ) && ! empty( $student_row['is_a_representative'] );
        $email             = is_array( $student_row ) && ! empty( $student_row['email'] ) ? sanitize_email( (string) $student_row['email'] ) : '';

        if ( '' === $email ) {
            $current_user = wp_get_current_user();
            $email        = $current_user instanceof WP_User ? sanitize_email( (string) $current_user->user_email ) : '';
        }

        $unique_class_id = '';
        $class_row       = null;

        if ( $class_id > 0 ) {
            $class_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT uniqueclassid, classname FROM $classes_table WHERE id = %d LIMIT 1",
                    $class_id
                ),
                ARRAY_A
            );

            if ( is_array( $class_row ) && ! empty( $class_row['uniqueclassid'] ) ) {
                $unique_class_id = sanitize_text_field( (string) $class_row['uniqueclassid'] );
            }
        }

        $class_name = is_array( $class_row ) && ! empty( $class_row['classname'] ) ? sanitize_text_field( (string) $class_row['classname'] ) : '';

        $amount_numeric = (float) preg_replace( '/[^0-9.\-]/', '', $total_paid_raw );
        $total_paid     = number_format( $amount_numeric, 2, '.', '' );

        $multiple_students = '';

        if ( $is_representative && '' !== $multiple_raw ) {
            $decoded = json_decode( $multiple_raw, true );

            if ( is_array( $decoded ) ) {
                $normalized = array();

                foreach ( $decoded as $entry ) {
                    if ( ! is_array( $entry ) ) {
                        continue;
                    }

                    $normalized[] = array(
                        'wpid'            => isset( $entry['wpid'] ) ? absint( $entry['wpid'] ) : 0,
                        'uniquestudentid' => isset( $entry['uniquestudentid'] ) ? sanitize_text_field( (string) $entry['uniquestudentid'] ) : '',
                    );
                }

                if ( ! empty( $normalized ) ) {
                    $multiple_students = wp_json_encode( $normalized );
                }
            }
        }

        $inserted = $wpdb->insert(
            $history_table,
            array(
                'wpuserid'        => $user_id,
                'uniquestudentid' => $uniquestudentid,
                'email'           => $email,
                'uniqueclassid'   => $unique_class_id,
                'totalpaid'       => $total_paid,
                'transid'         => $trans_id,
                'transtime'       => $this->format_payment_history_time( $gateway_time ),
                'multiplestudents' => $multiple_students,
                'invoicenumber'   => $invoice_number,
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );

        if ( false === $inserted ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to store payment history right now.', 'teqcidb' ),
                )
            );
        }

        $payment_history_id = (int) $wpdb->insert_id;

        $eastern_timezone = new DateTimeZone( 'America/New_York' );
        $enrollment_date  = '';

        if ( '' !== $gateway_time ) {
            try {
                $gateway_datetime = new DateTimeImmutable( trim( $gateway_time ), new DateTimeZone( 'UTC' ) );
                $enrollment_date  = $gateway_datetime->setTimezone( $eastern_timezone )->format( 'Y-m-d' );
            } catch ( Exception $exception ) {
                $enrollment_date = '';
            }
        }

        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $enrollment_date ) ) {
            $enrollment_date = ( new DateTimeImmutable( 'now', $eastern_timezone ) )->format( 'Y-m-d' );
        }

        $student_history_table = $wpdb->prefix . 'teqcidb_studenthistory';

        $student_history_inserted = $wpdb->insert(
            $student_history_table,
            array(
                'uniquestudentid'  => $uniquestudentid,
                'wpuserid'         => $user_id,
                'classname'        => $class_name,
                'uniqueclassid'    => $unique_class_id,
                'registered'       => 'Yes',
                'attended'         => 'Upcoming',
                'outcome'          => 'Upcoming',
                'paymentstatus'    => 'Paid in Full',
                'amountpaid'       => $total_paid,
                'enrollmentdate'   => $enrollment_date,
                'registeredby'     => $user_id,
                'courseinprogress' => 'no',
                'quizinprogress'   => 'no',
            ),
            array(
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
            )
        );

        if ( false === $student_history_inserted ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Payment was captured, but student history could not be saved.', 'teqcidb' ),
                )
            );
        }

        wp_send_json_success(
            array(
                'id' => $payment_history_id,
            )
        );
    }

    /**
     * Output the Authorize.Net Accept Hosted iframe communicator page.
     */
    public function authorizenet_iframe_communicator() {
        status_header( 200 );
        nocache_headers();
        header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );

        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Iframe Communicator</title>
    <script type="text/javascript">
        //<![CDATA[
            function callParentFunction(str) {
                if (str && str.length > 0 
                    && window.parent 
                    && window.parent.parent
                    && window.parent.parent.AuthorizeNetIFrame 
                    && window.parent.parent.AuthorizeNetIFrame.onReceiveCommunication)
                    {
                        // Errors indicate a mismatch in domain between the page containing the iframe and this page.
                        window.parent.parent.AuthorizeNetIFrame.onReceiveCommunication(str);
                    }
                }

            function receiveMessage(event) {
                if (event && event.data) {
                    callParentFunction(event.data);
                    }
                }

                if (window.addEventListener) {
                    window.addEventListener("message", receiveMessage, false);
                    } else if (window.attachEvent) {
                        window.attachEvent("onmessage", receiveMessage);
                    }

                if (window.location.hash && window.location.hash.length > 1) {
                    callParentFunction(window.location.hash.substring(1));
                    }
        //]]/>
    </script>
</head>
<body>
</body>
</html>

        <?php
        exit;
    }

    public function save_student() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        global $wpdb;

        $table = $wpdb->prefix . 'teqcidb_students';
        $id    = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
        $creating_new_student = ( 0 === $id );

        $first_name = $this->sanitize_text_value( 'first_name' );
        $last_name  = $this->sanitize_text_value( 'last_name' );
        $email_provided = isset( $_POST['email'] );
        $email          = $email_provided ? $this->sanitize_email_value( 'email' ) : '';

        if ( $creating_new_student && '' === $email ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please provide a valid email address.', 'teqcidb' ),
                )
            );
        }

        if ( ! $creating_new_student && $email_provided && '' === $email ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please provide a valid email address.', 'teqcidb' ),
                )
            );
        }

        $new_wp_user_id       = 0;
        $password             = $this->sanitize_text_value( 'password' );
        $verify_password      = $this->sanitize_text_value( 'verify_password' );

        if ( $creating_new_student ) {
            $existing_user = get_user_by( 'email', $email );

            if ( $existing_user ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => sprintf(
                            /* translators: %s: submitted email address. */
                            __( 'Whoops! Looks like a student with the email address of %s already exists! Please try creating an account with a different email address.', 'teqcidb' ),
                            $email
                        ),
                    )
                );
            }

            $display_name = trim( $first_name . ' ' . $last_name );

            if ( '' === $display_name ) {
                $display_name = $email;
            }

            $user_login = $this->generate_user_login( $email );
            $user_pass  = $password;

            if ( '' === $user_pass ) {
                $user_pass = wp_generate_password( 20, true, true );
            } elseif ( $password !== $verify_password ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'The passwords do not match.', 'teqcidb' ),
                    )
                );
            } elseif ( ! $this->is_strong_password( $user_pass ) ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'Your password must be at least 12 characters long and include uppercase and lowercase letters, a number, and a symbol.', 'teqcidb' ),
                    )
                );
            }

            $user_args  = array(
                'user_login'   => $user_login,
                'user_pass'    => $user_pass,
                'user_email'   => $email,
                'first_name'   => $first_name,
                'last_name'    => $last_name,
                'display_name' => $display_name,
            );

            $new_wp_user_id = wp_insert_user( $user_args );

            if ( is_wp_error( $new_wp_user_id ) ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => sprintf(
                            /* translators: %s: WordPress error message. */
                            __( 'Unable to create WordPress user: %s', 'teqcidb' ),
                            $new_wp_user_id->get_error_message()
                        ),
                    )
                );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_set_current_user( $new_wp_user_id );
                wp_set_auth_cookie( $new_wp_user_id, true );
            }
        }

        $association_options = array( 'AAPA', 'ARBA', 'AGC', 'ABC', 'AUCA' );

        $data = array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
        );

        if ( $creating_new_student || $email_provided ) {
            $data['email'] = $email;
        }

        if ( $creating_new_student || isset( $_POST['company'] ) ) {
            $data['company'] = $this->sanitize_text_value( 'company' );
        }

        if ( $creating_new_student || isset( $_POST['old_companies'] ) ) {
            $data['old_companies'] = $this->sanitize_items_value( 'old_companies' );
        }

        $address_keys = array(
            'student_address_street_1',
            'student_address_street_2',
            'student_address_city',
            'student_address_state',
            'student_address_postal_code',
        );
        $has_address_updates = false;

        foreach ( $address_keys as $address_key ) {
            if ( isset( $_POST[ $address_key ] ) ) {
                $has_address_updates = true;
                break;
            }
        }

        if ( $creating_new_student || $has_address_updates ) {
            $data['student_address'] = $this->sanitize_student_address();
        }

        if ( $creating_new_student || isset( $_POST['phone_cell'] ) ) {
            $data['phone_cell'] = $this->sanitize_phone_value( 'phone_cell' );
        }

        if ( $creating_new_student || isset( $_POST['phone_office'] ) ) {
            $data['phone_office'] = $this->sanitize_phone_value( 'phone_office' );
        }

        if ( $creating_new_student || isset( $_POST['fax'] ) ) {
            $data['fax'] = $this->sanitize_phone_value( 'fax' );
        }

        if ( $creating_new_student || isset( $_POST['initial_training_date'] ) ) {
            $data['initial_training_date'] = $this->sanitize_date_value( 'initial_training_date' );
        }

        if ( $creating_new_student || isset( $_POST['last_refresher_date'] ) ) {
            $data['last_refresher_date'] = $this->sanitize_date_value( 'last_refresher_date' );
        }

        if ( $creating_new_student || isset( $_POST['is_a_representative'] ) ) {
            $data['is_a_representative'] = $this->sanitize_yes_no_value( 'is_a_representative' );
        }

        $representative_keys = array(
            'representative_first_name',
            'representative_last_name',
            'representative_email',
            'representative_phone',
        );
        $has_representative_updates = false;

        foreach ( $representative_keys as $representative_key ) {
            if ( isset( $_POST[ $representative_key ] ) ) {
                $has_representative_updates = true;
                break;
            }
        }

        if ( $creating_new_student || $has_representative_updates ) {
            $data['their_representative'] = $this->sanitize_representative_contact();
        }

        if ( $creating_new_student || isset( $_POST['associations'] ) ) {
            $data['associations'] = $this->sanitize_associations_value( 'associations', $association_options );
        }

        if ( $creating_new_student || isset( $_POST['expiration_date'] ) ) {
            $data['expiration_date'] = $this->sanitize_date_value( 'expiration_date' );
        }

        if ( $creating_new_student || isset( $_POST['qcinumber'] ) ) {
            $data['qcinumber'] = $this->sanitize_text_value( 'qcinumber' );
        }

        if ( $creating_new_student || isset( $_POST['comments'] ) ) {
            $data['comments'] = $this->sanitize_textarea_value( 'comments' );
        }

        if ( $creating_new_student ) {
            $data['wpuserid']        = (string) $new_wp_user_id;
            $data['uniquestudentid'] = $this->generate_unique_student_id( $email );
        }

        $formats = array_fill( 0, count( $data ), '%s' );

        if ( $id > 0 ) {
            $result  = $wpdb->update( $table, $data, array( 'id' => $id ), $formats, array( '%d' ) );
            $message = __( 'Changes saved.', 'teqcidb' );

            if ( false === $result && $wpdb->last_error ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'Unable to save changes. Please try again.', 'teqcidb' ),
                    )
                );
            }
        } else {
            $result  = $wpdb->insert( $table, $data, $formats );
            $message = __( 'Saved', 'teqcidb' );

            if ( false === $result ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'Unable to save the record. Please try again.', 'teqcidb' ),
                    )
                );
            }
        }

        $unique_student_id = '';
        $wp_user_id = null;

        if ( $creating_new_student ) {
            $unique_student_id = isset( $data['uniquestudentid'] ) ? $data['uniquestudentid'] : '';
            $wp_user_id = isset( $data['wpuserid'] ) ? absint( $data['wpuserid'] ) : null;
        } else {
            $unique_student_id = (string) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT uniquestudentid FROM $table WHERE id = %d",
                    $id
                )
            );
            $wp_user_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT wpuserid FROM $table WHERE id = %d",
                    $id
                )
            );
            $wp_user_id = $wp_user_id ? absint( $wp_user_id ) : null;
        }

        $history_data = isset( $_POST['studenthistory'] ) ? wp_unslash( $_POST['studenthistory'] ) : array();

        if ( is_array( $history_data ) && $unique_student_id ) {
            $history_table = $wpdb->prefix . 'teqcidb_studenthistory';
            $class_table   = $wpdb->prefix . 'teqcidb_classes';

            foreach ( $history_data as $history_key => $entry ) {
                if ( ! is_array( $entry ) ) {
                    continue;
                }

                $history_id = isset( $entry['id'] ) ? absint( $entry['id'] ) : 0;
                $classname = isset( $entry['classname'] ) ? sanitize_text_field( (string) $entry['classname'] ) : '';
                $registered = isset( $entry['registered'] ) ? sanitize_text_field( (string) $entry['registered'] ) : '';
                $adminapproved = isset( $entry['adminapproved'] ) ? sanitize_text_field( (string) $entry['adminapproved'] ) : '';
                $attended = isset( $entry['attended'] ) ? sanitize_text_field( (string) $entry['attended'] ) : '';
                $outcome = isset( $entry['outcome'] ) ? sanitize_text_field( (string) $entry['outcome'] ) : '';
                $paymentstatus = isset( $entry['paymentstatus'] ) ? sanitize_text_field( (string) $entry['paymentstatus'] ) : '';
                $courseinprogress = isset( $entry['courseinprogress'] ) ? sanitize_text_field( (string) $entry['courseinprogress'] ) : '';
                $quizinprogress = isset( $entry['quizinprogress'] ) ? sanitize_text_field( (string) $entry['quizinprogress'] ) : '';
                $enrollmentdate = isset( $entry['enrollmentdate'] ) ? sanitize_text_field( (string) $entry['enrollmentdate'] ) : '';
                $amountpaid = isset( $entry['amountpaid'] ) ? sanitize_text_field( (string) $entry['amountpaid'] ) : '';
                $amountpaid = str_replace( array( '$', ',' ), '', $amountpaid );

                $amount_value = null;

                if ( '' !== $amountpaid && is_numeric( $amountpaid ) ) {
                    $amount_value = (float) $amountpaid;
                }

                $entry_unique_student_id = isset( $entry['uniquestudentid'] ) ? sanitize_text_field( (string) $entry['uniquestudentid'] ) : '';
                $entry_unique_student_id = $entry_unique_student_id ? $entry_unique_student_id : $unique_student_id;
                $entry_wp_user_id = isset( $entry['wpuserid'] ) ? absint( $entry['wpuserid'] ) : 0;
                $entry_wp_user_id = $entry_wp_user_id > 0 ? $entry_wp_user_id : $wp_user_id;

                $unique_class_id = isset( $entry['uniqueclassid'] ) ? sanitize_text_field( (string) $entry['uniqueclassid'] ) : '';

                if ( '' !== $classname ) {
                    $class_row = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT uniqueclassid, classname FROM $class_table WHERE LOWER(classname) = LOWER(%s) LIMIT 1",
                            $classname
                        ),
                        ARRAY_A
                    );

                    if ( is_array( $class_row ) && ! empty( $class_row['uniqueclassid'] ) ) {
                        $unique_class_id = sanitize_text_field( (string) $class_row['uniqueclassid'] );
                        $classname = sanitize_text_field( (string) $class_row['classname'] );
                    }
                }

                $has_content = '' !== $classname || '' !== $registered || '' !== $adminapproved || '' !== $attended || '' !== $outcome || '' !== $paymentstatus || '' !== $courseinprogress || '' !== $quizinprogress || '' !== $enrollmentdate || null !== $amount_value;

                if ( ! $has_content && ! $history_id ) {
                    continue;
                }

                $history_row = array(
                    'uniquestudentid'  => $entry_unique_student_id,
                    'wpuserid'         => $entry_wp_user_id,
                    'classname'        => $classname,
                    'uniqueclassid'    => $unique_class_id,
                    'registered'       => $registered,
                    'adminapproved'    => $adminapproved,
                    'attended'         => $attended,
                    'outcome'          => $outcome,
                    'paymentstatus'    => $paymentstatus,
                    'amountpaid'       => $amount_value,
                    'enrollmentdate'   => '' !== $enrollmentdate ? $enrollmentdate : null,
                    'registeredby'     => null,
                    'courseinprogress' => $courseinprogress,
                    'quizinprogress'   => $quizinprogress,
                );

                $history_formats = array(
                    '%s',
                    $entry_wp_user_id ? '%d' : '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    $amount_value === null ? '%s' : '%f',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                );

                if ( $history_id > 0 ) {
                    $wpdb->update(
                        $history_table,
                        $history_row,
                        array( 'id' => $history_id ),
                        $history_formats,
                        array( '%d' )
                    );
                } else {
                    $wpdb->insert( $history_table, $history_row, $history_formats );
                }
            }
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => $message,
            )
        );
    }

    public function login_user() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        $username = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
        $password = isset( $_POST['pwd'] ) ? wp_unslash( $_POST['pwd'] ) : '';
        $remember = isset( $_POST['rememberme'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['rememberme'] ) );

        if ( '' === $username || '' === $password ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please enter your username/email and password.', 'teqcidb' ),
                )
            );
        }

        $user = wp_signon(
            array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => $remember,
            ),
            false
        );

        if ( is_wp_error( $user ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'We could not log you in with those credentials. Please try again.', 'teqcidb' ),
                )
            );
        }

        wp_send_json_success();
    }

    public function update_profile() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! is_user_logged_in() ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You must be logged in to update your profile.', 'teqcidb' ),
                )
            );
        }

        $current_user = wp_get_current_user();

        if ( ! ( $current_user instanceof WP_User ) || ! $current_user->exists() ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to locate your account.', 'teqcidb' ),
                )
            );
        }

        $first_name = $this->sanitize_text_value( 'first_name' );
        $last_name  = $this->sanitize_text_value( 'last_name' );

        if ( '' === $first_name || '' === $last_name ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please complete all required fields.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $table = $wpdb->prefix . 'teqcidb_students';
        $row   = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE wpuserid = %d LIMIT 1",
                $current_user->ID
            ),
            ARRAY_A
        );

        if ( ! $row ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to locate your student record.', 'teqcidb' ),
                )
            );
        }

        $association_options = array( 'AAPA', 'ARBA', 'AGC', 'ABC', 'AUCA' );
        $data = array(
            'first_name'      => $first_name,
            'last_name'       => $last_name,
            'student_address' => $this->sanitize_student_address(),
            'phone_cell'      => $this->sanitize_phone_value( 'phone_cell' ),
            'phone_office'    => $this->sanitize_phone_value( 'phone_office' ),
            'associations'    => $this->sanitize_associations_value( 'associations', $association_options ),
        );


        $result = $wpdb->update(
            $table,
            $data,
            array( 'id' => (int) $row['id'] ),
            array_fill( 0, count( $data ), '%s' ),
            array( '%d' )
        );

        if ( false === $result && $wpdb->last_error ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to save your profile. Please try again.', 'teqcidb' ),
                )
            );
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => __( 'Profile saved.', 'teqcidb' ),
            )
        );
    }

    private function is_strong_password( $password ) {
        if ( strlen( $password ) < 12 ) {
            return false;
        }

        $has_upper   = preg_match( '/[A-Z]/', $password );
        $has_lower   = preg_match( '/[a-z]/', $password );
        $has_number  = preg_match( '/\\d/', $password );
        $has_special = preg_match( '/[^A-Za-z0-9]/', $password );

        return ( $has_upper && $has_lower && $has_number && $has_special );
    }

    public function save_studenthistory() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;
        $table      = $wpdb->prefix . 'teqcidb_studenthistory';
        $history_id = isset( $_POST['history_id'] ) ? absint( $_POST['history_id'] ) : 0;

        if ( $history_id <= 0 ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid student history entry.', 'teqcidb' ),
                )
            );
        }

        $history_data = isset( $_POST['studenthistory'] ) ? wp_unslash( $_POST['studenthistory'] ) : array();

        if ( ! is_array( $history_data ) || ! isset( $history_data[ $history_id ] ) || ! is_array( $history_data[ $history_id ] ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Missing student history details.', 'teqcidb' ),
                )
            );
        }

        $entry = $history_data[ $history_id ];

        $classname = isset( $entry['classname'] ) ? sanitize_text_field( (string) $entry['classname'] ) : '';
        $registered = isset( $entry['registered'] ) ? sanitize_text_field( (string) $entry['registered'] ) : '';
        $adminapproved = isset( $entry['adminapproved'] ) ? sanitize_text_field( (string) $entry['adminapproved'] ) : '';
        $attended = isset( $entry['attended'] ) ? sanitize_text_field( (string) $entry['attended'] ) : '';
        $outcome = isset( $entry['outcome'] ) ? sanitize_text_field( (string) $entry['outcome'] ) : '';
        $paymentstatus = isset( $entry['paymentstatus'] ) ? sanitize_text_field( (string) $entry['paymentstatus'] ) : '';
        $courseinprogress = isset( $entry['courseinprogress'] ) ? sanitize_text_field( (string) $entry['courseinprogress'] ) : '';
        $quizinprogress = isset( $entry['quizinprogress'] ) ? sanitize_text_field( (string) $entry['quizinprogress'] ) : '';
        $enrollmentdate = isset( $entry['enrollmentdate'] ) ? sanitize_text_field( (string) $entry['enrollmentdate'] ) : '';
        $amountpaid = isset( $entry['amountpaid'] ) ? sanitize_text_field( (string) $entry['amountpaid'] ) : '';
        $amountpaid = str_replace( array( '$', ',' ), '', $amountpaid );

        $amount_value = null;

        if ( '' !== $amountpaid && is_numeric( $amountpaid ) ) {
            $amount_value = (float) $amountpaid;
        }

        $class_table = $wpdb->prefix . 'teqcidb_classes';
        $unique_class_id = isset( $entry['uniqueclassid'] ) ? sanitize_text_field( (string) $entry['uniqueclassid'] ) : '';

        if ( '' !== $classname ) {
            $class_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT uniqueclassid, classname FROM $class_table WHERE LOWER(classname) = LOWER(%s) LIMIT 1",
                    $classname
                ),
                ARRAY_A
            );

            if ( is_array( $class_row ) && ! empty( $class_row['uniqueclassid'] ) ) {
                $unique_class_id = sanitize_text_field( (string) $class_row['uniqueclassid'] );
                $classname = sanitize_text_field( (string) $class_row['classname'] );
            }
        }

        $data = array(
            'classname'       => $classname,
            'uniqueclassid'   => $unique_class_id,
            'registered'      => $registered,
            'adminapproved'   => $adminapproved,
            'attended'        => $attended,
            'outcome'         => $outcome,
            'paymentstatus'   => $paymentstatus,
            'courseinprogress' => $courseinprogress,
            'quizinprogress'  => $quizinprogress,
            'enrollmentdate'  => '' !== $enrollmentdate ? $enrollmentdate : null,
            'amountpaid'      => $amount_value,
        );

        $formats = array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            $amount_value === null ? '%s' : '%f',
        );

        $result = $wpdb->update(
            $table,
            $data,
            array( 'id' => $history_id ),
            $formats,
            array( '%d' )
        );

        if ( false === $result ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to save the student history entry. Please try again.', 'teqcidb' ),
                )
            );
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => __( 'Student history entry saved.', 'teqcidb' ),
            )
        );
    }

    public function delete_studenthistory() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;
        $table      = $wpdb->prefix . 'teqcidb_studenthistory';
        $history_id = isset( $_POST['history_id'] ) ? absint( $_POST['history_id'] ) : 0;

        if ( $history_id <= 0 ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid student history entry.', 'teqcidb' ),
                )
            );
        }

        $deleted = $wpdb->delete( $table, array( 'id' => $history_id ), array( '%d' ) );

        if ( false === $deleted ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to delete the student history entry. Please try again.', 'teqcidb' ),
                )
            );
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => __( 'Student history entry deleted.', 'teqcidb' ),
            )
        );
    }

    public function create_studenthistory() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        $history_key = isset( $_POST['history_id'] ) ? sanitize_key( wp_unslash( $_POST['history_id'] ) ) : '';

        if ( '' === $history_key ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid student history entry.', 'teqcidb' ),
                )
            );
        }

        $history_data = isset( $_POST['studenthistory'] ) ? wp_unslash( $_POST['studenthistory'] ) : array();

        if ( ! is_array( $history_data ) || ! isset( $history_data[ $history_key ] ) || ! is_array( $history_data[ $history_key ] ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Missing student history details.', 'teqcidb' ),
                )
            );
        }

        $entry = $history_data[ $history_key ];

        $unique_student_id = isset( $entry['uniquestudentid'] ) ? sanitize_text_field( (string) $entry['uniquestudentid'] ) : '';

        if ( '' === $unique_student_id ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'A unique student ID is required to create a history entry.', 'teqcidb' ),
                )
            );
        }

        $wp_user_id = isset( $entry['wpuserid'] ) ? absint( $entry['wpuserid'] ) : 0;
        $wp_user_id = $wp_user_id > 0 ? $wp_user_id : null;

        $classname = isset( $entry['classname'] ) ? sanitize_text_field( (string) $entry['classname'] ) : '';
        $registered = isset( $entry['registered'] ) ? sanitize_text_field( (string) $entry['registered'] ) : '';
        $adminapproved = isset( $entry['adminapproved'] ) ? sanitize_text_field( (string) $entry['adminapproved'] ) : '';
        $attended = isset( $entry['attended'] ) ? sanitize_text_field( (string) $entry['attended'] ) : '';
        $outcome = isset( $entry['outcome'] ) ? sanitize_text_field( (string) $entry['outcome'] ) : '';
        $paymentstatus = isset( $entry['paymentstatus'] ) ? sanitize_text_field( (string) $entry['paymentstatus'] ) : '';
        $courseinprogress = isset( $entry['courseinprogress'] ) ? sanitize_text_field( (string) $entry['courseinprogress'] ) : '';
        $quizinprogress = isset( $entry['quizinprogress'] ) ? sanitize_text_field( (string) $entry['quizinprogress'] ) : '';
        $enrollmentdate = isset( $entry['enrollmentdate'] ) ? sanitize_text_field( (string) $entry['enrollmentdate'] ) : '';
        $amountpaid = isset( $entry['amountpaid'] ) ? sanitize_text_field( (string) $entry['amountpaid'] ) : '';
        $amountpaid = str_replace( array( '$', ',' ), '', $amountpaid );

        $amount_value = null;

        if ( '' !== $amountpaid && is_numeric( $amountpaid ) ) {
            $amount_value = (float) $amountpaid;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'teqcidb_studenthistory';
        $class_table = $wpdb->prefix . 'teqcidb_classes';
        $unique_class_id = isset( $entry['uniqueclassid'] ) ? sanitize_text_field( (string) $entry['uniqueclassid'] ) : '';

        if ( '' !== $classname ) {
            $class_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT uniqueclassid, classname FROM $class_table WHERE LOWER(classname) = LOWER(%s) LIMIT 1",
                    $classname
                ),
                ARRAY_A
            );

            if ( is_array( $class_row ) && ! empty( $class_row['uniqueclassid'] ) ) {
                $unique_class_id = sanitize_text_field( (string) $class_row['uniqueclassid'] );
                $classname = sanitize_text_field( (string) $class_row['classname'] );
            }
        }

        $data = array(
            'uniquestudentid'  => $unique_student_id,
            'wpuserid'         => $wp_user_id,
            'classname'        => $classname,
            'uniqueclassid'    => $unique_class_id,
            'registered'       => $registered,
            'adminapproved'    => $adminapproved,
            'attended'         => $attended,
            'outcome'          => $outcome,
            'paymentstatus'    => $paymentstatus,
            'amountpaid'       => $amount_value,
            'enrollmentdate'   => '' !== $enrollmentdate ? $enrollmentdate : null,
            'registeredby'     => null,
            'courseinprogress' => $courseinprogress,
            'quizinprogress'   => $quizinprogress,
        );

        $formats = array(
            '%s',
            $wp_user_id === null ? '%s' : '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            $amount_value === null ? '%s' : '%f',
            '%s',
            '%s',
            '%s',
            '%s',
        );

        $result = $wpdb->insert( $table, $data, $formats );

        if ( false === $result ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to create the student history entry. Please try again.', 'teqcidb' ),
                )
            );
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => __( 'Student history entry created.', 'teqcidb' ),
            )
        );
    }

    public function save_class() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to save this class.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $table          = $wpdb->prefix . 'teqcidb_classes';
        $id             = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
        $class_name     = $this->sanitize_text_value( 'classname' );
        $access_options = array( 'allowed', 'blocked' );
        $format_options = array( 'in_person', 'virtual', 'hybrid' );
        $type_options   = array( 'initial', 'refresher', 'other' );

        if ( '' === $class_name ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please provide a class name before saving.', 'teqcidb' ),
                )
            );
        }

        $existing_unique_id = '';
        $existing_class_url = '';

        if ( $id > 0 ) {
            $existing_class = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT uniqueclassid, classurl FROM $table WHERE id = %d",
                    $id
                ),
                ARRAY_A
            );

            if ( is_array( $existing_class ) ) {
                $existing_unique_id = isset( $existing_class['uniqueclassid'] ) ? (string) $existing_class['uniqueclassid'] : '';
                $existing_class_url = isset( $existing_class['classurl'] ) ? (string) $existing_class['classurl'] : '';
            }
        }

        $unique_class_id = '' !== $existing_unique_id ? $existing_unique_id : $this->generate_unique_class_id( $class_name );

        if ( '' !== $existing_class_url ) {
            $class_url = $existing_class_url;
        } else {
            $class_url = $this->generate_class_page_relative_url( $this->get_class_page_route_segment( $unique_class_id ) );
        }

        $data = array(
            'uniqueclassid'           => $unique_class_id,
            'classname'               => $class_name,
            'classformat'             => $this->sanitize_select_value( 'classformat', $format_options ),
            'classtype'               => $this->sanitize_select_value( 'classtype', $type_options ),
            'classsize'               => $this->sanitize_positive_int_value( 'classsize' ),
            'classsaddress'           => $this->sanitize_class_address(),
            'classstartdate'          => $this->sanitize_date_value( 'classstartdate' ),
            'classstarttime'          => $this->sanitize_time_value( 'classstarttime' ),
            'classendtime'            => $this->sanitize_time_value( 'classendtime' ),
            'classcost'               => $this->sanitize_decimal_value( 'classcost' ),
            'classdescription'        => $this->sanitize_textarea_value( 'classdescription' ),
            'teamslink'               => $this->sanitize_url_value( 'teamslink' ),
            'classurl'                => $class_url,
            'classhide'               => $this->sanitize_yes_no_value( 'classhide' ),
            'allallowedcourse'        => $this->sanitize_select_value( 'allallowedcourse', $access_options ),
            'allallowedquiz'          => $this->sanitize_select_value( 'allallowedquiz', $access_options ),
            'coursestudentsallowed'   => $this->sanitize_student_access_items( 'coursestudentsallowed' ),
            'quizstudentsallowed'     => $this->sanitize_student_access_items( 'quizstudentsallowed' ),
            'coursestudentsrestricted' => $this->sanitize_student_access_items( 'coursestudentsrestricted' ),
            'quizstudentsrestricted'   => $this->sanitize_student_access_items( 'quizstudentsrestricted' ),
            'instructors'             => $this->sanitize_items_value( 'instructors' ),
        );

        $class_resources = $this->sanitize_class_resources_value( 'classresources' );

        if ( null !== $class_resources ) {
            $data['classresources'] = $class_resources;
        }

        $formats = array_fill( 0, count( $data ), '%s' );

        if ( $id > 0 ) {
            $result  = $wpdb->update( $table, $data, array( 'id' => $id ), $formats, array( '%d' ) );
            $message = __( 'Changes saved.', 'teqcidb' );

            if ( false === $result && $wpdb->last_error ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'Unable to save changes. Please try again.', 'teqcidb' ),
                    )
                );
            }
        } else {
            $result  = $wpdb->insert( $table, $data, $formats );
            $message = __( 'Class saved.', 'teqcidb' );

            if ( false === $result ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'Unable to save the class. Please try again.', 'teqcidb' ),
                    )
                );
            }
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => $message,
            )
        );
    }

    public function save_quiz_question() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to save quiz questions.', 'teqcidb' ),
                )
            );
        }

        $quiz_id       = isset( $_POST['quiz_id'] ) ? absint( wp_unslash( $_POST['quiz_id'] ) ) : 0;
        $question_id   = isset( $_POST['question_id'] ) ? absint( wp_unslash( $_POST['question_id'] ) ) : 0;
        $question_type = isset( $_POST['question_type'] ) ? sanitize_key( wp_unslash( $_POST['question_type'] ) ) : '';
        $prompt        = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
        $correct       = isset( $_POST['correct'] ) ? sanitize_key( wp_unslash( $_POST['correct'] ) ) : '';

        if ( $quiz_id <= 0 || $question_id <= 0 ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid quiz question selection.', 'teqcidb' ),
                )
            );
        }

        $option_ids_raw     = isset( $_POST['option_ids'] ) ? (array) wp_unslash( $_POST['option_ids'] ) : array();
        $option_labels_raw  = isset( $_POST['option_labels'] ) ? (array) wp_unslash( $_POST['option_labels'] ) : array();
        $option_correct_raw = isset( $_POST['option_correct'] ) ? (array) wp_unslash( $_POST['option_correct'] ) : array();

        if ( ! in_array( $question_type, array( 'true_false', 'multi_select', 'multiple_choice' ), true ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Saving this question type is coming soon.', 'teqcidb' ),
                )
            );
        }

        if ( 'true_false' === $question_type && 'true' !== $correct && 'false' !== $correct ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Select True or False before saving this question.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $table = $wpdb->prefix . 'teqcidb_quiz_questions';
        $type  = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT type FROM $table WHERE id = %d AND quiz_id = %d LIMIT 1",
                $question_id,
                $quiz_id
            )
        );

        $saved_type = sanitize_key( (string) $type );

        if ( $question_type !== $saved_type ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'This question type changed. Refresh and try again.', 'teqcidb' ),
                )
            );
        }

        if ( 'true_false' === $question_type ) {
            $choices_json = wp_json_encode(
                array(
                    array(
                        'correct' => $correct,
                    ),
                )
            );

            if ( ! $choices_json ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'Unable to encode the true/false answer.', 'teqcidb' ),
                    )
                );
            }
        } else {
            $choices = array();

            foreach ( $option_labels_raw as $index => $label_raw ) {
                $label = sanitize_textarea_field( (string) $label_raw );

                if ( '' === trim( $label ) ) {
                    continue;
                }

                $option_id = isset( $option_ids_raw[ $index ] ) ? sanitize_key( (string) $option_ids_raw[ $index ] ) : '';

                if ( '' === $option_id ) {
                    $option_id = 'choice_' . ( $index + 1 );
                }

                $is_correct = isset( $option_correct_raw[ $index ] ) && 'true' === sanitize_key( (string) $option_correct_raw[ $index ] );

                $choices[] = array(
                    'id'      => $option_id,
                    'label'   => $label,
                    'correct' => $is_correct,
                );
            }

            if ( empty( $choices ) ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'Add at least one answer option before saving this question.', 'teqcidb' ),
                    )
                );
            }

            if ( 'multiple_choice' === $question_type ) {
                $correct_count = 0;

                foreach ( $choices as $choice ) {
                    if ( ! empty( $choice['correct'] ) ) {
                        $correct_count++;
                    }
                }

                if ( 1 !== $correct_count ) {
                    $this->maybe_delay( $start );
                    wp_send_json_error(
                        array(
                            'message' => __( 'Set exactly one answer option to True for a multiple choice question.', 'teqcidb' ),
                        )
                    );
                }
            }

            $choices_json = wp_json_encode( $choices );

            if ( ! $choices_json ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'Unable to encode the answer options.', 'teqcidb' ),
                    )
                );
            }
        }

        $updated = $wpdb->update(
            $table,
            array(
                'prompt'       => $prompt,
                'choices_json' => $choices_json,
                'updated_at'   => current_time( 'mysql' ),
            ),
            array(
                'id'      => $question_id,
                'quiz_id' => $quiz_id,
            ),
            array( '%s', '%s', '%s' ),
            array( '%d', '%d' )
        );

        if ( false === $updated ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to save the quiz question. Please try again.', 'teqcidb' ),
                )
            );
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => __( 'Question saved.', 'teqcidb' ),
            )
        );
    }

    public function search_students() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to search students.', 'teqcidb' ),
                )
            );
        }

        $term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
        $term = trim( $term );

        if ( strlen( $term ) < 2 ) {
            $this->maybe_delay( $start );
            wp_send_json_success(
                array(
                    'results' => array(),
                    'message' => __( 'Type at least two characters to search students.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $table = $wpdb->prefix . 'teqcidb_students';
        $like  = '%' . $wpdb->esc_like( $term ) . '%';

        $query = $wpdb->prepare(
            "SELECT id, wpuserid, uniquestudentid, first_name, last_name, email FROM $table WHERE first_name LIKE %s OR last_name LIKE %s OR email LIKE %s ORDER BY last_name ASC, first_name ASC LIMIT 15",
            $like,
            $like,
            $like
        );

        $rows = $wpdb->get_results( $query );

        if ( ! is_array( $rows ) ) {
            $rows = array();
        }

        $results = array();

        foreach ( $rows as $row ) {
            $name = trim( (string) $row->first_name . ' ' . (string) $row->last_name );
            $name = $name ? $name : __( 'Student', 'teqcidb' );
            $email = isset( $row->email ) ? (string) $row->email : '';

            $display = $name;

            if ( '' !== $email ) {
                $display .= ' (' . $email . ')';
            }

            $results[] = array(
                'id'               => isset( $row->wpuserid ) ? (int) $row->wpuserid : 0,
                'wpuserid'         => isset( $row->wpuserid ) ? (string) $row->wpuserid : '',
                'uniquestudentid'  => (string) $row->uniquestudentid,
                'first_name'       => (string) $row->first_name,
                'last_name'        => (string) $row->last_name,
                'email'            => $email,
                'display'          => $display,
                'value'            => sprintf(
                    /* translators: 1: WordPress user ID, 2: unique student ID, 3: student name, 4: student email */
                    __( 'WP ID: %1$d | Unique ID: %2$s | %3$s (%4$s)', 'teqcidb' ),
                    isset( $row->wpuserid ) ? (int) $row->wpuserid : 0,
                    (string) $row->uniquestudentid,
                    $name,
                    $email
                ),
            );
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'results' => $results,
            )
        );
    }

    public function save_general_settings() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to save these settings.', 'teqcidb' ),
                )
            );
        }

        $sanitized = TEQCIDB_Settings_Helper::sanitize_general_settings( $_POST );
        $result    = TEQCIDB_Settings_Helper::save_general_settings( $_POST );

        if ( false === $result ) {
            $stored = TEQCIDB_Settings_Helper::get_general_settings();

            if ( $stored !== $sanitized ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'Settings could not be saved. Please try again.', 'teqcidb' ),
                    )
                );
            }
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => __( 'Settings saved.', 'teqcidb' ),
            )
        );
    }

    public function save_api_settings() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to save these settings.', 'teqcidb' ),
                )
            );
        }

        $api_key = isset( $_POST['teqcidb_api_key'] ) ? sanitize_key( wp_unslash( $_POST['teqcidb_api_key'] ) ) : '';

        $api_definitions = array(
            'payment_gateway' => array(
                'fields' => array(
                    'payment_gateway_environment'   => array(
                        'type'    => 'select',
                        'options' => array( 'live', 'sandbox' ),
                        'default' => 'live',
                    ),
                    'payment_gateway_login_id'      => array(
                        'type' => 'text',
                    ),
                    'payment_gateway_transaction_key' => array(
                        'type' => 'text',
                    ),
                ),
            ),
            'sms_service' => array(
                'fields' => array(
                    'sms_environment' => array(
                        'type'    => 'select',
                        'options' => array( 'live', 'sandbox' ),
                        'default' => 'live',
                    ),
                    'sms_messaging_service_sid' => array(
                        'type' => 'text',
                    ),
                    'sms_sending_number' => array(
                        'type' => 'text',
                    ),
                    'sms_sandbox_number' => array(
                        'type' => 'text',
                    ),
                    'sms_user_sid' => array(
                        'type' => 'text',
                    ),
                    'sms_api_sid' => array(
                        'type' => 'text',
                    ),
                    'sms_api_key' => array(
                        'type' => 'text',
                    ),
                ),
            ),
        );

        if ( ! $api_key || ! isset( $api_definitions[ $api_key ] ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unknown API configuration.', 'teqcidb' ),
                )
            );
        }

        $definition = $api_definitions[ $api_key ];
        $sanitized  = array();

        foreach ( $definition['fields'] as $field_key => $field_definition ) {
            $raw_value = isset( $_POST[ $field_key ] ) ? wp_unslash( $_POST[ $field_key ] ) : '';
            $field_type = isset( $field_definition['type'] ) ? $field_definition['type'] : 'text';

            switch ( $field_type ) {
                case 'select':
                    $allowed_values = isset( $field_definition['options'] ) ? (array) $field_definition['options'] : array();
                    $default_value  = isset( $field_definition['default'] ) ? $field_definition['default'] : '';
                    $raw_value      = sanitize_key( $raw_value );

                    if ( ! in_array( $raw_value, $allowed_values, true ) ) {
                        $raw_value = $default_value;
                    }

                    $sanitized[ $field_key ] = $raw_value;
                    break;
                default:
                    $sanitized[ $field_key ] = sanitize_text_field( $raw_value );
                    break;
            }
        }

        $all_settings = get_option( 'teqcidb_api_settings', array() );

        if ( ! is_array( $all_settings ) ) {
            $all_settings = array();
        }

        $all_settings[ $api_key ] = $sanitized;

        update_option( 'teqcidb_api_settings', $all_settings );

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => __( 'API settings saved.', 'teqcidb' ),
            )
        );
    }

    public function upload_legacy_records() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to upload legacy records.', 'teqcidb' ),
                )
            );
        }

        $this->extend_legacy_upload_limits();

        $raw_record = isset( $_POST['legacy_record'] ) ? wp_unslash( $_POST['legacy_record'] ) : '';
        $raw_record = $this->get_legacy_upload_payload( $raw_record );

        if ( is_wp_error( $raw_record ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => $raw_record->get_error_message(),
                )
            );
        }
        $requested  = isset( $_POST['legacy_types'] ) ? (array) $_POST['legacy_types'] : array();
        $action     = isset( $_POST['action'] ) ? sanitize_key( wp_unslash( $_POST['action'] ) ) : '';

        $requested_types = array_values( array_unique( array_filter( array_map( 'sanitize_key', $requested ) ) ) );

        if ( empty( $requested_types ) && 'teqcidb_upload_legacy_student' === $action ) {
            $requested_types = array( 'student' );
        }

        $allowed_types  = array( 'student', 'class', 'studenthistory' );
        $selected_types = array_values( array_intersect( $requested_types, $allowed_types ) );

        if ( empty( $selected_types ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Select at least one supported upload type.', 'teqcidb' ),
                )
            );
        }

        if ( count( $selected_types ) > 1 ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please choose a single upload type at a time.', 'teqcidb' ),
                )
            );
        }

        $upload_type = $selected_types[0];

        if ( 'class' === $upload_type ) {
            $this->process_legacy_class_upload( $raw_record, $start );
            return;
        }

        if ( 'studenthistory' === $upload_type ) {
            $this->process_legacy_student_history_upload( $raw_record, $start );
            return;
        }

        $this->process_legacy_student_upload( $raw_record, $start );
    }

    private function process_legacy_student_history_upload( $raw_record, $start ) {
        $records = $this->split_legacy_rows( $raw_record );

        if ( empty( $records ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please paste a legacy student history row before uploading.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $table             = $wpdb->prefix . 'teqcidb_studenthistory';
        $inserted          = 0;
        $skipped_messages  = array();

        foreach ( $records as $index => $record ) {
            $row_number = $index + 1;

            $parsed = $this->parse_legacy_student_history_record( $record );

            if ( is_wp_error( $parsed ) ) {
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, $parsed->get_error_message() );
                continue;
            }

            $mapped = $this->map_legacy_student_history_record( $parsed );

            if ( is_wp_error( $mapped ) ) {
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, $mapped->get_error_message() );
                continue;
            }

            $data = array(
                'uniquestudentid' => $mapped['uniquestudentid'],
                'wpuserid'        => $mapped['wpuserid'],
                'classname'       => $mapped['classname'],
                'uniqueclassid'   => $mapped['uniqueclassid'],
                'registered'      => $mapped['registered'],
                'adminapproved'   => $mapped['adminapproved'],
                'attended'        => $mapped['attended'],
                'outcome'         => $mapped['outcome'],
                'paymentstatus'   => $mapped['paymentstatus'],
                'amountpaid'      => $mapped['amountpaid'],
                'enrollmentdate'  => $mapped['enrollmentdate'],
                'registeredby'    => $mapped['registeredby'],
                'courseinprogress' => $mapped['courseinprogress'],
                'quizinprogress'   => $mapped['quizinprogress'],
            );

            $formats = array(
                'uniquestudentid' => '%s',
                'wpuserid'        => '%d',
                'classname'       => '%s',
                'uniqueclassid'   => '%s',
                'registered'      => '%s',
                'adminapproved'   => '%s',
                'attended'        => '%s',
                'outcome'         => '%s',
                'paymentstatus'   => '%s',
                'amountpaid'      => '%f',
                'enrollmentdate'  => '%s',
                'registeredby'    => '%d',
                'courseinprogress' => '%s',
                'quizinprogress'   => '%s',
            );

            foreach ( $data as $key => $value ) {
                if ( null === $value ) {
                    unset( $data[ $key ], $formats[ $key ] );
                }
            }

            $insert_formats = array();

            foreach ( $data as $key => $_value ) {
                if ( isset( $formats[ $key ] ) ) {
                    $insert_formats[] = $formats[ $key ];
                }
            }

            $result = $wpdb->insert( $table, $data, $insert_formats );

            if ( false === $result ) {
                $error_message = $wpdb->last_error ? wp_strip_all_tags( $wpdb->last_error ) : __( 'Unable to upload the record. Please check the data and try again.', 'teqcidb' );
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, $error_message );
                continue;
            }

            $inserted++; 
        }

        $this->maybe_delay( $start );

        if ( $inserted > 0 ) {
            $message = __( 'Legacy student history uploaded successfully.', 'teqcidb' );

            if ( ! empty( $skipped_messages ) ) {
                $message = sprintf(
                    /* translators: 1: inserted count, 2: skipped count. */
                    __( 'Uploaded %1$d record(s); %2$d skipped.', 'teqcidb' ),
                    $inserted,
                    count( $skipped_messages )
                );
            }

            wp_send_json_success(
                array(
                    'message' => $message,
                    'skipped' => array_values( $skipped_messages ),
                )
            );
        }

        wp_send_json_error(
            array(
                'message' => __( 'Unable to upload any legacy records. Please review the data and try again.', 'teqcidb' ),
                'skipped' => array_values( $skipped_messages ),
            )
        );
    }

    private function process_legacy_student_upload( $raw_record, $start ) {
        $records = $this->split_legacy_rows( $raw_record );

        if ( empty( $records ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please paste a legacy student row before uploading.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $table            = $wpdb->prefix . 'teqcidb_students';
        $inserted         = 0;
        $skipped_messages = array();

        foreach ( $records as $index => $record ) {
            $row_number = $index + 1;

            $parsed = $this->parse_legacy_student_record( $record );

            if ( is_wp_error( $parsed ) ) {
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, $parsed->get_error_message() );
                continue;
            }

            $mapped = $this->map_legacy_student_record( $parsed, $row_number );

            if ( is_wp_error( $mapped ) ) {
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, $mapped->get_error_message() );
                continue;
            }

            if ( $this->legacy_student_value_exists( $table, 'email', $mapped['email'] ) ) {
                $mapped['email'] = $this->generate_unique_legacy_student_email( $parsed, $row_number, $table, $mapped['email'] );
            }

            if ( $this->legacy_student_value_exists( $table, 'uniquestudentid', $mapped['uniquestudentid'] ) ) {
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, __( 'A student with this unique ID already exists.', 'teqcidb' ) );
                continue;
            }

            $data = array(
                'wpuserid'              => $mapped['wpuserid'],
                'uniquestudentid'       => $mapped['uniquestudentid'],
                'first_name'            => $mapped['first_name'],
                'last_name'             => $mapped['last_name'],
                'company'               => $mapped['company'],
                'old_companies'         => $mapped['old_companies'],
                'student_address'       => $mapped['student_address'],
                'phone_cell'            => $mapped['phone_cell'],
                'phone_office'          => $mapped['phone_office'],
                'fax'                   => $mapped['fax'],
                'email'                 => $mapped['email'],
                'initial_training_date' => $mapped['initial_training_date'],
                'last_refresher_date'   => $mapped['last_refresher_date'],
                'is_a_representative'   => $mapped['is_a_representative'],
                'their_representative'  => $mapped['their_representative'],
                'new_class_signup_flag' => $mapped['new_class_signup_flag'],
                'associations'          => $mapped['associations'],
                'expiration_date'       => $mapped['expiration_date'],
                'qcinumber'             => $mapped['qcinumber'],
                'comments'              => $mapped['comments'],
            );

            $formats = array(
                'wpuserid'              => '%d',
                'uniquestudentid'       => '%s',
                'first_name'            => '%s',
                'last_name'             => '%s',
                'company'               => '%s',
                'old_companies'         => '%s',
                'student_address'       => '%s',
                'phone_cell'            => '%s',
                'phone_office'          => '%s',
                'fax'                   => '%s',
                'email'                 => '%s',
                'initial_training_date' => '%s',
                'last_refresher_date'   => '%s',
                'is_a_representative'   => '%d',
                'their_representative'  => '%s',
                'new_class_signup_flag' => '%d',
                'associations'          => '%s',
                'expiration_date'       => '%s',
                'qcinumber'             => '%s',
                'comments'              => '%s',
            );

            foreach ( $data as $key => $value ) {
                if ( null === $value ) {
                    unset( $data[ $key ], $formats[ $key ] );
                }
            }

            $insert_formats = array();

            foreach ( $data as $key => $_value ) {
                if ( isset( $formats[ $key ] ) ) {
                    $insert_formats[] = $formats[ $key ];
                }
            }

            $result = $wpdb->insert( $table, $data, $insert_formats );

            if ( false === $result ) {
                $error_message = $wpdb->last_error ? wp_strip_all_tags( $wpdb->last_error ) : __( 'Unable to upload the record. Please check the data and try again.', 'teqcidb' );
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, $error_message );
                continue;
            }

            $inserted++;
        }

        $this->maybe_delay( $start );

        if ( $inserted > 0 ) {
            $message = __( 'Legacy student uploaded successfully.', 'teqcidb' );

            if ( ! empty( $skipped_messages ) ) {
                $message = sprintf(
                    /* translators: 1: inserted count, 2: skipped count. */
                    __( 'Uploaded %1$d record(s); %2$d skipped.', 'teqcidb' ),
                    $inserted,
                    count( $skipped_messages )
                );
            }

            wp_send_json_success(
                array(
                    'message' => $message,
                    'skipped' => array_values( $skipped_messages ),
                )
            );
        }

        wp_send_json_error(
            array(
                'message' => __( 'Unable to upload any legacy records. Please review the data and try again.', 'teqcidb' ),
                'skipped' => array_values( $skipped_messages ),
            )
        );
    }

    private function process_legacy_class_upload( $raw_record, $start ) {
        $records = $this->split_legacy_rows( $raw_record );

        if ( empty( $records ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please paste a legacy class row before uploading.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $table            = $wpdb->prefix . 'teqcidb_classes';
        $inserted         = 0;
        $skipped_messages = array();

        foreach ( $records as $index => $record ) {
            $row_number = $index + 1;

            $parsed = $this->parse_legacy_class_record( $record );

            if ( is_wp_error( $parsed ) ) {
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, $parsed->get_error_message() );
                continue;
            }

            $mapped = $this->map_legacy_class_record( $parsed );

            if ( is_wp_error( $mapped ) ) {
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, $mapped->get_error_message() );
                continue;
            }

            if ( $this->legacy_class_value_exists( $table, 'uniqueclassid', $mapped['uniqueclassid'] ) ) {
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, __( 'A class with this unique ID already exists.', 'teqcidb' ) );
                continue;
            }

            $data = array(
                'uniqueclassid'         => $mapped['uniqueclassid'],
                'classname'             => $mapped['classname'],
                'classformat'           => $mapped['classformat'],
                'classtype'             => $mapped['classtype'],
                'classsize'             => $mapped['classsize'],
                'classregistrantnumber' => $mapped['classregistrantnumber'],
                'instructors'           => $mapped['instructors'],
                'classsaddress'         => $mapped['classsaddress'],
                'classstartdate'        => $mapped['classstartdate'],
                'classstarttime'        => $mapped['classstarttime'],
                'classendtime'          => $mapped['classendtime'],
                'classcost'             => $mapped['classcost'],
                'classdescription'      => $mapped['classdescription'],
                'classhide'             => $mapped['classhide'],
            );

            $formats = array(
                'uniqueclassid'         => '%s',
                'classname'             => '%s',
                'classformat'           => '%s',
                'classtype'             => '%s',
                'classsize'             => '%d',
                'classregistrantnumber' => '%d',
                'instructors'           => '%s',
                'classsaddress'         => '%s',
                'classstartdate'        => '%s',
                'classstarttime'        => '%s',
                'classendtime'          => '%s',
                'classcost'             => '%s',
                'classdescription'      => '%s',
                'classhide'             => '%d',
            );

            foreach ( $data as $key => $value ) {
                if ( null === $value ) {
                    unset( $data[ $key ], $formats[ $key ] );
                }
            }

            $insert_formats = array();

            foreach ( $data as $key => $_value ) {
                if ( isset( $formats[ $key ] ) ) {
                    $insert_formats[] = $formats[ $key ];
                }
            }

            $result = $wpdb->insert( $table, $data, $insert_formats );

            if ( false === $result ) {
                $error_message = $wpdb->last_error ? wp_strip_all_tags( $wpdb->last_error ) : __( 'Unable to upload the record. Please check the data and try again.', 'teqcidb' );
                $this->add_legacy_skipped_row_message( $skipped_messages, $row_number, $error_message );
                continue;
            }

            $inserted++;
        }

        $this->maybe_delay( $start );

        if ( $inserted > 0 ) {
            $message = __( 'Legacy class uploaded successfully.', 'teqcidb' );

            if ( ! empty( $skipped_messages ) ) {
                $message = sprintf(
                    /* translators: 1: inserted count, 2: skipped count. */
                    __( 'Uploaded %1$d record(s); %2$d skipped.', 'teqcidb' ),
                    $inserted,
                    count( $skipped_messages )
                );
            }

            wp_send_json_success(
                array(
                    'message' => $message,
                    'skipped' => array_values( $skipped_messages ),
                )
            );
        }

        wp_send_json_error(
            array(
                'message' => __( 'Unable to upload any legacy records. Please review the data and try again.', 'teqcidb' ),
                'skipped' => array_values( $skipped_messages ),
            )
        );
    }

    public function clear_error_log() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to modify error logs.', 'teqcidb' ),
                )
            );
        }

        $scope = isset( $_POST['scope'] ) ? TEQCIDB_Error_Log_Helper::normalize_scope( wp_unslash( $_POST['scope'] ) ) : '';

        if ( '' === $scope ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unknown log scope.', 'teqcidb' ),
                )
            );
        }

        $cleared = TEQCIDB_Error_Log_Helper::clear_log( $scope );

        if ( ! $cleared ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to clear the requested log. Please check file permissions.', 'teqcidb' ),
                )
            );
        }

        $message = TEQCIDB_Error_Log_Helper::get_clear_success_message( $scope );

        if ( '' === $message ) {
            $message = __( 'Log cleared.', 'teqcidb' );
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => $message,
                'content' => TEQCIDB_Error_Log_Helper::get_log_contents( $scope ),
            )
        );
    }

    public function download_error_log() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to download error logs.', 'teqcidb' ),
                )
            );
        }

        $scope = isset( $_POST['scope'] ) ? TEQCIDB_Error_Log_Helper::normalize_scope( wp_unslash( $_POST['scope'] ) ) : '';

        if ( '' === $scope ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unknown log scope.', 'teqcidb' ),
                )
            );
        }

        $filename = TEQCIDB_Error_Log_Helper::get_download_filename( $scope );

        if ( '' === $filename ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to prepare the download filename.', 'teqcidb' ),
                )
            );
        }

        $contents = TEQCIDB_Error_Log_Helper::get_log_contents( $scope );

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message'  => __( 'Log download ready.', 'teqcidb' ),
                'filename' => sanitize_file_name( $filename ),
                'content'  => (string) $contents,
            )
        );
    }

    public function delete_student() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );
        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        global $wpdb;
        $table = $wpdb->prefix . 'teqcidb_students';
        $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
        $this->maybe_delay( $start );
        wp_send_json_success( array( 'message' => __( 'Deleted', 'teqcidb' ) ) );
    }

    public function read_class() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );
        global $wpdb;
        $table    = $wpdb->prefix . 'teqcidb_classes';
        $page     = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;

        if ( $per_page <= 0 ) {
            $per_page = 10;
        }

        $per_page = min( $per_page, 100 );

        $raw_search = isset( $_POST['search'] ) ? wp_unslash( $_POST['search'] ) : array();

        if ( ! is_array( $raw_search ) ) {
            $raw_search = array();
        }

        $search_terms = array();

        foreach ( array( 'placeholder_1', 'placeholder_2', 'placeholder_3' ) as $column ) {
            if ( isset( $raw_search[ $column ] ) ) {
                $value = sanitize_text_field( $raw_search[ $column ] );

                if ( '' !== $value ) {
                    $search_terms[ $column ] = $value;
                }
            }
        }

        $where_clauses = array();
        $where_params  = array();

        foreach ( $search_terms as $key => $value ) {
            $like_value = '%' . $wpdb->esc_like( $value ) . '%';

            if ( 'placeholder_1' === $key ) {
                $where_clauses[] = 'classname LIKE %s';
                $where_params[]  = $like_value;
                continue;
            }

            if ( 'placeholder_2' === $key ) {
                $where_clauses[] = 'classformat LIKE %s';
                $where_params[]  = $like_value;
                continue;
            }

            if ( 'placeholder_3' === $key ) {
                $where_clauses[] = 'classtype LIKE %s';
                $where_params[]  = $like_value;
            }
        }

        $where_sql = '';

        if ( $where_clauses ) {
            $where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
        }

        $total_query = "SELECT COUNT(*) FROM $table";

        if ( $where_sql ) {
            $total_query .= ' ' . $where_sql;
        }

        if ( $where_params ) {
            $total = (int) $wpdb->get_var( $wpdb->prepare( $total_query, $where_params ) );
        } else {
            $total = (int) $wpdb->get_var( $total_query );
        }

        $total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;

        if ( $total_pages < 1 ) {
            $total_pages = 1;
        }

        if ( $page > $total_pages ) {
            $page = $total_pages;
        }

        $offset = ( $page - 1 ) * $per_page;

        if ( $offset < 0 ) {
            $offset = 0;
        }

        $entities = array();

        if ( $total > 0 ) {
            $select_query = "SELECT * FROM $table";

            if ( $where_sql ) {
                $select_query .= ' ' . $where_sql;
            }

            $select_query .= ' ORDER BY classstartdate ASC, classname ASC, id ASC LIMIT %d OFFSET %d';

            $select_params   = $where_params;
            $select_params[] = $per_page;
            $select_params[] = $offset;

            $entities = $wpdb->get_results(
                $wpdb->prepare(
                    $select_query,
                    $select_params
                ),
                ARRAY_A
            );

            if ( is_array( $entities ) ) {
                foreach ( $entities as &$entity ) {
                    if ( ! is_array( $entity ) ) {
                        $entity = array();
                        continue;
                    }

                    $entity = $this->prepare_class_entity( $entity );
                }
                unset( $entity );
            } else {
                $entities = array();
            }
        }

        $this->maybe_delay( $start, 0 );
        wp_send_json_success(
            array(
                'entities'    => $entities,
                'page'        => $page,
                'per_page'    => $per_page,
                'total'       => $total,
                'total_pages' => $total_pages,
            )
        );
    }

    public function read_student() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );
        global $wpdb;
        $table    = $wpdb->prefix . 'teqcidb_students';
        $page     = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20;

        if ( $per_page <= 0 ) {
            $per_page = 20;
        }

        $per_page = min( $per_page, 100 );

        $raw_search = isset( $_POST['search'] ) ? wp_unslash( $_POST['search'] ) : array();

        if ( ! is_array( $raw_search ) ) {
            $raw_search = array();
        }

        $search_terms = array();

        foreach ( array( 'placeholder_1', 'placeholder_2', 'placeholder_3' ) as $column ) {
            if ( isset( $raw_search[ $column ] ) ) {
                $value = sanitize_text_field( $raw_search[ $column ] );

                if ( '' !== $value ) {
                    $search_terms[ $column ] = $value;
                }
            }
        }

        $where_clauses = array();
        $where_params  = array();

        foreach ( $search_terms as $key => $value ) {
            $like_value = '%' . $wpdb->esc_like( $value ) . '%';

            if ( 'placeholder_1' === $key ) {
                $where_clauses[] = "CONCAT_WS(' ', first_name, last_name) LIKE %s";
                $where_params[]  = $like_value;
                continue;
            }

            if ( 'placeholder_2' === $key ) {
                $where_clauses[] = 'email LIKE %s';
                $where_params[]  = $like_value;
                continue;
            }

            if ( 'placeholder_3' === $key ) {
                $where_clauses[] = 'company LIKE %s';
                $where_params[]  = $like_value;
            }
        }

        $where_sql = '';

        if ( $where_clauses ) {
            $where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
        }

        $total_query = "SELECT COUNT(*) FROM $table";

        if ( $where_sql ) {
            $total_query .= ' ' . $where_sql;
        }

        if ( $where_params ) {
            $total = (int) $wpdb->get_var( $wpdb->prepare( $total_query, $where_params ) );
        } else {
            $total = (int) $wpdb->get_var( $total_query );
        }
        $total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;

        if ( $total_pages < 1 ) {
            $total_pages = 1;
        }

        if ( $page > $total_pages ) {
            $page = $total_pages;
        }

        $offset = ( $page - 1 ) * $per_page;

        if ( $offset < 0 ) {
            $offset = 0;
        }

        $entities = array();

        if ( $total > 0 ) {
            $select_query = "SELECT * FROM $table";

            if ( $where_sql ) {
                $select_query .= ' ' . $where_sql;
            }

            $select_query .= ' ORDER BY first_name ASC, last_name ASC, id ASC LIMIT %d OFFSET %d';

            $select_params   = $where_params;
            $select_params[] = $per_page;
            $select_params[] = $offset;

            $entities = $wpdb->get_results(
                $wpdb->prepare(
                    $select_query,
                    $select_params
                ),
                ARRAY_A
            );

            if ( is_array( $entities ) ) {
                foreach ( $entities as &$entity ) {
                    if ( ! is_array( $entity ) ) {
                        $entity = array();
                        continue;
                    }

                    $entity = $this->prepare_student_entity( $entity );
                }
                unset( $entity );
            } else {
                $entities = array();
            }
        }

        if ( $entities ) {
            $unique_ids = array();

            foreach ( $entities as $entity ) {
                if ( isset( $entity['uniquestudentid'] ) && is_scalar( $entity['uniquestudentid'] ) ) {
                    $unique_id = sanitize_text_field( (string) $entity['uniquestudentid'] );

                    if ( '' !== $unique_id ) {
                        $unique_ids[] = $unique_id;
                    }
                }
            }

            $history_map = $this->get_student_history_entries( array_unique( $unique_ids ) );

            foreach ( $entities as &$entity ) {
                $unique_id = isset( $entity['uniquestudentid'] ) && is_scalar( $entity['uniquestudentid'] ) ? (string) $entity['uniquestudentid'] : '';
                $entity['studenthistory'] = isset( $history_map[ $unique_id ] ) ? array_values( $history_map[ $unique_id ] ) : array();
            }
            unset( $entity );
        }

        $this->maybe_delay( $start, 0 );
        wp_send_json_success(
            array(
                'entities'    => $entities,
                'page'        => $page,
                'per_page'    => $per_page,
                'total'       => $total,
                'total_pages' => $total_pages,
            )
        );
    }

    public function assign_student_representative() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! is_user_logged_in() ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You must be logged in to add a student.', 'teqcidb' ),
                )
            );
        }

        $student_id = isset( $_POST['student_id'] ) ? absint( $_POST['student_id'] ) : 0;

        if ( $student_id <= 0 ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Missing student selection.', 'teqcidb' ),
                )
            );
        }

        $current_user = wp_get_current_user();

        if ( ! $current_user instanceof WP_User || ! $current_user->exists() ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to load your account details.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'teqcidb_students';
        $like       = $wpdb->esc_like( $table_name );
        $found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $table_name ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Student records are not available.', 'teqcidb' ),
                )
            );
        }

        $student_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE id = %d LIMIT 1",
                $student_id
            )
        );

        if ( ! $student_exists ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to locate the selected student.', 'teqcidb' ),
                )
            );
        }

        $representative = $this->build_representative_contact_from_user( $current_user, $table_name );

        if ( empty( $representative['email'] ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Representative details are incomplete.', 'teqcidb' ),
                )
            );
        }

        $updated = $wpdb->update(
            $table_name,
            array(
                'their_representative' => wp_json_encode( $representative ),
            ),
            array(
                'id' => $student_id,
            ),
            array( '%s' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to update the student representative.', 'teqcidb' ),
                )
            );
        }

        $this->maybe_delay( $start, 0 );
        wp_send_json_success(
            array(
                'message' => __( 'Student representative updated.', 'teqcidb' ),
            )
        );
    }

    public function save_email_template() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        $template_id = isset( $_POST['template_id'] ) ? sanitize_key( wp_unslash( $_POST['template_id'] ) ) : '';

        if ( '' === $template_id ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid template selection.', 'teqcidb' ),
                )
            );
        }

        $from_name = isset( $_POST['from_name'] ) ? TEQCIDB_Email_Template_Helper::sanitize_from_name( wp_unslash( $_POST['from_name'] ) ) : '';
        $from_email = isset( $_POST['from_email'] ) ? TEQCIDB_Email_Template_Helper::sanitize_from_email( wp_unslash( $_POST['from_email'] ) ) : '';
        $subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
        $body    = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
        $sms     = isset( $_POST['sms'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sms'] ) ) : '';

        TEQCIDB_Email_Template_Helper::update_template_settings(
            $template_id,
            array(
                'from_name'  => $from_name,
                'from_email' => $from_email,
                'subject'    => $subject,
                'body'       => $body,
                'sms'        => $sms,
            )
        );

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => __( 'Template saved.', 'teqcidb' ),
            )
        );
    }

    public function send_test_email() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        $template_id = isset( $_POST['template_id'] ) ? sanitize_key( wp_unslash( $_POST['template_id'] ) ) : '';

        if ( '' === $template_id ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid template selection.', 'teqcidb' ),
                )
            );
        }

        $to_email = isset( $_POST['to_email'] ) ? sanitize_email( wp_unslash( $_POST['to_email'] ) ) : '';

        if ( ! $to_email || ! is_email( $to_email ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please provide a valid email address.', 'teqcidb' ),
                )
            );
        }

        $from_name = isset( $_POST['from_name'] ) ? TEQCIDB_Email_Template_Helper::sanitize_from_name( wp_unslash( $_POST['from_name'] ) ) : '';
        $from_email = isset( $_POST['from_email'] ) ? TEQCIDB_Email_Template_Helper::sanitize_from_email( wp_unslash( $_POST['from_email'] ) ) : '';
        $subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
        $body    = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';

        $stored_settings = TEQCIDB_Email_Template_Helper::get_template_settings( $template_id );

        if ( '' === $from_name && isset( $stored_settings['from_name'] ) ) {
            $from_name = TEQCIDB_Email_Template_Helper::sanitize_from_name( $stored_settings['from_name'] );
        }

        if ( '' === $from_email && isset( $stored_settings['from_email'] ) ) {
            $from_email = TEQCIDB_Email_Template_Helper::sanitize_from_email( $stored_settings['from_email'] );
        }

        $from_name  = TEQCIDB_Email_Template_Helper::resolve_from_name( $from_name );
        $from_email = TEQCIDB_Email_Template_Helper::resolve_from_email( $from_email );

        $tokens = TEQCIDB_Student_Helper::get_first_preview_data();

        if ( ! empty( $tokens ) ) {
            $subject = $this->replace_template_tokens( $subject, $tokens );
            $body    = $this->replace_template_tokens( $body, $tokens );
        }

        $rendered_body = $body;

        if ( $rendered_body && ! preg_match( '/<[a-z][\s\S]*>/i', $rendered_body ) ) {
            $rendered_body = nl2br( esc_html( $rendered_body ) );
        }

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        $from_header = TEQCIDB_Email_Template_Helper::build_from_header( $from_name, $from_email );

        if ( $from_header ) {
            $headers[] = $from_header;
        }
        $sent    = wp_mail( $to_email, $subject, $rendered_body, $headers );

        $this->maybe_delay( $start );

        if ( ! $sent ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to send the test email. Please try again.', 'teqcidb' ),
                )
            );
        }

        $current_user = wp_get_current_user();
        $triggered_by = '';

        if ( $current_user instanceof WP_User && $current_user->exists() ) {
            $name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
            $identifier = $current_user->user_login;

            if ( $identifier && $identifier !== $name ) {
                $name .= ' (' . $identifier . ')';
            }

            if ( $current_user->user_email ) {
                $name .= ' <' . $current_user->user_email . '>';
            }

            $triggered_by = $name;
        }

        TEQCIDB_Email_Log_Helper::log_email(
            array(
                'template_id'    => $template_id,
                'template_title' => TEQCIDB_Email_Template_Helper::get_template_label( $template_id ),
                'recipient'      => $to_email,
                'from_name'      => $from_name,
                'from_email'     => $from_email,
                'subject'        => $subject,
                'body'           => $rendered_body,
                'context'        => __( 'Test email', 'teqcidb' ),
                'triggered_by'   => $triggered_by,
            )
        );

        wp_send_json_success(
            array(
                'message' => __( 'Test email sent.', 'teqcidb' ),
            )
        );
    }

    public function clear_email_log() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        if ( ! TEQCIDB_Email_Log_Helper::is_log_available() ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Email logging is unavailable. Check directory permissions and try again.', 'teqcidb' ),
                )
            );
        }

        $cleared = TEQCIDB_Email_Log_Helper::clear_log();

        $this->maybe_delay( $start );

        if ( ! $cleared ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to clear the email log. Please try again.', 'teqcidb' ),
                )
            );
        }

        wp_send_json_success(
            array(
                'message' => __( 'Email log cleared.', 'teqcidb' ),
            )
        );
    }

    private function get_email_templates_option_name() {
        /**
         * Filter the option name used to store email template settings.
         *
         * @param string $option_name Default option name.
         */
        return TEQCIDB_Email_Template_Helper::get_option_name();
    }

    private function get_post_value( $key ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return null;
        }

        $value = $_POST[ $key ];

        if ( is_array( $value ) ) {
            return array_map( 'wp_unslash', $value );
        }

        return wp_unslash( $value );
    }

    private function sanitize_text_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = implode( ',', $value );
        }

        return $this->normalize_plain_text( $value );
    }

    private function replace_template_tokens( $content, $tokens ) {
        if ( ! is_string( $content ) || '' === $content || empty( $tokens ) || ! is_array( $tokens ) ) {
            return $content;
        }

        foreach ( $tokens as $key => $value ) {
            if ( ! is_scalar( $value ) ) {
                continue;
            }

            $token = '{' . $key . '}';
            $content = str_replace( $token, (string) $value, $content );
        }

        return $content;
    }

    private function sanitize_select_value( $key, $allowed, $allow_empty = true ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return $allow_empty ? '' : reset( $allowed );
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $value = sanitize_text_field( $value );

        if ( '' === $value && $allow_empty ) {
            return '';
        }

        if ( in_array( $value, $allowed, true ) ) {
            return $value;
        }

        return $allow_empty ? '' : reset( $allowed );
    }

    private function sanitize_date_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $value = sanitize_text_field( $value );

        if ( '' === $value ) {
            return '';
        }

        $date = date_create_from_format( 'Y-m-d', $value );

        if ( ! $date ) {
            return '';
        }

        return $date->format( 'Y-m-d' );
    }

    private function split_legacy_rows( $raw_record ) {
        $normalized = trim( (string) $raw_record );

        if ( '' === $normalized ) {
            return array();
        }

        $lines = preg_split( '/\r\n|\r|\n/', $normalized );
        $lines = array_filter(
            array_map(
                function ( $line ) {
                    return trim( (string) $line, ",; \t\n\r\0\x0B" );
                },
                (array) $lines
            )
        );

        if ( count( $lines ) > 1 ) {
            return $lines;
        }

        $matches = array();
        preg_match_all( '/\([^()]*\)/', $normalized, $matches );

        if ( ! empty( $matches[0] ) ) {
            return $matches[0];
        }

        return array( $normalized );
    }

    private function add_legacy_skipped_row_message( array &$messages, $row_number, $reason ) {
        $reason = (string) $reason;

        $row = absint( $row_number );

        if ( $row > 0 ) {
            $messages[] = sprintf(
                /* translators: %d: legacy upload row number. */
                __( 'Row %d: %s', 'teqcidb' ),
                $row,
                $reason
            );
            return;
        }

        $messages[] = $reason;
    }

    private function parse_legacy_student_record( $raw_record ) {
        $normalized = trim( (string) $raw_record );

        if ( '' === $normalized ) {
            return new WP_Error( 'teqcidb_legacy_empty', __( 'Please paste a legacy student row before uploading.', 'teqcidb' ) );
        }

        $normalized = trim( $normalized, ",; \t\n\r\0\x0B" );

        if ( '(' === substr( $normalized, 0, 1 ) && ')' === substr( $normalized, -1 ) ) {
            $normalized = substr( $normalized, 1, -1 );
        }

        $normalized = trim( $normalized );

        $values = str_getcsv( $normalized, ',', "'" );

        if ( ! is_array( $values ) || empty( $values ) ) {
            return new WP_Error( 'teqcidb_legacy_parse', __( 'The legacy record could not be parsed. Please verify the comma-separated format.', 'teqcidb' ) );
        }

        $columns = array(
            'ID',
            'wpuserid',
            'uniquestudentid',
            'firstname',
            'lastname',
            'company',
            'contactstreetaddress',
            'contactcity',
            'contactstate',
            'contactzip',
            'billingstreetaddress',
            'billingcity',
            'billingstate',
            'billingzip',
            'phonecell',
            'phoneoffice',
            'fax',
            'email',
            'studentimage1',
            'studentimage2',
            'initialtrainingdate',
            'lastrefresherdate',
            'altcontactname',
            'altcontactemail',
            'altcontactphone',
            'newpaymentflag',
            'newregistrantflag',
            'allpaymentamounts',
            'allpaymentdates',
            'associations',
            'expirationdate',
            'qcinumber',
            'comments',
        );

        if ( count( $values ) < count( $columns ) ) {
            return new WP_Error(
                'teqcidb_legacy_columns',
                sprintf(
                    /* translators: 1: expected column count, 2: provided column count. */
                    __( 'The legacy record is missing columns. Expected %1$d values but found %2$d.', 'teqcidb' ),
                    count( $columns ),
                    count( $values )
                )
            );
        }

        $values = array_slice( $values, 0, count( $columns ) );

        $record = array();

        foreach ( $columns as $index => $column_key ) {
            $record[ $column_key ] = isset( $values[ $index ] ) ? $this->normalize_legacy_value( $values[ $index ] ) : '';
        }

        return $record;
    }

    private function parse_legacy_class_record( $raw_record ) {
        $normalized = trim( (string) $raw_record );

        if ( '' === $normalized ) {
            return new WP_Error( 'teqcidb_legacy_class_empty', __( 'Please paste a legacy class row before uploading.', 'teqcidb' ) );
        }

        $normalized = trim( $normalized, ",; \t\n\r\0\x0B" );

        if ( '(' === substr( $normalized, 0, 1 ) && ')' === substr( $normalized, -1 ) ) {
            $normalized = substr( $normalized, 1, -1 );
        }

        $normalized = trim( $normalized );

        $values = str_getcsv( $normalized, ',', "'" );

        if ( ! is_array( $values ) || empty( $values ) ) {
            return new WP_Error( 'teqcidb_legacy_class_parse', __( 'The legacy record could not be parsed. Please verify the comma-separated format.', 'teqcidb' ) );
        }

        $columns = array(
            'ID',
            'uniqueclassid',
            'classname',
            'classformat',
            'classtype',
            'classsize',
            'classregistrantnumber',
            'instructors',
            'classstreetaddress',
            'classcity',
            'classstate',
            'classzip',
            'classstartdate',
            'classstarttime',
            'classendtime',
            'classcost',
            'classdescription',
            'classhide',
        );

        if ( count( $values ) < count( $columns ) ) {
            return new WP_Error(
                'teqcidb_legacy_class_columns',
                sprintf(
                    /* translators: 1: expected column count, 2: provided column count. */
                    __( 'The legacy record is missing columns. Expected %1$d values but found %2$d.', 'teqcidb' ),
                    count( $columns ),
                    count( $values )
                )
            );
        }

        $values = array_slice( $values, 0, count( $columns ) );

        $record = array();

        foreach ( $columns as $index => $column_key ) {
            $record[ $column_key ] = isset( $values[ $index ] ) ? $this->normalize_legacy_value( $values[ $index ] ) : '';
        }

        return $record;
    }

    private function parse_legacy_student_history_record( $raw_record ) {
        $normalized = trim( (string) $raw_record );

        if ( '' === $normalized ) {
            return new WP_Error( 'teqcidb_legacy_history_empty', __( 'Please paste a legacy student history row before uploading.', 'teqcidb' ) );
        }

        $normalized = trim( $normalized, ",; \t\n\r\0\x0B" );

        if ( '(' === substr( $normalized, 0, 1 ) && ')' === substr( $normalized, -1 ) ) {
            $normalized = substr( $normalized, 1, -1 );
        }

        $normalized = trim( $normalized );

        $values = str_getcsv( $normalized, ',', "'" );

        if ( ! is_array( $values ) || empty( $values ) ) {
            return new WP_Error( 'teqcidb_legacy_history_parse', __( 'The legacy record could not be parsed. Please verify the comma-separated format.', 'teqcidb' ) );
        }

        $columns = array(
            'ID',
            'uniquestudentid',
            'classname',
            'wpuserid',
            'uniqueclassid',
            'registered',
            'adminapproved',
            'attended',
            'outcome',
            'paymentstatus',
            'amountpaid',
            'enrollmentdate',
            'credentialsdate',
            'referencenumber',
            'transactionid',
            'lastupdated',
        );

        if ( count( $values ) < count( $columns ) ) {
            return new WP_Error(
                'teqcidb_legacy_history_columns',
                sprintf(
                    /* translators: 1: expected column count, 2: provided column count. */
                    __( 'The legacy record is missing columns. Expected %1$d values but found %2$d.', 'teqcidb' ),
                    count( $columns ),
                    count( $values )
                )
            );
        }

        $values = array_slice( $values, 0, count( $columns ) );

        $record = array();

        foreach ( $columns as $index => $column_key ) {
            $record[ $column_key ] = isset( $values[ $index ] ) ? $this->normalize_legacy_value( $values[ $index ] ) : '';
        }

        return $record;
    }

    private function map_legacy_student_record( array $legacy_record, $row_number = 0 ) {
        $unique_id = isset( $legacy_record['uniquestudentid'] ) ? sanitize_text_field( $legacy_record['uniquestudentid'] ) : '';

        if ( '' === $unique_id ) {
            return new WP_Error( 'teqcidb_legacy_unique_id', __( 'A unique student ID is required.', 'teqcidb' ) );
        }

        $email = sanitize_email( isset( $legacy_record['email'] ) ? $legacy_record['email'] : '' );

        if ( '' === $email ) {
            $email = $this->extract_email_from_unique_student_id( $unique_id );
        }

        if ( '' === $email ) {
            $email = $this->generate_legacy_placeholder_email( $legacy_record, $row_number );
        }

        $wp_user_id = $this->resolve_legacy_history_user_id( $legacy_record );

        $address = array(
            'street_1' => sanitize_text_field( isset( $legacy_record['contactstreetaddress'] ) ? $legacy_record['contactstreetaddress'] : '' ),
            'street_2' => '',
            'city'     => sanitize_text_field( isset( $legacy_record['contactcity'] ) ? $legacy_record['contactcity'] : '' ),
            'state'    => $this->normalize_legacy_state( isset( $legacy_record['contactstate'] ) ? $legacy_record['contactstate'] : '' ),
            'zip_code' => sanitize_text_field( isset( $legacy_record['contactzip'] ) ? $legacy_record['contactzip'] : '' ),
        );

        $student_address = $this->encode_legacy_address( $address );
        $representative  = $this->encode_legacy_representative( $legacy_record );

        return array(
            'wpuserid'              => $wp_user_id,
            'uniquestudentid'       => $unique_id,
            'first_name'            => sanitize_text_field( isset( $legacy_record['firstname'] ) ? $legacy_record['firstname'] : '' ),
            'last_name'             => sanitize_text_field( isset( $legacy_record['lastname'] ) ? $legacy_record['lastname'] : '' ),
            'company'               => sanitize_text_field( isset( $legacy_record['company'] ) ? $legacy_record['company'] : '' ),
            'old_companies'         => wp_json_encode( array() ),
            'student_address'       => $student_address,
            'phone_cell'            => $this->format_phone_for_response( isset( $legacy_record['phonecell'] ) ? $legacy_record['phonecell'] : '' ),
            'phone_office'          => $this->format_phone_for_response( isset( $legacy_record['phoneoffice'] ) ? $legacy_record['phoneoffice'] : '' ),
            'fax'                   => $this->format_phone_for_response( isset( $legacy_record['fax'] ) ? $legacy_record['fax'] : '' ),
            'email'                 => $email,
            'initial_training_date' => $this->convert_legacy_date( isset( $legacy_record['initialtrainingdate'] ) ? $legacy_record['initialtrainingdate'] : '' ),
            'last_refresher_date'   => $this->convert_legacy_date( isset( $legacy_record['lastrefresherdate'] ) ? $legacy_record['lastrefresherdate'] : '' ),
            'is_a_representative'   => 0,
            'their_representative'  => $representative,
            'new_class_signup_flag' => $this->convert_legacy_flag( isset( $legacy_record['newpaymentflag'] ) ? $legacy_record['newpaymentflag'] : '' ),
            'associations'          => sanitize_textarea_field( isset( $legacy_record['associations'] ) ? $legacy_record['associations'] : '' ),
            'expiration_date'       => $this->convert_legacy_date( isset( $legacy_record['expirationdate'] ) ? $legacy_record['expirationdate'] : '' ),
            'qcinumber'             => sanitize_text_field( isset( $legacy_record['qcinumber'] ) ? $legacy_record['qcinumber'] : '' ),
            'comments'              => $this->merge_legacy_comments( $legacy_record ),
        );
    }

    private function map_legacy_class_record( array $legacy_record ) {
        $unique_id = isset( $legacy_record['uniqueclassid'] ) ? sanitize_text_field( $legacy_record['uniqueclassid'] ) : '';

        if ( '' === $unique_id ) {
            return new WP_Error( 'teqcidb_legacy_class_unique_id', __( 'A unique class ID is required.', 'teqcidb' ) );
        }

        $class_name = isset( $legacy_record['classname'] ) ? sanitize_text_field( $legacy_record['classname'] ) : '';

        if ( '' === $class_name ) {
            return new WP_Error( 'teqcidb_legacy_class_name', __( 'A class name is required.', 'teqcidb' ) );
        }

        $class_format = $this->sanitize_legacy_select_value(
            isset( $legacy_record['classformat'] ) ? $legacy_record['classformat'] : '',
            array( 'inperson', 'online', 'hybrid' )
        );

        $class_type = $this->sanitize_legacy_select_value(
            isset( $legacy_record['classtype'] ) ? $legacy_record['classtype'] : '',
            array( 'initial', 'refresher' )
        );

        $class_size        = $this->normalize_legacy_value( isset( $legacy_record['classsize'] ) ? $legacy_record['classsize'] : '' );
        $registrant_number = $this->normalize_legacy_value( isset( $legacy_record['classregistrantnumber'] ) ? $legacy_record['classregistrantnumber'] : '' );

        $address = array(
            'street_1' => sanitize_text_field( isset( $legacy_record['classstreetaddress'] ) ? $legacy_record['classstreetaddress'] : '' ),
            'street_2' => '',
            'city'     => sanitize_text_field( isset( $legacy_record['classcity'] ) ? $legacy_record['classcity'] : '' ),
            'state'    => $this->normalize_legacy_state( isset( $legacy_record['classstate'] ) ? $legacy_record['classstate'] : '' ),
            'zip_code' => sanitize_text_field( isset( $legacy_record['classzip'] ) ? $legacy_record['classzip'] : '' ),
        );

        $encoded_address   = $this->encode_legacy_address( $address );
        $encoded_instructors = $this->sanitize_legacy_instructors( isset( $legacy_record['instructors'] ) ? $legacy_record['instructors'] : '' );

        return array(
            'uniqueclassid'         => $unique_id,
            'classname'             => $class_name,
            'classformat'           => $class_format,
            'classtype'             => $class_type,
            'classsize'             => '' === $class_size ? null : absint( $class_size ),
            'classregistrantnumber' => '' === $registrant_number ? 0 : absint( $registrant_number ),
            'instructors'           => $encoded_instructors,
            'classsaddress'         => $encoded_address,
            'classstartdate'        => $this->convert_legacy_date( isset( $legacy_record['classstartdate'] ) ? $legacy_record['classstartdate'] : '' ),
            'classstarttime'        => $this->convert_legacy_time( isset( $legacy_record['classstarttime'] ) ? $legacy_record['classstarttime'] : '' ),
            'classendtime'          => $this->convert_legacy_time( isset( $legacy_record['classendtime'] ) ? $legacy_record['classendtime'] : '' ),
            'classcost'             => $this->convert_legacy_cost( isset( $legacy_record['classcost'] ) ? $legacy_record['classcost'] : '' ),
            'classdescription'      => sanitize_textarea_field( isset( $legacy_record['classdescription'] ) ? $legacy_record['classdescription'] : '' ),
            'classhide'             => $this->convert_legacy_flag( isset( $legacy_record['classhide'] ) ? $legacy_record['classhide'] : '' ),
        );
    }

    private function map_legacy_student_history_record( array $legacy_record ) {
        $unique_student_id = isset( $legacy_record['uniquestudentid'] ) ? sanitize_text_field( $legacy_record['uniquestudentid'] ) : '';

        if ( '' === $unique_student_id ) {
            return new WP_Error( 'teqcidb_legacy_history_unique_id', __( 'A unique student ID is required.', 'teqcidb' ) );
        }

        $class_name = isset( $legacy_record['classname'] ) ? sanitize_text_field( $legacy_record['classname'] ) : '';

        if ( '' === $class_name ) {
            return new WP_Error( 'teqcidb_legacy_history_class', __( 'A class name is required for student history records.', 'teqcidb' ) );
        }

        $unique_class_id = isset( $legacy_record['uniqueclassid'] ) ? sanitize_text_field( $legacy_record['uniqueclassid'] ) : '';

        if ( '' === $unique_class_id ) {
            return new WP_Error( 'teqcidb_legacy_history_class_id', __( 'A unique class ID is required for student history records.', 'teqcidb' ) );
        }

        $wp_user_id = $this->resolve_legacy_history_user_id( $legacy_record );

        return array(
            'uniquestudentid'  => $unique_student_id,
            'wpuserid'         => $wp_user_id,
            'classname'        => $class_name,
            'uniqueclassid'    => $unique_class_id,
            'registered'       => $this->convert_legacy_history_status( isset( $legacy_record['registered'] ) ? $legacy_record['registered'] : '', array( 'yes' => 'Yes', 'no' => 'No', 'pending' => 'Pending' ), 'Pending' ),
            'adminapproved'    => $this->convert_legacy_admin_approval( isset( $legacy_record['adminapproved'] ) ? $legacy_record['adminapproved'] : '' ),
            'attended'         => $this->convert_legacy_history_status( isset( $legacy_record['attended'] ) ? $legacy_record['attended'] : '', array( 'yes' => 'Yes', 'no' => 'No', 'upcoming' => 'Upcoming' ), 'Upcoming' ),
            'outcome'          => $this->convert_legacy_history_status( isset( $legacy_record['outcome'] ) ? $legacy_record['outcome'] : '', array( 'upcoming' => 'Upcoming', 'passed' => 'Passed', 'deferred' => 'Deferred', 'failed' => 'Failed' ), 'Upcoming' ),
            'paymentstatus'    => $this->convert_legacy_history_payment_status( isset( $legacy_record['paymentstatus'] ) ? $legacy_record['paymentstatus'] : '' ),
            'amountpaid'       => $this->convert_legacy_cost( isset( $legacy_record['amountpaid'] ) ? $legacy_record['amountpaid'] : '' ),
            'enrollmentdate'   => $this->convert_legacy_date( isset( $legacy_record['enrollmentdate'] ) ? $legacy_record['enrollmentdate'] : '' ),
            'registeredby'     => $wp_user_id,
            'courseinprogress' => 'no',
            'quizinprogress'   => 'no',
        );
    }

    private function resolve_legacy_history_user_id( array $legacy_record ) {
        $legacy_wp_user_id = isset( $legacy_record['wpuserid'] ) ? absint( $legacy_record['wpuserid'] ) : 0;

        if ( $legacy_wp_user_id > 0 ) {
            $existing_user = get_user_by( 'id', $legacy_wp_user_id );

            if ( $existing_user ) {
                return (int) $existing_user->ID;
            }
        }

        $unique_student_id = isset( $legacy_record['uniquestudentid'] ) ? $legacy_record['uniquestudentid'] : '';
        $email             = $this->extract_email_from_unique_student_id( $unique_student_id );

        if ( '' === $email ) {
            return null;
        }

        $user = get_user_by( 'email', $email );

        if ( ! $user ) {
            return null;
        }

        return (int) $user->ID;
    }

    private function extract_email_from_unique_student_id( $unique_student_id ) {
        $normalized = $this->normalize_legacy_value( $unique_student_id );

        if ( '' === $normalized ) {
            return '';
        }

        if ( preg_match( '/^(.+@.+?)(\d{5,})$/', $normalized, $matches ) ) {
            $email = sanitize_email( $matches[1] );

            if ( is_email( $email ) ) {
                return $email;
            }
        }

        $trimmed = $normalized;

        while ( strlen( $trimmed ) > 0 && ctype_digit( substr( $trimmed, -1 ) ) ) {
            $trimmed = substr( $trimmed, 0, -1 );
            $email   = sanitize_email( $trimmed );

            if ( is_email( $email ) ) {
                return $email;
            }
        }

        $fallback = sanitize_email( $normalized );

        if ( is_email( $fallback ) ) {
            return $fallback;
        }

        return '';
    }

    private function convert_legacy_date( $value ) {
        $value = $this->normalize_legacy_value( $value );

        if ( '' === $value ) {
            return null;
        }

        $value = str_replace( '.', '-', $value );

        $parsed = date_create( $value );

        if ( ! $parsed ) {
            return null;
        }

        return $parsed->format( 'Y-m-d' );
    }

    private function convert_legacy_time( $value ) {
        $value = $this->normalize_legacy_value( $value );

        if ( '' === $value ) {
            return null;
        }

        $value = str_replace( array( '.', ' ' ), ':', $value );

        $time_formats = array( 'H:i:s', 'H:i' );

        foreach ( $time_formats as $format ) {
            $parsed = date_create_from_format( $format, $value );

            if ( $parsed ) {
                return $parsed->format( 'H:i:s' );
            }
        }

        return '';
    }

    private function convert_legacy_cost( $value ) {
        $value = $this->normalize_legacy_value( $value );

        if ( '' === $value ) {
            return null;
        }

        $numeric = preg_replace( '/[^0-9.\-]/', '', $value );

        if ( '' === $numeric ) {
            return null;
        }

        return number_format( (float) $numeric, 2, '.', '' );
    }

    private function convert_legacy_history_status( $value, array $mapping, $default ) {
        $normalized = strtolower( $this->normalize_legacy_value( $value ) );

        return isset( $mapping[ $normalized ] ) ? $mapping[ $normalized ] : $default;
    }

    private function convert_legacy_admin_approval( $value ) {
        $normalized = strtolower( $this->normalize_legacy_value( $value ) );

        if ( '' === $normalized || 'null' === $normalized ) {
            return null;
        }

        if ( in_array( $normalized, array( 'pending', 'pendingapproval', 'pending approval' ), true ) ) {
            return 'Pending Approval';
        }

        if ( in_array( $normalized, array( 'yes', 'approved', 'approve' ), true ) ) {
            return 'Yes';
        }

        if ( in_array( $normalized, array( 'no', 'denied', 'declined' ), true ) ) {
            return 'No';
        }

        return null;
    }

    private function convert_legacy_history_payment_status( $value ) {
        $normalized = strtolower( $this->normalize_legacy_value( $value ) );

        if ( 'paidinfull' === $normalized || 'paid' === $normalized ) {
            return 'Paid in Full';
        }

        if ( 'nopaymentmade' === $normalized || 'none' === $normalized ) {
            return 'No Payment Made';
        }

        return 'Pending';
    }

    private function sanitize_legacy_select_value( $value, array $allowed ) {
        $value = sanitize_key( $this->normalize_legacy_value( $value ) );

        if ( '' === $value ) {
            return '';
        }

        return in_array( $value, $allowed, true ) ? $value : '';
    }

    private function sanitize_legacy_instructors( $value ) {
        $normalized = $this->normalize_legacy_value( $value );

        if ( '' === $normalized ) {
            return '';
        }

        $instructors = array_filter( array_map( 'trim', explode( ',', $normalized ) ) );

        if ( empty( $instructors ) ) {
            return '';
        }

        return wp_json_encode(
            array(
                'instructors' => array_values( $instructors ),
            )
        );
    }

    private function encode_legacy_address( array $address ) {
        $has_value = false;

        foreach ( $address as $value ) {
            if ( '' !== $value ) {
                $has_value = true;
                break;
            }
        }

        return $has_value ? wp_json_encode( $address ) : '';
    }

    private function convert_legacy_flag( $value ) {
        $normalized = strtolower( $this->normalize_legacy_value( $value ) );
        $truthy     = array( '1', 'true', 'yes', 'y', 'on', 't' );

        return in_array( $normalized, $truthy, true ) ? 1 : 0;
    }

    private function encode_legacy_representative( array $legacy_record ) {
        $name  = isset( $legacy_record['altcontactname'] ) ? $legacy_record['altcontactname'] : '';
        $email = sanitize_email( isset( $legacy_record['altcontactemail'] ) ? $legacy_record['altcontactemail'] : '' );
        $phone = $this->format_phone_for_response( isset( $legacy_record['altcontactphone'] ) ? $legacy_record['altcontactphone'] : '' );

        $name_parts = $this->split_legacy_name( $name );

        if ( '' === $name_parts['first_name'] && '' === $name_parts['last_name'] && '' === $email && '' === $phone ) {
            return '';
        }

        $contact = array(
            'first_name' => $name_parts['first_name'],
            'last_name'  => $name_parts['last_name'],
            'email'      => $email,
            'phone'      => $phone,
        );

        $contact = $this->attach_representative_lookup_data( $contact );

        return wp_json_encode( $contact );
    }

    private function attach_representative_lookup_data( array $contact ) {
        $email = isset( $contact['email'] ) ? $contact['email'] : '';

        if ( '' === $email ) {
            return $contact;
        }

        $user_id = $this->find_user_id_by_email( $email );

        if ( null !== $user_id ) {
            $contact['wpuserid'] = $user_id;
        }

        $unique_id = $this->find_student_unique_id_by_email_fragment( $email );

        if ( '' !== $unique_id ) {
            $contact['uniquestudentid'] = $unique_id;
        }

        return $contact;
    }

    private function split_legacy_name( $name ) {
        $name = $this->normalize_legacy_value( $name );

        if ( '' === $name ) {
            return array(
                'first_name' => '',
                'last_name'  => '',
            );
        }

        if ( false !== strpos( $name, ',' ) ) {
            $parts = array_map( 'trim', explode( ',', $name, 2 ) );

            return array(
                'first_name' => isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '',
                'last_name'  => isset( $parts[0] ) ? sanitize_text_field( $parts[0] ) : '',
            );
        }

        $parts = preg_split( '/\s+/', $name, 2 );

        return array(
            'first_name' => isset( $parts[0] ) ? sanitize_text_field( $parts[0] ) : '',
            'last_name'  => isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '',
        );
    }

    private function merge_legacy_comments( array $legacy_record ) {
        $comment = sanitize_textarea_field( isset( $legacy_record['comments'] ) ? $legacy_record['comments'] : '' );

        if ( '' === $comment ) {
            return '';
        }

        return $comment;
    }

    private function format_legacy_address( $street, $city, $state, $postal_code ) {
        $parts = array();

        $street = $this->normalize_legacy_value( $street );
        $city   = $this->normalize_legacy_value( $city );
        $state  = $this->normalize_legacy_state( $state );
        $postal = $this->normalize_legacy_value( $postal_code );

        if ( '' !== $street ) {
            $parts[] = sanitize_text_field( $street );
        }

        $city_state_zip = array();

        if ( '' !== $city ) {
            $city_state_zip[] = sanitize_text_field( $city );
        }

        if ( '' !== $state ) {
            $city_state_zip[] = $state;
        }

        if ( '' !== $postal ) {
            $city_state_zip[] = sanitize_text_field( $postal );
        }

        if ( ! empty( $city_state_zip ) ) {
            $parts[] = implode( ', ', $city_state_zip );
        }

        return implode( ' | ', $parts );
    }

    private function normalize_legacy_state( $value ) {
        $value = strtoupper( sanitize_text_field( $this->normalize_legacy_value( $value ) ) );

        return '' === $value ? '' : substr( $value, 0, 2 );
    }

    private function legacy_class_value_exists( $table, $column, $value ) {
        if ( '' === $value ) {
            return false;
        }

        global $wpdb;

        $allowed_columns = array( 'uniqueclassid' );

        if ( ! in_array( $column, $allowed_columns, true ) ) {
            return false;
        }

        $result = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE $column = %s LIMIT 1", $value ) );

        return ! empty( $result );
    }

    private function legacy_student_value_exists( $table, $column, $value ) {
        if ( '' === $value ) {
            return false;
        }

        global $wpdb;

        $allowed_columns = array( 'email', 'uniquestudentid' );

        if ( ! in_array( $column, $allowed_columns, true ) ) {
            return false;
        }

        $result = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE $column = %s LIMIT 1", $value ) );

        return ! empty( $result );
    }

    private function generate_legacy_placeholder_email( array $legacy_record, $row_number = 0, $suffix = '' ) {
        $seed = sanitize_key( (string) $suffix );

        if ( '' === $seed && ! empty( $legacy_record['uniquestudentid'] ) ) {
            $seed = sanitize_key( (string) $legacy_record['uniquestudentid'] );
        }

        if ( '' === $seed && ! empty( $legacy_record['ID'] ) ) {
            $seed = 'id-' . absint( $legacy_record['ID'] );
        }

        if ( '' === $seed && $row_number ) {
            $seed = 'row-' . absint( $row_number );
        }

        if ( '' === $seed ) {
            $seed = sanitize_key( wp_generate_uuid4() );
        }

        return sprintf( 'legacy-student-%s@example.invalid', $seed );
    }

    private function generate_unique_legacy_student_email( array $legacy_record, $row_number, $table, $current_email = '' ) {
        $candidate = $this->generate_legacy_placeholder_email( $legacy_record, $row_number );

        if ( '' !== $current_email && $candidate === $current_email ) {
            $candidate = $this->generate_legacy_placeholder_email( $legacy_record, $row_number, 1 );
        }

        $counter = 2;

        while ( $this->legacy_student_value_exists( $table, 'email', $candidate ) ) {
            $candidate = $this->generate_legacy_placeholder_email( $legacy_record, $row_number, $counter );
            $counter++;

            if ( $counter > 25 ) {
                $candidate = $this->generate_legacy_placeholder_email( $legacy_record, $row_number, wp_generate_uuid4() );
                break;
            }
        }

        return $candidate;
    }

    private function find_user_id_by_email( $email ) {
        $user = get_user_by( 'email', $email );

        if ( ! $user || is_wp_error( $user ) ) {
            return null;
        }

        return (int) $user->ID;
    }

    private function find_student_unique_id_by_email_fragment( $email ) {
        global $wpdb;

        $table = $wpdb->prefix . 'teqcidb_students';
        $like  = '%' . $wpdb->esc_like( $email ) . '%';

        $result = $wpdb->get_var( $wpdb->prepare( "SELECT uniquestudentid FROM $table WHERE uniquestudentid LIKE %s ORDER BY id ASC LIMIT 1", $like ) );

        return $result ? sanitize_text_field( $result ) : '';
    }

    private function normalize_legacy_value( $value ) {
        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        if ( 'NULL' === strtoupper( $value ) ) {
            return '';
        }

        return $value;
    }

    private function sanitize_positive_int_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $value = sanitize_text_field( $value );

        if ( '' === $value ) {
            return '';
        }

        return (string) absint( $value );
    }

    private function sanitize_time_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $value = sanitize_text_field( $value );

        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '/^(\d{2}):(\d{2})$/', $value, $matches ) ) {
            $hours = (int) $matches[1];
            $mins  = (int) $matches[2];

            $hours = max( 0, min( 23, $hours ) );
            $mins  = max( 0, min( 59, $mins ) );

            return sprintf( '%02d:%02d', $hours, $mins );
        }

        return '';
    }

    private function get_legacy_upload_payload( $raw_record ) {
        if ( empty( $_FILES['legacy_file'] ) || ! isset( $_FILES['legacy_file']['tmp_name'] ) ) {
            return $raw_record;
        }

        $file = $_FILES['legacy_file'];
        $error = isset( $file['error'] ) ? absint( $file['error'] ) : UPLOAD_ERR_OK;

        if ( UPLOAD_ERR_NO_FILE === $error ) {
            return $raw_record;
        }

        if ( UPLOAD_ERR_OK !== $error || empty( $file['tmp_name'] ) ) {
            return new WP_Error(
                'teqcidb_legacy_upload_file_error',
                __( 'Unable to read the uploaded legacy file. Please try again.', 'teqcidb' )
            );
        }

        $contents = file_get_contents( $file['tmp_name'] );

        if ( false === $contents ) {
            return new WP_Error(
                'teqcidb_legacy_upload_file_read',
                __( 'Unable to process the uploaded legacy file. Please ensure it is a valid .sql or text export.', 'teqcidb' )
            );
        }

        return $contents;
    }

    private function extend_legacy_upload_limits() {
        if ( function_exists( 'wp_raise_memory_limit' ) ) {
            wp_raise_memory_limit( 'admin' );
        }

        if ( function_exists( 'set_time_limit' ) ) {
            @set_time_limit( 0 );
        }
    }

    private function sanitize_decimal_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '0.00';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '0.00';
        }

        $normalized = preg_replace( '/[^0-9\-\.]/', '', $value );

        if ( '' === $normalized ) {
            return '0.00';
        }

        return number_format( (float) $normalized, 2, '.', '' );
    }

    private function sanitize_url_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        return esc_url_raw( $value );
    }

    private function sanitize_state_value( $key, $allowed_states ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $value = sanitize_text_field( $value );

        if ( '' === $value ) {
            return '';
        }

        if ( in_array( $value, $allowed_states, true ) ) {
            return $value;
        }

        return '';
    }

    private function sanitize_checkbox_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '0';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        return ! empty( $value ) ? '1' : '0';
    }

    private function sanitize_opt_in_summary( $keys ) {
        $selected = array();

        foreach ( $keys as $key ) {
            if ( '1' === $this->sanitize_checkbox_value( $key ) ) {
                $selected[] = $key;
            }
        }

        return wp_json_encode( $selected );
    }

    private function sanitize_items_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return wp_json_encode( array() );
        }

        $items = array();

        if ( is_array( $value ) ) {
            foreach ( $value as $item ) {
                $item = $this->normalize_plain_text( $item );

                if ( '' !== $item ) {
                    $items[] = $item;
                }
            }
        } else {
            $value = sanitize_textarea_field( $value );
            $split = preg_split( '/\r?\n/', $value );

            if ( is_array( $split ) ) {
                foreach ( $split as $item ) {
                    $item = $this->normalize_plain_text( $item );

                    if ( '' !== $item ) {
                        $items[] = $item;
                    }
                }
            }
        }

        return wp_json_encode( $items );
    }

    private function sanitize_student_access_items( $key ) {
        $labels     = $this->get_post_value( $key );
        $wp_ids     = $this->get_post_value( $key . '_wpuserid' );
        $unique_ids = $this->get_post_value( $key . '_uniquestudentid' );

        $labels     = null === $labels ? array() : ( is_array( $labels ) ? array_values( $labels ) : array( $labels ) );
        $wp_ids     = null === $wp_ids ? array() : ( is_array( $wp_ids ) ? array_values( $wp_ids ) : array( $wp_ids ) );
        $unique_ids = null === $unique_ids ? array() : ( is_array( $unique_ids ) ? array_values( $unique_ids ) : array( $unique_ids ) );

        $max_items = max( count( $labels ), count( $wp_ids ), count( $unique_ids ) );
        $items     = array();

        for ( $i = 0; $i < $max_items; $i++ ) {
            $label    = isset( $labels[ $i ] ) ? $this->normalize_plain_text( $labels[ $i ] ) : '';
            $wp_id    = isset( $wp_ids[ $i ] ) ? absint( $wp_ids[ $i ] ) : 0;
            $uniqueid = isset( $unique_ids[ $i ] ) ? $this->normalize_plain_text( $unique_ids[ $i ] ) : '';

            if ( '' === $label && 0 === $wp_id && '' === $uniqueid ) {
                continue;
            }

            $items[] = array(
                'label'          => $label,
                'wpuserid'       => $wp_id ? (string) $wp_id : '',
                'uniquestudentid' => $uniqueid,
            );
        }

        return wp_json_encode( $items );
    }

    private function normalize_plain_text( $value ) {
        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $value = sanitize_text_field( (string) wp_unslash( $value ) );

        if ( '' === $value ) {
            return '';
        }

        return wp_specialchars_decode( $value, ENT_QUOTES );
    }

    private function sanitize_color_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $value = sanitize_hex_color( $value );

        return $value ? $value : '';
    }

    private function sanitize_image_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        return (string) absint( $value );
    }

    private function sanitize_editor_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        return wp_kses_post( $value );
    }

    private function sanitize_email_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        $email = sanitize_email( $value );

        return $email ? $email : '';
    }

    private function sanitize_textarea_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        return sanitize_textarea_field( $value );
    }

    private function sanitize_yes_no_value( $key ) {
        return $this->sanitize_select_value( $key, array( '0', '1' ) );
    }

    private function sanitize_phone_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return '';
        }

        return $this->format_phone_for_response( $value );
    }

    private function sanitize_class_resources_value( $key ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return null;
        }

        if ( ! is_array( $value ) ) {
            return '';
        }

        $names = isset( $value['name'] ) && is_array( $value['name'] ) ? $value['name'] : array();
        $types = isset( $value['type'] ) && is_array( $value['type'] ) ? $value['type'] : array();
        $urls  = isset( $value['url'] ) && is_array( $value['url'] ) ? $value['url'] : array();

        $max_count = max( count( $names ), count( $types ), count( $urls ) );

        if ( $max_count < 1 ) {
            return '';
        }

        $allowed_types = array( 'pdf', 'video', 'external_link' );
        $resources     = array();

        for ( $index = 0; $index < $max_count; $index++ ) {
            $name = isset( $names[ $index ] ) ? $this->normalize_plain_text( $names[ $index ] ) : '';
            $type = isset( $types[ $index ] ) ? sanitize_text_field( $types[ $index ] ) : '';
            $url  = isset( $urls[ $index ] ) ? esc_url_raw( $urls[ $index ] ) : '';

            if ( ! in_array( $type, $allowed_types, true ) ) {
                $type = '';
            }

            if ( '' === $name && '' === $type && '' === $url ) {
                continue;
            }

            $resources[] = array(
                'name' => $name,
                'type' => $type,
                'url'  => $url,
            );
        }

        if ( empty( $resources ) ) {
            return '';
        }

        return wp_json_encode( $resources );
    }

    private function sanitize_class_address() {
        $states = $this->get_us_states_and_territories();

        $address = array(
            'street_1' => $this->sanitize_text_value( 'class_address_street_1' ),
            'street_2' => $this->sanitize_text_value( 'class_address_street_2' ),
            'city'     => $this->sanitize_text_value( 'class_address_city' ),
            'state'    => $this->sanitize_state_value( 'class_address_state', $states ),
            'zip_code' => $this->sanitize_text_value( 'class_address_postal_code' ),
        );

        $has_value = false;

        foreach ( $address as $part ) {
            if ( '' !== $part ) {
                $has_value = true;
                break;
            }
        }

        return $has_value ? wp_json_encode( $address ) : '';
    }

    private function sanitize_student_address() {
        $states = $this->get_us_states_and_territories();

        $address = array(
            'street_1'    => $this->sanitize_text_value( 'student_address_street_1' ),
            'street_2'    => $this->sanitize_text_value( 'student_address_street_2' ),
            'city'        => $this->sanitize_text_value( 'student_address_city' ),
            'state'       => $this->sanitize_state_value( 'student_address_state', $states ),
            'zip_code'    => $this->sanitize_text_value( 'student_address_postal_code' ),
        );

        $has_value = false;

        foreach ( $address as $part ) {
            if ( '' !== $part ) {
                $has_value = true;
                break;
            }
        }

        return $has_value ? wp_json_encode( $address ) : '';
    }

    private function sanitize_representative_contact() {
        global $wpdb;

        $contact = array(
            'first_name' => $this->sanitize_text_value( 'representative_first_name' ),
            'last_name'  => $this->sanitize_text_value( 'representative_last_name' ),
            'email'      => $this->sanitize_email_value( 'representative_email' ),
            'phone'      => $this->sanitize_phone_value( 'representative_phone' ),
            'wpid'       => '',
            'uniquestudentid' => '',
        );

        if ( '' !== $contact['email'] ) {
            $user = get_user_by( 'email', $contact['email'] );

            if ( $user instanceof WP_User ) {
                $contact['wpid'] = (string) $user->ID;
            }

            $students_table = $wpdb->prefix . 'teqcidb_students';
            $like           = $wpdb->esc_like( $students_table );
            $found          = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

            if ( $found === $students_table ) {
                $unique_id = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT uniquestudentid FROM $students_table WHERE email = %s LIMIT 1",
                        $contact['email']
                    )
                );

                if ( $unique_id ) {
                    $contact['uniquestudentid'] = sanitize_text_field( (string) $unique_id );
                }
            }
        }

        $has_value = false;

        foreach ( $contact as $value ) {
            if ( '' !== $value ) {
                $has_value = true;
                break;
            }
        }

        return $has_value ? wp_json_encode( $contact ) : '';
    }

    private function sanitize_associations_value( $key, array $allowed_values ) {
        $value = $this->get_post_value( $key );

        if ( null === $value ) {
            return wp_json_encode( array() );
        }

        if ( ! is_array( $value ) ) {
            $value = array( $value );
        }

        $selected = array();

        foreach ( $value as $entry ) {
            $entry = sanitize_text_field( $entry );

            if ( in_array( $entry, $allowed_values, true ) ) {
                $selected[] = $entry;
            }
        }

        $selected = array_values( array_unique( $selected ) );

        return wp_json_encode( $selected );
    }

    private function format_date_for_response( $value ) {
        if ( empty( $value ) || '0000-00-00' === $value ) {
            return '';
        }

        $date = date_create( $value );

        if ( ! $date ) {
            return '';
        }

        return $date->format( 'Y-m-d' );
    }

    private function format_time_for_response( $value ) {
        if ( empty( $value ) || '00:00:00' === $value ) {
            return '';
        }

        if ( preg_match( '/^(\d{2}:\d{2})/', $value, $matches ) ) {
            return $matches[1];
        }

        return '';
    }

    private function format_decimal_for_response( $value ) {
        if ( null === $value || '' === $value ) {
            return '0.00';
        }

        return number_format( (float) $value, 2, '.', '' );
    }

    private function format_json_field( $value ) {
        $normalize_items = static function( $list ) {
            if ( ! is_array( $list ) ) {
                return array();
            }

            $normalized = array();

            foreach ( $list as $item ) {
                if ( is_array( $item ) ) {
                    if ( isset( $item['label'] ) ) {
                        $normalized[] = (string) $item['label'];
                        continue;
                    }

                    $normalized[] = implode( ' ', array_map( 'strval', $item ) );
                    continue;
                }

                if ( is_scalar( $item ) ) {
                    $normalized[] = (string) $item;
                }
            }

            return array_values( array_filter( $normalized, 'strlen' ) );
        };

        if ( empty( $value ) ) {
            return wp_json_encode( array() );
        }

        if ( is_array( $value ) ) {
            return wp_json_encode( $normalize_items( $value ) );
        }

        $decoded = json_decode( $value, true );

        if ( is_array( $decoded ) ) {
            return wp_json_encode( $normalize_items( $decoded ) );
        }

        return wp_json_encode( array() );
    }

    private function format_color_for_response( $value ) {
        $value = sanitize_hex_color( $value );

        if ( ! $value ) {
            return '';
        }

        return $value;
    }

    private function format_editor_content_for_response( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        return wp_kses_post( $value );
    }

    private function build_representative_contact_from_user( WP_User $user, $students_table ) {
        global $wpdb;

        $contact = array(
            'first_name'      => sanitize_text_field( (string) $user->first_name ),
            'last_name'       => sanitize_text_field( (string) $user->last_name ),
            'email'           => sanitize_email( (string) $user->user_email ),
            'phone'           => '',
            'wpid'            => (string) $user->ID,
            'uniquestudentid' => '',
        );

        $student_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT first_name, last_name, email, phone_cell, phone_office, uniquestudentid FROM $students_table WHERE wpuserid = %d LIMIT 1",
                $user->ID
            ),
            ARRAY_A
        );

        if ( is_array( $student_row ) ) {
            if ( ! empty( $student_row['first_name'] ) ) {
                $contact['first_name'] = sanitize_text_field( (string) $student_row['first_name'] );
            }

            if ( ! empty( $student_row['last_name'] ) ) {
                $contact['last_name'] = sanitize_text_field( (string) $student_row['last_name'] );
            }

            if ( ! empty( $student_row['email'] ) ) {
                $contact['email'] = sanitize_email( (string) $student_row['email'] );
            }

            if ( ! empty( $student_row['phone_cell'] ) ) {
                $contact['phone'] = $this->format_phone_for_response( (string) $student_row['phone_cell'] );
            } elseif ( ! empty( $student_row['phone_office'] ) ) {
                $contact['phone'] = $this->format_phone_for_response( (string) $student_row['phone_office'] );
            }

            if ( ! empty( $student_row['uniquestudentid'] ) ) {
                $contact['uniquestudentid'] = sanitize_text_field( (string) $student_row['uniquestudentid'] );
            }
        }

        if ( '' === $contact['first_name'] && '' === $contact['last_name'] ) {
            $display_name = sanitize_text_field( (string) $user->display_name );
            $name_parts   = preg_split( '/\s+/', $display_name );

            if ( $name_parts ) {
                $contact['first_name'] = $name_parts[0];
                $contact['last_name']  = count( $name_parts ) > 1 ? implode( ' ', array_slice( $name_parts, 1 ) ) : '';
            }
        }

        return $contact;
    }

    private function format_class_student_list_for_response( $value ) {
        if ( empty( $value ) ) {
            return array();
        }

        if ( is_string( $value ) ) {
            $decoded = json_decode( $value, true );

            if ( is_array( $decoded ) ) {
                $value = $decoded;
            } else {
                $split_values = array_filter(
                    array_map( 'trim', explode( ',', $value ) ),
                    static function ( $item ) {
                        return '' !== $item;
                    }
                );

                if ( ! empty( $split_values ) ) {
                    $value = array_values( $split_values );
                }
            }
        }

        if ( ! is_array( $value ) ) {
            return array();
        }

        $items = array();

        foreach ( $value as $entry ) {
            $label     = '';
            $wp_id     = '';
            $unique_id = '';

            if ( is_array( $entry ) ) {
                if ( isset( $entry['label'] ) ) {
                    $label = $this->normalize_plain_text( $entry['label'] );
                }

                if ( isset( $entry['wpuserid'] ) && is_numeric( $entry['wpuserid'] ) ) {
                    $wp_id = (string) absint( $entry['wpuserid'] );
                }

                if ( isset( $entry['uniquestudentid'] ) && is_scalar( $entry['uniquestudentid'] ) ) {
                    $unique_id = $this->normalize_plain_text( $entry['uniquestudentid'] );
                }
            } elseif ( is_scalar( $entry ) ) {
                $label = $this->normalize_plain_text( $entry );
            }

            if ( '' === $label && '' === $wp_id && '' === $unique_id ) {
                continue;
            }

            $items[] = array(
                'label'           => $label,
                'wpuserid'        => $wp_id,
                'uniquestudentid' => $unique_id,
            );
        }

        return $items;
    }

    private function format_class_label_list_for_response( $value ) {
        if ( empty( $value ) ) {
            return array();
        }

        if ( is_string( $value ) ) {
            $decoded = json_decode( $value, true );

            if ( is_array( $decoded ) ) {
                $value = $decoded;
            }
        }

        if ( ! is_array( $value ) ) {
            return array();
        }

        $items = array();

        foreach ( $value as $entry ) {
            if ( is_scalar( $entry ) ) {
                $label = $this->normalize_plain_text( $entry );

                if ( '' !== $label ) {
                    $items[] = $label;
                }
            }
        }

        return $items;
    }

    private function prepare_class_entity( array $entity ) {
        $entity['classstartdate'] = $this->format_date_for_response( isset( $entity['classstartdate'] ) ? $entity['classstartdate'] : '' );
        $entity['classstarttime'] = $this->format_time_for_response( isset( $entity['classstarttime'] ) ? $entity['classstarttime'] : '' );
        $entity['classendtime']   = $this->format_time_for_response( isset( $entity['classendtime'] ) ? $entity['classendtime'] : '' );
        $entity['classcost']      = $this->format_decimal_for_response( isset( $entity['classcost'] ) ? $entity['classcost'] : '' );
        $entity['classhide']      = isset( $entity['classhide'] ) ? (string) ( (int) $entity['classhide'] ) : '0';

        $address = $this->decode_class_address_field( isset( $entity['classsaddress'] ) ? $entity['classsaddress'] : '' );
        $entity['class_address_street_1']    = $address['street_1'];
        $entity['class_address_street_2']    = $address['street_2'];
        $entity['class_address_city']        = $address['city'];
        $entity['class_address_state']       = $address['state'];
        $entity['class_address_postal_code'] = $address['postal_code'];

        $entity['coursestudentsallowed']   = $this->format_class_student_list_for_response( isset( $entity['coursestudentsallowed'] ) ? $entity['coursestudentsallowed'] : '' );
        $entity['quizstudentsallowed']     = $this->format_class_student_list_for_response( isset( $entity['quizstudentsallowed'] ) ? $entity['quizstudentsallowed'] : '' );
        $entity['coursestudentsrestricted'] = $this->format_class_student_list_for_response( isset( $entity['coursestudentsrestricted'] ) ? $entity['coursestudentsrestricted'] : '' );
        $entity['quizstudentsrestricted']   = $this->format_class_student_list_for_response( isset( $entity['quizstudentsrestricted'] ) ? $entity['quizstudentsrestricted'] : '' );
        $entity['instructors']             = $this->format_class_label_list_for_response( isset( $entity['instructors'] ) ? $entity['instructors'] : '' );

        $format_labels = array(
            'in_person' => __( 'In Person', 'teqcidb' ),
            'inperson'  => __( 'In Person', 'teqcidb' ),
            'virtual'   => __( 'Virtual', 'teqcidb' ),
            'hybrid'    => __( 'Hybrid', 'teqcidb' ),
        );

        $type_labels = array(
            'initial'   => __( 'Initial', 'teqcidb' ),
            'refresher' => __( 'Refresher', 'teqcidb' ),
            'other'     => __( 'Other', 'teqcidb' ),
        );

        $format_value = isset( $entity['classformat'] ) ? $entity['classformat'] : '';
        $type_value   = isset( $entity['classtype'] ) ? $entity['classtype'] : '';

        $entity['placeholder_1'] = isset( $entity['classname'] ) ? $entity['classname'] : '';
        $entity['placeholder_2'] = isset( $format_labels[ $format_value ] ) ? $format_labels[ $format_value ] : $format_value;
        $entity['placeholder_3'] = isset( $type_labels[ $type_value ] ) ? $type_labels[ $type_value ] : $type_value;
        $entity['placeholder_4'] = $entity['classstartdate'];
        $entity['placeholder_5'] = $entity['classcost'];
        $entity['name']          = $entity['placeholder_1'];

        return $entity;
    }

    private function decode_class_address_field( $value ) {
        $defaults = array(
            'street_1'    => '',
            'street_2'    => '',
            'city'        => '',
            'state'       => '',
            'postal_code' => '',
        );

        if ( empty( $value ) ) {
            return $defaults;
        }

        $decoded = json_decode( $value, true );

        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        if ( isset( $decoded['zip_code'] ) ) {
            $decoded['postal_code'] = $decoded['zip_code'];
        }

        return array_merge( $defaults, $decoded );
    }

    private function prepare_student_entity( array $entity ) {
        $entity['initial_training_date'] = $this->format_date_for_response( isset( $entity['initial_training_date'] ) ? $entity['initial_training_date'] : '' );
        $entity['last_refresher_date']   = $this->format_date_for_response( isset( $entity['last_refresher_date'] ) ? $entity['last_refresher_date'] : '' );
        $entity['expiration_date']       = $this->format_date_for_response( isset( $entity['expiration_date'] ) ? $entity['expiration_date'] : '' );
        $entity['old_companies']         = $this->format_json_field( isset( $entity['old_companies'] ) ? $entity['old_companies'] : '' );
        $entity['associations']          = $this->format_json_field( isset( $entity['associations'] ) ? $entity['associations'] : '' );

        foreach ( array( 'phone_cell', 'phone_office', 'fax' ) as $phone_field ) {
            $entity[ $phone_field ] = $this->format_phone_for_response( isset( $entity[ $phone_field ] ) ? $entity[ $phone_field ] : '' );
        }

        $address = $this->decode_student_address_field( isset( $entity['student_address'] ) ? $entity['student_address'] : '' );
        $entity['student_address_street_1']   = $address['street_1'];
        $entity['student_address_street_2']   = $address['street_2'];
        $entity['student_address_city']       = $address['city'];
        $entity['student_address_state']      = $address['state'];
        $entity['student_address_postal_code'] = $address['postal_code'];

        $representative = $this->decode_representative_contact_field( isset( $entity['their_representative'] ) ? $entity['their_representative'] : '' );
        $entity['representative_first_name'] = $representative['first_name'];
        $entity['representative_last_name']  = $representative['last_name'];
        $entity['representative_email']      = $representative['email'];
        $entity['representative_phone']      = $representative['phone'];

        $entity['is_a_representative'] = isset( $entity['is_a_representative'] ) ? (string) ( (int) $entity['is_a_representative'] ) : '0';

        $entity['placeholder_1'] = $this->build_student_display_name( $entity );
        $entity['placeholder_2'] = isset( $entity['email'] ) ? $entity['email'] : '';
        $entity['placeholder_3'] = isset( $entity['company'] ) ? $entity['company'] : '';
        $entity['placeholder_4'] = isset( $entity['phone_cell'] ) ? $entity['phone_cell'] : '';
        $entity['placeholder_5'] = $entity['expiration_date'];
        $entity['name']          = $entity['placeholder_1'];

        return $entity;
    }

    private function get_student_history_entries( array $unique_ids ) {
        $unique_ids = array_values(
            array_filter(
                $unique_ids,
                static function( $value ) {
                    return is_string( $value ) && '' !== $value;
                }
            )
        );

        if ( empty( $unique_ids ) ) {
            return array();
        }

        global $wpdb;
        $table        = $wpdb->prefix . 'teqcidb_studenthistory';
        $placeholders = implode( ', ', array_fill( 0, count( $unique_ids ), '%s' ) );
        $query        = "SELECT * FROM $table WHERE uniquestudentid IN ($placeholders) ORDER BY enrollmentdate DESC, id DESC";
        $results      = $wpdb->get_results( $wpdb->prepare( $query, $unique_ids ), ARRAY_A );
        $grouped      = array();
        $class_map    = $this->get_student_history_class_map( $results );

        if ( is_array( $results ) ) {
            foreach ( $results as $row ) {
                if ( ! is_array( $row ) || empty( $row['uniquestudentid'] ) ) {
                    continue;
                }

                $unique_id = sanitize_text_field( (string) $row['uniquestudentid'] );

                if ( '' === $unique_id ) {
                    continue;
                }

                if ( ! isset( $grouped[ $unique_id ] ) ) {
                    $grouped[ $unique_id ] = array();
                }

                $unique_class_id = isset( $row['uniqueclassid'] ) && is_scalar( $row['uniqueclassid'] ) ? (string) $row['uniqueclassid'] : '';

                if ( $unique_class_id && isset( $class_map[ $unique_class_id ] ) ) {
                    $class_data = $class_map[ $unique_class_id ];
                    $row['classname']  = isset( $class_data['classname'] ) ? $class_data['classname'] : $row['classname'];
                    $row['classdate']  = isset( $class_data['classdate'] ) ? $class_data['classdate'] : '';
                    $row['classtype']  = isset( $class_data['classtype'] ) ? $class_data['classtype'] : '';
                } else {
                    $row['classdate'] = '';
                    $row['classtype'] = '';
                }

                $grouped[ $unique_id ][] = $row;
            }
        }

        return $grouped;
    }

    private function get_student_history_class_map( array $history_rows ) {
        $unique_class_ids = array();

        foreach ( $history_rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            if ( isset( $row['uniqueclassid'] ) && is_scalar( $row['uniqueclassid'] ) ) {
                $unique_id = sanitize_text_field( (string) $row['uniqueclassid'] );

                if ( '' !== $unique_id ) {
                    $unique_class_ids[] = $unique_id;
                }
            }
        }

        $unique_class_ids = array_values( array_unique( $unique_class_ids ) );

        if ( empty( $unique_class_ids ) ) {
            return array();
        }

        global $wpdb;
        $table        = $wpdb->prefix . 'teqcidb_classes';
        $placeholders = implode( ', ', array_fill( 0, count( $unique_class_ids ), '%s' ) );
        $query        = "SELECT uniqueclassid, classname, classstartdate, classtype FROM $table WHERE uniqueclassid IN ($placeholders)";
        $results      = $wpdb->get_results( $wpdb->prepare( $query, $unique_class_ids ), ARRAY_A );
        $map          = array();

        if ( is_array( $results ) ) {
            foreach ( $results as $row ) {
                if ( ! is_array( $row ) || empty( $row['uniqueclassid'] ) ) {
                    continue;
                }

                $unique_id = sanitize_text_field( (string) $row['uniqueclassid'] );

                if ( '' === $unique_id ) {
                    continue;
                }

                $map[ $unique_id ] = array(
                    'classname' => isset( $row['classname'] ) ? sanitize_text_field( (string) $row['classname'] ) : '',
                    'classdate' => $this->format_date_for_response( isset( $row['classstartdate'] ) ? $row['classstartdate'] : '' ),
                    'classtype' => isset( $row['classtype'] ) ? sanitize_text_field( (string) $row['classtype'] ) : '',
                );
            }
        }

        return $map;
    }

    private function decode_student_address_field( $value ) {
        $defaults = array(
            'street_1'    => '',
            'street_2'    => '',
            'city'        => '',
            'state'       => '',
            'postal_code' => '',
        );

        if ( empty( $value ) ) {
            return $defaults;
        }

        $decoded = json_decode( $value, true );

        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        if ( isset( $decoded['zip_code'] ) ) {
            $decoded['postal_code'] = $decoded['zip_code'];
        }

        foreach ( $defaults as $key => $default_value ) {
            if ( isset( $decoded[ $key ] ) && is_scalar( $decoded[ $key ] ) ) {
                $defaults[ $key ] = sanitize_text_field( (string) $decoded[ $key ] );
            }
        }

        return $defaults;
    }

    private function decode_representative_contact_field( $value ) {
        $defaults = array(
            'first_name' => '',
            'last_name'  => '',
            'email'      => '',
            'phone'      => '',
            'wpuserid'   => '',
            'uniquestudentid' => '',
        );

        if ( empty( $value ) ) {
            return $defaults;
        }

        $decoded = json_decode( $value, true );

        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        foreach ( array( 'first_name', 'last_name' ) as $key ) {
            if ( isset( $decoded[ $key ] ) && is_scalar( $decoded[ $key ] ) ) {
                $defaults[ $key ] = sanitize_text_field( (string) $decoded[ $key ] );
            }
        }

        if ( isset( $decoded['phone'] ) ) {
            $defaults['phone'] = $this->format_phone_for_response( $decoded['phone'] );
        }

        if ( isset( $decoded['email'] ) ) {
            $email = sanitize_email( $decoded['email'] );
            $defaults['email'] = $email ? $email : '';
        }

        if ( isset( $decoded['wpuserid'] ) && is_numeric( $decoded['wpuserid'] ) ) {
            $defaults['wpuserid'] = (string) absint( $decoded['wpuserid'] );
        }

        if ( isset( $decoded['uniquestudentid'] ) && is_scalar( $decoded['uniquestudentid'] ) ) {
            $defaults['uniquestudentid'] = sanitize_text_field( (string) $decoded['uniquestudentid'] );
        }

        return $defaults;
    }

    private function format_phone_for_response( $value ) {
        $digits = $this->normalize_phone_digits( $value );

        if ( '' === $digits ) {
            return '';
        }

        return $this->format_digits_as_phone( $digits );
    }

    private function normalize_phone_digits( $value ) {
        if ( null === $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            $value = reset( $value );
        }

        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $digits = preg_replace( '/\D+/', '', (string) $value );

        if ( '' === $digits ) {
            return '';
        }

        if ( strlen( $digits ) > 10 && '1' === substr( $digits, 0, 1 ) ) {
            $digits = substr( $digits, 1 );
        }

        if ( strlen( $digits ) > 10 ) {
            $digits = substr( $digits, 0, 10 );
        }

        return $digits;
    }

    private function format_digits_as_phone( $digits ) {
        $digits = (string) $digits;

        if ( '' === $digits ) {
            return '';
        }

        if ( 10 !== strlen( $digits ) ) {
            return $digits;
        }

        return sprintf(
            '(%1$s) %2$s-%3$s',
            substr( $digits, 0, 3 ),
            substr( $digits, 3, 3 ),
            substr( $digits, 6, 4 )
        );
    }

    private function build_student_display_name( array $entity ) {
        $first = isset( $entity['first_name'] ) ? $entity['first_name'] : '';
        $last  = isset( $entity['last_name'] ) ? $entity['last_name'] : '';
        $name  = trim( $first . ' ' . $last );

        if ( '' !== $name ) {
            return $name;
        }

        return isset( $entity['email'] ) ? $entity['email'] : '';
    }

    private function get_attachment_url( $attachment_id ) {
        $attachment_id = absint( $attachment_id );

        if ( ! $attachment_id ) {
            return '';
        }

        $url = wp_get_attachment_url( $attachment_id );

        if ( ! $url ) {
            return '';
        }

        return esc_url_raw( $url );
    }


    private function get_class_page_route_segment( $unique_class_id ) {
        $segment = sanitize_title( (string) $unique_class_id );

        if ( '' === $segment ) {
            $segment = strtolower( wp_generate_password( 12, false, false ) );
        }

        return $segment;
    }

    private function generate_class_page_relative_url( $route_segment ) {
        $segment = sanitize_title( (string) $route_segment );

        if ( '' === $segment ) {
            return '/';
        }

        return '/' . self::CLASS_PAGE_PATH_PREFIX . '/' . $segment . '/';
    }

    private function generate_unique_class_id( $class_name ) {
        $normalized = strtolower( $class_name );
        $normalized = preg_replace( '/[^a-z0-9]/', '', $normalized );

        if ( '' === $normalized ) {
            $normalized = 'class';
        }

        return $normalized . time();
    }

    private function generate_unique_student_id( $email ) {
        return $email . time();
    }

    private function generate_user_login( $email ) {
        $base_login = sanitize_user( $email, true );

        if ( '' === $base_login ) {
            $base_login = 'teqcidb_user';
        }

        $candidate = $base_login;
        $suffix    = 1;

        while ( username_exists( $candidate ) ) {
            $candidate = $base_login . '_' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function get_us_states() {
        return array(
            __( 'Alabama', 'teqcidb' ),
            __( 'Alaska', 'teqcidb' ),
            __( 'Arizona', 'teqcidb' ),
            __( 'Arkansas', 'teqcidb' ),
            __( 'California', 'teqcidb' ),
            __( 'Colorado', 'teqcidb' ),
            __( 'Connecticut', 'teqcidb' ),
            __( 'Delaware', 'teqcidb' ),
            __( 'Florida', 'teqcidb' ),
            __( 'Georgia', 'teqcidb' ),
            __( 'Hawaii', 'teqcidb' ),
            __( 'Idaho', 'teqcidb' ),
            __( 'Illinois', 'teqcidb' ),
            __( 'Indiana', 'teqcidb' ),
            __( 'Iowa', 'teqcidb' ),
            __( 'Kansas', 'teqcidb' ),
            __( 'Kentucky', 'teqcidb' ),
            __( 'Louisiana', 'teqcidb' ),
            __( 'Maine', 'teqcidb' ),
            __( 'Maryland', 'teqcidb' ),
            __( 'Massachusetts', 'teqcidb' ),
            __( 'Michigan', 'teqcidb' ),
            __( 'Minnesota', 'teqcidb' ),
            __( 'Mississippi', 'teqcidb' ),
            __( 'Missouri', 'teqcidb' ),
            __( 'Montana', 'teqcidb' ),
            __( 'Nebraska', 'teqcidb' ),
            __( 'Nevada', 'teqcidb' ),
            __( 'New Hampshire', 'teqcidb' ),
            __( 'New Jersey', 'teqcidb' ),
            __( 'New Mexico', 'teqcidb' ),
            __( 'New York', 'teqcidb' ),
            __( 'North Carolina', 'teqcidb' ),
            __( 'North Dakota', 'teqcidb' ),
            __( 'Ohio', 'teqcidb' ),
            __( 'Oklahoma', 'teqcidb' ),
            __( 'Oregon', 'teqcidb' ),
            __( 'Pennsylvania', 'teqcidb' ),
            __( 'Rhode Island', 'teqcidb' ),
            __( 'South Carolina', 'teqcidb' ),
            __( 'South Dakota', 'teqcidb' ),
            __( 'Tennessee', 'teqcidb' ),
            __( 'Texas', 'teqcidb' ),
            __( 'Utah', 'teqcidb' ),
            __( 'Vermont', 'teqcidb' ),
            __( 'Virginia', 'teqcidb' ),
            __( 'Washington', 'teqcidb' ),
            __( 'West Virginia', 'teqcidb' ),
            __( 'Wisconsin', 'teqcidb' ),
            __( 'Wyoming', 'teqcidb' ),
        );
    }

    private function get_us_states_and_territories() {
        return array_merge(
            $this->get_us_states(),
            array(
                __( 'District of Columbia', 'teqcidb' ),
                __( 'American Samoa', 'teqcidb' ),
                __( 'Guam', 'teqcidb' ),
                __( 'Northern Mariana Islands', 'teqcidb' ),
                __( 'Puerto Rico', 'teqcidb' ),
                __( 'U.S. Virgin Islands', 'teqcidb' ),
            )
        );
    }
}
