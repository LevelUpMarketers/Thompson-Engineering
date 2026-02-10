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
            wp_enqueue_style(
                'teqcidb-shortcode-student-dashboard',
                TEQCIDB_PLUGIN_URL . 'assets/css/shortcodes/student-dashboard.css',
                array(),
                TEQCIDB_VERSION
            );

            wp_enqueue_script(
                'teqcidb-shortcode-student-dashboard',
                TEQCIDB_PLUGIN_URL . 'assets/js/shortcodes/student-dashboard.js',
                array( 'jquery' ),
                TEQCIDB_VERSION,
                true
            );

            wp_localize_script(
                'teqcidb-shortcode-student-dashboard',
                'teqcidbStudentDashboard',
                array(
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'teqcidb_student_dashboard' ),
                    'labels' => array(
                        'loggingIn' => esc_html_x( 'Logging in...', 'Login form loading message', 'teqcidb' ),
                        'creatingAccount' => esc_html_x( 'Creating your account...', 'Create account form loading message', 'teqcidb' ),
                        'savingProfile' => esc_html_x( 'Saving your profile...', 'Profile save loading message', 'teqcidb' ),
                    ),
                    'passwordToggle' => array(
                        'showLabel' => esc_attr_x( 'Show password', 'Password visibility toggle aria label', 'teqcidb' ),
                        'hideLabel' => esc_attr_x( 'Hide password', 'Password visibility toggle aria label', 'teqcidb' ),
                        'showText' => esc_html_x( 'Show', 'Password visibility toggle text', 'teqcidb' ),
                        'hideText' => esc_html_x( 'Hide', 'Password visibility toggle text', 'teqcidb' ),
                    ),
                )
            );
        }
    }
}
