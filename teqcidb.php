<?php
/**
 * Plugin Name:       Thompson Engineering QCI Database
 * Plugin URI:        https://training.thompsonengineering.com/
 * Description:       Manage Thompson Engineering QCI students, training history, certifications, and communications from a dedicated WordPress dashboard.
 * Version:           0.1.0
 * Author:            Level Up Digital Marketing
 * Author URI:        https://levelupmarketers.com
 * Text Domain:       teqcidb
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Plugin constants.
define( 'TEQCIDB_VERSION', '0.1.0' );
define( 'TEQCIDB_MIN_EXECUTION_TIME', 4 );
define( 'TEQCIDB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TEQCIDB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

$teqcidb_autoload = TEQCIDB_PLUGIN_DIR . 'vendor/autoload.php';

if ( file_exists( $teqcidb_autoload ) ) {
    require_once $teqcidb_autoload;
}

require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-activator.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-deactivator.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-i18n.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-student-helper.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-settings-helper.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-email-template-helper.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-email-log-helper.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-error-log-helper.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-error-logger.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-content-logger.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-cron.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-ajax.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-authorizenet-communicator.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-authorizenet-service.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/admin/class-teqcidb-admin.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/shortcodes/class-teqcidb-shortcode-student.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/shortcodes/class-teqcidb-shortcode-student-dashboard.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/shortcodes/class-teqcidb-shortcode-student-registration.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/blocks/class-teqcidb-block-student.php';
require_once TEQCIDB_PLUGIN_DIR . 'includes/class-teqcidb-plugin.php';

register_activation_hook( __FILE__, array( 'TEQCIDB_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'TEQCIDB_Deactivator', 'deactivate' ) );

/**
 * Bootstrap the Thompson Engineering QCI Database plugin after WordPress loads.
 */
function teqcidb_bootstrap() {
    static $plugin = null;

    if ( null !== $plugin ) {
        return;
    }

    $plugin = new TEQCIDB_Plugin();
    $plugin->run();
}
add_action( 'plugins_loaded', 'teqcidb_bootstrap', 0 );
