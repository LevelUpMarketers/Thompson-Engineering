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

			global $wpdb;

			// Get every single student, period.
			$table_name              = $wpdb->prefix . 'teqcidb_students';
			$this->all_students_array = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table_name}") );

		}

		/**
		 * Outputs all HTML elements on the page.
		 */
		public function output_settings1_form() {
			global $wpdb;

			// Set the current WordPress user.
			$currentwpuser = wp_get_current_user();

			$contact_form_html = '
				<div class="teqcidbplugin-form-section-wrapper">
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Name Of Class</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-classname" data-dbname="title" type="text" placeholder="Name of Class" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Class Format</label>
							<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-class-format" data-dbname="isbn10" type="text" placeholder="Student\'s State">
								<option value="default" selected default disabled>Make a Selection...</option>
								<option value="inperson">In-Person</option>
								<option value="online">Online</option>
								<option value="hybrid">Hybrid</option>
								<option value="other">Other</option>
							</select>
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Class Type</label>
							<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-class-type" data-dbname="isbn10" type="text" placeholder="Student\'s State">
								<option value="default" selected default disabled>Make a Selection...</option>
								<option value="initial">Initial</option>
								<option value="refresher">Refresher</option>
								<option value="hybrid">Hybrid</option>
								<option value="other">Other</option>
							</select>
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Street Address</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-address" data-dbname="authorlast1" type="text" placeholder="Class Street Address" />
						</div>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">City</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-city" data-dbname="authorfirst2" type="text" placeholder="Class City" />
						</div>
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
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-zip" data-dbname="authorfirst3" type="text" placeholder="Class Zip Code" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Class Date</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-startdate" data-dbname="originalpubdate" type="date" />
						</div>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Class Start Time</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-starttime" data-dbname="pages" type="time" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Class End Time</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-endtime" data-dbname="pages" type="time" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Class Cost</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-cost" data-dbname="isbn13" type="text" placeholder="Cost of Class" />
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Class Size</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-size" data-dbname="isbn13" type="number" placeholder="Maximum Number of Attendees" />
						</div>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Class Instructor(s)</label>
							<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-class-instructors-field" data-dbname="isbn13" type="text" placeholder="Class Instructor" />
							<div class="teqcidbplugin-add-more-instructors-div">
								<img class="teqcidbplugin-form-section-placeholder-image-small" id="teqcidbplugin-form-section-placeholder-image-backcover-actual" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png" />
								<p>Add an Instructor</p>
							</div>
						</div>
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">General Class Description</label>
							<textarea class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-textarea" id="teqcidbplugin-class-description" data-dbname="maincoverimage" type="text" placeholder="Provide a description for this class here."></textarea>
						</div>
					</div>
					<div class="teqcidbplugin-admin-form-section-header teqcidbplugin-historical-training-header">STUDENTS ASSOCIATED WITH THIS CLASS</div>


















					<div style="display: none;" class="teqcidbplugin-form-section-fields-class-wrapper teqcidbplugin-form-section-fields-class-wrapper-for-replication">
											<div class="teqcidbplugin-form-section-remove-student-historical-info-div">
												<img class="teqcidbplugin-form-section-placeholder-image-small teqcidbplugin-form-section-remove-student-historical-info" src="' . TEQCIDB_ROOT_IMG_URL . 'close.png">
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Choose a Student</label>
													<select class="teqcidbplugin-form-section-fields-input-text teqcidbplugin-form-studentname-student" id="teqcidbplugin-form-studentname-1">
														<option selected default disabled>Select a Student...</option>';	

														// Building drop-down of all existing Classes.
														$string8 = '';
														foreach ( $this->all_students_array as $student ) {
															$string8 = $string8 . '<option value="' . $student->firstname . ' ' . $student->lastname . '" data-altcontactname="' . $student->altcontactname . '" data-altcontactemail="' . $student->altcontactemail . '" data-altcontactphone="' . $student->altcontactphone . '" data-uniquestudentid="' . $student->uniquestudentid . '" data-wpuserid="' . $student->wpuserid . '" data-firstname="' . $student->firstname . '" data-lastname="' . $student->lastname . '" data-company="' . $student->company . '" data-contactstreetaddress="' . $student->contactstreetaddress . '" data-contactcity="' . $student->contactcity . '" data-contactstate="' . $student->contactstate . '" data-contactzip="' . $student->contactzip . '" data-phonecell="' . $student->phonecell . '" data-phoneoffice="' . $student->phoneoffice . '" data-email="' . $student->email . '" data-initialtrainingdate="' . $student->initialtrainingdate . '" data-qcinumber="' . $student->qcinumber . '" data-comments="' . $student->comments . '" data-associations="' . $student->associations . '" data-expirationdate="' . $student->expirationdate . '" data-lastrefresherdate="' . $student->lastrefresherdate . '">' . ucfirst( $student->firstname ) . ' ' . ucfirst( $student->lastname ) . '</option>';
														}

														$contact_form_html = $contact_form_html . $string8 . 
													'</select>
												</div>
												<div style="display: none;" class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">First Name</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-firstname" data-dbname="title" type="text" value="' . $actualstudentdata->firstname . '" placeholder="Student\'s First Name">
												</div>
												<div style="display: none;" class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Last Name</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastname" data-dbname="authorfirst1" type="text" value="' . $actualstudentdata->lastname . '" placeholder="Student\'s Last Name">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Company</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-company" data-dbname="authorlast1" type="text" value="' . $actualstudentdata->company . '" placeholder="Student\'s Company">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Cell Phone</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-phonecell" data-dbname="authorfirst2" type="text" value="' . $actualstudentdata->phonecell . '" placeholder="Student\'s Cell Phone">
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Office Phone</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-phoneoffice" data-dbname="authorlast2" type="text" value="' . $actualstudentdata->phoneoffice . '" placeholder="Students\'s Office Phone">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Email</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-email" data-dbname="authorfirst3" type="text" value="' . $actualstudentdata->email . '" placeholder="Student\'s Email Address">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Street Address</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-contactstreetaddress" data-dbname="authorlast3" type="text" value="' . $actualstudentdata->contactstreetaddress . '" placeholder="Student\'s Street Address">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">City</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-contactcity" data-dbname="pages" type="text" value="' . $actualstudentdata->contactcity . '" placeholder="Student\'s City">
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">State</label>
													<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-student-contactstate" data-dbname="isbn10" type="text" placeholder="Student\'s State">
														<option ' . $selected1 . ' value="AL">Alabama</option>
														<option ' . $selected2 . ' value="AK">Alaska</option>
														<option ' . $selected3 . ' value="AZ">Arizona</option>
														<option ' . $selected4 . ' value="AR">Arkansas</option>
														<option ' . $selected5 . ' value="CA">California</option>
														<option ' . $selected6 . ' value="CO">Colorado</option>
														<option ' . $selected7 . ' value="CT">Connecticut</option>
														<option ' . $selected8 . ' value="DE">Delaware</option>
														<option ' . $selected9 . ' value="DC">District Of Columbia</option>
														<option ' . $selected10 . ' value="FL">Florida</option>
														<option ' . $selected11 . ' value="GA">Georgia</option>
														<option ' . $selected12 . ' value="HI">Hawaii</option>
														<option ' . $selected13 . ' value="ID">Idaho</option>
														<option ' . $selected14 . ' value="IL">Illinois</option>
														<option ' . $selected15 . ' value="IN">Indiana</option>
														<option ' . $selected16 . ' value="IA">Iowa</option>
														<option ' . $selected17 . ' value="KS">Kansas</option>
														<option ' . $selected18 . ' value="KY">Kentucky</option>
														<option ' . $selected19 . ' value="LA">Louisiana</option>
														<option ' . $selected20 . ' value="ME">Maine</option>
														<option ' . $selected21 . ' value="MD">Maryland</option>
														<option ' . $selected22 . ' value="MA">Massachusetts</option>
														<option ' . $selected23 . ' value="MI">Michigan</option>
														<option ' . $selected24 . ' value="MN">Minnesota</option>
														<option ' . $selected25 . ' value="MS">Mississippi</option>
														<option ' . $selected26 . ' value="MO">Missouri</option>
														<option ' . $selected27 . ' value="MT">Montana</option>
														<option ' . $selected28 . ' value="NE">Nebraska</option>
														<option ' . $selected29 . ' value="NV">Nevada</option>
														<option ' . $selected30 . ' value="NH">New Hampshire</option>
														<option ' . $selected31 . ' value="NJ">New Jersey</option>
														<option ' . $selected32 . ' value="NM">New Mexico</option>
														<option ' . $selected33 . ' value="NY">New York</option>
														<option ' . $selected34 . ' value="NC">North Carolina</option>
														<option ' . $selected35 . ' value="ND">North Dakota</option>
														<option ' . $selected36 . ' value="OH">Ohio</option>
														<option ' . $selected37 . ' value="OK">Oklahoma</option>
														<option ' . $selected38 . ' value="OR">Oregon</option>
														<option ' . $selected39 . ' value="PA">Pennsylvania</option>
														<option ' . $selected40 . ' value="RI">Rhode Island</option>
														<option ' . $selected41 . ' value="SC">South Carolina</option>
														<option ' . $selected42 . ' value="SD">South Dakota</option>
														<option ' . $selected43 . ' value="TN">Tennessee</option>
														<option ' . $selected44 . ' value="TX">Texas</option>
														<option ' . $selected45 . ' value="UT">Utah</option>
														<option ' . $selected46 . ' value="VT">Vermont</option>
														<option ' . $selected47 . ' value="VA">Virginia</option>
														<option ' . $selected48 . ' value="WA">Washington</option>
														<option ' . $selected49 . ' value="WV">West Virginia</option>
														<option ' . $selected50 . ' value="WI">Wisconsin</option>
														<option ' . $selected51 . ' value="WY">Wyoming</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Zip Code</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-contactzip" data-dbname="isbn13" type="text" value="' . $actualstudentdata->contactzip . '" placeholder="Student\'s Zip Code">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">QCI Number</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-qcinumber" data-dbname="publisher" type="text" value="' . $actualstudentdata->qcinumber . '" placeholder="Student\'s QCI Number">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Initial Training Date</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-initialtrainingdate" data-dbname="originalpubdate" type="date" value="' . $actualstudentdata->initialtrainingdate . '" placeholder="Student\'s Initial Training Date">
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Name</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactname" data-dbname="isbn13" type="text" placeholder="Alternate Contact Name" />
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Email</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactemail" data-dbname="publisher" type="text" placeholder="Alternate Contact Email" />
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Phone</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactphone" type="text" placeholder="Alternate Contact Phone" />
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Last Refresher Date</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastrefresherdate" data-dbname="originalpubdate" type="date" value="' . $actualstudentdata->lastrefresherdate . '" placeholder="Student\'s Last Refresher Training Date">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Expiration Date</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-expirationdate" data-dbname="originalpubdate" type="date" value="' . $actualstudentdata->expirationdate . '" placeholder="Student\'s Expiration Date">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Student\'s Associations</label>
													<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div-container">
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-aapa" type="checkbox" ' . $aapa . ' data-association="aapa" />
															<label>AAPA</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-arba" type="checkbox" ' . $arba . ' data-association="arba" />
															<label>ARBA</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-agc" type="checkbox" ' . $agc . ' data-association="agc" />
															<label>AGC</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-abc" type="checkbox" ' . $abc . ' data-association="abc" />
															<label>ABC</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-auca" type="checkbox" ' . $auca . ' data-association="auca" />
															<label>AUCA</label>
														</div>
														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">
															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox" id="teqcidbplugin-student-association-none" type="checkbox" ' . $none . ' data-association="none" />
															<label>None</label>
														</div>
													</div>
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Comments about this Student</label>
													<textarea style="width: 13vw;" disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-textarea" id="teqcidbplugin-student-comments" data-dbname="maincoverimage" type="text" placeholder="Enter comments about this student">' . $actualstudentdata->comments . '</textarea>
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Admin Approved?</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-registeredforclass" data-dbname="isbn10" type="text" placeholder="Student\'s State">
														<option value="default" selected default disabled>Make a Selection...</option>
														<option ' . $registered_selected_yes . ' value="yes">Yes</option>
														<option ' . $registered_selected_no . ' value="no">No</option>
														<option ' . $registered_selected_pending . ' value="pending">Pending Approval</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Attended this Class?</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-attended" data-dbname="isbn10" type="text" placeholder="Student\'s State">
														<option value="default" selected default disabled>Make a Selection...</option>
														<option ' . $attended_selected_upcoming . ' value="upcoming">Class is Upcoming</option>
														<option ' . $attended_selected_yes . ' value="yes">Yes</option>
														<option ' . $attended_selected_no . ' value="no">No</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Enrollment Date</label>
													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classenrollment" type="date" />
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Credentials Date</label>
													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classcredentials" type="date" />
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Outcome</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-outcome" data-dbname="isbn10" type="text" placeholder="Student\'s State">
														<option value="default" selected default disabled>Make a Selection...</option>
														<option ' . $outcome_selected_upcoming . ' value="upcoming">Class is Upcoming</option>
														<option ' . $outcome_selected_passed . ' value="passed">Passed</option>
														<option ' . $outcome_selected_failed . ' value="failed">Failed</option>
														<option ' . $outcome_selected_deferred . ' value="deferred">Deferred/Delayed</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Payment Status</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-paymentstatus" data-dbname="isbn10" type="text" placeholder="Student\'s State">
														<option value="default" selected default disabled>Make a Selection...</option>
														<option ' . $payment_selected_pending . ' value="pending">Payment Pending</option>
														<option ' . $payment_selected_full . ' value="paidinfull">Paid in Full</option>
														<option ' . $payment_selected_none . ' value="nopaymentmade">No Payment Made</option>
														<option ' . $payment_selected_waived . ' value="paymentwaived">Payment Waived</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Amount Paid</label>
													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-amountpaid" data-dbname="isbn13" type="text" value="' . $training_data->amountpaid . '" placeholder="Amount Paid">
													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-uniquestudentid" type="hidden" value="' . $actualstudentdata->uniquestudentid . '">
													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-wpuserid" type="hidden" value="' . $actualstudentdata->wpuserid . '">
												</div>
											</div>




											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<div class="teqcidbplugin-form-section-placeholder-image-wrapper">
														<img class="teqcidbplugin-form-section-placeholder-image" id="teqcidbplugin-form-section-placeholder-image-frontcover-actual" src="' . $actualstudentdata->studentimage1 . '">
													</div>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<div class="teqcidbplugin-form-section-placeholder-image-wrapper">
														<img class="teqcidbplugin-form-section-placeholder-image" id="teqcidbplugin-form-section-placeholder-image-backcover-actual" src="' . $actualstudentdata->studentimage2 . '">
													</div>
												</div>
											</div>
										</div>































					<div class="teqcidbplugin-add-more-historical-data-div">
						<img class="teqcidbplugin-form-section-placeholder-image-small" id="teqcidbplugin-form-section-placeholder-image-backcover-actual" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png" />
						<p>Add a Student</p>
					</div>
					<div class="teqcidbplugin-form-section-fields-wrapper">
						<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
							<label class="teqcidbplugin-form-section-fields-label">Create This Class Now!</label>
							<button class="teqcidbplugin-form-section-submit-button" id="teqcidbplugin-form-section-add-class-button">Create This Class</button>
							<div class="teqcidbplugin-spinner"></div>
		 					<div class="teqcidbplugin-response-div-actual-container">
		 						<p class="teqcidbplugin-response-div-p"></p>
		 					</div>
						</div>
					</div>
				</div>';


			$string1 = '
				<div id="teqcidbplugin-display-options-container">
					<p class="teqcidbplugin-tab-intro-para">Here is where you can create individual classes.</p>
					<div class="teqcidbplugin-form-wrapper">
						' . $contact_form_html . '

					


					</div>
				</div>';

			echo $string1;
		}
	}
endif;
