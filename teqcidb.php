<?php
/**
 * WordPress Book List TEQciDb Extension
 *
 * @package     WordPress Book List TEQciDb Extension
 * @author      Jake Evans
 * @copyright   2018 Jake Evans
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Thompson Engineering QCI Database
 * Plugin URI: https://www.jakerevans.com
 * Description: A WordPress Plugin that facilitates the Thompson Engineering QCI/MDOT Database functions.
 * Version: 1.0.0
 * Author: Jake Evans
 * Text Domain: teqcidb
 * Author URI: https://www.jakerevans.com
 */

/*
 * SETUP NOTES:
 *
 * Rename root plugin folder to an all-lowercase version of teqcidb
 *
 * Change all filename instances from teqcidb to desired plugin name
 *
 * Modify Plugin Name
 *
 * Modify Description
 *
 * Modify Version Number in Block comment and in Constant
 *
 * Find & Replace these strings:
 * teqcidb
 * teqciDb
 * TEQcidb
 * TEQciDb
 * TEQCIDB
 * TEQcidbPlugin
 * teqcidbplugin
 * $toplevel
 * TOPLEVEL
 * teqcidb-extension
 * SITETHING - rename to whatever we're 'saving' or recording to the database. Is this for cars, vendors, contacts, etc. really to be used for the editing of whatever this database is concenred with in the 'class-settings-two-form.php' file. Replace it with something lowercase.
 * repw with something also random - db column that holds license.
 *
 * Rename and/or delete the Node_Modules folder to prevent that Sass error message when running Gulp
 *
 * Change the EDD_SL_ITEM_ID_TEQCIDB contant below.
 *
 * Install Gulp & all Plugins listed in gulpfile.js
 *
 * Mods to make the MDOT version work when copying completely from the QCI version...
 * 1. Delete everything in the 'temdotdb' folder except .git and .gitattributes
 * 2. Copy over everything from 'teqcidb' except .git and .gitattributes
 * 3. Delete files in assets/css and assets/js
 * 4. Rename all the filenames to 'temdotdb'
 * 5. Put the folder in sumblimetext, and open the 'temdotdb.php' file, and start Find-replacing the prefixes
 * 6. Remove the "require_once 'vendor/autoload.php';" line from the 'temdotdb.php' file
 * 7. Find and replace ' QCI ', ' qci ', 'QCI ', ' QCI', and ' qci'?
 * 8. Find and replace "/register-for-a-class-qci/" with "/register-for-a-class-mdot/"
 *
 *
 */




// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

/* REQUIRE STATEMENTS */
	require_once 'vendor/autoload.php';
	require_once 'includes/class-teqcidb-general-functions.php';
	require_once 'includes/class-teqcidb-ajax-functions.php';
	require_once 'includes/classes/update/class-teqcidb-update.php';
	require_once 'includes/classes/class-authorize-payments.php';
	require_once 'includes/classes/class-php-spreadsheet.php';
	require_once 'includes/classes/sendgrid-php/sendgrid-php.php';


/* END REQUIRE STATEMENTS */

/* CONSTANT DEFINITIONS */
  $tq_version_set = '7.1.123.2.13';

	if ( ! defined('TEQCIDB_VERSION_NUM' ) ) {
		define( 'TEQCIDB_VERSION_NUM', $tq_version_set );
	}

	// For Authorize.net
	define("AUTHORIZENET_LOG_FILE","phplog");

	// This is the URL our updater / license checker pings. This should be the URL of the site with EDD installed.
	define( 'EDD_SL_STORE_URL_TEQCIDB', 'https://teqcidbplugin.com' );

	// The id of your product in EDD.
	define( 'EDD_SL_ITEM_ID_TEQCIDB', 46 );

	// Root plugin folder directory.
	define( 'TEQCIDB_ROOT_DIR', plugin_dir_path( __FILE__ ) );

	// Root WordPress Plugin Directory. The If is for taking into account the update process - a temp folder gets created when updating, which temporarily replaces the 'teqcidbplugin-bulkbookupload' folder.
	if ( false !== stripos( plugin_dir_path( __FILE__ ) , '/teqcidb' ) ) { 
		define( 'TEQCIDB_ROOT_WP_PLUGINS_DIR', str_replace( '/teqcidb', '', plugin_dir_path( __FILE__ ) ) );
	} else {
		$temp = explode( 'plugins/', plugin_dir_path( __FILE__ ) );
		define( 'TEQCIDB_ROOT_WP_PLUGINS_DIR', $temp[0] . 'plugins/' );
	}



	// Website URL .
	define( 'TEQCIDB_SITE_URL', get_site_url() );

	// Root plugin folder URL .
	define( 'TEQCIDB_ROOT_URL', plugins_url() . '/teqcidb/' );

	// Root Logs Directory.
	define( 'TEQCIDB_LOGS_DIR', TEQCIDB_ROOT_DIR . 'logs/' );

	// Root Classes Directory.
	define( 'TEQCIDB_CLASS_DIR', TEQCIDB_ROOT_DIR . 'includes/classes/' );

	// Root Update Directory.
	define( 'TEQCIDB_UPDATE_DIR', TEQCIDB_CLASS_DIR . 'update/' );


	// Root REST Classes Directory.
	define( 'TEQCIDB_CLASS_REST_DIR', TEQCIDB_ROOT_DIR . 'includes/classes/rest/' );

	// Root Compatability Classes Directory.
	define( 'TEQCIDB_CLASS_COMPAT_DIR', TEQCIDB_ROOT_DIR . 'includes/classes/compat/' );

	// Root Transients Directory.
	define( 'TEQCIDB_CLASS_TRANSIENTS_DIR', TEQCIDB_ROOT_DIR . 'includes/classes/transients/' );

	// Root Image URL.
	define( 'TEQCIDB_ROOT_IMG_URL', TEQCIDB_ROOT_URL . 'assets/img/' );

	// Root Forms URL.
	define( 'TEQCIDB_ROOT_FORMS_URL', TEQCIDB_ROOT_URL . 'assets/forms/' );

	// Root Spreadsheets Directory.
	define( 'TEQCIDB_SPREADSHEETS_INITIAL_DIR', TEQCIDB_ROOT_DIR . 'spreadsheets/onlineinitial/' );

	// Root Spreadsheets Directory.
	define( 'TEQCIDB_SPREADSHEETS_REFRESHER_DIR', TEQCIDB_ROOT_DIR . 'spreadsheets/onlinerefresher/' );

	// Root Spreadsheets Directory.
	define( 'TEQCIDB_SPREADSHEETS_INITIAL_URL', TEQCIDB_ROOT_URL . 'spreadsheets/onlineinitial/' );

	// Root Spreadsheets Directory.
	define( 'TEQCIDB_SPREADSHEETS_REFRESHER_URL', TEQCIDB_ROOT_URL . 'spreadsheets/onlinerefresher/' );

	// Root Image Icons URL.
	define( 'TEQCIDB_ROOT_IMG_ICONS_URL', TEQCIDB_ROOT_URL . 'assets/img/icons/' );

	// Root CSS URL.
	define( 'TEQCIDB_CSS_URL', TEQCIDB_ROOT_URL . 'assets/css/' );

	// Root JS URL.
	define( 'TEQCIDB_JS_URL', TEQCIDB_ROOT_URL . 'assets/js/' );

	// Root UI directory.
	define( 'TEQCIDB_ROOT_INCLUDES_UI', TEQCIDB_ROOT_DIR . 'includes/ui/' );

	// Root UI Admin directory.
	define( 'TEQCIDB_ROOT_INCLUDES_UI_ADMIN_DIR', TEQCIDB_ROOT_DIR . 'includes/ui/' );

	// Define the Uploads base directory.
	$uploads     = wp_upload_dir();
	$upload_path = $uploads['basedir'];
	define( 'TEQCIDB_UPLOADS_BASE_DIR', $upload_path . '/' );

	// Define the Uploads base URL.
	$upload_url = $uploads['baseurl'];
	define( 'TEQCIDB_UPLOADS_BASE_URL', $upload_url . '/' );

	// Nonces array.
	define( 'TEQCIDB_NONCES_ARRAY',
		wp_json_encode(array(
			'adminnonce1' => 'teqcidb_save_license_key_action_callback',
			'adminnonce2' => 'teqcidb_add_new_student_action_callback',
			'adminnonce3' => 'teqcidb_add_new_class_action_callback',
			'adminnonce4' => 'teqcidb_edit_existing_student_action_callback',
			'adminnonce5' => 'teqcidb_add_new_student_frontend_action_callback',
			'adminnonce6' => 'teqcidb_make_class_payment_frontend_action_callback',
			'adminnonce7' => 'teqcidb_edit_existing_class_action_callback',
			'adminnonce8' => 'teqcidb_save_emails_action_callback',
			'adminnonce9' => 'teqcidb_delete_class_action_callback',
			'adminnonce10' => 'teqcidb_send_test_emails_action_callback',
			'adminnonce11' => 'teqcidb_save_email_edits_action_callback',
			'adminnonce12' => 'teqcidb_send_bulk_email_action_callback',
			'adminnonce13' => 'teqcidb_class_form_roster_action_callback',
			'adminnonce14' => 'teqcidb_class_form_signin_action_callback',
			'adminnonce15' => 'teqcidb_class_form_namebadge_action_callback',
			'adminnonce16' => 'teqcidb_class_form_certification_initial_inperson_action_callback',
			'adminnonce17' => 'teqcidb_class_form_walletcardfront_action_callback',	
			'adminnonce18' => 'teqcidb_class_form_walletcardback_action_callback',	
			'adminnonce19' => 'teqcidb_class_form_certification_refresher_inperson_action_callback',
			'adminnonce20' => 'teqcidb_class_form_maillabel_action_callback',
			'adminnonce21' => 'teqcidb_class_form_online_initial_spreadsheet_action_callback',
			'adminnonce22' => 'teqcidb_class_form_online_refresher_spreadsheet_action_callback',	
			'adminnonce23' => 'teqcidb_class_form_certification_initial_online_action_callback',
			'adminnonce24' => 'teqcidb_class_form_certification_refresher_online_action_callback',
			'adminnonce25' => 'teqcidb_class_form_certification_all_initial_inperson_action_callback',
			'adminnonce26' => 'teqcidb_class_form_certification_all_refresher_inperson_action_callback',
			'adminnonce27' => 'teqcidb_class_form_certification_all_initial_online_action_callback',
			'adminnonce28' => 'teqcidb_class_form_certification_all_refresher_online_action_callback',
			'adminnonce29' => 'teqcidb_email_by_expir_date_action_callback',
			'adminnonce30' => 'teqcidb_email_by_class_action_callback',
			'adminnonce31' => 'teqcidb_class_form_certification_all_mailing_labels_action_callback',
			'adminnonce32' => 'teqcidb_class_form_certification_all_walletcards_labels_action_callback',
			'adminnonce33' => 'teqcidb_download_report_action_callback',
			'adminnonce34' => 'teqcidb_credits_list_action_callback',
			'adminnonce35' => 'teqcidb_make_class_payment_frontend_actual_action_callback',
			'adminnonce36' => 'teqcidb_mark_student_viewed_action_callback',
			'adminnonce37' => 'teqcidb_mark_student_viewed_payment_action_callback',
			'adminnonce38' => 'teqcidb_class_form_certification_oneperpage_walletcards_labels_action_callback'
		))
	);

/* END OF CONSTANT DEFINITIONS */

/* MISC. INCLUSIONS & DEFINITIONS */

	// Loading textdomain.
	load_plugin_textdomain( 'teqcidb', false, TEQCIDB_ROOT_DIR . 'languages' );

/* END MISC. INCLUSIONS & DEFINITIONS */

/* CLASS INSTANTIATIONS */

	// Call the class found in teqcidbplugin-functions.php.
	$toplevel_general_functions = new TEQciDb_General_Functions();

	// Call the class found in teqcidbplugin-functions.php.
	$toplevel_ajax_functions = new TEQciDb_Ajax_Functions();

	// Call the class found in class-authorize-payments.php
	$toplevel_payment_ajax_functions = new TEQcidbPlugin_Authorize_Payments();

	// Call the class found in class-php-spreadsheet.php
	$toplevel_php_spreadsheet_ajax_functions = new TEQcidbPlugin_Php_Spreadsheet();

	// Include the Update Class.
	$toplevel_update_functions = new TEQcidbPlugin_Toplevel_Update();


/* END CLASS INSTANTIATIONS */


/* FUNCTIONS FOUND IN CLASS-WPPLUGIN-GENERAL-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

	// For the admin pages.
	add_action( 'admin_menu', array( $toplevel_general_functions, 'teqcidb_jre_my_admin_menu' ) );

	// Adding Ajax library.
	add_action( 'wp_head', array( $toplevel_general_functions, 'teqcidb_jre_prem_add_ajax_library' ) );

	// Adding the function that will take our TEQCIDB_NONCES_ARRAY Constant from above and create actual nonces to be passed to Javascript functions.
	add_action( 'init', array( $toplevel_general_functions, 'teqcidb_create_nonces' ) );

	// Function to run any code that is needed to modify the plugin between different versions.
	//add_action( 'plugins_loaded', array( $toplevel_general_functions, 'teqcidb_update_upgrade_function' ) );

	// Adding the admin js file.
	add_action( 'admin_enqueue_scripts', array( $toplevel_general_functions, 'teqcidb_admin_js' ) );

	// Adding the frontend js file.
	add_action( 'wp_enqueue_scripts', array( $toplevel_general_functions, 'teqcidb_frontend_js' ) );

	// Adding the admin css file for this extension.
	add_action( 'admin_enqueue_scripts', array( $toplevel_general_functions, 'teqcidb_admin_style' ) );

	// Adding the Front-End css file for this extension.
	add_action( 'wp_enqueue_scripts', array( $toplevel_general_functions, 'teqcidb_frontend_style' ) );

	// Function to add table names to the global $wpdb.
	add_action( 'admin_footer', array( $toplevel_general_functions, 'teqcidb_register_table_name' ) );

	// Function that adds in any possible admin pointers
	add_action( 'admin_footer', array( $toplevel_general_functions, 'teqcidb_admin_pointers_javascript' ) );

	// Creates tables upon activation.
	register_activation_hook( __FILE__, array( $toplevel_general_functions, 'teqcidb_create_tables' ) );

	// Adding the front-end login / dashboard shortcode.
	add_shortcode( 'teqcidb_student_registration_shortcode', array( $toplevel_general_functions, 'teqcidb_student_registration_shortcode_function' ) );

	// Adding the front-end login / dashboard shortcode.
	add_shortcode( 'teqcidb_all_classes_shortcode', array( $toplevel_general_functions, 'teqcidb_all_classes_shortcode_function' ) );

	// Adding the front-end QCI List shortcode.
	add_shortcode( 'teqcidb_qci_list_shortcode', array( $toplevel_general_functions, 'teqcidb_qci_list_shortcode_function' ) );

	// Function that logs in a user automatically after they've first registered.
	add_action( 'after_setup_theme', array( $toplevel_general_functions, 'teqcidb_autologin_after_registering' ) );

	// Function that hides the admin bar on the front-end for all but admins.
	add_action( 'after_setup_theme', array( $toplevel_general_functions, 'teqcidb_disable_admin_bar_for_non_admins' ) );





/* END OF FUNCTIONS FOUND IN CLASS-WPPLUGIN-GENERAL-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

/* FUNCTIONS FOUND IN CLASS-WPPLUGIN-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

// Function for manually adding a new user from the dashboard. 
add_action( 'wp_ajax_teqcidb_add_new_student_action', array( $toplevel_ajax_functions, 'teqcidb_add_new_student_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_roster_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_roster_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_signin_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_signin_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_initial_inperson_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_initial_inperson_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_refresher_inperson_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_refresher_inperson_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_initial_online_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_initial_online_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_refresher_online_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_refresher_online_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_all_initial_inperson_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_all_initial_inperson_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_email_by_expir_date_action', array( $toplevel_ajax_functions, 'teqcidb_email_by_expir_date_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_email_by_class_action', array( $toplevel_ajax_functions, 'teqcidb_email_by_class_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_all_refresher_inperson_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_all_refresher_inperson_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_all_initial_online_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_all_initial_online_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_all_refresher_online_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_all_refresher_online_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_all_mailing_labels_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_all_mailing_labels_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_all_walletcards_labels_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_all_walletcards_labels_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_certification_oneperpage_walletcards_labels_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_certification_oneperpage_walletcards_labels_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_walletcardfront_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_walletcardfront_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_maillabel_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_maillabel_action_callback' ) );

// Function for generating a Class Roster on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_walletcardback_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_walletcardback_action_callback' ) );

// Function for generating Name Badges on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_namebadge_action', array( $toplevel_ajax_functions, 'teqcidb_class_form_namebadge_action_callback' ) );

// Function for generating Name Badges on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_online_initial_spreadsheet_action', array( $toplevel_php_spreadsheet_ajax_functions, 'teqcidb_class_form_online_initial_spreadsheet_action_callback' ) );

// Function for generating Name Badges on the "Various Class Forms" tab of the dashboard. 
add_action( 'wp_ajax_teqcidb_class_form_online_refresher_spreadsheet_action', array( $toplevel_php_spreadsheet_ajax_functions, 'teqcidb_class_form_online_refresher_spreadsheet_action_callback' ) );

// Function for manually adding a new user from the dashboard. 
add_action( 'wp_ajax_teqcidb_delete_class_action', array( $toplevel_ajax_functions, 'teqcidb_delete_class_action_callback' ) );

// Function for manually adding a new user from the frontend. 
add_action( 'wp_ajax_nopriv_teqcidb_add_new_student_action', array( $toplevel_ajax_functions, 'teqcidb_add_new_student_action_callback' ) );

// Function for manually editing a student from the dashboard. 
add_action( 'wp_ajax_teqcidb_edit_existing_student_action', array( $toplevel_ajax_functions, 'teqcidb_edit_existing_student_action_callback' ) );

// Function for manually editing a student from the dashboard. 
add_action( 'wp_ajax_teqcidb_mark_student_viewed_action', array( $toplevel_ajax_functions, 'teqcidb_mark_student_viewed_action_callback' ) );

// Function for manually editing a student from the dashboard. 
add_action( 'wp_ajax_teqcidb_mark_student_viewed_payment_action', array( $toplevel_ajax_functions, 'teqcidb_mark_student_viewed_payment_action_callback' ) );

// Function for manually editing a class from the dashboard. 
add_action( 'wp_ajax_teqcidb_edit_existing_class_action', array( $toplevel_ajax_functions, 'teqcidb_edit_existing_class_action_callback' ) );

// Function for manually editing a class from the dashboard. 
add_action( 'wp_ajax_teqcidb_save_emails_action', array( $toplevel_ajax_functions, 'teqcidb_save_emails_action_callback' ) );

// Function for manually editing a class from the dashboard. 
add_action( 'wp_ajax_teqcidb_save_email_edits_action', array( $toplevel_ajax_functions, 'teqcidb_save_email_edits_action_callback' ) );

// Function for sending a test email. 
add_action( 'wp_ajax_teqcidb_send_test_emails_action', array( $toplevel_ajax_functions, 'teqcidb_send_test_emails_action_callback' ) );

// Function for sending a test email. 
add_action( 'wp_ajax_teqcidb_download_report_action', array( $toplevel_php_spreadsheet_ajax_functions, 'teqcidb_download_report_action_callback' ) );

// Function for sending a test email. 
add_action( 'wp_ajax_teqcidb_credits_list_action', array( $toplevel_php_spreadsheet_ajax_functions, 'teqcidb_credits_list_action_callback' ) );

// Function for sending a test email. 
add_action( 'wp_ajax_teqcidb_send_bulk_email_action', array( $toplevel_ajax_functions, 'teqcidb_send_bulk_email_action_callback' ) );

// Function for manually adding a new class from the dashboard. 
add_action( 'wp_ajax_teqcidb_add_new_class_action', array( $toplevel_ajax_functions, 'teqcidb_add_new_class_action_callback' ) );

// Function for manually adding a new user from the dashboard. 
add_action( 'wp_ajax_teqcidb_add_new_student_frontend_action', array( $toplevel_ajax_functions, 'teqcidb_add_new_student_frontend_action_callback' ) );

// Function for manually adding a new user from the frontend. 
add_action( 'wp_ajax_nopriv_teqcidb_add_new_student_frontend_action', array( $toplevel_ajax_functions, 'teqcidb_add_new_student_frontend_action_callback' ) );

// Function for making a class payment on the frontend.
add_action( 'wp_ajax_teqcidb_make_class_payment_frontend_action', array( $toplevel_payment_ajax_functions, 'teqcidb_make_class_payment_frontend_action_callback' ) );

// Function for making a class payment on the frontend.
add_action( 'wp_ajax_nopriv_teqcidb_make_class_payment_frontend_action', array( $toplevel_payment_ajax_functions, 'teqcidb_make_class_payment_frontend_action_callback' ) );

// Function for making a class payment on the frontend.
add_action( 'wp_ajax_teqcidb_make_class_payment_frontend_actual_action', array( $toplevel_payment_ajax_functions, 'teqcidb_make_class_payment_frontend_actual_action_callback' ) );


/* END OF FUNCTIONS FOUND IN CLASS-WPPLUGIN-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */