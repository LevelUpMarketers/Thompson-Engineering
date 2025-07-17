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



if ( ! class_exists( 'TEQcidbPlugin_settings2_Form', false ) ) :



	/**

	 * TEQcidbPlugin_Admin_Menu Class.

	 */

	class TEQcidbPlugin_settings2_Form {



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



			$this->create_search_ui();



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

			$this->search_state = $_GET['contactstate'];

			$this->search_city = $_GET['contactcity'];

			$this->search_company = $_GET['company'];

			$this->search_firstname = $_GET['firstname'];

			$this->search_lastname = $_GET['lastname'];

			$this->search_qcinumber = $_GET['qcinumber'];

			$this->sort_qcinumber = $_GET['sortqcinumber'];

			$this->sort_initialtrainingdate = $_GET['sortinitialtrainingdate'];

			$this->sort_lastrefresherdate = $_GET['sortlastrefresherdate'];

			$this->sort_expirationdate = $_GET['sortexpirationdate'];



			// Get where we're at with the Pagination currently.

			if ( isset( $_GET['pn'] ) ) {

				$this->pagination_place = $_GET['pn'];

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_state && '' !== $this->search_state && null !== $this->search_state ) {

				$this->set_params_array['contactstate'] = $this->search_state;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_city && '' !== $this->search_city && null !== $this->search_city ) {

				$this->set_params_array['contactcity'] = $this->search_city;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_company && '' !== $this->search_company && null !== $this->search_company ) {

				$this->set_params_array['company'] = $this->search_company;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_firstname && '' !== $this->search_firstname && null !== $this->search_firstname ) {

				$this->set_params_array['firstname'] = $this->search_firstname;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_lastname && '' !== $this->search_lastname && null !== $this->search_lastname ) {

				$this->set_params_array['lastname'] = $this->search_lastname;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_qcinumber && '' !== $this->search_qcinumber && null !== $this->search_qcinumber ) {

				$this->set_params_array['qcinumber'] = $this->search_qcinumber;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the sort flag to true.

			if ( 'null' !== $this->sort_qcinumber && '' !== $this->sort_qcinumber && null !== $this->sort_qcinumber ) {

				$this->active_sort = true;

			}



			// Add to the active Parameters array and set the sort flag to true.

			if ( 'null' !== $this->sort_initialtrainingdate && '' !== $this->sort_initialtrainingdate && null !== $this->sort_initialtrainingdate ) {

				$this->active_sort = true;

			}



			// Add to the active Parameters array and set the sort flag to true.

			if ( 'null' !== $this->sort_lastrefresherdate && '' !== $this->sort_lastrefresherdate && null !== $this->sort_lastrefresherdate ) {

				$this->active_sort = true;

			}



			// Add to the active Parameters array and set the sort flag to true.

			if ( 'null' !== $this->sort_expirationdate && '' !== $this->sort_expirationdate && null !== $this->sort_expirationdate ) {

				$this->active_sort = true;

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

			$this->class_history_table = $wpdb->prefix . 'teqcidb_classes';



			// If we have an active search in play...

			if ( $this->active_search ) {



				// This If-Else and the get_results line directly after it is if we want to do an exclusive search - a serach that returns a smaller, more specific amount of results.

				$query_part = '';

				$count_query_part = '';



				// If there's only 1 Search Parameter in play, this If statement executes, to make sure we're not appending additional stuff to the DB Query.

				if ( 1 === sizeof($this->set_params_array) ) {

					foreach ( $this->set_params_array as $params_search_key => $params_search_value ) {



						

						$query_part = "SELECT * FROM $this->students_table WHERE " . $params_search_key . " LIKE '%" . $params_search_value . "%'";

						$count_query_part = " WHERE " . $params_search_key . " LIKE '%" . $params_search_value . "%'";

						



						$this->query_part_for_export = $query_part;

						$query_part . "LIMIT $this->pagination_place, $this->pagination_display_limit";



						// Now taking into account any active sorting options...

						$orderby_part = 'ORDER BY LTRIM( lastname ) ASC, LTRIM( firstname ) ASC';

						if ( $this->active_sort ) {

							if ( '' !== $this->search_sortqcinumber ) {

								$orderby_part = 'ORDER BY qcinumber DESC';

							}



							if ( '' !== $this->search_sortinitialtrainingdate ) {

								$orderby_part = 'ORDER BY initialtrainingdate DESC';

							}



							if ( '' !== $this->search_sortlastrefresherdate ) {

								$orderby_part = 'ORDER BY lastrefresherdate DESC';

							}



							if ( '' !== $this->sort_expirationdate ) {

								$orderby_part = 'ORDER BY expirationdate DESC';

							}

						}



						$this->studentsdbresults = $wpdb->get_results($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

						error_log('2');

						error_log($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");



						$count_query = "select count(*) from $this->students_table" . $count_query_part;

		    			$this->total_students_count = $wpdb->get_var( $count_query );









					}

				} else {

					// All this below executes if there are more searches in play than just 1.

					$counter = 0;

					foreach ( $this->set_params_array as $params_search_key => $params_search_value ) {



						// If this is our first time in the loop, begin the new Query correctly.

						if ( 0 === $counter ) {

							

							// If we need a Wildcard Search for something - if so, we need to do a 'Like' instead of =, else, do a strict = comparison in the else block below.

							$query_part = "SELECT * FROM $this->students_table WHERE " . $params_search_key . " LIKE '%" . $params_search_value . "%'";

							$count_query_part = " WHERE " . $params_search_key . " LIKE '%" . $params_search_value . "%'";

							$counter++;

						} else {



							// Continue building the Query by appending 'AND' Operators.



							// If we need a Wildcard Search for something - if so, we need to do a 'Like' instead of =, else, do a strict = comparison in the else block below.

							$query_part = $query_part . " AND " . $params_search_key . " LIKE '%" . $params_search_value . "%'";

							$count_query_part = $count_query_part . " AND " . $params_search_key . " LIKE '%" . $params_search_value . "%'";

						}

					}

				}



				$this->query_part_for_export = $query_part;

				$query_part . "LIMIT $this->pagination_place, $this->pagination_display_limit";



				// Now taking into account any active sorting options...

				$orderby_part = 'ORDER BY LTRIM( lastname ) ASC, LTRIM( firstname ) ASC';

				if ( $this->active_sort ) {

					if ( '' !== $this->search_sortqcinumber ) {

						$orderby_part = 'ORDER BY qcinumber DESC';

					}



					if ( '' !== $this->search_sortinitialtrainingdate ) {

						$orderby_part = 'ORDER BY initialtrainingdate DESC';

					}



					if ( '' !== $this->search_sortlastrefresherdate ) {

						$orderby_part = 'ORDER BY lastrefresherdate DESC';

					}



					if ( '' !== $this->sort_expirationdate ) {

						$orderby_part = 'ORDER BY expirationdate DESC';

					}

				}

				



				$this->studentsdbresults = $wpdb->get_results($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

				error_log('333');

				error_log($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

				$count_query = "select count(*) from $this->students_table" . $count_query_part;

    			$this->total_students_count = $wpdb->get_var( $count_query );



			} else {



				// Now taking into account any active sorting options...

				$orderby_part = 'ORDER BY LTRIM( lastname ) ASC, LTRIM( firstname ) ASC';

				if ( $this->active_sort ) {

					if ( '' !== $this->search_sortqcinumber ) {

						$orderby_part = 'ORDER BY qcinumber DESC';

					}



					if ( '' !== $this->search_sortinitialtrainingdate ) {

						$orderby_part = 'ORDER BY initialtrainingdate DESC';

					}



					if ( '' !== $this->search_sortlastrefresherdate ) {

						$orderby_part = 'ORDER BY lastrefresherdate DESC';

					}



					if ( '' !== $this->sort_expirationdate ) {

						$orderby_part = 'ORDER BY expirationdate DESC';

					}

				}



				$this->studentsdbresults = $wpdb->get_results("SELECT * FROM $this->students_table $orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

				error_log('111');

				error_log($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

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

/*

			$students_initialtrainingdate_table = $wpdb->prefix . 'teqcidb_students_initialtrainingdate';

			$students_initialtrainingdate_in_db = $wpdb->get_results("SELECT DISTINCT(state) as state FROM $students_initialtrainingdate_table ORDER BY LTRIM( state ) ASC");

			// Build the default Select option.

			$initialtrainingdate_html = '<option value="" default disabled selected>Select A initialtrainingdate...</option>';

			// Loop through all results and build the actual Select options.

			foreach ($students_initialtrainingdate_in_db as $initialtrainingdate) {

				$initialtrainingdate_html = $initialtrainingdate_html . "<option>" . ucwords( strtolower( $initialtrainingdate->state ) ) . "</option>";

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



			$students_firstname_table = $wpdb->prefix . 'teqcidb_students_firstname';

			$students_firstname_in_db = $wpdb->get_results("SELECT DISTINCT(studentsfirstname) as studentsfirstname FROM $students_firstname_table ORDER BY LTRIM( studentsfirstname ) ASC");

			// Build the default Select option.

			$firstname_html = '<option value="" default disabled selected>Select A firstname...</option>';

			// Loop through all results and build the actual Select options.

			foreach ($students_firstname_in_db as $firstname) {

				$firstname_html = $firstname_html . "<option>" . $firstname->studentsfirstname . "</option>";

			}



			$students_THING5_table = $wpdb->prefix . 'teqcidb_students_THING5';

			$students_THING5_in_db = $wpdb->get_results("SELECT DISTINCT(studentsTHING5) as studentsTHING5 FROM $students_THING5_table ORDER BY LTRIM( studentsTHING5 ) ASC");

			// Build the default Select option.

			$THING5_html = '<option value="" default disabled selected>Select A Company THING5...</option>';

			// Loop through all results and build the actual Select options.

			foreach ( $students_THING5_in_db as $THING5) {

				$THING5_html = $THING5_html . "<option>" . $THING5->studentsTHING5 . "</option>";

			}

*/

			// Now start building the actual HTML for the search area.

			$string1 = '<div class="teqcidb-display-search-ui-top-container">

							<p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-searchsort">Select your search options below</p>

							<div class="teqcidb-display-search-ui-inner-container">

								<div class="teqcidb-display-search-ui-search-fields-container">

									<div class="teqcidb-form-section-fields-wrapper">

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<label class="teqcidb-form-section-fields-label">State</label>

											<select id="teqcidb-search-state" THING5="search_state">

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

											<label class="teqcidb-form-section-fields-label">City</label>

											<input type="text" id="teqcidb-search-city"/>

										</div>

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<label class="teqcidb-form-section-fields-label">Company</label>

											<input type="text" id="teqcidb-search-company"/>

										</div>

									</div>

									<div class="teqcidb-form-section-fields-wrapper">

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<label class="teqcidb-form-section-fields-label">First Name</label>

											<input type="text" id="teqcidb-search-firstname"/>

										</div>

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<label class="teqcidb-form-section-fields-label">Last Name</label>

											<input type="text" id="teqcidb-search-lastname"/>

										</div>

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<label class="teqcidb-form-section-fields-label">QCI Number</label>

											<input type="text" id="teqcidb-search-qcinumber"/>

										</div>

									</div>

									<p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-searchsort">Choose a sorting option below</p>

									<div class="teqcidb-form-section-fields-wrapper teqcidb-form-section-fields-wrapper-for-sorting">

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-sort-field">

											<label class="teqcidb-form-section-fields-label">QCI Number</label>

											<input type="checkbox" id="teqcidb-sort-qcinumber" class="teqcidb-sort-checkboxes"/>

										</div>

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-sort-field">

											<label class="teqcidb-form-section-fields-label">Initial Training Date</label>

											<input type="checkbox" id="teqcidb-sort-initialtrainingdate" class="teqcidb-sort-checkboxes"/>

										</div>

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-sort-field">

											<label class="teqcidb-form-section-fields-label">Refresher Training Date</label>

											<input type="checkbox" id="teqcidb-sort-lastrefresherdate" class="teqcidb-sort-checkboxes"/>

										</div>

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-sort-field">

											<label class="teqcidb-form-section-fields-label">Expiration Date</label>

											<input type="checkbox" id="teqcidb-sort-expirationdate" class="teqcidb-sort-checkboxes"/>

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



			// Get every singleclass, period.

			$class_table_name  = $wpdb->prefix . 'teqcidb_classes';

			$this->all_classes_array = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$class_table_name}") );



			$string1 = '';

			foreach ( $this->studentsdbresults as $key => $value ) {



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

					<div class="teqcidb-students-update-container teqcidb-all-students teqcidb-student-forms-update-container" >

						

						<button class="accordion teqcidb-students-update-container-accordion-heading">

							' .  $value->firstname . ' ' . $value->lastname . '

						</button>

						<div class="teqcidb-students-update-info-container" data-open="false">

							<div class="teqcidbplugin-form-wrapper">

								<div class="teqcidbplugin-form-wrapper-inner-instructor-row">

									<div class="teqcidbplugin-form-wrapper-inner-instructor-indiv">

										<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-certification-initial" id="teqcidbplugin-generate-class-forms-button-certification-initial-' . $value->ID . '" data-id="' . $value->ID . '" data-uniquestudentid="' . $value->uniquestudentid . '" data-firstname="' . $value->firstname . '" data-lastname="' . $value->lastname . '" data-company="' . stripslashes( $value->company ) . '" data-contactstreetaddress="' . $value->contactstreetaddress . '" data-contactcity="' . $value->contactcity . '" data-contactstate="' . $value->contactcity . '" data-contactzip="' . $value->contactzip . '" data-phonecell="' . $value->phonecell . '" data-phoneoffice="' . $value->phoneoffice . '" data-email="' . $value->email . '" data-initialtrainingdate="' . $value->initialtrainingdate . '" data-expirationdate="' . $value->expirationdate . '" data-qcinumber="' . $value->qcinumber . '" data-associations="' . $value->associations . '" data-lastrefresherdate="' . $value->lastrefresherdate . '">

											<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">

											<p data-nohistory="' . $no_history . '">Generate Completion Certificate (initial in-person)</p>

										</div>

										<div>

											<input class="teqcidbplugin-form-wrapper-inner-instructor-indiv-input" placeholder="Instructor Names" type="text" />

										</div>

									</div>

									<div class="teqcidbplugin-form-wrapper-inner-instructor-indiv">

										<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-certification-refresher" id="teqcidbplugin-generate-class-forms-button-certification-refresher-' . $value->ID . '" data-id="' . $value->ID . '" data-uniquestudentid="' . $value->uniquestudentid . '" data-firstname="' . $value->firstname . '" data-lastname="' . $value->lastname . '" data-company="' . stripslashes( $value->company ) . '" data-contactstreetaddress="' . $value->contactstreetaddress . '" data-contactcity="' . $value->contactcity . '" data-contactstate="' . $value->contactcity . '" data-contactzip="' . $value->contactzip . '" data-phonecell="' . $value->phonecell . '" data-phoneoffice="' . $value->phoneoffice . '" data-email="' . $value->email . '" data-initialtrainingdate="' . $value->initialtrainingdate . '" data-expirationdate="' . $value->expirationdate . '" data-qcinumber="' . $value->qcinumber . '" data-associations="' . $value->associations . '" data-lastrefresherdate="' . $value->lastrefresherdate . '">

											<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">

											<p data-nohistory="' . $no_history . '">Generate Completion Certificate (refresher in-person)</p>

										</div>

										<div>

											<input class="teqcidbplugin-form-wrapper-inner-instructor-indiv-input" placeholder="Instructor Names" type="text" />

										</div>

									</div>



								</div>



								<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-certification-initial-online" id="teqcidbplugin-generate-class-forms-button-certification-initial-online-' . $value->ID . '" data-id="' . $value->ID . '" data-uniquestudentid="' . $value->uniquestudentid . '" data-firstname="' . $value->firstname . '" data-lastname="' . $value->lastname . '" data-company="' . stripslashes( $value->company ) . '" data-contactstreetaddress="' . $value->contactstreetaddress . '" data-contactcity="' . $value->contactcity . '" data-contactstate="' . $value->contactcity . '" data-contactzip="' . $value->contactzip . '" data-phonecell="' . $value->phonecell . '" data-phoneoffice="' . $value->phoneoffice . '" data-email="' . $value->email . '" data-initialtrainingdate="' . $value->initialtrainingdate . '" data-expirationdate="' . $value->expirationdate . '" data-qcinumber="' . $value->qcinumber . '" data-associations="' . $value->associations . '" data-lastrefresherdate="' . $value->lastrefresherdate . '">

									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">

									<p data-nohistory="' . $no_history . '">Generate Completion Certificate (initial online)</p>

								</div>

								<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-certification-refresher-online" id="teqcidbplugin-generate-class-forms-button-certification-refresher-online-' . $value->ID . '" data-id="' . $value->ID . '" data-uniquestudentid="' . $value->uniquestudentid . '" data-firstname="' . $value->firstname . '" data-lastname="' . $value->lastname . '" data-company="' . stripslashes( $value->company ) . '" data-contactstreetaddress="' . $value->contactstreetaddress . '" data-contactcity="' . $value->contactcity . '" data-contactstate="' . $value->contactcity . '" data-contactzip="' . $value->contactzip . '" data-phonecell="' . $value->phonecell . '" data-phoneoffice="' . $value->phoneoffice . '" data-email="' . $value->email . '" data-initialtrainingdate="' . $value->initialtrainingdate . '" data-expirationdate="' . $value->expirationdate . '" data-qcinumber="' . $value->qcinumber . '" data-associations="' . $value->associations . '" data-lastrefresherdate="' . $value->lastrefresherdate . '">

									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">

									<p data-nohistory="' . $no_history . '">Generate Completion Certificate (refresher online)</p>

								</div>

								<div class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-walletcard" id="teqcidbplugin-generate-class-forms-button-walletcard-' . $value->ID . '" data-id="' . $value->ID . '" data-uniquestudentid="' . $value->uniquestudentid . '" data-firstname="' . $value->firstname . '" data-lastname="' . $value->lastname . '" data-company="' . stripslashes( $value->company ) . '" data-contactstreetaddress="' . $value->contactstreetaddress . '" data-contactcity="' . $value->contactcity . '" data-contactstate="' . $value->contactstate . '" data-contactzip="' . $value->contactzip . '" data-phonecell="' . $value->phonecell . '" data-phoneoffice="' . $value->phoneoffice . '" data-email="' . $value->email . '" data-initialtrainingdate="' . $value->initialtrainingdate . '" data-expirationdate="' . $value->expirationdate . '" data-qcinumber="' . $value->qcinumber . '" data-associations="' . $value->associations . '" data-lastrefresherdate="' . $value->lastrefresherdate . '">

									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">

									<p data-nohistory="' . $no_history . '">Generate Wallet<br/>Card</p>

								</div>

								<div style="display:none;" class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-walletcardback" id="teqcidbplugin-generate-class-forms-button-walletcardback-' . $value->ID . '" data-id="' . $value->ID . '" data-uniquestudentid="' . $value->uniquestudentid . '" data-firstname="' . $value->firstname . '" data-lastname="' . $value->lastname . '" data-company="' . stripslashes( $value->company ) . '" data-contactstreetaddress="' . $value->contactstreetaddress . '" data-contactcity="' . $value->contactcity . '" data-contactstate="' . $value->contactstate . '" data-contactzip="' . $value->contactzip . '" data-phonecell="' . $value->phonecell . '" data-phoneoffice="' . $value->phoneoffice . '" data-email="' . $value->email . '" data-initialtrainingdate="' . $value->initialtrainingdate . '" data-expirationdate="' . $value->expirationdate . '" data-qcinumber="' . $value->qcinumber . '" data-associations="' . $value->associations . '" data-lastrefresherdate="' . $value->lastrefresherdate . '">

									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">

									<p data-nohistory="' . $no_history . '">Generate Wallet Card (back)</p>

								</div>

								<div style="display:none;" class="teqcidbplugin-generate-class-forms-button teqcidbplugin-generate-class-forms-button-maillabel" id="teqcidbplugin-generate-class-forms-button-maillabel-' . $value->ID . '" data-id="' . $value->ID . '" data-uniquestudentid="' . $value->uniquestudentid . '" data-firstname="' . $value->firstname . '" data-lastname="' . $value->lastname . '" data-company="' . stripslashes( $value->company ) . '" data-contactstreetaddress="' . $value->contactstreetaddress . '" data-contactcity="' . $value->contactcity . '" data-contactstate="' . $value->contactstate . '" data-contactzip="' . $value->contactzip . '" data-phonecell="' . $value->phonecell . '" data-phoneoffice="' . $value->phoneoffice . '" data-email="' . $value->email . '" data-initialtrainingdate="' . $value->initialtrainingdate . '" data-expirationdate="' . $value->expirationdate . '" data-qcinumber="' . $value->qcinumber . '" data-associations="' . $value->associations . '" data-lastrefresherdate="' . $value->lastrefresherdate . '">

									<img class="teqcidbplugin-form-section-placeholder-image-small" src="' . TEQCIDB_ROOT_IMG_URL . 'plus.png">

									<p data-nohistory="' . $no_history . '">Generate Mailing Label</p>

								</div>

								<div class="teqcidbplugin-form-section-wrapper" id="teqcidbplugin-form-section-wrapper-' . $value->ID . '">

									<div class="teqcidbplugin-form-section-fields-wrapper">

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">First Name</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-firstname-' . $value->ID . '" data-dbname="title" type="text" value="' . $value->firstname . '" placeholder="Student\'s First Name">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Last Name</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastname-' . $value->ID . '" data-dbname="authorfirst1" type="text" value="' . $value->lastname . '" placeholder="Student\'s Last Name">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Company</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-company-' . $value->ID . '" data-dbname="authorlast1" type="text" value="' . stripslashes( $value->company ) . '" placeholder="Student\'s Company">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Cell Phone</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-cellphone-' . $value->ID . '" data-dbname="authorfirst2" type="text" value="' . $value->phonecell . '" placeholder="Student\'s Cell Phone">

										</div>

									</div>

									<div class="teqcidbplugin-form-section-fields-wrapper">

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Office Phone</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-officephone-' . $value->ID . '" data-dbname="authorlast2" type="text" value="' . $value->phoneoffice . '" placeholder="Students\'s Office Phone">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Email</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-email-' . $value->ID . '" data-dbname="authorfirst3" type="text" value="' . $value->email . '" placeholder="Student\'s Email Address">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Street Address</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-streetaddress-' . $value->ID . '" data-dbname="authorlast3" type="text" value="' . $value->contactstreetaddress . '" placeholder="Student\'s Street Address">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">City</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-city-' . $value->ID . '" data-dbname="pages" type="text" value="' . $value->contactcity . '" placeholder="Student\'s City">

										</div>

									</div>

									<div class="teqcidbplugin-form-section-fields-wrapper">

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">State</label>

											<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-student-state-' . $value->ID . '" data-dbname="isbn10" type="text" placeholder="Student\'s State">

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

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-zip-' . $value->ID . '" data-dbname="isbn13" type="text" value="' . $value->contactzip . '" placeholder="Student\'s Zip Code">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">QCI Number</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-qci-' . $value->ID . '" data-dbname="publisher" type="text" value="' . $value->qcinumber . '" placeholder="Student\'s QCI Number">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Initial Training Date</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-initialtrainingdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $value->initialtrainingdate . '" placeholder="Student\'s Initial Training Date">

										</div>

									</div>

									<div class="teqcidbplugin-form-section-fields-wrapper">

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Last Refresher Date</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastrefresherdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $value->lastrefresherdate . '" placeholder="Student\'s Last Refresher Training Date">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Expiration Date</label>

											<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-expirationdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $value->expirationdate . '" placeholder="Student\'s Initial Training Date">

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Student\'s Associations</label>

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div-container">

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-aapa" type="checkbox" ' . $aapa . ' data-association="aapa" />

													<label>AAPA</label>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-arba" type="checkbox" ' . $arba . ' data-association="arba" />

													<label>ARBA</label>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-agc" type="checkbox" ' . $agc . ' data-association="agc" />

													<label>AGC</label>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-abc" type="checkbox" ' . $abc . ' data-association="abc" />

													<label>ABC</label>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-auca" type="checkbox" ' . $auca . ' data-association="auca" />

													<label>AUCA</label>

												</div>

												<div class="teqcidbplugin-form-section-fields-indiv-wrapper-checkbox-div">

													<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-checkbox teqcidbplugin-form-section-fields-input-checkbox-' . $value->ID . '" id="teqcidbplugin-student-association-none" type="checkbox" ' . $none . ' data-association="none" />

													<label>None</label>

												</div>

											</div>

										</div>

									</div>

									<div class="teqcidbplugin-form-section-fields-wrapper">

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">General Notes &amp; Comments about this Student</label>

											<textarea disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-textarea" id="teqcidbplugin-student-comments-' . $value->ID . '" data-dbname="maincoverimage" type="text" placeholder="Enter comments about this student">' . $value->comments . '</textarea>

										</div>

									</div>

									<div class="teqcidbplugin-form-section-fields-wrapper">

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Student Image #1</label>

											<div class="teqcidbplugin-form-section-placeholder-image-wrapper">

												<img class="teqcidbplugin-form-section-placeholder-image" id="teqcidbplugin-form-section-placeholder-image-frontcover-actual" src="' . $value->studentimage1 . '">

											</div>

										</div>

										<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

											<label class="teqcidbplugin-form-section-fields-label">Student Image #2</label>

											<div class="teqcidbplugin-form-section-placeholder-image-wrapper">

												<img class="teqcidbplugin-form-section-placeholder-image" id="teqcidbplugin-form-section-placeholder-image-backcover-actual" src="' . $value->studentimage2 . '">

											</div>

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

								<select disabled class="teqcidb-prevnextbuttons" id="teqcidb-pageselect" data-currentpn="' . $this->pagination_place . '" data-pagelimit="' . $this->pagination_display_limit . '">

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

