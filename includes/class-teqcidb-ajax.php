<?php
/**
 * Handle Ajax operations with configurable minimum execution time.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Ajax {

    public function register() {
        add_action( 'wp_ajax_teqcidb_save_student', array( $this, 'save_student' ) );
        add_action( 'wp_ajax_teqcidb_delete_student', array( $this, 'delete_student' ) );
        add_action( 'wp_ajax_teqcidb_read_student', array( $this, 'read_student' ) );
        add_action( 'wp_ajax_teqcidb_save_general_settings', array( $this, 'save_general_settings' ) );
        add_action( 'wp_ajax_teqcidb_save_api_settings', array( $this, 'save_api_settings' ) );
        add_action( 'wp_ajax_teqcidb_upload_legacy_student', array( $this, 'upload_legacy_student' ) );
        add_action( 'wp_ajax_teqcidb_save_email_template', array( $this, 'save_email_template' ) );
        add_action( 'wp_ajax_teqcidb_send_test_email', array( $this, 'send_test_email' ) );
        add_action( 'wp_ajax_teqcidb_clear_email_log', array( $this, 'clear_email_log' ) );
        add_action( 'wp_ajax_teqcidb_clear_error_log', array( $this, 'clear_error_log' ) );
        add_action( 'wp_ajax_teqcidb_download_error_log', array( $this, 'download_error_log' ) );
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

    public function save_student() {
        $start = microtime( true );
        check_ajax_referer( 'teqcidb_ajax_nonce' );

        global $wpdb;

        $table = $wpdb->prefix . 'teqcidb_students';
        $id    = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        $first_name = $this->sanitize_text_value( 'first_name' );
        $last_name  = $this->sanitize_text_value( 'last_name' );
        $email      = $this->sanitize_email_value( 'email' );

        if ( '' === $email ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Please provide a valid email address.', 'teqcidb' ),
                )
            );
        }

        $creating_new_student = ( 0 === $id );
        $new_wp_user_id       = 0;

        if ( $creating_new_student ) {
            $existing_user = get_user_by( 'email', $email );

            if ( $existing_user ) {
                $this->maybe_delay( $start );
                wp_send_json_error(
                    array(
                        'message' => __( 'A WordPress user already exists with that email address.', 'teqcidb' ),
                    )
                );
            }

            $display_name = trim( $first_name . ' ' . $last_name );

            if ( '' === $display_name ) {
                $display_name = $email;
            }

            $user_login = $this->generate_user_login( $email );
            $user_pass  = wp_generate_password( 20, true, true );
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
        }

        $association_options = array( 'AAPA', 'ARBA', 'AGC', 'ABC', 'AUCA' );

        $data = array(
            'first_name'            => $first_name,
            'last_name'             => $last_name,
            'company'               => $this->sanitize_text_value( 'company' ),
            'old_companies'         => $this->sanitize_items_value( 'old_companies' ),
            'student_address'       => $this->sanitize_student_address(),
            'phone_cell'            => $this->sanitize_phone_value( 'phone_cell' ),
            'phone_office'          => $this->sanitize_phone_value( 'phone_office' ),
            'fax'                   => $this->sanitize_phone_value( 'fax' ),
            'email'                 => $email,
            'initial_training_date' => $this->sanitize_date_value( 'initial_training_date' ),
            'last_refresher_date'   => $this->sanitize_date_value( 'last_refresher_date' ),
            'is_a_representative'   => $this->sanitize_yes_no_value( 'is_a_representative' ),
            'their_representative'  => $this->sanitize_representative_contact(),
            'associations'          => $this->sanitize_associations_value( 'associations', $association_options ),
            'expiration_date'       => $this->sanitize_date_value( 'expiration_date' ),
            'qcinumber'             => $this->sanitize_text_value( 'qcinumber' ),
            'comments'              => $this->sanitize_textarea_value( 'comments' ),
        );

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

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => $message,
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
                    'payment_gateway_client_key'    => array(
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

    public function upload_legacy_student() {
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

        $raw_record = isset( $_POST['legacy_record'] ) ? wp_unslash( $_POST['legacy_record'] ) : '';
        $parsed     = $this->parse_legacy_student_record( $raw_record );

        if ( is_wp_error( $parsed ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => $parsed->get_error_message(),
                )
            );
        }

        $mapped = $this->map_legacy_student_record( $parsed );

        if ( is_wp_error( $mapped ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => $mapped->get_error_message(),
                )
            );
        }

        global $wpdb;

        $table = $wpdb->prefix . 'teqcidb_students';

        if ( $this->legacy_student_value_exists( $table, 'email', $mapped['email'] ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'A student with this email already exists.', 'teqcidb' ),
                )
            );
        }

        if ( $this->legacy_student_value_exists( $table, 'uniquestudentid', $mapped['uniquestudentid'] ) ) {
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'A student with this unique ID already exists.', 'teqcidb' ),
                )
            );
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
            $this->maybe_delay( $start );
            wp_send_json_error(
                array(
                    'message' => __( 'Unable to upload the record. Please check the data and try again.', 'teqcidb' ),
                )
            );
        }

        $this->maybe_delay( $start );
        wp_send_json_success(
            array(
                'message' => __( 'Legacy student uploaded successfully.', 'teqcidb' ),
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

    private function parse_legacy_student_record( $raw_record ) {
        $normalized = trim( (string) $raw_record );

        if ( '' === $normalized ) {
            return new WP_Error( 'teqcidb_legacy_empty', __( 'Please paste a legacy student row before uploading.', 'teqcidb' ) );
        }

        $normalized = trim( $normalized, "; \t\n\r\0\x0B" );

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

    private function map_legacy_student_record( array $legacy_record ) {
        $email = sanitize_email( isset( $legacy_record['email'] ) ? $legacy_record['email'] : '' );

        if ( '' === $email ) {
            return new WP_Error( 'teqcidb_legacy_email', __( 'A valid email address is required.', 'teqcidb' ) );
        }

        $unique_id = isset( $legacy_record['uniquestudentid'] ) ? sanitize_text_field( $legacy_record['uniquestudentid'] ) : '';

        if ( '' === $unique_id ) {
            return new WP_Error( 'teqcidb_legacy_unique_id', __( 'A unique student ID is required.', 'teqcidb' ) );
        }

        $wp_user_id = isset( $legacy_record['wpuserid'] ) ? absint( $legacy_record['wpuserid'] ) : 0;
        $wp_user_id = $wp_user_id > 0 ? $wp_user_id : null;

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

    private function convert_legacy_date( $value ) {
        $value = $this->normalize_legacy_value( $value );

        if ( '' === $value ) {
            return '';
        }

        $value = str_replace( '.', '-', $value );

        $parsed = date_create( $value );

        if ( ! $parsed ) {
            return '';
        }

        return $parsed->format( 'Y-m-d' );
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
        $contact = array(
            'first_name' => $this->sanitize_text_value( 'representative_first_name' ),
            'last_name'  => $this->sanitize_text_value( 'representative_last_name' ),
            'email'      => $this->sanitize_email_value( 'representative_email' ),
            'phone'      => $this->sanitize_phone_value( 'representative_phone' ),
        );

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
        if ( empty( $value ) ) {
            return wp_json_encode( array() );
        }

        if ( is_array( $value ) ) {
            return wp_json_encode( array_values( $value ) );
        }

        $decoded = json_decode( $value, true );

        if ( is_array( $decoded ) ) {
            return wp_json_encode( array_values( $decoded ) );
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
