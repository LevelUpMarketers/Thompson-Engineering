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

		public $search_expired = '';

		public $search_tenumbers = '';

		public $search_allnontenumbers = '';

		public $search_companyqcinumbers = '';

		public $search_fromdate = '';

		public $search_todate = '';

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

			$this->search_expired = $_GET['expired'];

			$this->search_tenumbers = $_GET['tenumbers'];

			$this->search_allnontenumbers = $_GET['allnontenumbers'];

			$this->search_companyqcinumbers = $_GET['companyqcinumbers'];

			$this->search_fromdate = $_GET['fromdate'];

			$this->search_todate = $_GET['todate'];



			// Get where we're at with the Pagination currently.

			if ( isset( $_GET['pn'] ) ) {

				$this->pagination_place = $_GET['pn'];

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_expired && '' !== $this->search_expired && null !== $this->search_expired ) {

				$this->set_params_array['expired'] = $this->search_expired;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_tenumbers && '' !== $this->search_tenumbers && null !== $this->search_tenumbers ) {

				$this->set_params_array['tenumbers'] = $this->search_tenumbers;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_allnontenumbers && '' !== $this->search_allnontenumbers && null !== $this->search_allnontenumbers ) {

				$this->set_params_array['allnontenumbers'] = $this->search_allnontenumbers;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_companyqcinumbers && '' !== $this->search_companyqcinumbers && null !== $this->search_companyqcinumbers ) {

				$this->search_companyqcinumbers = str_replace('+', ' ', $this->search_companyqcinumbers);

				$this->set_params_array['companyqcinumbers'] = $this->search_companyqcinumbers;

				$this->active_search = true;

			}


			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_fromdate && '' !== $this->search_fromdate && null !== $this->search_fromdate ) {

				$this->set_params_array['fromdate'] = $this->search_fromdate;

				$this->active_search = true;

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->search_todate && '' !== $this->search_todate && null !== $this->search_todate ) {

				$this->set_params_array['todate'] = $this->search_todate;

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

			$this->class_history_table = $wpdb->prefix . 'teqcidb_classes';

			$orderby_part = 'ORDER BY lastname ASC, firstname ASC';



			// If we have an active search in play...

			if ( $this->active_search ) {



				// This If-Else and the get_results line directly after it is if we want to do an exclusive search - a serach that returns a smaller, more specific amount of results.

				$query_part = '';

				$count_query_part = '';



				// If there's only 1 Search Parameter in play, this If statement executes, to make sure we're not appending additional stuff to the DB Query.

				if ( 'true' === $this->set_params_array['expired'] ) {



					$query_part = "SELECT * FROM $this->students_table WHERE DATE(expirationdate) < DATE(NOW())";

					$count_query_part = " WHERE expirationdate <= " . $today_date;

					

					$this->query_part_for_export = $query_part;

					$query_part . "LIMIT $this->pagination_place, $this->pagination_display_limit";



					$this->studentsdbresults = $wpdb->get_results($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

					

					$count_query = "select count(*) from $this->students_table" . $count_query_part;

	    			$this->total_students_count = $wpdb->get_var( $count_query );

	    			$this->query_part_for_export = $query_part;



				}



				if ( 'true' === $this->set_params_array['tenumbers'] ) {


					// Making a check to see if the expieration date range search request is active
					if ( ( ( '' !== $this->search_fromdate ) && ( null !== $this->search_fromdate ) && ( undefined !== $this->search_fromdate ) ) ){
						$query_part = "SELECT * FROM $this->students_table WHERE company LIKE 'Thompson Engineering%' AND expirationdate BETWEEN '$this->search_fromdate' AND '$this->search_todate'";
						$count_query_part = " WHERE qcinumber LIKE 't%' AND expirationdate BETWEEN '" . $this->search_fromdate . "' AND '" . $this->search_todate . "'";
						error_log('HERES THE COUNT QUERY' . $count_query_part  );
					} else {
						$query_part = "SELECT * FROM $this->students_table WHERE company LIKE 'Thompson Engineering%'";
						$count_query_part = " WHERE company LIKE 'Thompson Engineering%'";
					}

					

					
					$this->query_part_for_export = $query_part;

					$query_part . "LIMIT $this->pagination_place, $this->pagination_display_limit";



					$this->studentsdbresults = $wpdb->get_results($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

					

					$count_query = "select count(*) from $this->students_table" . $count_query_part;

	    			$this->total_students_count = $wpdb->get_var( $count_query );

	    			$this->query_part_for_export = $query_part;



				}



				if ( 'true' === $this->set_params_array['allnontenumbers'] ) {

					// Making a check to see if the expieration date range search request is active
					if ( ( ( '' !== $this->search_fromdate ) && ( null !== $this->search_fromdate ) && ( undefined !== $this->search_fromdate ) ) ){
						$query_part = "SELECT * FROM $this->students_table WHERE company NOT LIKE 'Thompson Engineering%' AND expirationdate BETWEEN '$this->search_fromdate' AND '$this->search_todate'";
						$count_query_part = " WHERE qcinumber NOT LIKE 't%' AND expirationdate BETWEEN '" . $this->search_fromdate . "' AND '" . $this->search_todate . "'";
					} else {
						$query_part = "SELECT * FROM $this->students_table WHERE company NOT LIKE 'Thompson Engineering%'";
						$count_query_part = " WHERE company NOT LIKE 'Thompson Engineering%'";
					}


					

					$this->query_part_for_export = $query_part;

					$query_part . "LIMIT $this->pagination_place, $this->pagination_display_limit";



					$this->studentsdbresults = $wpdb->get_results($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

					

					$count_query = "select count(*) from $this->students_table" . $count_query_part;

	    			$this->total_students_count = $wpdb->get_var( $count_query );

	    			$this->query_part_for_export = $query_part;



				}



				if ( ( '' !== $this->set_params_array['companyqcinumbers'] ) && ( null !== $this->set_params_array['companyqcinumbers'] ) ) {


					// Making a check to see if the expieration date range search request is active
					if ( ( ( '' !== $this->search_fromdate ) && ( null !== $this->search_fromdate ) && ( undefined !== $this->search_fromdate ) ) ){
						$query_part = "SELECT * FROM $this->students_table WHERE company LIKE '%" . $this->set_params_array['companyqcinumbers'] . "%' AND expirationdate BETWEEN '$this->search_fromdate' AND '$this->search_todate'";
						$count_query_part = " WHERE qcinumber LIKE 't%' AND expirationdate BETWEEN '" . $this->search_fromdate . "' AND '" . $this->search_todate . "'";
						error_log('HERES THE COUNT QUERY' . $query_part  );
					} else {
						$query_part = "SELECT * FROM $this->students_table WHERE company LIKE '%" . $this->set_params_array['companyqcinumbers'] . "%'";
						$count_query_part = " WHERE company LIKE '%" . $this->set_params_array['companyqcinumbers'] . "%'";
					}
					

					$this->query_part_for_export = $query_part;

					$query_part . "LIMIT $this->pagination_place, $this->pagination_display_limit";



					$this->studentsdbresults = $wpdb->get_results($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

					

					$count_query = "select count(*) from $this->students_table" . $count_query_part;

	    			$this->total_students_count = $wpdb->get_var( $count_query );

	    			$this->query_part_for_export = $query_part;



				}


				if ( ( ( '' !== $this->search_fromdate ) && ( null !== $this->search_fromdate ) && ( undefined !== $this->search_fromdate ) ) ){

					$query_part = "SELECT * FROM $this->students_table WHERE expirationdate BETWEEN '$this->search_fromdate' AND '$this->search_todate'";
					
					$count_query_part = " WHERE expirationdate BETWEEN '" . $this->search_fromdate . "' AND '" . $this->search_todate . "'";


					$this->query_part_for_export = $query_part;

					$query_part . "LIMIT $this->pagination_place, $this->pagination_display_limit";



					$this->studentsdbresults = $wpdb->get_results($query_part . "$orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

					

					$count_query = "select count(*) from $this->students_table" . $count_query_part;

	    			$this->total_students_count = $wpdb->get_var( $count_query );

	    			$this->query_part_for_export = $query_part;



				}


				



				



			} else {



				$this->studentsdbresults = $wpdb->get_results("SELECT * FROM $this->students_table $orderby_part LIMIT $this->pagination_place, $this->pagination_display_limit");

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

											<button data-pn="' . $this->pagination_place . '" class="teqcidb-form-section-fields-label" id="teqcidb-search-by-expired-qcis-button">Expired QCI Numbers</button>

										</div>

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<button data-pn="' . $this->pagination_place . '" class="teqcidb-form-section-fields-label" id="teqcidb-search-by-thompson-qcis-button">Thompson Engineering QCI Numbers</button>

										</div>

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<button data-pn="' . $this->pagination_place . '" class="teqcidb-form-section-fields-label" id="teqcidb-search-by-non-thompson-qcis-button">All Non-Thompson Engineering QCI Numbers</button>

										</div>

									</div>

									<div class="teqcidb-form-section-fields-wrapper teqcidb-form-section-fields-wrapper-specific-companies">

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<label class="teqcidb-form-section-fields-label">Specific Company QCI Numbers</label>

											<input type="text" id="teqcidb-search-by-company-qcis-input" placeholder="Provide a Company Name" value="' . $this->set_params_array['companyqcinumbers'] . '"/>

											<button data-pn="' . $this->pagination_place . '" class="teqcidb-form-section-fields-label" id="teqcidb-search-by-company-button">Search by Company</button>

										</div>
									</div>

									<div class="teqcidb-form-section-fields-wrapper teqcidb-form-section-fields-wrapper-expired-qci-date-range">

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-date-from-qcis-field">

											<label class="teqcidb-form-section-fields-label">From Date</label>

											<input type="date" id="teqcidb-date-from-qcis-input" value="' . $this->set_params_array['fromdate'] . '"/>

										</div>

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-date-to-qcis-field">

											<label class="teqcidb-form-section-fields-label">To Date</label>

											<input type="date" id="teqcidb-date-to-qcis-input" value="' . $this->set_params_array['todate'] . '"/>
										</div>

										<button data-pn="' . $this->pagination_place . '" class="teqcidb-form-section-fields-label" id="teqcidb-search-by-expired-qci-date-range-button">Search QCIs Expiring Within Date Range</button>
									</div>
									<div class="teqcidb-form-section-fields-wrapper teqcidb-form-section-fields-wrapper-specific-companies">

										<div class="teqcidb-form-section-fields-indiv-wrapper teqcidb-search-field">

											<button style="margin-top:30px;" id="teqcidb-reset-report-search-fields" class="teqcidb-search-ui-buttons">Reset Search Fields</button>

											<button style="margin-top:30px;" data-query="' . $this->query_part_for_export . '" id="teqcidb-download-report-search-fields" class="teqcidb-search-ui-buttons">Download Report</button>
										</div>
										<div class="teqcidbplugin-spinner"></div>
									</div>

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

				if ( ( null !== $value->wpuserid ) && ( ( '' !== $value->firstname ) || ( '' !== $value->lastname ) ) ) {

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





							$historical_training_html = $historical_training_html . '

											<div class="teqcidbplugin-form-section-fields-class-wrapper teqcidbplugin-form-section-fields-class-wrapper-for-replication" style="display:none">

												<div class="teqcidbplugin-form-section-fields-wrapper">

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Class Name</label>

														<select disabled class="teqcidbplugin-form-section-fields-input-select teqcidbplugin-form-classname-class select2-hidden-accessible" id="teqcidbplugin-form-classname-1" tabindex="-1" aria-hidden="true">

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

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classstartdate-1" data-dbname="genre" type="date" placeholder="Date of Class/Online Completion Date">

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

														<label class="teqcidbplugin-form-section-fields-label">Class Cost</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-classcost-1" type="text" placeholder="">

													</div>

												</div>

												<div class="teqcidbplugin-form-section-fields-wrapper">

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Enrollment Date</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classenrollment-1" type="date" />

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Credentials Date</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classcredentials" type="date" />

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Attended This Class?</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-attendedthisclass-1">

															<option default="" disabled="" >Make a Selection...</option>

															<option value="upcoming">Class is Upcoming</option>

															<option value="yes">Yes</option>

															<option value="no">No</option>

														</select>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Class Outcome</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-classoutcome-1">

															<option default="" disabled="" >Make a Selection...</option>

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

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-registeredforclass-1">

															<option default="" disabled="">Make a Selection...</option>

															<option value="pending">Pending Approval</option>

															<option value="yes">Yes</option>

															<option value="no">No</option>

														</select>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Payment Status</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-paymentstatus-1">

															<option default="" disabled="" >Make a Selection...</option>

															<option value="pending">Payment Pending</option>

															<option value="paidinfull">Paid in Full</option>

															<option value="nopaymentmade">No Payment Made</option>

															<option value="paymentwaived">Payment Waived</option>

														</select>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Amount Paid</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-amountactuallypaid-1" type="text" placeholder="">

													</div>

												</div>

											</div>

											<div class="teqcidbplugin-form-section-fields-class-wrapper teqcidbplugin-form-section-fields-class-edit-history-wrapper">

												<div class="teqcidbplugin-form-section-fields-wrapper">

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Class Name</label>

														<select disabled class="teqcidbplugin-form-section-fields-input-select teqcidbplugin-form-classname-class select2-hidden-accessible" id="teqcidbplugin-form-classname-1" tabindex="-1" aria-hidden="true">

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

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classstartdate-1" data-dbname="genre" type="date" placeholder="Date of Class/Online Completion Date" value="' . $class_info->classstartdate . '">

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

														<label class="teqcidbplugin-form-section-fields-label">Class Cost</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-classcost-1" type="text" value="' . $class_info->classcost . '">

													</div>

												</div>

												<div class="teqcidbplugin-form-section-fields-wrapper">

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Enrollment Date</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classenrollment-1" type="date" value="' . $training_data->enrollmentdate . '"/>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Credentials Date</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classcredentials" type="date" value="' . $training_data->credentialsdate . '"/>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Attended This Class?</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-attendedthisclass-1">

															<option ' . $attended_selected_null . ' default="" disabled="" >Make a Selection...</option>

															<option ' . $attended_selected_upcoming . ' value="upcoming">Class is Upcoming</option>

															<option ' . $attended_selected_yes . ' value="yes">Yes</option>

															<option ' . $attended_selected_no . ' value="no">No</option>

														</select>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Class Outcome</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-classoutcome-1">

															<option ' . $outcome_selected_null . ' default="" disabled="" >Make a Selection...</option>

															<option ' . $outcome_selected_upcoming . ' value="upcoming">Class is Upcoming</option>

															<option ' . $outcome_selected_passed . ' value="passed">Passed</option>

															<option ' . $outcome_selected_failed . ' value="failed">Failed</option>

															<option ' . $outcome_selected_deferred . ' value="deferred">Deferred/Delayed</option>

														</select>

													</div>

												</div>

												<div class="teqcidbplugin-form-section-fields-wrapper">

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Admin Approved?</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-registeredforclass-1">

															<option ' . $registered_selected_null . ' default="" disabled="" >Make a Selection...</option>

															<option ' . $registered_selected_pending . ' value="pending">Pending Approval</option>

															<option ' . $registered_selected_yes . ' value="yes">Yes</option>

															<option ' . $registered_selected_no . ' value="no">No</option>

														</select>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Payment Status</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-paymentstatus-1">

															<option ' . $payment_selected_null . ' default="" disabled="" >Make a Selection...</option>

															<option ' . $payment_selected_pending . ' value="pending">Payment Pending</option>

															<option ' . $payment_selected_full . ' value="paidinfull">Paid in Full</option>

															<option ' . $payment_selected_none . ' value="nopaymentmade">No Payment Made</option>

															<option ' . $payment_selected_waived . ' value="paymentwaived">Payment Waived</option>

														</select>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Amount Paid</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-amountactuallypaid-1" type="text" value="' . $training_data->amountpaid . '">

													</div>

												</div>

											</div>';

						}

					} else {

						$no_historical_data = 'This Student has no historical training information';

						$no_history = "true";

						$historical_training_html = '

											<div class="teqcidbplugin-form-section-fields-class-wrapper teqcidbplugin-form-section-fields-class-wrapper-for-replication" style="display:none">

												<div class="teqcidbplugin-form-section-fields-wrapper">

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Class Name</label>

														<select disabled class="teqcidbplugin-form-section-fields-input-select teqcidbplugin-form-classname-class select2-hidden-accessible" id="teqcidbplugin-form-classname-1" tabindex="-1" aria-hidden="true">

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

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classstartdate-1" data-dbname="genre" type="date" placeholder="Date of Class/Online Completion Date">

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

														<label class="teqcidbplugin-form-section-fields-label">Class Cost</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-classcost-1" type="text" placeholder="">

													</div>

												</div>

												<div class="teqcidbplugin-form-section-fields-wrapper">

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Enrollment Date</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classenrollment-1" type="date" />

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Credentials Date</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-classcredentials" type="date"/>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Attended This Class?</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-attendedthisclass-1">

															<option default="" disabled="" >Make a Selection...</option>

															<option value="upcoming">Class is Upcoming</option>

															<option value="yes">Yes</option>

															<option value="no">No</option>

														</select>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Class Outcome</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-classoutcome-1">

															<option default="" disabled="" >Make a Selection...</option>

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

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-registeredforclass-1">

															<option default="" disabled="">Make a Selection...</option>

															<option value="pending">Pending Approval</option>

															<option value="yes">Yes</option>

															<option value="no">No</option>

														</select>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Payment Status</label>

														<select disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-select" id="teqcidbplugin-form-paymentstatus-1">

															<option default="" disabled="" >Make a Selection...</option>

															<option value="pending">Payment Pending</option>

															<option value="paidinfull">Paid in Full</option>

															<option value="nopaymentmade">No Payment Made</option>

															<option value="paymentwaived">Payment Waived</option>

														</select>

													</div>

													<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

														<label class="teqcidbplugin-form-section-fields-label">Amount Paid</label>

														<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-form-amountactuallypaid-1" type="text" placeholder="">

													</div>

												</div>

											</div>';

					}



					// Build the state selection drop-down deal.

					switch ( $value->expired ) {

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

								' .  $value->firstname . ' ' . $value->lastname . '

							</button>

							<div class="teqcidb-students-update-info-container" data-open="false">

								<div class="teqcidbplugin-form-wrapper">

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

												<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Name</label>

												<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactname-' . $value->ID . '" data-dbname="isbn13" type="text" placeholder="Alternate Contact Name" value="' . $value->altcontactname . '" />

											</div>

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Email</label>

												<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactemail-' . $value->ID . '" data-dbname="publisher" type="text" placeholder="Alternate Contact Email" value="' . $value->altcontactemail . '" />

											</div>

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">Alternate Contact Phone</label>

												<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-altcontactphone-' . $value->ID . '" type="text" placeholder="Alternate Contact Phone" value="' . $value->altcontactphone . '" />

											</div>

										</div>

										<div class="teqcidbplugin-form-section-fields-wrapper">

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">Last Refresher Date</label>

												<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-lastrefresherdate-' . $value->ID . '" data-dbname="originalpubdate" type="date" value="' . $value->lastrefresherdate . '" placeholder="Student\'s Last Referesher Training Date">

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

												<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-image1-' . $value->ID . '" data-dbname="maincoverimage" type="text" value="' . $value->studentimage1 . '" placeholder="Enter URL or use button below">

											</div>

											<div class="teqcidbplugin-form-section-fields-indiv-wrapper">

												<label class="teqcidbplugin-form-section-fields-label">Student Image #2</label>

												<div class="teqcidbplugin-form-section-placeholder-image-wrapper">

													<img class="teqcidbplugin-form-section-placeholder-image" id="teqcidbplugin-form-section-placeholder-image-backcover-actual" src="' . $value->studentimage2 . '">

												</div>

												<input disabled class="teqcidbplugin-form-section-fields-input teqcidbplugin-form-section-fields-input-text" id="teqcidbplugin-student-image2-' . $value->ID . '" data-dbname="backcoverimage" type="text" value="' . $value->studentimage2 . '" placeholder="Enter URL or use button below">

											</div>

										</div>

										<div class="teqcidbplugin-admin-form-section-header teqcidbplugin-historical-training-header">HISTORICAL TRAINING INFORMATION</div>

										<div style="margin-bottom: 20px;" class="teqcidbplugin-admin-form-section-header">' . $no_historical_data . '</div>

										' . $historical_training_html . '

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

