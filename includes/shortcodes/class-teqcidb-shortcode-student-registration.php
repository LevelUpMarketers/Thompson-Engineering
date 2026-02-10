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

        return sprintf(
            '<section class="teqcidb-registration-section"><p>%s</p></section>',
            esc_html_x(
                'Class registration options will appear here soon.',
                'Student registration shortcode placeholder text for logged-in users',
                'teqcidb'
            )
        );
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
