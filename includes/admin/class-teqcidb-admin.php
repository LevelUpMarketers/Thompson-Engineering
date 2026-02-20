<?php
/**
 * Admin pages for Thompson Engineering QCI Database
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Admin {

    public function register() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_post_teqcidb_run_cron_event', array( $this, 'handle_run_cron_event' ) );
        add_action( 'admin_post_teqcidb_delete_cron_event', array( $this, 'handle_delete_cron_event' ) );
        add_action( 'admin_post_teqcidb_download_email_log', array( $this, 'handle_download_email_log' ) );
        add_action( 'admin_post_teqcidb_delete_generated_content', array( $this, 'handle_delete_generated_content' ) );
        add_action( 'admin_post_teqcidb_save_quiz', array( $this, 'handle_save_quiz' ) );
        add_action( 'admin_post_teqcidb_update_quiz', array( $this, 'handle_update_quiz' ) );
    }

    public function add_menu() {
        add_menu_page(
            __( 'QCI Database', 'teqcidb' ),
            __( 'QCI Database', 'teqcidb' ),
            'manage_options',
            'teqcidb-student',
            array( $this, 'render_student_page' ),
            TEQCIDB_PLUGIN_URL . 'assets/images/alabama_dashboard_icon.png',
            6
        );

        global $submenu;

        $submenu['teqcidb-student'][0] = array(
            __( 'Students', 'teqcidb' ),
            'manage_options',
            'teqcidb-student',
            __( 'QCI Database', 'teqcidb' ),
        );

        add_submenu_page(
            'teqcidb-student',
            __( 'Classes', 'teqcidb' ),
            __( 'Classes', 'teqcidb' ),
            'manage_options',
            'teqcidb-classes',
            array( $this, 'render_classes_page' )
        );

        add_submenu_page(
            'teqcidb-student',
            __( 'Quizzes', 'teqcidb' ),
            __( 'Quizzes', 'teqcidb' ),
            'manage_options',
            'teqcidb-quizzes',
            array( $this, 'render_quizzes_page' )
        );

        add_submenu_page(
            'teqcidb-student',
            __( 'Communications', 'teqcidb' ),
            __( 'Communications', 'teqcidb' ),
            'manage_options',
            'teqcidb-communications',
            array( $this, 'render_communications_page' )
        );

        add_submenu_page(
            'teqcidb-student',
            __( 'Settings', 'teqcidb' ),
            __( 'Settings', 'teqcidb' ),
            'manage_options',
            'teqcidb-settings',
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            'teqcidb-student',
            __( 'Logs', 'teqcidb' ),
            __( 'Logs', 'teqcidb' ),
            'manage_options',
            'teqcidb-logs',
            array( $this, 'render_logs_page' )
        );
    }

    public function render_communications_page() {
        $tabs = array(
            'email-templates' => __( 'Email Templates', 'teqcidb' ),
            'email-logs'      => __( 'Email Logs', 'teqcidb' ),
            'sms-templates'   => __( 'SMS Templates', 'teqcidb' ),
            'sms-logs'        => __( 'SMS Logs', 'teqcidb' ),
        );

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'email-templates';

        if ( ! array_key_exists( $active_tab, $tabs ) ) {
            $active_tab = 'email-templates';
        }

        echo '<div class="wrap"><h1>' . esc_html__( 'TEQCIDB Communications', 'teqcidb' ) . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';

        foreach ( $tabs as $tab_slug => $label ) {
            $classes = array( 'nav-tab' );

            if ( $tab_slug === $active_tab ) {
                $classes[] = 'nav-tab-active';
            }

            printf(
                '<a href="%1$s" class="%2$s">%3$s</a>',
                esc_url( add_query_arg( array( 'page' => 'teqcidb-communications', 'tab' => $tab_slug ), admin_url( 'admin.php' ) ) ),
                esc_attr( implode( ' ', $classes ) ),
                esc_html( $label )
            );
        }

        echo '</h2>';

        $tab_descriptions = array(
            'email-templates' => __( 'Review placeholder email templates that demonstrate how communications can be grouped for future automation requests.', 'teqcidb' ),
            'email-logs'      => __( 'Review detailed delivery history for plugin-generated emails and export the log for troubleshooting.', 'teqcidb' ),
            'sms-templates'   => __( 'Prepare SMS templates that mirror your email workflows so every touchpoint stays consistent.', 'teqcidb' ),
            'sms-logs'        => __( 'Audit sent SMS messages and spot delivery issues as soon as log data becomes available.', 'teqcidb' ),
        );

        $description = isset( $tab_descriptions[ $active_tab ] ) ? $tab_descriptions[ $active_tab ] : '';

        $this->render_tab_intro( $tabs[ $active_tab ], $description );

        if ( 'email-logs' === $active_tab ) {
            $this->render_logging_status_notice( TEQCIDB_Settings_Helper::FIELD_LOG_EMAIL );
        } elseif ( 'sms-logs' === $active_tab ) {
            $this->render_logging_status_notice( TEQCIDB_Settings_Helper::FIELD_LOG_SMS );
        }

        if ( 'email-templates' === $active_tab ) {
            $this->render_email_templates_tab();
        } elseif ( 'email-logs' === $active_tab ) {
            $this->render_email_logs_tab();
        } elseif ( 'sms-templates' === $active_tab ) {
            $this->render_communications_placeholder_tab(
                __( 'SMS template management is coming soon.', 'teqcidb' )
            );
        } else {
            $this->render_communications_placeholder_tab(
                __( 'SMS log history is coming soon.', 'teqcidb' )
            );
        }

        echo '</div>';
    }

    private function render_email_templates_tab() {
        $templates   = $this->get_sample_email_templates();
        foreach ( $templates as $template ) {
            if ( isset( $template['id'], $template['title'] ) ) {
                TEQCIDB_Email_Template_Helper::register_template_label( $template['id'], $template['title'] );
            }
        }
        $meta_labels = array(
            'trigger'             => __( 'Trigger', 'teqcidb' ),
            'communication_type'  => __( 'Communication Type', 'teqcidb' ),
            'category'            => __( 'Category', 'teqcidb' ),
        );
        $meta_order  = array( 'trigger', 'communication_type', 'category' );
        $column_count = count( $meta_order ) + 2; // Title and actions columns.

        echo '<div class="teqcidb-communications teqcidb-communications--email-templates">';
        echo '<div class="teqcidb-accordion-group teqcidb-accordion-group--table" data-teqcidb-accordion-group="communications">';
        echo '<table class="wp-list-table widefat striped teqcidb-accordion-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--title">' . esc_html__( 'Communication Name', 'teqcidb' ) . '</th>';

        foreach ( $meta_order as $meta_key ) {
            if ( ! isset( $meta_labels[ $meta_key ] ) ) {
                continue;
            }

            printf(
                '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--%1$s">%2$s</th>',
                esc_attr( $meta_key ),
                esc_html( $meta_labels[ $meta_key ] )
            );
        }

        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--actions">' . esc_html__( 'Actions', 'teqcidb' ) . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ( $templates as $template ) {
            $item_id    = sanitize_html_class( $template['id'] );
            $panel_id   = $item_id . '-panel';
            $header_id  = $item_id . '-header';
            $tooltip    = isset( $template['tooltip'] ) ? $template['tooltip'] : '';
            $meta_items = isset( $template['meta'] ) ? $template['meta'] : array();

            printf(
                '<tr id="%1$s" class="teqcidb-accordion__summary-row" tabindex="0" role="button" aria-expanded="false" aria-controls="%2$s">',
                esc_attr( $header_id ),
                esc_attr( $panel_id )
            );

            echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--title">';

            if ( $tooltip ) {
                printf(
                    '<span class="dashicons dashicons-info teqcidb-tooltip-icon" aria-hidden="true" data-tooltip="%1$s"></span><span class="screen-reader-text">%2$s</span>',
                    esc_attr( $tooltip ),
                    esc_html( $tooltip )
                );
            }

            echo '<span class="teqcidb-accordion__title-text">' . esc_html( $template['title'] ) . '</span>';
            echo '</td>';

            foreach ( $meta_order as $meta_key ) {
                $label      = isset( $meta_labels[ $meta_key ] ) ? $meta_labels[ $meta_key ] : '';
                $meta_value = isset( $meta_items[ $meta_key ] ) ? $meta_items[ $meta_key ] : '';

                echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--meta">';

                if ( $label ) {
                    printf(
                        '<span class="teqcidb-accordion__meta-text"><span class="teqcidb-accordion__meta-label">%1$s:</span> <span class="teqcidb-accordion__meta-value">%2$s</span></span>',
                        esc_html( $label ),
                        $meta_value ? esc_html( $meta_value ) : '&mdash;'
                    );
                }

                echo '</td>';
            }

            echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--actions">';
            echo '<span class="teqcidb-accordion__action-link" aria-hidden="true">' . esc_html__( 'Edit', 'teqcidb' ) . '</span>';
            echo '<span class="dashicons dashicons-arrow-down-alt2 teqcidb-accordion__icon" aria-hidden="true"></span>';
            echo '<span class="screen-reader-text">' . esc_html__( 'Toggle template details', 'teqcidb' ) . '</span>';
            echo '</td>';
            echo '</tr>';

            printf(
                '<tr id="%1$s" class="teqcidb-accordion__panel-row" role="region" aria-labelledby="%2$s" aria-hidden="true">',
                esc_attr( $panel_id ),
                esc_attr( $header_id )
            );
            printf(
                '<td colspan="%1$d">',
                absint( $column_count )
            );
            echo '<div class="teqcidb-accordion__panel">';
            $this->render_email_template_panel( $template );
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    private function render_email_template_panel( $template ) {
        if ( isset( $template['id'] ) && 'teqcidb-email-welcome' === $template['id'] ) {
            $this->render_welcome_email_template_panel( $template );
            return;
        }

        if ( isset( $template['content'] ) ) {
            echo '<p>' . esc_html( $template['content'] ) . '</p>';
        }
    }

    private function render_welcome_email_template_panel( $template ) {
        $template_id   = isset( $template['id'] ) ? $template['id'] : 'teqcidb-email-welcome';
        $field_prefix  = sanitize_html_class( $template_id );
        $from_name_id  = $field_prefix . '-from-name';
        $from_email_id = $field_prefix . '-from-email';
        $subject_id    = $field_prefix . '-subject';
        $body_id       = $field_prefix . '-body';
        $sms_id        = $field_prefix . '-sms';
        $token_groups       = $this->get_student_token_groups();
        $template_settings  = $this->get_email_template_settings( $template_id );
        $from_name_value    = isset( $template_settings['from_name'] ) ? $template_settings['from_name'] : '';
        $from_email_value   = isset( $template_settings['from_email'] ) ? $template_settings['from_email'] : '';
        $subject_value      = isset( $template_settings['subject'] ) ? $template_settings['subject'] : '';
        $body_value         = isset( $template_settings['body'] ) ? $template_settings['body'] : '';
        $sms_value          = isset( $template_settings['sms'] ) ? $template_settings['sms'] : '';
        $default_from_name  = TEQCIDB_Email_Template_Helper::get_default_from_name();
        $default_from_email = TEQCIDB_Email_Template_Helper::get_default_from_email();
        $preview_data       = TEQCIDB_Student_Helper::get_first_preview_data();
        $has_preview        = ! empty( $preview_data );
        $save_spinner_id    = $field_prefix . '-save-spinner';
        $save_feedback_id   = $field_prefix . '-save-feedback';
        $test_email_id      = $field_prefix . '-test-email';
        $test_spinner_id    = $field_prefix . '-test-spinner';
        $test_feedback_id   = $field_prefix . '-test-feedback';

        $preview_notice = $has_preview
            ? __( 'Enter a subject or body to generate the preview.', 'teqcidb' )
            : __( 'Add a student entry to generate a preview.', 'teqcidb' );

        echo '<div class="teqcidb-template-editor" data-template="' . esc_attr( $template_id ) . '">';

        echo '<div class="teqcidb-template-editor__fields">';

        printf(
            '<div class="teqcidb-template-editor__field"><label for="%1$s">%2$s</label><input type="text" id="%1$s" name="templates[%3$s][from_name]" class="regular-text" data-template-field="from_name" value="%4$s" placeholder="%5$s" autocomplete="name"></div>',
            esc_attr( $from_name_id ),
            esc_html__( 'Email From Name', 'teqcidb' ),
            esc_attr( $template_id ),
            esc_attr( $from_name_value ),
            esc_attr( $default_from_name )
        );

        printf(
            '<div class="teqcidb-template-editor__field"><label for="%1$s">%2$s</label><input type="email" id="%1$s" name="templates[%3$s][from_email]" class="regular-text" data-template-field="from_email" value="%4$s" placeholder="%5$s" autocomplete="email"></div>',
            esc_attr( $from_email_id ),
            esc_html__( 'Email From Address', 'teqcidb' ),
            esc_attr( $template_id ),
            esc_attr( $from_email_value ),
            esc_attr( $default_from_email )
        );

        printf(
            '<div class="teqcidb-template-editor__field"><label for="%1$s">%2$s</label><input type="text" id="%1$s" name="templates[%3$s][subject]" class="regular-text teqcidb-token-target" data-token-context="subject" value="%4$s"></div>',
            esc_attr( $subject_id ),
            esc_html__( 'Email Subject', 'teqcidb' ),
            esc_attr( $template_id ),
            esc_attr( $subject_value )
        );

        printf(
            '<div class="teqcidb-template-editor__field"><label for="%1$s">%2$s</label><textarea id="%1$s" name="templates[%3$s][body]" rows="8" class="widefat teqcidb-token-target" data-token-context="body">%4$s</textarea></div>',
            esc_attr( $body_id ),
            esc_html__( 'Email Body', 'teqcidb' ),
            esc_attr( $template_id ),
            esc_textarea( $body_value )
        );

        printf(
            '<div class="teqcidb-template-editor__field"><label for="%1$s">%2$s</label><textarea id="%1$s" name="templates[%3$s][sms]" rows="4" class="widefat teqcidb-token-target" data-token-context="sms">%4$s</textarea></div>',
            esc_attr( $sms_id ),
            esc_html__( 'SMS Text', 'teqcidb' ),
            esc_attr( $template_id ),
            esc_textarea( $sms_value )
        );

        echo '<div class="teqcidb-template-preview" aria-live="polite">';
        echo '<h3 class="teqcidb-template-preview__title">' . esc_html__( 'Email Preview', 'teqcidb' ) . '</h3>';
        echo '<p class="teqcidb-template-preview__notice">' . esc_html( $preview_notice ) . '</p>';
        echo '<div class="teqcidb-template-preview__content" data-preview-role="content">';
        echo '<p class="teqcidb-template-preview__subject"><span class="teqcidb-template-preview__label">' . esc_html__( 'Subject:', 'teqcidb' ) . '</span> <span class="teqcidb-template-preview__value" data-preview-field="subject"></span></p>';
        echo '<div class="teqcidb-template-preview__body" data-preview-field="body"></div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="teqcidb-template-editor__test">';
        printf(
            '<button type="button" class="button button-primary teqcidb-template-test-send" data-template="%1$s" data-email-input="#%2$s" data-spinner="#%3$s" data-feedback="#%4$s">%5$s</button>',
            esc_attr( $template_id ),
            esc_attr( $test_email_id ),
            esc_attr( $test_spinner_id ),
            esc_attr( $test_feedback_id ),
            esc_html__( 'Send Test Email', 'teqcidb' )
        );
        echo '<div class="teqcidb-template-editor__test-input">';
        printf(
            '<label class="screen-reader-text" for="%1$s">%2$s</label><input type="email" id="%1$s" class="regular-text teqcidb-template-test-email" placeholder="%3$s" autocomplete="off">',
            esc_attr( $test_email_id ),
            esc_html__( 'Test email address', 'teqcidb' ),
            esc_attr__( 'Enter an Email Address', 'teqcidb' )
        );
        echo '</div>';
        printf(
            '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span id="%1$s" class="spinner teqcidb-template-spinner" aria-hidden="true"></span><span id="%2$s" class="teqcidb-template-feedback" role="status" aria-live="polite"></span></span>',
            esc_attr( $test_spinner_id ),
            esc_attr( $test_feedback_id )
        );
        echo '</div>';

        echo '<div class="teqcidb-template-editor__actions">';
        printf(
            '<button type="button" class="button button-primary teqcidb-template-save" data-template="%1$s" data-spinner="#%2$s" data-feedback="#%3$s">%4$s</button>',
            esc_attr( $template_id ),
            esc_attr( $save_spinner_id ),
            esc_attr( $save_feedback_id ),
            esc_html__( 'Save Template', 'teqcidb' )
        );
        printf(
            '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span id="%1$s" class="spinner teqcidb-template-spinner" aria-hidden="true"></span><span id="%2$s" class="teqcidb-template-feedback" role="status" aria-live="polite"></span></span>',
            esc_attr( $save_spinner_id ),
            esc_attr( $save_feedback_id )
        );
        echo '</div>';

        echo '</div>';

        if ( ! empty( $token_groups ) ) {
            echo '<div class="teqcidb-template-editor__tokens">';
            echo '<h3 class="teqcidb-template-editor__tokens-heading">' . esc_html__( 'Tokens', 'teqcidb' ) . '</h3>';

            foreach ( $token_groups as $group ) {
                if ( empty( $group['tokens'] ) ) {
                    continue;
                }

                echo '<div class="teqcidb-token-group">';

                if ( ! empty( $group['title'] ) ) {
                    echo '<h4 class="teqcidb-token-group__title">' . esc_html( $group['title'] ) . '</h4>';
                }

                echo '<div class="teqcidb-token-group__buttons">';

                foreach ( $group['tokens'] as $token ) {
                    if ( empty( $token['value'] ) ) {
                        continue;
                    }

                    $label = isset( $token['label'] ) ? $token['label'] : $token['value'];

                    printf(
                        '<button type="button" class="button button-secondary teqcidb-token-button" data-token="%1$s">%2$s</button>',
                        esc_attr( $token['value'] ),
                        esc_html( $label )
                    );
                }

                echo '</div>';
                echo '</div>';
            }

            echo '</div>';
        }

        echo '</div>';
    }

    private function get_student_token_groups() {
        $labels      = $this->get_placeholder_labels();
        $token_group = array(
            'title'  => __( 'Student Information', 'teqcidb' ),
            'tokens' => array(),
        );

        foreach ( $labels as $key => $label ) {
            $token_group['tokens'][] = array(
                'value' => '{' . $key . '}',
                'label' => $label,
            );
        }

        /**
         * Filter the token groups displayed for communications templates.
         *
         * This filter allows child plugins to add new token collections or adjust
         * the existing Student defaults when repurposing the boilerplate for
         * client-specific data models.
         *
         * @param array $groups Array of token group definitions. Each group should contain
         *                      a `title` and a `tokens` list where every token includes
         *                      `value` (the merge tag) and `label` (the admin-facing text).
         */
        $groups = apply_filters( 'teqcidb_communications_token_groups', array( $token_group ) );

        return array_map( array( $this, 'normalize_token_group' ), $groups );
    }

    private function get_email_templates_option_name() {
        return TEQCIDB_Email_Template_Helper::get_option_name();
    }

    private function get_email_template_settings( $template_id ) {
        $template_id = sanitize_key( $template_id );

        if ( '' === $template_id ) {
            return array();
        }

        return TEQCIDB_Email_Template_Helper::get_template_settings( $template_id );
    }

    private function normalize_token_group( $group ) {
        if ( ! is_array( $group ) ) {
            $fields = array(
                'title'  => '',
                'tokens' => array(),
            );
        }

        $title  = isset( $group['title'] ) ? $group['title'] : '';
        $tokens = isset( $group['tokens'] ) && is_array( $group['tokens'] ) ? $group['tokens'] : array();

        $normalized_tokens = array();

        foreach ( $tokens as $token ) {
            if ( ! is_array( $token ) || empty( $token['value'] ) ) {
                continue;
            }

            $normalized_tokens[] = array(
                'value' => (string) $token['value'],
                'label' => isset( $token['label'] ) ? (string) $token['label'] : (string) $token['value'],
            );
        }

        return array(
            'title'  => (string) $title,
            'tokens' => $normalized_tokens,
        );
    }

    private function render_email_logs_tab() {
        $log_available = TEQCIDB_Email_Log_Helper::is_log_available();
        $entries       = $log_available ? TEQCIDB_Email_Log_Helper::get_log_entries() : array();
        $empty_message = __( 'No email activity has been recorded yet.', 'teqcidb' );
        $time_notice   = __( 'Timestamps display Eastern United States time.', 'teqcidb' );
        $clear_label   = __( 'Clear log', 'teqcidb' );
        $download_label = __( 'Download log file', 'teqcidb' );
        $sent_format   = __( 'Sent %s', 'teqcidb' );
        $not_available = __( 'Email logging is unavailable. Confirm that WordPress can write to the uploads directory.', 'teqcidb' );
        $body_empty    = __( 'No body content recorded.', 'teqcidb' );

        $empty_classes = 'teqcidb-email-log__empty';
        $empty_hidden  = '';

        if ( empty( $entries ) ) {
            $empty_classes .= ' is-visible';
        } else {
            $empty_hidden = ' hidden';
        }

        echo '<div class="teqcidb-communications teqcidb-communications--email-logs">';

        if ( ! $log_available ) {
            echo '<div class="notice notice-error inline"><p>' . esc_html( $not_available ) . '</p></div>';
        }

        echo '<div class="teqcidb-email-log">';
        echo '<p class="description">' . esc_html( $time_notice ) . '</p>';
        echo '<div id="teqcidb-email-log-list" class="teqcidb-email-log__list" data-empty-message="' . esc_attr( $empty_message ) . '">';
        echo '<p id="teqcidb-email-log-empty" class="' . esc_attr( $empty_classes ) . '"' . $empty_hidden . '>' . esc_html( $empty_message ) . '</p>';

        foreach ( $entries as $entry ) {
            $template_title   = isset( $entry['template_title'] ) ? trim( $entry['template_title'] ) : '';
            $template_id      = isset( $entry['template_id'] ) ? $entry['template_id'] : '';
            $template_display = $template_title;

            if ( '' === $template_display && isset( $entry['template_display'] ) ) {
                $template_display = trim( $entry['template_display'] );
            }

            if ( '' === $template_display ) {
                $template_display = $template_id ? $template_id : __( 'Email template', 'teqcidb' );
            }

            if ( $template_id && false === strpos( $template_display, $template_id ) ) {
                $template_display .= ' (' . $template_id . ')';
            }

            $time_display = isset( $entry['time_display'] ) ? $entry['time_display'] : '';
            $recipient    = isset( $entry['recipient'] ) ? $entry['recipient'] : '';
            $from_name    = isset( $entry['from_name'] ) ? $entry['from_name'] : '';
            $from_email   = isset( $entry['from_email'] ) ? $entry['from_email'] : '';
            $subject      = isset( $entry['subject'] ) ? $entry['subject'] : '';
            $context      = isset( $entry['context'] ) ? $entry['context'] : '';
            $triggered_by = isset( $entry['triggered_by'] ) ? $entry['triggered_by'] : '';
            $body         = isset( $entry['body'] ) ? $entry['body'] : '';

            echo '<article class="teqcidb-email-log__entry">';
            echo '<header class="teqcidb-email-log__header">';
            echo '<h3 class="teqcidb-email-log__title">' . esc_html( $template_display ) . '</h3>';

            if ( $time_display ) {
                printf(
                    '<p class="teqcidb-email-log__time">%s</p>',
                    esc_html( sprintf( $sent_format, $time_display ) )
                );
            }

            echo '</header>';

            $meta_items = array(
                array(
                    'label' => __( 'Sent (ET)', 'teqcidb' ),
                    'value' => $time_display,
                ),
                array(
                    'label' => __( 'Recipient', 'teqcidb' ),
                    'value' => $recipient,
                ),
                array(
                    'label' => __( 'From name', 'teqcidb' ),
                    'value' => $from_name,
                ),
                array(
                    'label' => __( 'From email', 'teqcidb' ),
                    'value' => $from_email,
                ),
                array(
                    'label' => __( 'Subject', 'teqcidb' ),
                    'value' => $subject,
                ),
            );

            if ( $template_id ) {
                $meta_items[] = array(
                    'label' => __( 'Template ID', 'teqcidb' ),
                    'value' => $template_id,
                );
            }

            if ( $context ) {
                $meta_items[] = array(
                    'label' => __( 'Context', 'teqcidb' ),
                    'value' => $context,
                );
            }

            if ( $triggered_by ) {
                $meta_items[] = array(
                    'label' => __( 'Initiated by', 'teqcidb' ),
                    'value' => $triggered_by,
                );
            }

            echo '<dl class="teqcidb-email-log__meta">';

            foreach ( $meta_items as $item ) {
                $label = isset( $item['label'] ) ? $item['label'] : '';
                $value = isset( $item['value'] ) ? $item['value'] : '';

                echo '<div class="teqcidb-email-log__meta-item">';
                echo '<dt>' . esc_html( $label ) . '</dt>';
                echo '<dd>' . esc_html( '' !== trim( $value ) ? $value : '—' ) . '</dd>';
                echo '</div>';
            }

            echo '</dl>';

            if ( '' !== $body ) {
                echo '<div class="teqcidb-email-log__body" aria-label="' . esc_attr__( 'Email body', 'teqcidb' ) . '">';
                echo wp_kses_post( nl2br( esc_html( $body ) ) );
                echo '</div>';
            } else {
                echo '<div class="teqcidb-email-log__body teqcidb-email-log__body--empty">' . esc_html( $body_empty ) . '</div>';
            }

            echo '</article>';
        }

        echo '</div>';

        $disabled_attr      = ' disabled="disabled" aria-disabled="true"';
        $clear_disabled    = $log_available ? '' : $disabled_attr;
        $download_disabled = $log_available ? '' : $disabled_attr;

        echo '<div class="teqcidb-email-log__actions">';
        echo '<button type="button" class="button button-secondary teqcidb-email-log__clear" data-spinner="#teqcidb-email-log-spinner" data-feedback="#teqcidb-email-log-feedback"' . $clear_disabled . '>' . esc_html( $clear_label ) . '</button>';
        echo '<form method="post" class="teqcidb-email-log__download" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        wp_nonce_field( 'teqcidb_download_email_log', 'teqcidb_email_log_nonce' );
        echo '<input type="hidden" name="action" value="teqcidb_download_email_log" />';
        echo '<button type="submit" class="button button-secondary"' . $download_disabled . '>' . esc_html( $download_label ) . '</button>';
        echo '</form>';
        echo '<span class="spinner teqcidb-email-log__spinner" id="teqcidb-email-log-spinner"></span>';
        echo '<p class="teqcidb-email-log__feedback" id="teqcidb-email-log-feedback" aria-live="polite"></p>';
        echo '</div>';

        echo '</div>';
        echo '</div>';
    }

    private function render_communications_placeholder_tab( $message ) {
        echo '<div class="teqcidb-communications teqcidb-communications--placeholder">';
        echo '<p>' . esc_html( $message ) . '</p>';
        echo '</div>';
    }

    private function get_sample_email_templates() {
        return array(
            array(
                'id'       => 'teqcidb-email-welcome',
                'title'    => __( 'Welcome Aboard', 'teqcidb' ),
                'tooltip'  => __( 'Sent after a customer signs up to introduce key onboarding steps.', 'teqcidb' ),
                'meta'     => array(
                    'trigger'            => __( 'New registration', 'teqcidb' ),
                    'communication_type' => __( 'External', 'teqcidb' ),
                    'category'           => __( 'Onboarding', 'teqcidb' ),
                ),
                'content'  => __( 'Test text', 'teqcidb' ),
            ),
            array(
                'id'       => 'teqcidb-email-follow-up',
                'title'    => __( 'Consultation Follow Up', 'teqcidb' ),
                'tooltip'  => __( 'Delivers recap notes and next steps after a discovery call wraps up.', 'teqcidb' ),
                'meta'     => array(
                    'trigger'            => __( 'Completed consultation', 'teqcidb' ),
                    'communication_type' => __( 'External', 'teqcidb' ),
                    'category'           => __( 'Sales Enablement', 'teqcidb' ),
                ),
                'content'  => __( 'Test text', 'teqcidb' ),
            ),
            array(
                'id'       => 'teqcidb-email-renewal',
                'title'    => __( 'Membership Renewal Reminder', 'teqcidb' ),
                'tooltip'  => __( 'Warns members that their plan expires soon and outlines renewal options.', 'teqcidb' ),
                'meta'     => array(
                    'trigger'            => __( 'Approaching renewal date', 'teqcidb' ),
                    'communication_type' => __( 'External', 'teqcidb' ),
                    'category'           => __( 'Retention', 'teqcidb' ),
                ),
                'content'  => __( 'Test text', 'teqcidb' ),
            ),
            array(
                'id'       => 'teqcidb-email-alert',
                'title'    => __( 'Internal Alert: Payment Review', 'teqcidb' ),
                'tooltip'  => __( 'Flags the support team when a payment requires manual approval.', 'teqcidb' ),
                'meta'     => array(
                    'trigger'            => __( 'Payment pending review', 'teqcidb' ),
                    'communication_type' => __( 'Internal', 'teqcidb' ),
                    'category'           => __( 'Operations', 'teqcidb' ),
                ),
                'content'  => __( 'Test text', 'teqcidb' ),
            ),
        );
    }

    public function enqueue_assets( $hook ) {
        if ( false === strpos( $hook, 'teqcidb' ) ) {
            return;
        }
        wp_enqueue_style( 'teqcidb-admin', TEQCIDB_PLUGIN_URL . 'assets/css/admin.css', array(), TEQCIDB_VERSION );
        wp_enqueue_script( 'teqcidb-admin', TEQCIDB_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-autocomplete' ), TEQCIDB_VERSION, true );
        wp_enqueue_media();
        wp_enqueue_editor();

        $placeholder_labels = $this->get_placeholder_labels();
        $field_definitions  = $this->prepare_student_fields_for_js();
        $student_history_definitions = $this->prepare_student_history_fields_for_js();
        $student_history_class_map = $this->get_student_history_class_map();
        $class_placeholder_labels = $this->get_class_placeholder_labels();
        $class_field_definitions  = $this->prepare_class_fields_for_js();

        wp_localize_script( 'teqcidb-admin', 'teqcidbAjax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'teqcidb_ajax_nonce' ),
        ) );
        wp_localize_script( 'teqcidb-admin', 'teqcidbAdmin', array(
            'placeholders' => array_values( $placeholder_labels ),
            'placeholderMap' => $placeholder_labels,
            'classPlaceholders' => array_values( $class_placeholder_labels ),
            'classPlaceholderMap' => $class_placeholder_labels,
            'delete'       => __( 'Delete', 'teqcidb' ),
            'none'         => __( 'No entries found.', 'teqcidb' ),
            'mediaTitle'   => __( 'Select Image', 'teqcidb' ),
            'mediaButton'  => __( 'Use this image', 'teqcidb' ),
            'itemPlaceholder' => '',
            'addAnotherItem' => __( '+ Add Another Item', 'teqcidb' ),
            'makeSelection' => __( 'Make a Selection...', 'teqcidb' ),
            'error'        => __( 'Something went wrong. Please try again.', 'teqcidb' ),
            'loadError'    => __( 'Unable to load records. Please try again.', 'teqcidb' ),
            'legacyUploadTypeRequired' => __( 'Select at least one upload type before submitting.', 'teqcidb' ),
            'legacyUploadSingleType'   => __( 'Please select only one upload type.', 'teqcidb' ),
            'totalRecords' => __( 'Total records: %s', 'teqcidb' ),
            'pageOf'       => __( 'Page %1$s of %2$s', 'teqcidb' ),
            'firstPage'    => __( 'First page', 'teqcidb' ),
            'prevPage'     => __( 'Previous page', 'teqcidb' ),
            'nextPage'     => __( 'Next page', 'teqcidb' ),
            'lastPage'     => __( 'Last page', 'teqcidb' ),
            'toggleDetails' => __( 'Toggle student details', 'teqcidb' ),
            'classToggleDetails' => __( 'Toggle class details', 'teqcidb' ),
            'nameLabel'    => __( 'Name', 'teqcidb' ),
            'studentNameLabel' => __( 'First & Last Name', 'teqcidb' ),
            'editAction'   => __( 'Edit', 'teqcidb' ),
            'saveChanges'  => __( 'Save Changes', 'teqcidb' ),
            'entityFields' => $field_definitions,
            'studentHistoryFields' => $student_history_definitions,
            'studentHistoryClassMap' => $student_history_class_map,
            'studentHistoryHeading' => __( 'Student History', 'teqcidb' ),
            /* translators: %s: history entry number */
            'studentHistoryEntryTitle' => __( 'History Entry %s', 'teqcidb' ),
            'studentHistoryNewEntryTitle' => __( 'New History Entry', 'teqcidb' ),
            'studentHistoryEmpty' => __( 'No history entries found for this student.', 'teqcidb' ),
            'studentHistoryAdd' => __( 'Add Student History Entry', 'teqcidb' ),
            'studentHistoryCreateNotice' => __( 'Please save or delete the new history entry.', 'teqcidb' ),
            'studentHistoryAddLabel' => __( 'Add This New Student History Record', 'teqcidb' ),
            'studentHistoryDuplicateNotice' => __( 'Duplicate records found. Click here to view them', 'teqcidb' ),
            'classFields'  => $class_field_definitions,
            'editorSettings' => $this->get_inline_editor_settings(),
            'previewEntity' => TEQCIDB_Student_Helper::get_first_preview_data(),
            'previewEmptyMessage' => __( 'Enter a subject or body to generate the preview.', 'teqcidb' ),
            'previewUnavailableMessage' => __( 'Add a student entry to generate a preview.', 'teqcidb' ),
            'testEmailRequired' => __( 'Enter an email address before sending a test.', 'teqcidb' ),
            'testEmailSuccess'  => __( 'Test email sent.', 'teqcidb' ),
            'emailLogCleared'   => __( 'Email log cleared.', 'teqcidb' ),
            'emailLogError'     => __( 'Unable to clear the email log. Please try again.', 'teqcidb' ),
            'emailLogEmpty'     => __( 'No email activity has been recorded yet.', 'teqcidb' ),
            'logDownloadReady'  => __( 'Log download ready.', 'teqcidb' ),
            'searchFiltersApplied' => __( 'Showing filtered results.', 'teqcidb' ),
            'classSearchFiltersApplied' => __( 'Showing filtered class results.', 'teqcidb' ),
            'studentSearchMinLength' => __( 'Type at least two characters to search students.', 'teqcidb' ),
            'studentSearchPlaceholder' => __( 'Start typing a name or email...', 'teqcidb' ),
            'studentSearchNoResults' => __( 'No matching students found.', 'teqcidb' ),
            'classEditHeading' => __( 'Edit Class Details', 'teqcidb' ),
            'resourceNameLabel' => __( 'Resource Name', 'teqcidb' ),
            'resourceTypeLabel' => __( 'Resource Type', 'teqcidb' ),
            'resourceUrlLabel' => __( 'Resource URL', 'teqcidb' ),
            'resourceTypePdf'   => __( 'PDF', 'teqcidb' ),
            'resourceTypeVideo' => __( 'Video', 'teqcidb' ),
            'resourceTypeExternalLink' => __( 'External Link', 'teqcidb' ),
            'quizQuestionSaved' => __( 'Question saved.', 'teqcidb' ),
            'quizQuestionDeletePending' => __( 'Delete functionality is coming soon.', 'teqcidb' ),
            'quizQuestionUnsupportedType' => __( 'Saving this question type is coming soon.', 'teqcidb' ),
            'quizQuestionAnswerRequired' => __( 'Select True or False before saving this question.', 'teqcidb' ),
            'quizQuestionMultiSelectOptionRequired' => __( 'Add at least one answer option before saving this question.', 'teqcidb' ),
            'quizQuestionMultipleChoiceSingleTrue' => __( 'Set exactly one answer option to True for a multiple choice question.', 'teqcidb' ),
            'quizQuestionOptionPlaceholder' => __( 'Enter answer option text…', 'teqcidb' ),
            'quizQuestionOptionCorrectLabel' => __( 'Select whether this answer option is correct', 'teqcidb' ),
            'trueLabel' => __( 'True', 'teqcidb' ),
            'falseLabel' => __( 'False', 'teqcidb' ),
        ) );
    }

    private function get_placeholder_labels() {
        static $labels = null;

        if ( null === $labels ) {
            $defaults = array(
                'placeholder_1' => __( 'Student Name', 'teqcidb' ),
                'placeholder_2' => __( 'Email Address', 'teqcidb' ),
                'placeholder_3' => __( 'Company', 'teqcidb' ),
                'placeholder_4' => __( 'Cell Phone', 'teqcidb' ),
                'placeholder_5' => __( 'Certification Expiration', 'teqcidb' ),
            );

            for ( $i = 1; $i <= 28; $i++ ) {
                $key = 'placeholder_' . $i;

                if ( ! isset( $defaults[ $key ] ) ) {
                    $defaults[ $key ] = sprintf( __( 'Placeholder %d', 'teqcidb' ), $i );
                }
            }

            /**
             * Allow customizing placeholder labels across the admin experience when cloning the plugin.
             *
             * Updating this filter ensures the edit table, creation form, and localized JavaScript
             * all stay in sync when Placeholder 1 becomes "Resource Name", "Student Name", etc.
             *
             * @param array $labels Associative array of placeholder slugs to labels.
             */
            $labels = apply_filters( 'teqcidb_students_placeholder_labels', $defaults );

            $labels = $this->sanitize_placeholder_label_map( $labels );
        }

        return $labels;
    }

    private function get_class_placeholder_labels() {
        static $labels = null;

        if ( null === $labels ) {
            $defaults = array(
                'placeholder_1' => __( 'Class Name', 'teqcidb' ),
                'placeholder_2' => __( 'Format', 'teqcidb' ),
                'placeholder_3' => __( 'Type', 'teqcidb' ),
                'placeholder_4' => __( 'Start Date', 'teqcidb' ),
                'placeholder_5' => __( 'Cost', 'teqcidb' ),
            );

            $labels = apply_filters( 'teqcidb_classes_placeholder_labels', $defaults );

            $labels = $this->sanitize_placeholder_label_map( $labels );
        }

        return $labels;
    }

    private function sanitize_placeholder_label_map( array $labels ) {
        $sanitized = array();

        foreach ( $labels as $key => $label ) {
            if ( ! is_scalar( $label ) ) {
                continue;
            }

            $normalized = sanitize_text_field( wp_unslash( (string) $label ) );

            if ( '' === $normalized && preg_match( '/^placeholder_(\d+)$/', (string) $key, $matches ) ) {
                $normalized = sprintf( __( 'Placeholder %d', 'teqcidb' ), (int) $matches[1] );
            }

            $sanitized[ $key ] = wp_specialchars_decode( $normalized, ENT_QUOTES );
        }

        return array_merge( $labels, $sanitized );
    }

    private function get_placeholder_label( $index ) {
        $labels = $this->get_placeholder_labels();
        $key    = 'placeholder_' . absint( $index );

        if ( isset( $labels[ $key ] ) ) {
            return $labels[ $key ];
        }

        return sprintf( __( 'Placeholder %d', 'teqcidb' ), absint( $index ) );
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

    private function get_tooltips() {
        return array(
            'first_name'                    => __( 'The student\'s legal first name for rosters and certificates.', 'teqcidb' ),
            'last_name'                     => __( 'The student\'s legal last name for rosters and certificates.', 'teqcidb' ),
            'company'                       => __( 'Company the student is currently associated with.', 'teqcidb' ),
            'old_companies'                 => __( 'List any previous companies to make historical lookups easier.', 'teqcidb' ),
            'student_address_street_1'      => __( 'Primary street address for the student or their company.', 'teqcidb' ),
            'student_address_street_2'      => __( 'Additional address information such as suite or floor.', 'teqcidb' ),
            'student_address_city'          => __( 'City associated with the student\'s current location.', 'teqcidb' ),
            'student_address_state'         => __( 'State or province associated with the student\'s current location.', 'teqcidb' ),
            'student_address_postal_code'   => __( 'ZIP or postal code that matches the student address.', 'teqcidb' ),
            'phone_cell'                    => __( 'Direct cell phone number for notifications or follow-up.', 'teqcidb' ),
            'phone_office'                  => __( 'Office or main line tied to the student\'s organization.', 'teqcidb' ),
            'fax'                           => __( 'Fax number for the student or their organization, if applicable.', 'teqcidb' ),
            'email'                         => __( 'This will also become the WordPress user email, so it must be unique.', 'teqcidb' ),
            'initial_training_date'         => __( 'Latest completed Initial class date.', 'teqcidb' ),
            'last_refresher_date'           => __( 'Latest completed Refresher class date.', 'teqcidb' ),
            'is_a_representative'           => __( 'Mark “Yes” when this student can manage other students.', 'teqcidb' ),
            'representative_first_name'     => __( 'Representative first name if someone manages this student.', 'teqcidb' ),
            'representative_last_name'      => __( 'Representative last name if someone manages this student.', 'teqcidb' ),
            'representative_email'          => __( 'Representative email for coordination.', 'teqcidb' ),
            'representative_phone'          => __( 'Representative phone number.', 'teqcidb' ),
            'associations'                  => __( 'Select the associations (AAPA, ARBA, AGC, ABC, AUCA) tied to this student.', 'teqcidb' ),
            'expiration_date'               => __( 'Certification expiration date (typically one year from the last class).', 'teqcidb' ),
            'qcinumber'                     => __( 'QCI number assigned to the student once certified.', 'teqcidb' ),
            'comments'                      => __( 'Private notes for admins—never shown to students.', 'teqcidb' ),
        );
    }

    private function get_class_tooltips() {
        return array(
            'classname'                 => __( 'Display name for the training session or course.', 'teqcidb' ),
            'classformat'               => __( 'Format of the session, such as in person, virtual, or hybrid.', 'teqcidb' ),
            'classtype'                 => __( 'Training type (Initial, Refresher, or other internal labels).', 'teqcidb' ),
            'classsize'                 => __( 'Maximum seats available for this class.', 'teqcidb' ),
            'instructors'               => __( 'List each instructor who will lead or assist with the class.', 'teqcidb' ),
            'class_address_street_1'    => __( 'Primary street address for the class location.', 'teqcidb' ),
            'class_address_street_2'    => __( 'Additional address information such as suite or floor.', 'teqcidb' ),
            'class_address_city'        => __( 'City where the class will be held.', 'teqcidb' ),
            'class_address_state'       => __( 'State or territory for the class location.', 'teqcidb' ),
            'class_address_postal_code' => __( 'ZIP or postal code for the class location.', 'teqcidb' ),
            'classstartdate'            => __( 'Date the class begins.', 'teqcidb' ),
            'classstarttime'            => __( 'Local start time for the session.', 'teqcidb' ),
            'classendtime'              => __( 'Local end time for the session.', 'teqcidb' ),
            'classcost'                 => __( 'Tuition or registration fee for the full class.', 'teqcidb' ),
            'classdescription'          => __( 'Agenda, prerequisites, or any notes students should know.', 'teqcidb' ),
            'teamslink'                 => __( 'Microsoft Teams meeting URL students should use for this class.', 'teqcidb' ),
            'classurl'                  => __( 'Virtual class page URL reserved for this class record.', 'teqcidb' ),
            'classhide'                 => __( 'Hide this class from public listings when set to Yes.', 'teqcidb' ),
            'allallowedcourse'          => __( 'Whether all enrolled students can access the course content by default.', 'teqcidb' ),
            'allallowedquiz'            => __( 'Whether all enrolled students can access the quiz content by default.', 'teqcidb' ),
            'coursestudentsallowed'     => __( 'Specific students who should be allowed to access the course even if access is blocked globally.', 'teqcidb' ),
            'quizstudentsallowed'       => __( 'Specific students who should be allowed to access the quiz even if access is blocked globally.', 'teqcidb' ),
            'coursestudentsrestricted'  => __( 'Students who should be blocked from this course even when course access is generally allowed.', 'teqcidb' ),
            'quizstudentsrestricted'    => __( 'Students who should be blocked from this quiz or exam even when quiz access is generally allowed.', 'teqcidb' ),
            'resource_name'             => __( 'The name of this class resource item.', 'teqcidb' ),
            'resource_type'             => __( 'The type of resource, such as PDF, video, or external link.', 'teqcidb' ),
            'resource_url'              => __( 'Direct URL associated with this resource item.', 'teqcidb' ),
        );
    }

    private function get_quiz_tooltips() {
        return array(
            'name'     => __( 'Internal quiz name shown to admins while managing quizzes and questions.', 'teqcidb' ),
            'class_id' => __( 'Select every class that should use this quiz. Selected IDs are saved as a comma-separated list in teqcidb_quizzes.class_id.', 'teqcidb' ),
        );
    }

    private function render_tab_intro( $title, $description ) {
        if ( empty( $title ) && empty( $description ) ) {
            return;
        }

        echo '<div class="teqcidb-tab-intro">';

        if ( $title ) {
            echo '<h2 class="teqcidb-tab-intro__title">' . esc_html( $title ) . '</h2>';
        }

        if ( $description ) {
            echo '<p class="teqcidb-tab-intro__description">' . esc_html( $description ) . '</p>';
        }

        echo '</div>';
    }

    private function render_logging_status_notice( $channel ) {
        if ( ! class_exists( 'TEQCIDB_Settings_Helper' ) ) {
            return;
        }

        if ( ! $channel ) {
            return;
        }

        $enabled = TEQCIDB_Settings_Helper::is_logging_enabled( $channel );

        $status_class = $enabled ? 'teqcidb-log-status--enabled' : 'teqcidb-log-status--disabled';
        $link         = add_query_arg(
            array(
                'page' => 'teqcidb-settings',
                'tab'  => 'general',
            ),
            admin_url( 'admin.php' )
        );

        $link_markup = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( $link ),
            esc_html__( 'Visit the TEQCIDB Settings page', 'teqcidb' )
        );

        if ( $enabled ) {
            /* translators: %s: Link to the TEQCIDB general settings tab. */
            $message = sprintf(
                __( 'Logging is currently enabled. %s to change this preference.', 'teqcidb' ),
                $link_markup
            );
            $indicator_label = __( 'Logging enabled', 'teqcidb' );
        } else {
            /* translators: %s: Link to the TEQCIDB general settings tab. */
            $message = sprintf(
                __( 'Logging is currently disabled. %s to change this preference.', 'teqcidb' ),
                $link_markup
            );
            $indicator_label = __( 'Logging disabled', 'teqcidb' );
        }

        echo '<div class="teqcidb-log-status ' . esc_attr( $status_class ) . '">';
        printf(
            '<span class="teqcidb-log-status__indicator" role="img" aria-label="%s"></span>',
            esc_attr( $indicator_label )
        );
        printf(
            '<p class="teqcidb-log-status__message">%s</p>',
            wp_kses_post( $message )
        );
        echo '</div>';
    }

    public function render_classes_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'create';
        echo '<div class="wrap"><h1>' . esc_html__( 'Classes', 'teqcidb' ) . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=teqcidb-classes&tab=create" class="nav-tab ' . ( 'create' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Create a Class', 'teqcidb' ) . '</a>';
        echo '<a href="?page=teqcidb-classes&tab=edit" class="nav-tab ' . ( 'edit' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Edit & Manage Classes', 'teqcidb' ) . '</a>';
        echo '</h2>';

        $tab_titles = array(
            'create' => __( 'Create a Class', 'teqcidb' ),
            'edit'   => __( 'Edit & Manage Classes', 'teqcidb' ),
        );

        $tab_descriptions = array(
            'create' => __( 'Outline the logistics, schedule, and attendee rules for a new training session.', 'teqcidb' ),
            'edit'   => __( 'Review upcoming or past sessions to adjust details and keep rosters accurate.', 'teqcidb' ),
        );

        if ( ! array_key_exists( $active_tab, $tab_titles ) ) {
            $active_tab = 'create';
        }

        $title       = isset( $tab_titles[ $active_tab ] ) ? $tab_titles[ $active_tab ] : '';
        $description = isset( $tab_descriptions[ $active_tab ] ) ? $tab_descriptions[ $active_tab ] : '';

        $this->render_tab_intro( $title, $description );

        if ( 'edit' === $active_tab ) {
            $this->render_class_edit_tab();
        } else {
            $this->render_class_create_tab();
        }

        echo '</div>';
    }

    public function render_student_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'create';
        echo '<div class="wrap"><h1>' . esc_html__( 'Students', 'teqcidb' ) . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=teqcidb-student&tab=create" class="nav-tab ' . ( 'create' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Create a Student', 'teqcidb' ) . '</a>';
        echo '<a href="?page=teqcidb-student&tab=edit" class="nav-tab ' . ( 'edit' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Edit Students', 'teqcidb' ) . '</a>';
        echo '</h2>';
        $tab_titles = array(
            'create' => __( 'Create a Student', 'teqcidb' ),
            'edit'   => __( 'Edit Students', 'teqcidb' ),
        );

        $tab_descriptions = array(
            'create' => __( 'Capture the student\'s profile, contact, and certification details before saving.', 'teqcidb' ),
            'edit'   => __( 'Review saved students to confirm their data, trigger edits, or remove records you no longer need.', 'teqcidb' ),
        );

        if ( ! array_key_exists( $active_tab, $tab_titles ) ) {
            $active_tab = 'create';
        }

        $title       = isset( $tab_titles[ $active_tab ] ) ? $tab_titles[ $active_tab ] : '';
        $description = isset( $tab_descriptions[ $active_tab ] ) ? $tab_descriptions[ $active_tab ] : '';

        $this->render_tab_intro( $title, $description );

        if ( 'edit' === $active_tab ) {
            $this->render_edit_tab();
        } else {
            $this->render_create_tab();
        }

        echo '</div>';
    }


    public function render_quizzes_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'create';
        echo '<div class="wrap"><h1>' . esc_html__( 'Quizzes', 'teqcidb' ) . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=teqcidb-quizzes&tab=create" class="nav-tab ' . ( 'create' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Create a Quiz', 'teqcidb' ) . '</a>';
        echo '<a href="?page=teqcidb-quizzes&tab=edit" class="nav-tab ' . ( 'edit' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Edit Quizzes', 'teqcidb' ) . '</a>';
        echo '</h2>';

        $tab_titles = array(
            'create' => __( 'Create a Quiz', 'teqcidb' ),
            'edit'   => __( 'Edit Quizzes', 'teqcidb' ),
        );

        $tab_descriptions = array(
            'create' => __( 'Define the quiz record metadata now so questions and delivery workflows can be connected in upcoming updates.', 'teqcidb' ),
            'edit'   => __( 'Review existing quizzes and open each record for future editing and question-management actions.', 'teqcidb' ),
        );

        if ( ! array_key_exists( $active_tab, $tab_titles ) ) {
            $active_tab = 'create';
        }

        $title       = isset( $tab_titles[ $active_tab ] ) ? $tab_titles[ $active_tab ] : '';
        $description = isset( $tab_descriptions[ $active_tab ] ) ? $tab_descriptions[ $active_tab ] : '';

        $this->render_quiz_top_message();

        $this->render_tab_intro( $title, $description );

        if ( 'edit' === $active_tab ) {
            $this->render_quiz_edit_tab();
        } else {
            $this->render_quiz_create_tab();
        }

        echo '</div>';
    }

    private function render_quiz_top_message() {
        $message_key = isset( $_GET['teqcidb_quiz_message'] ) ? sanitize_key( wp_unslash( $_GET['teqcidb_quiz_message'] ) ) : '';

        if ( '' === $message_key ) {
            return;
        }

        $messages = array(
            'created' => array(
                'class' => 'notice notice-success is-dismissible teqcidb-top-message',
                'text'  => __( 'Quiz saved successfully.', 'teqcidb' ),
            ),
            'missing_name' => array(
                'class' => 'notice notice-error is-dismissible teqcidb-top-message',
                'text'  => __( 'Please enter a quiz name before saving.', 'teqcidb' ),
            ),
            'missing_classes' => array(
                'class' => 'notice notice-error is-dismissible teqcidb-top-message',
                'text'  => __( 'Please select at least one related class.', 'teqcidb' ),
            ),
            'save_failed' => array(
                'class' => 'notice notice-error is-dismissible teqcidb-top-message',
                'text'  => __( 'Unable to save this quiz right now. Please try again.', 'teqcidb' ),
            ),
            'updated' => array(
                'class' => 'notice notice-success is-dismissible teqcidb-top-message',
                'text'  => __( 'Quiz updated successfully.', 'teqcidb' ),
            ),
            'missing_quiz' => array(
                'class' => 'notice notice-error is-dismissible teqcidb-top-message',
                'text'  => __( 'Please choose a valid quiz before saving changes.', 'teqcidb' ),
            ),
            'update_failed' => array(
                'class' => 'notice notice-error is-dismissible teqcidb-top-message',
                'text'  => __( 'Unable to update this quiz right now. Please try again.', 'teqcidb' ),
            ),
        );

        if ( ! isset( $messages[ $message_key ] ) ) {
            return;
        }

        $message = $messages[ $message_key ];

        echo '<div class="' . esc_attr( $message['class'] ) . '"><p>' . esc_html( $message['text'] ) . '</p></div>';
    }

    private function get_student_fields() {
        $tooltips = $this->get_tooltips();
        $yes_no   = array(
            ''  => __( 'Make a Selection...', 'teqcidb' ),
            '0' => __( 'No', 'teqcidb' ),
            '1' => __( 'Yes', 'teqcidb' ),
        );

        return array(
            array(
                'name'    => 'first_name',
                'label'   => __( 'First Name', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['first_name'],
            ),
            array(
                'name'    => 'last_name',
                'label'   => __( 'Last Name', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['last_name'],
            ),
            array(
                'name'    => 'email',
                'label'   => __( 'Email Address', 'teqcidb' ),
                'type'    => 'email',
                'tooltip' => $tooltips['email'],
            ),
            array(
                'name'    => 'company',
                'label'   => __( 'Current Company', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['company'],
            ),
            array(
                'name'       => 'old_companies',
                'label'      => __( 'Previous Companies', 'teqcidb' ),
                'type'       => 'items',
                'tooltip'    => $tooltips['old_companies'],
            ),
            array(
                'name'       => 'student_address_street_1',
                'label'      => __( 'Address Line 1', 'teqcidb' ),
                'type'       => 'text',
                'tooltip'    => $tooltips['student_address_street_1'],
            ),
            array(
                'name'       => 'student_address_street_2',
                'label'      => __( 'Address Line 2', 'teqcidb' ),
                'type'       => 'text',
                'tooltip'    => $tooltips['student_address_street_2'],
            ),
            array(
                'name'    => 'student_address_city',
                'label'   => __( 'City', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['student_address_city'],
            ),
            array(
                'name'    => 'student_address_state',
                'label'   => __( 'State', 'teqcidb' ),
                'type'    => 'state',
                'options' => $this->get_us_states_and_territories(),
                'tooltip' => $tooltips['student_address_state'],
            ),
            array(
                'name'    => 'student_address_postal_code',
                'label'   => __( 'Zip Code', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['student_address_postal_code'],
            ),
            array(
                'name'    => 'phone_cell',
                'label'   => __( 'Cell Phone', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['phone_cell'],
            ),
            array(
                'name'    => 'phone_office',
                'label'   => __( 'Office Phone', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['phone_office'],
            ),
            array(
                'name'    => 'fax',
                'label'   => __( 'Fax', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['fax'],
            ),
            array(
                'name'    => 'initial_training_date',
                'label'   => __( 'Initial Training Date', 'teqcidb' ),
                'type'    => 'date',
                'tooltip' => $tooltips['initial_training_date'],
            ),
            array(
                'name'    => 'last_refresher_date',
                'label'   => __( 'Last Refresher Date', 'teqcidb' ),
                'type'    => 'date',
                'tooltip' => $tooltips['last_refresher_date'],
            ),
            array(
                'name'    => 'is_a_representative',
                'label'   => __( 'Is this Student also a Representative?', 'teqcidb' ),
                'type'    => 'select',
                'options' => $yes_no,
                'tooltip' => $tooltips['is_a_representative'],
            ),
            array(
                'name'    => 'representative_first_name',
                'label'   => __( 'Representative First Name', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['representative_first_name'],
            ),
            array(
                'name'    => 'representative_last_name',
                'label'   => __( 'Representative Last Name', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['representative_last_name'],
            ),
            array(
                'name'    => 'representative_email',
                'label'   => __( 'Representative Email', 'teqcidb' ),
                'type'    => 'email',
                'tooltip' => $tooltips['representative_email'],
            ),
            array(
                'name'    => 'representative_phone',
                'label'   => __( 'Representative Phone', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['representative_phone'],
            ),
            array(
                'name'    => 'associations',
                'label'   => __( 'Associations', 'teqcidb' ),
                'type'    => 'checkboxes',
                'tooltip' => $tooltips['associations'],
                'options' => array(
                    'AAPA' => __( 'AAPA', 'teqcidb' ),
                    'ARBA' => __( 'ARBA', 'teqcidb' ),
                    'AGC'  => __( 'AGC', 'teqcidb' ),
                    'ABC'  => __( 'ABC', 'teqcidb' ),
                    'AUCA' => __( 'AUCA', 'teqcidb' ),
                ),
            ),
            array(
                'name'    => 'expiration_date',
                'label'   => __( 'Expiration Date', 'teqcidb' ),
                'type'    => 'date',
                'tooltip' => $tooltips['expiration_date'],
            ),
            array(
                'name'    => 'qcinumber',
                'label'   => __( 'QCI Number', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['qcinumber'],
            ),
            array(
                'name'       => 'comments',
                'label'      => __( 'Admin Comments', 'teqcidb' ),
                'type'       => 'textarea',
                'full_width' => true,
                'tooltip'    => $tooltips['comments'],
                'attrs'      => ' rows="4"',
            ),
        );
    }


    private function get_quiz_fields() {
        $tooltips = $this->get_quiz_tooltips();

        return array(
            array(
                'name'    => 'name',
                'label'   => __( 'Quiz Name', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['name'],
            ),
            array(
                'name'       => 'class_id',
                'label'      => __( 'Related Classes', 'teqcidb' ),
                'type'       => 'checkboxes',
                'full_width' => true,
                'options'    => $this->get_quiz_class_options(),
                'tooltip'    => $tooltips['class_id'],
            ),
        );
    }

    private function get_class_fields( $context = 'all' ) {
        $tooltips = $this->get_class_tooltips();
        $yes_no   = array(
            ''  => __( 'Make a Selection...', 'teqcidb' ),
            '0' => __( 'No', 'teqcidb' ),
            '1' => __( 'Yes', 'teqcidb' ),
        );

        $formats = array(
            ''          => __( 'Make a Selection...', 'teqcidb' ),
            'in_person' => __( 'In Person', 'teqcidb' ),
            'virtual'   => __( 'Virtual', 'teqcidb' ),
            'hybrid'    => __( 'Hybrid', 'teqcidb' ),
        );

        $class_types = array(
            ''          => __( 'Make a Selection...', 'teqcidb' ),
            'initial'   => __( 'Initial', 'teqcidb' ),
            'refresher' => __( 'Refresher', 'teqcidb' ),
            'other'     => __( 'Other', 'teqcidb' ),
        );

        $access_options = array(
            ''         => __( 'Make a Selection...', 'teqcidb' ),
            'allowed'  => __( 'Access Allowed', 'teqcidb' ),
            'blocked'  => __( 'Access Blocked', 'teqcidb' ),
        );

        $fields = array(
            array(
                'name'    => 'classname',
                'label'   => __( 'Class Name', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['classname'],
            ),
            array(
                'name'    => 'classformat',
                'label'   => __( 'Class Format', 'teqcidb' ),
                'type'    => 'select',
                'options' => $formats,
                'tooltip' => $tooltips['classformat'],
            ),
            array(
                'name'    => 'classtype',
                'label'   => __( 'Class Type', 'teqcidb' ),
                'type'    => 'select',
                'options' => $class_types,
                'tooltip' => $tooltips['classtype'],
            ),
            array(
                'name'    => 'classsize',
                'label'   => __( 'Maximum Class Size', 'teqcidb' ),
                'type'    => 'number',
                'attrs'   => ' min="0" step="1"',
                'tooltip' => $tooltips['classsize'],
            ),
            array(
                'name'    => 'class_address_street_1',
                'label'   => __( 'Address Line 1', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['class_address_street_1'],
            ),
            array(
                'name'    => 'class_address_street_2',
                'label'   => __( 'Address Line 2', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['class_address_street_2'],
            ),
            array(
                'name'    => 'class_address_city',
                'label'   => __( 'City', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['class_address_city'],
            ),
            array(
                'name'    => 'class_address_state',
                'label'   => __( 'State', 'teqcidb' ),
                'type'    => 'state',
                'options' => $this->get_us_states_and_territories(),
                'tooltip' => $tooltips['class_address_state'],
            ),
            array(
                'name'    => 'class_address_postal_code',
                'label'   => __( 'Zip Code', 'teqcidb' ),
                'type'    => 'text',
                'tooltip' => $tooltips['class_address_postal_code'],
            ),
            array(
                'name'    => 'classstartdate',
                'label'   => __( 'Class Start Date', 'teqcidb' ),
                'type'    => 'date',
                'tooltip' => $tooltips['classstartdate'],
            ),
            array(
                'name'    => 'classstarttime',
                'label'   => __( 'Class Start Time', 'teqcidb' ),
                'type'    => 'time',
                'tooltip' => $tooltips['classstarttime'],
            ),
            array(
                'name'    => 'classendtime',
                'label'   => __( 'Class End Time', 'teqcidb' ),
                'type'    => 'time',
                'tooltip' => $tooltips['classendtime'],
            ),
            array(
                'name'    => 'classcost',
                'label'   => __( 'Class Cost', 'teqcidb' ),
                'type'    => 'number',
                'attrs'   => ' min="0" step="0.01"',
                'tooltip' => $tooltips['classcost'],
            ),
            array(
                'name'    => 'teamslink',
                'label'   => __( 'Teams Link', 'teqcidb' ),
                'type'    => 'url',
                'tooltip' => $tooltips['teamslink'],
            ),
            array(
                'name'      => 'classurl',
                'label'     => __( 'Class URL', 'teqcidb' ),
                'type'      => 'url',
                'attrs'     => ' readonly="readonly"',
                'tooltip'   => $tooltips['classurl'],
                'label_link'=> '#',
            ),
            array(
                'name'    => 'classhide',
                'label'   => __( 'Hide this Class?', 'teqcidb' ),
                'type'    => 'select',
                'options' => $yes_no,
                'tooltip' => $tooltips['classhide'],
            ),
            array(
                'name'    => 'allallowedcourse',
                'label'   => __( 'Allow all students to access the associated Course?', 'teqcidb' ),
                'type'    => 'select',
                'options' => $access_options,
                'tooltip' => $tooltips['allallowedcourse'],
            ),
            array(
                'name'    => 'allallowedquiz',
                'label'   => __( 'Allow all students to access the associated Quiz?', 'teqcidb' ),
                'type'    => 'select',
                'options' => $access_options,
                'tooltip' => $tooltips['allallowedquiz'],
            ),
            array(
                'name'       => 'classdescription',
                'label'      => __( 'Class Description', 'teqcidb' ),
                'type'       => 'textarea',
                'tooltip'    => $tooltips['classdescription'],
                'full_width' => true,
                'attrs'      => ' rows="4"',
            ),
            array(
                'name'    => 'coursestudentsallowed',
                'label'   => __( 'Allow specific students to access the associated Course?', 'teqcidb' ),
                'type'    => 'items',
                'tooltip' => $tooltips['coursestudentsallowed'],
                'autocomplete' => 'student',
            ),
            array(
                'name'    => 'quizstudentsallowed',
                'label'   => __( 'Allow specific students to access the associated Quiz?', 'teqcidb' ),
                'type'    => 'items',
                'tooltip' => $tooltips['quizstudentsallowed'],
                'autocomplete' => 'student',
            ),
            array(
                'name'    => 'coursestudentsrestricted',
                'label'   => __( 'Restrict certain students from accessing the associated Course?', 'teqcidb' ),
                'type'    => 'items',
                'tooltip' => $tooltips['coursestudentsrestricted'],
                'autocomplete' => 'student',
            ),
            array(
                'name'    => 'quizstudentsrestricted',
                'label'   => __( 'Restrict certain students from accessing the associated Quiz?', 'teqcidb' ),
                'type'    => 'items',
                'tooltip' => $tooltips['quizstudentsrestricted'],
                'autocomplete' => 'student',
            ),
            array(
                'name'    => 'instructors',
                'label'   => __( 'Instructors', 'teqcidb' ),
                'type'    => 'items',
                'tooltip' => $tooltips['instructors'],
            ),
            array(
                'name'       => 'classresources',
                'label'      => __( 'Class Resources', 'teqcidb' ),
                'type'       => 'resource_rows',
                'tooltip'    => __( 'Resource rows for this class, including name, type, and URL.', 'teqcidb' ),
                'full_width' => true,
                'resource_tooltips' => array(
                    'name' => $tooltips['resource_name'],
                    'type' => $tooltips['resource_type'],
                    'url'  => $tooltips['resource_url'],
                ),
            ),
        );


        if ( 'create' === $context ) {
            $fields = array_values(
                array_filter(
                    $fields,
                    function( $field ) {
                        return ! isset( $field['name'] ) || 'classurl' !== $field['name'];
                    }
                )
            );
        }

        return $fields;
    }

    private function prepare_student_fields_for_js() {
        $fields    = $this->get_student_fields();
        $prepared  = array();

        foreach ( $fields as $field ) {
            $prepared_field = array(
                'name'      => $field['name'],
                'type'      => $field['type'],
                'label'     => $field['label'],
                'tooltip'   => $field['tooltip'],
                'fullWidth' => ! empty( $field['full_width'] ),
            );

            if ( isset( $field['options'] ) ) {
                $prepared_field['options'] = $field['options'];
            }

            if ( isset( $field['attrs'] ) ) {
                $prepared_field['attrs'] = $field['attrs'];
            }

            $prepared[] = $prepared_field;
        }

        return $prepared;
    }

    private function get_student_history_fields() {
        $class_options = $this->get_class_name_options();

        return array(
            array(
                'name'  => 'classname',
                'label' => __( 'Class Name', 'teqcidb' ),
                'type'  => 'select',
                'options' => $class_options,
            ),
            array(
                'name'  => 'classdate',
                'label' => __( 'Class Date', 'teqcidb' ),
                'type'  => 'date',
                'attrs' => ' disabled="disabled"',
            ),
            array(
                'name'  => 'classtype',
                'label' => __( 'Class Type', 'teqcidb' ),
                'type'  => 'select',
                'options' => array(
                    'Initial'   => __( 'Initial', 'teqcidb' ),
                    'Refresher' => __( 'Refresher', 'teqcidb' ),
                    'Hybrid'    => __( 'Hybrid', 'teqcidb' ),
                    'Other'     => __( 'Other', 'teqcidb' ),
                ),
                'attrs' => ' disabled="disabled"',
            ),
            array(
                'name'  => 'registered',
                'label' => __( 'Registered?', 'teqcidb' ),
                'type'  => 'select',
                'options' => array(
                    'Yes'     => __( 'Yes', 'teqcidb' ),
                    'No'      => __( 'No', 'teqcidb' ),
                    'Pending' => __( 'Pending', 'teqcidb' ),
                ),
            ),
            array(
                'name'  => 'adminapproved',
                'label' => __( 'Admin Approved?', 'teqcidb' ),
                'type'  => 'select',
                'options' => array(
                    'Yes'             => __( 'Yes', 'teqcidb' ),
                    'No'              => __( 'No', 'teqcidb' ),
                    'Pending Approval' => __( 'Pending Approval', 'teqcidb' ),
                ),
            ),
            array(
                'name'  => 'attended',
                'label' => __( 'Attended This Class?', 'teqcidb' ),
                'type'  => 'select',
                'options' => array(
                    'Upcoming' => __( 'Class is Upcoming', 'teqcidb' ),
                    'Yes'      => __( 'Yes', 'teqcidb' ),
                    'No'       => __( 'No', 'teqcidb' ),
                ),
            ),
            array(
                'name'  => 'outcome',
                'label' => __( 'Class Outcome', 'teqcidb' ),
                'type'  => 'select',
                'options' => array(
                    'Upcoming' => __( 'Class is Upcoming', 'teqcidb' ),
                    'Passed'   => __( 'Passed', 'teqcidb' ),
                    'Failed'   => __( 'Failed', 'teqcidb' ),
                    'Deferred/Delayed' => __( 'Deferred/Delayed', 'teqcidb' ),
                ),
            ),
            array(
                'name'  => 'paymentstatus',
                'label' => __( 'Payment Status', 'teqcidb' ),
                'type'  => 'select',
                'options' => array(
                    'Payment Pending' => __( 'Payment Pending', 'teqcidb' ),
                    'Paid in Full' => __( 'Paid in Full', 'teqcidb' ),
                    'No Payment Made' => __( 'No Payment Made', 'teqcidb' ),
                    'Payment Waived' => __( 'Payment Waived', 'teqcidb' ),
                ),
            ),
            array(
                'name'  => 'amountpaid',
                'label' => __( 'Amount Paid', 'teqcidb' ),
                'type'  => 'text',
                'attrs' => ' inputmode="decimal"',
            ),
            array(
                'name'  => 'enrollmentdate',
                'label' => __( 'Enrollment Date', 'teqcidb' ),
                'type'  => 'date',
            ),
            array(
                'name'  => 'courseinprogress',
                'label' => __( 'Course In Progress?', 'teqcidb' ),
                'type'  => 'select',
                'options' => array(
                    'Yes' => __( 'Yes', 'teqcidb' ),
                    'No'  => __( 'No', 'teqcidb' ),
                ),
            ),
            array(
                'name'  => 'quizinprogress',
                'label' => __( 'Quiz In Progress?', 'teqcidb' ),
                'type'  => 'select',
                'options' => array(
                    'Yes' => __( 'Yes', 'teqcidb' ),
                    'No'  => __( 'No', 'teqcidb' ),
                ),
            ),
        );
    }

    private function prepare_student_history_fields_for_js() {
        $fields   = $this->get_student_history_fields();
        $prepared = array();

        foreach ( $fields as $field ) {
            $prepared_field = array(
                'name'  => $field['name'],
                'type'  => $field['type'],
                'label' => $field['label'],
            );

            if ( isset( $field['options'] ) ) {
                $prepared_field['options'] = $field['options'];
            }

            if ( isset( $field['attrs'] ) ) {
                $prepared_field['attrs'] = $field['attrs'];
            }

            $prepared[] = $prepared_field;
        }

        return $prepared;
    }

    private function get_class_name_options() {
        global $wpdb;
        $table = $wpdb->prefix . 'teqcidb_classes';
        $rows  = $wpdb->get_col( "SELECT DISTINCT classname FROM $table ORDER BY classname ASC" );
        $options = array();

        if ( $rows ) {
            foreach ( $rows as $classname ) {
                if ( ! is_scalar( $classname ) ) {
                    continue;
                }

                $label = sanitize_text_field( (string) $classname );

                if ( '' === $label ) {
                    continue;
                }

                $options[ $label ] = $label;
            }
        }

        return $options;
    }


    private function get_quiz_class_options() {
        global $wpdb;

        $table   = $wpdb->prefix . 'teqcidb_classes';
        $results = $wpdb->get_results( "SELECT id, classname, classstartdate FROM $table ORDER BY classstartdate DESC, classname ASC", ARRAY_A );

        $options = array();

        if ( ! is_array( $results ) ) {
            return $options;
        }

        foreach ( $results as $row ) {
            $class_id   = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
            $class_name = isset( $row['classname'] ) ? sanitize_text_field( (string) $row['classname'] ) : '';

            if ( $class_id <= 0 || '' === $class_name ) {
                continue;
            }

            $class_date = isset( $row['classstartdate'] ) ? sanitize_text_field( (string) $row['classstartdate'] ) : '';

            if ( '' !== $class_date ) {
                /* translators: 1: class name, 2: class start date. */
                $label = sprintf( __( '%1$s (%2$s)', 'teqcidb' ), $class_name, $class_date );
            } else {
                $label = $class_name;
            }

            $options[ (string) $class_id ] = $label;
        }

        return $options;
    }

    private function get_saved_quizzes() {
        global $wpdb;

        $table = $wpdb->prefix . 'teqcidb_quizzes';

        $results = $wpdb->get_results( "SELECT id, name, class_id, updated_at FROM $table ORDER BY updated_at DESC, id DESC", ARRAY_A );

        if ( ! is_array( $results ) ) {
            return array();
        }

        return $results;
    }

    private function parse_quiz_class_ids( $class_ids_csv ) {
        if ( ! is_scalar( $class_ids_csv ) ) {
            return array();
        }

        $parts = explode( ',', (string) $class_ids_csv );
        $ids   = array();

        foreach ( $parts as $part ) {
            $class_id = absint( trim( (string) $part ) );

            if ( $class_id > 0 ) {
                $ids[] = (string) $class_id;
            }
        }

        return array_values( array_unique( $ids ) );
    }

    private function get_quiz_question_count_map( array $quiz_ids ) {
        global $wpdb;

        $quiz_ids = array_values( array_filter( array_map( 'absint', $quiz_ids ) ) );

        if ( empty( $quiz_ids ) ) {
            return array();
        }

        $placeholders = implode( ',', array_fill( 0, count( $quiz_ids ), '%d' ) );
        $table        = $wpdb->prefix . 'teqcidb_quiz_questions';

        $query = $wpdb->prepare(
            "SELECT quiz_id, COUNT(*) AS total FROM $table WHERE quiz_id IN ($placeholders) GROUP BY quiz_id",
            $quiz_ids
        );

        $results = $wpdb->get_results( $query, ARRAY_A );
        $map     = array();

        if ( ! is_array( $results ) ) {
            return $map;
        }

        foreach ( $results as $row ) {
            $quiz_id = isset( $row['quiz_id'] ) ? absint( $row['quiz_id'] ) : 0;
            $total   = isset( $row['total'] ) ? absint( $row['total'] ) : 0;

            if ( $quiz_id <= 0 ) {
                continue;
            }

            $map[ $quiz_id ] = $total;
        }

        return $map;
    }

    private function get_quiz_questions_map( array $quiz_ids ) {
        global $wpdb;

        $quiz_ids = array_values( array_filter( array_map( 'absint', $quiz_ids ) ) );

        if ( empty( $quiz_ids ) ) {
            return array();
        }

        $placeholders = implode( ',', array_fill( 0, count( $quiz_ids ), '%d' ) );
        $table        = $wpdb->prefix . 'teqcidb_quiz_questions';

        $query = $wpdb->prepare(
            "SELECT id, quiz_id, type, prompt, choices_json, sort_order FROM $table WHERE quiz_id IN ($placeholders) ORDER BY quiz_id ASC, sort_order ASC, id ASC",
            $quiz_ids
        );

        $results = $wpdb->get_results( $query, ARRAY_A );
        $map     = array();

        if ( ! is_array( $results ) ) {
            return $map;
        }

        foreach ( $results as $row ) {
            $quiz_id = isset( $row['quiz_id'] ) ? absint( $row['quiz_id'] ) : 0;

            if ( $quiz_id <= 0 ) {
                continue;
            }

            if ( ! isset( $map[ $quiz_id ] ) ) {
                $map[ $quiz_id ] = array();
            }

            $map[ $quiz_id ][] = array(
                'id'        => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
                'type'      => isset( $row['type'] ) ? sanitize_key( (string) $row['type'] ) : '',
                'prompt'    => isset( $row['prompt'] ) ? (string) $row['prompt'] : '',
                'choices_json' => isset( $row['choices_json'] ) ? (string) $row['choices_json'] : '',
                'sort_order'=> isset( $row['sort_order'] ) ? absint( $row['sort_order'] ) : 0,
            );
        }

        return $map;
    }

    private function get_quiz_question_type_label( $question_type ) {
        $question_type = sanitize_key( (string) $question_type );

        switch ( $question_type ) {
            case 'multiple_choice':
                return __( 'Multiple Choice', 'teqcidb' );
            case 'true_false':
                return __( 'True or False', 'teqcidb' );
            case 'multi_select':
                return __( 'Multi-Select', 'teqcidb' );
            default:
                return __( 'Question', 'teqcidb' );
        }
    }

    private function get_true_false_answer_from_choices_json( $choices_json ) {
        if ( ! is_scalar( $choices_json ) ) {
            return '';
        }

        $decoded = json_decode( (string) $choices_json, true );

        if ( ! is_array( $decoded ) || empty( $decoded[0] ) || ! is_array( $decoded[0] ) || ! isset( $decoded[0]['correct'] ) ) {
            return '';
        }

        $value = strtolower( sanitize_text_field( (string) $decoded[0]['correct'] ) );

        if ( 'true' === $value || 'false' === $value ) {
            return $value;
        }

        return '';
    }

    private function get_multi_select_choices_from_choices_json( $choices_json ) {
        if ( ! is_scalar( $choices_json ) ) {
            return array();
        }

        $decoded = json_decode( (string) $choices_json, true );

        if ( ! is_array( $decoded ) ) {
            return array();
        }

        $choices = array();

        foreach ( $decoded as $index => $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $choice_id = isset( $item['id'] ) ? sanitize_key( (string) $item['id'] ) : 'choice_' . ( $index + 1 );

            if ( '' === $choice_id ) {
                $choice_id = 'choice_' . ( $index + 1 );
            }

            $choices[] = array(
                'id'      => $choice_id,
                'label'   => isset( $item['label'] ) ? sanitize_textarea_field( (string) $item['label'] ) : '',
                'correct' => ! empty( $item['correct'] ),
            );
        }

        return $choices;
    }


    private function get_truncated_quiz_class_summary( $summary, $max_length = 55 ) {
        if ( ! is_scalar( $summary ) ) {
            return '';
        }

        $summary = trim( sanitize_text_field( (string) $summary ) );

        if ( '' === $summary ) {
            return '';
        }

        $max_length = absint( $max_length );

        if ( $max_length <= 3 ) {
            return $summary;
        }

        if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
            if ( mb_strlen( $summary ) <= $max_length ) {
                return $summary;
            }

            return rtrim( mb_substr( $summary, 0, $max_length - 3 ) ) . '...';
        }

        if ( strlen( $summary ) <= $max_length ) {
            return $summary;
        }

        return rtrim( substr( $summary, 0, $max_length - 3 ) ) . '...';
    }

    private function get_student_history_class_map() {
        global $wpdb;
        $table = $wpdb->prefix . 'teqcidb_classes';
        $results = $wpdb->get_results( "SELECT classname, uniqueclassid, classstartdate, classtype FROM $table ORDER BY classname ASC", ARRAY_A );
        $map = array();

        $type_map = array(
            'initial'   => __( 'Initial', 'teqcidb' ),
            'refresher' => __( 'Refresher', 'teqcidb' ),
            'hybrid'    => __( 'Hybrid', 'teqcidb' ),
            'other'     => __( 'Other', 'teqcidb' ),
        );

        if ( is_array( $results ) ) {
            foreach ( $results as $row ) {
                if ( empty( $row['classname'] ) ) {
                    continue;
                }

                $classname = sanitize_text_field( (string) $row['classname'] );

                if ( '' === $classname ) {
                    continue;
                }

                $class_type = isset( $row['classtype'] ) ? sanitize_text_field( (string) $row['classtype'] ) : '';
                $class_type_key = strtolower( $class_type );
                $class_type_label = isset( $type_map[ $class_type_key ] ) ? $type_map[ $class_type_key ] : $class_type;

                $map[ $classname ] = array(
                    'uniqueclassid' => isset( $row['uniqueclassid'] ) ? sanitize_text_field( (string) $row['uniqueclassid'] ) : '',
                    'classdate'     => isset( $row['classstartdate'] ) ? sanitize_text_field( (string) $row['classstartdate'] ) : '',
                    'classtype'     => $class_type_label,
                );
            }
        }

        return $map;
    }

    private function prepare_class_fields_for_js() {
        $fields   = $this->get_class_fields( 'edit' );
        $prepared = array();

        foreach ( $fields as $field ) {
            $prepared_field = array(
                'name'      => $field['name'],
                'type'      => $field['type'],
                'label'     => $field['label'],
                'tooltip'   => $field['tooltip'],
                'fullWidth' => ! empty( $field['full_width'] ),
            );

            if ( isset( $field['options'] ) ) {
                $prepared_field['options'] = $field['options'];
            }

            if ( isset( $field['attrs'] ) ) {
                $prepared_field['attrs'] = $field['attrs'];
            }

            if ( isset( $field['autocomplete'] ) ) {
                $prepared_field['autocomplete'] = $field['autocomplete'];
            }

            if ( isset( $field['label_link'] ) ) {
                $prepared_field['labelLink'] = $field['label_link'];
            }

            if ( isset( $field['resource_tooltips'] ) ) {
                $prepared_field['resourceTooltips'] = $field['resource_tooltips'];
            }

            $prepared[] = $prepared_field;
        }

        return $prepared;
    }

    private function get_inline_editor_settings() {
        $default_settings = array(
            'tinymce'   => array(
                'wpautop' => true,
            ),
            'quicktags' => true,
        );

        if ( function_exists( 'wp_get_editor_settings' ) ) {
            $settings = wp_get_editor_settings( 'placeholder_28', array( 'textarea_name' => 'placeholder_28' ) );

            if ( is_array( $settings ) ) {
                return $settings;
            }
        }

        return $default_settings;
    }

    private function render_entity_form( $fields, $form_id, $submit_label, $submit_enabled = true, $submit_note = '' ) {
        echo '<form id="' . esc_attr( $form_id ) . '"><div class="teqcidb-flex-form">';

        foreach ( $fields as $field ) {
            $classes = 'teqcidb-field';

            if ( ! empty( $field['full_width'] ) ) {
                $classes .= ' teqcidb-field-full';
            }

            $tooltip = isset( $field['tooltip'] ) ? $field['tooltip'] : '';
            $label   = isset( $field['label'] ) ? $field['label'] : '';

            echo '<div class="' . esc_attr( $classes ) . '">';
            echo '<label><span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr( $tooltip ) . '"></span>' . wp_kses_post( $label ) . '</label>';

            switch ( $field['type'] ) {
                case 'select':
                    echo '<select name="' . esc_attr( $field['name'] ) . '">';

                    foreach ( $field['options'] as $value => $option_label ) {
                        if ( '' === $value ) {
                            echo '<option value="" disabled selected>' . esc_html( $option_label ) . '</option>';
                        } else {
                            echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $option_label ) . '</option>';
                        }
                    }

                    echo '</select>';
                    break;
                case 'state':
                    $states = isset( $field['options'] ) ? $field['options'] : $this->get_us_states_and_territories();
                    echo '<select name="' . esc_attr( $field['name'] ) . '">';
                    echo '<option value="" disabled selected>' . esc_html__( 'Make a Selection...', 'teqcidb' ) . '</option>';

                    foreach ( $states as $state ) {
                        echo '<option value="' . esc_attr( $state ) . '">' . esc_html( $state ) . '</option>';
                    }

                    echo '</select>';
                    break;
                case 'radio':
                    foreach ( $field['options'] as $value => $opt ) {
                        echo '<label class="teqcidb-radio-option"><input type="radio" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $value ) . '" />';
                        echo ' <span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr( $opt['tooltip'] ) . '"></span>';
                        echo esc_html( $opt['label'] ) . '</label>';
                    }
                    break;
                case 'editor':
                    wp_editor( '', $field['name'], array( 'textarea_name' => $field['name'] ) );
                    break;
                case 'opt_in':
                    $opts = isset( $field['options'] ) ? $field['options'] : array();

                    if ( empty( $opts ) ) {
                        $opts = array(
                            array(
                                'name'    => 'opt_in_marketing_email',
                                'label'   => __( 'Option 1', 'teqcidb' ),
                                'tooltip' => __( 'Tooltip placeholder text for Placeholder 23 Option 1', 'teqcidb' ),
                            ),
                            array(
                                'name'    => 'opt_in_marketing_sms',
                                'label'   => __( 'Option 2', 'teqcidb' ),
                                'tooltip' => __( 'Tooltip placeholder text for Placeholder 23 Option 2', 'teqcidb' ),
                            ),
                            array(
                                'name'    => 'opt_in_event_update_email',
                                'label'   => __( 'Option 3', 'teqcidb' ),
                                'tooltip' => __( 'Tooltip placeholder text for Placeholder 23 Option 3', 'teqcidb' ),
                            ),
                            array(
                                'name'    => 'opt_in_event_update_sms',
                                'label'   => __( 'Option 4', 'teqcidb' ),
                                'tooltip' => __( 'Tooltip placeholder text for Placeholder 23 Option 4', 'teqcidb' ),
                            ),
                        );
                    }

                    echo '<fieldset>';

                    foreach ( $opts as $opt ) {
                        echo '<label class="teqcidb-opt-in-option"><input type="checkbox" name="' . esc_attr( $opt['name'] ) . '" value="1" />';
                        echo ' <span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr( $opt['tooltip'] ) . '"></span>';
                        echo esc_html( $opt['label'] ) . '</label>';
                    }

                    echo '</fieldset>';
                    break;
                case 'checkboxes':
                    if ( empty( $field['options'] ) ) {
                        break;
                    }

                    echo '<fieldset class="teqcidb-checkbox-group">';

                    foreach ( $field['options'] as $value => $option_label ) {
                        $input_id = $field['name'] . '-' . sanitize_title( $value );
                        echo '<label class="teqcidb-checkbox-option" for="' . esc_attr( $input_id ) . '">';
                        echo '<input type="checkbox" id="' . esc_attr( $input_id ) . '" name="' . esc_attr( $field['name'] ) . '[]" value="' . esc_attr( $value ) . '" /> ';
                        echo esc_html( $option_label );
                        echo '</label>';
                    }

                    echo '</fieldset>';
                    break;
                case 'items':
                    $container_id = 'teqcidb-items-container-' . sanitize_html_class( $field['name'] );
                    $autocomplete_attr = '';

                    if ( ! empty( $field['autocomplete'] ) ) {
                        $autocomplete_attr = ' data-autocomplete="' . esc_attr( $field['autocomplete'] ) . '"';
                    }

                    echo '<div id="' . esc_attr( $container_id ) . '" class="teqcidb-items-container" data-placeholder="' . esc_attr( $field['name'] ) . '"' . $autocomplete_attr . '>';
                    echo '<div class="teqcidb-item-row" style="margin-bottom:8px; display:flex; align-items:center;">';
                    $item_classes = 'regular-text teqcidb-item-field';

                    if ( ! empty( $field['autocomplete'] ) ) {
                        $item_classes .= ' teqcidb-autocomplete-field';
                    }

                    echo '<input type="text" name="' . esc_attr( $field['name'] ) . '[]" class="' . esc_attr( $item_classes ) . '" placeholder="' . esc_attr__( 'Item #1', 'teqcidb' ) . '" />';
                    echo '</div></div>';
                    $add_button_attrs = ' class="button teqcidb-add-item" data-target="#' . esc_attr( $container_id ) . '" style="margin-top:8px;"';

                    if ( ! empty( $field['autocomplete'] ) ) {
                        $add_button_attrs .= ' data-autocomplete="' . esc_attr( $field['autocomplete'] ) . '"';
                    }

                    echo '<button type="button"' . $add_button_attrs . '>' . esc_html__( '+ Add Another Item', 'teqcidb' ) . '</button>';
                    break;
                case 'resource_rows':
                    $resource_tooltips = isset( $field['resource_tooltips'] ) && is_array( $field['resource_tooltips'] ) ? $field['resource_tooltips'] : array();
                    $resource_types = array(
                        ''              => __( 'Make a Selection...', 'teqcidb' ),
                        'pdf'           => __( 'PDF', 'teqcidb' ),
                        'video'         => __( 'Video', 'teqcidb' ),
                        'external_link' => __( 'External Link', 'teqcidb' ),
                    );

                    echo '<div class="teqcidb-resource-rows" data-resource-field="' . esc_attr( $field['name'] ) . '">';
                    echo '<div class="teqcidb-resource-row">';
                    echo '<div class="teqcidb-resource-row__fields">';

                    echo '<div class="teqcidb-resource-subfield">';
                    echo '<label><span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr( isset( $resource_tooltips['name'] ) ? $resource_tooltips['name'] : '' ) . '"></span>' . esc_html__( 'Resource Name', 'teqcidb' ) . '</label>';
                    echo '<input type="text" name="' . esc_attr( $field['name'] ) . '[name][]" class="regular-text teqcidb-resource-name" />';
                    echo '</div>';

                    echo '<div class="teqcidb-resource-subfield">';
                    echo '<label><span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr( isset( $resource_tooltips['type'] ) ? $resource_tooltips['type'] : '' ) . '"></span>' . esc_html__( 'Resource Type', 'teqcidb' ) . '</label>';
                    echo '<select name="' . esc_attr( $field['name'] ) . '[type][]" class="teqcidb-resource-type">';
                    foreach ( $resource_types as $value => $option_label ) {
                        if ( '' === $value ) {
                            echo '<option value="" disabled selected>' . esc_html( $option_label ) . '</option>';
                        } else {
                            echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $option_label ) . '</option>';
                        }
                    }
                    echo '</select>';
                    echo '</div>';

                    echo '<div class="teqcidb-resource-subfield">';
                    echo '<label><span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr( isset( $resource_tooltips['url'] ) ? $resource_tooltips['url'] : '' ) . '"></span>' . esc_html__( 'Resource URL', 'teqcidb' ) . '</label>';
                    echo '<input type="url" name="' . esc_attr( $field['name'] ) . '[url][]" class="regular-text teqcidb-resource-url" />';
                    echo '</div>';

                    echo '</div>';
                    echo '<div class="teqcidb-resource-row__actions">';
                    echo '<button type="button" class="button teqcidb-add-resource-row" data-field-name="' . esc_attr( $field['name'] ) . '">' . esc_html__( '+ Add Another Item', 'teqcidb' ) . '</button>';
                    echo '<button type="button" class="teqcidb-remove-resource-row" aria-label="' . esc_attr__( 'Remove', 'teqcidb' ) . '"><span class="dashicons dashicons-no-alt"></span></button>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    break;
                case 'textarea':
                    $textarea_attrs = isset( $field['attrs'] ) ? ' ' . $field['attrs'] : '';
                    echo '<textarea name="' . esc_attr( $field['name'] ) . '"' . $textarea_attrs . '></textarea>';
                    break;
                case 'image':
                    echo '<input type="hidden" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['name'] ) . '" />';
                    echo '<button type="button" class="button teqcidb-upload" data-target="#' . esc_attr( $field['name'] ) . '">' . esc_html__( 'Select Image', 'teqcidb' ) . '</button>';
                    echo '<div id="' . esc_attr( $field['name'] ) . '-preview" style="margin-top:10px;"></div>';
                    break;
                default:
                    $attrs = isset( $field['attrs'] ) ? ' ' . $field['attrs'] : '';
                    echo '<input type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $field['name'] ) . '"' . $attrs . ' />';
                    break;
            }

            echo '</div>';
        }

        echo '</div>';

        $other_attributes = $submit_enabled ? '' : array(
            'disabled'      => 'disabled',
            'aria-disabled' => 'true',
        );

        $submit_button = get_submit_button( $submit_label, 'primary', 'submit', false, $other_attributes );

        echo '<p class="submit">' . $submit_button;
        echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span id="teqcidb-spinner" class="spinner" aria-hidden="true"></span><span id="teqcidb-feedback" role="status" aria-live="polite"></span></span>';

        if ( $submit_note ) {
            echo '<span class="description teqcidb-submit-note">' . esc_html( $submit_note ) . '</span>';
        }

        echo '</p>';
        echo '</form>';
    }

    private function render_class_create_tab() {
        $fields = $this->get_class_fields( 'create' );

        $this->render_entity_form(
            $fields,
            'teqcidb-class-create-form',
            __( 'Save Class', 'teqcidb' )
        );
    }


    private function render_quiz_create_tab() {
        $fields = $this->get_quiz_fields();
        echo '<form id="teqcidb-quiz-create-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="teqcidb_save_quiz" />';
        wp_nonce_field( 'teqcidb_save_quiz', 'teqcidb_save_quiz_nonce' );
        echo '<div class="teqcidb-flex-form">';

        foreach ( $fields as $field ) {
            $classes = 'teqcidb-field';

            if ( ! empty( $field['full_width'] ) ) {
                $classes .= ' teqcidb-field-full';
            }

            $tooltip = isset( $field['tooltip'] ) ? $field['tooltip'] : '';

            echo '<div class="' . esc_attr( $classes ) . '">';
            echo '<label><span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr( $tooltip ) . '"></span>' . esc_html( $field['label'] ) . '</label>';

            if ( 'checkboxes' === $field['type'] ) {
                echo '<fieldset class="teqcidb-checkbox-group">';

                if ( ! empty( $field['options'] ) ) {
                    foreach ( $field['options'] as $value => $option_label ) {
                        $input_id = $field['name'] . '-' . sanitize_html_class( (string) $value );
                        echo '<label class="teqcidb-checkbox-option" for="' . esc_attr( $input_id ) . '">';
                        echo '<input type="checkbox" id="' . esc_attr( $input_id ) . '" name="' . esc_attr( $field['name'] ) . '[]" value="' . esc_attr( $value ) . '" /> ';
                        echo esc_html( $option_label );
                        echo '</label>';
                    }
                }

                echo '</fieldset>';
            } else {
                echo '<input type="text" name="' . esc_attr( $field['name'] ) . '" />';
            }

            echo '</div>';
        }

        echo '</div>';
        echo '<p class="submit">';
        echo get_submit_button( __( 'Save Quiz', 'teqcidb' ), 'primary', 'submit', false );
        echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span id="teqcidb-spinner" class="spinner" aria-hidden="true"></span><span id="teqcidb-feedback" role="status" aria-live="polite"></span></span>';
        echo '</p>';
        echo '</form>';
    }

    public function handle_save_quiz() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'teqcidb' ) );
        }

        check_admin_referer( 'teqcidb_save_quiz', 'teqcidb_save_quiz_nonce' );

        $redirect = add_query_arg(
            array(
                'page' => 'teqcidb-quizzes',
                'tab'  => 'create',
            ),
            admin_url( 'admin.php' )
        );

        $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

        if ( '' === $name ) {
            wp_safe_redirect( add_query_arg( 'teqcidb_quiz_message', 'missing_name', $redirect ) );
            exit;
        }

        $class_ids_raw = isset( $_POST['class_id'] ) ? (array) wp_unslash( $_POST['class_id'] ) : array();
        $class_ids     = array();

        foreach ( $class_ids_raw as $class_id_raw ) {
            $class_id = absint( $class_id_raw );

            if ( $class_id > 0 ) {
                $class_ids[] = $class_id;
            }
        }

        $class_ids = array_values( array_unique( $class_ids ) );

        if ( empty( $class_ids ) ) {
            wp_safe_redirect( add_query_arg( 'teqcidb_quiz_message', 'missing_classes', $redirect ) );
            exit;
        }

        $class_ids_csv = implode( ',', $class_ids );
        global $wpdb;
        $table = $wpdb->prefix . 'teqcidb_quizzes';

        $public_token = wp_generate_password( 32, false, false );

        while ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE public_token = %s LIMIT 1", $public_token ) ) ) {
            $public_token = wp_generate_password( 32, false, false );
        }

        $inserted = $wpdb->insert(
            $table,
            array(
                'class_id'     => $class_ids_csv,
                'public_token' => $public_token,
                'name'         => $name,
                'status'       => 2,
                'settings_json'=> '',
            ),
            array( '%s', '%s', '%s', '%d', '%s' )
        );

        if ( false === $inserted ) {
            wp_safe_redirect( add_query_arg( 'teqcidb_quiz_message', 'save_failed', $redirect ) );
            exit;
        }

        wp_safe_redirect( add_query_arg( 'teqcidb_quiz_message', 'created', $redirect ) );
        exit;
    }

    private function render_quiz_edit_tab() {
        $quizzes      = $this->get_saved_quizzes();
        $class_map    = $this->get_quiz_class_options();
        $column_count = 4;
        $quiz_ids     = array();

        foreach ( $quizzes as $quiz ) {
            $quiz_id = isset( $quiz['id'] ) ? absint( $quiz['id'] ) : 0;

            if ( $quiz_id > 0 ) {
                $quiz_ids[] = $quiz_id;
            }
        }

        $question_count_map = $this->get_quiz_question_count_map( $quiz_ids );
        $question_map       = $this->get_quiz_questions_map( $quiz_ids );

        echo '<div class="teqcidb-communications teqcidb-communications--quizzes">';
        echo '<div class="teqcidb-accordion-group teqcidb-accordion-group--table" data-teqcidb-accordion-group="quizzes">';
        echo '<table class="wp-list-table widefat striped teqcidb-accordion-table">';
        echo '<thead><tr>';
        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--quiz-name">' . esc_html__( 'Quiz Name', 'teqcidb' ) . '</th>';
        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--class-name">' . esc_html__( 'Related Classes', 'teqcidb' ) . '</th>';
        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--updated">' . esc_html__( 'Last Updated', 'teqcidb' ) . '</th>';
        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--actions">' . esc_html__( 'Actions', 'teqcidb' ) . '</th>';
        echo '</tr></thead>';

        echo '<tbody id="teqcidb-quiz-list" data-column-count="' . esc_attr( $column_count ) . '">';

        if ( empty( $quizzes ) ) {
            echo '<tr><td colspan="' . esc_attr( $column_count ) . '">' . esc_html__( 'No quizzes found yet.', 'teqcidb' ) . '</td></tr>';
        } else {
            foreach ( $quizzes as $quiz ) {
                $quiz_id    = isset( $quiz['id'] ) ? absint( $quiz['id'] ) : 0;
                $quiz_name  = isset( $quiz['name'] ) ? sanitize_text_field( (string) $quiz['name'] ) : '';
                $updated_at = isset( $quiz['updated_at'] ) ? sanitize_text_field( (string) $quiz['updated_at'] ) : '';

                if ( $quiz_id <= 0 ) {
                    continue;
                }

                $selected_class_ids = $this->parse_quiz_class_ids( isset( $quiz['class_id'] ) ? $quiz['class_id'] : '' );
                $selected_labels    = array();

                foreach ( $selected_class_ids as $class_id ) {
                    if ( isset( $class_map[ $class_id ] ) ) {
                        $selected_labels[] = $class_map[ $class_id ];
                    }
                }

                $summary_classes = empty( $selected_labels ) ? __( 'No classes selected', 'teqcidb' ) : implode( ', ', $selected_labels );
                $summary_classes = $this->get_truncated_quiz_class_summary( $summary_classes, 55 );
                $panel_id        = 'teqcidb-quiz-panel-' . $quiz_id;

                echo '<tr class="teqcidb-accordion__summary-row" tabindex="0" role="button" aria-expanded="false" aria-controls="' . esc_attr( $panel_id ) . '">';
                echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--title"><span class="teqcidb-accordion__title-text">' . esc_html( $quiz_name ) . '</span></td>';
                echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--meta"><span class="teqcidb-accordion__meta-text">' . esc_html( $summary_classes ) . '</span></td>';
                echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--meta"><span class="teqcidb-accordion__meta-text">' . esc_html( $updated_at ) . '</span></td>';
                echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--actions"><span class="teqcidb-accordion__action-link" aria-hidden="true">' . esc_html__( 'Edit', 'teqcidb' ) . '</span><span class="dashicons dashicons-arrow-down-alt2 teqcidb-accordion__icon" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'Toggle quiz details', 'teqcidb' ) . '</span></td>';
                echo '</tr>';

                echo '<tr id="' . esc_attr( $panel_id ) . '" class="teqcidb-accordion__panel-row" aria-hidden="true">';
                echo '<td colspan="' . esc_attr( $column_count ) . '">';
                echo '<div class="teqcidb-accordion__panel">';
                echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
                echo '<input type="hidden" name="action" value="teqcidb_update_quiz" />';
                echo '<input type="hidden" name="quiz_id" value="' . esc_attr( $quiz_id ) . '" />';
                wp_nonce_field( 'teqcidb_update_quiz_' . $quiz_id, 'teqcidb_update_quiz_nonce' );
                echo '<div class="teqcidb-flex-form">';

                echo '<div class="teqcidb-field">';
                echo '<label><span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr__( 'Update the quiz name shown in admin quiz management lists.', 'teqcidb' ) . '"></span>' . esc_html__( 'Quiz Name', 'teqcidb' ) . '</label>';
                echo '<input type="text" name="name" value="' . esc_attr( $quiz_name ) . '" />';
                echo '</div>';

                echo '<div class="teqcidb-field teqcidb-field-full">';
                echo '<label><span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr__( 'Select every class that should use this quiz. Selected IDs are saved as a comma-separated list in teqcidb_quizzes.class_id.', 'teqcidb' ) . '"></span>' . esc_html__( 'Related Classes', 'teqcidb' ) . '</label>';
                echo '<fieldset class="teqcidb-checkbox-group">';

                foreach ( $class_map as $class_id => $class_label ) {
                    $input_id = 'teqcidb-quiz-' . $quiz_id . '-class-' . sanitize_html_class( (string) $class_id );
                    echo '<label class="teqcidb-checkbox-option" for="' . esc_attr( $input_id ) . '">';
                    echo '<input type="checkbox" id="' . esc_attr( $input_id ) . '" name="class_id[]" value="' . esc_attr( $class_id ) . '" ' . checked( in_array( (string) $class_id, $selected_class_ids, true ), true, false ) . ' /> ';
                    echo esc_html( $class_label );
                    echo '</label>';
                }

                echo '</fieldset>';
                echo '</div>';

                echo '</div>';

                $question_count = isset( $question_count_map[ $quiz_id ] ) ? absint( $question_count_map[ $quiz_id ] ) : 0;
                $quiz_questions = isset( $question_map[ $quiz_id ] ) && is_array( $question_map[ $quiz_id ] ) ? $question_map[ $quiz_id ] : array();

                echo '<div class="teqcidb-quiz-questions" data-quiz-id="' . esc_attr( $quiz_id ) . '">';
                echo '<h4 class="teqcidb-quiz-questions__title">' . esc_html__( 'Quiz Questions', 'teqcidb' ) . '</h4>';

                if ( $question_count > 0 ) {
                    /* translators: %d: number of saved quiz questions. */
                    $question_count_text = sprintf( _n( 'This quiz currently has %d saved question.', 'This quiz currently has %d saved questions.', $question_count, 'teqcidb' ), $question_count );
                    echo '<p class="description teqcidb-quiz-questions__count">' . esc_html( $question_count_text ) . '</p>';

                    foreach ( $quiz_questions as $index => $question ) {
                        $question_id         = isset( $question['id'] ) ? absint( $question['id'] ) : 0;
                        $question_number     = $index + 1;
                        $question_type       = isset( $question['type'] ) ? $question['type'] : '';
                        $question_type_label = $this->get_quiz_question_type_label( $question_type );
                        $question_prompt     = isset( $question['prompt'] ) ? (string) $question['prompt'] : '';
                        $question_choices_json = isset( $question['choices_json'] ) ? $question['choices_json'] : '';

                        if ( $question_id <= 0 ) {
                            continue;
                        }

                        echo '<div class="teqcidb-quiz-question" data-question-id="' . esc_attr( $question_id ) . '">';
                        echo '<input type="hidden" class="teqcidb-quiz-question__quiz-id" value="' . esc_attr( $quiz_id ) . '" />';
                        echo '<input type="hidden" class="teqcidb-quiz-question__type" value="' . esc_attr( $question_type ) . '" />';

                        /* translators: 1: question number, 2: question type label. */
                        $question_title   = sprintf( __( 'Question #%1$d (%2$s)', 'teqcidb' ), $question_number, $question_type_label );
                        $question_preview = '' === trim( sanitize_text_field( $question_prompt ) ) ? __( 'No question text entered yet.', 'teqcidb' ) : sanitize_text_field( $question_prompt );
                        $question_panel_id = 'teqcidb-quiz-' . $quiz_id . '-question-panel-' . $question_id;

                        echo '<button type="button" class="teqcidb-quiz-question__summary" aria-expanded="false" aria-controls="' . esc_attr( $question_panel_id ) . '">';
                        echo '<span class="teqcidb-quiz-question__summary-text">';
                        echo '<span class="teqcidb-quiz-question__title">' . esc_html( $question_title ) . '</span>';
                        echo '<span class="teqcidb-quiz-question__preview">' . esc_html( $question_preview ) . '</span>';
                        echo '</span>';
                        echo '<span class="dashicons dashicons-arrow-down-alt2 teqcidb-quiz-question__summary-icon" aria-hidden="true"></span>';
                        echo '</button>';

                        echo '<div id="' . esc_attr( $question_panel_id ) . '" class="teqcidb-quiz-question__panel" hidden="hidden">';

                        echo '<input type="hidden" name="question_ids[]" value="' . esc_attr( $question_id ) . '" />';
                        echo '<label class="screen-reader-text" for="teqcidb-quiz-' . esc_attr( $quiz_id ) . '-question-' . esc_attr( $question_id ) . '">' . esc_html__( 'Question prompt', 'teqcidb' ) . '</label>';
                        echo '<textarea id="teqcidb-quiz-' . esc_attr( $quiz_id ) . '-question-' . esc_attr( $question_id ) . '" name="question_prompt[' . esc_attr( $question_id ) . ']" rows="4" class="widefat">' . esc_textarea( $question_prompt ) . '</textarea>';

                        if ( 'true_false' === $question_type ) {
                            $true_false_value = $this->get_true_false_answer_from_choices_json( $question_choices_json );

                            echo '<p class="teqcidb-quiz-question__answer-label"><strong>' . esc_html__( 'Select an Answer', 'teqcidb' ) . '</strong></p>';
                            echo '<select name="question_correct[' . esc_attr( $question_id ) . ']">';
                            echo '<option value="" disabled ' . selected( '', $true_false_value, false ) . '>' . esc_html__( 'Choose True or False...', 'teqcidb' ) . '</option>';
                            echo '<option value="true" ' . selected( 'true', $true_false_value, false ) . '>' . esc_html__( 'True', 'teqcidb' ) . '</option>';
                            echo '<option value="false" ' . selected( 'false', $true_false_value, false ) . '>' . esc_html__( 'False', 'teqcidb' ) . '</option>';
                            echo '</select>';
                        }

                        if ( in_array( $question_type, array( 'multi_select', 'multiple_choice' ), true ) ) {
                            $multi_select_choices = $this->get_multi_select_choices_from_choices_json( $question_choices_json );

                            if ( empty( $multi_select_choices ) ) {
                                $multi_select_choices = array(
                                    array(
                                        'id'      => 'choice_1',
                                        'label'   => '',
                                        'correct' => false,
                                    ),
                                );
                            }

                            echo '<div class="teqcidb-quiz-question__options" data-question-id="' . esc_attr( $question_id ) . '" data-question-type="' . esc_attr( $question_type ) . '">';
                            echo '<p class="teqcidb-quiz-question__answer-label"><strong>' . esc_html__( 'Possible Answers', 'teqcidb' ) . '</strong></p>';
                            echo '<div class="teqcidb-quiz-question-options">';

                            foreach ( $multi_select_choices as $choice_index => $choice ) {
                                $choice_key     = isset( $choice['id'] ) ? sanitize_key( (string) $choice['id'] ) : 'choice_' . ( $choice_index + 1 );
                                $choice_label   = isset( $choice['label'] ) ? (string) $choice['label'] : '';
                                $choice_correct = ! empty( $choice['correct'] ) ? 'true' : 'false';

                                if ( '' === $choice_key ) {
                                    $choice_key = 'choice_' . ( $choice_index + 1 );
                                }

                                echo '<div class="teqcidb-quiz-question-option-row" data-option-row="' . esc_attr( $choice_key ) . '">';
                                echo '<textarea rows="2" class="widefat teqcidb-quiz-question-option-label" name="question_option_label[' . esc_attr( $question_id ) . '][]" placeholder="' . esc_attr__( 'Enter answer option text…', 'teqcidb' ) . '">' . esc_textarea( $choice_label ) . '</textarea>';
                                echo '<div class="teqcidb-quiz-question-option-meta">';
                                echo '<input type="hidden" class="teqcidb-quiz-question-option-id" name="question_option_id[' . esc_attr( $question_id ) . '][]" value="' . esc_attr( $choice_key ) . '" />';
                                echo '<label class="screen-reader-text" for="teqcidb-quiz-' . esc_attr( $quiz_id ) . '-question-' . esc_attr( $question_id ) . '-option-' . esc_attr( $choice_key ) . '">' . esc_html__( 'Select whether this answer option is correct', 'teqcidb' ) . '</label>';
                                echo '<select id="teqcidb-quiz-' . esc_attr( $quiz_id ) . '-question-' . esc_attr( $question_id ) . '-option-' . esc_attr( $choice_key ) . '" class="teqcidb-quiz-question-option-correct" name="question_option_correct[' . esc_attr( $question_id ) . '][]">';
                                echo '<option value="true" ' . selected( 'true', $choice_correct, false ) . '>' . esc_html__( 'True', 'teqcidb' ) . '</option>';
                                echo '<option value="false" ' . selected( 'false', $choice_correct, false ) . '>' . esc_html__( 'False', 'teqcidb' ) . '</option>';
                                echo '</select>';
                                echo '</div>';
                                echo '</div>';
                            }

                            echo '</div>';
                            echo '<button type="button" class="button button-secondary teqcidb-quiz-question-option-add">' . esc_html__( 'Add Another Answer', 'teqcidb' ) . '</button>';
                            echo '</div>';
                        }

                        echo '<div class="teqcidb-quiz-question__actions">';
                        echo '<button type="button" class="button button-primary teqcidb-quiz-question-save">' . esc_html__( 'Save Changes', 'teqcidb' ) . '</button> ';
                        echo '<button type="button" class="button button-secondary teqcidb-quiz-question-delete">' . esc_html__( 'Delete', 'teqcidb' ) . '</button>';
                        echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline">';
                        echo '<span class="spinner teqcidb-quiz-question-spinner" aria-hidden="true"></span>';
                        echo '<span class="teqcidb-quiz-question-feedback" role="status" aria-live="polite"></span>';
                        echo '</span>';
                        echo '</div>';

                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="description teqcidb-quiz-questions__empty">' . esc_html__( 'No questions found for this quiz.', 'teqcidb' ) . '</p>';
                }

                echo '<button type="button" class="button button-secondary teqcidb-quiz-question-add">' . esc_html__( 'Add Quiz Question', 'teqcidb' ) . '</button>';
                echo '</div>';

                echo '<p class="submit">';
                echo get_submit_button( __( 'Save Changes', 'teqcidb' ), 'primary', 'submit', false );
                echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span class="spinner" aria-hidden="true"></span><span role="status" aria-live="polite"></span></span>';
                echo '</p>';
                echo '</form>';
                echo '</div>';
                echo '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    public function handle_update_quiz() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'teqcidb' ) );
        }

        $quiz_id = isset( $_POST['quiz_id'] ) ? absint( wp_unslash( $_POST['quiz_id'] ) ) : 0;

        $redirect = add_query_arg(
            array(
                'page' => 'teqcidb-quizzes',
                'tab'  => 'edit',
            ),
            admin_url( 'admin.php' )
        );

        if ( $quiz_id <= 0 ) {
            wp_safe_redirect( add_query_arg( 'teqcidb_quiz_message', 'missing_quiz', $redirect ) );
            exit;
        }

        check_admin_referer( 'teqcidb_update_quiz_' . $quiz_id, 'teqcidb_update_quiz_nonce' );

        $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

        if ( '' === $name ) {
            wp_safe_redirect( add_query_arg( 'teqcidb_quiz_message', 'missing_name', $redirect ) );
            exit;
        }

        $class_ids_raw = isset( $_POST['class_id'] ) ? (array) wp_unslash( $_POST['class_id'] ) : array();
        $class_ids     = array();

        foreach ( $class_ids_raw as $class_id_raw ) {
            $class_id = absint( $class_id_raw );

            if ( $class_id > 0 ) {
                $class_ids[] = $class_id;
            }
        }

        $class_ids = array_values( array_unique( $class_ids ) );

        if ( empty( $class_ids ) ) {
            wp_safe_redirect( add_query_arg( 'teqcidb_quiz_message', 'missing_classes', $redirect ) );
            exit;
        }

        $class_ids_csv = implode( ',', $class_ids );

        global $wpdb;
        $table = $wpdb->prefix . 'teqcidb_quizzes';

        $updated = $wpdb->update(
            $table,
            array(
                'name'     => $name,
                'class_id' => $class_ids_csv,
            ),
            array( 'id' => $quiz_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            wp_safe_redirect( add_query_arg( 'teqcidb_quiz_message', 'update_failed', $redirect ) );
            exit;
        }

        $question_ids_raw = isset( $_POST['question_ids'] ) ? (array) wp_unslash( $_POST['question_ids'] ) : array();
        $question_prompts = isset( $_POST['question_prompt'] ) ? (array) wp_unslash( $_POST['question_prompt'] ) : array();
        $question_correct = isset( $_POST['question_correct'] ) ? (array) wp_unslash( $_POST['question_correct'] ) : array();

        if ( ! empty( $question_ids_raw ) && ! empty( $question_prompts ) ) {
            $questions_table = $wpdb->prefix . 'teqcidb_quiz_questions';

            foreach ( $question_ids_raw as $question_id_raw ) {
                $question_id = absint( $question_id_raw );

                if ( $question_id <= 0 ) {
                    continue;
                }

                if ( ! isset( $question_prompts[ $question_id ] ) && ! isset( $question_prompts[ (string) $question_id ] ) ) {
                    continue;
                }

                $prompt_value = isset( $question_prompts[ $question_id ] ) ? $question_prompts[ $question_id ] : $question_prompts[ (string) $question_id ];
                $prompt       = sanitize_textarea_field( (string) $prompt_value );

                $wpdb->update(
                    $questions_table,
                    array( 'prompt' => $prompt ),
                    array(
                        'id'      => $question_id,
                        'quiz_id' => $quiz_id,
                    ),
                    array( '%s' ),
                    array( '%d', '%d' )
                );

                if ( isset( $question_correct[ $question_id ] ) || isset( $question_correct[ (string) $question_id ] ) ) {
                    $correct_value_raw = isset( $question_correct[ $question_id ] ) ? $question_correct[ $question_id ] : $question_correct[ (string) $question_id ];
                    $correct_value     = strtolower( sanitize_text_field( (string) $correct_value_raw ) );

                    if ( 'true' === $correct_value || 'false' === $correct_value ) {
                        $question_type = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT type FROM $questions_table WHERE id = %d AND quiz_id = %d LIMIT 1",
                                $question_id,
                                $quiz_id
                            )
                        );

                        if ( 'true_false' === sanitize_key( (string) $question_type ) ) {
                            $choices_json = wp_json_encode(
                                array(
                                    array(
                                        'correct' => $correct_value,
                                    ),
                                )
                            );

                            if ( $choices_json ) {
                                $wpdb->update(
                                    $questions_table,
                                    array( 'choices_json' => $choices_json ),
                                    array(
                                        'id'      => $question_id,
                                        'quiz_id' => $quiz_id,
                                    ),
                                    array( '%s' ),
                                    array( '%d', '%d' )
                                );
                            }
                        }
                    }
                }
            }
        }

        wp_safe_redirect( add_query_arg( 'teqcidb_quiz_message', 'updated', $redirect ) );
        exit;
    }

    private function render_class_edit_tab() {
        $per_page     = 10;
        $column_count = 6;

        echo '<div class="teqcidb-communications teqcidb-communications--classes">';
        echo '<div class="teqcidb-entity-search" role="search">';
        echo '<form id="teqcidb-class-search" class="teqcidb-entity-search__form" method="post">';
        echo '<h3 class="teqcidb-entity-search__title">' . esc_html__( 'Search Classes', 'teqcidb' ) . '</h3>';
        echo '<p class="teqcidb-entity-search__description">' . esc_html__( 'Locate saved sessions by name, format, or type to review their details.', 'teqcidb' ) . '</p>';
        echo '<div class="teqcidb-entity-search__fields">';

        $search_fields = array(
            array(
                'key'   => 'placeholder_1',
                'label' => __( 'Class Name', 'teqcidb' ),
            ),
            array(
                'key'   => 'placeholder_2',
                'label' => __( 'Class Format', 'teqcidb' ),
            ),
            array(
                'key'   => 'placeholder_3',
                'label' => __( 'Class Type', 'teqcidb' ),
            ),
        );

        foreach ( $search_fields as $field ) {
            $field_id = 'teqcidb-class-search-' . $field['key'];

            echo '<div class="teqcidb-entity-search__field">';
            echo '<label class="teqcidb-entity-search__label" for="' . esc_attr( $field_id ) . '">';
            echo esc_html( $field['label'] );
            echo '</label>';
            echo '<input type="text" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field['key'] ) . '" class="regular-text" />';
            echo '</div>';
        }

        echo '</div>';
        echo '<div class="teqcidb-entity-search__actions">';
        echo '<button type="submit" class="button button-primary">' . esc_html__( 'Search', 'teqcidb' ) . '</button>';
        echo '<button type="button" id="teqcidb-class-search-clear" class="button button-secondary">' . esc_html__( 'Clear Search', 'teqcidb' ) . '</button>';
        echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline">';
        echo '<span id="teqcidb-class-search-spinner" class="spinner" aria-hidden="true"></span>';
        echo '<span id="teqcidb-class-search-feedback" role="status" aria-live="polite"></span>';
        echo '</span>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '<div class="teqcidb-accordion-group teqcidb-accordion-group--table" data-teqcidb-accordion-group="classes">';
        echo '<table class="wp-list-table widefat striped teqcidb-accordion-table">';
        echo '<thead>';
        echo '<tr>';

        $labels = $this->get_class_placeholder_labels();

        for ( $i = 1; $i <= 5; $i++ ) {
            $label = isset( $labels[ 'placeholder_' . $i ] ) ? $labels[ 'placeholder_' . $i ] : sprintf( __( 'Placeholder %d', 'teqcidb' ), $i );

            printf(
                '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--placeholder-%1$d">%2$s</th>',
                absint( $i ),
                esc_html( $label )
            );
        }

        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--actions">' . esc_html__( 'Actions', 'teqcidb' ) . '</th>';
        echo '</tr>';
        echo '</thead>';
        printf(
            '<tbody id="teqcidb-class-list" data-per-page="%1$d" data-column-count="%2$d">',
            absint( $per_page ),
            absint( $column_count )
        );
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '<div class="tablenav"><div id="teqcidb-class-pagination" class="tablenav-pages"></div></div>';
        echo '</div>';
        echo '<div id="teqcidb-class-feedback" class="teqcidb-feedback-area teqcidb-feedback-area--block" role="status" aria-live="polite"></div>';
    }

    private function render_create_tab() {
        $fields = $this->get_student_fields();

        $this->render_entity_form( $fields, 'teqcidb-create-form', __( 'Save', 'teqcidb' ) );
    }

    private function render_edit_tab() {
        $per_page     = 20;
        $column_count = 6; // Five placeholder columns plus actions.

        echo '<div class="teqcidb-communications teqcidb-communications--students">';
        echo '<div class="teqcidb-entity-search" role="search">';
        echo '<form id="teqcidb-student-search" class="teqcidb-entity-search__form" method="post">';
        echo '<h3 class="teqcidb-entity-search__title">' . esc_html__( 'Search Students', 'teqcidb' ) . '</h3>';
        echo '<p class="teqcidb-entity-search__description">' . esc_html__( 'Filter records by placeholder values to quickly locate the entries you need.', 'teqcidb' ) . '</p>';
        echo '<div class="teqcidb-entity-search__fields">';

        for ( $i = 1; $i <= 3; $i++ ) {
            $field_key = 'placeholder_' . $i;
            $label     = $this->get_placeholder_label( $i );
            $field_id  = 'teqcidb-entity-search-' . $field_key;

            echo '<div class="teqcidb-entity-search__field">';
            echo '<label class="teqcidb-entity-search__label" for="' . esc_attr( $field_id ) . '">';
            echo esc_html( $label );
            echo '</label>';
            echo '<input type="text" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_key ) . '" class="regular-text" />';
            echo '</div>';
        }

        echo '</div>';
        echo '<div class="teqcidb-entity-search__actions">';
        echo '<button type="submit" class="button button-primary">' . esc_html__( 'Search', 'teqcidb' ) . '</button>';
        echo '<button type="button" id="teqcidb-entity-search-clear" class="button button-secondary">' . esc_html__( 'Clear Search', 'teqcidb' ) . '</button>';
        echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline">';
        echo '<span id="teqcidb-entity-search-spinner" class="spinner" aria-hidden="true"></span>';
        echo '<span id="teqcidb-entity-search-feedback" role="status" aria-live="polite"></span>';
        echo '</span>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '<div class="teqcidb-accordion-group teqcidb-accordion-group--table" data-teqcidb-accordion-group="students">';
        echo '<table class="wp-list-table widefat striped teqcidb-accordion-table">';
        echo '<thead>';
        echo '<tr>';

        for ( $i = 1; $i <= 5; $i++ ) {
            $label = $this->get_placeholder_label( $i );

            printf(
                '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--placeholder-%1$d">%2$s</th>',
                absint( $i ),
                esc_html( $label )
            );
        }

        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--actions">' . esc_html__( 'Actions', 'teqcidb' ) . '</th>';
        echo '</tr>';
        echo '</thead>';
        printf(
            '<tbody id="teqcidb-entity-list" data-per-page="%1$d" data-column-count="%2$d">',
            absint( $per_page ),
            absint( $column_count )
        );
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '<div class="tablenav"><div id="teqcidb-entity-pagination" class="tablenav-pages"></div></div>';
        echo '</div>';
        echo '<div id="teqcidb-entity-feedback" class="teqcidb-feedback-area teqcidb-feedback-area--block" role="status" aria-live="polite"></div>';
    }

    public function render_settings_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        echo '<div class="wrap"><h1>' . esc_html__( 'TEQCIDB Settings', 'teqcidb' ) . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=teqcidb-settings&tab=general" class="nav-tab ' . ( 'general' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'General Settings', 'teqcidb' ) . '</a>';
        echo '<a href="?page=teqcidb-settings&tab=style" class="nav-tab ' . ( 'style' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Style Settings', 'teqcidb' ) . '</a>';
        echo '<a href="?page=teqcidb-settings&tab=api" class="nav-tab ' . ( 'api' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'API Settings', 'teqcidb' ) . '</a>';
        echo '<a href="?page=teqcidb-settings&tab=upload" class="nav-tab ' . ( 'upload' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Upload', 'teqcidb' ) . '</a>';
        echo '<a href="?page=teqcidb-settings&tab=cron" class="nav-tab ' . ( 'cron' === $active_tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Cron Jobs', 'teqcidb' ) . '</a>';
        echo '</h2>';
        $tab_titles = array(
            'general' => __( 'General Settings', 'teqcidb' ),
            'style'   => __( 'Style Settings', 'teqcidb' ),
            'api'     => __( 'API Settings', 'teqcidb' ),
            'upload'  => __( 'Upload', 'teqcidb' ),
            'cron'    => __( 'Cron Jobs', 'teqcidb' ),
        );

        $tab_descriptions = array(
            'general' => __( 'Adjust the baseline configuration values that control how Thompson Engineering QCI Database behaves across your site.', 'teqcidb' ),
            'style'   => __( 'Apply design tweaks and CSS overrides to align the boilerplate output with your brand guidelines.', 'teqcidb' ),
            'api'     => __( 'Store external service credentials behind collapsible sections so each integration can be updated without leaving this page.', 'teqcidb' ),
            'upload'  => __( 'Paste a legacy student record and convert it into the current schema so it is stored in the new students table.', 'teqcidb' ),
            'cron'    => __( 'Review and manage every scheduled cron event created by Thompson Engineering QCI Database, including running or deleting hooks on demand.', 'teqcidb' ),
        );

        if ( ! array_key_exists( $active_tab, $tab_titles ) ) {
            $active_tab = 'general';
        }

        $title       = isset( $tab_titles[ $active_tab ] ) ? $tab_titles[ $active_tab ] : '';
        $description = isset( $tab_descriptions[ $active_tab ] ) ? $tab_descriptions[ $active_tab ] : '';

        $this->render_tab_intro( $title, $description );

        if ( 'style' === $active_tab ) {
            $this->render_style_settings_tab();
        } elseif ( 'api' === $active_tab ) {
            $this->render_api_settings_tab();
        } elseif ( 'upload' === $active_tab ) {
            $this->render_upload_settings_tab();
        } elseif ( 'cron' === $active_tab ) {
            $this->render_cron_jobs_tab();
        } else {
            $this->render_general_settings_tab();
        }

        echo '</div>';
    }

    private function render_general_settings_tab() {
        $settings = TEQCIDB_Settings_Helper::get_general_settings();

        $logging_fields = array(
            TEQCIDB_Settings_Helper::FIELD_LOG_EMAIL => array(
                'label'       => __( 'Email Logs', 'teqcidb' ),
                'description' => __( 'Capture email delivery activity so you can audit messages from the Email Logs tab.', 'teqcidb' ),
            ),
            TEQCIDB_Settings_Helper::FIELD_LOG_SMS => array(
                'label'       => __( 'SMS Logs', 'teqcidb' ),
                'description' => __( 'Store outbound SMS activity for future messaging diagnostics.', 'teqcidb' ),
            ),
            TEQCIDB_Settings_Helper::FIELD_LOG_PLUGIN_ERRORS => array(
                'label'       => __( 'Errors, notices, and warnings specific to this plugin only', 'teqcidb' ),
                'description' => __( 'Limit error tracking to issues related to Thompson Engineering QCI Database for targeted troubleshooting.', 'teqcidb' ),
            ),
            TEQCIDB_Settings_Helper::FIELD_LOG_PAYMENTS => array(
                'label'       => __( 'Payment logs', 'teqcidb' ),
                'description' => __( 'Retain payment gateway diagnostics and transaction context within the Payment Logs tab.', 'teqcidb' ),
            ),
        );

        echo '<form id="teqcidb-general-settings-form" class="teqcidb-settings-form">';
        echo '<table class="form-table" role="presentation">';

        $option_tooltip = esc_attr__( 'Tooltip placeholder text for Option', 'teqcidb' );
        $option_value   = isset( $settings[ TEQCIDB_Settings_Helper::FIELD_OPTION ] ) ? $settings[ TEQCIDB_Settings_Helper::FIELD_OPTION ] : '';

        echo '<tr>';
        echo '<th scope="row">';
        echo '<label for="teqcidb-general-option">' . esc_html__( 'Option', 'teqcidb' ) . ' <span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . $option_tooltip . '"></span></label>';
        echo '</th>';
        echo '<td>';
        echo '<input type="text" id="teqcidb-general-option" name="' . esc_attr( TEQCIDB_Settings_Helper::FIELD_OPTION ) . '" value="' . esc_attr( $option_value ) . '" class="regular-text" />';
        echo '</td>';
        echo '</tr>';

        foreach ( $logging_fields as $field_key => $field ) {
            $field_id   = 'teqcidb-general-' . str_replace( '_', '-', $field_key );
            $is_enabled = ! empty( $settings[ $field_key ] );

            echo '<tr>';
            echo '<th scope="row">' . esc_html( $field['label'] ) . '</th>';
            echo '<td>';
            echo '<label for="' . esc_attr( $field_id ) . '">';
            echo '<input type="checkbox" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_key ) . '" value="1" ' . checked( $is_enabled, true, false ) . ' />';
            echo ' ' . esc_html__( 'Enable logging', 'teqcidb' );
            echo '</label>';

            if ( ! empty( $field['description'] ) ) {
                echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';

        $submit_button = get_submit_button( __( 'Save Settings', 'teqcidb' ), 'primary', 'submit', false );
        echo '<p class="submit">' . $submit_button;
        echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span id="teqcidb-spinner" class="spinner" aria-hidden="true"></span><span id="teqcidb-feedback" role="status" aria-live="polite"></span></span>';
        echo '</p>';
        echo '</form>';
    }

    private function render_api_settings_tab() {
        $saved_settings = get_option( 'teqcidb_api_settings', array() );

        if ( ! is_array( $saved_settings ) ) {
            $saved_settings = array();
        }

        $apis = array(
            'payment_gateway' => array(
                'title'    => __( 'Payment Gateway', 'teqcidb' ),
                'category' => __( 'Payments', 'teqcidb' ),
                'fields' => array(
                    array(
                        'type'    => 'select',
                        'name'    => 'payment_gateway_environment',
                        'label'   => __( 'Environment', 'teqcidb' ),
                        'options' => array(
                            'live'    => __( 'Live', 'teqcidb' ),
                            'sandbox' => __( 'Sandbox', 'teqcidb' ),
                        ),
                    ),
                    array(
                        'type'  => 'text',
                        'name'  => 'payment_gateway_login_id',
                        'label' => __( 'Login ID', 'teqcidb' ),
                    ),
                    array(
                        'type'    => 'password',
                        'name'    => 'payment_gateway_transaction_key',
                        'label'   => __( 'Transaction Key', 'teqcidb' ),
                        'reveal'  => true,
                    ),
                ),
            ),
            'sms_service' => array(
                'title'    => __( 'SMS Service', 'teqcidb' ),
                'category' => __( 'Messaging', 'teqcidb' ),
                'fields'   => array(
                    array(
                        'type'    => 'select',
                        'name'    => 'sms_environment',
                        'label'   => __( 'Environment', 'teqcidb' ),
                        'options' => array(
                            'live'    => __( 'Live', 'teqcidb' ),
                            'sandbox' => __( 'Sandbox', 'teqcidb' ),
                        ),
                    ),
                    array(
                        'type'   => 'password',
                        'name'   => 'sms_messaging_service_sid',
                        'label'  => __( 'Messaging Service SID', 'teqcidb' ),
                        'reveal' => true,
                    ),
                    array(
                        'type'  => 'text',
                        'name'  => 'sms_sending_number',
                        'label' => __( 'Sending Number', 'teqcidb' ),
                    ),
                    array(
                        'type'  => 'text',
                        'name'  => 'sms_sandbox_number',
                        'label' => __( 'Sandbox Number', 'teqcidb' ),
                    ),
                    array(
                        'type'   => 'password',
                        'name'   => 'sms_user_sid',
                        'label'  => __( 'User SID', 'teqcidb' ),
                        'reveal' => true,
                    ),
                    array(
                        'type'   => 'password',
                        'name'   => 'sms_api_sid',
                        'label'  => __( 'API SID', 'teqcidb' ),
                        'reveal' => true,
                    ),
                    array(
                        'type'   => 'password',
                        'name'   => 'sms_api_key',
                        'label'  => __( 'API Key', 'teqcidb' ),
                        'reveal' => true,
                    ),
                ),
            ),
        );

        echo '<div class="teqcidb-api-settings teqcidb-communications teqcidb-communications--api-settings">';
        echo '<div class="teqcidb-accordion-group teqcidb-accordion-group--table" data-teqcidb-accordion-group="api-settings">';
        echo '<table class="wp-list-table widefat striped teqcidb-accordion-table teqcidb-accordion-table--api">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--title">' . esc_html__( 'API', 'teqcidb' ) . '</th>';
        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--category">' . esc_html__( 'Category', 'teqcidb' ) . '</th>';
        echo '<th scope="col" class="teqcidb-accordion__heading teqcidb-accordion__heading--actions">' . esc_html__( 'Actions', 'teqcidb' ) . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ( $apis as $api_key => $api ) {
            $summary_id = 'teqcidb-api-' . sanitize_html_class( $api_key ) . '-header';
            $panel_id   = 'teqcidb-api-' . sanitize_html_class( $api_key ) . '-panel';
            $form_id    = 'teqcidb-api-form-' . sanitize_html_class( $api_key );
            $spinner_id = $form_id . '-spinner';
            $feedback_id = $form_id . '-feedback';
            $saved_api_settings = isset( $saved_settings[ $api_key ] ) && is_array( $saved_settings[ $api_key ] ) ? $saved_settings[ $api_key ] : array();

            echo '<tr id="' . esc_attr( $summary_id ) . '" class="teqcidb-accordion__summary-row" tabindex="0" role="button" aria-expanded="false" aria-controls="' . esc_attr( $panel_id ) . '">';
            echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--title">';
            echo '<span class="teqcidb-accordion__title-text">' . esc_html( $api['title'] ) . '</span>';
            echo '</td>';
            echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--category">';
            $category = isset( $api['category'] ) ? $api['category'] : '';
            echo $category ? esc_html( $category ) : '&mdash;';
            echo '</td>';
            echo '<td class="teqcidb-accordion__cell teqcidb-accordion__cell--actions">';
            echo '<span class="teqcidb-accordion__action-link" aria-hidden="true">' . esc_html__( 'Configure', 'teqcidb' ) . '</span>';
            echo '<span class="dashicons dashicons-arrow-down-alt2 teqcidb-accordion__icon" aria-hidden="true"></span>';
            echo '</td>';
            echo '</tr>';

            echo '<tr id="' . esc_attr( $panel_id ) . '" class="teqcidb-accordion__panel-row" role="region" aria-labelledby="' . esc_attr( $summary_id ) . '" aria-hidden="true">';
            echo '<td colspan="3">';
            echo '<div class="teqcidb-accordion__panel">';
            echo '<form id="' . esc_attr( $form_id ) . '" class="teqcidb-api-settings__form" method="post">';
            echo '<input type="hidden" name="teqcidb_api_key" value="' . esc_attr( $api_key ) . '" />';
            echo '<div class="teqcidb-api-settings__fields">';

            foreach ( $api['fields'] as $field ) {
                $field_id = $field['name'];
                $saved_value = isset( $saved_api_settings[ $field['name'] ] ) ? $saved_api_settings[ $field['name'] ] : '';
                echo '<div class="teqcidb-api-settings__field">';
                echo '<label for="' . esc_attr( $field_id ) . '">' . esc_html( $field['label'] ) . '</label>';

                if ( 'select' === $field['type'] ) {
                    echo '<select id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field['name'] ) . '">';
                    foreach ( $field['options'] as $option_value => $option_label ) {
                        $selected = selected( $saved_value, $option_value, false );
                        echo '<option value="' . esc_attr( $option_value ) . '"' . $selected . '>' . esc_html( $option_label ) . '</option>';
                    }
                    echo '</select>';
                } else {
                    $input_type = esc_attr( $field['type'] );
                    $input_classes = 'regular-text';
                    $input_attributes = ' id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field['name'] ) . '"';
                    $input_value = esc_attr( $saved_value );

                    if ( ! empty( $field['reveal'] ) ) {
                        $show_label = __( 'Reveal', 'teqcidb' );
                        $hide_label = __( 'Hide', 'teqcidb' );
                        echo '<div class="teqcidb-api-settings__input-group">';
                        echo '<input type="' . $input_type . '" class="' . esc_attr( $input_classes ) . '"' . $input_attributes . ' value="' . $input_value . '" autocomplete="off" />';
                        echo '<button type="button" class="button button-secondary teqcidb-api-settings__toggle-visibility" data-target="#' . esc_attr( $field_id ) . '" data-label-show="' . esc_attr( $show_label ) . '" data-label-hide="' . esc_attr( $hide_label ) . '" aria-pressed="false">' . esc_html( $show_label ) . '</button>';
                        echo '</div>';
                    } else {
                        echo '<input type="' . $input_type . '" class="' . esc_attr( $input_classes ) . '"' . $input_attributes . ' value="' . $input_value . '" />';
                    }
                }

                echo '</div>';
            }

            echo '</div>';
            $submit_button = get_submit_button( __( 'Save API Settings', 'teqcidb' ), 'primary', 'submit', false );
            echo '<p class="submit">' . $submit_button;
            echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span id="' . esc_attr( $spinner_id ) . '" class="spinner" aria-hidden="true"></span><span id="' . esc_attr( $feedback_id ) . '" role="status" aria-live="polite"></span></span>';
            echo '</p>';
            echo '</form>';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    private function render_upload_settings_tab() {
        $example_record = "(2, 22, 'sammy.harton@greshamsmith.com1651589964', 'Samuel C.', 'Harton, Jr.', 'Gresham Smith', '2222 Arlington Ave. S. Suite 202', 'Birmingham', 'AL', '35205', '885 Third Avenue', 'Odenville', 'AL', '35120', '', '(205) 298-9216', '', 'sammy.harton@greshamsmith.com', 'https://training.thompsonengineering.com/wp-content/plugins/teqcidb/assets/img/student-image-placeholder.png', 'https://training.thompsonengineering.com/wp-content/plugins/teqcidb/assets/img/student-image-placeholder.png', '', '2025-06-23', '', '', '', 'true', NULL, NULL, NULL, 'none', '2026-06-26', 'T5703', '')";

        echo '<form id="teqcidb-legacy-upload-form" class="teqcidb-settings-form">';

        echo '<p>' . esc_html__( 'Paste a row (or many rows) from the selected legacy table exactly as it appears in the database export, or upload a .sql/text file that contains multiple rows. The importer will map fields into the new schema and store the record.', 'teqcidb' ) . '</p>';

        echo '<table class="form-table" role="presentation">';
        echo '<tr>';
        echo '<th scope="row">';
        echo '<label for="teqcidb-legacy-record">' . esc_html__( 'Legacy student record', 'teqcidb' ) . '</label>';
        echo '</th>';
        echo '<td>';
        echo '<textarea id="teqcidb-legacy-record" name="legacy_record" rows="8" class="large-text code" placeholder="' . esc_attr( $example_record ) . '"></textarea>';
        echo '<p class="description">' . esc_html__( 'Include full comma-separated rows, with or without wrapping parentheses. Multiple rows can be pasted at once; separate them with commas or new lines.', 'teqcidb' ) . '</p>';

        echo '<label for="teqcidb-legacy-file" class="screen-reader-text">' . esc_html__( 'Legacy upload file', 'teqcidb' ) . '</label>';
        echo '<input type="file" id="teqcidb-legacy-file" name="legacy_file" accept=".sql,.txt" />';
        echo '<p class="description">' . esc_html__( 'For thousands of legacy rows, choose a .sql/text export from the legacy table that lists one parenthesized row per line to import in one request.', 'teqcidb' ) . '</p>';

        echo '<p class="description">' . esc_html__( 'Choose what type of legacy record you are uploading.', 'teqcidb' ) . '</p>';
        echo '<fieldset class="teqcidb-legacy-upload__types">';
        echo '<legend class="screen-reader-text">' . esc_html__( 'Legacy upload types', 'teqcidb' ) . '</legend>';

        $upload_types = array(
            'student'        => __( 'Upload Legacy Student Records', 'teqcidb' ),
            'class'          => __( 'Upload Legacy Class Records', 'teqcidb' ),
            'studenthistory' => __( 'Upload Legacy Student History Records', 'teqcidb' ),
        );

        foreach ( $upload_types as $type_value => $type_label ) {
            $field_id = 'teqcidb-legacy-type-' . esc_attr( $type_value );
            echo '<label for="' . esc_attr( $field_id ) . '" class="teqcidb-legacy-upload__type">';
            echo '<input type="checkbox" id="' . esc_attr( $field_id ) . '" name="legacy_types[]" value="' . esc_attr( $type_value ) . '"' . checked( 'student', $type_value, false ) . ' /> ';
            echo esc_html( $type_label );
            echo '</label><br />';
        }

        echo '<p class="description">' . esc_html__( 'Select one upload type at a time. More options can be added here in the future.', 'teqcidb' ) . '</p>';
        echo '</fieldset>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';

        $submit_button = get_submit_button( __( 'Upload Legacy Record', 'teqcidb' ), 'primary', 'submit', false );

        echo '<p class="submit">' . $submit_button;
        echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline">';
        echo '<span class="spinner" aria-hidden="true"></span>';
        echo '<span role="status" aria-live="polite"></span>';
        echo '</span>';
        echo '</p>';

        echo '</form>';
    }

    private function render_style_settings_tab() {
        echo '<form id="teqcidb-style-settings-form">';
        echo '<label>' . esc_html__( 'Custom CSS', 'teqcidb' ) . ' <span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr__( 'Tooltip placeholder text for Custom CSS', 'teqcidb' ) . '"></span></label>';
        echo '<textarea name="custom_css"></textarea>';
        $submit_button = get_submit_button( __( 'Save Settings', 'teqcidb' ), 'primary', 'submit', false );
        echo '<p class="submit">' . $submit_button;
        echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span id="teqcidb-spinner" class="spinner" aria-hidden="true"></span><span id="teqcidb-feedback" role="status" aria-live="polite"></span></span>';
        echo '</p>';
        echo '</form>';
    }

    private function render_cron_jobs_tab() {
        echo '<div class="teqcidb-cron-tab">';

        $messages = array(
            'deleted'       => array(
                'type'    => 'success',
                'message' => __( 'Cron event deleted successfully.', 'teqcidb' ),
            ),
            'delete_failed' => array(
                'type'    => 'error',
                'message' => __( 'Unable to delete the cron event. Please try again.', 'teqcidb' ),
            ),
            'run'           => array(
                'type'    => 'success',
                'message' => __( 'Cron event executed immediately.', 'teqcidb' ),
            ),
            'run_failed'    => array(
                'type'    => 'error',
                'message' => __( 'Unable to execute the cron event. Ensure the hook is registered.', 'teqcidb' ),
            ),
        );

        $notice_key = isset( $_GET['teqcidb_cron_message'] ) ? sanitize_text_field( wp_unslash( $_GET['teqcidb_cron_message'] ) ) : '';

        if ( $notice_key && isset( $messages[ $notice_key ] ) ) {
            $notice = $messages[ $notice_key ];
            printf(
                '<div class="notice notice-%1$s"><p>%2$s</p></div>',
                esc_attr( $notice['type'] ),
                esc_html( $notice['message'] )
            );
        }

        $events    = TEQCIDB_Cron_Manager::get_plugin_cron_events();
        $per_page  = 20;
        $total     = count( $events );
        $page      = isset( $_GET['teqcidb_cron_page'] ) ? max( 1, absint( wp_unslash( $_GET['teqcidb_cron_page'] ) ) ) : 1;
        $max_pages = max( 1, (int) ceil( $total / $per_page ) );

        if ( $page > $max_pages ) {
            $page = $max_pages;
        }

        $offset          = ( $page - 1 ) * $per_page;
        $displayed_events = array_slice( $events, $offset, $per_page );

        $pagination_base = add_query_arg(
            array(
                'page' => 'teqcidb-settings',
                'tab'  => 'cron',
                'teqcidb_cron_page' => '%#%',
            ),
            admin_url( 'admin.php' )
        );

        $pagination = paginate_links(
            array(
                'base'      => $pagination_base,
                'format'    => '%#%',
                'current'   => $page,
                'total'     => $max_pages,
                'prev_text' => __( '&laquo; Previous', 'teqcidb' ),
                'next_text' => __( 'Next &raquo;', 'teqcidb' ),
                'type'      => 'list',
            )
        );

        if ( $pagination ) {
            echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post( $pagination ) . '</div></div>';
        }

        echo '<table class="widefat striped teqcidb-cron-table">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( 'Cron Job', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Description', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Type', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Schedule', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Hook', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Next Run', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Countdown', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Arguments', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Actions', 'teqcidb' ) . '</th>';
        echo '</tr></thead><tbody>';

        if ( empty( $displayed_events ) ) {
            echo '<tr><td colspan="9">' . esc_html__( 'No cron events found for Thompson Engineering QCI Database.', 'teqcidb' ) . '</td></tr>';
        } else {
            $redirect = add_query_arg(
                array(
                    'page' => 'teqcidb-settings',
                    'tab'  => 'cron',
                ),
                admin_url( 'admin.php' )
            );

            if ( $page > 1 ) {
                $redirect = add_query_arg( 'teqcidb_cron_page', $page, $redirect );
            }

            foreach ( $displayed_events as $event ) {
                $hook_data      = TEQCIDB_Cron_Manager::get_hook_display_data( $event['hook'] );
                $type_label     = TEQCIDB_Cron_Manager::is_recurring( $event['schedule'] ) ? esc_html__( 'Recurring', 'teqcidb' ) : esc_html__( 'One-off', 'teqcidb' );
                $schedule_label = TEQCIDB_Cron_Manager::get_schedule_label( $event['schedule'], $event['interval'] );
                $next_run       = TEQCIDB_Cron_Manager::format_timestamp( $event['timestamp'] );
                $countdown      = TEQCIDB_Cron_Manager::get_countdown( $event['timestamp'] );
                $args_display   = empty( $event['args'] ) ? '&mdash;' : esc_html( wp_json_encode( $event['args'] ) );
                $args_encoded   = base64_encode( wp_json_encode( $event['args'] ) );

                if ( false === $args_encoded ) {
                    $args_encoded = '';
                }

                echo '<tr>';
                echo '<td><strong>' . esc_html( $hook_data['name'] ) . '</strong> <span class="teqcidb-tooltip-icon dashicons dashicons-editor-help" data-tooltip="' . esc_attr( $hook_data['description'] ) . '"></span></td>';
                echo '<td>' . esc_html( $hook_data['description'] ) . '</td>';
                echo '<td>' . esc_html( $type_label ) . '</td>';
                echo '<td>' . esc_html( $schedule_label ) . '</td>';
                echo '<td><code>' . esc_html( $event['hook'] ) . '</code></td>';
                echo '<td>' . esc_html( $next_run ) . '</td>';
                echo '<td>' . esc_html( $countdown ) . '</td>';
                echo '<td>' . ( empty( $event['args'] ) ? '&mdash;' : $args_display ) . '</td>';
                echo '<td>';
                echo '<div class="teqcidb-cron-actions">';
                echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="teqcidb-cron-action-form">';
                wp_nonce_field( 'teqcidb_run_cron_event', 'teqcidb_run_cron_event_nonce' );
                echo '<input type="hidden" name="action" value="teqcidb_run_cron_event" />';
                echo '<input type="hidden" name="hook" value="' . esc_attr( $event['hook'] ) . '" />';
                echo '<input type="hidden" name="args" value="' . esc_attr( $args_encoded ) . '" />';
                echo '<input type="hidden" name="redirect" value="' . esc_attr( $redirect ) . '" />';
                echo '<button type="submit" class="button button-secondary">' . esc_html__( 'Run Now', 'teqcidb' ) . '</button>';
                echo '</form>';

                $confirm = esc_js( __( 'Are you sure you want to delete this cron event?', 'teqcidb' ) );

                echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="teqcidb-cron-action-form" onsubmit="return confirm(\'' . $confirm . '\');">';
                wp_nonce_field( 'teqcidb_delete_cron_event', 'teqcidb_delete_cron_event_nonce' );
                echo '<input type="hidden" name="action" value="teqcidb_delete_cron_event" />';
                echo '<input type="hidden" name="hook" value="' . esc_attr( $event['hook'] ) . '" />';
                echo '<input type="hidden" name="timestamp" value="' . esc_attr( $event['timestamp'] ) . '" />';
                echo '<input type="hidden" name="args" value="' . esc_attr( $args_encoded ) . '" />';
                echo '<input type="hidden" name="redirect" value="' . esc_attr( $redirect ) . '" />';
                echo '<button type="submit" class="button button-link-delete">' . esc_html__( 'Delete Event', 'teqcidb' ) . '</button>';
                echo '</form>';
                echo '</div>';
                echo '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';

        if ( $pagination ) {
            echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post( $pagination ) . '</div></div>';
        }

        echo '</div>';
    }

    private function decode_cron_args( $encoded ) {
        if ( empty( $encoded ) ) {
            return array();
        }

        $decoded = base64_decode( wp_unslash( $encoded ), true );

        if ( false === $decoded ) {
            return array();
        }

        $args = json_decode( $decoded, true );

        return is_array( $args ) ? $args : array();
    }

    private function get_cron_redirect_url() {
        $fallback = add_query_arg(
            array(
                'page' => 'teqcidb-settings',
                'tab'  => 'cron',
            ),
            admin_url( 'admin.php' )
        );

        if ( empty( $_POST['redirect'] ) ) {
            return $fallback;
        }

        $redirect = esc_url_raw( wp_unslash( $_POST['redirect'] ) );

        return $redirect ? $redirect : $fallback;
    }

    private function redirect_with_cron_message( $redirect, $message ) {
        $url = add_query_arg( 'teqcidb_cron_message', $message, $redirect );
        wp_safe_redirect( $url );
        exit;
    }

    public function handle_delete_cron_event() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'teqcidb' ) );
        }

        check_admin_referer( 'teqcidb_delete_cron_event', 'teqcidb_delete_cron_event_nonce' );

        $redirect = $this->get_cron_redirect_url();
        $hook     = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';
        $timestamp = isset( $_POST['timestamp'] ) ? absint( wp_unslash( $_POST['timestamp'] ) ) : 0;
        $args     = $this->decode_cron_args( isset( $_POST['args'] ) ? $_POST['args'] : '' );

        if ( empty( $hook ) || 0 !== strpos( $hook, TEQCIDB_Cron_Manager::HOOK_PREFIX ) || empty( $timestamp ) ) {
            $this->redirect_with_cron_message( $redirect, 'delete_failed' );
        }

        $deleted = wp_unschedule_event( $timestamp, $hook, $args );

        if ( $deleted ) {
            $this->redirect_with_cron_message( $redirect, 'deleted' );
        }

        $this->redirect_with_cron_message( $redirect, 'delete_failed' );
    }

    public function handle_run_cron_event() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'teqcidb' ) );
        }

        check_admin_referer( 'teqcidb_run_cron_event', 'teqcidb_run_cron_event_nonce' );

        $redirect = $this->get_cron_redirect_url();
        $hook     = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';
        $args     = $this->decode_cron_args( isset( $_POST['args'] ) ? $_POST['args'] : '' );

        if ( empty( $hook ) || 0 !== strpos( $hook, TEQCIDB_Cron_Manager::HOOK_PREFIX ) ) {
            $this->redirect_with_cron_message( $redirect, 'run_failed' );
        }

        if ( ! has_action( $hook ) ) {
            $this->redirect_with_cron_message( $redirect, 'run_failed' );
        }

        do_action_ref_array( $hook, $args );

        $this->redirect_with_cron_message( $redirect, 'run' );
    }

    public function render_logs_page() {
        $tabs = array(
            'generated_content' => __( 'Generated Content', 'teqcidb' ),
            'error_logs'        => __( 'Error Logs', 'teqcidb' ),
            'payment_logs'      => __( 'Payment Logs', 'teqcidb' ),
        );

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'generated_content';

        if ( ! array_key_exists( $active_tab, $tabs ) ) {
            $active_tab = 'generated_content';
        }

        echo '<div class="wrap"><h1>' . esc_html__( 'TEQCIDB Logs', 'teqcidb' ) . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';

        foreach ( $tabs as $tab_slug => $label ) {
            $classes = array( 'nav-tab' );

            if ( $tab_slug === $active_tab ) {
                $classes[] = 'nav-tab-active';
            }

            printf(
                '<a href="%1$s" class="%2$s">%3$s</a>',
                esc_url( add_query_arg( array( 'page' => 'teqcidb-logs', 'tab' => $tab_slug ), admin_url( 'admin.php' ) ) ),
                esc_attr( implode( ' ', $classes ) ),
                esc_html( $label )
            );
        }

        echo '</h2>';
        $tab_descriptions = array(
            'generated_content' => __( 'Inspect saved content entries and jump to editing, viewing, or deleting items created by the logger.', 'teqcidb' ),
            'error_logs'        => __( 'Review PHP notices captured for the Thompson Engineering QCI Database features.', 'teqcidb' ),
            'payment_logs'      => __( 'Monitor payment-related activity and capture diagnostics for future transaction workflows.', 'teqcidb' ),
        );

        $title       = isset( $tabs[ $active_tab ] ) ? $tabs[ $active_tab ] : '';
        $description = isset( $tab_descriptions[ $active_tab ] ) ? $tab_descriptions[ $active_tab ] : '';

        $this->render_tab_intro( $title, $description );

        if ( 'generated_content' === $active_tab ) {
            $this->render_generated_content_log();
        } elseif ( 'error_logs' === $active_tab ) {
            $this->render_error_logs_tab();
        } elseif ( 'payment_logs' === $active_tab ) {
            $this->render_payment_logs_tab();
        }

        echo '</div>';
    }

    private function render_generated_content_log() {
        $logger  = new TEQCIDB_Content_Logger();
        $entries = $logger->get_logged_content();
        echo '<table class="widefat"><thead><tr>';
        echo '<th>' . esc_html__( 'Title', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Type', 'teqcidb' ) . '</th>';
        echo '<th>' . esc_html__( 'Actions', 'teqcidb' ) . '</th>';
        echo '</tr></thead><tbody>';
        if ( $entries ) {
            foreach ( $entries as $entry ) {
                $post = get_post( $entry->post_id );
                if ( ! $post ) {
                    continue;
                }
                $view   = get_permalink( $post );
                $edit   = get_edit_post_link( $post->ID );
                $delete = wp_nonce_url( admin_url( 'admin-post.php?action=teqcidb_delete_generated_content&post_id=' . $post->ID ), 'teqcidb_delete_generated_content_' . $post->ID );
                echo '<tr>';
                echo '<td><a href="' . esc_url( $view ) . '" target="_blank">' . esc_html( get_the_title( $post ) ) . '</a></td>';
                echo '<td>' . esc_html( ucfirst( $entry->post_type ) ) . '</td>';
                echo '<td><a href="' . esc_url( $edit ) . '">' . esc_html__( 'Edit', 'teqcidb' ) . '</a> | ';
                $confirm = esc_js( __( 'Are you sure you want to delete this item?', 'teqcidb' ) );
                echo '<a href="' . esc_url( $delete ) . '" onclick="return confirm(\'' . $confirm . '\');">' . esc_html__( 'Delete', 'teqcidb' ) . '</a></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="3">' . esc_html__( 'No generated content found.', 'teqcidb' ) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    private function render_error_logs_tab() {
        $sections = array(
            array(
                'scope'       => TEQCIDB_Error_Log_Helper::SCOPE_PLUGIN,
                'title'       => __( 'TEQCIDB-Related errors/notices/warnings', 'teqcidb' ),
                /* translators: description for the plugin scoped error log textarea. */
                'description' => __( 'Focused on Thompson Engineering QCI Database functionality—covering all current features and anything we build in the future.', 'teqcidb' ),
                'channel'     => TEQCIDB_Settings_Helper::FIELD_LOG_PLUGIN_ERRORS,
            ),
        );

        $this->render_log_sections( $sections );
    }

    private function render_payment_logs_tab() {
        $sections = array(
            array(
                'scope'       => TEQCIDB_Error_Log_Helper::SCOPE_PAYMENTS,
                'title'       => __( 'Payment activity logs', 'teqcidb' ),
                /* translators: description for the payment log textarea. */
                'description' => __( 'Tracks payment gateway notices, API responses, and transaction diagnostics without storing sensitive card data.', 'teqcidb' ),
                'empty'       => __( 'No payment activity logged yet.', 'teqcidb' ),
                'channel'     => TEQCIDB_Settings_Helper::FIELD_LOG_PAYMENTS,
            ),
        );

        $this->render_log_sections( $sections );
    }

    private function render_log_sections( array $sections ) {
        if ( empty( $sections ) ) {
            return;
        }

        echo '<div class="teqcidb-error-logs">';

        foreach ( $sections as $section ) {
            if ( empty( $section['scope'] ) ) {
                continue;
            }

            $scope = TEQCIDB_Error_Log_Helper::normalize_scope( $section['scope'] );

            if ( '' === $scope ) {
                continue;
            }

            $log_contents = TEQCIDB_Error_Log_Helper::get_log_contents( $scope );
            $textarea_id  = 'teqcidb-log-' . $scope;
            $heading_id   = 'teqcidb-log-heading-' . $scope;
            $title        = isset( $section['title'] ) ? $section['title'] : '';
            $description  = isset( $section['description'] ) ? $section['description'] : '';
            $empty_text   = isset( $section['empty'] ) ? $section['empty'] : __( 'No log entries recorded yet.', 'teqcidb' );
            $empty_notice = '' === trim( $log_contents ) ? $empty_text : '';
            $channel      = isset( $section['channel'] ) ? $section['channel'] : '';

            echo '<section class="teqcidb-error-logs__section">';
            echo '<h3 id="' . esc_attr( $heading_id ) . '" class="teqcidb-error-logs__heading">' . esc_html( $title ) . '</h3>';

            if ( $channel ) {
                $this->render_logging_status_notice( $channel );
            }

            if ( $description ) {
                echo '<p class="teqcidb-error-logs__description">' . esc_html( $description ) . '</p>';
            }

            if ( $empty_notice ) {
                echo '<p class="teqcidb-error-logs__empty" role="status" aria-live="polite">' . esc_html( $empty_notice ) . '</p>';
            }

            echo '<textarea id="' . esc_attr( $textarea_id ) . '" class="teqcidb-error-logs__textarea" rows="12" readonly aria-labelledby="' . esc_attr( $heading_id ) . '">';
            echo esc_textarea( $log_contents );
            echo '</textarea>';

            echo '<div class="teqcidb-error-logs__actions">';

            echo '<form class="teqcidb-log-actions__form teqcidb-log-actions__form--clear" data-ajax-action="teqcidb_clear_error_log" data-log-action="clear" data-log-target="#' . esc_attr( $textarea_id ) . '">';
            echo '<input type="hidden" name="scope" value="' . esc_attr( $scope ) . '" />';
            echo '<button type="submit" class="button button-secondary">' . esc_html__( 'Clear Logs', 'teqcidb' ) . '</button>';
            echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span class="spinner" aria-hidden="true"></span><span role="status" aria-live="polite"></span></span>';
            echo '</form>';

            echo '<form class="teqcidb-log-actions__form teqcidb-log-actions__form--download" data-ajax-action="teqcidb_download_error_log" data-log-action="download">';
            echo '<input type="hidden" name="scope" value="' . esc_attr( $scope ) . '" />';
            echo '<button type="submit" class="button button-secondary">' . esc_html__( 'Download Logs', 'teqcidb' ) . '</button>';
            echo '<span class="teqcidb-feedback-area teqcidb-feedback-area--inline"><span class="spinner" aria-hidden="true"></span><span role="status" aria-live="polite"></span></span>';
            echo '</form>';

            echo '</div>';
            echo '</section>';
        }

        echo '</div>';
    }

    public function handle_download_email_log() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to download the email log.', 'teqcidb' ) );
        }

        check_admin_referer( 'teqcidb_download_email_log', 'teqcidb_email_log_nonce' );

        if ( ! TEQCIDB_Email_Log_Helper::is_log_available() ) {
            wp_die( esc_html__( 'The email log could not be found. Check upload directory permissions.', 'teqcidb' ) );
        }

        $contents = TEQCIDB_Email_Log_Helper::get_log_contents();
        $filename = TEQCIDB_Email_Log_Helper::get_download_filename();

        if ( '' === $filename ) {
            $filename = 'teqcidb-email-log.txt';
        }

        $filename = sanitize_file_name( $filename );

        if ( '' === $contents ) {
            $contents = '';
        }

        nocache_headers();
        header( 'Content-Type: text/plain; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . strlen( $contents ) );

        echo $contents;
        exit;
    }

    public function handle_delete_generated_content() {
        if ( ! current_user_can( 'delete_posts' ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'teqcidb' ) );
        }
        $post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
        check_admin_referer( 'teqcidb_delete_generated_content_' . $post_id );
        wp_delete_post( $post_id, true );
        wp_redirect( admin_url( 'admin.php?page=teqcidb-logs&tab=generated_content' ) );
        exit;
    }
}
