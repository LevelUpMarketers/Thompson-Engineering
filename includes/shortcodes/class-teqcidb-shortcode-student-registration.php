<?php
/**
 * Shortcode for displaying student class registration content.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Shortcode_Student_Registration {

    const SHORTCODE_TAG = 'teqcidb_student_registration_shortcode';

    /**
     * Student dashboard shortcode instance.
     *
     * @var TEQCIDB_Shortcode_Student_Dashboard
     */
    private $dashboard_shortcode;

    /**
     * Authorize.Net service helper.
     *
     * @var TEQCIDB_AuthorizeNet_Service
     */
    private $authorizenet_service;

    /**
     * Constructor.
     *
     * @param TEQCIDB_Shortcode_Student_Dashboard|null $dashboard_shortcode Optional existing dashboard shortcode instance.
     * @param TEQCIDB_AuthorizeNet_Service|null        $authorizenet_service Optional existing Authorize.Net service instance.
     */
    public function __construct( TEQCIDB_Shortcode_Student_Dashboard $dashboard_shortcode = null, TEQCIDB_AuthorizeNet_Service $authorizenet_service = null ) {
        $this->dashboard_shortcode = $dashboard_shortcode instanceof TEQCIDB_Shortcode_Student_Dashboard
            ? $dashboard_shortcode
            : new TEQCIDB_Shortcode_Student_Dashboard();
        $this->authorizenet_service = $authorizenet_service instanceof TEQCIDB_AuthorizeNet_Service
            ? $authorizenet_service
            : new TEQCIDB_AuthorizeNet_Service();
    }

    /**
     * Register shortcode and frontend assets.
     */
    public function register() {
        add_shortcode( self::SHORTCODE_TAG, array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Render shortcode output.
     *
     * @param array  $atts    Shortcode attributes.
     * @param string $content Shortcode content.
     *
     * @return string
     */
    public function render( $atts = array(), $content = '' ) {
        if ( ! is_user_logged_in() ) {
            return $this->dashboard_shortcode->render( $atts, $content );
        }

        $classes = $this->get_visible_classes_for_registration();
        $authorize_settings = $this->authorizenet_service->get_payment_gateway_settings();
        $authorize_environment = isset( $authorize_settings[ TEQCIDB_AuthorizeNet_Service::FIELD_ENVIRONMENT ] )
            ? $authorize_settings[ TEQCIDB_AuthorizeNet_Service::FIELD_ENVIRONMENT ]
            : 'sandbox';
        $authorize_has_credentials = $this->authorizenet_service->has_credentials() ? 'yes' : 'no';
        $authorize_hosted_post_url = $this->authorizenet_service->get_accept_hosted_iframe_url();

        ob_start();
        ?>
        <section
            class="teqcidb-registration-section teqcidb-registration-classes"
            data-teqcidb-registration="true"
            data-authorizenet-environment="<?php echo esc_attr( $authorize_environment ); ?>"
            data-authorizenet-has-credentials="<?php echo esc_attr( $authorize_has_credentials ); ?>"
            data-authorizenet-hosted-post-url="<?php echo esc_url( $authorize_hosted_post_url ); ?>"
        >
            <?php if ( ! empty( $classes ) ) : ?>
                <div class="teqcidb-registration-class-list" role="list">
                    <?php foreach ( $classes as $index => $class ) : ?>
                        <?php
                        $accordion_id = 'teqcidb-registration-class-' . $index;
                        $panel_id     = $accordion_id . '-panel';
                        ?>
                        <div class="teqcidb-registration-class-item" role="listitem">
                            <button
                                class="teqcidb-dashboard-tab teqcidb-registration-class-toggle"
                                type="button"
                                id="<?php echo esc_attr( $accordion_id ); ?>"
                                aria-expanded="false"
                                aria-controls="<?php echo esc_attr( $panel_id ); ?>"
                            >
                                <span class="teqcidb-registration-class-name"><?php echo esc_html( $class['classname'] ); ?></span>
                                <span class="teqcidb-registration-class-date"><?php echo esc_html( $class['classstartdate'] ); ?></span>
                            </button>
                            <div
                                class="teqcidb-registration-class-panel"
                                id="<?php echo esc_attr( $panel_id ); ?>"
                                role="region"
                                aria-labelledby="<?php echo esc_attr( $accordion_id ); ?>"
                                hidden
                            >
                                <div class="teqcidb-registration-class-description">
                                    <h3 class="teqcidb-registration-class-section-title">
                                        <?php echo esc_html_x( 'Class Description', 'Student registration class detail label', 'teqcidb' ); ?>
                                    </h3>
                                    <div class="teqcidb-registration-class-description-content">
                                        <?php echo wp_kses_post( wpautop( $class['classdescription'] ) ); ?>
                                    </div>
                                </div>

                                <dl class="teqcidb-registration-class-details">
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'Class Cost', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['classcost'] ); ?></dd>
                                    </div>
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'Class Type', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['classtype'] ); ?></dd>
                                    </div>
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'Class Format', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['classformat'] ); ?></dd>
                                    </div>
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'Class Date', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['classstartdate'] ); ?></dd>
                                    </div>
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'Class Start Time', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['classstarttime'] ); ?></dd>
                                    </div>
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'Class End Time', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['classendtime'] ); ?></dd>
                                    </div>
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'Street Address', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['street_address'] ); ?></dd>
                                    </div>
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'City', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['city'] ); ?></dd>
                                    </div>
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'State', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['state'] ); ?></dd>
                                    </div>
                                    <div class="teqcidb-registration-class-detail">
                                        <dt><?php echo esc_html_x( 'Zip Code', 'Student registration class detail label', 'teqcidb' ); ?></dt>
                                        <dd><?php echo esc_html( $class['zip_code'] ); ?></dd>
                                    </div>
                                </dl>

                                <div class="teqcidb-registration-payment" data-teqcidb-registration-payment="<?php echo esc_attr( $class['class_id'] ); ?>">
                                    <div class="teqcidb-registration-payment-policy" role="note">
                                        <p class="teqcidb-registration-payment-policy-intro"><?php echo esc_html_x( 'Please read this information before completing your registration and payment below!', 'Student registration payment policy intro text', 'teqcidb' ); ?></p>
                                        <p>
                                            <strong><?php echo esc_html_x( 'Cancellation & Payment Policy:', 'Student registration payment policy heading', 'teqcidb' ); ?></strong>
                                            <?php echo esc_html_x( 'Registration fees for in-person classes and online courses are non-refundable. Payment is requested prior to or on the date of the training. In certain situations, we may issue credits that are good for one year from the original (initial) training date. These credits may be transferable to another employee of the same company/organization. We do not issue credits for online refresher training fees.', 'Student registration payment policy details', 'teqcidb' ); ?>
                                        </p>
                                        <p><?php echo esc_html_x( 'Certificates of completion and QCI numbers issued upon completion of training and receipt of payment.', 'Student registration payment policy completion details', 'teqcidb' ); ?></p>
                                        <p>
                                            <?php
                                            echo wp_kses(
                                                sprintf(
                                                    /* translators: %1$s: phone link open tag, %2$s: phone link close tag. */
                                                    __( 'For more information or clarification, please call %1$s(251) 666-2443%2$s.', 'teqcidb' ),
                                                    '<a href="tel:2516662443">',
                                                    '</a>'
                                                ),
                                                array(
                                                    'a' => array(
                                                        'href' => true,
                                                    ),
                                                )
                                            );
                                            ?>
                                        </p>
                                        <p>
                                            <?php
                                            echo wp_kses(
                                                sprintf(
                                                    /* translators: %1$s: email link open tag, %2$s: email link close tag. */
                                                    __( 'If you choose to register by registration form, please email the completed form to %1$sQCI@thompsonengineering.com%2$s, or mail to the address below. Payments can be mailed to this address as well.', 'teqcidb' ),
                                                    '<a href="mailto:QCI@thompsonengineering.com">',
                                                    '</a>'
                                                ),
                                                array(
                                                    'a' => array(
                                                        'href' => true,
                                                    ),
                                                )
                                            );
                                            ?>
                                        </p>
                                        <p class="teqcidb-registration-payment-policy-address">
                                            <?php echo esc_html_x( 'Thompson Engineering', 'Student registration mailing address line 1', 'teqcidb' ); ?><br>
                                            <?php echo esc_html_x( 'ATTN: QCI Program', 'Student registration mailing address line 2', 'teqcidb' ); ?><br>
                                            <?php echo esc_html_x( '2970 Cottage Hill Road, Suite 190', 'Student registration mailing address line 3', 'teqcidb' ); ?><br>
                                            <?php echo esc_html_x( 'Mobile, AL 36606', 'Student registration mailing address line 4', 'teqcidb' ); ?>
                                        </p>
                                    </div>

                                    <div class="teqcidb-registration-payment-actions">
                                        <button
                                            type="button"
                                            class="teqcidb-dashboard-button teqcidb-registration-pay-button"
                                            data-teqcidb-registration-pay
                                            data-class-id="<?php echo esc_attr( $class['class_id'] ); ?>"
                                        >
                                            <?php echo esc_html_x( 'Register & Pay Online', 'Student registration checkout button label', 'teqcidb' ); ?>
                                        </button>
                                        <button
                                            type="button"
                                            class="teqcidb-dashboard-button teqcidb-registration-pay-button teqcidb-registration-form-button"
                                            onclick="window.open('<?php echo esc_url( 'https://training.thompsonengineering.com/wp-content/uploads/2024/07/1-QCI-FORM-2024-NEW.pdf' ); ?>', '_blank', 'noopener');"
                                        >
                                            <?php echo esc_html_x( 'Print & Email Your Registration Form', 'Student registration printable form button label', 'teqcidb' ); ?>
                                        </button>
                                    </div>
                                    <div class="teqcidb-form-feedback teqcidb-registration-payment-feedback" aria-live="polite" aria-atomic="true">
                                        <span class="spinner is-active" aria-hidden="true"></span>
                                        <span class="teqcidb-form-message"></span>
                                    </div>

                                    <form
                                        method="post"
                                        target="teqcidb-authorizenet-iframe-<?php echo esc_attr( $class['class_id'] ); ?>"
                                        class="teqcidb-registration-payment-form"
                                        data-teqcidb-registration-payment-form
                                        action="<?php echo esc_url( $authorize_hosted_post_url ); ?>"
                                    >
                                        <input type="hidden" name="token" value="" data-teqcidb-registration-token>
                                    </form>

                                    <iframe
                                        class="teqcidb-registration-payment-iframe"
                                        name="teqcidb-authorizenet-iframe-<?php echo esc_attr( $class['class_id'] ); ?>"
                                        title="<?php echo esc_attr( sprintf( /* translators: %s: class name. */ __( 'Secure payment for %s', 'teqcidb' ), $class['classname'] ) ); ?>"
                                        data-teqcidb-registration-iframe
                                        loading="lazy"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="teqcidb-dashboard-empty">
                    <?php
                    echo esc_html_x(
                        'No classes are currently available for registration.',
                        'Student registration shortcode empty state text',
                        'teqcidb'
                    );
                    ?>
                </p>
            <?php endif; ?>
        </section>
        <?php

        return ob_get_clean();
    }

    /**
     * Retrieve visible classes ordered with upcoming classes first.
     *
     * @return array<int, array<string, string>>
     */
    private function get_visible_classes_for_registration() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'teqcidb_classes';
        $like       = $wpdb->esc_like( $table_name );
        $found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $table_name ) {
            return array();
        }

        $today = wp_date( 'Y-m-d' );

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, classname, classdescription, classcost, classtype, classformat, classstartdate, classstarttime, classendtime, classsaddress
                FROM $table_name
                WHERE COALESCE(classhide, 0) <> 1
                ORDER BY CASE WHEN classstartdate >= %s THEN 0 ELSE 1 END ASC, classstartdate ASC, classname ASC, id ASC",
                $today
            ),
            ARRAY_A
        );

        if ( ! is_array( $rows ) ) {
            return array();
        }

        $classes = array();

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $class_name = isset( $row['classname'] ) ? sanitize_text_field( (string) $row['classname'] ) : '';

            if ( '' === $class_name ) {
                continue;
            }

            $address = $this->decode_class_address_field( isset( $row['classsaddress'] ) ? (string) $row['classsaddress'] : '' );

            $classes[] = array(
                'class_id'       => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
                'classname'      => $class_name,
                'classdescription' => $this->format_description_for_display( isset( $row['classdescription'] ) ? $row['classdescription'] : '' ),
                'classcost'      => $this->format_cost_for_display( isset( $row['classcost'] ) ? $row['classcost'] : '' ),
                'classtype'      => $this->format_label_for_display( isset( $row['classtype'] ) ? $row['classtype'] : '' ),
                'classformat'    => $this->format_label_for_display( isset( $row['classformat'] ) ? $row['classformat'] : '' ),
                'classstartdate' => $this->format_class_start_date_for_display( isset( $row['classstartdate'] ) ? $row['classstartdate'] : '' ),
                'classstarttime' => $this->format_time_for_display( isset( $row['classstarttime'] ) ? $row['classstarttime'] : '' ),
                'classendtime'   => $this->format_time_for_display( isset( $row['classendtime'] ) ? $row['classendtime'] : '' ),
                'street_address' => $this->compose_street_address_for_display( $address ),
                'city'           => $this->fallback_display_value( isset( $address['city'] ) ? $address['city'] : '' ),
                'state'          => $this->fallback_display_value( isset( $address['state'] ) ? $address['state'] : '' ),
                'zip_code'       => $this->fallback_display_value( isset( $address['postal_code'] ) ? $address['postal_code'] : '' ),
            );
        }

        return $classes;
    }

    /**
     * Decode class address JSON into component fields.
     *
     * @param string $value Stored JSON value.
     *
     * @return array{street_1:string,street_2:string,city:string,state:string,postal_code:string}
     */
    private function decode_class_address_field( $value ) {
        $defaults = array(
            'street_1'    => '',
            'street_2'    => '',
            'city'        => '',
            'state'       => '',
            'postal_code' => '',
        );

        if ( '' === $value ) {
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

    /**
     * Format class description text for output.
     *
     * @param string $value Raw description text.
     *
     * @return string
     */
    private function format_description_for_display( $value ) {
        $description = sanitize_textarea_field( (string) $value );

        if ( '' === $description ) {
            return esc_html_x( 'No class description is currently available.', 'Student registration class description fallback text', 'teqcidb' );
        }

        return $description;
    }

    /**
     * Format a class cost value for output.
     *
     * @param string $value Raw class cost.
     *
     * @return string
     */
    private function format_cost_for_display( $value ) {
        $cost = sanitize_text_field( (string) $value );

        if ( '' === $cost ) {
            return esc_html_x( 'Not available', 'Student registration fallback value', 'teqcidb' );
        }

        if ( is_numeric( $cost ) ) {
            return '$' . number_format( (float) $cost, 2 );
        }

        return $cost;
    }

    /**
     * Format generic labels for display.
     *
     * @param string $value Raw value.
     *
     * @return string
     */
    private function format_label_for_display( $value ) {
        $label = sanitize_text_field( (string) $value );

        if ( '' === $label ) {
            return esc_html_x( 'Not available', 'Student registration fallback value', 'teqcidb' );
        }

        return ucwords( str_replace( array( '_', '-' ), ' ', $label ) );
    }

    /**
     * Format class start date from storage value to mm-dd-yyyy.
     *
     * @param string $raw_date Raw date value.
     *
     * @return string
     */
    private function format_class_start_date_for_display( $raw_date ) {
        $value = sanitize_text_field( (string) $raw_date );

        if ( '' === $value ) {
            return esc_html_x( 'Date unavailable', 'Student registration class date fallback text', 'teqcidb' );
        }

        $timestamp = strtotime( $value );

        if ( false === $timestamp ) {
            return $value;
        }

        return wp_date( 'm-d-Y', $timestamp );
    }

    /**
     * Format class time for display.
     *
     * @param string $value Raw time value.
     *
     * @return string
     */
    private function format_time_for_display( $value ) {
        $raw = sanitize_text_field( (string) $value );

        if ( '' === $raw ) {
            return esc_html_x( 'Not available', 'Student registration fallback value', 'teqcidb' );
        }

        $timestamp = strtotime( $raw );

        if ( false === $timestamp ) {
            return $raw;
        }

        return wp_date( 'g:i A', $timestamp );
    }

    /**
     * Combine street address fields.
     *
     * @param array{street_1:string,street_2:string,city:string,state:string,postal_code:string} $address Decoded address.
     *
     * @return string
     */
    private function compose_street_address_for_display( array $address ) {
        $parts = array();

        if ( ! empty( $address['street_1'] ) ) {
            $parts[] = sanitize_text_field( (string) $address['street_1'] );
        }

        if ( ! empty( $address['street_2'] ) ) {
            $parts[] = sanitize_text_field( (string) $address['street_2'] );
        }

        if ( empty( $parts ) ) {
            return esc_html_x( 'Not available', 'Student registration fallback value', 'teqcidb' );
        }

        return implode( ', ', $parts );
    }

    /**
     * Return fallback value when empty.
     *
     * @param string $value Raw value.
     *
     * @return string
     */
    private function fallback_display_value( $value ) {
        $formatted = sanitize_text_field( (string) $value );

        if ( '' === $formatted ) {
            return esc_html_x( 'Not available', 'Student registration fallback value', 'teqcidb' );
        }

        return $formatted;
    }

    /**
     * Enqueue assets for shortcode pages.
     */
    public function enqueue_assets() {
        if ( ! is_singular() ) {
            return;
        }

        global $post;

        if ( ! $post instanceof WP_Post ) {
            return;
        }

        if ( has_shortcode( $post->post_content, self::SHORTCODE_TAG ) ) {
            wp_enqueue_style( 'dashicons' );

            wp_enqueue_style(
                'teqcidb-shortcode-student-dashboard',
                TEQCIDB_PLUGIN_URL . 'assets/css/shortcodes/student-dashboard.css',
                array(),
                TEQCIDB_VERSION
            );

            wp_enqueue_script(
                'teqcidb-jspdf',
                TEQCIDB_PLUGIN_URL . 'assets/js/vendor/jspdf.umd.min.js',
                array(),
                TEQCIDB_VERSION,
                true
            );

            wp_enqueue_script(
                'teqcidb-shortcode-student-dashboard',
                TEQCIDB_PLUGIN_URL . 'assets/js/shortcodes/student-dashboard.js',
                array( 'password-strength-meter', 'teqcidb-jspdf' ),
                TEQCIDB_VERSION,
                true
            );

            wp_localize_script(
                'teqcidb-shortcode-student-dashboard',
                'teqcidbStudentDashboard',
                array(
                    'toggleShowLabel' => esc_html_x( 'Show', 'Password field toggle button text', 'teqcidb' ),
                    'toggleHideLabel' => esc_html_x( 'Hide', 'Password field toggle button text', 'teqcidb' ),
                    'toggleShowAria'  => esc_attr_x( 'Show password', 'Password field toggle button label', 'teqcidb' ),
                    'toggleHideAria'  => esc_attr_x( 'Hide password', 'Password field toggle button label', 'teqcidb' ),
                    'ajaxUrl'         => esc_url( admin_url( 'admin-ajax.php' ) ),
                    'ajaxNonce'       => wp_create_nonce( 'teqcidb_ajax_nonce' ),
                    'ajaxAction'      => 'teqcidb_save_student',
                    'ajaxLoginAction' => 'teqcidb_login_user',
                    'ajaxTokenAction' => 'teqcidb_get_accept_hosted_token',
                    'messageRequired' => esc_html_x( 'Please complete all required fields.', 'Create account form validation message', 'teqcidb' ),
                    'messageEmail'    => esc_html_x( 'The email addresses do not match.', 'Create account form validation message', 'teqcidb' ),
                    'messagePassword' => esc_html_x( 'The passwords do not match.', 'Create account form validation message', 'teqcidb' ),
                    'messageStrength' => esc_html_x( 'Your password must be at least 12 characters long and include uppercase and lowercase letters, a number, and a symbol.', 'Create account form validation message', 'teqcidb' ),
                    'messageUnknown'  => esc_html_x( 'Something went wrong while creating the account. Please try again.', 'Create account form validation message', 'teqcidb' ),
                    'messageLoginRequired' => esc_html_x( 'Please enter your username/email and password.', 'Login form validation message', 'teqcidb' ),
                    'messageLoginFailed' => esc_html_x( 'We could not log you in with those credentials. Please try again.', 'Login form validation message', 'teqcidb' ),
                    'messagePaymentUnavailable' => esc_html_x( 'Online checkout is unavailable right now. Please contact Thompson Engineering for payment assistance.', 'Registration checkout unavailable message', 'teqcidb' ),
                    'messagePaymentLoading' => esc_html_x( 'Loading secure payment form...', 'Registration checkout loading message', 'teqcidb' ),
                    'messagePaymentError' => esc_html_x( 'Unable to load the payment form right now. Please try again.', 'Registration checkout error message', 'teqcidb' ),
                    'messagePaymentSuccess' => esc_html_x( 'Payment completed successfully.', 'Registration checkout success message', 'teqcidb' ),
                    'messagePaymentFailed' => esc_html_x( 'Payment could not be completed. Please verify your payment details and try again.', 'Registration checkout failure message', 'teqcidb' ),
                    'messagePaymentCancelled' => esc_html_x( 'Payment was canceled before completion.', 'Registration checkout canceled message', 'teqcidb' ),
                    'registrationReceipt' => array(
                        'logoUrl' => esc_url( home_url( '/wp-content/uploads/2021/11/TE-Stormwater-Training-logo.png' ) ),
                        'downloadFileName' => esc_html_x( 'qci-registration-receipt.pdf', 'Registration payment receipt download file name', 'teqcidb' ),
                        'missingPdfMessage' => esc_html_x( 'Unable to generate the transaction receipt right now. Please try again.', 'Registration payment receipt generation error message', 'teqcidb' ),
                    ),
                )
            );
        }
    }
}
