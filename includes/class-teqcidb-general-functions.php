<?php
/**
 * Class TEQciDb_General_Functions - class-toplevel-general-functions.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes
 * @version  6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TEQciDb_General_Functions', false ) ) :
	/**
	 * TEQciDb_General_Functions class. Here we'll do things like enqueue scripts/css, set up menus, etc.
	 */
	class TEQciDb_General_Functions {

		/**
		 *  Functions that loads up all menu pages/contents, etc.
		 */
		public function teqcidb_jre_admin_page_function() {
			global $wpdb;
			require_once TEQCIDB_ROOT_INCLUDES_UI_ADMIN_DIR . 'class-admin-master-ui.php';
		}

		/** Functions that loads up the menu page entry for this Extension.
		 *
		 *  @param array $submenu_array - The array that contains submenu entries to add to.
		 */
		public function teqcidb_jre_my_admin_menu() {
			add_menu_page( 'Bell  TEQciDb', 'QCI Database', 'manage_options', 'TEQciDb-Options', array( $this, 'teqcidb_jre_admin_page_function' ), TEQCIDB_ROOT_IMG_URL . 'alabama_dashboard_icon.png', 6 );

			$submenu_array = array(
				'Students',
				'Classes',
				'Emails',
				'Reports',
			);

			// Filter to allow the addition of a new subpage.
			if ( has_filter( 'toplevel_add_sub_menu' ) ) {
				$submenu_array = apply_filters( 'toplevel_add_sub_menu', $submenu_array );
			}

			foreach ( $submenu_array as $key => $submenu ) {
				$menu_slug = strtolower( str_replace( ' ', '-', $submenu ) );
				add_submenu_page( 'TEQciDb-Options', 'TEQciDb', $submenu, 'manage_options', 'TEQciDb-Options-' . $menu_slug, array( $this, 'teqcidb_jre_admin_page_function' ) );
			}

			remove_submenu_page( 'TEQciDb-Options', 'TEQciDb-Options' );
		}

		/**
		 *  Code for adding ajax
		 */
		public function teqcidb_jre_prem_add_ajax_library() {

			$html = '<script type="text/javascript">';

			// Checking $protocol in HTTP or HTTPS.
			if ( isset( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) {
				// This is HTTPS.
				$protocol = 'https';
			} else {
				// This is HTTP.
				$protocol = 'http';
			}
			$temp_ajax_path = admin_url( 'admin-ajax.php' );
			$good_ajax_url  = $protocol . strchr( $temp_ajax_path, ':' );

			$html .= 'var ajaxurl = "' . $good_ajax_url . '"';
			$html .= '</script>';
			echo $html;
		}

		/**
		 *  Here we take the Constant defined in teqcidbplugin.php that holds the values that all our nonces will be created from, we create the actual nonces using wp_create_nonce, and the we define our new, final nonces Constant, called WPPLUGIN_FINAL_NONCES_ARRAY.
		 */
		public function teqcidb_create_nonces() {

			$temp_array = array();
			foreach ( json_decode( TEQCIDB_NONCES_ARRAY ) as $key => $noncetext ) {
				$nonce              = wp_create_nonce( $noncetext );
				$temp_array[ $key ] = $nonce;
			}

			// Defining our final nonce array.
			define( 'TOPLEVEL_FINAL_NONCES_ARRAY', wp_json_encode( $temp_array ) );

		}

		/**
		 *  Function to run the compatability code in the Compat class for upgrades/updates, if stored version number doesn't match the defined global in teqcidb.php
		 */
		public function teqcidb_update_upgrade_function() {

			// Get current version #.
			global $wpdb;
			$existing_string = $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'teqcidb_jre_user_options' );

			// Check to see if Extension is already registered and matches this version.
			if ( false !== strpos( $existing_string->extensionversions, 'teqcidb' ) ) {
				$split_string = explode( 'teqcidb', $existing_string->extensionversions );
				$version      = substr( $split_string[1], 0, 5 );

				// If version number does not match the current version number found in teqcidbplugin.php, call the Compat class and run upgrade functions.
				if ( TEQCIDB_VERSION_NUM !== $version ) {
					require_once TOPLEVEL_CLASS_COMPAT_DIR . 'class-toplevel-compat-functions.php';
					$compat_class = new TEQciDb_Compat_Functions();
				}
			}
		}

		/**
		 * Adding the admin js file
		 */
		public function teqcidb_admin_js() {

			wp_register_script( 'teqcidb_adminjs', TEQCIDB_JS_URL . 'teqcidb_admin.min.js', array( 'jquery' ), TEQCIDB_VERSION_NUM, true );

			global $wpdb;

			$final_array_of_php_values = array();

			// Now grab all of our Nonces to pass to the JavaScript for the Ajax functions and merge with the Translations array.
			$final_array_of_php_values = array_merge( $final_array_of_php_values, json_decode( TOPLEVEL_FINAL_NONCES_ARRAY, true ) );

			// Adding some other individual values we may need.
			$final_array_of_php_values['TEQCIDB_ROOT_IMG_ICONS_URL']   = TEQCIDB_ROOT_IMG_ICONS_URL;
			$final_array_of_php_values['TEQCIDB_ROOT_IMG_URL']   = TEQCIDB_ROOT_IMG_URL;
			$final_array_of_php_values['FOR_TAB_HIGHLIGHT']    = admin_url() . 'admin.php';
			$final_array_of_php_values['SAVED_ATTACHEMENT_ID'] = get_option( 'media_selector_attachment_id', 0 );
			$final_array_of_php_values['SETTINGS_PAGE_URL'] = menu_page_url( 'WPBookList-Options-settings', false );
			$final_array_of_php_values['DB_PREFIX'] = $wpdb->prefix;


			// Now registering/localizing our JavaScript file, passing all the PHP variables we'll need in our $final_array_of_php_values array, to be accessed from 'wpbooklist_php_variables' object (like wpbooklist_php_variables.nameofkey, like any other JavaScript object).
			wp_localize_script( 'teqcidb_adminjs', 'teqciDbPhpVariables', $final_array_of_php_values );

			wp_enqueue_script( 'teqcidb_adminjs' );

		}

		/**
		 * Adding the frontend js file
		 */
		public function teqcidb_frontend_js() {

			wp_register_script( 'teqcidb_frontendjs', TEQCIDB_JS_URL . 'teqcidb_frontend.min.js', array( 'jquery' ), TEQCIDB_VERSION_NUM, true );

				global $wpdb;

			$final_array_of_php_values = array();

			// Now grab all of our Nonces to pass to the JavaScript for the Ajax functions and merge with the Translations array.
			$final_array_of_php_values = array_merge( $final_array_of_php_values, json_decode( TOPLEVEL_FINAL_NONCES_ARRAY, true ) );

			// Adding some other individual values we may need.
			$final_array_of_php_values['TEQCIDB_ROOT_IMG_ICONS_URL']   = TEQCIDB_ROOT_IMG_ICONS_URL;
			$final_array_of_php_values['TEQCIDB_ROOT_IMG_URL']   = TEQCIDB_ROOT_IMG_URL;
			$final_array_of_php_values['FOR_TAB_HIGHLIGHT']    = admin_url() . 'admin.php';
			$final_array_of_php_values['SAVED_ATTACHEMENT_ID'] = get_option( 'media_selector_attachment_id', 0 );
			$final_array_of_php_values['DB_PREFIX'] = $wpdb->prefix;


			// Now registering/localizing our JavaScript file, passing all the PHP variables we'll need in our $final_array_of_php_values array, to be accessed from 'wpbooklist_php_variables' object (like wpbooklist_php_variables.nameofkey, like any other JavaScript object).
			wp_localize_script( 'teqcidb_frontendjs', 'teqciDbPhpVariables', $final_array_of_php_values );

			wp_enqueue_script( 'teqcidb_frontendjs' );

			// Now include the PayTrace protect.js file.
			//if ( false !== stripos( $_SERVER['REQUEST_URI'], 'register-for-a-' ) ) {
				//wp_register_script( 'teqcidb_paytrace_js', 'https://protect.paytrace.com/js/protect.min.js', array( 'jquery' ), TEQCIDB_VERSION_NUM, true );
				//wp_enqueue_script( 'teqcidb_paytrace_js' );
			//}

		}

		/**
		 * Adding the admin css file
		 */
		public function teqcidb_admin_style() {

			wp_register_style( 'teqcidb_adminui', TEQCIDB_CSS_URL . 'teqcidb-main-admin.css', null, TEQCIDB_VERSION_NUM );
			wp_enqueue_style( 'teqcidb_adminui' );

		}

		/**
		 * Adding the frontend css file
		 */
		public function teqcidb_frontend_style() {

			wp_register_style( 'teqcidb_frontendui', TEQCIDB_CSS_URL . 'teqcidb-main-frontend.css', null, TEQCIDB_VERSION_NUM );
			wp_enqueue_style( 'teqcidb_frontendui' );

		}

		/**
		 *  Function to add table names to the global $wpdb.
		 */
		public function teqcidb_register_table_name() {
			global $wpdb;
			$wpdb->teqcidb_settings = "{$wpdb->prefix}teqcidb_settings";
			$wpdb->teqcidb_students = "{$wpdb->prefix}teqcidb_students";
			$wpdb->teqcidb_classes = "{$wpdb->prefix}teqcidb_classes";
			$wpdb->teqcidb_studenthistory = "{$wpdb->prefix}teqcidb_studenthistory";
			$wpdb->teqcidb_invoicehistory = "{$wpdb->prefix}teqcidb_invoicehistory";
			$wpdb->teqcidb_emails = "{$wpdb->prefix}teqcidb_emails";
		}

		/**
		 *  Function that calls the Style and Scripts needed for displaying of admin pointer messages.
		 */
		public function teqcidb_admin_pointers_javascript() {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
		}

		/**
		 *  Runs once upon plugin activation and creates the table that holds info on TEQcidbPlugin Pages & Posts.
		 */
		public function teqcidb_create_tables() {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			global $wpdb;
			global $charset_collate;

			// Call this manually as we may have missed the init hook.
			$this->teqcidb_register_table_name();

			$sql_create_table1 = "CREATE TABLE {$wpdb->teqcidb_settings}
			(
				ID bigint(190) auto_increment,
				repw varchar(255),
				PRIMARY KEY  (ID),
				KEY repw (repw)
			) $charset_collate; ";

			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'teqcidb_settings';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table1 );
				$table_name = $wpdb->prefix . 'teqcidb_settings';
				$wpdb->insert( $table_name, array( 'ID' => 1, ) );
			}

			$sql_create_table2 = "CREATE TABLE {$wpdb->teqcidb_students}
			(
				ID bigint(190) auto_increment,
				wpuserid bigint(190),
				uniquestudentid varchar(255),
				firstname varchar(255),
				lastname varchar(255),
				company varchar(255),
				contactstreetaddress varchar(255),
				contactcity varchar(255),
				contactstate varchar(255),
				contactzip varchar(255),
				billingstreetaddress varchar(255),
				billingcity varchar(255),
				billingstate varchar(255),
				billingzip varchar(255),
				phonecell varchar(255),
				phoneoffice varchar(255),
				fax varchar(255),
				email varchar(255),
				studentimage1 varchar(255),
				studentimage2 varchar(255),
				initialtrainingdate varchar(255),
				lastrefresherdate varchar(255),
				altcontactname varchar(255),
				altcontactemail varchar(255),
				altcontactphone varchar(255),
				newpaymentflag varchar(255),
				newregistrantflag varchar(255),
				allpaymentamounts varchar(255),
				allpaymentdates varchar(255),
				associations varchar(255),
				expirationdate varchar(255),
				qcinumber varchar(255),
				comments MEDIUMTEXT,
				PRIMARY KEY  (ID),
				KEY uniquestudentid (uniquestudentid)
			) $charset_collate; ";

			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'teqcidb_students';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table2 );
				$table_name = $wpdb->prefix . 'teqcidb_students';
				$wpdb->insert( $table_name, array( 'ID' => 1, ) );
			}


			$sql_create_table3 = "CREATE TABLE {$wpdb->teqcidb_classes}
			(
				ID bigint(190) auto_increment,
				uniqueclassid varchar(255),
				classname varchar(255),
				classformat varchar(255),
				classtype varchar(255),
				classsize varchar(255),
				classregistrantnumber varchar(255),
				instructors varchar(255),
				classstreetaddress varchar(255),
				classcity varchar(255),
				classstate varchar(255),
				classzip varchar(255),
				classstartdate varchar(255),
				classstarttime varchar(255),
				classendtime varchar(255),
				classcost varchar(255),
				classdescription MEDIUMTEXT,
				classhide varchar(255),
				PRIMARY KEY  (ID),
				KEY uniqueclassid (uniqueclassid)
			) $charset_collate; ";

			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'teqcidb_classes';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table3 );
				$table_name = $wpdb->prefix . 'teqcidb_classes';
				$wpdb->insert( $table_name, array( 'ID' => 1, ) );
			}

			$sql_create_table4 = "CREATE TABLE {$wpdb->teqcidb_studenthistory}
			(
				ID bigint(190) auto_increment,
				uniquestudentid varchar(255),
				classname varchar(255),
				wpuserid bigint(190),
				uniqueclassid varchar(255),
				registered varchar(255),
				adminapproved varchar(255),
				attended varchar(255),
				outcome varchar(255),
				paymentstatus varchar(255),
				amountpaid varchar(255),
				enrollmentdate varchar(255),
				credentialsdate varchar(255),
				referencenumber varchar(255),
				transactionid varchar(255),
				PRIMARY KEY  (ID),
				KEY uniqueclassid (uniqueclassid)
			) $charset_collate; ";

			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'teqcidb_studenthistory';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table4 );
				$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
				$wpdb->insert( $table_name, array( 'ID' => 1, ) );
			}


			$sql_create_table5 = "CREATE TABLE {$wpdb->teqcidb_invoicehistory}
			(
				ID bigint(190) auto_increment,
				uniquestudentid varchar(255),
				uniqueclassid varchar(255),
				classname varchar(255),
				wpuserid bigint(190),
				amountactuallypaid varchar(255),
				authcode varchar(255),
				messagecode varchar(255),
				transid varchar(255),
				transtime varchar(255),
				PRIMARY KEY  (ID),
				KEY uniquestudentid (uniquestudentid)
			) $charset_collate; ";

			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'teqcidb_invoicehistory';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table5 );
				$table_name = $wpdb->prefix . 'teqcidb_invoicehistory';
				$wpdb->insert( $table_name, array( 'ID' => 1, ) );
			}

			$sql_create_table6 = "CREATE TABLE {$wpdb->teqcidb_emails}
			(
				ID bigint(190) auto_increment,
				uniqueemailid varchar(255),
				emailname varchar(255),
				emaildescription varchar(255),
				subjectline varchar(255),
				fromemailaddress varchar(255),
				testingemailaddress varchar(255),
				emailmessage MEDIUMTEXT,
				PRIMARY KEY  (ID),
				KEY uniqueemailid (uniqueemailid)
			) $charset_collate; ";

			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'teqcidb_emails';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table6 );
				$table_name = $wpdb->prefix . 'teqcidb_emails';
				$wpdb->insert( $table_name, array( 'ID' => 1, ) );
			}


		}

		/**
		 *  The shortcode for displaying the login form / register forms / dashboard.
		 */
		public function teqcidb_student_registration_shortcode_function() {

			ob_start();
			include_once TEQCIDB_CLASS_DIR . 'class-teqcidb-registration-ui.php';
			$front_end_ui = new TEQciDb_Registration_UI();
			return ob_get_clean();
		}

		/**
		 *  The shortcode for displaying the login form / register forms / dashboard.
		 */
		public function teqcidb_all_classes_shortcode_function() {

			ob_start();
			include_once TEQCIDB_CLASS_DIR . 'class-teqcidb-all-classes-ui.php';
			$front_end_ui = new TEQciDb_All_Classes_UI();
			return ob_get_clean();
		}

		/**
		 *  The shortcode for displaying the front-end qci list.
		 */
		public function teqcidb_qci_list_shortcode_function() {

			ob_start();
			include_once TEQCIDB_CLASS_DIR . 'class-teqcidb-qci-list-ui.php';
			$front_end_ui = new TEQcidbPlugin_Qci_frontend_list();
			echo $front_end_ui->final_echoed_html;
			return ob_get_clean();
		}

		/**
		 *  Function that logs in a user automatically after they've first registered.
		 */
		public function teqcidb_autologin_after_registering() {

			if ( false !== stripos( $_SERVER['REQUEST_URI'], '?un=' ) ) {

				$username = filter_var( $_GET['un'], FILTER_SANITIZE_STRING );
				$user     = get_user_by( 'login', $username );

				// Redirect URL.
				if ( ! is_wp_error( $user ) ) {
					clean_user_cache( $user->ID );
					wp_clear_auth_cookie();
					wp_set_current_user($user->ID);
					wp_set_auth_cookie( $user->ID, true, false );
					update_user_caches( $user );
				}
			}
		}

		public function teqcidb_disable_admin_bar_for_non_admins() {
		    if (!current_user_can('administrator')) {
		        show_admin_bar(false);
		    }
		}



	}
endif;
