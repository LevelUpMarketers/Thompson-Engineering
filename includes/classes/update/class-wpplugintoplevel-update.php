<?php
/**
 * WPPlugin WPPlugin_Toplevel_Update Class
 *
 * @author   Jake Evans
 * @category admin
 * @package  classes/update
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPPlugin_Toplevel_Update', false ) ) :
	/**
	 * WPPlugin_Toplevel_Update Class.
	 */
	class WPPlugin_Toplevel_Update {

		/**
		 * Class Constructor
		 */
		public function __construct() {

			$this->wpplugintoplevel_update_kickoff();

		}


		/**
		 * Outputs the actual HTML for the tab.
		 */
		public function wpplugintoplevel_update_kickoff() {

			if ( ! class_exists( 'WPPlugin_Toplevel_Update_Actual' ) ) {

				// Load our custom updater if it doesn't already exist.
				require_once( WPPLUGINTOPLEVEL_UPDATE_DIR . 'class-wpplugintoplevel-update-actual.php' );
			}

			global $wpdb;

			// Checking if table exists.
			$test_name = $wpdb->prefix . 'wpplugintoplevel_settings';
			if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {

				// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
				$extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpplugintoplevel_settings' );
				$extension_settings = explode( '---', $extension_settings->repw);

				// Retrieve our license key from the DB.
				$license_key = $extension_settings[0];

				// Setup the updater.
				$edd_updater = new WPPlugin_Toplevel_Update_Actual( EDD_SL_STORE_URL_WPPLUGINTOPLEVEL, WPPLUGINTOPLEVEL_ROOT_DIR . 'wpplugintoplevel.php', array(
					'version' => WPPLUGINTOPLEVEL_VERSION_NUM,
					'license' => $license_key,
					'item_id' => EDD_SL_ITEM_ID_WPPLUGINTOPLEVEL,
					'author'  => 'Jake Evans',
					'url'     => home_url(),
					'beta'    => false,
				) );

			}
		}
	}

endif;
