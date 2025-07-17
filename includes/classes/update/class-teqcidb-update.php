<?php
/**
 * TEQcidbPlugin TEQcidbPlugin_Toplevel_Update Class
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

if ( ! class_exists( 'TEQcidbPlugin_Toplevel_Update', false ) ) :
	/**
	 * TEQcidbPlugin_Toplevel_Update Class.
	 */
	class TEQcidbPlugin_Toplevel_Update {

		/**
		 * Class Constructor
		 */
		public function __construct() {

			$this->teqcidb_update_kickoff();

		}


		/**
		 * Outputs the actual HTML for the tab.
		 */
		public function teqcidb_update_kickoff() {

			if ( ! class_exists( 'TEQcidbPlugin_Toplevel_Update_Actual' ) ) {

				// Load our custom updater if it doesn't already exist.
				require_once( TEQCIDB_UPDATE_DIR . 'class-teqcidb-update-actual.php' );
			}

			global $wpdb;

			// Checking if table exists.
			$test_name = $wpdb->prefix . 'teqcidb_settings';
			if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {

				// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
				$extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'teqcidb_settings' );
				$extension_settings = explode( '---', $extension_settings->repw);

				// Retrieve our license key from the DB.
				$license_key = $extension_settings[0];

				// Setup the updater.
				$edd_updater = new TEQcidbPlugin_Toplevel_Update_Actual( EDD_SL_STORE_URL_TEQCIDB, TEQCIDB_ROOT_DIR . 'teqcidb.php', array(
					'version' => TEQCIDB_VERSION_NUM,
					'license' => $license_key,
					'item_id' => EDD_SL_ITEM_ID_TEQCIDB,
					'author'  => 'Jake Evans',
					'url'     => home_url(),
					'beta'    => false,
				) );

			}
		}
	}

endif;
