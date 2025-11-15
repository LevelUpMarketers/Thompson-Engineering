<?php
/**
 * The core plugin class.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Plugin {

    private $i18n;
    private $admin;
    private $ajax;
    private $shortcode;
    private $block;

    public function __construct() {
        $this->i18n     = new TEQCIDB_I18n();
        $this->admin    = new TEQCIDB_Admin();
        $this->ajax     = new TEQCIDB_Ajax();
        $this->shortcode = new TEQCIDB_Shortcode_Student();
        $this->block     = new TEQCIDB_Block_Student();
    }

    public function run() {
        $this->i18n->load_textdomain();
        $this->admin->register();
        $this->ajax->register();
        $this->shortcode->register();
        $this->block->register();
    }
}
