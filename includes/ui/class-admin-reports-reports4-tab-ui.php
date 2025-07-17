<?php
/**
 * TEQcidbPlugin_Settings_Settings1_Tab Tab - class-admin-settings-libraries-tab-ui.php.
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes/Classes
 * @version  1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TEQcidbPlugin_Settings_Settings1_Tab', false ) ) :

	/**
	 * TEQcidbPlugin_Settings_Settings1_Tab Class.
	 */
	class TEQcidbPlugin_Settings_Settings1_Tab {

		/**
		 * Class Constructor
		 */
		public function __construct() {
			require_once TEQCIDB_CLASS_DIR . 'class-admin-ui-template.php';
			require_once TEQCIDB_CLASS_DIR . 'class-reports-four-form.php';

			// Instantiate the class.
			$this->template = new TEQcidbPlugin_Admin_UI_Template();
			$this->form     = new TEQcidbPlugin_Settings1_Form();
			$this->output_open_admin_container();
			$this->output_tab_content();
			$this->output_close_admin_container();
			$this->output_admin_template_advert();
		}

		/**
		 * Opens the admin container for the tab
		 */
		private function output_open_admin_container() {
			$title    = 'New Students';
			$icon_url = TEQCIDB_ROOT_IMG_URL . 'newstudent.png';

			echo $this->template->output_open_admin_container( $title, $icon_url );

		}

		/**
		 * Outputs actual tab contents
		 */
		private function output_tab_content() {
			echo $this->form->final_echoed_html;
		}

		/**
		 * Closes admin container.
		 */
		private function output_close_admin_container() {
			echo $this->template->output_close_admin_container();
		}

		/**
		 * Outputs advertisment area.
		 */
		private function output_admin_template_advert() {
			echo $this->template->output_template_advert();
		}


	}
endif;

// Instantiate the class.
$cm = new TEQcidbPlugin_Settings_Settings1_Tab();
