<?php
/**
 * TEQcidbPlugin Book Display Options Form Tab Class - class-teqcidbplugin-book-display-options-form.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes/Classes
 * @version  6.1.5.
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TEQcidbPlugin_Php_Spreadsheet', false ) ) :

	/**
	 * TEQcidbPlugin_Php_Spreadsheet Class.
	 */
	class TEQcidbPlugin_Php_Spreadsheet {


		/**
		 * Class Constructor - Simply calls the Translations
		 */
		public function __construct() {

			global $wpdb;

			


		}

		function teqcidb_class_form_online_initial_spreadsheet_action_callback(){
			global $wpdb;
			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$classname = filter_var($_POST['classname'],FILTER_SANITIZE_STRING);
			$classformat = filter_var($_POST['classformat'],FILTER_SANITIZE_STRING);
			$classtype = filter_var($_POST['classtype'],FILTER_SANITIZE_STRING);
			$classstreetaddress = filter_var($_POST['classstreetaddress'],FILTER_SANITIZE_STRING);
			$classcity = filter_var($_POST['classcity'],FILTER_SANITIZE_STRING);
			$classstate = filter_var($_POST['classstate'],FILTER_SANITIZE_STRING);
			$classzip = filter_var($_POST['classzip'],FILTER_SANITIZE_STRING);
			$classstartdate = filter_var($_POST['classstartdate'],FILTER_SANITIZE_STRING);
			$classcost = filter_var($_POST['classcost'],FILTER_SANITIZE_URL);

			

			// Get all student history entries that are for this particular class
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );

			// Now get all full student info for each student associated with this class, and build an array.
			$student_table_name = $wpdb->prefix . 'teqcidb_students';
			$student_array = array();
			foreach( $results as $key => $registeredstudent ){
				$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );
				array_push( $student_array, $student );
			}

			// Sort our new students by last name alphabetically.
			$lastname = array_column( $student_array, 'lastname');
			array_multisort($lastname, SORT_ASC, $student_array);

			// Now start building out spreadsheet.
			$spreadsheet = new Spreadsheet();
			$arrayData = [
			    ['QCI Number', 'First Name', 'Last Name', 'Email', 'Company', 'Address', 'City', 'State', 'Zip', 'Phone', 'Alt. Contact Name', 'Alt. Contact Email Address', 'Alt. Contact Phone', 'Enroll Date', 'Completion Date', 'Pass or Fail', 'Payment Status Notes', 'Payment Method', 'Payment Date', 'Amount Due', 'Amount Collected', 'Credentials Date', 'Approved to Attend This Class?', 'General Student Comments'],
			    //['Q1',   12,   15,   21], // Here just to give me a visual reminder of how I can set cells.
			    //['Q2',   56,   73,   86], // Here just to give me a visual reminder of how I can set cells.
			    //['Q3',   52,   61,   69], // Here just to give me a visual reminder of how I can set cells.
			    //['Q4',   30,   32,    0], // Here just to give me a visual reminder of how I can set cells.
			];
			$spreadsheet->getActiveSheet()->fromArray( $arrayData, NULL, 'A1' );

			foreach( $student_array as $key => $student ) {

				$pass = '';
				$amountpaid = '';
				$paymentstatus = '';
				$credentialsdate = '';
				$enrolldate = '';
				foreach( $results as $key2 => $studenthistory ) {
					if ( $student->uniquestudentid === $studenthistory->uniquestudentid ) {

						$pass = $studenthistory->outcome;
						$amountpaid = $studenthistory->amountpaid;
						$credentialsdate = $studenthistory->credentialsdate;
						$enrolldate = $studenthistory->enrollmentdate;

						if ( ( null === $studenthistory->registered ) || ( 'null' === $studenthistory->registered ) ) {
							$studenthistory->registered = 'No';
						}
						$approved = $studenthistory->registered;

						if ( 'paidinfull' === $studenthistory->paymentstatus ) {
							$studenthistory->paymentstatus = 'Paid in Full';
						}
						$paymentstatus = $studenthistory->paymentstatus;
					}
				}

				if ( ( '' !== $credentialsdate ) && ( null !== $credentialsdate ) && ( 'null' !== $credentialsdate ) ) {
					$credentialsdate = date('m-d-Y', strtotime( $credentialsdate ) );
				}

				$arrayData = [
				    [$student->qcinumber, $student->firstname, $student->lastname, $student->email, stripslashes(html_entity_decode($student->company, ENT_QUOTES, 'UTF-8')), $student->contactstreetaddress, $student->contactcity, $student->contactstate, $student->contactzip, $student->phonecell, $student->altcontactname, $student->altcontactemail, $student->altcontactphone, $enrolldate, $classstartdate, $pass, $paymentstatus, 'PLACEHOLDER', 'PLACEHOLDER', $classcost, $amountpaid, $credentialsdate, ucfirst( $approved ), $student->comments  ],
				];
				$spreadsheet->getActiveSheet()->fromArray( $arrayData, NULL, 'A' . ( $key + 2 ) );
			}

			// Iterate through each column and set the size to "Auto Size".
			$styleArray = [
			    'font' => [
			        'bold' => true,
			    ],
			    'alignment' => [
			        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
			    ],
			    'borders' => [
			        'top' => [
			            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			        ],
			    ],
			    'fill' => [
			        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
			        'rotation' => 90,
			        'startColor' => [
			            'argb' => 'FFA0A0A0',
			        ],
			        'endColor' => [
			            'argb' => 'FFFFFFFF',
			        ],
			    ],
			];
			$worksheet = $spreadsheet->getActiveSheet();
			$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
			for ($col = 'A'; $col != $highestColumn; ++$col) {
			    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
			    $spreadsheet->getActiveSheet()->getStyle( $col . '1' )->applyFromArray($styleArray);
			}

			// Select the active sheet (or specify the sheet you want to modify)
			$worksheet = $spreadsheet->getActiveSheet();

			// Iterate through each cell in column D
			$iterator = 0;
			foreach ($worksheet->getColumnIterator('D') as $column) {

				if ( 0 === $iterator ){
				    foreach ($column->getCellIterator() as $cell) {
				        $email = $cell->getValue();
				        $cell->getHyperlink()->setUrl('mailto:' . $email);
				        $cell->setValue($email);
				    }
				}
			   	$iterator = 1;
			}

			// Iterate through each cell in column D
			$iterator = 0;
			foreach ($worksheet->getColumnIterator('L') as $column) {

				if ( 0 === $iterator ){
				    foreach ($column->getCellIterator() as $cell) {
				        $email = $cell->getValue();
				        $cell->getHyperlink()->setUrl('mailto:' . $email);
				        $cell->setValue($email);
				    }
				}
			   	$iterator = 1;
			}

			// Create the actual spreadsheet.
			$writer = new Xlsx($spreadsheet);

			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			//header('Content-Disposition: attachment; filename="'. ucwords( str_replace( ' ', '_', $classname ) ) . '_Initial_Online.xlsx' .'"');
			//$writer->save('php://output');

			$classname = str_replace( ' ', '_', $classname );
			$classname = str_replace( '/', '-', $classname );
			$filename = TEQCIDB_SPREADSHEETS_INITIAL_DIR . ucwords( $classname ) . '_Class_Roster.xlsx';
			$writer->save( $filename );

			wp_die( TEQCIDB_SPREADSHEETS_INITIAL_URL . ucwords( $classname ) . '_Class_Roster.xlsx' );	
			







			// //$inputFileName = TEQCIDB_SPREADSHEETS_INITIAL_DIR . 'TotalQCIEntriesPart1.xlsx';
			// $inputFileName = TEQCIDB_SPREADSHEETS_INITIAL_DIR . 'missed_students_during_transition_8-7-23.xlsx';

			// // Load $inputFileName to a Spreadsheet Object
			// $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);

			// $worksheet = $spreadsheet->getActiveSheet();
			// $highestColumn = $worksheet->getHighestColumn();
			// $highestRow = $worksheet->getHighestRow();  

			// for ($row = 1; $row < $highestRow + 1; $row++) { 
				
			// 	$colarray = array();
			// 	for ($col = 'A'; $col != $highestColumn; ++$col) {

			// 		array_push( $colarray, $spreadsheet->getActiveSheet()->getCell($col . $row)->getFormattedValue() );
			//   		//  $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
			//   		//  $spreadsheet->getActiveSheet()->getStyle( $col . '1' )->applyFromArray($styleArray);
			// 	}

			// 	// Create a unique student ID, and pause execution for 1 second to make sure time() remains unique!
			// 	usleep(1500000);
			// 	$exact_time = time();
			// 	$uniquestudentid = $colarray[4] . $exact_time;


			// 	/*
			// 	// Fix the unix timestamped initial training dates...
			// 	$colarray[14] = str_replace( ' 00:00:00', '', $colarray[14] );
			// 	$colarray[14] = str_replace( '00:00:00', '', $colarray[14] );
			// 	$colarray[14] = str_replace( '00:00:00 ', '', $colarray[14] );

			// 	$colarray[15] = str_replace( ' 00:00:00', '', $colarray[15] );
			// 	$colarray[15] = str_replace( '00:00:00', '', $colarray[15] );
			// 	$colarray[15] = str_replace( '00:00:00 ', '', $colarray[15] );

			// 	$colarray[16] = str_replace( ' 00:00:00', '', $colarray[16] );
			// 	$colarray[16] = str_replace( '00:00:00', '', $colarray[16] );
			// 	$colarray[16] = str_replace( '00:00:00 ', '', $colarray[16] );

			// 	*/

			// 	/* OLD ARRAY FROM MY INITIAL SPREADSHEET UPLOADS

			// 	$temp = explode( '/', $colarray[14] );
			// 	if( 1 === strlen( $temp[0] ) ){
			// 		$temp[0] = '0' . $temp[0];
			// 	}
			// 	if( 1 === strlen( $temp[1] ) ){
			// 		$temp[1] = '0' . $temp[1];
			// 	}
			// 	$colarray[14] = $temp[2] . '-' . $temp[0] . '-' . $temp[1];

			// 	$temp = explode( '/', $colarray[15] );
			// 	if( 1 === strlen( $temp[0] ) ){
			// 		$temp[0] = '0' . $temp[0];
			// 	}
			// 	if( 1 === strlen( $temp[1] ) ){
			// 		$temp[1] = '0' . $temp[1];
			// 	}
			// 	$colarray[15] = $temp[2] . '-' . $temp[0] . '-' . $temp[1];

			// 	$temp = explode( '/', $colarray[16] );
			// 	if( 1 === strlen( $temp[0] ) ){
			// 		$temp[0] = '0' . $temp[0];
			// 	}
			// 	if( 1 === strlen( $temp[1] ) ){
			// 		$temp[1] = '0' . $temp[1];
			// 	}
			// 	$colarray[16] = $temp[2] . '-' . $temp[0] . '-' . $temp[1];

			// 	// Building array to add to DB.
			// 	$db_insert_array = array(
			// 		'firstname' =>  $colarray[5],
			// 		'lastname' =>  $colarray[6],
			// 		'company' =>  $colarray[3],
			// 		'contactstreetaddress' =>  $colarray[7],
			// 		'contactcity' =>  $colarray[8],
			// 		'contactstate' => $colarray[9],
			// 		'contactzip' =>  $colarray[10],
			// 		'phoneoffice' =>  $colarray[11],
			// 		'email' => $colarray[13],
			// 		'initialtrainingdate' =>  $colarray[14],
			// 		'qcinumber' =>  $colarray[2],
			// 		'lastrefresherdate' =>  $colarray[15],
			// 		'expirationdate' => $colarray[16],
			// 		'fax' => $colarray[12],
			// 		'uniquestudentid' => $uniquestudentid,
			// 	);

			// 	// Building mask array to add to DB.
			// 	$db_mask_insert_array = array(
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 	);

			// 	*/


			// 	// BRAND-NEW STUDENT ENTRY UPLOAD INTO THE DATABASE - DIFFERENT FORMAT CINDY SENT 
			// 	$temp = explode( '/', $colarray[10] );
			// 	if( 1 === strlen( $temp[0] ) ){
			// 		$temp[0] = '0' . $temp[0];
			// 	}
			// 	if( 1 === strlen( $temp[1] ) ){
			// 		$temp[1] = '0' . $temp[1];
			// 	}
			// 	$colarray[10] = $temp[2] . '-' . $temp[0] . '-' . $temp[1];

			// 	$temp = explode( '/', $colarray[13] );
			// 	if( 1 === strlen( $temp[0] ) ){
			// 		$temp[0] = '0' . $temp[0];
			// 	}
			// 	if( 1 === strlen( $temp[1] ) ){
			// 		$temp[1] = '0' . $temp[1];
			// 	}
			// 	$colarray[13] = $temp[2] . '-' . $temp[0] . '-' . $temp[1];


			// 	// Building array to add to DB.
			// 	$db_insert_array = array(
			// 		'firstname' =>  $colarray[0],
			// 		'lastname' =>  $colarray[1],
			// 		'company' =>  $colarray[2],
			// 		'phonecell' =>  $colarray[3],
			// 		'email' =>  $colarray[4],
			// 		'contactstreetaddress' =>  $colarray[5],
			// 		'contactcity' =>  $colarray[6],
			// 		'contactstate' =>  $colarray[7],
			// 		'contactzip' =>  $colarray[8],
			// 		'qcinumber' =>  $colarray[9],
			// 		'initialtrainingdate' =>  $colarray[10],
			// 		'altcontactname' =>  $colarray[11],
			// 		'altcontactemail' =>  $colarray[12],
			// 		'expirationdate' =>  $colarray[13],
			// 		'uniquestudentid' => $uniquestudentid,
			// 	);

			// 	// Building mask array to add to DB.
			// 	$db_mask_insert_array = array(
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 	);

			// 	$table_name = $wpdb->prefix . 'teqcidb_students';
			// 	$result = $wpdb->insert(  $table_name, $db_insert_array, $db_mask_insert_array );

			// 	// Now create an actual WordPress User, if the insertion was successfull.
			// 	$wp_user_id = '';
			// 	if ( 1 === $result ) {

			// 		// Format first name to account for middle initials
			// 		$colarray[0] = str_replace( ' ', '-', $colarray[0] );
			// 		$colarray[0] = str_replace( '.', '-', $colarray[0] );
			// 		$colarray[0] = str_replace( '--', '-', $colarray[0] );
			// 		$finalname = $colarray[0] . '-' . $colarray[1];
			// 		$finalname = str_replace( '--', '-', $finalname );

			// 		$wp_user_id = wp_create_user( $finalname . '-' . $exact_time , $finalname, $colarray[4] );

			// 		// WP User object
			// 		$wp_user = new WP_User( $wp_user_id );

			// 		$display_name_change_result = wp_update_user( array( 'ID' => $wp_user_id, 'display_name' => $colarray[0] . ' ' . $colarray[1] ) );

			// 		// Set the role of this user to subscriber.
			// 		$wp_user->set_role( 'subscriber' );

			// 		// Now add the User's WordPress ID to our custom table, if user creation was successful.
			// 		if ( ! is_wp_error( $wp_user_id ) ) {
			// 			$data = array(
			// 				'wpuserid' => $wp_user_id,
			// 			);

			// 			$format = array(
			// 				'%d',
			// 			);

			// 			$table_name = $wpdb->prefix . 'teqcidb_students';
			// 			$where        = array( 'uniquestudentid' => ( $uniquestudentid ) );
			// 			$where_format = array( '%s' );
			// 			$add_wpuserid_result = $wpdb->update( $table_name, $data, $where, $format, $where_format );
			// 		}

			// 	}

				


			// 	/* TO UPDATE EXISTING ENTRIES IN THE DATABASE WITH ADDITIONAL INFOR BASED ON THE FORMAT CINDY SENT ME ON 8/15/2023
			// 	$temp = explode( '/', $colarray[5] );
			// 	if( 1 === strlen( $temp[0] ) ){
			// 		$temp[0] = '0' . $temp[0];
			// 	}
			// 	if( 1 === strlen( $temp[1] ) ){
			// 		$temp[1] = '0' . $temp[1];
			// 	}
			// 	$colarray[5] = $temp[2] . '-' . $temp[0] . '-' . $temp[1];

			// 	$temp = explode( '/', $colarray[8] );
			// 	if( 1 === strlen( $temp[0] ) ){
			// 		$temp[0] = '0' . $temp[0];
			// 	}
			// 	if( 1 === strlen( $temp[1] ) ){
			// 		$temp[1] = '0' . $temp[1];
			// 	}
			// 	$colarray[8] = $temp[2] . '-' . $temp[0] . '-' . $temp[1];

			// 	// Building array to add to DB.
			// 	$db_insert_array = array(
			// 		'lastname' =>  $colarray[9],
			// 		'contactstreetaddress' =>  $colarray[0],
			// 		'contactcity' =>  $colarray[1],
			// 		'contactstate' =>  $colarray[2],
			// 		'contactzip' =>  $colarray[3],
			// 		'qcinumber' =>  $colarray[4],
			// 		'initialtrainingdate' =>  $colarray[5],
			// 		'altcontactname' =>  $colarray[6],
			// 		'altcontactemail' =>  $colarray[7],
			// 		'expirationdate' =>  $colarray[8],
			// 	);

			// 	// Building mask array to add to DB.
			// 	$db_mask_insert_array = array(
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 	);

			// 	$table_name = $wpdb->prefix . 'teqcidb_students';
			// 	$where        = array( 'qcinumber' => ( $colarray[4] ) );
			// 	$where_format = array( '%s' );
			// 	$add_wpuserid_result = $wpdb->update( $table_name, $db_insert_array, $where, $db_mask_insert_array, $where_format );
			// 	*/
					
			//}
		

			
		}




		function teqcidb_class_form_online_refresher_spreadsheet_action_callback(){
			global $wpdb;
			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$classname = filter_var($_POST['classname'],FILTER_SANITIZE_STRING);
			$classformat = filter_var($_POST['classformat'],FILTER_SANITIZE_STRING);
			$classtype = filter_var($_POST['classtype'],FILTER_SANITIZE_STRING);
			$classstreetaddress = filter_var($_POST['classstreetaddress'],FILTER_SANITIZE_STRING);
			$classcity = filter_var($_POST['classcity'],FILTER_SANITIZE_STRING);
			$classstate = filter_var($_POST['classstate'],FILTER_SANITIZE_STRING);
			$classzip = filter_var($_POST['classzip'],FILTER_SANITIZE_STRING);
			$classstartdate = filter_var($_POST['classstartdate'],FILTER_SANITIZE_STRING);
			$classcost = filter_var($_POST['classcost'],FILTER_SANITIZE_URL);

			// Get all student history entries that are for this particular class
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );

			// Now get all full student info for each student associated with this class, and build an array.
			$student_table_name = $wpdb->prefix . 'teqcidb_students';
			$student_array = array();
			foreach( $results as $key => $registeredstudent ){
				$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );
				array_push( $student_array, $student );
			}

			// Sort our new students by last name alphabetically.
			$lastname = array_column( $student_array, 'lastname');
			array_multisort($lastname, SORT_ASC, $student_array);

			// Now start building out spreadsheet.
			$spreadsheet = new Spreadsheet();
			$arrayData = [
			    ['QCI Number', 'First Name', 'Last Name', 'Email', 'Company', 'Address', 'City', 'State', 'Zip', 'Phone', 'Alt. Contact Name', 'Alt. Contact Email Address', 'Alt. Contact Phone', 'Enroll Date', 'Completion Date', 'Pass or Fail', 'Payment Status Notes', 'Payment Method', 'Payment Date', 'Amount Due', 'Amount Collected', 'Credentials Date', 'Approved to Attend This Class?', 'General Student Comments'],
			    //['Q1',   12,   15,   21], // Here just to give me a visual reminder of how I can set cells.
			    //['Q2',   56,   73,   86], // Here just to give me a visual reminder of how I can set cells.
			    //['Q3',   52,   61,   69], // Here just to give me a visual reminder of how I can set cells.
			    //['Q4',   30,   32,    0], // Here just to give me a visual reminder of how I can set cells.
			];
			$spreadsheet->getActiveSheet()->fromArray( $arrayData, NULL, 'A1' );

			foreach( $student_array as $key => $student ) {

				$pass = '';
				$amountpaid = '';
				$paymentstatus = '';
				$enrolldate = '';
				foreach( $results as $key2 => $studenthistory ) {
					if ( $student->uniquestudentid === $studenthistory->uniquestudentid ) {

						$pass = $studenthistory->outcome;
						$amountpaid = $studenthistory->amountpaid;
						$enrolldate = $studenthistory->enrollmentdate;

						if ( 'paidinfull' === $studenthistory->paymentstatus ) {
							$studenthistory->paymentstatus = 'Paid in Full';
						}
						$paymentstatus = $studenthistory->paymentstatus;
					}
				}

				if ( ( '' !== $credentialsdate ) && ( null !== $credentialsdate ) && ( 'null' !== $credentialsdate ) ) {
					$credentialsdate = date('m-d-Y', strtotime( $credentialsdate ) );
				}

				$arrayData = [
				    [$student->qcinumber, $student->firstname, $student->lastname, $student->email, $student->company, $student->contactstreetaddress, $student->contactcity, $student->contactstate, $student->contactzip, $student->phonecell, $student->altcontactname, $student->altcontactemail, $student->altcontactphone, $enrolldate, $classstartdate, $pass, $paymentstatus, 'PLACEHOLDER', 'PLACEHOLDER', $classcost, $amountpaid, $credentialsdate, ucfirst( $approved ), $student->comments  ],
				];
				$spreadsheet->getActiveSheet()->fromArray( $arrayData, NULL, 'A' . ( $key + 2 ) );
			}

			// Iterate through each column and set the size to "Auto Size".
			$styleArray = [
			    'font' => [
			        'bold' => true,
			    ],
			    'alignment' => [
			        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
			    ],
			    'borders' => [
			        'top' => [
			            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			        ],
			    ],
			    'fill' => [
			        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
			        'rotation' => 90,
			        'startColor' => [
			            'argb' => 'FFA0A0A0',
			        ],
			        'endColor' => [
			            'argb' => 'FFFFFFFF',
			        ],
			    ],
			];
			$worksheet = $spreadsheet->getActiveSheet();
			$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
			for ($col = 'A'; $col != $highestColumn; ++$col) {
			    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
			    $spreadsheet->getActiveSheet()->getStyle( $col . '1' )->applyFromArray($styleArray);

			}

			// Create the actual spreadsheet.
			$writer = new Xlsx($spreadsheet);

			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			//header('Content-Disposition: attachment; filename="'. ucwords( str_replace( ' ', '_', $classname ) ) . '_Initial_Online.xlsx' .'"');
			//$writer->save('php://output');

			$filename = TEQCIDB_SPREADSHEETS_REFRESHER_DIR . ucwords( str_replace( ' ', '_', $classname ) ) . '_Refresher_Online.xlsx';
			$writer->save( $filename );

			wp_die( TEQCIDB_SPREADSHEETS_REFRESHER_URL . ucwords( str_replace( ' ', '_', $classname ) ) . '_Refresher_Online.xlsx' );	
		}

		function teqcidb_download_report_action_callback(){
			global $wpdb;

			$query = stripslashes( $_POST['query'] );
			$results = $wpdb->get_results( $query );

			// Now start building out spreadsheet.
			$spreadsheet = new Spreadsheet();
			$arrayData = [
			    ['QCI Number', 'First Name', 'Last Name', 'Email', 'Company', 'Address', 'City', 'State', 'Zip', 'Phone', 'Alt. Contact Name', 'Alt. Contact Email Address', 'Alt. Contact Phone', 'Initial Training Date', 'Last Refresher Date',  'Expiration Date', 'General Student Comments'],
			    //['Q1',   12,   15,   21], // Here just to give me a visual reminder of how I can set cells.
			    //['Q2',   56,   73,   86], // Here just to give me a visual reminder of how I can set cells.
			    //['Q3',   52,   61,   69], // Here just to give me a visual reminder of how I can set cells.
			    //['Q4',   30,   32,    0], // Here just to give me a visual reminder of how I can set cells.
			];
			$spreadsheet->getActiveSheet()->fromArray( $arrayData, NULL, 'A1' );

			foreach( $results as $key => $student ) {

				// Formatting dates.
				$student->expirationdate = date("m-d-Y", strtotime($student->expirationdate));
				$student->lastrefresherdate = date("m-d-Y", strtotime($student->lastrefresherdate));
				$student->initialtrainingdate = date("m-d-Y", strtotime($student->initialtrainingdate));

				$arrayData = [
				    [$student->qcinumber, $student->firstname, $student->lastname, $student->email, $student->company, $student->contactstreetaddress, $student->contactcity, $student->contactstate, $student->contactzip, $student->phonecell, $student->altcontactname, $student->altcontactemail, $student->altcontactphone, $student->initialtrainingdate, $student->lastrefresherdate, $student->expirationdate, $student->comments  ],
				];
				$spreadsheet->getActiveSheet()->fromArray( $arrayData, NULL, 'A' . ( $key + 2 ) );
			}

			// Iterate through each column and set the size to "Auto Size".
			$styleArray = [
			    'font' => [
			        'bold' => true,
			    ],
			    'alignment' => [
			        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
			    ],
			    'borders' => [
			        'top' => [
			            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			        ],
			    ],
			    'fill' => [
			        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
			        'rotation' => 90,
			        'startColor' => [
			            'argb' => 'FFA0A0A0',
			        ],
			        'endColor' => [
			            'argb' => 'FFFFFFFF',
			        ],
			    ],
			];
			$worksheet = $spreadsheet->getActiveSheet();
			$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
			for ($col = 'A'; $col != $highestColumn; ++$col) {
			    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
			    $spreadsheet->getActiveSheet()->getStyle( $col . '1' )->applyFromArray($styleArray);

			}

			// Create the actual spreadsheet.
			$writer = new Xlsx($spreadsheet);

			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			//header('Content-Disposition: attachment; filename="'. ucwords( str_replace( ' ', '_', $classname ) ) . '_Initial_Online.xlsx' .'"');
			//$writer->save('php://output');
			$time = time();
			$filename = TEQCIDB_SPREADSHEETS_REFRESHER_DIR . $time .'qcinumbers_report_tab.xlsx';
			$writer->save( $filename );

			wp_die( TEQCIDB_SPREADSHEETS_REFRESHER_URL . $time . 'qcinumbers_report_tab.xlsx' );


				

		}

		function teqcidb_credits_list_action_callback(){
			global $wpdb;

			$query = stripslashes( $_POST['query'] );
			$results = $wpdb->get_results( $query );

			// Now start building out spreadsheet.
			$spreadsheet = new Spreadsheet();
			$arrayData = [
			    ['QCI Number', 'First Name', 'Last Name', 'Email', 'Phone', 'Alt. Contact Name', 'Alt. Contact Email Address', 'Alt. Contact Phone', 'Class Name', 'Payment Status', 'Amount Paid', 'Enrollment Date', 'Transaction ID'],
			    //['Q1',   12,   15,   21], // Here just to give me a visual reminder of how I can set cells.
			    //['Q2',   56,   73,   86], // Here just to give me a visual reminder of how I can set cells.
			    //['Q3',   52,   61,   69], // Here just to give me a visual reminder of how I can set cells.
			    //['Q4',   30,   32,    0], // Here just to give me a visual reminder of how I can set cells.
			];
			$spreadsheet->getActiveSheet()->fromArray( $arrayData, NULL, 'A1' );

			foreach( $results as $key => $studenthistory ) {

				$indiv_students = $wpdb->get_row( "SELECT * FROM " );
				// Get all student history entries that are for this particular class
				$table_name = $wpdb->prefix . 'teqcidb_students';
				$indiv_student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $studenthistory->uniquestudentid ) );

				$arrayData = [
				    [$indiv_student->qcinumber, $indiv_student->firstname, $indiv_student->lastname, $indiv_student->email, $indiv_student->phonecell, $indiv_student->altcontactname, $indiv_student->altcontactemail, $indiv_student->altcontactphone, $studenthistory->classname, $studenthistory->paymentstatus, $studenthistory->amountpaid, $studenthistory->enrollmentdate, $studenthistory->transactionid ],
				];
				$spreadsheet->getActiveSheet()->fromArray( $arrayData, NULL, 'A' . ( $key + 2 ) );
			}

			// Iterate through each column and set the size to "Auto Size".
			$styleArray = [
			    'font' => [
			        'bold' => true,
			    ],
			    'alignment' => [
			        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
			    ],
			    'borders' => [
			        'top' => [
			            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			        ],
			    ],
			    'fill' => [
			        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
			        'rotation' => 90,
			        'startColor' => [
			            'argb' => 'FFA0A0A0',
			        ],
			        'endColor' => [
			            'argb' => 'FFFFFFFF',
			        ],
			    ],
			];
			$worksheet = $spreadsheet->getActiveSheet();
			$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
			for ($col = 'A'; $col != $highestColumn; ++$col) {
			    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
			    $spreadsheet->getActiveSheet()->getStyle( $col . '1' )->applyFromArray($styleArray);

			}

			// Create the actual spreadsheet.
			$writer = new Xlsx($spreadsheet);

			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			//header('Content-Disposition: attachment; filename="'. ucwords( str_replace( ' ', '_', $classname ) ) . '_Initial_Online.xlsx' .'"');
			//$writer->save('php://output');

			$filename = TEQCIDB_SPREADSHEETS_REFRESHER_DIR . 'credits_list_report_tab.xlsx';
			$writer->save( $filename );

			wp_die( TEQCIDB_SPREADSHEETS_REFRESHER_URL . 'credits_list_report_tab.xlsx' );


				

		}
















		
	}
endif;
