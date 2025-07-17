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

* Replace 'initialtrainingdate' through 'THING6' with an individual item we're going to be searching for.

* Replace 'teqcidb_students' with the database table THING5 of the main thing we're recording here.

* Replace 'THINGWENEEDWILDCARDSEARCHFOR' with one of the search things (initialtrainingdate, THING2, THING3, firstname, THING5, or THING6) that we need to perform a Wildcard search on, using the MySQL 'LIKE' Operator, as opposed to a straight = operation.

* Replace 'studentsTHING5' with the main THING5 of the thing we're recording. This is also the thing we'll be ordering the Queries by.

*/



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



if ( ! class_exists( 'TEQcidbPlugin_Qci_frontend_list', false ) ) :



	/**

	 * TEQcidbPlugin_Admin_Menu Class.

	 */

	class TEQcidbPlugin_Qci_frontend_list {



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

		public $search_state = '';

		public $search_city = '';

		public $search_company = '';

		public $search_firstname = '';

		public $search_lastname = '';

		public $search_qcinumber = '';

		public $sort_qcinumber = '';

		public $sort_initialtrainingdate = '';

		public $sort_initialrefresherdate = '';

		public $active_search = false;

		public $active_sort = false;

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

			if ( 'null' !== $this->sort_expirationdate && '' !== $this->sort_expirationdate && null !== $this->sort_expirationdate && 'https://training.thompsonengineering.com/list-of-credentialed-inspectors-qci/' != $this->sort_expirationdate ) {

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

			$string1 = '<div class="teqcidb-display-search-ui-top-container teqcidb-display-search-ui-top-container-on-frontend">

							<div class="teqcidb-display-search-ui-inner-container">

								<div class="teqcidb-display-search-ui-search-fields-container">

									<div class="teqcidb-form-section-fields-wrapper">

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<label class="teqcidb-form-section-fields-label">State</label>

											<select class="teqcidb-form-section-fields-input teqcidb-form-section-fields-input-select" id="teqcidb-search-state" THING5="search_state">

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



				if ( ( null !== $value->qcinumber ) && ( '' !== $value->qcinumber ) && ( null !== $value->wpuserid ) ) {

					

					// Build the state selection drop-down deal.

					$statename = '';

					$states = array(

					     array('name'=>'Alabama', 'abbr'=>'AL'),

					     array('name'=>'Alaska', 'abbr'=>'AK'),

					     array('name'=>'Arizona', 'abbr'=>'AZ'),

					     array('name'=>'Arkansas', 'abbr'=>'AR'),

					     array('name'=>'California', 'abbr'=>'CA'),

					     array('name'=>'Colorado', 'abbr'=>'CO'),

					     array('name'=>'Connecticut', 'abbr'=>'CT'),

					     array('name'=>'Delaware', 'abbr'=>'DE'),

					     array('name'=>'Florida', 'abbr'=>'FL'),

					     array('name'=>'Georgia', 'abbr'=>'GA'),

					     array('name'=>'Hawaii', 'abbr'=>'HI'),

					     array('name'=>'Idaho', 'abbr'=>'ID'),

					     array('name'=>'Illinois', 'abbr'=>'IL'),

					     array('name'=>'Indiana', 'abbr'=>'IN'),

					     array('name'=>'Iowa', 'abbr'=>'IA'),

					     array('name'=>'Kansas', 'abbr'=>'KS'),

					     array('name'=>'Kentucky', 'abbr'=>'KY'),

					     array('name'=>'Louisiana', 'abbr'=>'LA'),

					     array('name'=>'Maine', 'abbr'=>'ME'),

					     array('name'=>'Maryland', 'abbr'=>'MD'),

					     array('name'=>'Massachusetts', 'abbr'=>'MA'),

					     array('name'=>'Michigan', 'abbr'=>'MI'),

					     array('name'=>'Minnesota', 'abbr'=>'MN'),

					     array('name'=>'Mississippi', 'abbr'=>'MS'),

					     array('name'=>'Missouri', 'abbr'=>'MO'),

					     array('name'=>'Montana', 'abbr'=>'MT'),

					     array('name'=>'Nebraska', 'abbr'=>'NE'),

					     array('name'=>'Nevada', 'abbr'=>'NV'),

					     array('name'=>'New Hampshire', 'abbr'=>'NH'),

					     array('name'=>'New Jersey', 'abbr'=>'NJ'),

					     array('name'=>'New Mexico', 'abbr'=>'NM'),

					     array('name'=>'New York', 'abbr'=>'NY'),

					     array('name'=>'North Carolina', 'abbr'=>'NC'),

					     array('name'=>'North Dakota', 'abbr'=>'ND'),

					     array('name'=>'Ohio', 'abbr'=>'OH'),

					     array('name'=>'Oklahoma', 'abbr'=>'OK'),

					     array('name'=>'Oregon', 'abbr'=>'OR'),

					     array('name'=>'Pennsylvania', 'abbr'=>'PA'),

					     array('name'=>'Rhode Island', 'abbr'=>'RI'),

					     array('name'=>'South Carolina', 'abbr'=>'SC'),

					     array('name'=>'South Dakota', 'abbr'=>'SD'),

					     array('name'=>'Tennessee', 'abbr'=>'TN'),

					     array('name'=>'Texas', 'abbr'=>'TX'),

					     array('name'=>'Utah', 'abbr'=>'UT'),

					     array('name'=>'Vermont', 'abbr'=>'VT'),

					     array('name'=>'Virginia', 'abbr'=>'VA'),

					     array('name'=>'Washington', 'abbr'=>'WA'),

					     array('name'=>'West Virginia', 'abbr'=>'WV'),

					     array('name'=>'Wisconsin', 'abbr'=>'WI'),

					     array('name'=>'Wyoming', 'abbr'=>'WY'),

					     array('name'=>'Virgin Islands', 'abbr'=>'V.I.'),

					     array('name'=>'Guam', 'abbr'=>'GU'),

					     array('name'=>'Puerto Rico', 'abbr'=>'PR')

					  );



					foreach( $states as $statekey => $statevalue ) {

						if ( $value->contactstate === $statevalue['abbr'] ) {

							$statename = $statevalue['name'];

						}

					}



					if ( false !== stripos( $value->associations, 'none' ) ) {

						$value->associations = 'None';

					}



					if ( '' === $value->associations || null === $value->associations ) {

						$value->associations = 'None';

					}



					$value->associations = str_replace( ',', ', ', $value->associations );

					$value->associations = str_replace( 'aapa', 'Alabama Asphalt Pavement Association', $value->associations );

					$value->associations = str_replace( 'arba', 'Alabama Road Builders Association', $value->associations );

					$value->associations = str_replace( 'agc', 'Alabama Associated General Contractors', $value->associations );

					$value->associations = str_replace( 'abc', 'Alabama Associated Builders and Contractors', $value->associations );

					$value->associations = str_replace( 'auca', 'Alabama Utility Contractors Association', $value->associations );



					if ( ( null === $value->expirationdate ) || ( '' === $value->expirationdate ) ) {

						$value->expirationdate = 'N/A';

					}



					$string1 = $string1 . '

						<div class="teqcidb-students-update-container teqcidb-all-students teqcidb-all-students-on-frontend" >

							

							<button class="accordion teqcidb-students-update-container-accordion-heading">

								' .  $value->lastname . ', ' . $value->firstname . '

							</button>

							<div class="teqcidb-students-update-info-container" data-open="false">

								<div class="teqcidbplugin-form-wrapper">

									<div class="teqcidbplugin-form-section-wrapper" id="teqcidbplugin-form-section-wrapper-' . $value->ID . '">

										<div class="teqcidbplugin-form-section-fields-wrapper">

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">Name</label>

												<p class="teqcidb-form-section-fields-label-actualvalue">' .  stripslashes( $value->firstname ) . ' ' . stripslashes( $value->lastname ) . '</p>

											</div>

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">Company</label>

												<p class="teqcidb-form-section-fields-label-actualvalue">' . stripslashes( stripslashes( $value->company ) ) . '</p>

											</div>

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">City</label>

												<p class="teqcidb-form-section-fields-label-actualvalue">' . stripslashes( $value->contactcity ) . '</p>

											</div>

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">State</label>

												<p class="teqcidb-form-section-fields-label-actualvalue">' . $statename . '</p>

											</div>

										</div>

										<div class="teqcidbplugin-form-section-fields-wrapper">

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">QCI Number</label>

												<p class="teqcidb-form-section-fields-label-actualvalue">' . $value->qcinumber . '</p>

											</div>

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">Expiration Date</label>

												<p class="teqcidb-form-section-fields-label-actualvalue">' . date("m-d-Y", strtotime( $value->expirationdate ) ) . '</p>

											</div>

										</div>

									</div>

								</div>

							</div>						

					</div>';

				}



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

				for ($i=0; $i <= $loop_control_whole_numbers; $i++) { 

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

