<?php
/**
 * TEQcidbPlugin Book Display Options Form Tab Class - class-teqcidbplugin-book-display-options-form.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes/Classes
 * @version  6.1.5.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TEQcidbPlugin_Settings1_Form', false ) ) :

	/**
	 * TEQcidbPlugin_Admin_Menu Class.
	 */
	class TEQcidbPlugin_Settings1_Form {


		/**
		 * Class Constructor - Simply calls the Translations
		 */
		public function __construct() {

			

		}

		/**
		 * Outputs all HTML elements on the page.
		 */
		public function output_settings1_form() {
			global $wpdb;

			// Set the current WordPress user.
			$currentwpuser = wp_get_current_user();

			$string1 = '<div id="teqcidbplugin-display-options-container">
							<p class="teqcidbplugin-tab-intro-para">Here you can specify your Email Messages, Subject Lines, "From" Email Address, etc.</p>
							<div class="teqcidbplugin-form-section-wrapper teqcidbplugin-form-section-create-db-wrapper">
								<div class="teqcidbplugin-table-creator-wrapper">
									<div class="teqcidbplugin-form-section-fields-wrapper">
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Name</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-emailname" type="text" placeholder="Enter a name for this type of email" />
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Description</label>
											<textarea id="teqcidbplugin-email-emaildescription" class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" type="text" placeholder="A Description of what this type of email is used for"></textarea>
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Subject Line</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-subjectline" type="text" placeholder="Enter the Subject Line for this email" />
										</div>
									</div>
									<div class="teqcidbplugin-form-section-fields-wrapper">
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email "From" Address</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-fromaddress" type="text" placeholder="The email address recipients will see this being sent from" />
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Address for Testing</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-testingaddress" type="text" placeholder="Send a test email to this email address" />
										</div>
									</div>
									<div class="teqcidbplugin-form-section-fields-wrapper">
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Message</label>
											<label style="margin-top: 20px; margin-bottom: 5px;" class="teqcidbplugin-form-section-fields-label">To use personalized information in the emails, create your template using these fields: </label>
											<label style="font-weight:bold;margin-bottom: 5px;" class="teqcidbplugin-form-section-fields-label">[firstname] [lastname] [studentemail] [studentphone] [studentstreetaddress] [studentcity] [studentstate] [studentzip] [studentcompany] [studentexpiredate]</label>
											<label style="margin-bottom: 20px; margin-top:5px;" class="teqcidbplugin-form-section-fields-label">For example, if you typed "Hello [firstname] from [studentcity], [studentstate]!" in the text area below, the text the student would receive in their actual email would read: "Hello John from Denver, CO!"</label>
											<label style="margin-top: 20px; margin-bottom: 40px;" class="teqcidbplugin-form-section-fields-label">To use links in your emails, place <span style="font-weight:bold;">[link]</span> right before the URL, and place <span style="font-weight:bold;">[endlink]</span> directly after the URL. For example, if I wanted to place a link to Google.com in my email, I would type this in the text area below: "[link]https://www.google.com[endlink]" </label>
											<textarea placeholder="Hi [firstname],

We wanted to let you know that your Stormwater Training Certification will expire on [studentexpiredate]. To remain certified, please visit  [link]https://training.thompsonengineering.com/[endlink] to view a list of all upcoming classes and register for a refresher training course.

Thanks!" id="teqcidbplugin-email-actualmessage" class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-form-section-fields-input-text-emailmessage-actual" type="text" placeholder="The actual message you want to provide to recipients"></textarea>
										</div>
									</div>
									<div class="teqcidbplugin-form-section-create-extra-columns-wrapper">
										<div>
											<button id="teqcidbplugin-save-this-email-button">Save This Email</button>
											<button id="teqcidbplugin-send-test-email-button">Send Test Email</button>
										</div>
									</div>
									<div class="teqcidbplugin-spinner"></div>
				 					<div class="teqcidbplugin-response-div-actual-container">
				 						<p class="teqcidbplugin-response-div-p"></p>
				 					</div>
								</div>
							</div>
						</div>';


			echo $string1;
		}
	}
endif;







