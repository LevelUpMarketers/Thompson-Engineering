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
     * Constructor.
     *
     * @param TEQCIDB_Shortcode_Student_Dashboard|null $dashboard_shortcode Optional existing dashboard shortcode instance.
     */
    public function __construct( TEQCIDB_Shortcode_Student_Dashboard $dashboard_shortcode = null ) {
        $this->dashboard_shortcode = $dashboard_shortcode instanceof TEQCIDB_Shortcode_Student_Dashboard
            ? $dashboard_shortcode
            : new TEQCIDB_Shortcode_Student_Dashboard();
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

        ob_start();
        ?>
        <section class="teqcidb-registration-section teqcidb-registration-classes" data-teqcidb-registration="true">
            <?php if ( ! empty( $classes ) ) : ?>
                <div class="teqcidb-registration-class-list" role="list">
                    <?php foreach ( $classes as $index => $class ) : ?>
                        <?php
                        $class_label = sprintf(
                            /* translators: 1: Class name. 2: Formatted class start date. */
                            esc_html_x( '%1$s - %2$s', 'Student registration class list button label', 'teqcidb' ),
                            $class['classname'],
                            $class['classstartdate']
                        );
                        ?>
                        <div class="teqcidb-registration-class-item" role="listitem">
                            <button
                                class="teqcidb-dashboard-tab teqcidb-registration-class-toggle<?php echo 0 === $index ? ' is-active' : ''; ?>"
                                type="button"
                                aria-expanded="false"
                            >
                                <?php echo esc_html( $class_label ); ?>
                            </button>
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
     * @return array<int, array{classname:string,classstartdate:string}>
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
                "SELECT classname, classstartdate
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
            $class_date = $this->format_class_start_date_for_display( isset( $row['classstartdate'] ) ? $row['classstartdate'] : '' );

            if ( '' === $class_name ) {
                continue;
            }

            $classes[] = array(
                'classname'      => $class_name,
                'classstartdate' => $class_date,
            );
        }

        return $classes;
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
                    'messageRequired' => esc_html_x( 'Please complete all required fields.', 'Create account form validation message', 'teqcidb' ),
                    'messageEmail'    => esc_html_x( 'The email addresses do not match.', 'Create account form validation message', 'teqcidb' ),
                    'messagePassword' => esc_html_x( 'The passwords do not match.', 'Create account form validation message', 'teqcidb' ),
                    'messageStrength' => esc_html_x( 'Your password must be at least 12 characters long and include uppercase and lowercase letters, a number, and a symbol.', 'Create account form validation message', 'teqcidb' ),
                    'messageUnknown'  => esc_html_x( 'Something went wrong while creating the account. Please try again.', 'Create account form validation message', 'teqcidb' ),
                    'messageLoginRequired' => esc_html_x( 'Please enter your username/email and password.', 'Login form validation message', 'teqcidb' ),
                    'messageLoginFailed' => esc_html_x( 'We could not log you in with those credentials. Please try again.', 'Login form validation message', 'teqcidb' ),
                )
            );
        }
    }
}
