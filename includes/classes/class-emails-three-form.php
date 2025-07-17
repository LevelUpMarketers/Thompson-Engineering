<?php
/**
 * TEQcidbPlugin Book Display Options Form Tab Class - class-teqcidbplugin-book-display-options-form.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes/Classes
 * @version  6.1.5.
 */

/*
* INSTRUCTIONS
* Replace students with the thing we're editing here. Is this plugin for recording students, Leads, Cars, Websites, Properties, etc.?
* Replace 'THING1' through 'THING6' with an individual item we're going to be searching for.
* Replace 'teqcidb_students' with the database table THING5 of the main thing we're recording here.
* Replace 'THINGWENEEDWILDCARDSEARCHFOR' with one of the search things (THING1, THING2, THING3, THING4, THING5, or THING6) that we need to perform a Wildcard search on, using the MySQL 'LIKE' Operator, as opposed to a straight = operation.
* Replace 'studentsTHING5' with the main THING5 of the thing we're recording. This is also the thing we'll be ordering the Queries by.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TEQcidbPlugin_settings1_Form', false ) ) :

	/**
	 * TEQcidbPlugin_Admin_Menu Class.
	 */
	class TEQcidbPlugin_settings1_Form {

		public $students_table    = '';
		public $studentsdbresults = array();
		public $create_opening_html = '';
		public $create_search_ui_html = '';
		public $create_search_ui_results_html = '';
		public $create_individual_students_html = '';
		public $create_pagination_html = '';
		public $create_closing_html = '';
		public $final_echoed_html = '';
		public $final_grabbed_params = '';
		public $total_students_count = 0;
		public $pagination_display_limit = 40;
		public $pagination_place = 0;
		public $search_THING1 = '';
		public $search_THING2 = '';
		public $search_THING3 = '';
		public $search_THING4 = '';
		public $search_THING5 = '';
		public $search_THING6 = '';
		public $active_search = false;
		public $set_params_array = array();
		public $export_button_html = '';
		public $export = '';
		public $query_part_for_export = '';


		/**
		 * Class Constructor
		 */
		public function __construct() {

			$this->grab_url_params();

			$this->query_db();

			$this->create_opening_html();

			//$this->create_search_ui();

			$this->create_individual_students_html();

			$this->create_pagination_html();

			$this->create_closing_html();

			$this->stitch_ui_html();

		}

		/**
		 * Function to grab URL params, if any exist.
		 */
		public function grab_url_params()
		{
			// Grab all the things we could be searching for from the URL Params.
			$this->search_THING1 = $_GET['THING1'];
			$this->search_THING2 = $_GET['THING2'];
			$this->search_THING3 = $_GET['THING3'];
			$this->search_THING4 = $_GET['THING4'];
			$this->search_THING5 = $_GET['THING5'];
			$this->search_THING6 = $_GET['THING6'];

			// Get where we're at with the Pagination currently.
			if ( isset( $_GET['pn'] ) ) {
				$this->pagination_place = $_GET['pn'];
			}

			// Add to the active Parameters array and set the search flag to true.
			if ( 'null' !== $this->search_THING1 && '' !== $this->search_THING1 && null !== $this->search_THING1 ) {
				$this->set_params_array['studentsTHING1'] = $this->search_THING1;
				$this->active_search = true;
			}

			// Add to the active Parameters array and set the search flag to true.
			if ( 'null' !== $this->search_THING2 && '' !== $this->search_THING2 && null !== $this->search_THING2 ) {
				$this->set_params_array['studentsTHING2'] = $this->search_THING2;
				$this->active_search = true;
			}

			// Add to the active Parameters array and set the search flag to true.
			if ( 'null' !== $this->search_THING3 && '' !== $this->search_THING3 && null !== $this->search_THING3 ) {
				$this->set_params_array['studentsTHING3'] = $this->search_THING3;
				$this->active_search = true;
			}

			// Add to the active Parameters array and set the search flag to true.
			if ( 'null' !== $this->search_THING4 && '' !== $this->search_THING4 && null !== $this->search_THING4 ) {
				$this->set_params_array['studentsTHING4'] = $this->search_THING4;
				$this->active_search = true;
			}

			// Add to the active Parameters array and set the search flag to true.
			if ( 'null' !== $this->search_THING5 && '' !== $this->search_THING5 && null !== $this->search_THING5 ) {
				$this->set_params_array['studentsTHING5'] = $this->search_THING5;
				$this->active_search = true;
			}

			// Add to the active Parameters array and set the search flag to true.
			if ( 'null' !== $this->search_THING6 && '' !== $this->search_THING6 && null !== $this->search_THING6 ) {
				$this->set_params_array['studentsTHING6'] = $this->search_THING6;
				$this->active_search = true;
			}
		}

		/**
		 * Function to house all logic required to query the database depending on URL params, if any exist.
		 */
		public function query_db()
		{

			global $wpdb;
			$this->students_table = $wpdb->prefix . 'teqcidb_emails';
			$this->student_history_table = $wpdb->prefix . 'teqcidb_studenthistory';
			$this->class_history_table = $wpdb->prefix . 'teqcidb_classes';

			// If we have an active search in play...
			if ( $this->active_search ) {

				// This If-Else and the get_results line directly after it is if we want to do an exclusive search - a serach that returns a smaller, more specific amount of results.
				$query_part = '';
				$count_query_part = '';

				// If there's only 1 Search Parameter in play, this If statement executes, to make sure we're not appending additional stuff to the DB Query.
				if ( 1 === sizeof($this->set_params_array) ) {
					foreach ( $this->set_params_array as $params_search_key => $params_search_value ) {

						// If we need a Wildcard Search for something - if so, we need to do a 'Like' instead of =, else, do a strict = comparison in the else block below.
						if ( 'studentsTHINGWENEEDWILDCARDSEARCHFOR' === $params_search_key ) {
							$query_part = "SELECT * FROM $this->students_table WHERE " . $params_search_key . " LIKE '%" . $params_search_value . "%'";
							$count_query_part = " WHERE " . $params_search_key . " LIKE '%" . $params_search_value . "%'";
						} else {
							$query_part = "SELECT * FROM $this->students_table WHERE " . $params_search_key . " = '" . $params_search_value . "'";
							$count_query_part = " WHERE " . $params_search_key . " = '" . $params_search_value . "'";
						}

					}
				} else {
					// All this below executes if there are more searches in play than just 1.
					$counter = 0;
					foreach ( $this->set_params_array as $params_search_key => $params_search_value ) {

						// If this is our first time in the loop, begin the new Query correctly.
						if ( 0 === $counter ) {
							
							// If we need a Wildcard Search for something - if so, we need to do a 'Like' instead of =, else, do a strict = comparison in the else block below.
							if ( 'studentsTHINGWENEEDWILDCARDSEARCHFOR' === $params_search_key ) {
								$query_part = "SELECT * FROM $this->students_table WHERE " . $params_search_key . " LIKE '%" . $params_search_value . "%'";
								$count_query_part = " WHERE " . $params_search_key . " LIKE '%" . $params_search_value . "%'";
							} else {
								$query_part = "SELECT * FROM $this->students_table WHERE " . $params_search_key . " = '" . $params_search_value . "'";
								$count_query_part = " WHERE " . $params_search_key . " = '" . $params_search_value . "'";
							}
							$counter++;
						} else {

							// Continue building the Query by appending 'AND' Operators.

							// If we need a Wildcard Search for something - if so, we need to do a 'Like' instead of =, else, do a strict = comparison in the else block below.
							if ( 'studentsTHINGWENEEDWILDCARDSEARCHFOR' === $params_search_key ) {
								$query_part = $query_part . " AND " . $params_search_key . " LIKE '%" . $params_search_value . "%'";
								$count_query_part = $count_query_part . " AND " . $params_search_key . " LIKE '%" . $params_search_value . "%'";
							} else {
								$query_part = $query_part . " AND " . $params_search_key . " = '" . $params_search_value . "'";
								$count_query_part = $count_query_part . " AND " . $params_search_key . " = '" . $params_search_value . "'";
							}

						}
					}
				}

				$this->query_part_for_export = $query_part;
				$query_part . "LIMIT $this->pagination_place, $this->pagination_display_limit";

				$this->students_final_search_results = $wpdb->get_results($query_part . "ORDER BY studentsTHING5 ASC LIMIT $this->pagination_place, $this->pagination_display_limit");

				$count_query = "select count(*) from $this->students_table" . $count_query_part;
    			$this->total_students_count = $wpdb->get_var( $count_query );

/*

				// This block of code and the associated if statements is if we want to do an inclusive search - a search that returns a larger amount of results accounting for all search terms.

				$this->students_search_THING1_results = $wpdb->get_results("SELECT * FROM $this->students_table WHERE studentsTHING1 = '" . $this->search_THING1 . "' LIMIT $this->pagination_place, $this->pagination_display_limit");
				$this->students_search_THING2_results = $wpdb->get_results("SELECT * FROM $this->students_table WHERE studentsTHING2 = '" . $this->search_THING2 . "' LIMIT $this->pagination_place, $this->pagination_display_limit");
				$this->students_search_THING3_results = $wpdb->get_results("SELECT * FROM $this->students_table WHERE studentsTHING3 = '" . $this->search_THING3 . "' LIMIT $this->pagination_place, $this->pagination_display_limit");
				$this->students_search_THING4_results = $wpdb->get_results("SELECT * FROM $this->students_table WHERE studentsTHING4 = '" . $this->search_THING4 . "' LIMIT $this->pagination_place, $this->pagination_display_limit");
				$this->students_search_THING5_results = $wpdb->get_results("SELECT * FROM $this->students_table WHERE studentsTHING5 = '" . $this->search_THING5 . "' LIMIT $this->pagination_place, $this->pagination_display_limit");
				$this->students_final_search_results = array();
				foreach ($this->students_search_THING1_results as $students) {
					if (!in_array($students, $this->students_final_search_results)) {
						array_push($this->students_final_search_results, $students);
					}
				}
				foreach ($this->students_search_THING2_results as $students) {
					if (!in_array($students, $this->students_final_search_results)) {
						array_push($this->students_final_search_results, $students);
					}
				}
				foreach ($this->students_search_THING3_results as $students) {
					if (!in_array($students, $this->students_final_search_results)) {
						array_push($this->students_final_search_results, $students);
					}
				}
				foreach ($this->students_search_THING4_results as $students) {
					if (!in_array($students, $this->students_final_search_results)) {
						array_push($this->students_final_search_results, $students);
					}
				}
				foreach ($this->students_search_THING5_results as $students) {
					if (!in_array($students, $this->students_final_search_results)) {
						array_push($this->students_final_search_results, $students);
					}
				}
*/
				$this->studentsdbresults = $this->students_final_search_results;

			} else {
				$this->studentsdbresults = $wpdb->get_results("SELECT * FROM $this->students_table");
				$count_query = "select count(*) from $this->students_table";
    			$this->total_students_count = $wpdb->get_var( $count_query );
			}
		}

		/**
		 * Creates opening HTML elements on the page. Can be used to intro stuff, or whatever needed really, just add HTML in that $string1 variable.
		 */
		public function create_opening_html()
		{
			global $wpdb;

			$string1 = '';


			$this->create_opening_html = $string1;
		}

		/**
		 * Creates the Search UI.
		 */
		public function create_search_ui()
		{

			global $wpdb;

			// All of this code until we get down to $string1 is for getting entries from individual tables where we record unique entries, and are usually the things we're needing to search for. These DB results will populate the Drop-Down/search menus that give us the options of things to search by. The things we can search by are stored in their own tables so we don't have to pull the entire main table every time this page loads.

			$students_THING1_table = $wpdb->prefix . 'teqcidb_students_THING1';
			$students_THING1_in_db = $wpdb->get_results("SELECT DISTINCT(studentsTHING1) as studentsTHING1 FROM $students_THING1_table ORDER BY LTRIM( studentsTHING1 ) ASC");
			// Build the default Select option.
			$THING1_html = '<option value="" default disabled selected>Select A THING1...</option>';
			// Loop through all results and build the actual Select options.
			foreach ($students_THING1_in_db as $THING1) {
				$THING1_html = $THING1_html . "<option>" . ucwords( strtolower( $THING1->studentsTHING1 ) ) . "</option>";
			}

			$students_THING2_table = $wpdb->prefix . 'teqcidb_students_THING2';
			$students_THING2_in_db = $wpdb->get_results("SELECT DISTINCT(studentsTHING2) as studentsTHING2 FROM $students_THING2_table ORDER BY LTRIM( studentsTHING2 ) ASC");
			// Build the default Select option.
			$THING2_html = '<option value="" default disabled selected>Select A THING2...</option>';
			// Loop through all results and build the actual Select options.
			foreach ($students_THING2_in_db as $THING2) {
				$THING2_html = $THING2_html . "<option>" . $THING2->studentsTHING2 . "</option>";
			}

			$students_THING3_table = $wpdb->prefix . 'teqcidb_students_THING3';
			$students_THING3_in_db = $wpdb->get_results("SELECT DISTINCT(studentsTHING3) as studentsTHING3 FROM $students_THING3_table ORDER BY LTRIM( studentsTHING3 ) ASC");
			// Build the default Select option.
			$THING3_html = '<option value="" default disabled selected>Select A THING3...</option>';
			// Loop through all results and build the actual Select options.
			foreach ($students_THING3_in_db as $THING3) {
				$THING3_html = $THING3_html . "<option>" . $THING3->studentsTHING3 . "</option>";
			}

			$students_THING4_table = $wpdb->prefix . 'teqcidb_students_THING4';
			$students_THING4_in_db = $wpdb->get_results("SELECT DISTINCT(studentsTHING4) as studentsTHING4 FROM $students_THING4_table ORDER BY LTRIM( studentsTHING4 ) ASC");
			// Build the default Select option.
			$THING4_html = '<option value="" default disabled selected>Select A THING4...</option>';
			// Loop through all results and build the actual Select options.
			foreach ($students_THING4_in_db as $THING4) {
				$THING4_html = $THING4_html . "<option>" . $THING4->studentsTHING4 . "</option>";
			}

			$students_THING5_table = $wpdb->prefix . 'teqcidb_students_THING5';
			$students_THING5_in_db = $wpdb->get_results("SELECT DISTINCT(studentsTHING5) as studentsTHING5 FROM $students_THING5_table ORDER BY LTRIM( studentsTHING5 ) ASC");
			// Build the default Select option.
			$THING5_html = '<option value="" default disabled selected>Select A Company THING5...</option>';
			// Loop through all results and build the actual Select options.
			foreach ( $students_THING5_in_db as $THING5) {
				$THING5_html = $THING5_html . "<option>" . $THING5->studentsTHING5 . "</option>";
			}

			// Now start building the actual HTML for the search area.
			$string1 = '<div class="teqcidb-display-search-ui-top-container">
							<p class="teqcidb-tab-intro-para">Select your search options below</p>
							<div class="teqcidb-display-search-ui-inner-container">
								<div class="teqcidb-display-search-ui-search-fields-container">
									<div class="teqcidb-form-section-fields-wrapper">
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">
											<label class="teqcidb-form-section-fields-label">THING1</label>
											<select id="teqcidb-search-THING1">' .	$THING1_html	. '</select>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">
											<label class="teqcidb-form-section-fields-label">State</label>
											<select id="teqcidb-search-states" THING5="search_state">
												<option value="" default disabled selected>Select A State...</option>
												<option value="AL">Alabama</option>
												<option value="AK">Alaska</option>
												<option value="AZ">Arizona</option>
												<option value="AR">Arkansas</option>
												<option value="CA">California</option>
												<option value="CO">Colorado</option>
												<option value="CT">Connecticut</option>
												<option value="DE">Delaware</option>
												<option value="DC">District of Columbia</option>
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
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">
											<label class="teqcidb-form-section-fields-label">THING2</label>
											<select id="teqcidb-search-THING2">' .	$THING2_html	. '</select>
										</div>
									</div>
									<div class="teqcidb-form-section-fields-wrapper">
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">
											<label class="teqcidb-form-section-fields-label">THING3</label>
											<select id="teqcidb-search-THING3">' .	$THING3_html	. '</select>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">
											<label class="teqcidb-form-section-fields-label">THING4</label>
											<select id="teqcidb-search-THING4">' .	$THING4_html	. '</select>
										</div>
										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">
											<label class="teqcidb-form-section-fields-label">THING5</label>
											<select id="teqcidb-search-THING5">' .	$THING5_html	. '</select>
										</div>
									</div>

								</div>
								<div class="teqcidb-display-search-ui-search-buttons-container">
									<button id="teqcidb-search-button" class="teqcidb-search-ui-buttons" data-pn="' . $this->pagination_place . '">Search</button>
									<button id="teqcidb-reset-search-fields" class="teqcidb-search-ui-buttons">Reset Search Fields</button>
								</div>
							</div>
						</div>
						';

			$search_results_students = $this->students_final_search_results;
			$this->create_search_ui_html = $string1 . $string2;
		}




















		/**
		 * Creates the HTML for each individual entry from the DB.
		 */
		public function create_individual_students_html()
		{
			global $wpdb;

			// Get every single class, period.
			$classes_table_name  = $wpdb->prefix . 'teqcidb_classes';
			$this->all_classes_array = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$classes_table_name}") );

			// Build the Email template HTML.
				$template_html = '';
				foreach( $this->studentsdbresults as $emailkey1 => $emailtemplate1 ){
					$template_html = $template_html . '<option data-uniqueemailid="' . $emailtemplate1->uniqueemailid . '" data-emailname="' . $emailtemplate1->emailname . '" data-emaildescription="' . $emailtemplate1->emaildescription . '" data-subjectline="' . $emailtemplate1->subjectline . '" data-fromemailaddress="' . $emailtemplate1->fromemailaddress . '" data-emailmessage="' . $emailtemplate1->emailmessage . '" data-testingemailaddress="' . $emailtemplate1->testingemailaddress . '">' . $emailtemplate1->emailname . '</option>';
				}

				// Now get ALL Students.
				$table_name              = $wpdb->prefix . 'teqcidb_students';
				$this->all_students_array = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table_name}") );

				$string1 = $string1 . '
					<div id="teqcidbplugin-display-options-container">
						<p class="teqcidbplugin-tab-intro-para">Here you can send emails to as many students as you\'d like using your previously-created Email Templates, or by writing a unique email below. </p>
						<div class="teqcidbplugin-form-section-wrapper teqcidbplugin-form-section-create-db-wrapper">
							<div class="teqcidbplugin-table-creator-wrapper">
								<div class="teqcidbplugin-form-section-fields-wrapper">
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Select an Email Template</label>
										<select class="teqcidbplugin-form-section-fields-input-text teqcidbplugin-form-email-template" id="teqcidbplugin-form-emailtemplate-1">
											<option selected default disabled>Select an Email Template...</option>
											<option>No Template</option>
											' . $template_html . '
										</select>
									</div>
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Email Address for Testing</label>
										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-testingemailaddress" type="text" placeholder="Enter an Email address for testing" />
									</div>
								</div>
								<div class="teqcidbplugin-form-section-fields-wrapper">
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper teqcidbplugin-form-section-fields-indiv-wrapper-for-wide-select2">
										<label class="teqcidbplugin-form-section-fields-label">Select the Students to Email</label>
										<select style="width:100%;" multiple="multiple" class="teqcidbplugin-form-section-fields-input-text teqcidbplugin-form-studentname-student" id="teqcidbplugin-form-studentname-1">
												<option disabled>Select a Student...</option>';	

												// Building drop-down of all existing Classes.
												$string8 = '';
												foreach ( $this->all_students_array as $student ) {
													$string8 = $string8 . '<option value="' . $student->firstname . ' ' . $student->lastname . '" data-uniquestudentid="' . $student->uniquestudentid . '"  data-altcontactemail="' . $student->altcontactemail . '" data-email="' . $student->email . '" data-wpuserid="' . $student->wpuserid . '" data-firstname="' . $student->firstname . '" data-lastname="' . $student->lastname . '" data-phonecell="' . $student->phonecell . '" data-contactstreetaddress="' . $student->contactstreetaddress . '" data-contactcity="' . $student->contactcity . '" data-contactstate="' . $student->contactstate . '" data-contactzip="' . $student->contactzip . '" data-company="' . $student->company . '" data-expirationdate="' . $student->expirationdate . '">' . ucfirst( $student->firstname ) . ' ' . ucfirst( $student->lastname ) . '</option>';
												}

												$string1 = $string1 . $string8 . 
										'</select>
										<div class="teqcidbplugin-email-student-selection-buttons-holder">
											<div class="teqcidbplugin-email-student-selection-buttons-inner-holder">
												<label>Email students by Expiration Date</label>
												<input id="teqcidbplugin-email-student-selection-buttons-expirations-date-field" type="date" />
												<button id="teqcidbplugin-add-classmembers-to-email-expir-date">Add Students</button>
											</div>
											<div class="teqcidbplugin-email-student-selection-buttons-inner-holder">
												<label>Email students by Class</label>
												<select class="teqcidbplugin-form-section-fields-input-select teqcidbplugin-form-classname-class select2-hidden-accessible" id="teqcidbplugin-form-all-classes-for-email" tabindex="-1" aria-hidden="true">
															<option default="" disabled="" value="Choose a Class...">Choose a Class...</option>';

														// Building drop-down of all existing Classes.
														$string9 = '';
														foreach ( $this->all_classes_array as $class ) {

															$string9 = $string9 . '<option value="' . $class->classname . '" data-classtype="' . $class->classtype . '" data-classstartdate="' . $class->classstartdate . '" data-classcost="' . $class->classcost . '" data-uniqueclassid="' . $class->uniqueclassid . '" data-classtype="' . $class->classtype . '"    >' . ucfirst( $class->classname ) . '</option>';
														}

													$string1 = $string1 . $string9 . '</select>
												<button id="teqcidbplugin-add-classmembers-to-email-class">Add Students</button>
											</div>
											<div class="teqcidbplugin-email-student-selection-buttons-inner-holder">
												<label>Also email student\'s<br/>alternate contact</label>
												<input id="teqcidbplugin-email-student-selection-buttons-alt-contact-field" type="checkbox" />
											</div>
										</div>
									</div>
								</div>
								<div class="teqcidbplugin-form-section-fields-wrapper">
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper teqcidbplugin-form-section-fields-indiv-wrapper-for-wide-select2">
										<label class="teqcidbplugin-form-section-fields-label">Email Message (leave blank if using an email template from the drop-down above) </label>
										<textarea style="width: 90%;" placeholder="Hi [firstname],

We wanted to let you know that your Stormwater Training Certification will expire on [studentexpiredate]. To remain certified, please visit  [link]https://training.thompsonengineering.com/[endlink] to view a list of all upcoming classes and register for a refresher training course.

Thanks!" id="teqcidbplugin-email-actualmessage" class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-form-section-fields-input-text-emailmessage-actual" type="text" placeholder="The actual message you want to provide to recipients"></textarea>
									</div>
								</div>
								<div class="teqcidbplugin-form-section-fields-wrapper">
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper teqcidbplugin-form-section-fields-indiv-wrapper-for-wide-select2">
										<label class="teqcidbplugin-form-section-fields-label">Subject Line (leave blank if using an email template from the drop-down above)</label>
										<input style="width: 90%;" class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-subjectline" type="text" placeholder="Email Subject Line" />
									</div>
								</div>
								<div class="teqcidbplugin-form-section-create-extra-columns-wrapper">
									<div>
										<button id="teqcidbplugin-send-this-email-button">Send This Email</button>
										<button id="teqcidbplugin-send-test-email-from-bulk-button">Send a Test Email</button>
									</div>
								</div>
								<div class="teqcidbplugin-spinner"></div>
			 					<div class="teqcidbplugin-response-div-actual-container">
			 						<p style="display: none;" class="hidden-total-requested"></p>
			 						<p style="display: none;" class="hidden-total-accomplished">0</p>
			 						<p class="teqcidbplugin-response-div-p"></p>
			 					</div>
							</div>
						</div>
					</div>';
			
			$this->create_individual_students_html = $string1;
		}








		/**
		 * Creates the Pagination HTML at the bottom.
		 */
		public function create_pagination_html()
		{
			global $wpdb;

			// Builds the Drop-Down for choosing a page to jump to.
			$pagination_option_html = '';
			$loop_control_whole_numbers = floor( $this->total_students_count / $this->pagination_display_limit );
			if ( $this->total_students_count < $this->pagination_display_limit ) {
				$pagination_option_html = '<option value="1">Page 1</option>';
			} else {
				for ($i=0; $i < $loop_control_whole_numbers; $i++) { 
					$pagination_option_html = $pagination_option_html  . '<option value="' . ( $i + 1 ) . '">Page ' . ( $i + 1 ) . '</option>';
				}
			}

			// Actual output HTML.
			$string1 = '';

			$this->create_pagination_html = $string1;
		}

		/**
		 * Creates closing HTML elements on the page. Just add HTML in that $string1 variable.
		 */
		public function create_closing_html()
		{
			global $wpdb;

			$string1 = '';


			$this->create_closing_html = $string1;
		}



		/**
		 * Stitches together and outputs all HTML elements on the page.
		 */
		public function stitch_ui_html()
		{

			$this->final_echoed_html = $this->create_opening_html . $this->create_search_ui_html . $this->create_individual_students_html . $this->create_pagination_html . $this->create_closing_html;
		}







	}
endif;
