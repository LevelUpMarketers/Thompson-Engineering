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

			// Get every single email, period.
			$emails_table_name  = $wpdb->prefix . 'teqcidb_emails';
			$this->all_classes_array = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$emails_table_name}") );

			$string1 = '';
			foreach ( $this->studentsdbresults as $key => $value ) {

				// Reset the Historical HTML;
				$historical_training_html = '';

				// Get the historical training info for each student.
				$historical_training_data = $wpdb->get_results( "SELECT * FROM $this->student_history_table WHERE uniquestudentid LIKE '%" . $value->uniquestudentid . "%'" );

				// Now build the historical data html.
				$no_historical_data = '';
				if ( 0 < sizeof( $historical_training_data ) ) {

					foreach( $historical_training_data as $training_data ){

						$registered_selected_yes = '';
						$registered_selected_no = '';
						$registered_selected_pending = '';
						if ( 'no' === $training_data->registered ) {
							$registered_selected_no = 'selected';
						}

						if ( 'yes' === $training_data->registered ) {
							$registered_selected_yes = 'selected';
						}

						if ( 'pending' === $training_data->registered ) {
							$registered_selected_pending = 'selected';
						}

						$attended_selected_yes = '';
						$attended_selected_no = '';
						$attended_selected_upcoming = '';
						$attended_selected_null = '';
						if ( 'no' === $training_data->attended ) {
							$attended_selected_no = 'selected';
						}

						if ( 'yes' === $training_data->attended ) {
							$attended_selected_yes = 'selected';
						}

						if ( 'upcoming' === $training_data->attended ) {
							$attended_selected_upcoming = 'selected';
						}

						if ( ( null === $training_data->attended ) || ( 'null' === $training_data->attended ) ) {
							$attended_selected_null = 'selected';
						}

						$outcome_selected_upcoming   = '';
						$outcome_selected_passed     = '';
						$outcome_selected_failed     = '';
						$outcome_selected_deferred   = '';
						if ( 'upcoming' === $training_data->outcome ) {
							$outcome_selected_upcoming = 'selected';
						}

						if ( 'passed' === $training_data->outcome ) {
							$outcome_selected_passed = 'selected';
						}

						if ( 'failed' === $training_data->outcome ) {
							$outcome_selected_failed = 'selected';
						} 

						if ( 'deferred' === $training_data->outcome ) {
							$outcome_selected_deferred = 'selected';
						} 

						$payment_selected_pending   = '';
						$payment_selected_full      = '';
						$payment_selected_none      = '';
						$payment_selected_waived    = '';
						if ( 'pending' === $training_data->paymentstatus ) {
							$payment_selected_pending = 'selected';
						}
						if ( 'paidinfull' === $training_data->paymentstatus ) {
							$payment_selected_full = 'selected';
						}

						if ( 'nopaymentmade' === $training_data->paymentstatus ) {
							$payment_selected_none = 'selected';
						} 

						if ( 'paymentwaived' === $training_data->paymentstatus ) {
							$payment_selected_waived = 'selected';
						} 

						// Let's get the class cost and type.
						$class_info = $wpdb->get_row( "SELECT * FROM $emails_table_name WHERE uniqueclassid LIKE '%" . $training_data->uniqueclassid . "%'" );

						$class_selected_initial   = '';
						$class_selected_refresher = '';
						$class_selected_hybrid    = '';
						$class_selected_other     = '';
						if ( 'initial' === $class_info->classtype ) {
							$class_selected_initial = 'selected';
						}

						if ( 'refresher' === $class_info->classtype ) {
							$class_selected_refresher = 'selected';
						} 

						if ( 'hybrid' === $class_info->classtype ) {
							$class_selected_hybrid = 'selected';
						} 

						if ( 'other' === $class_info->classtype ) {
							$class_selected_other = 'selected';
						} 

						$historical_training_html = $historical_training_html . '
										<div class="teqcidbplugin-form-section-fields-class-wrapper teqcidbplugin-form-section-fields-class-edit-history-wrapper">
											<div class="teqcidbplugin-form-section-remove-student-historical-info-div">
												<img class="teqcidbplugin-form-section-placeholder-image-small teqcidbplugin-form-section-remove-student-historical-info" src="' . TEQCIDB_ROOT_IMG_URL . 'close.png">
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Name</label>
													<select class="teqcidbplugin-form-section-fields-input-select teqcidbplugin-form-classname-class select2-hidden-accessible" id="teqcidbplugin-form-classname-1" tabindex="-1" aria-hidden="true">
															<option default="" disabled="" value="Choose a Class...">Choose a Class...</option>';

														// Building drop-down of all existing Classes.
														$string8 = '';
														foreach ( $this->all_classes_array as $class ) {

															$selected_variable = '';
															if ( $class->uniqueclassid === $training_data->uniqueclassid ) {
																$selected_variable = 'selected';
															}

															$string8 = $string8 . '<option ' . $selected_variable . ' value="' . $class->classname . '" data-classtype="' . $class->classtype . '" data-classstartdate="' . $class->classstartdate . '" data-classcost="' . $class->classcost . '" data-uniqueclassid="' . $class->uniqueclassid . '" data-classtype="' . $class->classtype . '"    >' . ucfirst( $class->classname ) . '</option>';
														}

													$historical_training_html = $historical_training_html . $string8 . '</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Date</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classstartdate-1" data-dbname="genre" type="text" placeholder="Date of Class/Online Completion Date" value="' . $class_info->classstartdate . '">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Type</label>
													<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-classtype-1" data-dbname="isbn10" type="text" placeholder="Student\'s State">
														<option value="default"   default="" disabled="">Make a Selection...</option>
														<option value="initial" ' . $class_selected_initial . ' >Initial</option>
														<option value="refresher" ' . $class_selected_refresher . ' >Refresher</option>
														<option value="hybrid" ' . $class_selected_hybrid . ' >Hybrid</option>
														<option value="other" ' . $class_selected_other . ' >Other</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Admin Approved?</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-registeredforclass-1">
														<option default="" disabled="" >Make a Selection...</option>
														<option ' . $registered_selected_pending . ' value="pending">Pending Approval</option>
														<option ' . $registered_selected_yes . ' value="yes">Yes</option>
														<option ' . $registered_selected_no . ' value="no">No</option>
													</select>
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Attended This Class?</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-attendedthisclass-1">
														<option ' . $attended_selected_null . ' default="" disabled="" >Make a Selection...</option>
														<option ' . $attended_selected_upcoming . ' value="upcoming">Class is Upcoming</option>
														<option ' . $attended_selected_yes . ' value="yes">Yes</option>
														<option ' . $attended_selected_no . ' value="no">No</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Outcome</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-classoutcome-1">
														<option default="" disabled="" >Make a Selection...</option>
														<option ' . $outcome_selected_upcoming . ' value="upcoming">Class is Upcoming</option>
														<option ' . $outcome_selected_passed . ' value="passed">Passed</option>
														<option ' . $outcome_selected_failed . ' value="failed">Failed</option>
														<option ' . $outcome_selected_deferred . ' value="deferred">Deferred/Delayed</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Cost</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-classcost-1" type="text" value="' . $class_info->classcost . '">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Payment Status</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-paymentstatus-1">
														<option default="" disabled="" >Make a Selection...</option>
														<option ' . $payment_selected_pending . ' value="pending">Payment Pending</option>
														<option ' . $payment_selected_full . ' value="paidinfull">Paid in Full</option>
														<option ' . $payment_selected_none . ' value="nopaymentmade">No Payment Made</option>
														<option ' . $payment_selected_waived . ' value="paymentwaived">Payment Waived</option>
													</select>
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Amount Paid</label>
													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-amountactuallypaid-1" type="text" value="' . $training_data->amountpaid . '">
												</div>
											</div>
										</div>';
					}
				} else {
					$no_historical_data = 'This Student has no historical training information';
					$no_history = "true";
					$historical_training_html = '
										<div class="teqcidbplugin-form-section-fields-class-wrapper teqcidbplugin-form-section-fields-class-wrapper-for-replication" style="display:none">
											<div class="teqcidbplugin-form-section-remove-student-historical-info-div">
												<img class="teqcidbplugin-form-section-placeholder-image-small teqcidbplugin-form-section-remove-student-historical-info" src="' . TEQCIDB_ROOT_IMG_URL . 'close.png">
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Name</label>
													<select class="teqcidbplugin-form-section-fields-input-select teqcidbplugin-form-classname-class select2-hidden-accessible" id="teqcidbplugin-form-classname-1" tabindex="-1" aria-hidden="true">
															<option default="" disabled="" value="Choose a Class...">Choose a Class...</option>';

														// Building drop-down of all existing Classes.
														$string8 = '';
														foreach ( $this->all_classes_array as $class ) {
															$string8 = $string8 . '<option value="' . $class->classname . '" data-classtype="' . $class->classtype . '" data-classstartdate="' . $class->classstartdate . '" data-classcost="' . $class->classcost . '" data-uniqueclassid="' . $class->uniqueclassid . '" data-classtype="' . $class->classtype . '"    >' . ucfirst( $class->classname ) . '</option>';
														}

													$historical_training_html = $historical_training_html . $string8 . '</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Date</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classstartdate-1" data-dbname="genre" type="text" placeholder="Date of Class/Online Completion Date">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Type</label>
													<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-classtype-1" data-dbname="isbn10" type="text" placeholder="Student\'s State">
														<option value="default" default="" disabled="">Make a Selection...</option>
														<option value="initial">Initial</option>
														<option value="refresher">Refresher</option>
														<option value="hybrid">Hybrid</option>
														<option value="other">Other</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Admin Approved?</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-registeredforclass-1">
														<option default="" disabled="">Make a Selection...</option>
														<option value="pending">Pending Approval</option>
														<option value="yes">Yes</option>
														<option value="no">No</option>
													</select>
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Attended This Class?</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-attendedthisclass-1">
														<option default="" disabled="" >Make a Selection...</option>
														<option value="upcoming">Class is Upcoming</option>
														<option value="yes">Yes</option>
														<option value="no">No</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Outcome</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-classoutcome-1">
														<option default="" disabled="" >Make a Selection...</option>
														<option value="upcoming">Class is Upcoming</option>
														<option value="passed">Passed</option>
														<option value="failed">Failed</option>
														<option value="deferred">Deferred/Delayed</option>
													</select>
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Class Cost</label>
													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-classcost-1" type="text" placeholder="">
												</div>
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Payment Status</label>
													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-paymentstatus-1">
														<option default="" disabled="" >Make a Selection...</option>
														<option value="pending">Payment Pending</option>
														<option value="paidinfull">Paid in Full</option>
														<option value="nopaymentmade">No Payment Made</option>
														<option value="paymentwaived">Payment Waived</option>
													</select>
												</div>
											</div>
											<div class="teqcidbplugin-form-section-fields-wrapper">
												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
													<label class="teqcidbplugin-form-section-fields-label">Amount Paid</label>
													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-amountactuallypaid-1" type="text" placeholder="">
												</div>
											</div>
										</div>';
				}

				// Build the state selection drop-down deal.
				switch ( $value->contactstate ) {
					case 'AL':
						$selected1 = 'selected';
						break;
					case 'AK':
						$selected2 = 'selected';
						break;
					case 'AZ':
						$selected3 = 'selected';
						break;
					case 'AR':
						$selected4 = 'selected';
						break;
					case 'CA':
						$selected5 = 'selected';
						break;
					case 'CO':
						$selected6 = 'selected';
						break;
					case 'CT':
						$selected7 = 'selected';
						break;
					case 'DE':
						$selected8 = 'selected';
						break;
					case 'DC':
						$selected9 = 'selected';
						break;
					case 'FL':
						$selected10 = 'selected';
						break;
					case 'GA':
						$selected11 = 'selected';
						break;
					case 'HI':
						$selected12 = 'selected';
						break;
					case 'ID':
						$selected13 = 'selected';
						break;
					case 'IL':
						$selected14 = 'selected';
						break;
					case 'IN':
						$selected15 = 'selected';
						break;
					case 'IA':
						$selected16 = 'selected';
						break;
					case 'KS':
						$selected17 = 'selected';
						break;
					case 'KY':
						$selected18 = 'selected';
						break;
					case 'LA':
						$selected19 = 'selected';
						break;
					case 'ME':
						$selected20 = 'selected';
						break;
					case 'MD':
						$selected21 = 'selected';
						break;
					case 'MA':
						$selected22 = 'selected';
						break;
					case 'MI':
						$selected23 = 'selected';
						break;
					case 'MN':
						$selected24 = 'selected';
						break;
					case 'MS':
						$selected25 = 'selected';
						break;
					case 'MO':
						$selected26 = 'selected';
						break;
					case 'MT':
						$selected27 = 'selected';
						break;
					case 'NE':
						$selected28 = 'selected';
						break;
					case 'NV':
						$selected29 = 'selected';
						break;
					case 'NH':
						$selected30 = 'selected';
						break;
					case 'NJ':
						$selected31 = 'selected';
						break;
					case 'NM':
						$selected32 = 'selected';
						break;
					case 'NY':
						$selected33 = 'selected';
						break;
					case 'NC':
						$selected34 = 'selected';
						break;
					case 'ND':
						$selected35 = 'selected';
						break;
					case 'OH':
						$selected36 = 'selected';
						break;
					case 'OK':
						$selected37 = 'selected';
						break;
					case 'OR':
						$selected38 = 'selected';
						break;
					case 'PA':
						$selected39 = 'selected';
						break;
					case 'RI':
						$selected40 = 'selected';
						break;
					case 'SC':
						$selected41 = 'selected';
						break;
					case 'SD':
						$selected42 = 'selected';
						break;
					case 'TN':
						$selected43 = 'selected';
						break;
					case 'TX':
						$selected44 = 'selected';
						break;
					case 'UT':
						$selected45 = 'selected';
						break;
					case 'VT':
						$selected46 = 'selected';
						break;
					case 'VA':
						$selected47 = 'selected';
						break;
					case 'WA':
						$selected48 = 'selected';
						break;
					case 'WV':
						$selected49 = 'selected';
						break;
					case 'WI':
						$selected50 = 'selected';
						break;
					case 'WY':
						$selected51 = 'selected';
						break;
					
					default:
						// code...
						break;
				}

				$association_array = explode( ',', $value->associations );
				$aapa = '';
				$arba = '';
				$agc  = '';
				$abc  = '';
				$auca = '';
				$none = '';
				foreach ( $association_array as $assoc_key => $assoc_value) {
					if ( 'aapa' === $assoc_value ) { 
						$aapa = 'checked';
					}

					if ( 'arba' === $assoc_value ) { 
						$arba = 'checked';
					}

					if ( 'agc' === $assoc_value ) { 
						$agc = 'checked';
					}

					if ( 'abc' === $assoc_value ) { 
						$abc = 'checked';
					}

					if ( 'auca' === $assoc_value ) { 
						$auca = 'checked';
					}

					if ( 'none' === $assoc_value ) { 
						$none = 'checked';
					}
				}

				$string1 = $string1 . '
					<div class="teqcidb-students-update-container teqcidb-all-students" >
						
						<button class="accordion teqcidb-students-update-container-accordion-heading">
							' .  $value->emailname . '
						</button>
						<div class="teqcidb-students-update-info-container" data-open="false">
							<div class="teqcidbplugin-form-wrapper">
								<div class="teqcidbplugin-form-section-wrapper" id="teqcidbplugin-form-section-wrapper-' . $value->ID . '">
									<div class="teqcidbplugin-form-section-fields-wrapper">
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Name</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-emailname-' . $value->ID . '" type="text" placeholder="Enter a name for this type of email" value="' . $value->emailname . '" />
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Description</label>
											<textarea id="teqcidbplugin-email-emaildescription-' . $value->ID . '" class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" type="text" placeholder="A Description of what this type of email is used for">' . $value->emaildescription . '</textarea>
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Subject Line</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-subjectline-' . $value->ID . '" type="text" placeholder="Enter the Subject Line for this email" value="' . $value->subjectline . '" />
										</div>
									</div>
									<div class="teqcidbplugin-form-section-fields-wrapper">
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email "From" Address</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-fromaddress-' . $value->ID . '" type="text" placeholder="The email address recipients will see this being sent from" value="' . $value->fromemailaddress . '" />
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Address for Testing</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-email-testingaddress-' . $value->ID . '" type="text" placeholder="Send a test email to this email address" value="' . $value->testingemailaddress . '" />
										</div>
									</div>
									<div class="teqcidbplugin-form-section-fields-wrapper">
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">Email Message</label>
											<label style="margin-top: 20px; margin-bottom: 5px;" class="teqcidbplugin-form-section-fields-label">To use personalized information in the emails, create your template using these fields: </label>
											<label style="font-weight:bold;margin-bottom: 5px;" class="teqcidbplugin-form-section-fields-label">[firstname] [lastname] [studentemail] [studentphone] [studentstreetaddress] [studentcity] [studentstate] [studentzip] [studentcompany] [studentexpiredate]</label>
											<label style="margin-bottom: 20px; margin-top:5px;" class="teqcidbplugin-form-section-fields-label">For example, if you typed "Hello [firstname] from [studentcity], [studentstate]!" in the text area below, the text the student would receive in their actual email would read: "Hello John from Denver, CO!"</label>
											<label style="margin-top: 20px; margin-bottom: 40px;" class="teqcidbplugin-form-section-fields-label">To use links in your emails, place <span style="font-weight:bold;">[link]</span> right before the URL, and place <span style="font-weight:bold;">[endlink]</span> directly after the URL. For example, if I wanted to place a link to Google.com in my email, I would type this in the text area below: "[link]https://www.google.com[endlink]" </label>
											<textarea id="teqcidbplugin-email-actualmessage-' . $value->ID . '" class="teqcidbplugin-form-section-fields-input  teqcidbplugin-form-section-fields-input-text-emailmessage-actual" type="text" placeholder="The actual message you want to provide to recipients">' . $value->emailmessage . '</textarea>
										</div>
									</div>
									<div class="teqcidbplugin-form-section-create-extra-columns-wrapper">
										<div>
											<button data-uniqueemailid="' . $value->uniqueemailid . '" data-id="' . $value->ID . '"  class="teqcidbplugin-save-this-email-edits-button">Save Edits to This Email</button>
											<button data-uniqueemailid="' . $value->uniqueemailid . '" data-id="' . $value->ID . '" class="teqcidbplugin-send-test-email-edits-button">Send Test Email</button>
										</div>
									</div>
									<div class="teqcidbplugin-spinner teqcidbplugin-spinner-' . $value->ID . '"></div>
				 					<div class="teqcidbplugin-response-div-actual-container">
				 						<p class="teqcidbplugin-response-div-p"></p>
				 					</div>
								</div>
							</div>
						</div>						
				</div>';
			}

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
			$string1 = '
				<div class="teqcidb-pagination-top-container">
					<div class="teqcidb-pagination-inner-container">

						<div id="teqcidb-pagecontrols">
							<div class="teqcidb-prevnextbuttons" id="teqcidb-previouspage" data-currentpn="' . $this->pagination_place . '" data-pagelimit="' . $this->pagination_display_limit . '">Previous</div>
							<div>
								<select class="teqcidb-prevnextbuttons" id="teqcidb-pageselect" data-currentpn="' . $this->pagination_place . '" data-pagelimit="' . $this->pagination_display_limit . '">
									' . $pagination_option_html . '
								</select>
							</div>
							<div class="teqcidb-prevnextbuttons" id="teqcidb-nextpage" data-currentpn="' . $this->pagination_place . '" data-pagelimit="' . $this->pagination_display_limit . '">Next</div>
						</div>
					</div>
				</div>
			';

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
