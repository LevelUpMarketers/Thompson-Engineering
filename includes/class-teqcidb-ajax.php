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
        add_action( 'wp_ajax_teqcidb_delete_quiz_question', array( $this, 'delete_quiz_question' ) );
        add_action( 'wp_ajax_teqcidb_create_quiz_question', array( $this, 'create_quiz_question' ) );
        add_action( 'wp_ajax_teqcidb_reset_failed_quiz_attempt', array( $this, 'reset_failed_quiz_attempt' ) );
        add_action( 'wp_ajax_teqcidb_save_quiz_progress', array( $this, 'save_quiz_progress' ) );
        add_action( 'wp_ajax_teqcidb_submit_quiz_attempt', array( $this, 'submit_quiz_attempt' ) );
        add_action( 'wp_ajax_teqcidb_save_studenthistory', array( $this, 'save_studenthistory' ) );
        add_action( 'wp_ajax_teqcidb_create_studenthistory', array( $this, 'create_studenthistory' ) );
        add_action( 'wp_ajax_teqcidb_delete_student', array( $this, 'delete_student' ) );
        add_action( 'wp_ajax_teqcidb_delete_studenthistory', array( $this, 'delete_studenthistory' ) );
        add_action( 'wp_ajax_teqcidb_read_student', array( $this, 'read_student' ) );
        add_action( 'wp_ajax_teqcidb_read_class', array( $this, 'read_class' ) );
        add_action( 'wp_ajax_teqcidb_read_class_registered_students', array( $this, 'read_class_registered_students' ) );
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
                "SELECT id, classname, uniqueclassid, classtype, allallowedquiz, quizstudentsallowed, classresources FROM $table WHERE classurl = %s LIMIT 1",
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
        $class_page_script     = TEQCIDB_PLUGIN_URL . 'assets/js/shortcodes/class-page.js';
        $class_type            = isset( $class_row['classtype'] ) ? sanitize_key( (string) $class_row['classtype'] ) : '';
        $quiz_access_mode      = isset( $class_row['allallowedquiz'] ) ? sanitize_key( (string) $class_row['allallowedquiz'] ) : '';
        $quiz_access_allowed   = true;

        echo '<!doctype html><html><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" /><title>' . esc_html__( 'Class Page', 'teqcidb' ) . '</title><link rel="stylesheet" href="' . esc_url( $class_page_stylesheet ) . '" /></head><body class="teqcidb-class-route">';
        echo '<main class="teqcidb-class-route__main">';
        echo '<header class="teqcidb-class-route__header">';

        if ( '' !== $class_name ) {
            /* translators: %s: class name. */
            echo '<h1 class="teqcidb-class-route__title">' . esc_html( sprintf( __( '%s Class Page', 'teqcidb' ), $class_name ) ) . '</h1>';
        } else {
            echo '<h1 class="teqcidb-class-route__title">' . esc_html__( 'Class Page', 'teqcidb' ) . '</h1>';
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
        $quiz_runtime     = array();
        $current_user     = wp_get_current_user();
        $students_table   = $wpdb->prefix . 'teqcidb_students';
        $student_name     = '';

        $student_name_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT first_name, last_name FROM $students_table WHERE wpuserid = %d ORDER BY id DESC LIMIT 1",
                $current_user_id
            ),
            ARRAY_A
        );

        if ( is_array( $student_name_row ) ) {
            $student_first_name = isset( $student_name_row['first_name'] ) ? sanitize_text_field( (string) $student_name_row['first_name'] ) : '';
            $student_last_name  = isset( $student_name_row['last_name'] ) ? sanitize_text_field( (string) $student_name_row['last_name'] ) : '';
            $student_name       = trim( $student_first_name . ' ' . $student_last_name );
        }

        if ( '' === $student_name ) {
            $student_name = trim( (string) $current_user->first_name . ' ' . (string) $current_user->last_name );
        }

        if ( '' === $student_name ) {
            $student_name = (string) $current_user->display_name;
        }

        if ( '' === $student_name ) {
            $student_name = __( 'Student', 'teqcidb' );
        }

        if ( 'blocked' === $quiz_access_mode ) {
            $quiz_access_allowed = false;

            $quiz_students_allowed = $this->format_class_student_list_for_response( isset( $class_row['quizstudentsallowed'] ) ? $class_row['quizstudentsallowed'] : '' );

            if ( ! empty( $quiz_students_allowed ) ) {
                foreach ( $quiz_students_allowed as $allowed_student ) {
                    $allowed_wp_user_id = isset( $allowed_student['wpuserid'] ) ? absint( $allowed_student['wpuserid'] ) : 0;

                    if ( $allowed_wp_user_id > 0 && $allowed_wp_user_id === $current_user_id ) {
                        $quiz_access_allowed = true;
                        break;
                    }
                }
            }
        }

        if ( $class_id > 0 && $current_user_id > 0 ) {
            $quizzes_table      = $wpdb->prefix . 'teqcidb_quizzes';
            $quiz_classes_table = $wpdb->prefix . 'teqcidb_quiz_classes';
            $attempts_table     = $wpdb->prefix . 'teqcidb_quiz_attempts';
            $students_table     = $wpdb->prefix . 'teqcidb_students';

            $quiz_id = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT q.id FROM $quizzes_table q INNER JOIN $quiz_classes_table qc ON qc.quiz_id = q.id WHERE qc.class_id = %d ORDER BY q.updated_at DESC, q.id DESC LIMIT 1",
                    $class_id
                )
            );

            if ( $quiz_id <= 0 ) {
                $quiz_id = (int) $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM $quizzes_table WHERE FIND_IN_SET( CAST( %d AS CHAR ), REPLACE( class_id, ' ', '' ) ) > 0 ORDER BY updated_at DESC, id DESC LIMIT 1",
                        $class_id
                    )
                );
            }

            if ( $quiz_id > 0 && $quiz_access_allowed ) {
                $quiz_runtime = $this->build_class_quiz_runtime_payload( $quiz_id, $class_id, $current_user_id );

                $attempt = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT status, updated_at FROM $attempts_table WHERE quiz_id = %d AND class_id = %d AND user_id = %d ORDER BY id DESC LIMIT 1",
                        $quiz_id,
                        $class_id,
                        $current_user_id
                    ),
                    ARRAY_A
                );

                if ( is_array( $attempt ) && isset( $attempt['status'] ) ) {
                    $attempt_status = (int) $attempt['status'];

                    if ( 1 === $attempt_status ) {
                        if ( 'initial' === $class_type ) {
                            $register_url = home_url( '/register-for-a-class-qci/' );
                            $feedback_message = sprintf(
                                /* translators: 1: opening anchor tag to class registration page, 2: closing anchor tag, 3: opening tel link, 4: closing tel link, 5: opening email link, 6: closing email link. */
                                __( 'Whoops - it looks like you\'ve failed this class! Unfortunately, you\'ll need to register for another upcoming Initial Class. Visit the %1$sRegister For A Class%2$s page to register & pay for an upcoming class. For questions, please contact %3$sIlka Porter at (251) 666-2443%4$s or %5$sQCI@thompsonengineering.com%6$s for further instructions.', 'teqcidb' ),
                                '<a href="' . esc_url( $register_url ) . '">',
                                '</a>',
                                '<a href="tel:2516662443">',
                                '</a>',
                                '<a href="mailto:QCI@thompsonengineering.com">',
                                '</a>'
                            );
                        } else {
                            $feedback_message = __( 'Whoops - it looks like you\'ve failed this class! Please contact <a href="tel:2516662443">Ilka Porter at (251) 666-2443</a> or <a href="mailto:QCI@thompsonengineering.com">QCI@thompsonengineering.com</a> for further instructions.', 'teqcidb' );
                        }
                    } elseif ( 0 === $attempt_status ) {
                        $dashboard_url = $this->get_student_dashboard_certificates_url();

                        if ( 'initial' === $class_type ) {
                            $feedback_message = sprintf(
                                /* translators: 1: opening anchor tag to student dashboard certificates tab, 2: closing anchor tag. */
                                __( 'Congratulations! Looks like you\'ve passed this class! Please %1$svisit your QCI Dashboard%2$s for resources and information such as your QCI Certificate, Wallet Card, and important QCI expiration dates.', 'teqcidb' ),
                                '<a href="' . esc_url( $dashboard_url ) . '">',
                                '</a>'
                            );
                        } else {
                            $qci_number = (string) $wpdb->get_var(
                                $wpdb->prepare(
                                    "SELECT qcinumber FROM $students_table WHERE wpuserid = %d ORDER BY id DESC LIMIT 1",
                                    $current_user_id
                                )
                            );

                            $feedback_message = sprintf(
                                /* translators: 1: opening anchor tag to student dashboard certificates tab, 2: closing anchor tag, 3: student QCI number. */
                                __( 'Congratulations! Looks like you\'ve passed this class! Please %1$svisit your QCI Dashboard%2$s for resources and information such as your QCI Certificate, Wallet Card, and important QCI expiration dates. Your QCI Number is: <strong>%3$s</strong>.', 'teqcidb' ),
                                '<a href="' . esc_url( $dashboard_url ) . '">',
                                '</a>',
                                esc_html( '' !== $qci_number ? $qci_number : __( 'Not available', 'teqcidb' ) )
                            );
                        }
                    } elseif ( 2 === $attempt_status ) {
                        $feedback_message = __( 'Welcome back! Looks like you\'ve already started this quiz.', 'teqcidb' );
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
        /* translators: %s: student's first and last name. */
        echo '<h2 class="teqcidb-class-route__section-title">' . esc_html( sprintf( __( 'Welcome, %s!', 'teqcidb' ), $student_name ) ) . '</h2>';
        echo '<div class="teqcidb-class-route__feedback">' . wp_kses( $feedback_message, $allowed_feedback_html ) . '</div>';
        echo '</section>';

        $quiz_section_title = __( 'Class Quiz', 'teqcidb' );

        if ( 'initial' === $class_type ) {
            $quiz_section_title = __( 'QCI Exam', 'teqcidb' );
        } elseif ( 'refresher' === $class_type ) {
            $quiz_section_title = __( 'Refresher Quiz', 'teqcidb' );
        }

        echo '<section class="teqcidb-class-route__quiz">';

        if ( ! empty( $quiz_runtime ) ) {
            $has_refresher_slides = ( 'refresher' === $class_type && ! empty( $quiz_runtime['slides'] ) && is_array( $quiz_runtime['slides'] ) );
            $has_completed_refresher_slides = ( $has_refresher_slides && ! empty( $quiz_runtime['slideProgress']['completed'] ) );

            if ( $has_refresher_slides && ! $has_completed_refresher_slides ) {
                $quiz_section_title = __( 'Refresher Class Slides', 'teqcidb' );
            }

            echo '<h2 id="teqcidb-class-quiz-section-title" class="teqcidb-class-route__section-title">' . esc_html( $quiz_section_title ) . '</h2>';

            if ( 'initial' === $class_type ) {
                $quiz_intro = __( 'Below is your QCI Exam! A score of 75% or higher is passing. Anything below 75% will be considered failing. If you fail, you\'ll need to visit the <a href="/register-for-a-class-qci/">Register For A Class</a> page to register & pay for another upcoming Initial Class. For questions, please contact Ilka Porter at <a href="tel:2516662443">(251) 666-2443</a> or <a href="mailto:qci@thompsonengineering.com">qci@thompsonengineering.com</a>. Good luck!', 'teqcidb' );
            } elseif ( $has_refresher_slides && ! $has_completed_refresher_slides ) {
                $quiz_intro = __( 'Please review each refresher slide before starting your quiz. The quiz will unlock after you have worked through every slide.', 'teqcidb' );
            } elseif ( 'refresher' === $class_type ) {
                $quiz_intro = __( 'Below is your QCI Refresher Quiz! A score of 80% or higher is considered passing. Anything below an 80% will be considered failing. If you fail, you will need to contact Ilka Porter at <a href="tel:2516662443">(251) 666-2443</a> or <a href="mailto:qci@thompsonengineering.com">qci@thompsonengineering.com</a> to request another Refresher Quiz attempt. Only 1 additional attempt is granted! If you fail both Refresher Quiz attempts, you\'ll need to visit the <a href="/register-for-a-class-qci/">Register for a Class</a> page to register and pay for an upcoming Refresher Class. Good luck!', 'teqcidb' );
            } else {
                $quiz_intro = __( 'Answer each question and continue through the quiz. Your progress is auto-saved frequently.', 'teqcidb' );
            }

            echo '<p id="teqcidb-class-quiz-section-description" class="teqcidb-class-route__section-description">' . wp_kses( $quiz_intro, $allowed_feedback_html ) . '</p>';
            echo '<div id="teqcidb-class-quiz-app" class="teqcidb-class-quiz-app" data-quiz-runtime="' . esc_attr( wp_json_encode( $quiz_runtime ) ) . '"></div>';
            echo '<script src="' . esc_url( $class_page_script ) . '" defer></script>';
        } else {
            echo '<h2 id="teqcidb-class-quiz-section-title" class="teqcidb-class-route__section-title">' . esc_html( $quiz_section_title ) . '</h2>';

            if ( ! $quiz_access_allowed ) {
                if ( 'initial' === $class_type ) {
                    echo '<p class="teqcidb-class-route__section-description">' . esc_html__( 'Your instructor has not enabled this Exam yet!', 'teqcidb' ) . '</p>';
                } elseif ( 'refresher' === $class_type ) {
                    echo '<p class="teqcidb-class-route__section-description">' . esc_html__( 'Your instructor has not enabled this Refresher Quiz yet!', 'teqcidb' ) . '</p>';
                } else {
                    echo '<p class="teqcidb-class-route__section-description">' . esc_html__( 'Your instructor has not enabled this quiz yet!', 'teqcidb' ) . '</p>';
                }
            } else {
                echo '<p class="teqcidb-class-route__section-description">' . esc_html__( 'No quiz is assigned to this class yet.', 'teqcidb' ) . '</p>';
            }
        }

        echo '</section>';

        $class_resources = $this->parse_class_resources_for_route( isset( $class_row['classresources'] ) ? $class_row['classresources'] : '' );
        $resource_rows   = array();

        foreach ( $class_resources as $resource ) {
            $resource_name = isset( $resource['name'] ) ? sanitize_text_field( (string) $resource['name'] ) : '';
            $resource_type = isset( $resource['type'] ) ? sanitize_key( (string) $resource['type'] ) : '';
            $resource_url  = isset( $resource['url'] ) ? esc_url( (string) $resource['url'] ) : '';

            if ( '' === $resource_name && '' === $resource_url ) {
                continue;
            }

            $resource_rows[] = array(
                'name' => $resource_name,
                'type' => $resource_type,
                'url'  => $resource_url,
            );
        }

        echo '<section class="teqcidb-class-route__resources">';
        echo '<h2 class="teqcidb-class-route__section-title">' . esc_html__( 'Class Resources', 'teqcidb' ) . '</h2>';

        if ( empty( $resource_rows ) ) {
            echo '<ul class="teqcidb-class-route__resource-list"><li class="teqcidb-class-route__resource-item">' . esc_html__( 'No resources have been added for this class yet!', 'teqcidb' ) . '</li></ul>';
        } else {
            echo '<ul class="teqcidb-class-route__resource-list">';

            foreach ( $resource_rows as $resource ) {
                $resource_name = $resource['name'];
                $resource_type = $resource['type'];
                $resource_url  = $resource['url'];

                $resource_type_label = $this->get_class_resource_type_label( $resource_type );
                $resource_title       = '' !== $resource_type_label ? sprintf( '%1$s (%2$s)', $resource_name, $resource_type_label ) : $resource_name;

                echo '<li class="teqcidb-class-route__resource-item">';
                echo '<strong class="teqcidb-class-route__resource-title">' . esc_html( $resource_title ) . '</strong>';

                if ( '' !== $resource_url ) {
                    echo '<p class="teqcidb-class-route__resource-link-wrap"><a href="' . esc_url( $resource_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $resource_url ) . '</a></p>';
                }

                echo '</li>';
            }

            echo '</ul>';
        }

        echo '</section>';
        echo '</main></body></html>';
        exit;
    }

    private function parse_class_resources_for_route( $class_resources_value ) {
        if ( empty( $class_resources_value ) ) {
            return array();
        }

        if ( is_string( $class_resources_value ) ) {
            $decoded = json_decode( $class_resources_value, true );

            if ( is_array( $decoded ) ) {
                $class_resources_value = $decoded;
            }
        }

        if ( ! is_array( $class_resources_value ) ) {
            return array();
        }

        return array_values( $class_resources_value );
    }

    private function get_class_resource_type_label( $resource_type ) {
        $resource_type = sanitize_key( (string) $resource_type );

        if ( 'pdf' === $resource_type ) {
            return __( 'PDF', 'teqcidb' );
        }

        if ( 'video' === $resource_type ) {
            return __( 'Video', 'teqcidb' );
        }

        if ( 'external_link' === $resource_type ) {
            return __( 'External Link', 'teqcidb' );
        }

        return '' !== $resource_type ? strtoupper( $resource_type ) : '';
    }


    private function build_class_quiz_runtime_payload( $quiz_id, $class_id, $user_id ) {
        global $wpdb;

        $quiz_id = absint( $quiz_id );
        $class_id = absint( $class_id );
        $user_id = absint( $user_id );

        if ( $quiz_id <= 0 || $class_id <= 0 || $user_id <= 0 ) {
            return array();
        }

        $quizzes_table   = $wpdb->prefix . 'teqcidb_quizzes';
        $classes_table   = $wpdb->prefix . 'teqcidb_classes';
        $questions_table = $wpdb->prefix . 'teqcidb_quiz_questions';
        $slides_table    = $wpdb->prefix . 'teqcidb_quiz_slides';
        $attempts_table  = $wpdb->prefix . 'teqcidb_quiz_attempts';
        $answers_table      = $wpdb->prefix . 'teqcidb_quiz_answers';
        $answer_items_table = $wpdb->prefix . 'teqcidb_quiz_answer_items';

        $quiz_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, name FROM $quizzes_table WHERE id = %d LIMIT 1",
                $quiz_id
            ),
            ARRAY_A
        );

        if ( empty( $quiz_row ) ) {
            return array();
        }

        $class_type = (string) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT classtype FROM $classes_table WHERE id = %d LIMIT 1",
                $class_id
            )
        );

        $pass_threshold = ( 'refresher' === strtolower( sanitize_key( $class_type ) ) ) ? 80 : 75;

        $question_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, type, prompt, choices_json FROM $questions_table WHERE quiz_id = %d ORDER BY sort_order ASC, id ASC",
                $quiz_id
            ),
            ARRAY_A
        );

        $slide_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, attachment_id, slide_order FROM $slides_table WHERE quiz_id = %d AND is_active = 1 ORDER BY slide_order ASC, id ASC",
                $quiz_id
            ),
            ARRAY_A
        );

        $slides = array();

        if ( is_array( $slide_rows ) && ! empty( $slide_rows ) ) {
            foreach ( $slide_rows as $slide_row ) {
                $attachment_id = isset( $slide_row['attachment_id'] ) ? absint( $slide_row['attachment_id'] ) : 0;

                if ( $attachment_id <= 0 ) {
                    continue;
                }

                $slide_url = wp_get_attachment_image_url( $attachment_id, 'full' );

                if ( ! is_string( $slide_url ) || '' === $slide_url ) {
                    continue;
                }

                $slides[] = array(
                    'id'         => isset( $slide_row['id'] ) ? absint( $slide_row['id'] ) : 0,
                    'order'      => isset( $slide_row['slide_order'] ) ? absint( $slide_row['slide_order'] ) : 0,
                    'url'        => esc_url_raw( $slide_url ),
                    'alt'        => trim( wp_strip_all_tags( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
                    'attachmentId' => $attachment_id,
                );
            }
        }

        if ( ! is_array( $question_rows ) || empty( $question_rows ) ) {
            return array();
        }

        $questions = array();

        foreach ( $question_rows as $row ) {
            $question_id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
            $type        = isset( $row['type'] ) ? sanitize_key( (string) $row['type'] ) : '';
            $prompt      = isset( $row['prompt'] ) ? wp_kses_post( (string) $row['prompt'] ) : '';

            if ( $question_id <= 0 || '' === $type || '' === trim( wp_strip_all_tags( $prompt ) ) ) {
                continue;
            }

            $questions[] = array(
                'id'      => $question_id,
                'type'    => $type,
                'prompt'  => $prompt,
                'choices' => $this->normalize_question_choices_for_runtime( $type, isset( $row['choices_json'] ) ? (string) $row['choices_json'] : '' ),
            );
        }

        if ( empty( $questions ) ) {
            return array();
        }

        $attempt_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, status, score, current_index, submitted_at, updated_at FROM $attempts_table WHERE quiz_id = %d AND class_id = %d AND user_id = %d ORDER BY id DESC LIMIT 1",
                $quiz_id,
                $class_id,
                $user_id
            ),
            ARRAY_A
        );

        $attempt_id      = isset( $attempt_row['id'] ) ? absint( $attempt_row['id'] ) : 0;
        $attempt_status  = isset( $attempt_row['status'] ) ? (int) $attempt_row['status'] : 2;
        $saved_answers   = array();
        $saved_index     = 0;
        $incorrect_details = array();

        if ( $attempt_id > 0 && 2 === $attempt_status ) {
            $saved_index = isset( $attempt_row['current_index'] ) ? max( 0, absint( $attempt_row['current_index'] ) ) : 0;

            $answer_item_rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT question_id, selected_json FROM $answer_items_table WHERE attempt_id = %d",
                    $attempt_id
                ),
                ARRAY_A
            );

            if ( is_array( $answer_item_rows ) && ! empty( $answer_item_rows ) ) {
                foreach ( $answer_item_rows as $answer_item_row ) {
                    $question_id = isset( $answer_item_row['question_id'] ) ? absint( $answer_item_row['question_id'] ) : 0;

                    if ( $question_id <= 0 ) {
                        continue;
                    }

                    $selected_values = json_decode( isset( $answer_item_row['selected_json'] ) ? (string) $answer_item_row['selected_json'] : '', true );

                    $saved_answers[ (string) $question_id ] = $this->sanitize_runtime_selected_values( array( 'selected' => $selected_values ) );
                }
            }

            if ( empty( $saved_answers ) ) {
                $answers_json = (string) $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT answers_json FROM $answers_table WHERE attempt_id = %d LIMIT 1",
                        $attempt_id
                    )
                );

                if ( '' !== $answers_json ) {
                    $decoded = json_decode( $answers_json, true );

                    if ( is_array( $decoded ) && isset( $decoded['answers'] ) && is_array( $decoded['answers'] ) ) {
                        foreach ( $decoded['answers'] as $question_key => $answer_data ) {
                            $saved_answers[ (string) absint( $question_key ) ] = $this->sanitize_runtime_selected_values( $answer_data );
                        }

                        if ( isset( $decoded['current_index'] ) && $saved_index <= 0 ) {
                            $saved_index = max( 0, absint( $decoded['current_index'] ) );
                        }
                    }
                }
            }
        }


        if ( $attempt_id > 0 ) {
            $answers_json = (string) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT answers_json FROM $answers_table WHERE attempt_id = %d LIMIT 1",
                    $attempt_id
                )
            );

            if ( '' !== $answers_json ) {
                $decoded_answers_payload = json_decode( $answers_json, true );

                if ( is_array( $decoded_answers_payload ) && isset( $decoded_answers_payload['incorrect_details'] ) && is_array( $decoded_answers_payload['incorrect_details'] ) ) {
                    $incorrect_details = array_values(
                        array_filter(
                            $decoded_answers_payload['incorrect_details'],
                            static function( $detail ) {
                                return is_array( $detail );
                            }
                        )
                    );
                }
            }
        }

        $slide_progress = array(
            'currentIndex' => 0,
            'maxViewed'    => 0,
            'completed'    => false,
            'updatedAt'    => '',
        );

        if ( 'refresher' === strtolower( sanitize_key( $class_type ) ) && ! empty( $slides ) ) {
            $slide_progress = $this->get_refresher_slide_progress( $quiz_id, $class_id, $user_id );
        }

        return array(
            'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( 'teqcidb_ajax_nonce' ),
            'restUrl'      => rest_url( 'teqcidb/v1' ),
            'restNonce'    => wp_create_nonce( 'wp_rest' ),
            'useRestQuizApi'=> true,
            'i18n'    => array(
                'validationAnswerRequired' => __( 'Please select an answer before continuing.', 'teqcidb' ),
                'saveError'                => __( 'We could not save your latest answer. Please check your connection and try again.', 'teqcidb' ),
                'submitError'              => __( 'We could not submit your quiz. Please try again.', 'teqcidb' ),
                'resumeNotice'             => __( 'We restored your previous progress from your latest save.', 'teqcidb' ),
                'saving'                   => __( 'Saving…', 'teqcidb' ),
                'saved'                    => __( 'Progress saved.', 'teqcidb' ),
                'submitting'               => __( 'Submitting quiz…', 'teqcidb' ),
                'passed'                   => __( 'Passed', 'teqcidb' ),
                'failed'                   => __( 'Failed', 'teqcidb' ),
                'questionOf'               => __( 'Question %1$s of %2$s', 'teqcidb' ),
                'completedRemaining'       => __( '%1$s completed / %2$s remaining', 'teqcidb' ),
                'nextQuestion'             => __( 'Next Question', 'teqcidb' ),
                'submitQuiz'               => __( 'Submit Quiz', 'teqcidb' ),
                'scoreSummary'             => __( 'Score: %1$s% (Required: %2$s%)', 'teqcidb' ),
                'questionsToReview'        => __( 'Questions to Review', 'teqcidb' ),
                'yourAnswer'               => __( 'Your answer:', 'teqcidb' ),
                'correctAnswer'            => __( 'Correct answer:', 'teqcidb' ),
                'noAnswer'                 => __( 'No answer', 'teqcidb' ),
                'optionLabel'              => __( 'Option %s', 'teqcidb' ),
                'slideOf'                  => __( 'Slide %1$s of %2$s', 'teqcidb' ),
                'slidesCompletedRemaining' => __( '%1$s completed / %2$s remaining', 'teqcidb' ),
                'nextSlide'                => __( 'Next Slide', 'teqcidb' ),
                'previousSlide'            => __( 'Previous Slide', 'teqcidb' ),
                'startQuiz'                => __( 'Start Quiz', 'teqcidb' ),
                'slideWaitTooltip'        => __( 'Please study the slide and wait to proceed.', 'teqcidb' ),
                'slideProgressSaved'       => __( 'Slide progress saved.', 'teqcidb' ),
                'slideProgressSaveError'   => __( 'We could not save your slide progress. Please check your connection and try again.', 'teqcidb' ),
                'slideProgressRestored'    => __( 'We restored your slide progress from your last save.', 'teqcidb' ),
                'refresherSlidesSectionTitle'=> __( 'Refresher Class Slides', 'teqcidb' ),
                'refresherQuizSectionTitle'  => __( 'Refresher Quiz', 'teqcidb' ),
                'refresherSlidesIntro'       => __( 'Please review each refresher slide before starting your quiz. The quiz will unlock after you have worked through every slide.', 'teqcidb' ),
                'refresherQuizIntro'         => __( 'Below is your QCI Refresher Quiz! A score of 80% or higher is considered passing. Anything below an 80% will be considered failing. If you fail, you will need to contact Ilka Porter at <a href="tel:2516662443">(251) 666-2443</a> or <a href="mailto:qci@thompsonengineering.com">qci@thompsonengineering.com</a> to request another Refresher Quiz attempt. Only 1 additional attempt is granted! If you fail both Refresher Quiz attempts, you\'ll need to visit the <a href="/register-for-a-class-qci/">Register for a Class</a> page to register and pay for an upcoming Refresher Class. Good luck!', 'teqcidb' ),
                'initialPassedMessageBeforeLink' => __( 'Congratulations! Looks like you\'ve passed this class! Please ', 'teqcidb' ),
                'initialPassedMessageLinkText'   => __( 'visit your QCI Dashboard', 'teqcidb' ),
                'initialPassedMessageAfterLink'  => __( ' for resources and information such as your QCI Certificate, Wallet Card, and important QCI expiration dates.', 'teqcidb' ),
            ),
            'quiz'    => array(
                'id'             => $quiz_id,
                'name'           => isset( $quiz_row['name'] ) ? sanitize_text_field( (string) $quiz_row['name'] ) : '',
                'classId'        => $class_id,
                'classType'      => sanitize_key( $class_type ),
                'passThreshold'  => $pass_threshold,
                'totalQuestions' => count( $questions ),
            ),
            'attempt' => array(
                'id'           => $attempt_id,
                'status'       => $attempt_status,
                'score'        => isset( $attempt_row['score'] ) ? (int) $attempt_row['score'] : null,
                'submittedAt'  => isset( $attempt_row['submitted_at'] ) ? (string) $attempt_row['submitted_at'] : '',
                'currentIndex' => $saved_index,
                'answers'      => $saved_answers,
                'incorrectDetails' => $incorrect_details,
            ),
            'questions'     => $questions,
            'slides'        => $slides,
            'slideProgress' => $slide_progress,
            'dashboardCertificatesUrl' => $this->get_student_dashboard_certificates_url(),
        );
    }


    public function get_refresher_slide_progress( $quiz_id, $class_id, $user_id ) {
        global $wpdb;

        $quiz_id  = absint( $quiz_id );
        $class_id = absint( $class_id );
        $user_id  = absint( $user_id );

        $default_progress = array(
            'currentIndex' => 0,
            'maxViewed'    => 0,
            'completed'    => false,
            'updatedAt'    => '',
        );

        if ( $quiz_id <= 0 || $class_id <= 0 || $user_id <= 0 ) {
            return $default_progress;
        }

        $table_name = $wpdb->prefix . 'teqcidb_slide_progress';
        $row        = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT current_slide_index, max_slide_index_viewed, completed, updated_at FROM $table_name WHERE quiz_id = %d AND class_id = %d AND user_id = %d LIMIT 1",
                $quiz_id,
                $class_id,
                $user_id
            ),
            ARRAY_A
        );

        if ( ! is_array( $row ) ) {
            return $default_progress;
        }

        return array(
            'currentIndex' => isset( $row['current_slide_index'] ) ? absint( $row['current_slide_index'] ) : 0,
            'maxViewed'    => isset( $row['max_slide_index_viewed'] ) ? absint( $row['max_slide_index_viewed'] ) : 0,
            'completed'    => ! empty( $row['completed'] ),
            'updatedAt'    => isset( $row['updated_at'] ) ? (string) $row['updated_at'] : '',
        );
    }

    public function save_refresher_slide_progress( $quiz_id, $class_id, $user_id, $current_slide_index, $max_slide_index_viewed, $slides_total, $completed ) {
        global $wpdb;

        $quiz_id                = absint( $quiz_id );
        $class_id               = absint( $class_id );
        $user_id                = absint( $user_id );
        $current_slide_index    = absint( $current_slide_index );
        $max_slide_index_viewed = absint( $max_slide_index_viewed );
        $slides_total           = absint( $slides_total );
        $completed              = ! empty( $completed ) ? 1 : 0;

        if ( $quiz_id <= 0 || $class_id <= 0 || $user_id <= 0 || $slides_total <= 0 ) {
            return new WP_Error( 'teqcidb_slide_progress_invalid', __( 'Unable to save slide progress because the payload was invalid.', 'teqcidb' ), array( 'status' => 400 ) );
        }

        if ( ! $this->is_quiz_assigned_to_class( $quiz_id, $class_id ) ) {
            return new WP_Error( 'teqcidb_slide_progress_unavailable', __( 'This slide deck is not available for the selected class.', 'teqcidb' ), array( 'status' => 403 ) );
        }

        if ( ! $this->user_can_access_class_quiz( $class_id, $user_id ) ) {
            return new WP_Error( 'teqcidb_slide_progress_forbidden', __( 'You do not have access to this slide deck.', 'teqcidb' ), array( 'status' => 403 ) );
        }

        $classes_table = $wpdb->prefix . 'teqcidb_classes';
        $class_type    = (string) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT classtype FROM $classes_table WHERE id = %d LIMIT 1",
                $class_id
            )
        );

        if ( 'refresher' !== strtolower( sanitize_key( $class_type ) ) ) {
            return new WP_Error( 'teqcidb_slide_progress_not_refresher', __( 'Slide progress tracking is only available for refresher classes.', 'teqcidb' ), array( 'status' => 400 ) );
        }

        $current_slide_index    = min( $current_slide_index, max( 0, $slides_total - 1 ) );
        $max_slide_index_viewed = min( $max_slide_index_viewed, max( 0, $slides_total - 1 ) );

        if ( $max_slide_index_viewed < $current_slide_index ) {
            $max_slide_index_viewed = $current_slide_index;
        }

        $table_name = $wpdb->prefix . 'teqcidb_slide_progress';
        $existing   = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, max_slide_index_viewed, current_slide_index, completed FROM $table_name WHERE quiz_id = %d AND class_id = %d AND user_id = %d LIMIT 1",
                $quiz_id,
                $class_id,
                $user_id
            ),
            ARRAY_A
        );

        $effective_max = $max_slide_index_viewed;

        if ( is_array( $existing ) ) {
            $existing_max = isset( $existing['max_slide_index_viewed'] ) ? absint( $existing['max_slide_index_viewed'] ) : 0;
            $effective_max = max( $existing_max, $max_slide_index_viewed );
            $completed = ( ! empty( $existing['completed'] ) || $completed ) ? 1 : 0;
        }

        if ( $effective_max >= ( $slides_total - 1 ) ) {
            $completed = 1;
        }

        $data = array(
            'quiz_id'                => $quiz_id,
            'class_id'               => $class_id,
            'user_id'                => $user_id,
            'attempt_token'          => '',
            'current_slide_index'    => $current_slide_index,
            'max_slide_index_viewed' => $effective_max,
            'slides_total'           => $slides_total,
            'completed'              => $completed,
            'updated_at'             => current_time( 'mysql' ),
        );

        $formats = array( '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d', '%s' );

        if ( is_array( $existing ) && isset( $existing['id'] ) ) {
            $updated = $wpdb->update(
                $table_name,
                $data,
                array( 'id' => absint( $existing['id'] ) ),
                $formats,
                array( '%d' )
            );

            if ( false === $updated ) {
                return new WP_Error( 'teqcidb_slide_progress_update_failed', __( 'Unable to update slide progress right now.', 'teqcidb' ), array( 'status' => 500 ) );
            }
        } else {
            $inserted = $wpdb->insert( $table_name, $data, $formats );

            if ( false === $inserted ) {
                return new WP_Error( 'teqcidb_slide_progress_insert_failed', __( 'Unable to save slide progress right now.', 'teqcidb' ), array( 'status' => 500 ) );
            }
        }

        return array(
            'currentIndex' => $current_slide_index,
            'maxViewed'    => $effective_max,
            'completed'    => (bool) $completed,
            'updatedAt'    => current_time( 'mysql' ),
        );
    }

    private function normalize_question_choices_for_runtime( $question_type, $choices_json ) {
        $question_type = sanitize_key( (string) $question_type );
        $decoded       = json_decode( (string) $choices_json, true );

        if ( 'true_false' === $question_type ) {
            return array(
                array(
                    'value' => 'true',
                    'label' => __( 'True', 'teqcidb' ),
                ),
                array(
                    'value' => 'false',
                    'label' => __( 'False', 'teqcidb' ),
                ),
            );
        }

        if ( ! is_array( $decoded ) ) {
            return array();
        }

        $choices = array();

        foreach ( $decoded as $index => $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $label = isset( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '';

            if ( '' === trim( $label ) ) {
                continue;
            }

            $choices[] = array(
                'value' => 'option_' . ( $index + 1 ),
                'label' => $label,
            );
        }

        return $choices;
    }

    private function sanitize_runtime_selected_values( $answer_data ) {
        $selected = array();

        if ( is_array( $answer_data ) && isset( $answer_data['selected'] ) ) {
            $candidate_values = is_array( $answer_data['selected'] ) ? $answer_data['selected'] : array( $answer_data['selected'] );

            foreach ( $candidate_values as $value ) {
                $normalized = sanitize_key( (string) $value );

                if ( '' !== $normalized ) {
                    $selected[] = $normalized;
                }
            }
        }

        return array_values( array_unique( $selected ) );
    }

    public function save_quiz_progress() {
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Please log in again and retry saving your quiz progress.', 'teqcidb' ) ) );
        }

        $quiz_id       = isset( $_POST['quiz_id'] ) ? absint( wp_unslash( $_POST['quiz_id'] ) ) : 0;
        $class_id      = isset( $_POST['class_id'] ) ? absint( wp_unslash( $_POST['class_id'] ) ) : 0;
        $attempt_id    = isset( $_POST['attempt_id'] ) ? absint( wp_unslash( $_POST['attempt_id'] ) ) : 0;
        $current_index = isset( $_POST['current_index'] ) ? absint( wp_unslash( $_POST['current_index'] ) ) : 0;
        $answers_json  = isset( $_POST['answers_json'] ) ? wp_unslash( $_POST['answers_json'] ) : '';
        $current_user  = get_current_user_id();

        $answers_payload = json_decode( (string) $answers_json, true );

        $result = $this->process_quiz_attempt_request(
            array(
                'quiz_id'       => $quiz_id,
                'class_id'      => $class_id,
                'attempt_id'    => $attempt_id,
                'current_index' => $current_index,
                'answers'       => $answers_payload,
            ),
            $current_user,
            false,
            'ajax'
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), $this->get_error_status_code( $result ) );
        }


        wp_send_json_success(
            array(
                'message'   => isset( $result['message'] ) ? $result['message'] : __( 'Quiz progress saved.', 'teqcidb' ),
                'attemptId' => isset( $result['attempt_id'] ) ? (int) $result['attempt_id'] : 0,
                'savedAt'   => isset( $result['saved_at'] ) ? (string) $result['saved_at'] : '',
            )
        );
    }

    public function submit_quiz_attempt() {
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Please log in again and retry submitting your quiz.', 'teqcidb' ) ) );
        }

        $quiz_id       = isset( $_POST['quiz_id'] ) ? absint( wp_unslash( $_POST['quiz_id'] ) ) : 0;
        $class_id      = isset( $_POST['class_id'] ) ? absint( wp_unslash( $_POST['class_id'] ) ) : 0;
        $attempt_id    = isset( $_POST['attempt_id'] ) ? absint( wp_unslash( $_POST['attempt_id'] ) ) : 0;
        $answers_json  = isset( $_POST['answers_json'] ) ? wp_unslash( $_POST['answers_json'] ) : '';
        $current_index = isset( $_POST['current_index'] ) ? absint( wp_unslash( $_POST['current_index'] ) ) : 0;
        $current_user  = get_current_user_id();

        $answers_payload = json_decode( (string) $answers_json, true );

        $result = $this->process_quiz_attempt_request(
            array(
                'quiz_id'       => $quiz_id,
                'class_id'      => $class_id,
                'attempt_id'    => $attempt_id,
                'current_index' => $current_index,
                'answers'       => $answers_payload,
            ),
            $current_user,
            true,
            'ajax'
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), $this->get_error_status_code( $result ) );
        }

        wp_send_json_success(
            array(
                'message'          => __( 'Quiz submitted.', 'teqcidb' ),
                'score'            => isset( $result['score'] ) ? (int) $result['score'] : 0,
                'passThreshold'    => isset( $result['pass_threshold'] ) ? (int) $result['pass_threshold'] : 75,
                'passed'           => ! empty( $result['passed'] ),
                'incorrectDetails' => isset( $result['incorrect_details'] ) ? $result['incorrect_details'] : array(),
                'attemptId'        => isset( $result['attempt_id'] ) ? (int) $result['attempt_id'] : 0,
                'savedAt'          => isset( $result['saved_at'] ) ? (string) $result['saved_at'] : '',
            )
        );
    }

    public function process_quiz_attempt_request( $request_data, $user_id, $is_final_submission, $request_source = 'ajax' ) {
        $quiz_id       = isset( $request_data['quiz_id'] ) ? absint( $request_data['quiz_id'] ) : 0;
        $class_id      = isset( $request_data['class_id'] ) ? absint( $request_data['class_id'] ) : 0;
        $attempt_id    = isset( $request_data['attempt_id'] ) ? absint( $request_data['attempt_id'] ) : 0;
        $current_index = isset( $request_data['current_index'] ) ? absint( $request_data['current_index'] ) : 0;
        $answers       = isset( $request_data['answers'] ) && is_array( $request_data['answers'] ) ? $request_data['answers'] : null;
        $user_id       = absint( $user_id );

        if ( $quiz_id <= 0 || $class_id <= 0 || ! is_array( $answers ) || $user_id <= 0 ) {
            return new WP_Error( 'teqcidb_invalid_payload', __( 'Unable to process quiz request because the payload was invalid.', 'teqcidb' ), array( 'status' => 400 ) );
        }

        if ( ! $this->is_quiz_assigned_to_class( $quiz_id, $class_id ) ) {
            return new WP_Error( 'teqcidb_quiz_unavailable', __( 'This quiz is not available for the selected class.', 'teqcidb' ), array( 'status' => 403 ) );
        }

        if ( ! $this->user_can_access_class_quiz( $class_id, $user_id ) ) {
            return new WP_Error( 'teqcidb_quiz_forbidden', __( 'You do not have access to this quiz.', 'teqcidb' ), array( 'status' => 403 ) );
        }

        if ( $attempt_id > 0 && ! $this->does_attempt_belong_to_user( $attempt_id, $quiz_id, $class_id, $user_id ) ) {
            return new WP_Error( 'teqcidb_attempt_forbidden', __( 'That quiz attempt does not belong to the current user.', 'teqcidb' ), array( 'status' => 403 ) );
        }

        $result = $this->persist_quiz_attempt_answers( $quiz_id, $class_id, $user_id, $answers, $current_index, $is_final_submission, $attempt_id );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $this->log_quiz_endpoint_usage( $request_source . '_' . ( $is_final_submission ? 'submit' : 'progress' ) );

        return $result;
    }

    public function is_quiz_assigned_to_class( $quiz_id, $class_id ) {
        global $wpdb;

        $quizzes_table      = $wpdb->prefix . 'teqcidb_quizzes';
        $quiz_classes_table = $wpdb->prefix . 'teqcidb_quiz_classes';

        $match_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT q.id FROM $quizzes_table q INNER JOIN $quiz_classes_table qc ON qc.quiz_id = q.id WHERE q.id = %d AND qc.class_id = %d LIMIT 1",
                $quiz_id,
                $class_id
            )
        );

        if ( $match_id > 0 ) {
            return true;
        }

        $legacy_match_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $quizzes_table WHERE id = %d AND FIND_IN_SET( CAST( %d AS CHAR ), REPLACE( class_id, ' ', '' ) ) > 0 LIMIT 1",
                $quiz_id,
                $class_id
            )
        );

        return $legacy_match_id > 0;
    }

    public function user_can_access_class_quiz( $class_id, $user_id ) {
        global $wpdb;

        $classes_table = $wpdb->prefix . 'teqcidb_classes';
        $class_row     = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT allallowedquiz, quizstudentsallowed FROM $classes_table WHERE id = %d LIMIT 1",
                $class_id
            ),
            ARRAY_A
        );

        if ( ! is_array( $class_row ) ) {
            return false;
        }

        $quiz_access_mode = isset( $class_row['allallowedquiz'] ) ? sanitize_key( (string) $class_row['allallowedquiz'] ) : '';

        if ( 'blocked' !== $quiz_access_mode ) {
            return true;
        }

        $quiz_students_allowed = $this->format_class_student_list_for_response( isset( $class_row['quizstudentsallowed'] ) ? $class_row['quizstudentsallowed'] : '' );

        foreach ( $quiz_students_allowed as $allowed_student ) {
            if ( isset( $allowed_student['wpuserid'] ) && absint( $allowed_student['wpuserid'] ) === $user_id ) {
                return true;
            }
        }

        return false;
    }

    private function does_attempt_belong_to_user( $attempt_id, $quiz_id, $class_id, $user_id ) {
        global $wpdb;

        $attempts_table = $wpdb->prefix . 'teqcidb_quiz_attempts';
        $match_id       = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $attempts_table WHERE id = %d AND quiz_id = %d AND class_id = %d AND user_id = %d LIMIT 1",
                $attempt_id,
                $quiz_id,
                $class_id,
                $user_id
            )
        );

        return $match_id > 0;
    }

    private function get_error_status_code( $error ) {
        if ( ! ( $error instanceof WP_Error ) ) {
            return 400;
        }

        $error_data = $error->get_error_data();

        if ( is_array( $error_data ) && isset( $error_data['status'] ) ) {
            return max( 400, absint( $error_data['status'] ) );
        }

        return 400;
    }

    private function log_quiz_endpoint_usage( $channel ) {
        $option_key = 'teqcidb_quiz_endpoint_usage';
        $usage      = get_option( $option_key, array() );

        if ( ! is_array( $usage ) ) {
            $usage = array();
        }

        $usage_key = sanitize_key( (string) $channel );

        if ( '' === $usage_key ) {
            return;
        }

        $usage[ $usage_key ] = isset( $usage[ $usage_key ] ) ? absint( $usage[ $usage_key ] ) + 1 : 1;
        update_option( $option_key, $usage, false );
    }

    private function persist_quiz_attempt_answers( $quiz_id, $class_id, $user_id, $answers_payload, $current_index, $is_final_submission, $attempt_id = 0 ) {
        global $wpdb;

        $attempts_table     = $wpdb->prefix . 'teqcidb_quiz_attempts';
        $answers_table      = $wpdb->prefix . 'teqcidb_quiz_answers';
        $answer_items_table = $wpdb->prefix . 'teqcidb_quiz_answer_items';
        $questions_table    = $wpdb->prefix . 'teqcidb_quiz_questions';
        $classes_table      = $wpdb->prefix . 'teqcidb_classes';

        $attempt = array();

        if ( $attempt_id > 0 ) {
            $attempt = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id, status FROM $attempts_table WHERE id = %d LIMIT 1",
                    $attempt_id
                ),
                ARRAY_A
            );
        } else {
            $attempt = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id, status FROM $attempts_table WHERE quiz_id = %d AND class_id = %d AND user_id = %d ORDER BY id DESC LIMIT 1",
                    $quiz_id,
                    $class_id,
                    $user_id
                ),
                ARRAY_A
            );
        }

        $attempt_id = isset( $attempt['id'] ) ? absint( $attempt['id'] ) : 0;

        if ( $attempt_id > 0 && isset( $attempt['status'] ) && in_array( (int) $attempt['status'], array( 0, 1 ), true ) ) {
            return new WP_Error( 'teqcidb_attempt_submitted', __( 'This quiz attempt has already been submitted.', 'teqcidb' ), array( 'status' => 409 ) );
        }

        if ( $attempt_id <= 0 ) {
            $inserted = $wpdb->insert(
                $attempts_table,
                array(
                    'quiz_id'       => $quiz_id,
                    'class_id'      => $class_id,
                    'user_id'       => $user_id,
                    'status'        => 2,
                    'current_index' => 0,
                ),
                array( '%d', '%d', '%d', '%d', '%d' )
            );

            if ( false === $inserted ) {
                return new WP_Error( 'teqcidb_attempt_create_failed', __( 'Unable to create a new quiz attempt.', 'teqcidb' ), array( 'status' => 500 ) );
            }

            $attempt_id = (int) $wpdb->insert_id;
        }

        $question_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, type, prompt, choices_json FROM $questions_table WHERE quiz_id = %d ORDER BY sort_order ASC, id ASC",
                $quiz_id
            ),
            ARRAY_A
        );

        if ( ! is_array( $question_rows ) || empty( $question_rows ) ) {
            return new WP_Error( 'teqcidb_quiz_no_questions', __( 'Quiz has no questions to save.', 'teqcidb' ), array( 'status' => 400 ) );
        }

        $question_ids = array();

        foreach ( $question_rows as $row ) {
            $question_id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;

            if ( $question_id > 0 ) {
                $question_ids[] = $question_id;
            }
        }

        $sanitized_answers = array();

        foreach ( $question_ids as $question_id ) {
            $question_key = (string) $question_id;

            if ( ! array_key_exists( $question_key, $answers_payload ) ) {
                continue;
            }

            $sanitized_answers[ $question_id ] = $this->sanitize_runtime_selected_values( array( 'selected' => $answers_payload[ $question_key ] ) );
        }

        if ( ! empty( $sanitized_answers ) ) {
            $existing_item_rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT question_id, selected_json FROM $answer_items_table WHERE attempt_id = %d",
                    $attempt_id
                ),
                ARRAY_A
            );

            $existing_items = array();

            if ( is_array( $existing_item_rows ) ) {
                foreach ( $existing_item_rows as $existing_item_row ) {
                    $existing_question_id = isset( $existing_item_row['question_id'] ) ? absint( $existing_item_row['question_id'] ) : 0;

                    if ( $existing_question_id <= 0 ) {
                        continue;
                    }

                    $existing_items[ $existing_question_id ] = isset( $existing_item_row['selected_json'] ) ? (string) $existing_item_row['selected_json'] : '';
                }
            }

            foreach ( $sanitized_answers as $question_id => $selected_values ) {
                $selected_json = wp_json_encode( $selected_values );

                if ( ! $selected_json ) {
                    continue;
                }

                $has_existing = array_key_exists( $question_id, $existing_items );
                $is_changed   = true;

                if ( $has_existing ) {
                    $previous_values = json_decode( $existing_items[ $question_id ], true );
                    $previous_values = $this->sanitize_runtime_selected_values( array( 'selected' => $previous_values ) );
                    $is_changed      = $previous_values !== $selected_values;
                }

                if ( ! $is_changed ) {
                    continue;
                }

                if ( $has_existing ) {
                    $wpdb->update(
                        $answer_items_table,
                        array(
                            'selected_json' => $selected_json,
                            'updated_at'    => current_time( 'mysql' ),
                        ),
                        array(
                            'attempt_id'  => $attempt_id,
                            'question_id' => $question_id,
                        ),
                        array( '%s', '%s' ),
                        array( '%d', '%d' )
                    );
                } else {
                    $wpdb->insert(
                        $answer_items_table,
                        array(
                            'attempt_id'    => $attempt_id,
                            'question_id'   => $question_id,
                            'selected_json' => $selected_json,
                            'updated_at'    => current_time( 'mysql' ),
                        ),
                        array( '%d', '%d', '%s', '%s' )
                    );
                }
            }
        }

        $saved_at = current_time( 'mysql' );

        $wpdb->update(
            $attempts_table,
            array(
                'status'        => 2,
                'current_index' => max( 0, absint( $current_index ) ),
            ),
            array( 'id' => $attempt_id ),
            array( '%d', '%d' ),
            array( '%d' )
        );

        if ( ! $is_final_submission ) {
            return array(
                'attempt_id' => $attempt_id,
                'saved_at'   => $saved_at,
                'message'    => __( 'Quiz progress saved.', 'teqcidb' ),
            );
        }

        $stored_answer_items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT question_id, selected_json FROM $answer_items_table WHERE attempt_id = %d",
                $attempt_id
            ),
            ARRAY_A
        );

        $stored_answers = array();

        if ( is_array( $stored_answer_items ) ) {
            foreach ( $stored_answer_items as $stored_answer_item ) {
                $stored_question_id = isset( $stored_answer_item['question_id'] ) ? absint( $stored_answer_item['question_id'] ) : 0;

                if ( $stored_question_id <= 0 ) {
                    continue;
                }

                $selected_values               = json_decode( isset( $stored_answer_item['selected_json'] ) ? (string) $stored_answer_item['selected_json'] : '', true );
                $stored_answers[ $stored_question_id ] = $this->sanitize_runtime_selected_values( array( 'selected' => $selected_values ) );
            }
        }

        $correct_count      = 0;
        $incorrect_details  = array();
        $final_answers_data = array();

        foreach ( $question_rows as $row ) {
            $question_id  = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
            $question_key = (string) $question_id;

            if ( $question_id <= 0 ) {
                continue;
            }

            $selected = isset( $stored_answers[ $question_id ] ) ? $stored_answers[ $question_id ] : array();

            $evaluation = $this->evaluate_runtime_answer( isset( $row['type'] ) ? $row['type'] : '', isset( $row['choices_json'] ) ? $row['choices_json'] : '', $selected );

            if ( $evaluation['answered'] && $evaluation['is_correct'] ) {
                $correct_count++;
            }

            if ( $evaluation['answered'] && ! $evaluation['is_correct'] ) {
                $incorrect_details[] = array(
                    'questionId'        => $question_id,
                    'prompt'            => wp_strip_all_tags( isset( $row['prompt'] ) ? (string) $row['prompt'] : '' ),
                    'type'              => sanitize_key( isset( $row['type'] ) ? (string) $row['type'] : '' ),
                    'choices'           => $this->normalize_question_choices_for_runtime( isset( $row['type'] ) ? $row['type'] : '', isset( $row['choices_json'] ) ? $row['choices_json'] : '' ),
                    'selected'          => $selected,
                    'correctSelections' => $evaluation['correct_values'],
                );
            }

            $final_answers_data[ $question_key ] = array(
                'selected' => $selected,
                'answered' => $evaluation['answered'],
                'correct'  => $evaluation['is_correct'],
            );
        }

        $total_questions = count( $question_rows );
        $score           = $total_questions > 0 ? (int) round( ( $correct_count / $total_questions ) * 100 ) : 0;

        $class_type = (string) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT classtype FROM $classes_table WHERE id = %d LIMIT 1",
                $class_id
            )
        );

        $pass_threshold = ( 'refresher' === strtolower( sanitize_key( $class_type ) ) ) ? 80 : 75;
        $passed         = $score >= $pass_threshold;

        $legacy_payload_json = wp_json_encode(
            array(
                'answers'           => $final_answers_data,
                'current_index'     => max( 0, absint( $current_index ) ),
                'total_questions'   => $total_questions,
                'score'             => $score,
                'incorrect_details' => $incorrect_details,
            )
        );

        if ( $legacy_payload_json ) {
            $existing_answer_id = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $answers_table WHERE attempt_id = %d LIMIT 1",
                    $attempt_id
                )
            );

            if ( $existing_answer_id > 0 ) {
                $wpdb->update(
                    $answers_table,
                    array( 'answers_json' => $legacy_payload_json ),
                    array( 'attempt_id' => $attempt_id ),
                    array( '%s' ),
                    array( '%d' )
                );
            } else {
                $wpdb->insert(
                    $answers_table,
                    array(
                        'attempt_id'   => $attempt_id,
                        'answers_json' => $legacy_payload_json,
                    ),
                    array( '%d', '%s' )
                );
            }
        }

        $wpdb->update(
            $attempts_table,
            array(
                'status'        => $passed ? 0 : 1,
                'score'         => $score,
                'submitted_at'  => $saved_at,
                'current_index' => max( 0, absint( $current_index ) ),
            ),
            array( 'id' => $attempt_id ),
            array( '%d', '%d', '%s', '%d' ),
            array( '%d' )
        );

        if ( $passed ) {
            $normalized_class_type = strtolower( sanitize_key( $class_type ) );

            if ( in_array( $normalized_class_type, array( 'initial', 'refresher' ), true ) ) {
                $this->apply_quiz_pass_updates( $user_id, $class_id, $normalized_class_type );
            }
        }

        return array(
            'attempt_id'        => $attempt_id,
            'score'             => $score,
            'pass_threshold'    => $pass_threshold,
            'passed'            => $passed,
            'incorrect_details' => $incorrect_details,
            'saved_at'          => $saved_at,
            'message'           => __( 'Quiz submitted.', 'teqcidb' ),
        );
    }

    private function apply_quiz_pass_updates( $user_id, $class_id, $class_type ) {
        global $wpdb;

        $user_id           = absint( $user_id );
        $class_id          = absint( $class_id );
        $class_type        = sanitize_key( (string) $class_type );
        $students_table    = $wpdb->prefix . 'teqcidb_students';
        $classes_table     = $wpdb->prefix . 'teqcidb_classes';
        $studenthistory_table = $wpdb->prefix . 'teqcidb_studenthistory';

        if ( $user_id <= 0 || $class_id <= 0 ) {
            return;
        }

        $student_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, uniquestudentid, qcinumber, expiration_date, initial_training_date, last_refresher_date FROM $students_table WHERE wpuserid = %d ORDER BY id DESC LIMIT 1",
                $user_id
            ),
            ARRAY_A
        );

        if ( ! is_array( $student_row ) || empty( $student_row['id'] ) ) {
            return;
        }

        $student_id = absint( $student_row['id'] );
        $today      = current_time( 'Y-m-d' );
        $student_update = array();
        $student_formats = array();

        $existing_qci_number = isset( $student_row['qcinumber'] ) ? trim( (string) $student_row['qcinumber'] ) : '';

        if ( '' === $existing_qci_number ) {
            $student_update['qcinumber'] = $this->generate_next_qci_number();
            $student_formats[]           = '%s';
        }

        $existing_expiration = isset( $student_row['expiration_date'] ) ? trim( (string) $student_row['expiration_date'] ) : '';
        $expiration_source   = $this->parse_student_date_value( $existing_expiration );

        if ( ! $expiration_source ) {
            $expiration_source = $this->parse_student_date_value( $today );
        }

        if ( $expiration_source ) {
            $student_update['expiration_date'] = $expiration_source->modify( '+2 years' )->format( 'Y-m-d' );
            $student_formats[]                 = '%s';
        }

        if ( 'initial' === $class_type ) {
            $existing_initial_training_date = isset( $student_row['initial_training_date'] ) ? trim( (string) $student_row['initial_training_date'] ) : '';

            if ( '' === $existing_initial_training_date || '0000-00-00' === $existing_initial_training_date ) {
                $student_update['initial_training_date'] = $today;
                $student_formats[]                       = '%s';
            }
        }

        if ( 'refresher' === $class_type ) {
            $student_update['last_refresher_date'] = $today;
            $student_formats[]                     = '%s';
        }

        if ( ! empty( $student_update ) ) {
            $wpdb->update(
                $students_table,
                $student_update,
                array( 'id' => $student_id ),
                $student_formats,
                array( '%d' )
            );
        }

        $class_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT uniqueclassid, classname FROM $classes_table WHERE id = %d LIMIT 1",
                $class_id
            ),
            ARRAY_A
        );

        if ( ! is_array( $class_row ) ) {
            return;
        }

        $unique_class_id = isset( $class_row['uniqueclassid'] ) ? sanitize_text_field( (string) $class_row['uniqueclassid'] ) : '';
        $class_name      = isset( $class_row['classname'] ) ? sanitize_text_field( (string) $class_row['classname'] ) : '';
        $unique_student_id = isset( $student_row['uniquestudentid'] ) ? sanitize_text_field( (string) $student_row['uniquestudentid'] ) : '';

        $history_id = 0;

        if ( '' !== $unique_class_id ) {
            $history_id = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $studenthistory_table WHERE wpuserid = %d AND uniqueclassid = %s ORDER BY enrollmentdate DESC, id DESC LIMIT 1",
                    $user_id,
                    $unique_class_id
                )
            );

            if ( $history_id <= 0 && '' !== $unique_student_id ) {
                $history_id = (int) $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM $studenthistory_table WHERE uniquestudentid = %s AND uniqueclassid = %s ORDER BY enrollmentdate DESC, id DESC LIMIT 1",
                        $unique_student_id,
                        $unique_class_id
                    )
                );
            }
        }

        if ( $history_id <= 0 && '' !== $class_name ) {
            $history_id = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $studenthistory_table WHERE wpuserid = %d AND classname = %s ORDER BY enrollmentdate DESC, id DESC LIMIT 1",
                    $user_id,
                    $class_name
                )
            );
        }

        if ( $history_id > 0 ) {
            $wpdb->update(
                $studenthistory_table,
                array(
                    'outcome'       => 'Passed',
                    'attended'      => 'Yes',
                    'adminapproved' => 'Yes',
                ),
                array( 'id' => $history_id ),
                array( '%s', '%s', '%s' ),
                array( '%d' )
            );
        }
    }

    private function generate_next_qci_number() {
        global $wpdb;

        $students_table = $wpdb->prefix . 'teqcidb_students';
        $qci_values     = $wpdb->get_col( "SELECT qcinumber FROM $students_table WHERE qcinumber IS NOT NULL AND qcinumber <> ''" );
        $max_numeric    = 0;

        if ( is_array( $qci_values ) ) {
            foreach ( $qci_values as $qci_value ) {
                if ( ! is_scalar( $qci_value ) ) {
                    continue;
                }

                if ( preg_match( '/^T(\d{1,})/i', trim( (string) $qci_value ), $matches ) ) {
                    $max_numeric = max( $max_numeric, absint( $matches[1] ) );
                }
            }
        }

        return 'T' . str_pad( (string) ( $max_numeric + 1 ), 4, '0', STR_PAD_LEFT );
    }

    private function parse_student_date_value( $value ) {
        $normalized = trim( (string) $value );

        if ( '' === $normalized ) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat( 'Y-m-d', $normalized );

        if ( $date instanceof DateTimeImmutable && $date->format( 'Y-m-d' ) === $normalized ) {
            return $date;
        }

        return null;
    }


    private function evaluate_runtime_answer( $question_type, $choices_json, $selected_values ) {
        $question_type  = sanitize_key( (string) $question_type );
        $selected       = is_array( $selected_values ) ? array_values( array_unique( array_map( 'sanitize_key', $selected_values ) ) ) : array();
        $correct_values = array();

        if ( 'true_false' === $question_type ) {
            $decoded = json_decode( (string) $choices_json, true );

            if ( is_array( $decoded ) && isset( $decoded[0]['correct'] ) ) {
                $correct_value = sanitize_key( (string) $decoded[0]['correct'] );

                if ( in_array( $correct_value, array( 'true', 'false' ), true ) ) {
                    $correct_values = array( $correct_value );
                }
            }
        } else {
            $decoded = json_decode( (string) $choices_json, true );

            if ( is_array( $decoded ) ) {
                foreach ( $decoded as $index => $choice ) {
                    if ( is_array( $choice ) && ! empty( $choice['correct'] ) ) {
                        $correct_values[] = 'option_' . ( $index + 1 );
                    }
                }
            }
        }

        sort( $selected );
        sort( $correct_values );

        return array(
            'answered'       => ! empty( $selected ),
            'is_correct'     => ! empty( $selected ) && $selected === $correct_values,
            'correct_values' => $correct_values,
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
        $multiple_raw = isset( $_POST['multiple_students'] ) ? wp_unslash( (string) $_POST['multiple_students'] ) : '';

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
                "SELECT id, classname, classcost, classtype, classstartdate, classhide FROM $table_name WHERE id = %d",
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

        $raw_cost                     = isset( $row['classcost'] ) ? (string) $row['classcost'] : '';
        $base_amount                  = (float) preg_replace( '/[^0-9.]/', '', $raw_cost );
        $class_type                   = isset( $row['classtype'] ) ? sanitize_key( (string) $row['classtype'] ) : '';
        $allow_association_discounts  = $this->class_type_allows_association_discount( $class_type );

        $selected_students = $this->parse_selected_students_for_checkout( $multiple_raw );
        $selected_count    = count( $selected_students );

        if ( $selected_count <= 0 ) {
            $selected_students = array(
                array(
                    'wpid' => get_current_user_id(),
                ),
            );
            $selected_count    = 1;
        }

        $discount_count = $allow_association_discounts ? $this->count_association_discounts_for_selected_students( $selected_students ) : 0;
        $amount         = ( $base_amount * $selected_count ) - ( 50 * $discount_count );

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
                'totalAmount' => number_format( $amount, 2, '.', '' ),
                'studentCount' => $selected_count,
                'discountCount' => $discount_count,
            )
        );
    }

    private function parse_selected_students_for_checkout( $raw_value ) {
        if ( ! is_string( $raw_value ) || '' === trim( $raw_value ) ) {
            return array();
        }

        $decoded = json_decode( $raw_value, true );

        if ( ! is_array( $decoded ) || empty( $decoded ) ) {
            return array();
        }

        $normalized = array();

        foreach ( $decoded as $entry ) {
            $wpid = 0;

            if ( is_array( $entry ) ) {
                if ( isset( $entry['wpid'] ) ) {
                    $wpid = absint( $entry['wpid'] );
                } elseif ( isset( $entry['wpuserid'] ) ) {
                    $wpid = absint( $entry['wpuserid'] );
                } elseif ( isset( $entry['id'] ) ) {
                    $wpid = absint( $entry['id'] );
                }
            } elseif ( is_scalar( $entry ) ) {
                $wpid = absint( $entry );
            }

            if ( $wpid <= 0 ) {
                continue;
            }

            $normalized[] = array(
                'wpid' => $wpid,
            );
        }

        return array_values(
            array_filter(
                $normalized,
                static function ( $entry ) {
                    return is_array( $entry ) && ! empty( $entry['wpid'] );
                }
            )
        );
    }

    private function class_type_allows_association_discount( $class_type ) {
        return 'initial' === strtolower( sanitize_key( (string) $class_type ) );
    }

    private function count_association_discounts_for_selected_students( array $selected_students ) {
        if ( empty( $selected_students ) ) {
            return 0;
        }

        $wpids = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static function ( $entry ) {
                            return isset( $entry['wpid'] ) ? absint( $entry['wpid'] ) : 0;
                        },
                        $selected_students
                    )
                )
            )
        );

        if ( empty( $wpids ) ) {
            return 0;
        }

        global $wpdb;
        $students_table = $wpdb->prefix . 'teqcidb_students';
        $like           = $wpdb->esc_like( $students_table );
        $found          = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $students_table ) {
            return 0;
        }

        $placeholders = implode( ', ', array_fill( 0, count( $wpids ), '%d' ) );
        $query        = "SELECT wpuserid, associations FROM $students_table WHERE wpuserid IN ($placeholders)";
        $rows         = $wpdb->get_results( $wpdb->prepare( $query, $wpids ), ARRAY_A );

        if ( ! is_array( $rows ) || empty( $rows ) ) {
            return 0;
        }

        $discount_count = 0;

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $associations = isset( $row['associations'] ) ? $row['associations'] : '';

            if ( $this->student_has_any_association( $associations ) ) {
                $discount_count++;
            }
        }

        return $discount_count;
    }

    private function student_has_any_association( $value ) {
        if ( is_array( $value ) ) {
            return ! empty(
                array_filter(
                    $value,
                    static function ( $item ) {
                        return '' !== trim( (string) $item );
                    }
                )
            );
        }

        $raw = is_scalar( $value ) ? trim( (string) $value ) : '';

        if ( '' === $raw ) {
            return false;
        }

        $decoded = json_decode( $raw, true );

        if ( is_array( $decoded ) ) {
            return ! empty(
                array_filter(
                    $decoded,
                    static function ( $item ) {
                        return '' !== trim( (string) $item );
                    }
                )
            );
        }

        $parts = array_filter(
            array_map( 'trim', explode( ',', $raw ) ),
            static function ( $item ) {
                return '' !== $item;
            }
        );

        return ! empty( $parts );
    }

    private function get_students_by_wpids( array $wpids ) {
        $wpids = array_values(
            array_unique(
                array_filter(
                    array_map( 'absint', $wpids )
                )
            )
        );

        if ( empty( $wpids ) ) {
            return array();
        }

        global $wpdb;

        $students_table = $wpdb->prefix . 'teqcidb_students';
        $like           = $wpdb->esc_like( $students_table );
        $found          = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $students_table ) {
            return array();
        }

        $placeholders = implode( ', ', array_fill( 0, count( $wpids ), '%d' ) );
        $query        = "SELECT wpuserid, uniquestudentid, first_name, last_name, email, company, phone_cell, their_representative, associations FROM $students_table WHERE wpuserid IN ($placeholders)";
        $results      = $wpdb->get_results( $wpdb->prepare( $query, $wpids ), ARRAY_A );

        return is_array( $results ) ? $results : array();
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
                "SELECT uniquestudentid, is_a_representative, email, first_name, last_name, company, phone_cell, their_representative FROM $students_table WHERE wpuserid = %d LIMIT 1",
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
        $class_cost      = 0.0;

        if ( $class_id > 0 ) {
            $class_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT uniqueclassid, classname, classcost, classtype, classformat, classstartdate, classstarttime, classurl, teamslink FROM $classes_table WHERE id = %d LIMIT 1",
                    $class_id
                ),
                ARRAY_A
            );

            if ( is_array( $class_row ) && ! empty( $class_row['uniqueclassid'] ) ) {
                $unique_class_id = sanitize_text_field( (string) $class_row['uniqueclassid'] );
            }

            if ( is_array( $class_row ) && isset( $class_row['classcost'] ) ) {
                $class_cost = (float) preg_replace( '/[^0-9.]/', '', (string) $class_row['classcost'] );
            }
        }

        $class_name                  = is_array( $class_row ) && ! empty( $class_row['classname'] ) ? sanitize_text_field( (string) $class_row['classname'] ) : '';
        $allow_association_discounts = $this->class_type_allows_association_discount( is_array( $class_row ) && isset( $class_row['classtype'] ) ? (string) $class_row['classtype'] : '' );

        $amount_numeric = (float) preg_replace( '/[^0-9.\-]/', '', $total_paid_raw );
        $total_paid     = number_format( $amount_numeric, 2, '.', '' );

        $multiple_students     = '';
        $selected_student_rows = array();
        $selected_students     = $this->parse_selected_students_for_checkout( $multiple_raw );
        $selected_wpids        = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static function ( $entry ) {
                            return isset( $entry['wpid'] ) ? absint( $entry['wpid'] ) : 0;
                        },
                        $selected_students
                    )
                )
            )
        );

        if ( ! empty( $selected_wpids ) ) {
            $multiple_students     = wp_json_encode( $selected_wpids );
            $selected_student_rows = $this->get_students_by_wpids( $selected_wpids );
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

        if ( ! empty( $selected_student_rows ) ) {
            foreach ( $selected_student_rows as $student_payment_row ) {
                if ( ! is_array( $student_payment_row ) ) {
                    continue;
                }

                $student_wpid         = isset( $student_payment_row['wpuserid'] ) ? absint( $student_payment_row['wpuserid'] ) : 0;
                $student_unique_id    = isset( $student_payment_row['uniquestudentid'] ) ? sanitize_text_field( (string) $student_payment_row['uniquestudentid'] ) : '';
                $student_email        = isset( $student_payment_row['email'] ) ? sanitize_email( (string) $student_payment_row['email'] ) : '';
                $student_associations = isset( $student_payment_row['associations'] ) ? $student_payment_row['associations'] : '';

                if ( $student_wpid <= 0 ) {
                    continue;
                }

                $student_has_discount = $allow_association_discounts && $this->student_has_any_association( $student_associations );
                $student_amount_value = max( 0, $class_cost - ( $student_has_discount ? 50 : 0 ) );
                $student_amount_paid  = number_format( $student_amount_value, 2, '.', '' );

                $student_payment_inserted = $wpdb->insert(
                    $history_table,
                    array(
                        'wpuserid'         => $student_wpid,
                        'uniquestudentid'  => $student_unique_id,
                        'email'            => $student_email,
                        'uniqueclassid'    => $unique_class_id,
                        'totalpaid'        => $student_amount_paid,
                        'transid'          => $trans_id,
                        'transtime'        => $this->format_payment_history_time( $gateway_time ),
                        'multiplestudents' => '',
                        'invoicenumber'    => $invoice_number,
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

                if ( false === $student_payment_inserted ) {
                    wp_send_json_error(
                        array(
                            'message' => __( 'Payment was captured, but a selected student payment history record could not be saved.', 'teqcidb' ),
                        )
                    );
                }

                $student_history_inserted = $wpdb->insert(
                    $student_history_table,
                    array(
                        'uniquestudentid'  => $student_unique_id,
                        'wpuserid'         => $student_wpid,
                        'classname'        => $class_name,
                        'uniqueclassid'    => $unique_class_id,
                        'registered'       => 'Yes',
                        'adminapproved'    => 'Yes',
                        'attended'         => 'Upcoming',
                        'outcome'          => 'Upcoming',
                        'paymentstatus'    => 'Paid in Full',
                        'amountpaid'       => $student_amount_paid,
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
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                    )
                );

                if ( false === $student_history_inserted ) {
                    wp_send_json_error(
                        array(
                            'message' => __( 'Payment was captured, but selected student history could not be saved.', 'teqcidb' ),
                        )
                    );
                }
            }
        } else {
            $student_history_inserted = $wpdb->insert(
                $student_history_table,
                array(
                    'uniquestudentid'  => $uniquestudentid,
                    'wpuserid'         => $user_id,
                    'classname'        => $class_name,
                    'uniqueclassid'    => $unique_class_id,
                    'registered'       => 'Yes',
                    'adminapproved'    => 'Yes',
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
                    '%s',
                    '%s',
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
        }

        if ( ! empty( $selected_student_rows ) ) {
            $representative_context = is_array( $student_row ) ? $student_row : array();
            $class_context          = is_array( $class_row ) ? $class_row : array();

            $this->maybe_send_representative_initial_online_student_emails(
                $representative_context,
                $class_context,
                $total_paid,
                $selected_student_rows
            );
            $this->maybe_send_representative_refresher_online_student_emails(
                $representative_context,
                $class_context,
                $total_paid,
                $selected_student_rows
            );
            $this->maybe_send_representative_initial_in_person_student_emails(
                $representative_context,
                $class_context,
                $total_paid,
                $selected_student_rows
            );
            $this->maybe_send_representative_refresher_in_person_student_emails(
                $representative_context,
                $class_context,
                $total_paid,
                $selected_student_rows
            );
            $this->maybe_send_representative_initial_online_representative_email(
                $representative_context,
                $class_context,
                $total_paid,
                $selected_student_rows
            );
            $this->maybe_send_representative_refresher_online_representative_email(
                $representative_context,
                $class_context,
                $total_paid,
                $selected_student_rows
            );
            $this->maybe_send_representative_initial_in_person_representative_email(
                $representative_context,
                $class_context,
                $total_paid,
                $selected_student_rows
            );
            $this->maybe_send_representative_refresher_in_person_representative_email(
                $representative_context,
                $class_context,
                $total_paid,
                $selected_student_rows
            );
        } else {
            $this->maybe_send_student_self_initial_online_email(
                $email,
                is_array( $student_row ) ? $student_row : array(),
                is_array( $class_row ) ? $class_row : array(),
                $total_paid
            );
            $this->maybe_send_student_self_refresher_online_email(
                $email,
                is_array( $student_row ) ? $student_row : array(),
                is_array( $class_row ) ? $class_row : array(),
                $total_paid
            );
            $this->maybe_send_student_self_initial_in_person_email(
                $email,
                is_array( $student_row ) ? $student_row : array(),
                is_array( $class_row ) ? $class_row : array(),
                $total_paid
            );
            $this->maybe_send_student_self_refresher_in_person_email(
                $email,
                is_array( $student_row ) ? $student_row : array(),
                is_array( $class_row ) ? $class_row : array(),
                $total_paid
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
            wp_send_json_error(
                array(
                    'message' => __( 'Please provide a valid email address.', 'teqcidb' ),
                )
            );
        }

        if ( ! $creating_new_student && $email_provided && '' === $email ) {
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
                wp_send_json_error(
                    array(
                        'message' => __( 'The passwords do not match.', 'teqcidb' ),
                    )
                );
            } elseif ( ! $this->is_strong_password( $user_pass ) ) {
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

        $saved_student_id = $id > 0 ? $id : (int) $wpdb->insert_id;
        $this->sync_admin_representative_assignments( $saved_student_id );

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
            wp_send_json_error(
                array(
                    'message' => __( 'You must be logged in to update your profile.', 'teqcidb' ),
                )
            );
        }

        $current_user = wp_get_current_user();

        if ( ! ( $current_user instanceof WP_User ) || ! $current_user->exists() ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to locate your account.', 'teqcidb' ),
                )
            );
        }

        $first_name = $this->sanitize_text_value( 'first_name' );
        $last_name  = $this->sanitize_text_value( 'last_name' );

        if ( '' === $first_name || '' === $last_name ) {
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
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to save your profile. Please try again.', 'teqcidb' ),
                )
            );
        }

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
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid student history entry.', 'teqcidb' ),
                )
            );
        }

        $history_data = isset( $_POST['studenthistory'] ) ? wp_unslash( $_POST['studenthistory'] ) : array();

        if ( ! is_array( $history_data ) || ! isset( $history_data[ $history_id ] ) || ! is_array( $history_data[ $history_id ] ) ) {
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
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to save the student history entry. Please try again.', 'teqcidb' ),
                )
            );
        }

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
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid student history entry.', 'teqcidb' ),
                )
            );
        }

        $deleted = $wpdb->delete( $table, array( 'id' => $history_id ), array( '%d' ) );

        if ( false === $deleted ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to delete the student history entry. Please try again.', 'teqcidb' ),
                )
            );
        }

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
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        $history_key = isset( $_POST['history_id'] ) ? sanitize_key( wp_unslash( $_POST['history_id'] ) ) : '';

        if ( '' === $history_key ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid student history entry.', 'teqcidb' ),
                )
            );
        }

        $history_data = isset( $_POST['studenthistory'] ) ? wp_unslash( $_POST['studenthistory'] ) : array();

        if ( ! is_array( $history_data ) || ! isset( $history_data[ $history_key ] ) || ! is_array( $history_data[ $history_key ] ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Missing student history details.', 'teqcidb' ),
                )
            );
        }

        $entry = $history_data[ $history_key ];

        $unique_student_id = isset( $entry['uniquestudentid'] ) ? sanitize_text_field( (string) $entry['uniquestudentid'] ) : '';

        if ( '' === $unique_student_id ) {
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
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to create the student history entry. Please try again.', 'teqcidb' ),
                )
            );
        }

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

        $quiz_id = isset( $_POST['quiz_id'] ) ? absint( wp_unslash( $_POST['quiz_id'] ) ) : 0;

        if ( $quiz_id > 0 && ! $this->quiz_exists( $quiz_id ) ) {
            $quiz_id = 0;
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
                wp_send_json_error(
                    array(
                        'message' => __( 'Unable to save the class. Please try again.', 'teqcidb' ),
                    )
                );
            }
        }

        $saved_class_id = $id > 0 ? $id : (int) $wpdb->insert_id;

        if ( $saved_class_id > 0 ) {
            $this->sync_class_quiz_mapping( $saved_class_id, $quiz_id );
        }

        wp_send_json_success(
            array(
                'message' => $message,
            )
        );
    }

    private function quiz_exists( $quiz_id ) {
        global $wpdb;

        $quiz_id = absint( $quiz_id );

        if ( $quiz_id <= 0 ) {
            return false;
        }

        $table = $wpdb->prefix . 'teqcidb_quizzes';

        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE id = %d LIMIT 1",
                $quiz_id
            )
        );
    }

    private function sync_class_quiz_mapping( $class_id, $quiz_id ) {
        global $wpdb;

        $class_id           = absint( $class_id );
        $quiz_id            = absint( $quiz_id );
        $quiz_table         = $wpdb->prefix . 'teqcidb_quizzes';
        $quiz_classes_table = $wpdb->prefix . 'teqcidb_quiz_classes';

        if ( $class_id <= 0 ) {
            return;
        }

        $existing_quiz_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT quiz_id FROM $quiz_classes_table WHERE class_id = %d",
                $class_id
            )
        );

        $wpdb->delete( $quiz_classes_table, array( 'class_id' => $class_id ), array( '%d' ) );

        if ( $quiz_id > 0 ) {
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT IGNORE INTO $quiz_classes_table (quiz_id, class_id) VALUES (%d, %d)",
                    $quiz_id,
                    $class_id
                )
            );

            $existing_quiz_ids[] = $quiz_id;
        }

        $existing_quiz_ids = array_values( array_unique( array_map( 'absint', $existing_quiz_ids ) ) );

        foreach ( $existing_quiz_ids as $existing_quiz_id ) {
            if ( $existing_quiz_id <= 0 ) {
                continue;
            }

            $class_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT class_id FROM $quiz_classes_table WHERE quiz_id = %d ORDER BY class_id ASC",
                    $existing_quiz_id
                )
            );

            $class_ids = array_values( array_filter( array_map( 'absint', $class_ids ) ) );
            $class_csv = implode( ',', $class_ids );

            $wpdb->update(
                $quiz_table,
                array( 'class_id' => $class_csv ),
                array( 'id' => $existing_quiz_id ),
                array( '%s' ),
                array( '%d' )
            );
        }
    }

    private function get_class_quiz_map( array $class_ids ) {
        global $wpdb;

        $class_ids = array_values( array_filter( array_map( 'absint', $class_ids ) ) );

        if ( empty( $class_ids ) ) {
            return array();
        }

        $quiz_classes_table = $wpdb->prefix . 'teqcidb_quiz_classes';
        $placeholders       = implode( ',', array_fill( 0, count( $class_ids ), '%d' ) );
        $query              = "SELECT class_id, quiz_id FROM $quiz_classes_table WHERE class_id IN ($placeholders) ORDER BY quiz_id DESC";
        $rows               = $wpdb->get_results( $wpdb->prepare( $query, $class_ids ), ARRAY_A );
        $map                = array();

        if ( is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                $class_id = isset( $row['class_id'] ) ? absint( $row['class_id'] ) : 0;
                $quiz_id  = isset( $row['quiz_id'] ) ? absint( $row['quiz_id'] ) : 0;

                if ( $class_id <= 0 || $quiz_id <= 0 || isset( $map[ $class_id ] ) ) {
                    continue;
                }

                $map[ $class_id ] = $quiz_id;
            }
        }

        return $map;
    }

    public function save_quiz_question() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
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
            wp_send_json_error(
                array(
                    'message' => __( 'Saving this question type is coming soon.', 'teqcidb' ),
                )
            );
        }

        if ( 'true_false' === $question_type && 'true' !== $correct && 'false' !== $correct ) {
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
                    wp_send_json_error(
                        array(
                            'message' => __( 'Set exactly one answer option to True for a multiple choice question.', 'teqcidb' ),
                        )
                    );
                }
            }

            $choices_json = wp_json_encode( $choices );

            if ( ! $choices_json ) {
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
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to save the quiz question. Please try again.', 'teqcidb' ),
                )
            );
        }

        wp_send_json_success(
            array(
                'message' => __( 'Question saved.', 'teqcidb' ),
            )
        );
    }

    public function reset_failed_quiz_attempt() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to reset this quiz attempt.', 'teqcidb' ),
                )
            );
        }

        $quiz_id  = isset( $_POST['quiz_id'] ) ? absint( wp_unslash( $_POST['quiz_id'] ) ) : 0;
        $class_id = isset( $_POST['class_id'] ) ? absint( wp_unslash( $_POST['class_id'] ) ) : 0;
        $user_id  = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;

        if ( $quiz_id <= 0 || $class_id <= 0 || $user_id <= 0 ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid quiz attempt selection.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $classes_table      = $wpdb->prefix . 'teqcidb_classes';
        $attempts_table     = $wpdb->prefix . 'teqcidb_quiz_attempts';
        $answers_table      = $wpdb->prefix . 'teqcidb_quiz_answers';
        $answer_items_table = $wpdb->prefix . 'teqcidb_quiz_answer_items';

        $class_type = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT classtype FROM $classes_table WHERE id = %d LIMIT 1",
                $class_id
            )
        );

        if ( 'refresher' !== sanitize_key( (string) $class_type ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Only refresher quiz attempts can be reset.', 'teqcidb' ),
                )
            );
        }

        $attempt_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM $attempts_table WHERE quiz_id = %d AND class_id = %d AND user_id = %d",
                $quiz_id,
                $class_id,
                $user_id
            )
        );

        $attempt_ids = array_values( array_filter( array_map( 'absint', is_array( $attempt_ids ) ? $attempt_ids : array() ) ) );

        if ( empty( $attempt_ids ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'No matching quiz attempts were found to reset.', 'teqcidb' ),
                )
            );
        }

        $placeholders = implode( ',', array_fill( 0, count( $attempt_ids ), '%d' ) );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $answer_items_table WHERE attempt_id IN ($placeholders)",
                $attempt_ids
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $answers_table WHERE attempt_id IN ($placeholders)",
                $attempt_ids
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $attempts_table WHERE id IN ($placeholders)",
                $attempt_ids
            )
        );

        wp_send_json_success(
            array(
                'message' => __( 'Quiz attempt reset. The student can now retake this quiz.', 'teqcidb' ),
            )
        );
    }


    public function delete_quiz_question() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to delete quiz questions.', 'teqcidb' ),
                )
            );
        }

        $quiz_id     = isset( $_POST['quiz_id'] ) ? absint( wp_unslash( $_POST['quiz_id'] ) ) : 0;
        $question_id = isset( $_POST['question_id'] ) ? absint( wp_unslash( $_POST['question_id'] ) ) : 0;

        if ( $quiz_id <= 0 || $question_id <= 0 ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid quiz question selection.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $table = $wpdb->prefix . 'teqcidb_quiz_questions';
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE id = %d AND quiz_id = %d LIMIT 1",
                $question_id,
                $quiz_id
            )
        );

        if ( ! $exists ) {
            wp_send_json_error(
                array(
                    'message' => __( 'That question no longer exists.', 'teqcidb' ),
                )
            );
        }

        $deleted = $wpdb->delete(
            $table,
            array(
                'id'      => $question_id,
                'quiz_id' => $quiz_id,
            ),
            array( '%d', '%d' )
        );

        if ( false === $deleted ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to delete the quiz question. Please try again.', 'teqcidb' ),
                )
            );
        }

        wp_send_json_success(
            array(
                'message' => __( 'Question deleted. Reloading…', 'teqcidb' ),
                'quiz_id' => $quiz_id,
            )
        );
    }


    public function create_quiz_question() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to create quiz questions.', 'teqcidb' ),
                )
            );
        }

        $quiz_id       = isset( $_POST['quiz_id'] ) ? absint( wp_unslash( $_POST['quiz_id'] ) ) : 0;
        $question_type = isset( $_POST['question_type'] ) ? sanitize_key( wp_unslash( $_POST['question_type'] ) ) : '';
        $prompt        = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
        $correct       = isset( $_POST['correct'] ) ? sanitize_key( wp_unslash( $_POST['correct'] ) ) : '';

        if ( $quiz_id <= 0 ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid quiz selection.', 'teqcidb' ),
                )
            );
        }

        if ( ! in_array( $question_type, array( 'true_false', 'multi_select', 'multiple_choice' ), true ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Choose a valid question type before saving.', 'teqcidb' ),
                )
            );
        }

        if ( '' === trim( $prompt ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Enter the question text before saving.', 'teqcidb' ),
                )
            );
        }

        $option_ids_raw     = isset( $_POST['option_ids'] ) ? (array) wp_unslash( $_POST['option_ids'] ) : array();
        $option_labels_raw  = isset( $_POST['option_labels'] ) ? (array) wp_unslash( $_POST['option_labels'] ) : array();
        $option_correct_raw = isset( $_POST['option_correct'] ) ? (array) wp_unslash( $_POST['option_correct'] ) : array();

        $choices_json = '';

        if ( 'true_false' === $question_type ) {
            if ( 'true' !== $correct && 'false' !== $correct ) {
                wp_send_json_error(
                    array(
                        'message' => __( 'Select True or False before saving this question.', 'teqcidb' ),
                    )
                );
            }

            $choices_json = wp_json_encode(
                array(
                    array(
                        'correct' => $correct,
                    ),
                )
            );
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
                    wp_send_json_error(
                        array(
                            'message' => __( 'Set exactly one answer option to True for a multiple choice question.', 'teqcidb' ),
                        )
                    );
                }
            }

            $choices_json = wp_json_encode( $choices );
        }

        if ( ! $choices_json ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to encode question data for saving.', 'teqcidb' ),
                )
            );
        }

        global $wpdb;

        $quiz_table = $wpdb->prefix . 'teqcidb_quizzes';
        $quiz_exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $quiz_table WHERE id = %d LIMIT 1", $quiz_id ) );

        if ( ! $quiz_exists ) {
            wp_send_json_error(
                array(
                    'message' => __( 'The selected quiz no longer exists.', 'teqcidb' ),
                )
            );
        }

        $table = $wpdb->prefix . 'teqcidb_quiz_questions';
        $max_sort_order = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(sort_order) FROM $table WHERE quiz_id = %d", $quiz_id ) );
        $next_sort_order = absint( $max_sort_order ) + 1;

        $inserted = $wpdb->insert(
            $table,
            array(
                'quiz_id'      => $quiz_id,
                'type'         => $question_type,
                'prompt'       => $prompt,
                'choices_json' => $choices_json,
                'sort_order'   => $next_sort_order,
                'updated_at'   => current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%s', '%s', '%d', '%s' )
        );

        if ( false === $inserted ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to create the quiz question. Please try again.', 'teqcidb' ),
                )
            );
        }

        $question_id = absint( $wpdb->insert_id );

        wp_send_json_success(
            array(
                'message'     => __( 'Question created. Reloading…', 'teqcidb' ),
                'quiz_id'     => $quiz_id,
                'question_id' => $question_id,
            )
        );
    }

    public function search_students() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to search students.', 'teqcidb' ),
                )
            );
        }

        $term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
        $term = trim( $term );

        if ( strlen( $term ) < 2 ) {
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
                wp_send_json_error(
                    array(
                        'message' => __( 'Settings could not be saved. Please try again.', 'teqcidb' ),
                    )
                );
            }
        }

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
            wp_send_json_error(
                array(
                    'message' => __( 'Select at least one supported upload type.', 'teqcidb' ),
                )
            );
        }

        if ( count( $selected_types ) > 1 ) {
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
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to modify error logs.', 'teqcidb' ),
                )
            );
        }

        $scope = isset( $_POST['scope'] ) ? TEQCIDB_Error_Log_Helper::normalize_scope( wp_unslash( $_POST['scope'] ) ) : '';

        if ( '' === $scope ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unknown log scope.', 'teqcidb' ),
                )
            );
        }

        $cleared = TEQCIDB_Error_Log_Helper::clear_log( $scope );

        if ( ! $cleared ) {
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
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to download error logs.', 'teqcidb' ),
                )
            );
        }

        $scope = isset( $_POST['scope'] ) ? TEQCIDB_Error_Log_Helper::normalize_scope( wp_unslash( $_POST['scope'] ) ) : '';

        if ( '' === $scope ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unknown log scope.', 'teqcidb' ),
                )
            );
        }

        $filename = TEQCIDB_Error_Log_Helper::get_download_filename( $scope );

        if ( '' === $filename ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to prepare the download filename.', 'teqcidb' ),
                )
            );
        }

        $contents = TEQCIDB_Error_Log_Helper::get_log_contents( $scope );

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

        $entities        = array();
        $class_quiz_map = array();

        if ( $total > 0 ) {
            $select_query = "SELECT * FROM $table";

            if ( $where_sql ) {
                $select_query .= ' ' . $where_sql;
            }

            $select_query .= ' ORDER BY id DESC, classstartdate DESC, classname ASC LIMIT %d OFFSET %d';

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
                $class_quiz_map = $this->get_class_quiz_map( wp_list_pluck( $entities, 'id' ) );

                foreach ( $entities as &$entity ) {
                    $class_id = isset( $entity['id'] ) ? absint( $entity['id'] ) : 0;
                    $entity['quiz_id'] = isset( $class_quiz_map[ $class_id ] ) ? (string) $class_quiz_map[ $class_id ] : '';
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


    public function read_class_registered_students() {
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        $class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;

        if ( $class_id <= 0 ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid class selection.', 'teqcidb' ),
                )
            );
        }

        $page     = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
        $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 40;

        if ( $per_page <= 0 ) {
            $per_page = 40;
        }

        $per_page = min( $per_page, 100 );

        $class_entity = $this->get_class_entity_for_registered_students( $class_id );

        if ( empty( $class_entity ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Class not found.', 'teqcidb' ),
                )
            );
        }

        $registered_data = $this->get_registered_students_page_for_class( $class_entity, $page, $per_page );
        $total           = isset( $registered_data['total'] ) ? absint( $registered_data['total'] ) : 0;
        $students        = isset( $registered_data['students'] ) && is_array( $registered_data['students'] ) ? $registered_data['students'] : array();
        $total_pages     = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;

        if ( $total_pages < 1 ) {
            $total_pages = 1;
        }

        if ( $page > $total_pages ) {
            $page = $total_pages;
        }

        $loaded_count = ( $page - 1 ) * $per_page + count( $students );

        wp_send_json_success(
            array(
                'students'     => $students,
                'page'         => $page,
                'per_page'     => $per_page,
                'total'        => $total,
                'total_pages'  => $total_pages,
                'loaded_count' => min( $loaded_count, $total ),
                'has_more'     => $loaded_count < $total,
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

    public function save_email_template() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        $template_id = isset( $_POST['template_id'] ) ? sanitize_key( wp_unslash( $_POST['template_id'] ) ) : '';

        if ( '' === $template_id ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid template selection.', 'teqcidb' ),
                )
            );
        }

        $from_name = isset( $_POST['from_name'] ) ? TEQCIDB_Email_Template_Helper::sanitize_from_name( wp_unslash( $_POST['from_name'] ) ) : '';
        $from_email = isset( $_POST['from_email'] ) ? TEQCIDB_Email_Template_Helper::sanitize_from_email( wp_unslash( $_POST['from_email'] ) ) : '';
        $cc = isset( $_POST['cc'] ) ? TEQCIDB_Email_Template_Helper::sanitize_recipient_list( wp_unslash( $_POST['cc'] ) ) : '';
        $bcc = isset( $_POST['bcc'] ) ? TEQCIDB_Email_Template_Helper::sanitize_recipient_list( wp_unslash( $_POST['bcc'] ) ) : '';
        $subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
        $body    = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
        $sms     = isset( $_POST['sms'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sms'] ) ) : '';

        TEQCIDB_Email_Template_Helper::update_template_settings(
            $template_id,
            array(
                'from_name'  => $from_name,
                'from_email' => $from_email,
                'cc'         => $cc,
                'bcc'        => $bcc,
                'subject'    => $subject,
                'body'       => $body,
                'sms'        => $sms,
            )
        );

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
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        $template_id = isset( $_POST['template_id'] ) ? sanitize_key( wp_unslash( $_POST['template_id'] ) ) : '';

        if ( '' === $template_id ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid template selection.', 'teqcidb' ),
                )
            );
        }

        $to_email = isset( $_POST['to_email'] ) ? sanitize_email( wp_unslash( $_POST['to_email'] ) ) : '';

        if ( ! $to_email || ! is_email( $to_email ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Please provide a valid email address.', 'teqcidb' ),
                )
            );
        }

        $from_name = isset( $_POST['from_name'] ) ? TEQCIDB_Email_Template_Helper::sanitize_from_name( wp_unslash( $_POST['from_name'] ) ) : '';
        $from_email = isset( $_POST['from_email'] ) ? TEQCIDB_Email_Template_Helper::sanitize_from_email( wp_unslash( $_POST['from_email'] ) ) : '';
        $cc = isset( $_POST['cc'] ) ? TEQCIDB_Email_Template_Helper::sanitize_recipient_list( wp_unslash( $_POST['cc'] ) ) : '';
        $bcc = isset( $_POST['bcc'] ) ? TEQCIDB_Email_Template_Helper::sanitize_recipient_list( wp_unslash( $_POST['bcc'] ) ) : '';
        $subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
        $body    = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';

        $stored_settings = TEQCIDB_Email_Template_Helper::get_template_settings( $template_id );

        if ( '' === $from_name && isset( $stored_settings['from_name'] ) ) {
            $from_name = TEQCIDB_Email_Template_Helper::sanitize_from_name( $stored_settings['from_name'] );
        }

        if ( '' === $from_email && isset( $stored_settings['from_email'] ) ) {
            $from_email = TEQCIDB_Email_Template_Helper::sanitize_from_email( $stored_settings['from_email'] );
        }

        if ( '' === $cc && isset( $stored_settings['cc'] ) ) {
            $cc = TEQCIDB_Email_Template_Helper::sanitize_recipient_list( $stored_settings['cc'] );
        }

        if ( '' === $bcc && isset( $stored_settings['bcc'] ) ) {
            $bcc = TEQCIDB_Email_Template_Helper::sanitize_recipient_list( $stored_settings['bcc'] );
        }

        $from_name  = TEQCIDB_Email_Template_Helper::resolve_from_name( $from_name );
        $from_email = TEQCIDB_Email_Template_Helper::resolve_from_email( $from_email );

        $tokens = TEQCIDB_Student_Helper::get_latest_preview_data();

        if ( ! empty( $tokens ) ) {
            $subject = $this->replace_template_tokens( $subject, $tokens );
            $body    = $this->replace_template_tokens( $body, $tokens );
        }

        $rendered_body = wp_kses_post( $body );

        if ( '' !== $rendered_body ) {
            $rendered_body = nl2br( $rendered_body );
        }

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        $from_header = TEQCIDB_Email_Template_Helper::build_from_header( $from_name, $from_email );

        if ( $from_header ) {
            $headers[] = $from_header;
        }

        if ( '' !== $cc ) {
            $headers[] = 'Cc: ' . $cc;
        }

        if ( '' !== $bcc ) {
            $headers[] = 'Bcc: ' . $bcc;
        }

        $sent    = wp_mail( $to_email, $subject, $rendered_body, $headers );


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

    /**
     * Send the Student Self-Registration (Initial Online) template after a qualifying self-registration payment.
     *
     * @param string $to_email Recipient email address.
     * @param array  $student  Student row data.
     * @param array  $class    Class row data.
     * @param string $total_paid Formatted transaction total.
     */
    private function maybe_send_student_self_initial_online_email( $to_email, array $student, array $class, $total_paid ) {
        $this->maybe_send_student_self_email_for_class_type_and_format(
            $to_email,
            $student,
            $class,
            $total_paid,
            'initial',
            array( 'virtual', 'online' ),
            'teqcidb-email-student-self-initial-online'
        );
    }

    /**
     * Send the Student Self-Registration (Refresher Online) template after a qualifying self-registration payment.
     *
     * @param string $to_email Recipient email address.
     * @param array  $student  Student row data.
     * @param array  $class    Class row data.
     * @param string $total_paid Formatted transaction total.
     */
    private function maybe_send_student_self_refresher_online_email( $to_email, array $student, array $class, $total_paid ) {
        $this->maybe_send_student_self_email_for_class_type_and_format(
            $to_email,
            $student,
            $class,
            $total_paid,
            'refresher',
            array( 'virtual', 'online' ),
            'teqcidb-email-student-self-refresher-online'
        );
    }


    /**
     * Send the Student Self-Registration (Initial In-Person) template after a qualifying self-registration payment.
     *
     * @param string $to_email Recipient email address.
     * @param array  $student  Student row data.
     * @param array  $class    Class row data.
     * @param string $total_paid Formatted transaction total.
     */
    private function maybe_send_student_self_initial_in_person_email( $to_email, array $student, array $class, $total_paid ) {
        $this->maybe_send_student_self_email_for_class_type_and_format(
            $to_email,
            $student,
            $class,
            $total_paid,
            'initial',
            array( 'in_person', 'inperson' ),
            'teqcidb-email-student-self-initial-in-person'
        );
    }


    /**
     * Send the Student Self-Registration (Refresher In-Person) template after a qualifying self-registration payment.
     *
     * @param string $to_email Recipient email address.
     * @param array  $student  Student row data.
     * @param array  $class    Class row data.
     * @param string $total_paid Formatted transaction total.
     */
    private function maybe_send_student_self_refresher_in_person_email( $to_email, array $student, array $class, $total_paid ) {
        $this->maybe_send_student_self_email_for_class_type_and_format(
            $to_email,
            $student,
            $class,
            $total_paid,
            'refresher',
            array( 'in_person', 'inperson' ),
            'teqcidb-email-student-self-refresher-in-person'
        );
    }

    /**
     * Send a self-registration template after a qualifying payment by class type and class format.
     *
     * @param string $to_email            Recipient email address.
     * @param array  $student             Student row data.
     * @param array  $class               Class row data.
     * @param string $total_paid          Formatted transaction total.
     * @param string $required_class_type Required class type slug.
     * @param string $template_id         Template ID to send.
     */
    private function maybe_send_student_self_email_for_class_type_and_format( $to_email, array $student, array $class, $total_paid, $required_class_type, array $required_formats, $template_id ) {
        $normalized_type   = strtolower( sanitize_text_field( isset( $class['classtype'] ) ? (string) $class['classtype'] : '' ) );
        $normalized_format = strtolower( sanitize_text_field( isset( $class['classformat'] ) ? (string) $class['classformat'] : '' ) );

        if ( strtolower( sanitize_key( (string) $required_class_type ) ) !== $normalized_type || ! in_array( $normalized_format, $required_formats, true ) ) {
            return;
        }

        $recipient = sanitize_email( (string) $to_email );

        if ( '' === $recipient || ! is_email( $recipient ) ) {
            return;
        }

        $template_id      = sanitize_key( (string) $template_id );
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

        $tokens  = $this->build_registration_email_tokens( $student, $class, $total_paid );
        $subject = $this->replace_template_tokens( $subject_template, $tokens );
        $body    = $this->replace_template_tokens( $body_template, $tokens );

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
                'context'        => __( 'Automatic self-registration email', 'teqcidb' ),
                'triggered_by'   => __( 'Student registration payment success', 'teqcidb' ),
            )
        );
    }


    /**
     * Send the Representative Registration (Initial Online - Student) template to each selected student after a qualifying representative checkout.
     *
     * @param array  $representative Representative student row data.
     * @param array  $class          Class row data.
     * @param string $total_paid     Formatted transaction total.
     * @param array  $selected_students Selected student rows for this payment.
     */
    private function maybe_send_representative_initial_online_student_emails( array $representative, array $class, $total_paid, array $selected_students ) {
        $normalized_type   = strtolower( sanitize_text_field( isset( $class['classtype'] ) ? (string) $class['classtype'] : '' ) );
        $normalized_format = strtolower( sanitize_text_field( isset( $class['classformat'] ) ? (string) $class['classformat'] : '' ) );

        if ( 'initial' !== $normalized_type || ! in_array( $normalized_format, array( 'virtual', 'online' ), true ) ) {
            return;
        }

        if ( empty( $selected_students ) ) {
            return;
        }

        $template_id      = 'teqcidb-email-rep-initial-online-student';
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

        foreach ( $selected_students as $selected_student ) {
            if ( ! is_array( $selected_student ) ) {
                continue;
            }

            $recipient = isset( $selected_student['email'] ) ? sanitize_email( (string) $selected_student['email'] ) : '';

            if ( '' === $recipient || ! is_email( $recipient ) ) {
                continue;
            }

            $tokens  = $this->build_registration_email_tokens( $selected_student, $class, $total_paid, $representative );
            $subject = $this->replace_template_tokens( $subject_template, $tokens );
            $body    = $this->replace_template_tokens( $body_template, $tokens );

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
                continue;
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
                    'context'        => __( 'Automatic representative registration student email', 'teqcidb' ),
                    'triggered_by'   => __( 'Representative registration payment success', 'teqcidb' ),
                )
            );
        }
    }




    /**
     * Send the Representative Registration (Initial In-Person - Student) template to each selected student after a qualifying representative checkout.
     *
     * @param array  $representative Representative student row data.
     * @param array  $class          Class row data.
     * @param string $total_paid     Formatted transaction total.
     * @param array  $selected_students Selected student rows for this payment.
     */
    private function maybe_send_representative_initial_in_person_student_emails( array $representative, array $class, $total_paid, array $selected_students ) {
        $normalized_type   = strtolower( sanitize_text_field( isset( $class['classtype'] ) ? (string) $class['classtype'] : '' ) );
        $normalized_format = strtolower( sanitize_text_field( isset( $class['classformat'] ) ? (string) $class['classformat'] : '' ) );

        if ( 'initial' !== $normalized_type || ! in_array( $normalized_format, array( 'in_person', 'inperson' ), true ) ) {
            return;
        }

        if ( empty( $selected_students ) ) {
            return;
        }

        $template_id      = 'teqcidb-email-rep-initial-in-person-student';
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

        foreach ( $selected_students as $selected_student ) {
            if ( ! is_array( $selected_student ) ) {
                continue;
            }

            $recipient = isset( $selected_student['email'] ) ? sanitize_email( (string) $selected_student['email'] ) : '';

            if ( '' === $recipient || ! is_email( $recipient ) ) {
                continue;
            }

            $tokens  = $this->build_registration_email_tokens( $selected_student, $class, $total_paid, $representative );
            $subject = $this->replace_template_tokens( $subject_template, $tokens );
            $body    = $this->replace_template_tokens( $body_template, $tokens );

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
                continue;
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
                    'context'        => __( 'Automatic representative registration student email', 'teqcidb' ),
                    'triggered_by'   => __( 'Representative registration payment success', 'teqcidb' ),
                )
            );
        }
    }


    /**
     * Send the Representative Registration (Refresher In-Person - Student) template to each selected student after a qualifying representative checkout.
     *
     * @param array  $representative Representative student row data.
     * @param array  $class          Class row data.
     * @param string $total_paid     Formatted transaction total.
     * @param array  $selected_students Selected student rows for this payment.
     */
    private function maybe_send_representative_refresher_in_person_student_emails( array $representative, array $class, $total_paid, array $selected_students ) {
        $normalized_type   = strtolower( sanitize_text_field( isset( $class['classtype'] ) ? (string) $class['classtype'] : '' ) );
        $normalized_format = strtolower( sanitize_text_field( isset( $class['classformat'] ) ? (string) $class['classformat'] : '' ) );

        if ( 'refresher' !== $normalized_type || ! in_array( $normalized_format, array( 'in_person', 'inperson' ), true ) ) {
            return;
        }

        if ( empty( $selected_students ) ) {
            return;
        }

        $template_id      = 'teqcidb-email-rep-refresher-in-person-student';
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

        foreach ( $selected_students as $selected_student ) {
            if ( ! is_array( $selected_student ) ) {
                continue;
            }

            $recipient = isset( $selected_student['email'] ) ? sanitize_email( (string) $selected_student['email'] ) : '';

            if ( '' === $recipient || ! is_email( $recipient ) ) {
                continue;
            }

            $tokens  = $this->build_registration_email_tokens( $selected_student, $class, $total_paid, $representative );
            $subject = $this->replace_template_tokens( $subject_template, $tokens );
            $body    = $this->replace_template_tokens( $body_template, $tokens );

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
                continue;
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
                    'context'        => __( 'Automatic representative registration student email', 'teqcidb' ),
                    'triggered_by'   => __( 'Representative registration payment success', 'teqcidb' ),
                )
            );
        }
    }

    /**
     * Send the Representative Registration (Refresher Online - Student) template to each selected student after a qualifying representative checkout.
     *
     * @param array  $representative Representative student row data.
     * @param array  $class          Class row data.
     * @param string $total_paid     Formatted transaction total.
     * @param array  $selected_students Selected student rows for this payment.
     */
    private function maybe_send_representative_refresher_online_student_emails( array $representative, array $class, $total_paid, array $selected_students ) {
        $normalized_type   = strtolower( sanitize_text_field( isset( $class['classtype'] ) ? (string) $class['classtype'] : '' ) );
        $normalized_format = strtolower( sanitize_text_field( isset( $class['classformat'] ) ? (string) $class['classformat'] : '' ) );

        if ( 'refresher' !== $normalized_type || ! in_array( $normalized_format, array( 'virtual', 'online' ), true ) ) {
            return;
        }

        if ( empty( $selected_students ) ) {
            return;
        }

        $template_id      = 'teqcidb-email-rep-refresher-online-student';
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

        foreach ( $selected_students as $selected_student ) {
            if ( ! is_array( $selected_student ) ) {
                continue;
            }

            $recipient = isset( $selected_student['email'] ) ? sanitize_email( (string) $selected_student['email'] ) : '';

            if ( '' === $recipient || ! is_email( $recipient ) ) {
                continue;
            }

            $tokens  = $this->build_registration_email_tokens( $selected_student, $class, $total_paid, $representative );
            $subject = $this->replace_template_tokens( $subject_template, $tokens );
            $body    = $this->replace_template_tokens( $body_template, $tokens );

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
                continue;
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
                    'context'        => __( 'Automatic representative registration student email', 'teqcidb' ),
                    'triggered_by'   => __( 'Representative registration payment success', 'teqcidb' ),
                )
            );
        }
    }

    /**
     * Send the Representative Registration (Initial Online - Representative) template to the logged-in representative after a qualifying checkout.
     *
     * @param array  $representative   Representative student row data.
     * @param array  $class            Class row data.
     * @param string $total_paid       Formatted transaction total.
     * @param array  $selected_students Selected student rows for this payment.
     */
    private function maybe_send_representative_initial_online_representative_email( array $representative, array $class, $total_paid, array $selected_students ) {
        $normalized_type   = strtolower( sanitize_text_field( isset( $class['classtype'] ) ? (string) $class['classtype'] : '' ) );
        $normalized_format = strtolower( sanitize_text_field( isset( $class['classformat'] ) ? (string) $class['classformat'] : '' ) );

        if ( 'initial' !== $normalized_type || ! in_array( $normalized_format, array( 'virtual', 'online' ), true ) ) {
            return;
        }

        $recipient = isset( $representative['email'] ) ? sanitize_email( (string) $representative['email'] ) : '';

        if ( '' === $recipient || ! is_email( $recipient ) ) {
            return;
        }

        $template_id      = 'teqcidb-email-rep-initial-online-representative';
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

        $tokens = $this->build_registration_email_tokens( $representative, $class, $total_paid, $representative );
        $tokens['individuals_registered'] = $this->build_individuals_registered_html_from_students( $selected_students );

        $subject = $this->replace_template_tokens( $subject_template, $tokens );
        $body    = $this->replace_template_tokens( $body_template, $tokens );

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
                'context'        => __( 'Automatic representative registration representative email', 'teqcidb' ),
                'triggered_by'   => __( 'Representative registration payment success', 'teqcidb' ),
            )
        );
    }


    /**
     * Send the Representative Registration (Refresher Online - Representative) template to the logged-in representative after a qualifying checkout.
     *
     * @param array  $representative   Representative student row data.
     * @param array  $class            Class row data.
     * @param string $total_paid       Formatted transaction total.
     * @param array  $selected_students Selected student rows for this payment.
     */
    private function maybe_send_representative_refresher_online_representative_email( array $representative, array $class, $total_paid, array $selected_students ) {
        $normalized_type   = strtolower( sanitize_text_field( isset( $class['classtype'] ) ? (string) $class['classtype'] : '' ) );
        $normalized_format = strtolower( sanitize_text_field( isset( $class['classformat'] ) ? (string) $class['classformat'] : '' ) );

        if ( 'refresher' !== $normalized_type || ! in_array( $normalized_format, array( 'virtual', 'online' ), true ) ) {
            return;
        }

        $recipient = isset( $representative['email'] ) ? sanitize_email( (string) $representative['email'] ) : '';

        if ( '' === $recipient || ! is_email( $recipient ) ) {
            return;
        }

        $template_id      = 'teqcidb-email-rep-refresher-online-representative';
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

        $tokens = $this->build_registration_email_tokens( $representative, $class, $total_paid, $representative );
        $tokens['individuals_registered'] = $this->build_individuals_registered_html_from_students( $selected_students );

        $subject = $this->replace_template_tokens( $subject_template, $tokens );
        $body    = $this->replace_template_tokens( $body_template, $tokens );

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
                'context'        => __( 'Automatic representative registration representative email', 'teqcidb' ),
                'triggered_by'   => __( 'Representative registration payment success', 'teqcidb' ),
            )
        );
    }


    /**
     * Send the Representative Registration (Initial In-Person - Representative) template to the logged-in representative after a qualifying checkout.
     *
     * @param array  $representative   Representative student row data.
     * @param array  $class            Class row data.
     * @param string $total_paid       Formatted transaction total.
     * @param array  $selected_students Selected student rows for this payment.
     */
    private function maybe_send_representative_initial_in_person_representative_email( array $representative, array $class, $total_paid, array $selected_students ) {
        $normalized_type   = strtolower( sanitize_text_field( isset( $class['classtype'] ) ? (string) $class['classtype'] : '' ) );
        $normalized_format = strtolower( sanitize_text_field( isset( $class['classformat'] ) ? (string) $class['classformat'] : '' ) );

        if ( 'initial' !== $normalized_type || ! in_array( $normalized_format, array( 'in_person', 'inperson' ), true ) ) {
            return;
        }

        $recipient = isset( $representative['email'] ) ? sanitize_email( (string) $representative['email'] ) : '';

        if ( '' === $recipient || ! is_email( $recipient ) ) {
            return;
        }

        $template_id      = 'teqcidb-email-rep-initial-in-person-representative';
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

        $tokens = $this->build_registration_email_tokens( $representative, $class, $total_paid, $representative );
        $tokens['individuals_registered'] = $this->build_individuals_registered_html_from_students( $selected_students );

        $subject = $this->replace_template_tokens( $subject_template, $tokens );
        $body    = $this->replace_template_tokens( $body_template, $tokens );

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
                'context'        => __( 'Automatic representative registration representative email', 'teqcidb' ),
                'triggered_by'   => __( 'Representative registration payment success', 'teqcidb' ),
            )
        );
    }


    /**
     * Send the Representative Registration (Refresher In-Person - Representative) template to the logged-in representative after a qualifying checkout.
     *
     * @param array  $representative   Representative student row data.
     * @param array  $class            Class row data.
     * @param string $total_paid       Formatted transaction total.
     * @param array  $selected_students Selected student rows for this payment.
     */
    private function maybe_send_representative_refresher_in_person_representative_email( array $representative, array $class, $total_paid, array $selected_students ) {
        $normalized_type   = strtolower( sanitize_text_field( isset( $class['classtype'] ) ? (string) $class['classtype'] : '' ) );
        $normalized_format = strtolower( sanitize_text_field( isset( $class['classformat'] ) ? (string) $class['classformat'] : '' ) );

        if ( 'refresher' !== $normalized_type || ! in_array( $normalized_format, array( 'in_person', 'inperson' ), true ) ) {
            return;
        }

        $recipient = isset( $representative['email'] ) ? sanitize_email( (string) $representative['email'] ) : '';

        if ( '' === $recipient || ! is_email( $recipient ) ) {
            return;
        }

        $template_id      = 'teqcidb-email-rep-refresher-in-person-representative';
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

        $tokens = $this->build_registration_email_tokens( $representative, $class, $total_paid, $representative );
        $tokens['individuals_registered'] = $this->build_individuals_registered_html_from_students( $selected_students );

        $subject = $this->replace_template_tokens( $subject_template, $tokens );
        $body    = $this->replace_template_tokens( $body_template, $tokens );

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
                'context'        => __( 'Automatic representative registration representative email', 'teqcidb' ),
                'triggered_by'   => __( 'Representative registration payment success', 'teqcidb' ),
            )
        );
    }

    /**
     * Build an email-safe unordered list of selected students for representative registration templates.
     *
     * @param array $selected_students Selected student rows for a representative transaction.
     *
     * @return string
     */
    private function build_individuals_registered_html_from_students( array $selected_students ) {
        if ( empty( $selected_students ) ) {
            return '';
        }

        $items = array();

        foreach ( $selected_students as $selected_student ) {
            if ( ! is_array( $selected_student ) ) {
                continue;
            }

            $first_name = isset( $selected_student['first_name'] ) ? sanitize_text_field( (string) $selected_student['first_name'] ) : '';
            $last_name  = isset( $selected_student['last_name'] ) ? sanitize_text_field( (string) $selected_student['last_name'] ) : '';
            $email      = isset( $selected_student['email'] ) ? sanitize_email( (string) $selected_student['email'] ) : '';
            $full_name  = trim( $first_name . ' ' . $last_name );

            if ( '' === $full_name && '' === $email ) {
                continue;
            }

            $label = '';

            if ( '' !== $full_name && '' !== $email ) {
                $label = sprintf( '%1$s (%2$s)', $full_name, $email );
            } elseif ( '' !== $full_name ) {
                $label = $full_name;
            } else {
                $label = $email;
            }

            $items[] = '<li>' . esc_html( $label ) . '</li>';
        }

        if ( empty( $items ) ) {
            return '';
        }

        return '<ul>' . implode( '', $items ) . '</ul>';
    }

    /**
     * Build merge-token values for registration confirmation emails.
     *
     * @param array  $student    Student row data.
     * @param array  $class      Class row data.
     * @param string $total_paid Formatted transaction total.
     *
     * @return array
     */
    private function build_registration_email_tokens( array $student, array $class, $total_paid, array $representative = array() ) {
        $tokens = TEQCIDB_Student_Helper::get_latest_preview_data();

        $tokens['student_first_name'] = isset( $student['first_name'] ) ? sanitize_text_field( (string) $student['first_name'] ) : ( isset( $tokens['student_first_name'] ) ? $tokens['student_first_name'] : '' );
        $tokens['student_last_name']  = isset( $student['last_name'] ) ? sanitize_text_field( (string) $student['last_name'] ) : ( isset( $tokens['student_last_name'] ) ? $tokens['student_last_name'] : '' );

        $representative_first_name = isset( $representative['first_name'] ) ? sanitize_text_field( (string) $representative['first_name'] ) : '';
        $representative_last_name  = isset( $representative['last_name'] ) ? sanitize_text_field( (string) $representative['last_name'] ) : '';

        if ( '' === $representative_first_name ) {
            $representative_first_name = isset( $student['first_name'] ) ? sanitize_text_field( (string) $student['first_name'] ) : ( isset( $tokens['representative_first_name'] ) ? $tokens['representative_first_name'] : '' );
        }

        if ( '' === $representative_last_name ) {
            $representative_last_name = isset( $student['last_name'] ) ? sanitize_text_field( (string) $student['last_name'] ) : ( isset( $tokens['representative_last_name'] ) ? $tokens['representative_last_name'] : '' );
        }

        $tokens['representative_first_name'] = $representative_first_name;
        $tokens['representative_last_name']  = $representative_last_name;
        $tokens['student_email']           = isset( $student['email'] ) ? sanitize_email( (string) $student['email'] ) : ( isset( $tokens['student_email'] ) ? $tokens['student_email'] : '' );
        $tokens['student_company']    = isset( $student['company'] ) ? sanitize_text_field( (string) $student['company'] ) : ( isset( $tokens['student_company'] ) ? $tokens['student_company'] : '' );
        $tokens['student_phone_cell'] = isset( $student['phone_cell'] ) ? sanitize_text_field( (string) $student['phone_cell'] ) : ( isset( $tokens['student_phone_cell'] ) ? $tokens['student_phone_cell'] : '' );

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

        $tokens['class_name']      = isset( $class['classname'] ) ? sanitize_text_field( (string) $class['classname'] ) : ( isset( $tokens['class_name'] ) ? $tokens['class_name'] : '' );
        $tokens['class_type']      = isset( $class['classtype'] ) ? ucwords( str_replace( array( '_', '-' ), ' ', sanitize_text_field( (string) $class['classtype'] ) ) ) : ( isset( $tokens['class_type'] ) ? $tokens['class_type'] : '' );
        $tokens['class_date']      = isset( $class['classstartdate'] ) ? $this->format_date_token_value( $class['classstartdate'] ) : ( isset( $tokens['class_date'] ) ? $tokens['class_date'] : '' );
        $tokens['class_time']      = isset( $class['classstarttime'] ) ? $this->format_time_token_value( $class['classstarttime'] ) : ( isset( $tokens['class_time'] ) ? $tokens['class_time'] : '' );
        $tokens['class_page']      = isset( $class['classurl'] ) ? $this->format_email_template_class_url( $class['classurl'] ) : ( isset( $tokens['class_page'] ) ? $tokens['class_page'] : '' );
        $tokens['class_team_link'] = isset( $class['teamslink'] ) ? esc_url_raw( (string) $class['teamslink'] ) : ( isset( $tokens['class_team_link'] ) ? $tokens['class_team_link'] : '' );

        $transaction_total                          = (float) preg_replace( '/[^0-9.\-]/', '', (string) $total_paid );
        $tokens['class_cost_total_transaction']     = '$' . number_format( max( 0, $transaction_total ), 2 );
        $tokens['class_cost_student_self']          = $tokens['class_cost_total_transaction'];
        $tokens['class_cost_student_representative'] = isset( $class['classcost'] ) && is_numeric( preg_replace( '/[^0-9.\-]/', '', (string) $class['classcost'] ) )
            ? '$' . number_format( (float) preg_replace( '/[^0-9.\-]/', '', (string) $class['classcost'] ), 2 )
            : ( isset( $tokens['class_cost_student_representative'] ) ? $tokens['class_cost_student_representative'] : '' );

        return $tokens;
    }

    /**
     * Format a YYYY-mm-dd date value for token replacement.
     *
     * @param string $value Raw date value.
     *
     * @return string
     */
    private function format_date_token_value( $value ) {
        $value = sanitize_text_field( (string) $value );

        if ( '' === $value || '0000-00-00' === $value ) {
            return '';
        }

        $date = date_create( $value );

        return $date ? $date->format( 'm-d-Y' ) : '';
    }

    /**
     * Format a stored class time for token replacement.
     *
     * @param string $value Raw time value.
     *
     * @return string
     */
    private function format_time_token_value( $value ) {
        $value = sanitize_text_field( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        $timestamp = strtotime( $value );

        if ( false === $timestamp ) {
            return '';
        }

        return gmdate( 'g:i A', $timestamp );
    }


    private function format_email_template_class_url( $value ) {
        if ( ! is_scalar( $value ) ) {
            return '';
        }

        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '#^https?://#i', $value ) ) {
            return esc_url_raw( $value );
        }

        return esc_url_raw( home_url( '/' . ltrim( $value, '/' ) ) );
    }

    public function clear_email_log() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'You are not allowed to perform this action.', 'teqcidb' ),
                )
            );
        }

        if ( ! TEQCIDB_Email_Log_Helper::is_log_available() ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Email logging is unavailable. Check directory permissions and try again.', 'teqcidb' ),
                )
            );
        }

        $cleared = TEQCIDB_Email_Log_Helper::clear_log();


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

        $rows             = array();
        $length           = strlen( $normalized );
        $inside_quotes    = false;
        $paren_depth      = 0;
        $current_fragment = '';

        for ( $index = 0; $index < $length; $index++ ) {
            $character = $normalized[ $index ];

            if ( "'" === $character ) {
                if ( $this->is_legacy_quote_backslash_escaped( $normalized, $index ) ) {
                    if ( $paren_depth > 0 ) {
                        $current_fragment .= $character;
                    }

                    continue;
                }

                if ( $inside_quotes && ( $index + 1 ) < $length && "'" === $normalized[ $index + 1 ] ) {
                    if ( $paren_depth > 0 ) {
                        $current_fragment .= "''";
                    }

                    $index++;
                    continue;
                }

                $inside_quotes = ! $inside_quotes;
            }

            if ( ! $inside_quotes ) {
                if ( '(' === $character ) {
                    if ( 0 === $paren_depth ) {
                        $current_fragment = '';
                    }

                    $paren_depth++;
                }

                if ( $paren_depth > 0 ) {
                    $current_fragment .= $character;
                }

                if ( ')' === $character && $paren_depth > 0 ) {
                    $paren_depth--;

                    if ( 0 === $paren_depth ) {
                        $rows[]           = trim( $current_fragment, ",; \t\n\r\0\x0B" );
                        $current_fragment = '';
                    }
                }

                continue;
            }

            if ( $paren_depth > 0 ) {
                $current_fragment .= $character;
            }
        }

        if ( ! empty( $rows ) ) {
            return array_values( array_filter( $rows ) );
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

    private function is_legacy_quote_backslash_escaped( $value, $quote_index ) {
        $backslash_count = 0;

        for ( $index = (int) $quote_index - 1; $index >= 0; $index-- ) {
            if ( '\\' !== $value[ $index ] ) {
                break;
            }

            $backslash_count++;
        }

        return 1 === ( $backslash_count % 2 );
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


    private function sync_admin_representative_assignments( $representative_student_id ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $representative_student_id = absint( $representative_student_id );

        if ( $representative_student_id <= 0 ) {
            return;
        }

        if (
            ! isset( $_POST['assigned_students'] ) &&
            ! isset( $_POST['assigned_students_meta_wpuserid'] ) &&
            ! isset( $_POST['assigned_students_meta_uniquestudentid'] ) &&
            ! isset( $_POST['is_a_representative'] )
        ) {
            return;
        }

        global $wpdb;
        $students_table = $wpdb->prefix . 'teqcidb_students';
        $representative = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, first_name, last_name, email, phone_cell, phone_office, wpuserid, uniquestudentid, is_a_representative FROM $students_table WHERE id = %d LIMIT 1",
                $representative_student_id
            ),
            ARRAY_A
        );

        if ( ! is_array( $representative ) ) {
            return;
        }

        $representative_contact = $this->build_representative_contact_from_student_row( $representative );
        $current_assigned_ids   = $this->find_assigned_student_ids_for_representative( $representative_contact, $students_table, $representative_student_id );

        $is_representative = ! empty( $representative['is_a_representative'] );
        $selected_ids      = $is_representative ? $this->resolve_assigned_student_ids_from_post( $students_table, $representative_student_id ) : array();

        $remove_ids = array_values( array_diff( $current_assigned_ids, $selected_ids ) );

        if ( ! empty( $remove_ids ) ) {
            $this->update_student_representative_value_for_ids( $students_table, $remove_ids, '' );
        }

        if ( ! empty( $selected_ids ) ) {
            $this->update_student_representative_value_for_ids( $students_table, $selected_ids, wp_json_encode( $representative_contact ) );
        }
    }

    private function resolve_assigned_student_ids_from_post( $students_table, $representative_student_id ) {
        $labels     = $this->sanitize_text_array( isset( $_POST['assigned_students'] ) ? wp_unslash( $_POST['assigned_students'] ) : array() );
        $wp_user_ids = $this->sanitize_id_array( isset( $_POST['assigned_students_meta_wpuserid'] ) ? wp_unslash( $_POST['assigned_students_meta_wpuserid'] ) : array() );
        $unique_ids = $this->sanitize_text_array( isset( $_POST['assigned_students_meta_uniquestudentid'] ) ? wp_unslash( $_POST['assigned_students_meta_uniquestudentid'] ) : array() );

        $max_count = max( count( $labels ), count( $wp_user_ids ), count( $unique_ids ) );

        if ( $max_count <= 0 ) {
            return array();
        }

        $student_ids = array();

        for ( $i = 0; $i < $max_count; $i++ ) {
            $resolved_id = 0;
            $wp_user_id  = isset( $wp_user_ids[ $i ] ) ? absint( $wp_user_ids[ $i ] ) : 0;
            $unique_id   = isset( $unique_ids[ $i ] ) ? sanitize_text_field( (string) $unique_ids[ $i ] ) : '';
            $label       = isset( $labels[ $i ] ) ? sanitize_text_field( (string) $labels[ $i ] ) : '';

            if ( $wp_user_id > 0 ) {
                $resolved_id = (int) $this->find_student_id_by_wp_user_id( $students_table, $wp_user_id );
            }

            if ( $resolved_id <= 0 && '' !== $unique_id ) {
                $resolved_id = (int) $this->find_student_id_by_unique_student_id( $students_table, $unique_id );
            }

            if ( $resolved_id <= 0 && '' !== $label ) {
                $email_from_label = $this->extract_email_from_assignment_label( $label );

                if ( '' !== $email_from_label ) {
                    $resolved_id = (int) $this->find_student_id_by_email( $students_table, $email_from_label );
                }
            }

            if ( $resolved_id > 0 && $resolved_id !== $representative_student_id ) {
                $student_ids[] = $resolved_id;
            }
        }

        return array_values( array_unique( array_map( 'absint', $student_ids ) ) );
    }

    private function find_assigned_student_ids_for_representative( array $representative_contact, $students_table, $representative_student_id ) {
        global $wpdb;

        $search_tokens = array_filter(
            array(
                isset( $representative_contact['email'] ) ? $representative_contact['email'] : '',
                isset( $representative_contact['wpuserid'] ) ? $representative_contact['wpuserid'] : '',
                isset( $representative_contact['wpid'] ) ? $representative_contact['wpid'] : '',
                isset( $representative_contact['uniquestudentid'] ) ? $representative_contact['uniquestudentid'] : '',
            ),
            static function( $value ) {
                return '' !== (string) $value;
            }
        );

        if ( empty( $search_tokens ) ) {
            return array();
        }

        $where_parts  = array();
        $where_params = array();

        foreach ( $search_tokens as $token ) {
            $where_parts[]  = 'their_representative LIKE %s';
            $where_params[] = '%' . $wpdb->esc_like( (string) $token ) . '%';
        }

        $where_sql = implode( ' OR ', $where_parts );
        $query     = "SELECT id FROM $students_table WHERE id <> %d AND ($where_sql)";
        $params    = array_merge( array( absint( $representative_student_id ) ), $where_params );
        $results   = $wpdb->get_col( $wpdb->prepare( $query, $params ) );

        if ( ! is_array( $results ) ) {
            return array();
        }

        return array_values( array_filter( array_map( 'absint', $results ) ) );
    }

    private function update_student_representative_value_for_ids( $students_table, array $student_ids, $representative_json ) {
        global $wpdb;

        $student_ids = array_values( array_filter( array_map( 'absint', $student_ids ) ) );

        if ( empty( $student_ids ) ) {
            return;
        }

        foreach ( $student_ids as $student_id ) {
            $wpdb->update(
                $students_table,
                array( 'their_representative' => $representative_json ),
                array( 'id' => $student_id ),
                array( '%s' ),
                array( '%d' )
            );
        }
    }

    private function find_student_id_by_wp_user_id( $students_table, $wp_user_id ) {
        global $wpdb;

        if ( absint( $wp_user_id ) <= 0 ) {
            return 0;
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $students_table WHERE wpuserid = %s LIMIT 1",
                (string) absint( $wp_user_id )
            )
        );
    }

    private function find_student_id_by_unique_student_id( $students_table, $unique_student_id ) {
        global $wpdb;

        $unique_student_id = sanitize_text_field( (string) $unique_student_id );

        if ( '' === $unique_student_id ) {
            return 0;
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $students_table WHERE uniquestudentid = %s LIMIT 1",
                $unique_student_id
            )
        );
    }

    private function find_student_id_by_email( $students_table, $email ) {
        global $wpdb;

        $email = sanitize_email( $email );

        if ( '' === $email ) {
            return 0;
        }

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $students_table WHERE email = %s LIMIT 1",
                $email
            )
        );
    }

    private function extract_email_from_assignment_label( $label ) {
        $label = trim( (string) $label );

        if ( '' === $label ) {
            return '';
        }

        if ( preg_match( '/\(([\w.%+\-]+@[\w.\-]+\.[A-Za-z]{2,})\)\s*$/', $label, $matches ) ) {
            return sanitize_email( $matches[1] );
        }

        if ( is_email( $label ) ) {
            return sanitize_email( $label );
        }

        return '';
    }

    private function sanitize_text_array( $value ) {
        if ( ! is_array( $value ) ) {
            return array();
        }

        return array_values(
            array_map(
                static function( $item ) {
                    return sanitize_text_field( (string) $item );
                },
                $value
            )
        );
    }

    private function sanitize_id_array( $value ) {
        if ( ! is_array( $value ) ) {
            return array();
        }

        return array_values(
            array_map(
                static function( $item ) {
                    return absint( $item );
                },
                $value
            )
        );
    }

    private function build_representative_contact_from_student_row( array $student_row ) {
        $contact = array(
            'first_name'      => isset( $student_row['first_name'] ) ? sanitize_text_field( (string) $student_row['first_name'] ) : '',
            'last_name'       => isset( $student_row['last_name'] ) ? sanitize_text_field( (string) $student_row['last_name'] ) : '',
            'email'           => isset( $student_row['email'] ) ? sanitize_email( (string) $student_row['email'] ) : '',
            'phone'           => '',
            'wpid'            => isset( $student_row['wpuserid'] ) ? (string) absint( $student_row['wpuserid'] ) : '',
            'wpuserid'        => isset( $student_row['wpuserid'] ) ? (string) absint( $student_row['wpuserid'] ) : '',
            'uniquestudentid' => isset( $student_row['uniquestudentid'] ) ? sanitize_text_field( (string) $student_row['uniquestudentid'] ) : '',
        );

        $phone_cell   = isset( $student_row['phone_cell'] ) ? $this->format_phone_for_response( $student_row['phone_cell'] ) : '';
        $phone_office = isset( $student_row['phone_office'] ) ? $this->format_phone_for_response( $student_row['phone_office'] ) : '';

        if ( '' !== $phone_cell ) {
            $contact['phone'] = $phone_cell;
        } elseif ( '' !== $phone_office ) {
            $contact['phone'] = $phone_office;
        }

        return $contact;
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
        $entity['registered_students_total'] = $this->get_registered_students_total_for_class( $entity );
        $entity['registered_students_loaded'] = 0;

        $class_url_value = isset( $entity['classurl'] ) ? trim( (string) $entity['classurl'] ) : '';

        if ( '' !== $class_url_value && 0 === strpos( $class_url_value, '/' ) ) {
            $entity['classurl'] = esc_url_raw( home_url( $class_url_value ) );
        }

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


    private function get_registered_students_for_class( array $entity ) {
        $registered_data = $this->get_registered_students_page_for_class( $entity, 1, $this->get_registered_students_total_for_class( $entity ) );

        return isset( $registered_data['students'] ) && is_array( $registered_data['students'] ) ? $registered_data['students'] : array();
    }

    private function get_class_entity_for_registered_students( $class_id ) {
        global $wpdb;

        $classes_table = $wpdb->prefix . 'teqcidb_classes';
        $row           = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, uniqueclassid, classname FROM $classes_table WHERE id = %d",
                absint( $class_id )
            ),
            ARRAY_A
        );

        return is_array( $row ) ? $row : array();
    }

    private function get_registered_students_total_for_class( array $entity ) {
        $history_args = $this->get_class_history_query_args( $entity );

        if ( empty( $history_args ) ) {
            return 0;
        }

        global $wpdb;
        $history_table = $wpdb->prefix . 'teqcidb_studenthistory';

        $count_query = "SELECT COUNT(*) FROM (
            SELECT CASE WHEN wpuserid > 0 THEN CONCAT('wpid:', wpuserid) ELSE CONCAT('uid:', uniquestudentid) END AS student_key
            FROM $history_table
            WHERE {$history_args['where_sql']}
            AND ( wpuserid > 0 OR uniquestudentid <> '' )
            GROUP BY student_key
        ) AS registered_students";

        return (int) $wpdb->get_var( $wpdb->prepare( $count_query, $history_args['where_params'] ) );
    }

    private function get_registered_students_page_for_class( array $entity, $page, $per_page ) {
        global $wpdb;

        $total = $this->get_registered_students_total_for_class( $entity );

        if ( $total <= 0 ) {
            return array(
                'students' => array(),
                'total'    => 0,
            );
        }

        $page     = max( 1, absint( $page ) );
        $per_page = max( 1, absint( $per_page ) );
        $per_page = min( $per_page, 100 );
        $offset   = ( $page - 1 ) * $per_page;

        if ( $offset < 0 ) {
            $offset = 0;
        }

        $history_args = $this->get_class_history_query_args( $entity );

        if ( empty( $history_args ) ) {
            return array(
                'students' => array(),
                'total'    => 0,
            );
        }

        $history_table   = $wpdb->prefix . 'teqcidb_studenthistory';
        $identifier_sql  = "SELECT
                CASE WHEN wpuserid > 0 THEN CONCAT('wpid:', wpuserid) ELSE CONCAT('uid:', uniquestudentid) END AS student_key,
                MIN(id) AS history_id,
                MAX(CASE WHEN wpuserid > 0 THEN wpuserid ELSE 0 END) AS wpuserid,
                MAX(uniquestudentid) AS uniquestudentid
            FROM $history_table
            WHERE {$history_args['where_sql']}
            AND ( wpuserid > 0 OR uniquestudentid <> '' )
            GROUP BY student_key
            ORDER BY history_id ASC
            LIMIT %d OFFSET %d";
        $identifier_args = array_merge( $history_args['where_params'], array( $per_page, $offset ) );
        $identifier_rows = $wpdb->get_results( $wpdb->prepare( $identifier_sql, $identifier_args ), ARRAY_A );

        if ( ! is_array( $identifier_rows ) || empty( $identifier_rows ) ) {
            return array(
                'students' => array(),
                'total'    => $total,
            );
        }

        $students_by_key = $this->get_students_map_for_registered_identifiers( $identifier_rows );
        $students        = array();

        foreach ( $identifier_rows as $identifier_row ) {
            $wp_user_id = isset( $identifier_row['wpuserid'] ) ? absint( $identifier_row['wpuserid'] ) : 0;
            $unique_id  = isset( $identifier_row['uniquestudentid'] ) ? sanitize_text_field( (string) $identifier_row['uniquestudentid'] ) : '';
            $student_key = $wp_user_id > 0 ? 'wpid:' . $wp_user_id : 'uid:' . $unique_id;

            if ( isset( $students_by_key[ $student_key ] ) ) {
                $students[] = $students_by_key[ $student_key ];
            }
        }

        return array(
            'students' => $students,
            'total'    => $total,
        );
    }

    private function get_class_history_query_args( array $entity ) {
        global $wpdb;

        $history_table   = $wpdb->prefix . 'teqcidb_studenthistory';
        $unique_class_id = isset( $entity['uniqueclassid'] ) ? sanitize_text_field( (string) $entity['uniqueclassid'] ) : '';
        $class_name      = isset( $entity['classname'] ) ? sanitize_text_field( (string) $entity['classname'] ) : '';

        if ( '' !== $unique_class_id ) {
            $has_unique_rows = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $history_table WHERE uniqueclassid = %s",
                    $unique_class_id
                )
            );

            if ( $has_unique_rows > 0 ) {
                return array(
                    'where_sql'    => 'uniqueclassid = %s',
                    'where_params' => array( $unique_class_id ),
                );
            }
        }

        if ( '' !== $class_name ) {
            return array(
                'where_sql'    => 'classname = %s',
                'where_params' => array( $class_name ),
            );
        }

        return array();
    }

    private function get_students_map_for_registered_identifiers( array $identifier_rows ) {
        global $wpdb;

        $students_table = $wpdb->prefix . 'teqcidb_students';
        $wp_user_ids    = array();
        $unique_ids     = array();

        foreach ( $identifier_rows as $identifier_row ) {
            $wp_user_id = isset( $identifier_row['wpuserid'] ) ? absint( $identifier_row['wpuserid'] ) : 0;
            $unique_id  = isset( $identifier_row['uniquestudentid'] ) ? sanitize_text_field( (string) $identifier_row['uniquestudentid'] ) : '';

            if ( $wp_user_id > 0 ) {
                $wp_user_ids[] = $wp_user_id;
            }

            if ( '' !== $unique_id ) {
                $unique_ids[] = $unique_id;
            }
        }

        $wp_user_ids = array_values( array_unique( $wp_user_ids ) );
        $unique_ids  = array_values( array_unique( $unique_ids ) );

        if ( empty( $wp_user_ids ) && empty( $unique_ids ) ) {
            return array();
        }

        $where_clauses = array();
        $query_params  = array();

        if ( ! empty( $wp_user_ids ) ) {
            $where_clauses[] = 'wpuserid IN (' . implode( ', ', array_fill( 0, count( $wp_user_ids ), '%d' ) ) . ')';
            $query_params    = array_merge( $query_params, $wp_user_ids );
        }

        if ( ! empty( $unique_ids ) ) {
            $where_clauses[] = 'uniquestudentid IN (' . implode( ', ', array_fill( 0, count( $unique_ids ), '%s' ) ) . ')';
            $query_params    = array_merge( $query_params, $unique_ids );
        }

        $student_query = "SELECT * FROM $students_table";

        if ( ! empty( $where_clauses ) ) {
            $student_query .= ' WHERE ' . implode( ' OR ', $where_clauses );
        }

        $student_rows = $wpdb->get_results( $wpdb->prepare( $student_query, $query_params ), ARRAY_A );

        if ( ! is_array( $student_rows ) || empty( $student_rows ) ) {
            return array();
        }

        $students_by_key = array();

        foreach ( $student_rows as $student_row ) {
            if ( ! is_array( $student_row ) ) {
                continue;
            }

            $prepared = $this->prepare_student_entity( $student_row );
            $student  = array(
                'wpuserid'       => isset( $prepared['wpuserid'] ) ? absint( $prepared['wpuserid'] ) : 0,
                'uniquestudentid' => isset( $prepared['uniquestudentid'] ) ? sanitize_text_field( (string) $prepared['uniquestudentid'] ) : '',
                'first_name'     => isset( $prepared['first_name'] ) ? sanitize_text_field( (string) $prepared['first_name'] ) : '',
                'last_name'      => isset( $prepared['last_name'] ) ? sanitize_text_field( (string) $prepared['last_name'] ) : '',
                'company'        => isset( $prepared['company'] ) ? sanitize_text_field( (string) $prepared['company'] ) : '',
                'email'          => isset( $prepared['email'] ) ? sanitize_email( (string) $prepared['email'] ) : '',
                'phone_cell'     => isset( $prepared['phone_cell'] ) ? sanitize_text_field( (string) $prepared['phone_cell'] ) : '',
                'phone_office'   => isset( $prepared['phone_office'] ) ? sanitize_text_field( (string) $prepared['phone_office'] ) : '',
            );

            if ( $student['wpuserid'] > 0 ) {
                $students_by_key[ 'wpid:' . $student['wpuserid'] ] = $student;
            }

            if ( '' !== $student['uniquestudentid'] ) {
                $students_by_key[ 'uid:' . $student['uniquestudentid'] ] = $student;
            }
        }

        return $students_by_key;
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
        $entity['assigned_students'] = $this->get_assigned_students_for_admin_representative( $entity );

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


    private function get_assigned_students_for_admin_representative( array $representative_student ) {
        if ( empty( $representative_student['is_a_representative'] ) ) {
            return array();
        }

        global $wpdb;
        $students_table = $wpdb->prefix . 'teqcidb_students';
        $representative = $this->build_representative_contact_from_student_row( $representative_student );
        $assigned_ids   = $this->find_assigned_student_ids_for_representative(
            $representative,
            $students_table,
            isset( $representative_student['id'] ) ? absint( $representative_student['id'] ) : 0
        );

        if ( empty( $assigned_ids ) ) {
            return array();
        }

        $assigned_students = array();

        foreach ( $assigned_ids as $assigned_id ) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT first_name, last_name, email, wpuserid, uniquestudentid FROM $students_table WHERE id = %d LIMIT 1",
                    absint( $assigned_id )
                ),
                ARRAY_A
            );

            if ( ! is_array( $row ) ) {
                continue;
            }

            $label = $this->build_student_display_name( $row );
            $email = isset( $row['email'] ) ? sanitize_email( (string) $row['email'] ) : '';

            if ( '' !== $email ) {
                $label = '' !== $label ? sprintf( '%1$s (%2$s)', $label, $email ) : $email;
            }

            if ( '' === $label ) {
                continue;
            }

            $assigned_students[] = array(
                'label'           => $label,
                'wpuserid'        => isset( $row['wpuserid'] ) ? (string) absint( $row['wpuserid'] ) : '',
                'uniquestudentid' => isset( $row['uniquestudentid'] ) ? sanitize_text_field( (string) $row['uniquestudentid'] ) : '',
            );
        }

        return $assigned_students;
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
