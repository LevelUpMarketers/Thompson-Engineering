<?php
/**
 * Gutenberg block mirroring the student shortcode.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Block_Student {

    public function register() {
        add_action( 'init', array( $this, 'register_block' ) );
    }

    public function register_block() {
        wp_register_script(
            'teqcidb-block-student',
            TEQCIDB_PLUGIN_URL . 'assets/js/blocks/student.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor' ),
            TEQCIDB_VERSION,
            true
        );

        wp_register_style(
            'teqcidb-block-student',
            TEQCIDB_PLUGIN_URL . 'assets/css/blocks/student.css',
            array(),
            TEQCIDB_VERSION
        );

        register_block_type( 'teqcidb/student', array(
            'editor_script'   => 'teqcidb-block-student',
            'editor_style'    => 'teqcidb-block-student',
            'style'           => 'teqcidb-block-student',
            'render_callback' => array( $this, 'render' ),
        ) );
    }

    public function render( $attributes, $content ) {
        $shortcode = new TEQCIDB_Shortcode_Student();
        return $shortcode->render();
    }
}
