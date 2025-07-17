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

		public $search_state = '';

		public $search_tenumbers = '';

		public $search_allnontenumbers = '';

		public $search_companyqcinumbers = '';

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

			$this->state = $_GET['state'];



			// Get where we're at with the Pagination currently.

			if ( isset( $_GET['pn'] ) ) {

				$this->pagination_place = $_GET['pn'];

			}



			// Add to the active Parameters array and set the search flag to true.

			if ( 'null' !== $this->state && '' !== $this->state && null !== $this->state ) {

				$this->set_params_array['state'] = $this->state;

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



			// If we have an active search in play...

			if ( $this->active_search ) {





			} else {



				$this->state_array = array(

					 array( 'represented'=>'no', 'name'=>'Alabama', 'abbr'=>'AL'),

				     array( 'represented'=>'no', 'name'=>'Alaska', 'abbr'=>'AK'),

				     array( 'represented'=>'no', 'name'=>'Arizona', 'abbr'=>'AZ'),

				     array( 'represented'=>'no', 'name'=>'Arkansas', 'abbr'=>'AR'),

				     array( 'represented'=>'no', 'name'=>'California', 'abbr'=>'CA'),

				     array( 'represented'=>'no', 'name'=>'Colorado', 'abbr'=>'CO'),

				     array( 'represented'=>'no', 'name'=>'Connecticut', 'abbr'=>'CT'),

				     array( 'represented'=>'no', 'name'=>'Delaware', 'abbr'=>'DE'),

				     array( 'represented'=>'no', 'name'=>'Florida', 'abbr'=>'FL'),

				     array( 'represented'=>'no', 'name'=>'Georgia', 'abbr'=>'GA'),

				     array( 'represented'=>'no', 'name'=>'Hawaii', 'abbr'=>'HI'),

				     array( 'represented'=>'no', 'name'=>'Idaho', 'abbr'=>'ID'),

				     array( 'represented'=>'no', 'name'=>'Illinois', 'abbr'=>'IL'),

				     array( 'represented'=>'no', 'name'=>'Indiana', 'abbr'=>'IN'),

				     array( 'represented'=>'no', 'name'=>'Iowa', 'abbr'=>'IA'),

				     array( 'represented'=>'no', 'name'=>'Kansas', 'abbr'=>'KS'),

				     array( 'represented'=>'no', 'name'=>'Kentucky', 'abbr'=>'KY'),

				     array( 'represented'=>'no', 'name'=>'Louisiana', 'abbr'=>'LA'),

				     array( 'represented'=>'no', 'name'=>'Maine', 'abbr'=>'ME'),

				     array( 'represented'=>'no', 'name'=>'Maryland', 'abbr'=>'MD'),

				     array( 'represented'=>'no', 'name'=>'Massachusetts', 'abbr'=>'MA'),

				     array( 'represented'=>'no', 'name'=>'Michigan', 'abbr'=>'MI'),

				     array( 'represented'=>'no', 'name'=>'Minnesota', 'abbr'=>'MN'),

				     array( 'represented'=>'no', 'name'=>'Mississippi', 'abbr'=>'MS'),

				     array( 'represented'=>'no', 'name'=>'Missouri', 'abbr'=>'MO'),

				     array( 'represented'=>'no', 'name'=>'Montana', 'abbr'=>'MT'),

				     array( 'represented'=>'no', 'name'=>'Nebraska', 'abbr'=>'NE'),

				     array( 'represented'=>'no', 'name'=>'Nevada', 'abbr'=>'NV'),

				     array( 'represented'=>'no', 'name'=>'New Hampshire', 'abbr'=>'NH'),

				     array( 'represented'=>'no', 'name'=>'New Jersey', 'abbr'=>'NJ'),

				     array( 'represented'=>'no', 'name'=>'New Mexico', 'abbr'=>'NM'),

				     array( 'represented'=>'no', 'name'=>'New York', 'abbr'=>'NY'),

				     array( 'represented'=>'no', 'name'=>'North Carolina', 'abbr'=>'NC'),

				     array( 'represented'=>'no', 'name'=>'North Dakota', 'abbr'=>'ND'),

				     array( 'represented'=>'no', 'name'=>'Ohio', 'abbr'=>'OH'),

				     array( 'represented'=>'no', 'name'=>'Oklahoma', 'abbr'=>'OK'),

				     array( 'represented'=>'no', 'name'=>'Oregon', 'abbr'=>'OR'),

				     array( 'represented'=>'no', 'name'=>'Pennsylvania', 'abbr'=>'PA'),

				     array( 'represented'=>'no', 'name'=>'Rhode Island', 'abbr'=>'RI'),

				     array( 'represented'=>'no', 'name'=>'South Carolina', 'abbr'=>'SC'),

				     array( 'represented'=>'no', 'name'=>'South Dakota', 'abbr'=>'SD'),

				     array( 'represented'=>'no', 'name'=>'Tennessee', 'abbr'=>'TN'),

				     array( 'represented'=>'no', 'name'=>'Texas', 'abbr'=>'TX'),

				     array( 'represented'=>'no', 'name'=>'Utah', 'abbr'=>'UT'),

				     array( 'represented'=>'no', 'name'=>'Vermont', 'abbr'=>'VT'),

				     array( 'represented'=>'no', 'name'=>'Virginia', 'abbr'=>'VA'),

				     array( 'represented'=>'no', 'name'=>'Washington', 'abbr'=>'WA'),

				     array( 'represented'=>'no', 'name'=>'West Virginia', 'abbr'=>'WV'),

				     array( 'represented'=>'no', 'name'=>'Wisconsin', 'abbr'=>'WI'),

				     array( 'represented'=>'no', 'name'=>'Wyoming', 'abbr'=>'WY'),

				     array( 'represented'=>'no', 'name'=>'Virgin Islands', 'abbr'=>'V.I.'),

				     array( 'represented'=>'no', 'name'=>'Guam', 'abbr'=>'GU'),

				     array( 'represented'=>'no', 'name'=>'Puerto Rico', 'abbr'=>'PR')

				);



				foreach ($this->state_array as $statekey => $statevalue) {

					$results = $wpdb->get_results( "SELECT * FROM $this->students_table WHERE contactstate = '" . $statevalue['abbr'] . "'" );

					if ( 0 < sizeof( $results ) ) {

						$this->state_array[$statekey]['represented'] = 'yes-' . sizeof( $results );

					}

				}



				//var_dump( $this->state_array );

				$query = "SELECT * FROM $this->students_table WHERE contactstate = '" . $statekey . "'";

				$this->query_part_for_export = $query;			

























			}



			$this->studentsdbresults = $wpdb->get_results( $query . " ORDER BY lastname ASC, firstname ASC LIMIT $this->pagination_place, $this->pagination_display_limit" );

			$count_query = "select count(*) from $this->students_table";

			$this->total_students_count = $wpdb->get_var( $count_query );

			

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

							<p class="teqcidb-tab-intro-para teqcidb-tab-intro-para-searchsort">Below is a visual listing off all states and whether they are or aren\'t represented, based on all Student\'s state of residence.</p>

						</div>';

					



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

			foreach ( $this->state_array as $key => $value ) {



				$inlinestyle = '';

				if ( false !== stripos( $value['represented'], 'yes' ) ) {

					$exp = explode( '-', $value['represented'] );

					$value['represented'] = ' is represented with ' . $exp[1] . ' student(s)! Good job - take the day off!';

					$inlinestyle = 'style="background-color:green;color:#fff"';

				} else {

					$value['represented'] = ' is <i>NOT</i> represented... get to work and turn this green!';

					$inlinestyle = 'style="background-color:red;color:#fff"';

				}









				$string1 = $string1 . '

					<div class="teqcidb-students-update-container teqcidb-all-students" >

						

						<button ' . $inlinestyle . ' class="accordion teqcidb-students-update-container-accordion-heading">

							' .  $value['name'] . ' ' . $value['represented'] . '

						</button>

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

				for ($i=0; $i <= $loop_control_whole_numbers; $i++) { 

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

