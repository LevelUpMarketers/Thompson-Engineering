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
        $now   = current_time( 'mysql' );

        $name = $this->sanitize_text_value( 'name' );

        if ( '' === $name ) {
            $name = $this->sanitize_text_value( 'placeholder_1' );
        }

        $state_options          = $this->get_us_states();
        $extended_state_options = $this->get_us_states_and_territories();
        $opt_in_keys            = array(
            'opt_in_marketing_email',
            'opt_in_marketing_sms',
            'opt_in_event_update_email',
            'opt_in_event_update_sms',
        );

        $data = array(
            'name'                        => $name,
            'placeholder_1'               => $this->sanitize_text_value( 'placeholder_1' ),
            'placeholder_2'               => $this->sanitize_text_value( 'placeholder_2' ),
            'placeholder_3'               => $this->sanitize_date_value( 'placeholder_3' ),
            'placeholder_4'               => $this->sanitize_select_value( 'placeholder_4', array( '0', '1' ) ),
            'placeholder_5'               => $this->sanitize_time_value( 'placeholder_5' ),
            'placeholder_6'               => $this->sanitize_time_value( 'placeholder_6' ),
            'placeholder_7'               => $this->sanitize_select_value( 'placeholder_7', array( '0', '1' ) ),
            'placeholder_8'               => $this->sanitize_text_value( 'placeholder_8' ),
            'placeholder_9'               => $this->sanitize_text_value( 'placeholder_9' ),
            'placeholder_10'              => $this->sanitize_text_value( 'placeholder_10' ),
            'placeholder_11'              => $this->sanitize_state_value( 'placeholder_11', $state_options ),
            'placeholder_12'              => $this->sanitize_text_value( 'placeholder_12' ),
            'placeholder_13'              => $this->sanitize_text_value( 'placeholder_13' ),
            'placeholder_14'              => $this->sanitize_url_value( 'placeholder_14' ),
            'placeholder_15'              => $this->sanitize_select_value( 'placeholder_15', array( 'option1', 'option2', 'option3' ) ),
            'placeholder_16'              => $this->sanitize_decimal_value( 'placeholder_16' ),
            'placeholder_17'              => $this->sanitize_decimal_value( 'placeholder_17' ),
            'placeholder_18'              => $this->sanitize_decimal_value( 'placeholder_18' ),
            'placeholder_19'              => $this->sanitize_select_value( 'placeholder_19', array( '0', '1' ) ),
            'placeholder_20'              => $this->sanitize_select_value( 'placeholder_20', array( '0', '1' ) ),
            'placeholder_21'              => $this->sanitize_state_value( 'placeholder_21', $extended_state_options ),
            'placeholder_22'              => $this->sanitize_text_value( 'placeholder_22' ),
            'placeholder_23'              => $this->sanitize_select_value( 'placeholder_23', array( 'option1', 'option2', 'option3' ) ),
            'placeholder_24'              => $this->sanitize_opt_in_summary( $opt_in_keys ),
            'placeholder_25'              => $this->sanitize_items_value( 'placeholder_25' ),
            'placeholder_26'              => $this->sanitize_color_value( 'placeholder_26' ),
            'placeholder_27'              => $this->sanitize_image_value( 'placeholder_27' ),
            'placeholder_28'              => $this->sanitize_editor_value( 'placeholder_28' ),
            'opt_in_marketing_email'      => $this->sanitize_checkbox_value( 'opt_in_marketing_email' ),
            'opt_in_marketing_sms'        => $this->sanitize_checkbox_value( 'opt_in_marketing_sms' ),
            'opt_in_event_update_email'   => $this->sanitize_checkbox_value( 'opt_in_event_update_email' ),
            'opt_in_event_update_sms'     => $this->sanitize_checkbox_value( 'opt_in_event_update_sms' ),
            'updated_at'                  => $now,
        );

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
            $data['created_at'] = $now;
            $formats[]          = '%s';
            $result             = $wpdb->insert( $table, $data, $formats );
            $message            = __( 'Saved', 'teqcidb' );

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
        wp_send_json_success( array( 'message' => $message ) );
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

        $searchable_columns = array(
            'placeholder_1',
            'placeholder_2',
            'placeholder_3',
        );

        $search_terms = array();

        foreach ( $searchable_columns as $column ) {
            if ( isset( $raw_search[ $column ] ) ) {
                $value = sanitize_text_field( $raw_search[ $column ] );

                if ( '' !== $value ) {
                    $search_terms[ $column ] = $value;
                }
            }
        }

        $where_clauses = array();
        $where_params  = array();

        foreach ( $search_terms as $column => $value ) {
            $where_clauses[] = $column . ' LIKE %s';
            $where_params[]  = '%' . $wpdb->esc_like( $value ) . '%';
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
            $opt_in_keys = array(
                'opt_in_marketing_email',
                'opt_in_marketing_sms',
                'opt_in_event_update_email',
                'opt_in_event_update_sms',
            );

            $select_query = "SELECT * FROM $table";

            if ( $where_sql ) {
                $select_query .= ' ' . $where_sql;
            }

            $select_query .= ' ORDER BY placeholder_1 ASC, id ASC LIMIT %d OFFSET %d';

            $select_params = $where_params;
            $select_params[] = $per_page;
            $select_params[] = $offset;

            $entities = $wpdb->get_results(
                $wpdb->prepare(
                    $select_query,
                    $select_params
                ),
                ARRAY_A
            );

            foreach ( $entities as &$entity ) {
                $entity['placeholder_3']  = $this->format_date_for_response( $entity['placeholder_3'] );
                $entity['placeholder_5']  = $this->format_time_for_response( $entity['placeholder_5'] );
                $entity['placeholder_6']  = $this->format_time_for_response( $entity['placeholder_6'] );
                $entity['placeholder_16'] = $this->format_decimal_for_response( $entity['placeholder_16'] );
                $entity['placeholder_17'] = $this->format_decimal_for_response( $entity['placeholder_17'] );
                $entity['placeholder_18'] = $this->format_decimal_for_response( $entity['placeholder_18'] );
                $entity['placeholder_24'] = $this->format_json_field( $entity['placeholder_24'] );
                $entity['placeholder_25'] = $this->format_json_field( $entity['placeholder_25'] );
                $entity['placeholder_26'] = $this->format_color_for_response( $entity['placeholder_26'] );
                $entity['placeholder_27'] = (string) absint( $entity['placeholder_27'] );
                $entity['placeholder_27_url'] = $this->get_attachment_url( $entity['placeholder_27'] );
                $entity['placeholder_28'] = $this->format_editor_content_for_response( $entity['placeholder_28'] );

                foreach ( array( 'placeholder_4', 'placeholder_7', 'placeholder_19', 'placeholder_20', 'opt_in_marketing_email', 'opt_in_marketing_sms', 'opt_in_event_update_email', 'opt_in_event_update_sms' ) as $bool_key ) {
                    if ( isset( $entity[ $bool_key ] ) ) {
                        $entity[ $bool_key ] = (string) ( (int) $entity[ $bool_key ] );
                    }
                }

                if ( ! isset( $entity['placeholder_21'] ) ) {
                    $entity['placeholder_21'] = '';
                }

                if ( ! isset( $entity['placeholder_22'] ) ) {
                    $entity['placeholder_22'] = '';
                }

                if ( ! isset( $entity['placeholder_23'] ) ) {
                    $entity['placeholder_23'] = '';
                }

                if ( ! isset( $entity['placeholder_24'] ) ) {
                    $entity['placeholder_24'] = wp_json_encode( array() );
                }

                if ( ! isset( $entity['placeholder_25'] ) ) {
                    $entity['placeholder_25'] = wp_json_encode( array() );
                }

                foreach ( $opt_in_keys as $opt_in_key ) {
                    if ( ! isset( $entity[ $opt_in_key ] ) ) {
                        $entity[ $opt_in_key ] = '0';
                    }
                }
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
