<?php
/**
 * Shortcode for displaying students.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Shortcode_Student {

    public function register() {
        add_shortcode( 'teqcidb-student', array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function render( $atts = array(), $content = '' ) {
        return '<div class="teqcidb-student">' . esc_html__( 'Student Output', 'teqcidb' ) . '</div>';
    }

    public function enqueue_assets() {
        if ( is_singular() ) {
            global $post;
            if ( has_shortcode( $post->post_content, 'teqcidb-student' ) ) {
                wp_enqueue_style( 'teqcidb-shortcode-student', TEQCIDB_PLUGIN_URL . 'assets/css/shortcodes/student.css', array(), TEQCIDB_VERSION );
                wp_enqueue_script( 'teqcidb-shortcode-student', TEQCIDB_PLUGIN_URL . 'assets/js/shortcodes/student.js', array(), TEQCIDB_VERSION, true );
            }
        }
    }
}
