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

			// For grabbing an image from media library.
			wp_enqueue_media();

			global $wpdb;

			// Get every single class, period.
			$table_name              = $wpdb->prefix . 'teqcidb_classes';
			$this->all_classes_array = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table_name}") );
		}

		/**
		 * Outputs all HTML elements on the page.
		 */
		public function output_settings1_form() {
			global $wpdb;

			/*
				Below is a default contact form using default class names, ids, and custom data attributes, with associated default styling found in the "BEGIN CSS FOR COMMON FORM FILL" section of the teqcidb-admin-ui.scss file. The custom data attribute "data-dbname" is supposed to hold the exact name of the corresponding database column in the database, prefixed with a description of the kind of "object" we're working with. For example, if I were creating an App that needed to save Student data, I would probably call that database table 'studentdata' and each column in that database would begin with 'student'. So, I would replace all instances below of data-dbname="contact with data-dbname="student. I would also replace each instance of id="teqcidbplugin-form-contact with id="teqcidbplugin-form-student. If I were creating an app that needed to track customer info, and not students, I would replace all instances below of data-dbname="contact with data-dbname="customer. I would also replace each instance of id="teqcidbplugin-form-contact with id="teqcidbplugin-form-customer.
			*/
			$contact_form_html = '
				<div class="teqcidbplugin-form-section-wrapper">
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">First Name</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-firstname" data-dbname="title" type="text" placeholder="Student\'s First Name" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Last Name</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastname" data-dbname="authorfirst1" type="text" placeholder="Student\'s Last Name" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Company</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-company" data-dbname="authorlast1" type="text" placeholder="Student\'s Company" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Cell Phone</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-cellphone" data-dbname="authorfirst2" type="text" placeholder="Student\'s Cell Phone" />
						</div>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Office Phone</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-officephone" data-dbname="authorlast2" type="text" placeholder="Students\'s Office Phone" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Email</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-email" data-dbname="authorfirst3" type="text" placeholder="Student\'s Email Address" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Street Address</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-streetaddress" data-dbname="authorlast3" type="text" placeholder="Student\'s Street Address" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">City</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-city" data-dbname="pages" type="text" placeholder="Student\'s City" />
						</div>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">State</label>
							<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-student-state" data-dbname="isbn10" type="text" placeholder="Student\'s State">
								<option value="AL">Alabama</option>
								<option value="AK">Alaska</option>
								<option value="AZ">Arizona</option>
								<option value="AR">Arkansas</option>
								<option value="CA">California</option>
								<option value="CO">Colorado</option>
								<option value="CT">Connecticut</option>
								<option value="DE">Delaware</option>
								<option value="DC">District Of Columbia</option>
								<option value="FL">Florida</option>
								<option value="GA">Georgia</option>
								<option value="HI">Hawaii</option>
								<option value="ID">Idaho</option>
								<option value="IL">Illinois</option>
								<option value="IN">Indiana</option>
								<option value="IA">Iowa</option>
								<option value="KS">Kansas</option>
								<option value="KY">Kentucky</option>
								<option value="LA">Louisiana</option>
								<option value="ME">Maine</option>
								<option value="MD">Maryland</option>
								<option value="MA">Massachusetts</option>
								<option value="MI">Michigan</option>
								<option value="MN">Minnesota</option>
								<option value="MS">Mississippi</option>
								<option value="MO">Missouri</option>
								<option value="MT">Montana</option>
								<option value="NE">Nebraska</option>
								<option value="NV">Nevada</option>
								<option value="NH">New Hampshire</option>
								<option value="NJ">New Jersey</option>
								<option value="NM">New Mexico</option>
								<option value="NY">New York</option>
								<option value="NC">North Carolina</option>
								<option value="ND">North Dakota</option>
								<option value="OH">Ohio</option>
								<option value="OK">Oklahoma</option>
								<option value="OR">Oregon</option>
								<option value="PA">Pennsylvania</option>
								<option value="RI">Rhode Island</option>
								<option value="SC">South Carolina</option>
								<option value="SD">South Dakota</option>
								<option value="TN">Tennessee</option>
								<option value="TX">Texas</option>
								<option value="UT">Utah</option>
								<option value="VT">Vermont</option>
								<option value="VA">Virginia</option>
								<option value="WA">Washington</option>
								<option value="WV">West Virginia</option>
								<option value="WI">Wisconsin</option>
								<option value="WY">Wyoming</option>
							</select>
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Zip Code</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-zip" data-dbname="isbn13" type="text" placeholder="Student\'s Zip Code" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">QCI Number</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-qci" data-dbname="publisher" type="text" placeholder="Student\'s QCI Number" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Initial Training Date</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-initialtrainingdate" type="date" placeholder="Student\'s Initial Training Date" />
						</div>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Name</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactname" data-dbname="isbn13" type="text" placeholder="Alternate Contact Name" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Email</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactemail" data-dbname="publisher" type="text" placeholder="Alternate Contact Email" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Phone</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactphone" type="text" placeholder="Alternate Contact Phone" />
						</div>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Last Refresher Date</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastrefresherdate" type="date" placeholder="Student\'s Last Referesher Training Date" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Expiration Date</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-expirationdate" type="date" placeholder="Student\'s Initial Training Date" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Student\'s Associations</label>
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div-container">
								<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
									<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-aapa" type="checkbox" data-association="aapa" />
									<label>AAPA</label>
								</div>
								<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
									<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-arba" type="checkbox" data-association="arba" />
									<label>ARBA</label>
								</div>
								<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
									<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-agc" type="checkbox" data-association="agc" />
									<label>AGC</label>
								</div>
								<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
									<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-abc" type="checkbox" data-association="abc" />
									<label>ABC</label>
								</div>
								<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
									<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-auca" type="checkbox" data-association="auca" />
									<label>AUCA</label>
								</div>
								<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
									<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-none" type="checkbox" data-association="none" />
									<label>None</label>
								</div>
							</div>
						</div>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">General Notes & Comments about this Student</label>
							<textarea class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-textarea" id="teqcidbplugin-student-comments" data-dbname="maincoverimage" type="text" placeholder="Enter comments about this student"></textarea>
						</div>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Student Image #1</label>
							<div class="teqcidbplugin-form-section-placeholder-image-wrapper">
								<img class="teqcidbplugin-form-section-placeholder-image" id="teqcidbplugin-form-section-placeholder-image-frontcover-actual" src="' . TEQCIDB_ROOT_IMG_URL . 'student-image-placeholder.png" />
							</div>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-image1" data-dbname="maincoverimage" type="text" placeholder="Enter URL or use button below" />
							<button class="teqcidbplugin-form-section-placeholder-image-button" id="teqcidbplugin-form-section-placeholder-image-button-frontcover">Choose Image...</button>
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Student Image #2</label>
							<div class="teqcidbplugin-form-section-placeholder-image-wrapper">
								<img class="teqcidbplugin-form-section-placeholder-image" id="teqcidbplugin-form-section-placeholder-image-backcover-actual" src="' . TEQCIDB_ROOT_IMG_URL . 'student-image-placeholder.png" />
							</div>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-image2" data-dbname="backcoverimage" type="text" placeholder="Enter URL or use button below" />
							<button class="teqcidbplugin-form-section-placeholder-image-button" id="teqcidbplugin-form-section-placeholder-image-button-backcover">Choose Image...</button>
						</div>
					</div>
					<div class="teqcidbplugin-admin-form-section-header teqcidbplugin-historical-training-header">HISTORICAL TRAINING INFORMATION</div>
					<div class="teqcidbplugin-form-section-fields-class-wrapper teqcidbplugin-form-section-fields-class-add-history-wrapper teqcidbplugin-form-section-fields-class-wrapper-for-replication" style="display:none;">
						<div class="teqcidbplugin-form-section-remove-student-historical-info-div">
							<img class="teqcidbplugin-form-section-placeholder-image-small teqcidbplugin-form-section-remove-student-historical-info" src="' . TEQCIDB_ROOT_IMG_URL . 'close.png" />
						</div>
						<div class="teqcidbplugin-form-section-fields-wrapper">
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Class Name</label>
								<select class="teqcidbplugin-form-section-fields-input-text teqcidbplugin-form-classname-class" id="teqcidbplugin-form-classname-1">
										<option selected default disabled>Choose a Class...</option>';	

										// Building drop-down of all existing Classes.
										$string8 = '';
										foreach ( $this->all_classes_array as $class ) {
											$string8 = $string8 . '<option value="' . $class->classname . '" data-classtype="' . $class->classtype . '" data-classstartdate="' . $class->classstartdate . '" data-classcost="' . $class->classcost . '" data-uniqueclassid="' . $class->uniqueclassid . '" data-classtype="' . $class->classtype . '"    >' . ucfirst( $class->classname ) . '</option>';
										}

										$contact_form_html = $contact_form_html . $string8 . 
								'</select>
							</div>
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Class Date</label>
								<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classstartdate-1" data-dbname="genre" type="date" placeholder="Date of Class/Online Completion Date" />
							</div>
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Class Type</label>
								<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-classtype-1" data-dbname="isbn10" type="text" placeholder="Student\'s State">
									<option value="default" selected default disabled>Make a Selection...</option>
									<option value="initial">Initial</option>
									<option value="refresher">Refresher</option>
									<option value="hybrid">Hybrid</option>
									<option value="other">Other</option>
								</select>
							</div>
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Class Cost</label>
								<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-classcost-1" type="text" placeholder="" />
							</div>
						</div>
						<div class="teqcidbplugin-form-section-fields-wrapper">
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Enrollment Date</label>
								<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classenrollment-1" type="date" />
							</div>
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Credentials Date</label>
								<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classcredentials" type="date" />
							</div>
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Attended This Class?</label>
								<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-attendedthisclass-1">
									<option default disabled selected>Make a Selection...</option>
									<option value="upcoming">Class is Upcoming</option>
									<option value="yes">Yes</option>
									<option value="no">No</option>
								</select>
							</div>
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Class Outcome</label>
								<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-classoutcome-1">
									<option default disabled selected>Make a Selection...</option>
									<option value="upcoming">Class is Upcoming</option>
									<option value="passed">Passed</option>
									<option value="failed">Failed</option>
									<option value="deferred">Deferred/Delayed</option>
								</select>
							</div>
						</div>
						<div class="teqcidbplugin-form-section-fields-wrapper">
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Admin Approved?</label>
								<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-registeredforclass-1">
									<option default disabled selected>Make a Selection...</option>
									<option value="pending">Pending Approval</option>
									<option value="yes">Yes</option>
									<option value="no">No</option>
								</select>
							</div>
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Payment Status</label>
								<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-paymentstatus-1">
									<option default disabled selected>Make a Selection...</option>
									<option value="pending">Payment Pending</option>
									<option value="paidinfull">Paid in Full</option>
									<option value="nopaymentmade">No Payment Made</option>
									<option value="paymentwaived">Payment Waived</option>
								</select>
							</div>
							<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
								<label class="teqcidbplugin-form-section-fields-label">Amount Paid</label>
								<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-amountactuallypaid-1" type="text" placeholder="" />
							</div>
						</div>
					</div>
					<div class="teqcidbplugin-add-more-historical-data-div">
						<img class="teqcidbplugin-form-section-placeholder-image-small" id="teqcidbplugin-form-section-placeholder-image-backcover-actual" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png" />
						<p>Add Training Info</p>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Add This Student Now!</label>
							<button class="teqcidbplugin-form-section-submit-button teqcidbplugin-form-section-add-new-student-submit-button" id="teqcidbplugin-form-section-add-student-by-admin-button">Add Student</button>
							<div class="teqcidbplugin-spinner"></div>
		 					<div class="teqcidbplugin-response-div-actual-container">
		 						<p class="teqcidbplugin-response-div-p"></p>
		 					</div>
						</div>
					</div>
				</div>';


			$string1 = '
				<div id="teqcidbplugin-display-options-container">
					<p class="teqcidbplugin-tab-intro-para">Here is where you can manually add a new student to the QCI Database.</p>
					<div class="teqcidbplugin-form-wrapper">
						' . $contact_form_html . '

					


					</div>
				</div>';

			echo $string1;
		}
	}
endif;
