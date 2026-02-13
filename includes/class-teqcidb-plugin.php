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
    private $dashboard_shortcode;
    private $registration_shortcode;
    private $block;
    private $content_logger;
    private $cron_manager;
    private $error_logger;
    private $authorizenet_communicator;

    public function __construct() {
        $this->i18n     = new TEQCIDB_I18n();
        $this->admin    = new TEQCIDB_Admin();
        $this->ajax     = new TEQCIDB_Ajax();
        $this->authorizenet_communicator = new TEQCIDB_AuthorizeNet_Communicator();
        $this->shortcode = new TEQCIDB_Shortcode_Student();
        $this->dashboard_shortcode = new TEQCIDB_Shortcode_Student_Dashboard();
        $this->registration_shortcode = new TEQCIDB_Shortcode_Student_Registration( $this->dashboard_shortcode );
        $this->block     = new TEQCIDB_Block_Student();
        $this->content_logger = new TEQCIDB_Content_Logger();
        $this->cron_manager   = new TEQCIDB_Cron_Manager();

        if ( $this->should_register_error_logger() ) {
            $this->error_logger = new TEQCIDB_Error_Logger();
        }
    }

    public function run() {
        $this->i18n->load_textdomain();
        if ( $this->error_logger instanceof TEQCIDB_Error_Logger ) {
            $this->error_logger->register();
        }
        $this->admin->register();
        $this->ajax->register();
        $this->authorizenet_communicator->register();
        $this->shortcode->register();
        $this->dashboard_shortcode->register();
        $this->registration_shortcode->register();
        $this->block->register();
        $this->content_logger->register();
        $this->cron_manager->register();
    }

    /**
     * Determine whether the error logger should initialize for this request.
     *
     * @return bool
     */
    private function should_register_error_logger() {
        $plugin_logging_enabled = TEQCIDB_Settings_Helper::is_logging_enabled( TEQCIDB_Settings_Helper::FIELD_LOG_PLUGIN_ERRORS );

        $should_register = $plugin_logging_enabled;

        /**
         * Filter whether the error logger should be registered.
         *
         * @since 0.1.0
         *
         * @param bool $should_register Whether the logger should run.
         */
        return apply_filters( 'teqcidb_should_register_error_logger', $should_register );
    }
}
