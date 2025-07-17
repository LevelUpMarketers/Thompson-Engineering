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

if ( ! class_exists( 'TEQcidbPlugin_settings3_Form', false ) ) :

	/**
	 * TEQcidbPlugin_Admin_Menu Class.
	 */
	class TEQcidbPlugin_settings3_Form {

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

			global $wpdb;

			// Get every single student, period.
			$table_name              = $wpdb->prefix . 'teqcidb_students';
			$this->all_students_array = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table_name}") );

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
			$this->students_table = $wpdb->prefix . 'teqcidb_students';
			$this->student_history_table = $wpdb->prefix . 'teqcidb_studenthistory';
			$this->classes_table = $wpdb->prefix . 'teqcidb_classes';

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
    			$this->total_classes_count = $wpdb->get_var( $count_query );

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
				$this->classesdbresults = $this->students_final_search_results;

			} else {
				$this->classesdbresults = $wpdb->get_results(
				    "SELECT * FROM $this->classes_table 
				     ORDER BY classname 
				     LIMIT $this->pagination_place, $this->pagination_display_limit"
				);
				$count_query = "select count(*) from $this->classes_table";
    			$this->total_classes_count = $wpdb->get_var( $count_query );
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
			$class_table_name  = $wpdb->prefix . 'teqcidb_classes';
			$this->all_classes_array = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$class_table_name}") );

			$string1 = '';
			foreach ( $this->classesdbresults as $key => $value ) {

				
				$selected1 = '';
				$selected2 = '';
				$selected3 = '';
				$selected4 = '';
				$selected5 = '';
				$selected6 = '';
				$selected7 = '';
				$selected8 = '';
				$selected9 = '';
				$selected10 = '';
				$selected11 = '';
				$selected12 = '';
				$selected13 = '';
				$selected14 = '';
				$selected15 = '';
				$selected16 = '';
				$selected17 = '';
				$selected18 = '';
				$selected19 = '';
				$selected20 = '';
				$selected21 = '';
				$selected22 = '';
				$selected23 = '';
				$selected24 = '';
				$selected25 = '';
				$selected26 = '';
				$selected27 = '';
				$selected28 = '';
				$selected29 = '';
				$selected30 = '';
				$selected31 = '';
				$selected32 = '';
				$selected33 = '';
				$selected34 = '';
				$selected35 = '';
				$selected36 = '';
				$selected37 = '';
				$selected38 = '';
				$selected39 = '';
				$selected40 = '';
				$selected41 = '';
				$selected42 = '';
				$selected43 = '';
				$selected44 = '';
				$selected45 = '';
				$selected46 = '';
				$selected47 = '';
				$selected48 = '';
				$selected49 = '';
				$selected50 = '';
				$selected51 = '';

				// Build the state selection drop-down deal.
				switch ( $value->classstate ) {
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

				$format_selected_inperson = '';
				$format_selected_online = '';
				$format_selected_hybrid = '';
				$format_selected_other = '';
				if ( 'inperson' === $value->classformat ) {
					$format_selected_inperson = 'selected';
				}

				if ( 'online' === $value->classformat ) {
					$format_selected_online = 'selected';
				}

				if ( 'hybrid' === $value->classformat ) {
					$format_selected_hybrid = 'selected';
				}

				if ( 'other' === $value->classformat ) {
					$format_selected_other = 'selected';
				}

				$other_selected_initial = '';
				$other_selected_refresher = '';
				$other_selected_hybrid = '';
				$other_selected_other = '';
				if ( 'initial' === $value->classtype ) {
					$other_selected_initial = 'selected';
				}

				if ( 'refresher' === $value->classtype ) {
					$other_selected_refresher = 'selected';
				}

				if ( 'hybrid' === $value->classtype ) {
					$other_selected_hybrid = 'selected';
				}

				if ( 'other' === $value->classtype ) {
					$other_selected_other = 'selected';
				}

				$onlinehiderinitial = 'style="opacity: 0.3; pointer-events: none;"';
				if ( ( 'online' === $value->classformat ) && ( 'initial' === $value->classtype ) ) {
					$onlinehiderinitial = '';
				}

				$onlinehiderrefresher = 'style="opacity: 0.3; pointer-events: none;"';
				if ( ( 'online' === $value->classformat ) && ( 'refresher' === $value->classtype ) ) {
					$onlinehiderrefresher = '';
				}

				$inpersonhiderinitialcert = 'style="opacity: 0.3; pointer-events: none;"';
				if ( ( 'inperson' === $value->classformat ) && ( 'initial' === $value->classtype ) ) {
					$inpersonhiderinitialcert = '';
				}

				$inpersonhiderrefreshercert = 'style="opacity: 0.3; pointer-events: none;"';
				if ( ( 'inperson' === $value->classformat ) && ( 'refresher' === $value->classtype ) ) {
					$inpersonhiderrefreshercert = '';
				}

				$onlinehiderinitialcert = 'style="opacity: 0.3; pointer-events: none;"';
				if ( ( 'online' === $value->classformat ) && ( 'initial' === $value->classtype ) ) {
					$onlinehiderinitialcert = '';
				}

				$onlinehiderrefreshercert = 'style="opacity: 0.3; pointer-events: none;"';
				if ( ( 'online' === $value->classformat ) && ( 'refresher' === $value->classtype ) ) {
					$onlinehiderrefreshercert = '';
				}

			

				$string1 = $string1 . '
					<div class="teqcidb-students-update-container teqcidb-all-students" >
						
						<button class="accordion teqcidb-students-update-container-accordion-heading">
							' .  $value->classname . '
						</button>
						<div class="teqcidb-students-update-info-container" data-open="false">
							<div class="teqcidbplugin-form-wrapper" id="teqcidbplugin-form-section-wrapper-' . $value->ID . '">
								<div ' . $inpersonhiderinitialcert . ' class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-all-initial-inperson" id="teqcidbplugin-generate-class-forms-button-certification-initial-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-instructors="' . $value->instructors . '" >
									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
									<p data-nohistory="' . $no_history . '">Generate Completion Certificate (initial in-person)</p>
								</div>
								<div ' . $inpersonhiderrefreshercert . ' class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-all-refresher-inperson" id="teqcidbplugin-generate-class-forms-button-certification-initial-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-instructors="' . $value->instructors . '" >
									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
									<p data-nohistory="' . $no_history . '">Generate Completion Certificate (refresher in-person)</p>
								</div>
								<div class="teqcidbplugin-walletcard-dates-holder">
									<div ' . $onlinehiderinitialcert . ' class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-all-initial-online" id="teqcidbplugin-generate-class-forms-button-certification-initial-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-instructors="' . $value->instructors . '" >
										<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
										<p data-nohistory="' . $no_history . '">Generate Completion Certificate (initial online)</p>
									</div>
									<div ' . $onlinehiderinitialcert . '>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">From Date</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-completecertinitialonline-fromdate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-fromdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">To Date</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-completecertinitialonline-todate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-todate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
										</div>
									</div>
								</div>
								<div class="teqcidbplugin-walletcard-dates-holder">
									<div ' . $onlinehiderrefreshercert . ' class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-all-refresher-online" id="teqcidbplugin-generate-class-forms-button-certification-initial-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-instructors="' . $value->instructors . '" >
										<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
										<p data-nohistory="' . $no_history . '">Generate Completion Certificate (refresher online)</p>
									</div>
									<div ' . $onlinehiderrefreshercert . '>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">From Date</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-completecertrefresheronline-fromdate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-fromdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">To Date</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-completecertrefresheronline-todate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-todate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
										</div>
									</div>
								</div>
								<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-online-initial-spreadsheet" id="teqcidbplugin-generate-class-forms-button-online-initial-spreadsheet-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-classname="' . $value->classname . '" data-classstartdate="' . $value->classstartdate . '" data-classtype="' . $value->classtype . '" data-classformat="' . $value->classformat . '" data-classcost="' . $value->classcost . '" data-classstreetaddress="' . $value->classstreetaddress . '" data-classcity="' . $value->classcity . '" data-classstate="' . $value->classstate . '" data-classzip="' . $value->classzip . '">
									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
									<p data-nohistory="' . $no_history . '">Generate Class Roster Spreadsheet</p>
								</div>
								<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-signin" id="teqcidbplugin-generate-class-forms-button-signin-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-classname="' . $value->classname . '" data-classstartdate="' . $value->classstartdate . '" data-classtype="' . $value->classtype . '" data-classformat="' . $value->classformat . '" data-classcost="' . $value->classcost . '" data-classstreetaddress="' . $value->classstreetaddress . '" data-classcity="' . $value->classcity . '" data-classstate="' . $value->classstate . '" data-classzip="' . $value->classzip . '">
									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
									<p data-nohistory="' . $no_history . '">Generate Sign-In Sheet</p>
								</div>
								<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-namebadge" id="teqcidbplugin-generate-class-forms-button-namebadge-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-classname="' . $value->classname . '" data-classstartdate="' . $value->classstartdate . '" data-classtype="' . $value->classtype . '" data-classformat="' . $value->classformat . '" data-classcost="' . $value->classcost . '" data-classstreetaddress="' . $value->classstreetaddress . '" data-classcity="' . $value->classcity . '" data-classstate="' . $value->classstate . '" data-classzip="' . $value->classzip . '">
									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
									<p data-nohistory="' . $no_history . '">Generate Name Badges</p>
								</div>
								<div class="teqcidbplugin-walletcard-dates-holder">
									<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-all-mailing-labels" id="teqcidbplugin-generate-class-forms-button-all-mailing-labels-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-classname="' . $value->classname . '" data-classstartdate="' . $value->classstartdate . '" data-classtype="' . $value->classtype . '" data-classformat="' . $value->classformat . '" data-classcost="' . $value->classcost . '" data-classstreetaddress="' . $value->classstreetaddress . '" data-classcity="' . $value->classcity . '" data-classstate="' . $value->classstate . '" data-classzip="' . $value->classzip . '">
										<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
										<p data-nohistory="' . $no_history . '">Generate Mailing<br/>Labels</p>
									</div>
									<div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">From Date</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-walletcards-fromdate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-fromdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">To Date</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-walletcards-todate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-todate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
										</div>
									</div>
								</div>
								<div class="teqcidbplugin-walletcard-dates-holder">
									<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-all-walletcards-labels" id="teqcidbplugin-generate-class-forms-button-all-walletcards-labels-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-classname="' . $value->classname . '" data-classstartdate="' . $value->classstartdate . '" data-classtype="' . $value->classtype . '" data-classformat="' . $value->classformat . '" data-classcost="' . $value->classcost . '" data-classstreetaddress="' . $value->classstreetaddress . '" data-classcity="' . $value->classcity . '" data-classstate="' . $value->classstate . '" data-classzip="' . $value->classzip . '">
										<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
										<p data-nohistory="' . $no_history . '">Generate Wallet Cards<br/>(Printable Template)</p>
									</div>
									<div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">From Date</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-walletcards-fromdate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-fromdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
										</div>
										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
											<label class="teqcidbplugin-form-section-fields-label">To Date</label>
											<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-walletcards-todate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-todate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
										</div>
									</div>
								</div>

<div class="teqcidbplugin-walletcard-dates-holder">
	<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-oneperpage-walletcards-labels" id="teqcidbplugin-generate-class-forms-button-oneperpage-walletcards-labels-' . $value->ID . '" data-id="' . $value->ID . '" data-uniqueclassid="' . $value->uniqueclassid . '" data-classname="' . $value->classname . '" data-classstartdate="' . $value->classstartdate . '" data-classtype="' . $value->classtype . '" data-classformat="' . $value->classformat . '" data-classcost="' . $value->classcost . '" data-classstreetaddress="' . $value->classstreetaddress . '" data-classcity="' . $value->classcity . '" data-classstate="' . $value->classstate . '" data-classzip="' . $value->classzip . '">
		<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">
		<p data-nohistory="' . $no_history . '">Generate Wallet Cards<br/>(One Per Page)</p>
	</div>
	<div>
		<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
			<label class="teqcidbplugin-form-section-fields-label">From Date</label>
			<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-walletcards-fromdate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-fromdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
		</div>
		<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
			<label class="teqcidbplugin-form-section-fields-label">To Date</label>
			<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-generate-class-forms-button-all-walletcards-todate" id="teqcidbplugin-generate-class-forms-button-all-walletcards-todate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="">
		</div>
	</div>
</div>


								<div class="teqcidbplugin-form-section-fields-wrapper">
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Name Of Class</label>
										<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-classname-' . $value->ID . '" data-dbname="title" type="text" placeholder="Name of Class" value="' . $value->classname . '" />
									</div>
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Class Format</label>
										<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-class-format-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">
											<option value="default" selected default disabled>Make a Selection...</option>
											<option ' . $format_selected_inperson . ' value="inperson">In-Person</option>
											<option ' . $format_selected_online . ' value="online">Online</option>
											<option ' . $format_selected_hybrid . ' value="hybrid">Hybrid</option>
											<option ' . $format_selected_other . ' value="other">Other</option>
										</select>
									</div>
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Class Type</label>
										<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-class-type-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">
											<option value="default" selected default disabled>Make a Selection...</option>
											<option ' . $other_selected_initial . ' value="initial">Initial</option>
											<option ' . $other_selected_refresher . ' value="refresher">Refresher</option>
											<option ' . $other_selected_hybrid . ' value="hybrid">Hybrid</option>
											<option ' . $other_selected_other . ' value="other">Other</option>
										</select>
									</div>
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Street Address</label>
										<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-address-' . $value->ID . '" data-dbname="authorlast1" type="text" placeholder="Class Street Address" value="' . $value->classstreetaddress . '"/>
									</div>
								</div>
								<div class="teqcidbplugin-form-section-fields-wrapper">
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">City</label>
										<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-city-' . $value->ID . '" data-dbname="authorfirst2" type="text" placeholder="Class City" value="' . $value->classcity . '"/>
									</div>
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">State</label>
										<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-student-contactstate-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">
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
										<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-zip-' . $value->ID . '" data-dbname="authorfirst3" type="text" placeholder="Class Zip Code" value="' . $value->classzip . '"/>
									</div>
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Class Date</label>
										<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-startdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $value->classstartdate . '"/>
									</div>
								</div>
								<div class="teqcidbplugin-form-section-fields-wrapper">
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Class Start Time</label>
										<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-starttime-' . $value->ID . '" data-dbname="pages" type="time" value="' . $value->classstarttime . '"/>
									</div>
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Class End Time</label>
										<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-endtime-' . $value->ID . '" data-dbname="pages" type="time" value="' . $value->classendtime . '"/>
									</div>
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Class Cost</label>
										<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-cost-' . $value->ID . '" data-dbname="isbn13" type="text" placeholder="Cost of Class" value="' . $value->classcost . '"/>
									</div>
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">Class Size</label>
										<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-size-' . $value->ID . '" data-dbname="isbn13" type="number" placeholder="Maximum Number of Attendees" value="' . $value->classsize . '"/>
									</div>
								</div>
								<div class="teqcidbplugin-form-section-fields-wrapper">
									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">
										<label class="teqcidbplugin-form-section-fields-label">General Class Description</label>
										<textarea disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-textarea" id="teqcidbplugin-class-description-' . $value->ID . '" data-dbname="maincoverimage" type="text" placeholder="Provide a description for this class here.">' . $value->classdescription . '</textarea>
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
			$loop_control_whole_numbers = floor( $this->total_classes_count / $this->pagination_display_limit );
			if ( $this->total_classes_count < $this->pagination_display_limit ) {
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
