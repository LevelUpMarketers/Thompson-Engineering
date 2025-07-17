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

		public $string8 = '';





		/**

		 * Class Constructor

		 */

		public function __construct() {



			global $wpdb;



			// Get every single student, period.

			$table_name              = $wpdb->prefix . 'teqcidb_students';

			$this->all_students_array = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table_name} ORDER BY lastname, firstname") );

           
			foreach ( $this->all_students_array as $key => $student ) {

			    if ( empty( $student->lastname ) ) continue;
			   //if ($key > 30) break;

			  $this->string8 .= '<option value="' . $student->firstname . ' ' . $student->lastname . 

			  '" data-altcontactname="' . $student->altcontactname . '" data-altcontactemail="' . $student->altcontactemail . 

			  '" data-altcontactphone="' . $student->altcontactphone . '" data-uniquestudentid="' . $student->uniquestudentid . 

			  '" data-wpuserid="' . $student->wpuserid . '" data-firstname="' . $student->firstname . '" data-lastname="' . $student->lastname . 

			  '" data-company="' . $student->company . '" data-contactstreetaddress="' . $student->contactstreetaddress . '" data-contactcity="' . $student->contactcity . 

			  '" data-contactstate="' . $student->contactstate . '" data-contactzip="' . $student->contactzip . '" data-phonecell="' . $student->phonecell . 

			  '" data-phoneoffice="' . $student->phoneoffice . '" data-email="' . $student->email . '" data-initialtrainingdate="' . $student->initialtrainingdate . 

			  '" data-qcinumber="' . $student->qcinumber . '" data-comments="' . $student->comments . '" data-associations="' . $student->associations . 

			  '" data-expirationdate="' . $student->expirationdate . '" data-lastrefresherdate="' . $student->lastrefresherdate . 

			  '">' . ucfirst( $student->firstname ) . ' ' . ucfirst( $student->lastname ) . '</option>';

            }

            //die( '<select>' . $this->string8 . '</select>' );

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
				//echo 'fdsfdsafdsfsda' . $key;
				if( 2 === $key || ( $key > 2 ) ){
					//break;
				}

				// set the variable to determine if class is hidden from front-end
				$hiddenfromfrontyes = '';
				$hiddenfromfrontno = '';

				if ( 'Yes' === $value->classhide) {
					$hiddenfromfrontyes = 'selected';
				}

				if ( 'No' === $value->classhide ) {
					$hiddenfromfrontno = 'selected';
				}




				$hiddenfromfront = '';
				if ( 'on' === $value->classhide ) {
					$hiddenfromfront = 'checked';
				}


				// Reset the Historical HTML;

				$historical_training_html = '';



				// Get the historical training info for each student.
if ( 'inperson' === $value->classformat ) {
				$historical_training_data = $wpdb->get_results( "SELECT * FROM $this->student_history_table WHERE uniqueclassid LIKE '%" . $value->uniqueclassid . "%'" );



				// The following If Else is all simply to get the listed students associated with a class alphabetized, because I didn't include first/last names in the teqcidb_studenthistory table.

				$newhistoricalarray = array();

				if ( 1 < sizeof( $historical_training_data ) ) {

					$query = "SELECT * FROM $this->students_table WHERE uniquestudentid LIKE '%" . $historical_training_data[0]->uniquestudentid . "%'";



					// Build 1 huge query to get ALL students in 1 db call.

					foreach( $historical_training_data as $sorting_key => $sorting_data ){

						$query = $query . " OR uniquestudentid LIKE '%" . $sorting_data->uniquestudentid . "%'";

					}



					// Order the query and make the db call.

					$query = $query . " ORDER BY lastname";

					$allstudentresults = $wpdb->get_results( $query );



					// Now loop through all the resulting students.

					foreach( $allstudentresults as $sorting_student_key => $sorting_student_data ){

						if( 2 === $sorting_student_key || ( $sorting_student_key > 2 ) ){
							//break;
						}



						// then loop through the class results. When a student matches, through the class results into a new array.

						foreach( $historical_training_data as $hist_key => $hist_data ){

							if ( $hist_data->uniquestudentid === $sorting_student_data->uniquestudentid ){

								array_push( $newhistoricalarray, $historical_training_data[$hist_key] );

							}

						}

					}

				} else {

					// If there's just 1 class result.

					$newhistoricalarray = $historical_training_data;

				}



				//echo ' -- ' . sizeof($historical_training_data) .  ' -- ';
				// Now build the historical data html.

				$no_historical_data = '';

				if ( 0 < sizeof( $historical_training_data ) ) {

					foreach( $newhistoricalarray as $training_key => $training_data ){



						$actualstudentdata = $wpdb->get_row( "SELECT * FROM $this->students_table WHERE uniquestudentid LIKE '%" . $training_data->uniquestudentid . "%'" );



						$registered_selected_yes = '';

						$registered_selected_no = '';

						$registered_selected_pending = '';

						$registered_selected_null = '';

						if ( 'no' === $training_data->registered ) {

							$registered_selected_no = 'selected';

						}



						if ( 'yes' === $training_data->registered ) {

							$registered_selected_yes = 'selected';

						}



						if ( 'pending' === $training_data->registered ) {

							$registered_selected_pending = 'selected';

						}



						if ( ( null === $training_data->registered ) || ( 'null' === $training_data->registered ) ) {

							$registered_selected_null = 'selected';

						}

						$attended_selected_yes = '';

						$attended_selected_no = '';

						$attended_selected_upcoming = '';

						if ( 'no' === $training_data->attended ) {

							$attended_selected_no = 'selected';

						}



						if ( 'yes' === $training_data->attended ) {

							$attended_selected_yes = 'selected';

						}



						if ( 'upcoming' === $training_data->attended ) {

							$attended_selected_upcoming = 'selected';

						}



						$outcome_selected_upcoming   = '';

						$outcome_selected_passed     = '';

						$outcome_selected_failed     = '';

						$outcome_selected_deferred   = '';

						$outcome_selected_null   = '';

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



						if ( ( null === $training_data->outcome ) || ( 'null' === $training_data->outcome ) ) {

							$outcome_selected_null = 'selected';

						}



						$payment_selected_pending   = '';

						$payment_selected_full      = '';

						$payment_selected_none      = '';

						$payment_selected_waived    = '';

						$payment_selected_null      = '';

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



						if ( ( null === $training_data->paymentstatus ) || ( 'null' === $training_data->paymentstatus ) ) {

							$payment_selected_null = 'selected';

						} 



						// Let's get the class cost and type.

						$class_info = $wpdb->get_row( "SELECT * FROM $class_table_name WHERE uniqueclassid LIKE '%" . $training_data->uniqueclassid . "%'" );



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

						switch ( $actualstudentdata->contactstate ) {

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



						$association_array = explode( ',', $actualstudentdata->associations );

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



						$historical_training_html = $historical_training_html . '









<div style="display: none;" class="teqcidbplugin-form-section-fields-class-wrapper teqcidbplugin-form-section-fields-class-wrapper-for-replication">

											<div class="teqcidbplugin-form-section-remove-student-historical-info-div">

												<img class="teqcidbplugin-form-section-placeholder-image-small teqcidbplugin-form-section-remove-student-historical-info" src="' . TEQCIDB_ROOT_IMG_URL . 'close.png">

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Choose a Student</label>

													<select data-id="' . $value->ID . '" class="to-populate-just-students teqcidbplugin-form-section-fields-input-text teqcidbplugin-form-studentname-student-editingclass" id="teqcidbplugin-form-studentname-1">

														<option selected default disabled>Select a Student...</option>';	





														$historical_training_html = $historical_training_html . //$this->string8 . 

													'</select>

												</div>

												<div style="display: none;" class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">First Name</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-firstname-' . $value->ID . '" data-dbname="title" type="text" value="' . $actualstudentdata->firstname . '" placeholder="Student\'s First Name">

												</div>

												<div style="display: none;" class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Last Name</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastname-' . $value->ID . '" data-dbname="authorfirst1" type="text" value="' . $actualstudentdata->lastname . '" placeholder="Student\'s Last Name">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Company</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-company-' . $value->ID . '" data-dbname="authorlast1" type="text" value="' . $actualstudentdata->company . '" placeholder="Student\'s Company">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Cell Phone</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-phonecell-' . $value->ID . '" data-dbname="authorfirst2" type="text" value="' . $actualstudentdata->phonecell . '" placeholder="Student\'s Cell Phone">

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Office Phone</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-phoneoffice-' . $value->ID . '" data-dbname="authorlast2" type="text" value="' . $actualstudentdata->phoneoffice . '" placeholder="Students\'s Office Phone">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Email</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-email-' . $value->ID . '" data-dbname="authorfirst3" type="text" value="' . $actualstudentdata->email . '" placeholder="Student\'s Email Address">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Street Address</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-contactstreetaddress-' . $value->ID . '" data-dbname="authorlast3" type="text" value="' . $actualstudentdata->contactstreetaddress . '" placeholder="Student\'s Street Address">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">City</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-contactcity-' . $value->ID . '" data-dbname="pages" type="text" value="' . $actualstudentdata->contactcity . '" placeholder="Student\'s City">

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

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

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-contactzip-' . $value->ID . '" data-dbname="isbn13" type="text" value="' . $actualstudentdata->contactzip . '" placeholder="Student\'s Zip Code">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">QCI Number</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-qcinumber-' . $value->ID . '" data-dbname="publisher" type="text" value="' . $actualstudentdata->qcinumber . '" placeholder="Student\'s QCI Number">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Initial Training Date</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-initialtrainingdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $actualstudentdata->initialtrainingdate . '" placeholder="Student\'s Initial Training Date">

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Name</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactname-' . $value->ID . '" data-dbname="isbn13" type="text" placeholder="Alternate Contact Name" value="' . $actualstudentdata->altcontactname . '" />

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Email</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactemail-' . $value->ID . '" data-dbname="publisher" type="text" placeholder="Alternate Contact Email" value="' . $actualstudentdata->altcontactemail . '" />

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Phone</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactphone-' . $value->ID . '" type="text" placeholder="Alternate Contact Phone" value="' . $actualstudentdata->altcontactphone . '"/>

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Last Refresher Date</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastrefresherdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $actualstudentdata->lastrefresherdate . '" placeholder="Student\'s Last Refresher Training Date">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Expiration Date</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-expirationdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $actualstudentdata->expirationdate . '" placeholder="Student\'s Initial Training Date">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Student\'s Associations</label>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div-container">

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-aapa-' . $value->ID . '" type="checkbox" ' . $aapa . ' data-association="aapa" />

															<label>AAPA</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-arba-' . $value->ID . '" type="checkbox" ' . $arba . ' data-association="arba" />

															<label>ARBA</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-agc-' . $value->ID . '" type="checkbox" ' . $agc . ' data-association="agc" />

															<label>AGC</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-abc-' . $value->ID . '" type="checkbox" ' . $abc . ' data-association="abc" />

															<label>ABC</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-auca-' . $value->ID . '" type="checkbox" ' . $auca . ' data-association="auca" />

															<label>AUCA</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-none-' . $value->ID . '" type="checkbox" ' . $none . ' data-association="none" />

															<label>None</label>

														</div>

													</div>

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Comments about this Student</label>

													<textarea style="width: 13vw;" disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-textarea" id="teqcidbplugin-student-comments-' . $value->ID . '" data-dbname="maincoverimage" type="text" placeholder="Enter comments about this student">' . $actualstudentdata->comments . '</textarea>

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Admin Approved?</label>

													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-registeredforclass-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

														<option ' . $registered_selected_null . ' value="default" default disabled>Make a Selection...</option>

														<option ' . $registered_selected_yes . ' value="yes">Yes</option>

														<option ' . $registered_selected_no . ' value="no">No</option>

														<option ' . $registered_selected_pending . ' value="pending">Pending Approval</option>

													</select>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Attended this Class?</label>

													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-attended-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

														<option value="default" selected default disabled>Make a Selection...</option>

														<option ' . $attended_selected_upcoming . ' value="upcoming">Class is Upcoming</option>

														<option ' . $attended_selected_yes . ' value="yes">Yes</option>

														<option ' . $attended_selected_no . ' value="no">No</option>

													</select>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Enrollment Date</label>

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classenrollment-' . $value->ID . '" type="date" value="' . $training_data->enrollmentdate . '"/>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Credentials Date</label>

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classcredentials-' . $value->ID . '" type="date" value="' . $training_data->credentialsdate . '"/>

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Class Outcome</label>

													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-outcome-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

														<option ' . $outcome_selected_null . ' value="default" default disabled>Make a Selection...</option>

														<option ' . $outcome_selected_upcoming . ' value="upcoming">Class is Upcoming</option>

														<option ' . $outcome_selected_passed . ' value="passed">Passed</option>

														<option ' . $outcome_selected_failed . ' value="failed">Failed</option>

														<option ' . $outcome_selected_deferred . ' value="deferred">Deferred/Delayed</option>

													</select>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Payment Status</label>

													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-paymentstatus-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

														<option ' . $payment_selected_null . ' value="default" selected default disabled>Make a Selection...</option>

														<option ' . $payment_selected_pending . ' value="pending">Payment Pending</option>

														<option ' . $payment_selected_full . ' value="paidinfull">Paid in Full</option>

														<option ' . $payment_selected_none . ' value="nopaymentmade">No Payment Made</option>

														<option ' . $payment_selected_waived . ' value="paymentwaived">Payment Waived</option>

													</select>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Amount Paid</label>

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-amountpaid-' . $value->ID . '" data-dbname="isbn13" type="text" value="' . $training_data->amountpaid . '" placeholder="Amount Paid">

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-uniquestudentid-' . $value->ID . '" type="hidden" value="' . $actualstudentdata->uniquestudentid . '">

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-wpuserid-' . $value->ID . '" type="hidden" value="' . $actualstudentdata->wpuserid . '">

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

										</div>';

		}

					}

				} else {

					$no_historical_data = 'Only students for in-person classes are displayed on this tab.';

					$no_history = "true";

					$historical_training_html = '

										<div style="display: none;" class="teqcidbplugin-form-section-fields-class-wrapper teqcidbplugin-form-section-fields-class-wrapper-for-replication">

											<div class="teqcidbplugin-form-section-remove-student-historical-info-div">

												<img class="teqcidbplugin-form-section-placeholder-image-small teqcidbplugin-form-section-remove-student-historical-info" src="' . TEQCIDB_ROOT_IMG_URL . 'close.png">

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Choose a Student</label>

													<select data-id="' . $value->ID . '" class="to-populate-just-students teqcidbplugin-form-section-fields-input-text teqcidbplugin-form-studentname-student-editingclass" id="teqcidbplugin-form-studentname-1">

														<option selected default disabled>Select a Student...</option>';	





														$historical_training_html = $historical_training_html . //$this->string8 . 

													'</select>

												</div>

												<div style="display: none;" class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">First Name</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-firstname-' . $value->ID . '" data-dbname="title" type="text" value="' . $actualstudentdata->firstname . '" placeholder="Student\'s First Name">

												</div>

												<div style="display: none;" class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Last Name</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastname-' . $value->ID . '" data-dbname="authorfirst1" type="text" value="' . $actualstudentdata->lastname . '" placeholder="Student\'s Last Name">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Company</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-company-' . $value->ID . '" data-dbname="authorlast1" type="text" value="' . $actualstudentdata->company . '" placeholder="Student\'s Company">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Cell Phone</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-phonecell-' . $value->ID . '" data-dbname="authorfirst2" type="text" value="' . $actualstudentdata->phonecell . '" placeholder="Student\'s Cell Phone">

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Office Phone</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-phoneoffice-' . $value->ID . '" data-dbname="authorlast2" type="text" value="' . $actualstudentdata->phoneoffice . '" placeholder="Students\'s Office Phone">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Email</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-email-' . $value->ID . '" data-dbname="authorfirst3" type="text" value="' . $actualstudentdata->email . '" placeholder="Student\'s Email Address">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Street Address</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-contactstreetaddress-' . $value->ID . '" data-dbname="authorlast3" type="text" value="' . $actualstudentdata->contactstreetaddress . '" placeholder="Student\'s Street Address">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">City</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-contactcity-' . $value->ID . '" data-dbname="pages" type="text" value="' . $actualstudentdata->contactcity . '" placeholder="Student\'s City">

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

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

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-contactzip-' . $value->ID . '" data-dbname="isbn13" type="text" value="' . $actualstudentdata->contactzip . '" placeholder="Student\'s Zip Code">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">QCI Number</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-qcinumber-' . $value->ID . '" data-dbname="publisher" type="text" value="' . $actualstudentdata->qcinumber . '" placeholder="Student\'s QCI Number">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Initial Training Date</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-initialtrainingdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $actualstudentdata->initialtrainingdate . '" placeholder="Student\'s Initial Training Date">

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Name</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactname-' . $value->ID . '" data-dbname="isbn13" type="text" placeholder="Alternate Contact Name" value="' . $actualstudentdata->altcontactname . '" />

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Email</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactemail-' . $value->ID . '" data-dbname="publisher" type="text" placeholder="Alternate Contact Email" value="' . $actualstudentdata->altcontactemail . '" />

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Phone</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactphone-' . $value->ID . '" type="text" placeholder="Alternate Contact Phone" value="' . $actualstudentdata->altcontactphone . '"/>

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Last Refresher Date</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastrefresherdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $actualstudentdata->lastrefresherdate . '" placeholder="Student\'s Last Refresher Training Date">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Expiration Date</label>

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-expirationdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $actualstudentdata->expirationdate . '" placeholder="Student\'s Initial Training Date">

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Student\'s Associations</label>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div-container">

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-aapa-' . $value->ID . '" type="checkbox" ' . $aapa . ' data-association="aapa" />

															<label>AAPA</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-arba-' . $value->ID . '" type="checkbox" ' . $arba . ' data-association="arba" />

															<label>ARBA</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-agc-' . $value->ID . '" type="checkbox" ' . $agc . ' data-association="agc" />

															<label>AGC</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-abc-' . $value->ID . '" type="checkbox" ' . $abc . ' data-association="abc" />

															<label>ABC</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-auca-' . $value->ID . '" type="checkbox" ' . $auca . ' data-association="auca" />

															<label>AUCA</label>

														</div>

														<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

															<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-none-' . $value->ID . '" type="checkbox" ' . $none . ' data-association="none" />

															<label>None</label>

														</div>

													</div>

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Comments about this Student</label>

													<textarea style="width: 13vw;" disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-textarea" id="teqcidbplugin-student-comments-' . $value->ID . '" data-dbname="maincoverimage" type="text" placeholder="Enter comments about this student">' . $actualstudentdata->comments . '</textarea>

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Admin Approved?</label>

													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-registeredforclass-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

														<option ' . $registered_selected_null . ' value="default" default disabled>Make a Selection...</option>

														<option ' . $registered_selected_yes . ' value="yes">Yes</option>

														<option ' . $registered_selected_no . ' value="no">No</option>

														<option ' . $registered_selected_pending . ' value="pending">Pending Approval</option>

													</select>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Attended this Class?</label>

													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-attended-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

														<option value="default" selected default disabled>Make a Selection...</option>

														<option ' . $attended_selected_upcoming . ' value="upcoming">Class is Upcoming</option>

														<option ' . $attended_selected_yes . ' value="yes">Yes</option>

														<option ' . $attended_selected_no . ' value="no">No</option>

													</select>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Enrollment Date</label>

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classenrollment-' . $value->ID . '" type="date" value="' . $training_data->enrollmentdate . '"/>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Credentials Date</label>

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classcredentials-' . $value->ID . '" type="date" value="' . $training_data->credentialsdate . '"/>

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-wrapper">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Class Outcome</label>

													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-outcome-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

														<option ' . $outcome_selected_null . ' value="default" default disabled>Make a Selection...</option>

														<option ' . $outcome_selected_upcoming . ' value="upcoming">Class is Upcoming</option>

														<option ' . $outcome_selected_passed . ' value="passed">Passed</option>

														<option ' . $outcome_selected_failed . ' value="failed">Failed</option>

														<option ' . $outcome_selected_deferred . ' value="deferred">Deferred/Delayed</option>

													</select>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Payment Status</label>

													<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-paymentstatus-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

														<option ' . $payment_selected_null . ' value="default" selected default disabled>Make a Selection...</option>

														<option ' . $payment_selected_pending . ' value="pending">Payment Pending</option>

														<option ' . $payment_selected_full . ' value="paidinfull">Paid in Full</option>

														<option ' . $payment_selected_none . ' value="nopaymentmade">No Payment Made</option>

														<option ' . $payment_selected_waived . ' value="paymentwaived">Payment Waived</option>

													</select>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

													<label class="teqcidbplugin-form-section-fields-label">Amount Paid</label>

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-amountpaid-' . $value->ID . '" data-dbname="isbn13" type="text" value="' . $training_data->amountpaid . '" placeholder="Amount Paid">

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-uniquestudentid-' . $value->ID . '" type="hidden" value="' . $actualstudentdata->uniquestudentid . '">

													<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-wpuserid-' . $value->ID . '" type="hidden" value="' . $actualstudentdata->wpuserid . '">

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

										</div>';

				}



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



				// Build the Class Instructors HTML

				$instructors = explode( ',', $value->instructors );

				$instructorhtml = '';

				$firstinstructor = '';

				foreach ( $instructors as $instructorkey => $instructor ) {

					

					if ( '' !== $instructor ) {



						if ( '' === $firstinstructor ){

							$firstinstructor = $instructor;

						} else {

							$instructorhtml = $instructorhtml . '

								<div class="teqcidbplugin-extra-instructors-top-div">

									<label style="margin-top: 15px;" class="teqcidbplugin-form-section-fields-label">Class Instructor(s)</label>

									<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-class-instructors-field-' . $value->ID . '" data-dbname="isbn13" type="text" placeholder="Class Instructor" value="' . $instructor . '"/>

									<div class="teqcidbplugin-form-section-remove-instructor-field-div">

										<img class="teqcidbplugin-form-section-placeholder-image-extrasmall teqcidbplugin-form-section-placeholder-image-small teqcidbplugin-form-section-remove-student-historical-info" src="' . TEQCIDB_ROOT_IMG_URL . 'close.png"/>

									</div>

								</div>';

						}

					}



				}



				



				$string1 = $string1 . '

					<div class="teqcidb-students-update-container teqcidb-all-students" >

						

						<button class="accordion teqcidb-students-update-container-accordion-heading">

							' .  $value->classname . '

						</button>

						<div class="teqcidb-students-update-info-container" data-open="false">

							<div class="teqcidbplugin-form-wrapper" id="teqcidbplugin-form-section-wrapper-' . $value->ID . '">

								<div class="teqcidbplugin-form-section-fields-wrapper">

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Name Of Class</label>

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-classname-' . $value->ID . '" data-dbname="title" type="text" placeholder="Name of Class" value="' . $value->classname . '" />

									</div>

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Class Format</label>

										<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-class-format-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

											<option value="default" selected default disabled>Make a Selection...</option>

											<option ' . $format_selected_inperson . ' value="inperson">In-Person</option>

											<option ' . $format_selected_online . ' value="online">Online</option>

											<option ' . $format_selected_hybrid . ' value="hybrid">Hybrid</option>

											<option ' . $format_selected_other . ' value="other">Other</option>

										</select>

									</div>

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Class Type</label>

										<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-class-type-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

											<option value="default" selected default disabled>Make a Selection...</option>

											<option ' . $other_selected_initial . ' value="initial">Initial</option>

											<option ' . $other_selected_refresher . ' value="refresher">Refresher</option>

											<option ' . $other_selected_hybrid . ' value="hybrid">Hybrid</option>

											<option ' . $other_selected_other . ' value="other">Other</option>

										</select>

									</div>

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Street Address</label>

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-address-' . $value->ID . '" data-dbname="authorlast1" type="text" placeholder="Class Street Address" value="' . $value->classstreetaddress . '"/>

									</div>

								</div>

								<div class="teqcidbplugin-form-section-fields-wrapper">

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">City</label>

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-city-' . $value->ID . '" data-dbname="authorfirst2" type="text" placeholder="Class City" value="' . $value->classcity . '"/>

									</div>

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">State</label>

										<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-class-state-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

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

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-zip-' . $value->ID . '" data-dbname="authorfirst3" type="text" placeholder="Class Zip Code" value="' . $value->classzip . '"/>

									</div>

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Class Date</label>

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-startdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $value->classstartdate . '"/>

									</div>

								</div>

								<div class="teqcidbplugin-form-section-fields-wrapper">

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Class Start Time</label>

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-starttime-' . $value->ID . '" data-dbname="pages" type="time" value="' . $value->classstarttime . '"/>

									</div>

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Class End Time</label>

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-endtime-' . $value->ID . '" data-dbname="pages" type="time" value="' . $value->classendtime . '"/>

									</div>

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Class Cost</label>

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-cost-' . $value->ID . '" data-dbname="isbn13" type="text" placeholder="Cost of Class" value="' . $value->classcost . '"/>

									</div>

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Class Size</label>

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-class-size-' . $value->ID . '" data-dbname="isbn13" type="number" placeholder="Maximum Number of Attendees" value="' . $value->classsize . '"/>

									</div>

								</div>

								<div class="teqcidbplugin-form-section-fields-wrapper">

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">Class Instructor(s)</label>

										<input class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text teqcidbplugin-class-instructors-field-' . $value->ID . '" data-dbname="isbn13" type="text" placeholder="Class Instructor" value="' . $firstinstructor . '" />

										' . $instructorhtml . '

										<div class="teqcidbplugin-add-more-instructors-div teqcidbplugin-add-more-instructors-edit-div" data-id="' . $value->ID . '">

											<img class="teqcidbplugin-form-section-placeholder-image-small" id="teqcidbplugin-form-section-placeholder-image-backcover-actual" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png" />

											<p>Add an Instructor</p>

										</div>

									</div>

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<label class="teqcidbplugin-form-section-fields-label">General Class Description</label>

										<textarea class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-textarea" id="teqcidbplugin-class-description-' . $value->ID . '" data-dbname="maincoverimage" type="text" placeholder="Provide a description for this class here.">' . $value->classdescription . '</textarea>

									</div>

								</div>

								<div class="teqcidbplugin-form-section-fields-wrapper">

									<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

										<br/><br/>
										<label>Hide from Front-End</label>
										<br/>
										<select class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-class-hide-' . $value->ID . '" data-classid="' . $value->ID . '" data-classname="' . $value->classname . '" data-uniqueclassid="' . $value->uniqueclassid . '" type="text" placeholder="Hide From Front-End?">

											<option value="default" selected default disabled>Make a Selection...</option>

											<option ' . $hiddenfromfrontyes . ' value="Yes">Yes</option>

											<option ' . $hiddenfromfrontno . ' value="No">No</option>
										</select>
										<br/><br/>

										<label class="teqcidbplugin-form-section-fields-label">Save your edits to this class now!</label>

										<button data-classid="' . $value->ID . '" data-classname="' . $value->classname . '" data-uniqueclassid="' . $value->uniqueclassid . '" class="teqcidbplugin-form-section-submit-button teqcidbplugin-form-section-save-class-edits-submit-button">Save Edits to Class</button>

										<button data-classid="' . $value->ID . '" data-classname="' . $value->classname . '" data-uniqueclassid="' . $value->uniqueclassid . '" class="teqcidbplugin-form-section-submit-button teqcidbplugin-form-section-delete-this-class-submit-button">Delete this Class</button>

										<div class="teqcidbplugin-spinner" id="teqcidbplugin-spinner-' . $value->ID . '"></div>

					 					<div class="teqcidbplugin-response-div-actual-container">

					 						<p class="teqcidbplugin-response-div-p" id="teqcidbplugin-response-div-p-' . $value->ID . '"></p>

					 						<button data-classid="' . $value->ID . '" data-classname="' . $value->classname . '" data-uniqueclassid="' . $value->uniqueclassid . '" class="teqcidbplugin-form-section-submit-button teqcidbplugin-form-section-delete-this-class-submit-button-actual">I\'m Sure - Delete this Class!</button>

					 					</div>

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

$thisscript = '
<script>
(function($){
function init(){
    $("body").on("click", ".select2-container", function (event) {
        if ( ! $(this).prev().hasClass("already-populated")  ) {
            var $options = $( "#all-students-select option" ).clone();
            $(this).prev().append($options);
            $(this).prev().addClass("already-populated");
            $(this).prev().select2("close");
            $(this).prev().select2("open");
		}
    });
}
$( document ).ready(init);
})(jQuery);
</script>';

			$this->final_echoed_html = $this->create_opening_html . $this->create_search_ui_html . $this->create_individual_students_html . $this->create_pagination_html . '<select id="all-students-select" style="display:none">' . $this->string8 . '</select>' . $thisscript . $this->create_closing_html;

		}















	}

endif;

