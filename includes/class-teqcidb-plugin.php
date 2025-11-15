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
    private $content_logger;
    private $cron_manager;
    private $error_logger;

    public function __construct() {
        $this->i18n     = new TEQCIDB_I18n();
        $this->admin    = new TEQCIDB_Admin();
        $this->ajax     = new TEQCIDB_Ajax();
        $this->shortcode = new TEQCIDB_Shortcode_Student();
        $this->block     = new TEQCIDB_Block_Student();
        $this->content_logger = new TEQCIDB_Content_Logger();
        $this->cron_manager   = new TEQCIDB_Cron_Manager();
        $this->error_logger   = new TEQCIDB_Error_Logger();
    }

    public function run() {
        $this->i18n->load_textdomain();
        $this->error_logger->register();
        $this->admin->register();
        $this->ajax->register();
        $this->shortcode->register();
        $this->block->register();
        $this->content_logger->register();
        $this->cron_manager->register();
    }
}
