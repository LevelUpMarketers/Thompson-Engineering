<?php
/**
 * Class TEQciDb_Ajax_Functions - class-teqcidbplugin-ajax-functions.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes
 * @version  6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TEQciDb_Ajax_Functions', false ) ) :
	/**
	 * TEQciDb_Ajax_Functions class. Here we'll do things like enqueue scripts/css, set up menus, etc.
	 */
	class TEQciDb_Ajax_Functions {

		/**
		 * Class Constructor - Simply calls the Translations
		 */
		public function __construct() {


		}


		function teqcidb_add_new_student_action_callback(){
			global $wpdb;
			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$phoneoffice = filter_var($_POST['phoneoffice'],FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
			$studentimage1 = filter_var($_POST['studentimage1'],FILTER_SANITIZE_URL);
			$studentimage2 = filter_var($_POST['studentimage2'],FILTER_SANITIZE_URL);
			$initialtrainingdate = filter_var($_POST['initialtrainingdate'],FILTER_SANITIZE_STRING);
			$lastrefresherdate = filter_var($_POST['lastrefresherdate'],FILTER_SANITIZE_STRING);
			$qcinumber = filter_var($_POST['qcinumber'],FILTER_SANITIZE_STRING);
			$comments = filter_var($_POST['comments'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$fax = filter_var($_POST['fax'],FILTER_SANITIZE_STRING);
			$superstring = filter_var($_POST['superstring'],FILTER_SANITIZE_STRING);
			$associations = filter_var($_POST['associations'],FILTER_SANITIZE_STRING);
			$altcontactname = filter_var($_POST['altcontactname'],FILTER_SANITIZE_STRING);
			$altcontactemail = filter_var($_POST['altcontactemail'],FILTER_SANITIZE_STRING);
			$altcontactphone = filter_var($_POST['altcontactphone'],FILTER_SANITIZE_STRING);

			// Make checks to see if we have a student in the DB with this exact email and/or QCI number already.
			$table_name = $wpdb->prefix . 'teqcidb_students';
			$qci_flag = false;
			$email_flag = false;

			// Check for duplicate QCI number, if one was provided.
			if ( '' !== $qcinumber && null !== $qcinumber ) {
				
				$qci_number_check = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE qcinumber = %s", $qcinumber ) );
				
				if( null !== $qci_number_check ){
					$qci_flag = true;
				}
			}
			
			// Check for duplicate email.
			$email_check = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE email = %s", $email ) );

			if( null !== $email_check ){
				$email_flag = true;
			}

			if ( $qci_flag || $email_flag ) {

				if ( $qci_flag ){
					wp_die( 'Whoops! There\'s already a student registered with this QCI number!' );
				}

				if ( $email_flag ){
					wp_die( 'Whoops! There\'s already a student registered with this Email Address!' );
				} 

			} else {

				// Create a unique student ID
				$uniquestudentid = $email . time();

				if ( false !== stripos( $lastname, '&#39;' ) ){
					$lastname = str_replace('&#39;', "'", $lastname );
				}

				if ( false !== stripos( $lastname, "\\" ) ){
					$lastname = str_replace('\\', "", $lastname );
				}

				if ( false !== stripos( $altcontactname, '&#39;' ) ){
					$altcontactname = str_replace('&#39;', "'", $altcontactname );
				}

				if ( false !== stripos( $altcontactname, "\\" ) ){
					$altcontactname = str_replace('\\', "", $altcontactname );
				}

				// Building array to add to DB.
				$db_insert_array = array(
					'firstname' =>  $firstname,
					'lastname' =>  $lastname,
					'company' =>  $company,
					'contactstreetaddress' =>  $contactstreetaddress,
					'contactcity' =>  $contactcity,
					'contactstate' =>  $contactstate,
					'contactzip' =>  $contactzip,
					'phonecell' =>  $phonecell,
					'phoneoffice' =>  $phoneoffice,
					'email' =>  $email,
					'studentimage1' =>  $studentimage1,
					'studentimage2' =>  $studentimage2,
					'initialtrainingdate' =>  $initialtrainingdate,
					'lastrefresherdate' =>  $lastrefresherdate,
					'qcinumber' =>  $qcinumber,
					'comments' =>  $comments,
					'expirationdate' => $expirationdate,
					'fax' => $fax,
					'uniquestudentid' => $uniquestudentid,
					'associations' => $associations,
					'altcontactname' => $altcontactname,
					'altcontactphone' => $altcontactphone,
					'altcontactemail' => $altcontactemail,
					'newregistrantflag' => 'true',
				);

				// Building mask array to add to DB.
				$db_mask_insert_array = array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				);

				$result = $wpdb->insert(  $table_name, $db_insert_array, $db_mask_insert_array );
				$lastid = $wpdb->insert_id;

				// Now create an actual WordPress User, if the insertion was successfull.
				$wp_user_id = '';
				if ( 1 === $result ) {

					$wp_user_id = wp_create_user( $firstname . '-' . $lastname . '-' . time() , $firstname . '-' . $lastname, $email );

					// WP User object
					$wp_user = new WP_User( $wp_user_id );

					$display_name_change_result = wp_update_user( array( 'ID' => $wp_user_id, 'display_name' => $firstname . ' ' . $lastname ) );

					// Set the role of this user to subscriber.
					$wp_user->set_role( 'subscriber' );

					// Now add the User's WordPress ID to our custom table, if user creation was successful.
					if ( ! is_wp_error( $wp_user_id ) ) {
						$data = array(
							'wpuserid' => $wp_user_id,
						);

						$format = array(
							'%s',
						);

						$table_name = $wpdb->prefix . 'teqcidb_students';
						$where        = array( 'ID' => ( $lastid ) );
						$where_format = array( '%d' );
						$add_wpuserid_result = $wpdb->update( $table_name, $data, $where, $format, $where_format );
					}

				}


				// Work on structuring data to add historical Class info for this student.
				$superstring = explode( ';', $superstring );
				foreach( $superstring as $string ){

					if ( false !== stripos( $string, '-' ) ) {

						$subarray = explode( '-', $string );
						$subarray[7] = str_replace( '/', '-', $subarray[7] );
						$subarray[8] = str_replace( '/', '-', $subarray[8] );

						$db_insert_array = array(
							'wpuserid' => $wp_user_id,
							'classname' => $subarray[0],
							'uniquestudentid' => $uniquestudentid,
							'uniqueclassid' =>  $subarray[1],
							'registered' =>  $subarray[2],
							'attended' =>  $subarray[3],
							'outcome' =>  $subarray[4],
							'paymentstatus' =>  $subarray[5],
							'amountpaid' =>  $subarray[6],
							'enrollmentdate' =>  $subarray[7],
							'credentialsdate' =>  $subarray[8],
						);

						$db_mask_insert_array = array(
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
						);

						$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
						$result = $wpdb->insert(  $table_name, $db_insert_array, $db_mask_insert_array );
					}
				}

				wp_die( $result );
			}	
		}





		function teqcidb_class_form_roster_action_callback(){
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

			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );
			error_log( print_r( $results, true ) );

			// If in-person, provide address...
			$inpersonaddress = '';
			if ( 'in-person' === $classformat ) {
				$inpersonaddress = '
					<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
						' . $classstreetaddress . '
					</div>
					<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
						' . $classcity . ', ' . $classstate . ' ' . $classzip . '
					</div>';
			}

			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div class="teqcidb-top-level-colorbox-holder" id="teqcidb-top-level-colorbox-holder-id">
					<div class="teqcidb-top-level-colorbox-holder-header-wrapper">
						<div class="teqcidb-top-level-colorbox-holder-wrapper teqcidb-top-level-colorbox-holder-class-title-wrapper">
							<div class="teqcidb-top-level-colorbox-holder-class-title">
								Class Roster for ' . $classname . '
								<img class="teqcidb-te-logo-for-print" src="' . TEQCIDB_ROOT_IMG_URL . 'telogo.png" />
							</div>
						</div>
						<div class="teqcidb-top-level-colorbox-holder-wrapper teqcidb-top-level-colorbox-holder-class-subtitle-wrapper">
							<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
								' . ucfirst( $classtype ) . ' | ' . ucfirst( $classformat ) . ' | ' . date('m-d-Y', strtotime( $classstartdate ) ) . '
							</div>
							' . $inpersonaddress . '
						</div>
					</div>
					<div class="teqcidb-top-level-colorbox-inner-holder-instructions">Sign below your name, check your information, and print any corrections & additions</div>
					<div class="teqcidb-top-level-colorbox-inner-holder">';

			$student_table_name = $wpdb->prefix . 'teqcidb_students';
			$student_array = array();
			foreach( $results as $key => $registeredstudent ){

				$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );
				array_push( $student_array, $student );

			}

			$lastname = array_column( $student_array, 'lastname');
			array_multisort($lastname, SORT_ASC, $student_array);

			$pagecount = 0;
			foreach( $results as $key2 => $registeredstudent ){

				// First, if the student is approved/registered for the class...
				if ( 'yes' === $registeredstudent->registered ) {

					// Now get the actual student info
					//$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );

					$printpagebreak = '';
					if( ( 0 == $key2 % 3 ) && ( 0 != $key2 ) ){
						$pagecount++;
  						$printpagebreak = '
  						<br/><span class="teqcdib-print-page-span" style="font-size:10px;">Page ' . $pagecount . '</span>
  						<div class="pagebreak"></div>
  						<div class="teqcidb-top-level-colorbox-holder-header-wrapper">
							<div class="teqcidb-top-level-colorbox-holder-wrapper teqcidb-top-level-colorbox-holder-class-title-wrapper">
								<div class="teqcidb-top-level-colorbox-holder-class-title">
									Class Roster for ' . $classname . '
									<img class="teqcidb-te-logo-for-print" src="' . TEQCIDB_ROOT_IMG_URL . 'telogo.png" />
								</div>
							</div>
							<div class="teqcidb-top-level-colorbox-holder-wrapper teqcidb-top-level-colorbox-holder-class-subtitle-wrapper">
								<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
									' . ucfirst( $classtype ) . ' | ' . ucfirst( $classformat ) . ' | ' . date('m-d-Y', strtotime( $classstartdate ) ) . '
								</div>
								' . $inpersonaddress . '
							</div>
						</div>
						<div class="teqcidb-top-level-colorbox-inner-holder-instructions">Sign below your name, check your information, and print any corrections & additions</div>';
					} else {
						$printpagebreak = '';
					}




					if ( ( '' === $student_array[$key2]->qcinumber ) || ( null === $student_array[$key2]->qcinumber ) ) {
						$student_array[$key2]->qcinumber = 'Not Yet Assigned';
					}


					$html = $html . $printpagebreak . '
						<div class="teqcidb-top-level-colorbox-indiv-holder">
							<div class="teqcidb-top-level-colorbox-indiv-actual">
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Name </span><span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->lastname . ', ' . $student_array[$key2]->firstname . '</span>
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold teqcidb-top-level-colorbox-row-span-bold-signature">Signature:<span class="teqcidb-top-level-colorbox-row-span-bold-signature-line"></span></span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">QCI Number </span><span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->qcinumber . ' </span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Company </span><span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->company . ' </span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Payment </span><span class="teqcidb-top-level-colorbox-row-span">' . $registeredstudent->amountpaid . ' </span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Email </span><span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->email . ' </span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<div class="teqcidb-top-level-colorbox-indiv-row-for-inlineblock">
										<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Phone </span>
									</div>
									<div class="teqcidb-top-level-colorbox-indiv-row-for-inlineblock">
										<span class="teqcidb-top-level-colorbox-row-span">Cell: ' . $student_array[$key2]->phonecell . ' </span>
										<span class="teqcidb-top-level-colorbox-row-span">Office: ' . $student_array[$key2]->phoneoffice . ' </span>
									</div>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<div class="teqcidb-top-level-colorbox-indiv-row-for-inlineblock">
										<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Address </span>
									</div>
									<div class="teqcidb-top-level-colorbox-indiv-row-for-inlineblock">
										<span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->contactstreetaddress . ' </span>
										<span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->contactcity . ', ' . $student_array[$key2]->contactstate . ' </span>
										<span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->contactzip . ' </span>
									</div>
								</div>
							</div>
						</div>';
				}
			}

			$html = $html . '
					</div>
				</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Roster!</button>
			</div>';


			wp_die( $html );	
		}

		


		function teqcidb_class_form_certification_initial_inperson_action_callback(){
			global $wpdb;


			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$phoneoffice = filter_var($_POST['phoneoffice'],FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
			$initialtrainingdate = filter_var($_POST['initialtrainingdate'],FILTER_SANITIZE_STRING);
			$lastrefresherdate = filter_var($_POST['lastrefresherdate'],FILTER_SANITIZE_STRING);
			$qcinumber = filter_var($_POST['qcinumber'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$associations = filter_var($_POST['associations'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);

			

			

			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div>
					<div class="teqcidb-top-level-colorbox-inner-holder teqcidb-top-level-colorbox-inner-holder-student-cert">
					<div class="teqcidb-top-level-colorbox-inner-holder-student-cert-background">
						<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-student-cert-background.png" />
					</div>
						<div class="teqcidb-training-certificate-topholder teqcidb-initial-inperson-cert-topholder">
							<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-1">QCI Training Program</p>
							<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-2">Certificate of Completion</p>
							<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-3">is hereby granted to:</p>
							<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-4">' . $firstname . ' ' . stripslashes($lastname) . '</p>
							<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-5">' . stripslashes($company) . '</p>
							<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p teqcidb-initial-inperson-cert-p-jaketest" id="teqcidb-training-certificate-p-6">for satisfactory completion of 8 instructional hours</p>
						</div>
						<div class="teqcidb-training-certificate-leftrightholder">
							<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-imageholder">
								<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
								<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" />
							</div>
							<div class="teqcidb-training-certificate-div teqcidb-initial-inperson-cert-holder" id="teqcidb-training-certificate-date-instructor-holder">
								<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-7">Initial Training Class</p>
								<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-8">' .  date('m-d-Y', strtotime( $initialtrainingdate ) ) . '</p>
								<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-9">Instructor Name(s)</p>
								<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-10">' . $instructors . '</p>
							</div>
						</div>
						<div class="teqcidb-training-certificate-bottomholder">
							<div>
								<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-11">This certificate confers eight (8.0) professional development hours (PDHs) to students who require credits for licenses or certifications. Such PDHs are subject to the qualifying requirements of the licensing or certifying organization.</p>
							</div>
							<div>
								<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-12">QCI NO: ' . $qcinumber . '</p>
								<p class="teqcidb-training-certificate-p teqcidb-initial-inperson-cert-p" id="teqcidb-training-certificate-p-13">Expires: ' .  date('m-d-Y', strtotime( $expirationdate ) ) . '</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
			</div>';


			wp_die( $html );	
		}

		function teqcidb_class_form_certification_refresher_inperson_action_callback(){
			global $wpdb;


			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$phoneoffice = filter_var($_POST['phoneoffice'],FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
			$initialtrainingdate = filter_var($_POST['initialtrainingdate'],FILTER_SANITIZE_STRING);
			$lastrefresherdate = filter_var($_POST['lastrefresherdate'],FILTER_SANITIZE_STRING);
			$qcinumber = filter_var($_POST['qcinumber'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$associations = filter_var($_POST['associations'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);

			

			

			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div>
					<div class="teqcidb-top-level-colorbox-inner-holder teqcidb-top-level-colorbox-inner-holder-student-cert">
					<div class="teqcidb-top-level-colorbox-inner-holder-student-cert-background">
						<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-student-cert-background.png" />
					</div>
						<div class="teqcidb-training-certificate-topholder">
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-1">QCI Training Program</p>
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-2">Certificate of Completion</p>
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-3">is hereby granted to:</p>
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-4">' . $firstname . ' ' . stripslashes($lastname) . '</p>
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-5">' . stripslashes($company) . '</p>
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-6">for satisfactory completion of 4 instructional hours</p>
						</div>
						<div class="teqcidb-training-certificate-leftrightholder">
							<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-imageholder">
								<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
								<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" />
							</div>
							<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-date-instructor-holder">
								<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-7">Refresher Training Class</p>
								<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-8">' .  date('m-d-Y', strtotime( $lastrefresherdate ) ) . '</p>
								<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-9">Instructor Names</p>
								<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-10">' . $instructors . '</p>
							</div>
						</div>
						<div class="teqcidb-training-certificate-bottomholder">
							<div>
								<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-11">This certificate confers four (4.0) professional development hours (PDHs) to students who require credits for licenses or certifications. Such PDHs are subject to the qualifying requirements of the licensing or certifying organization.</p>
							</div>
							<div>
								<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-12">QCI NO: ' . $qcinumber . '</p>
								<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-13">Expires: ' .  date('m-d-Y', strtotime( $expirationdate ) ) . '</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
			</div>';


			wp_die( $html );	
		}


		function teqcidb_class_form_certification_initial_online_action_callback(){
			global $wpdb;


			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$phoneoffice = filter_var($_POST['phoneoffice'],FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
			$initialtrainingdate = filter_var($_POST['initialtrainingdate'],FILTER_SANITIZE_STRING);
			$lastrefresherdate = filter_var($_POST['lastrefresherdate'],FILTER_SANITIZE_STRING);
			$qcinumber = filter_var($_POST['qcinumber'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$associations = filter_var($_POST['associations'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);

			

			

			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div class="teqcidb-top-level-colorbox-inner-holder teqcidb-top-level-colorbox-inner-holder-student-cert">
				<div class="teqcidb-top-level-colorbox-inner-holder-student-cert-background">
					<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-student-cert-background.png" />
				</div>
					<div class="teqcidb-training-certificate-topholder">
						<div class="teqcidb-training-certificate-topholder-row1">
							<img class="teqcidb-training-certificate-topholder-row1-imgleft" src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-1">QCI Training Program</p>
							<img class="teqcidb-training-certificate-topholder-row1-imgright" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" />
						</div>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-2">Certificate of Completion</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-3">is hereby granted to:</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-4">' . $firstname . ' ' . stripslashes($lastname) . '</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-5">' . stripslashes($company) . '</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-6">for satisfactory completion of</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-7">Online Initial <br/>Training</p>
					</div>
					<div class="teqcidb-training-certificate-leftrightholder">
						<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-date-instructor-holder">
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-10">QCI No. ' . $qcinumber . '</p>
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-8">Expires ' .  date('m/d/Y', strtotime( $expirationdate ) ) . '</p>
						</div>
					</div>
					<div class="teqcidb-training-certificate-bottomholder teqcidb-training-certificate-initial-online-bottomholder">
						<div>
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-11">This certificate confers six (6.0) professional development hours (PDHs) to students who require credits for licenses or certifications.<br/>Such PDHs are subject to the qualifying requirements of the licensing or certifying organization.</p>
						</div>
					</div>
				</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
			</div>';


			wp_die( $html );	
		}

		function teqcidb_class_form_certification_refresher_online_action_callback(){
			global $wpdb;


			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$phoneoffice = filter_var($_POST['phoneoffice'],FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
			$initialtrainingdate = filter_var($_POST['initialtrainingdate'],FILTER_SANITIZE_STRING);
			$lastrefresherdate = filter_var($_POST['lastrefresherdate'],FILTER_SANITIZE_STRING);
			$qcinumber = filter_var($_POST['qcinumber'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$associations = filter_var($_POST['associations'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);

			

			

			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div class="teqcidb-top-level-colorbox-inner-holder teqcidb-top-level-colorbox-inner-holder-student-cert">
				<div class="teqcidb-top-level-colorbox-inner-holder-student-cert-background">
					<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-student-cert-background.png" />
				</div>
					<div class="teqcidb-training-certificate-topholder">
						<div class="teqcidb-training-certificate-topholder-row1">
							<img class="teqcidb-training-certificate-topholder-row1-imgleft" src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-1">QCI Training Program</p>
							<img class="teqcidb-training-certificate-topholder-row1-imgright" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" />
						</div>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-2">Certificate of Completion</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-3">is hereby granted to:</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-4">' . $firstname . ' ' . stripslashes($lastname) . '</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-5">' . stripslashes($company) . '</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-6">for satisfactory completion of</p>
						<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-7">Online Refresher <br/>Training</p>
					</div>
					<div class="teqcidb-training-certificate-leftrightholder">
						<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-date-instructor-holder">
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-10">QCI No. ' . $qcinumber . '</p>
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-8">Expires ' .  date('m/d/Y', strtotime( $expirationdate ) ) . '</p>
						</div>
					</div>
					<div class="teqcidb-training-certificate-bottomholder teqcidb-training-certificate-initial-online-bottomholder">
						<div>
							<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-11">This certificate confers four (4.0) professional development hours (PDHs) to students who require credits for licenses or certifications.<br/>Such PDHs are subject to the qualifying requirements of the licensing or certifying organization.</p>
						</div>
					</div>
				</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
			</div>';


			wp_die( $html );	
		}

		function teqcidb_class_form_certification_all_initial_inperson_action_callback(){
			global $wpdb;

			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$id = filter_var($_POST['id'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);
			$instructors = ltrim( $instructors, ',' );
			$instructors = str_replace( ',', ', ', $instructors );
			
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );

			// Grab the class start date.
			$class_table_name = $wpdb->prefix . 'teqcidb_classes';
			$classresults = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $class_table_name WHERE uniqueclassid = %s", $uniqueclassid ) );


			// formatted class start date
			$inputDate = $classresults->classstartdate;
			$parts = explode('-', $inputDate);
			$formattedDate = $parts[1] . '-' . $parts[2] . '-' . $parts[0];


			$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing">';
			foreach ( $results as $key => $value ) {

				//if ( ( 'passed' === $value->outcome ) && ( 'yes' === $value->registered )  ) {

					$table_name = $wpdb->prefix . 'teqcidb_students';
					$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $value->uniquestudentid ) );

					

					$html = $html . '
						<div>
							<div class="teqcidb-top-level-colorbox-inner-holder teqcidb-top-level-colorbox-inner-holder-student-cert">
							<div class="teqcidb-top-level-colorbox-inner-holder-student-cert-background">
								<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-student-cert-background.png" />
							</div>
								<div class="teqcidb-training-certificate-topholder">
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-1">QCI Training Program</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-2">Certificate of Completion</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-3">is hereby granted to:</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-4">' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-5">' . stripslashes($student->company) . '</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-6">for satisfactory completion of 8 instructional hours</p>
								</div>
								<div class="teqcidb-training-certificate-leftrightholder">
									<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-imageholder">
										<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
										<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" />
									</div>
									<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-date-instructor-holder">
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-7">Initial Training Class</p>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-8">' .  $formattedDate . '</p>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-9">Instructor Name(s)</p>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-10">' . $instructors . '</p>
									</div>
								</div>
								<div class="teqcidb-training-certificate-bottomholder">
									<div>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-11">This certificate confers eight (8.0) professional development hours (PDHs) to students who require credits for licenses or certifications. Such PDHs are subject to the qualifying requirements of the licensing or certifying organization.</p>
									</div>
									<div>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-12">QCI NO: ' . $student->qcinumber . '</p>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-13">Expires: ' .  date('m-d-Y', strtotime( $student->expirationdate ) ) . '</p>
									</div>
								</div>
							</div>
						</div>
						<div class="pagebreak"></div>';
				//}
			}

			$html = $html . '
				</div>
				<div class="teqcidb-top-level-colorbox-print-holder">
					<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
				</div>';


			wp_die( $html );	
		}

		function teqcidb_class_form_certification_all_refresher_inperson_action_callback(){
			global $wpdb;

			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$id = filter_var($_POST['id'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);
			$instructors = ltrim( $instructors, ',' );
			$instructors = str_replace( ',', ', ', $instructors );
			
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );

			$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing">';
			foreach ( $results as $key => $value ) {

				if ( ( 'passed' === $value->outcome ) && ( 'yes' === $value->registered )  ) {

					$table_name = $wpdb->prefix . 'teqcidb_students';
					$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $value->uniquestudentid ) );

					$html = $html . '
						<div>
							<div class="teqcidb-top-level-colorbox-inner-holder teqcidb-top-level-colorbox-inner-holder-student-cert">
							<div class="teqcidb-top-level-colorbox-inner-holder-student-cert-background">
								<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-student-cert-background.png" />
							</div>
								<div class="teqcidb-training-certificate-topholder">
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-1">QCI Training Program</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-2">Certificate of Completion</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-3">is hereby granted to:</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-4">' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-5">' . stripslashes($student->company) . '</p>
									<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-6">for satisfactory completion of 4 instructional hours</p>
								</div>
								<div class="teqcidb-training-certificate-leftrightholder">
									<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-imageholder">
										<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
										<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" />
									</div>
									<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-date-instructor-holder">
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-7">Refresher Training Class</p>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-8">' .  date('m-d-Y', strtotime( $student->lastrefresherdate ) ) . '</p>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-9">Instructor Names</p>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-10">' . $instructors . '</p>
									</div>
								</div>
								<div class="teqcidb-training-certificate-bottomholder">
									<div>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-11">This certificate confers four (4.0) professional development hours (PDHs) to students who require credits for licenses or certifications. Such PDHs are subject to the qualifying requirements of the licensing or certifying organization.</p>
									</div>
									<div>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-12">QCI NO: ' . $student->qcinumber . '</p>
										<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-13">Expires: ' .  date('m-d-Y', strtotime( $student->expirationdate ) ) . '</p>
									</div>
								</div>
							</div>
						</div>
						<div class="pagebreak"></div>';
				}
			}

			$html = $html . '
				</div>
				<div class="teqcidb-top-level-colorbox-print-holder">
					<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
				</div>';


			wp_die( $html );	
		}

		function teqcidb_class_form_certification_all_initial_online_action_callback(){
			global $wpdb;

			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$id = filter_var($_POST['id'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);
			$instructors = ltrim( $instructors, ',' );
			$instructors = str_replace( ',', ', ', $instructors );
			$fromdate = filter_var($_POST['fromdate'],FILTER_SANITIZE_STRING);
			$todate = filter_var($_POST['todate'],FILTER_SANITIZE_STRING);
			
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';

			// to record which students have been added so we don't have multiple certs for the same student
			$alreadyadded = array();

			//if( ( ( '' === $fromdate ) || ( '' === $todate ) ) ){
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );
			//} else {
				//$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s AND expirationdate >= %s AND expirationdate <= %s", $uniqueclassid, $fromdate, $todate ) );
			//}

			$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing">';
			foreach ( $results as $key => $value ) {

				if ( ( 'passed' === $value->outcome ) && ( 'yes' === $value->registered )  ) {



					$table_name = $wpdb->prefix . 'teqcidb_students';
					$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s AND expirationdate >= %s AND expirationdate <= %s", $value->uniquestudentid, $fromdate, $todate ) );

					if ( ! in_array( $value->uniquestudentid, $alreadyadded ) ) {

						// Next 3 lines trys to get the most recent entry in the Studenthistory table for this specific student, regradless of what kind of class it is. If that most recent student history entry doesn't match this unqiue class id, then this student isn't displayed.
						$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
						$all_students_history = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s ORDER BY ID DESC", $student->uniquestudentid ) );
						if ( ( 0 < sizeof( $all_students_history ) ) && ( $uniqueclassid === $all_students_history[0]->uniqueclassid ) ) {

							if ( ( null !== $student->qcinumber ) && ( '' !== $student->qcinumber ) ) {

								$html = $html . '
								<div>
									<div class="teqcidb-top-level-colorbox-inner-holder teqcidb-top-level-colorbox-inner-holder-student-cert">
									<div class="teqcidb-top-level-colorbox-inner-holder-student-cert-background">
										<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-student-cert-background.png" />
									</div>
										<div class="teqcidb-training-certificate-topholder">
											<div class="teqcidb-training-certificate-topholder-row1">
												<img class="teqcidb-training-certificate-topholder-row1-imgleft" src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
												<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-1">QCI Training Program</p>
												<img class="teqcidb-training-certificate-topholder-row1-imgright" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" />
											</div>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-2">Certificate of Completion</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-3">is hereby granted to:</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-4">' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-5">' . stripslashes($student->company) . '</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-6">for satisfactory completion of</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-7">Online Initial <br/>Training</p>
										</div>
										<div class="teqcidb-training-certificate-leftrightholder">
											<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-date-instructor-holder">
												<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-10">QCI No. ' . $student->qcinumber . '</p>
												<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-8">Expires ' .  date('m/d/Y', strtotime( $student->expirationdate ) ) . '</p>
											</div>
										</div>
										<div class="teqcidb-training-certificate-bottomholder teqcidb-training-certificate-initial-online-bottomholder">
											<div>
												<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-11">This certificate confers six (6.0) professional development hours (PDHs) to students who require credits for licenses or certifications.<br/>Such PDHs are subject to the qualifying requirements of the licensing or certifying organization.</p>
											</div>
										</div>
									</div>
								</div>
								<div class="pagebreak"></div>';

								// record that we've already added this student
								array_push( $alreadyadded, $student->uniquestudentid );
							}

						}
					}

				}
			}

			$html = $html . '
				</div>
				<div class="teqcidb-top-level-colorbox-print-holder">
					<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
				</div>';


			wp_die( $html );	
		}

		function teqcidb_class_form_certification_all_refresher_online_action_callback(){
			global $wpdb;

			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$id = filter_var($_POST['id'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);
			$instructors = ltrim( $instructors, ',' );
			$instructors = str_replace( ',', ', ', $instructors );
			$fromdate = filter_var($_POST['fromdate'],FILTER_SANITIZE_STRING);
			$todate = filter_var($_POST['todate'],FILTER_SANITIZE_STRING);
			
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';

			// to record which students have been added so we don't have multiple certs for the same student
			$alreadyadded = array();

			//if( ( ( '' === $fromdate ) || ( '' === $todate ) ) ){
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );
			//} else {
				//$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s AND enrollmentdate >= %s AND enrollmentdate <= %s", $uniqueclassid, $fromdate, $todate ) );
			//}




			$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing">';
			foreach ( $results as $key => $value ) {

				if ( ( 'passed' === $value->outcome ) && ( 'yes' === $value->registered )  ) {

					$table_name = $wpdb->prefix . 'teqcidb_students';
					$student = $wpdb->get_row(
					    $wpdb->prepare(
					        "SELECT * FROM $table_name 
					        WHERE uniquestudentid = %s 
					        AND lastrefresherdate IS NOT NULL 
					        AND lastrefresherdate != '' 
					        AND lastrefresherdate >= %s 
					        AND lastrefresherdate <= %s",
					        $value->uniquestudentid,
					        $fromdate,
					        $todate
					    )
					);

					if ( ! in_array( $value->uniquestudentid, $alreadyadded ) ) {

						// Next 3 lines trys to get the most recent entry in the Studenthistory table for this specific student, regradless of what kind of class it is. If that most recent student history entry doesn't match this unqiue class id, then this student isn't displayed.
						$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
						$all_students_history = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s ORDER BY ID DESC", $student->uniquestudentid ) );
						if ( ( 0 < sizeof( $all_students_history ) ) && ( $uniqueclassid === $all_students_history[0]->uniqueclassid ) ) {

							if ( ( null !== $student->qcinumber ) && ( '' !== $student->qcinumber ) ) {



								$html = $html . '
								<div>
									<div class="teqcidb-top-level-colorbox-inner-holder teqcidb-top-level-colorbox-inner-holder-student-cert">
									<div class="teqcidb-top-level-colorbox-inner-holder-student-cert-background">
										<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-student-cert-background.png" />
									</div>
										<div class="teqcidb-training-certificate-topholder">
											<div class="teqcidb-training-certificate-topholder-row1">
												<img class="teqcidb-training-certificate-topholder-row1-imgleft" src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
												<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-1">QCI Training Program</p>
												<img class="teqcidb-training-certificate-topholder-row1-imgright" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" />
											</div>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-2">Certificate of Completion</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-3">is hereby granted to:</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-4">' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-5">' . stripslashes($student->company) . '</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-6">for satisfactory completion of</p>
											<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-7">Online Refresher <br/>Training</p>
										</div>
										<div class="teqcidb-training-certificate-leftrightholder">
											<div class="teqcidb-training-certificate-div" id="teqcidb-training-certificate-date-instructor-holder">
												<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-10">QCI No. ' . $student->qcinumber . '</p>
												<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-8">Expires ' .  date('m/d/Y', strtotime( $student->expirationdate ) ) . '</p>
											</div>
										</div>
										<div class="teqcidb-training-certificate-bottomholder teqcidb-training-certificate-initial-online-bottomholder">
											<div>
												<p class="teqcidb-training-certificate-p" id="teqcidb-training-certificate-p-11">This certificate confers four (4.0) professional development hours (PDHs) to students who require credits for licenses or certifications.<br/>Such PDHs are subject to the qualifying requirements of the licensing or certifying organization.</p>
											</div>
										</div>
									</div>
								</div>
								<div class="pagebreak"></div>';

								// record that we've already added this student
								array_push( $alreadyadded, $student->uniquestudentid );

							}
						}
					}
				}
			}

			$html = $html . '
				</div>
				<div class="teqcidb-top-level-colorbox-print-holder">
					<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
				</div>';


			wp_die( $html );	
		}

		function teqcidb_class_form_certification_all_mailing_labels_action_callback(){
			
			/*
				The requirements are that the student has registers and passed the class, and they've been marked as such in the database

				INTIAL classes date range are based off of expirationdate.
				REFRESHER classes date range are based off of lastrefresherdate
			*/


			global $wpdb;

			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$classtype = filter_var($_POST['classtype'],FILTER_SANITIZE_STRING);
			$id = filter_var($_POST['id'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);
			$instructors = ltrim( $instructors, ',' );
			$instructors = str_replace( ',', ', ', $instructors );
			$fromdate = filter_var($_POST['fromdate'],FILTER_SANITIZE_STRING);
			$todate = filter_var($_POST['todate'],FILTER_SANITIZE_STRING);

			
			$passedarray = array();
			// This IF is if no date ranges are provided.
			if( ( ( '' === $fromdate ) || ( '' === $todate ) ) ){
				$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );

				foreach ( $results as $key => $value ) {
					if ( ( 'passed' === $value->outcome ) && ( 'yes' === $value->registered )  ) {
						array_push( $passedarray, $value );
					}
				}
			} else {
				// This IF is if date ranges are provided. We have to cross-reference both tables, as we don't have the same "expirationdate" fields/data in both tables.
				$table_name = $wpdb->prefix . 'teqcidb_students';

				if ( $classtype === 'refresher' ){
					$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE lastrefresherdate BETWEEN %s AND %s", $fromdate, $todate ) );
				} 

				if ( $classtype === 'initial' ){
					$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE expirationdate BETWEEN %s AND %s", $fromdate, $todate ) );
				}
				
				$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
				foreach ($results as $rangekey => $rangevalue) {
					$indivstudent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s AND uniqueclassid = %s", $rangevalue->uniquestudentid, $uniqueclassid ) );

					if ( null !== $indivstudent ){
						if ( ( 'passed' === $indivstudent->outcome ) && ( 'yes' === $indivstudent->registered )  ) {
							array_push( $passedarray, $indivstudent );
						}
					}


				}
			}

			$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing">
						<div id="teqcidb-top-level-colorbox-holder-for-centered-mail-labels">';
			$frontsidehtml = '';
			

			$lastoneclass = "";
			foreach ( $passedarray as $key2 => $value2 ) {
				error_log($classtype);

				$table_name = $wpdb->prefix . 'teqcidb_students';


				// If it's the Initial Online Course...
				if ( $classtype === 'initial' ){

					if( ( ( '' === $fromdate ) || ( '' === $todate ) ) ){
						$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $value2->uniquestudentid ) );
					} else {
						$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s AND expirationdate BETWEEN %s AND %s", $value2->uniquestudentid, $fromdate, $todate ) );

						if( null === $student ){
							break;
						}
					}

				} elseif( $classtype === 'refresher' ){

					if( ( ( '' === $fromdate ) || ( '' === $todate ) ) ){
						$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $value2->uniquestudentid ) );
					} else {
						error_log('here1');
						$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s AND lastrefresherdate BETWEEN %s AND %s", $value2->uniquestudentid, $fromdate, $todate ) );
						error_log(print_r($student,true));
						if( null === $student ){
							break;
						}

					}

				} else {
					$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $value2->uniquestudentid ) );
				}


				$margintoplabel = '';
				$printpagebreak = '';

				if( ( $key2 === ( sizeof( $passedarray ) - 1 ) ) && (  0 == ($key2 % 2) ) ) {
					$lastoneclass = 'teqcidb-training-walletcard-side1-lastone';
				}

				$margintoplabel = '';
				$printpagebreak = '';
				if( ( 0 == $key2 % 14 ) && ( 0 != $key2 ) ){
					$margintoplabel = 'teqcidb-margintop-mail-label-class';
					$printpagebreak = '<div class="pagebreak pagebreak-with-margin-mail-labels"></div>';
				} else {
					$printpagebreak = '';
					$margintoplabel = '';
				}

				$oddlabel = '';
				if( 0 == $key2 % 2 ){
						$oddlabel = 'teqcidb-odd-mail-label-class';
				} else {
					$oddlabel = 'teqcidb-even-mail-label-class';
				}
				
				$html = $html . $printpagebreak . '
						<div class="teqcidb-training-maillabel-side1 ' . $oddlabel . ' ' . $margintoplabel . ' ' . $lastoneclass . '">
							<div class="teqcidb-training-maillabel-middleholder">
								<p class="teqcidb-training-maillabel-p" id="teqcidb-training-maillabel-p-3">' . stripslashes($student->company) . '</p>
								<p class="teqcidb-training-maillabel-p" id="teqcidb-training-maillabel-p-3">ATTN: ' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
								<p class="teqcidb-training-maillabel-p" id="teqcidb-training-maillabel-p-3">' . $student->contactstreetaddress . '</p>
								<p class="teqcidb-training-maillabel-p" id="teqcidb-training-maillabel-p-3">' . $student->contactcity . ', ' . $student->contactstate . ' ' . $student->contactzip . '</p>
							</div>
						</div>';

			}

			$html = $html . '
				</div>
				</div>
				<div class="teqcidb-top-level-colorbox-print-holder">
					<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print These Mailing Labels!!</button>
				</div>';


			wp_die( $html );
		}


		function teqcidb_class_form_certification_all_walletcards_labels_action_callback(){

			/*
				The requirements are that the student has registers and passed the class, and they've been marked as such in the database

				INTIAL classes date range are based off of expirationdate.
				REFRESHER classes date range are based off of lastrefresherdate
			*/


			global $wpdb;

			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$classtype = filter_var($_POST['classtype'],FILTER_SANITIZE_STRING);
			$id = filter_var($_POST['id'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);
			$instructors = ltrim( $instructors, ',' );
			$instructors = str_replace( ',', ', ', $instructors );
			$fromdate = filter_var($_POST['fromdate'],FILTER_SANITIZE_STRING);
			$todate = filter_var($_POST['todate'],FILTER_SANITIZE_STRING);

			
			$passedarray = array();
			// This IF is if no date ranges are provided.
			if( ( ( '' === $fromdate ) || ( '' === $todate ) ) ){
				error_log('tracker1');
				$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );

				foreach ( $results as $key => $value ) {
					if ( ( 'passed' === $value->outcome ) && ( 'yes' === $value->registered )  ) {
						array_push( $passedarray, $value );
					}
				}
			} else {
				error_log('tracker2');
				error_log($classtype);
				// This IF is if date ranges are provided. We have to cross-reference both tables, as we don't have the same "expirationdate" fields/data in both tables.
				$table_name = $wpdb->prefix . 'teqcidb_students';

				if ( $classtype === 'refresher' ){
					$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE lastrefresherdate BETWEEN %s AND %s", $fromdate, $todate ) );
				} 

				if ( $classtype === 'initial' ){
					$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE expirationdate BETWEEN %s AND %s", $fromdate, $todate ) );
				}
				
				$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
				foreach ($results as $rangekey => $rangevalue) {
					$indivstudent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s AND uniqueclassid = %s", $rangevalue->uniquestudentid, $uniqueclassid ) );

					if ( null !== $indivstudent ){
						if ( ( 'passed' === $indivstudent->outcome ) && ( 'yes' === $indivstudent->registered )  ) {
							array_push( $passedarray, $indivstudent );
						}
					}


				}
				/*
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s AND DATE_ADD( enrollmentdate, INTERVAL 1 YEAR ) >= %s AND DATE_ADD( enrollmentdate, INTERVAL 1 YEAR ) <= %s", $uniqueclassid, $fromdate, $todate ) );
				error_log('In the right spot22222??');
				error_log('From Date: ' . $fromdate);
				error_log('To Date: ' . $todate);
				*/
			}

			$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing">
						<div id="teqcidb-top-level-colorbox-holder-for-centered-walletcards-labels">';
			$frontsidehtml = '';
			

			$backsidecounter = 0;
			$lastoneclass = "";
			foreach ( $passedarray as $key2 => $value2 ) {
				error_log($classtype);

				$table_name = $wpdb->prefix . 'teqcidb_students';


				// If it's the Initial Online Course...
				if ( $classtype === 'initial' ){

					if( ( ( '' === $fromdate ) || ( '' === $todate ) ) ){
						$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $value2->uniquestudentid ) );
					} else {
						$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s AND expirationdate BETWEEN %s AND %s", $value2->uniquestudentid, $fromdate, $todate ) );

						if( null === $student ){
							break;
						}
					}

				} elseif( $classtype === 'refresher' ){

					if( ( ( '' === $fromdate ) || ( '' === $todate ) ) ){
						$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $value2->uniquestudentid ) );
					} else {
						error_log('here1');
						$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s AND lastrefresherdate BETWEEN %s AND %s", $value2->uniquestudentid, $fromdate, $todate ) );
						error_log(print_r($student,true));
						if( null === $student ){
							break;
						}

					}

				} else {
					$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $value2->uniquestudentid ) );
				}


				$margintoplabel = '';
				$printpagebreak = '';
				$backsidehtml = '';

				if( ( 0 == $key2 % 10 ) && ( 0 != $key2 ) ){
					$html = $html . '
					<div class="pagebreak pagebreak-with-margin-mail-labels"></div>';

					for ($i=0; $i < 10; $i++) { 
						$html = $html . '
							<div class="teqcidb-training-walletcard-side2">
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">QCI Important Information</p>
							<div class="teqcidb-training-walletcard-middleholder">
								<ul>
									<li>Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.</li>
									<li>QCIs must recertify if they change employers or if their training provider is no longer certified.</li>
									<li class="wallet-card-back-last-li"><div class="wallet-card-back-last-div-1">For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.</div>
										<div class="wallet-card-back-last-div-2"><img style="width:50px;" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" /></div></li>
								</ul>
							</div>
						</div>';
					}

					$html = $html . '
					<div class="pagebreak pagebreak-with-margin-mail-labels"></div>';
					$backsidecounter = 0;
				}

				if ( ( '01-01-1970' === $student->lastrefresherdate ) || ( '' === $student->lastrefresherdate ) || ( null === $student->lastrefresherdate ) ) {
					$student->lastrefresherdate = 'N/A';
				} else {
					$student->lastrefresherdate = date('m-d-Y', strtotime( $student->lastrefresherdate ) );
				}


				if( ( $key2 === ( sizeof( $passedarray ) - 1 ) ) && (  0 == ($key2 % 2) ) ) {
					$lastoneclass = 'teqcidb-training-walletcard-side1-lastone';
				}
				
				$html = $html . '<div class="teqcidb-training-walletcard-side1 ' . $lastoneclass . '">
					<div class="teqcidb-training-walletcard-topholder">
						<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
					</div>
					<div class="teqcidb-training-walletcard-middleholder">
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-1">Qualified Credentialed Inspector</p>
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-4">QCI No. ' . $student->qcinumber . '</p>
					</div>
					<div class="teqcidb-training-walletcard-bottomholder">
						<div class="teqcidb-training-walletcard-bottomholder-leftdiv">
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-3">' . stripslashes($student->company) . '</p>
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">' . $student->contactstreetaddress . '</p>
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">' . $student->contactcity . ', ' . $student->contactstate . ' ' . $student->contactzip . '</p>
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-8">' . $student->phonecell . '</p>
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-9">' . $student->email . '</p>
						</div>
						<div class="teqcidb-training-walletcard-bottomholder-rightdiv">
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-5">Expiration Date: ' . date('m-d-Y', strtotime( $student->expirationdate ) ) . '</p>
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">Initial Training: ' . date('m-d-Y', strtotime( $student->initialtrainingdate ) ) . '</p>
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">Most Recent Annual Update:<br/>' . $student->lastrefresherdate . '</p>
						</div>
					</div>
				</div>';
				

				$backsidecounter++;
			}

			// 
			if ( 0 !== $backsidecounter ){

				$backsidelastclass = '';
				$html = $html . '
					<div class="pagebreak pagebreak-with-margin-mail-labels"></div>';

					for ($i=0; $i < $backsidecounter; $i++) { 


						if ( ( $i === ( $backsidecounter - 1 ) ) && (  0 == ($i % 2)  ) ) {
							$backsidelastclass = 'teqcidb-training-walletcard-side2-lastone';
						}

						$html = $html . '
							<div class="teqcidb-training-walletcard-side2 ' . $backsidelastclass . '">
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">QCI Important Information</p>
							<div class="teqcidb-training-walletcard-middleholder">
								<ul>
									<li>Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.</li>
									<li>QCIs must recertify if they change employers or if their training provider is no longer certified.</li>
									<li class="wallet-card-back-last-li"><div class="wallet-card-back-last-div-1">For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.</div>
										<div class="wallet-card-back-last-div-2"><img style="width:50px;" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" /></div></li>
								</ul>
							</div>
						</div>';
					}

					$html = $html . '
					<div class="pagebreak pagebreak-with-margin-mail-labels"></div>';
					$backsidecounter = 0;
			}

			$html = $html . '
				</div>
				</div>
				<div class="teqcidb-top-level-colorbox-print-holder">
					<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
				</div>';


			wp_die( $html );
				
		}

		
		function teqcidb_class_form_certification_oneperpage_walletcards_labels_action_callback() {

			global $wpdb;

			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$id = filter_var($_POST['id'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);
			$instructors = ltrim( $instructors, ',' );
			$instructors = str_replace( ',', ', ', $instructors );
			$fromdate = filter_var($_POST['fromdate'],FILTER_SANITIZE_STRING);
			$todate = filter_var($_POST['todate'],FILTER_SANITIZE_STRING);
			
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';

			// to record which students have been added so we don't have multiple certs for the same student
			$alreadyadded = array();

			//if( ( ( '' === $fromdate ) || ( '' === $todate ) ) ){
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );
			//} else {
				//$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s AND expirationdate >= %s AND expirationdate <= %s", $uniqueclassid, $fromdate, $todate ) );
			//}


			// Determine Class Type Real Quick
			$temp_table_name = $wpdb->prefix . 'teqcidb_classes';
			$classtyperesults = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $temp_table_name WHERE uniqueclassid = %s", $uniqueclassid ) );
			$classtype = $classtyperesults->classtype;
			// change table name back
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';


if ( 'refresher' === $classtype ) {


	$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div id="teqcidb-top-level-colorbox-holder-for-centered-walletcards-labels">';
	foreach ( $results as $key => $value ) {

		if ( ( 'passed' === $value->outcome ) && ( 'yes' === $value->registered )  ) {

			$table_name = $wpdb->prefix . 'teqcidb_students';
			$student = $wpdb->get_row(
			    $wpdb->prepare(
			        "SELECT * FROM $table_name 
			        WHERE uniquestudentid = %s 
			        AND lastrefresherdate IS NOT NULL 
			        AND lastrefresherdate != '' 
			        AND lastrefresherdate >= %s 
			        AND lastrefresherdate <= %s",
			        $value->uniquestudentid,
			        $fromdate,
			        $todate
			    )
			);

			if ( ! in_array( $value->uniquestudentid, $alreadyadded ) ) {

				// Next 3 lines trys to get the most recent entry in the Studenthistory table for this specific student, regradless of what kind of class it is. If that most recent student history entry doesn't match this unqiue class id, then this student isn't displayed.
				$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
				$all_students_history = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s ORDER BY ID DESC", $student->uniquestudentid ) );
				if ( ( 0 < sizeof( $all_students_history ) ) && ( $uniqueclassid === $all_students_history[0]->uniqueclassid ) ) {

					if ( ( null !== $student->qcinumber ) && ( '' !== $student->qcinumber ) ) {

						// FRONT SIDE
						$html .= '<div class="teqcidb-training-walletcard-side1-non-template-layout teqcidb-training-walletcard-side1">
							<div class="teqcidb-training-walletcard-topholder">
								<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
							</div>
							<div class="teqcidb-training-walletcard-middleholder">
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-1">Qualified Credentialed Inspector</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-4">QCI No. ' . $student->qcinumber . '</p>
							</div>
							<div class="teqcidb-training-walletcard-bottomholder">
								<div class="teqcidb-training-walletcard-bottomholder-leftdiv">
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-3">' . stripslashes($student->company) . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">' . $student->contactstreetaddress . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">' . $student->contactcity . ', ' . $student->contactstate . ' ' . $student->contactzip . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-8">' . $student->phonecell . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-9">' . $student->email . '</p>
								</div>
								<div class="teqcidb-training-walletcard-bottomholder-rightdiv">
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-5">Expiration Date: ' . date('m-d-Y', strtotime($student->expirationdate)) . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">Initial Training: ' . date('m-d-Y', strtotime($student->initialtrainingdate)) . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">Most Recent Annual Update:<br/>' . $student->lastrefresherdate . '</p>
								</div>
							</div>
						</div><br/>';

						// BACK SIDE
						$html .= '<div class="teqcidb-training-walletcard-side2-non-template-layout teqcidb-training-walletcard-side2">
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">QCI Important Information</p>
							<div class="teqcidb-training-walletcard-middleholder">
								<ul>
									<li>Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.</li>
									<li>QCIs must recertify if they change employers or if their training provider is no longer certified.</li>
									<li class="wallet-card-back-last-li">
										<div class="wallet-card-back-last-div-1">For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.</div>
										<div class="wallet-card-back-last-div-2"><img style="width:50px;" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" /></div>
									</li>
								</ul>
							</div>
						</div>';

						// PAGE BREAK
						$html .= '<div class="pagebreak pagebreak-with-margin-mail-labels"></div>';

						// record that we've already added this student
						array_push( $alreadyadded, $student->uniquestudentid );

					}
				}
			}
		}
	}

	$html = $html . '
		</div>
		</div>
		<div class="teqcidb-top-level-colorbox-print-holder">
			<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
		</div>';


	wp_die( $html );


} else {

	$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div id="teqcidb-top-level-colorbox-holder-for-centered-walletcards-labels">';

	foreach ( $results as $key => $value ) {

		if ( ( 'passed' === $value->outcome ) && ( 'yes' === $value->registered )  ) {



			$table_name = $wpdb->prefix . 'teqcidb_students';
			$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s AND expirationdate >= %s AND expirationdate <= %s", $value->uniquestudentid, $fromdate, $todate ) );

			if ( ! in_array( $value->uniquestudentid, $alreadyadded ) ) {

				// Next 3 lines trys to get the most recent entry in the Studenthistory table for this specific student, regradless of what kind of class it is. If that most recent student history entry doesn't match this unqiue class id, then this student isn't displayed.
				$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
				$all_students_history = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s ORDER BY ID DESC", $student->uniquestudentid ) );
				if ( ( 0 < sizeof( $all_students_history ) ) && ( $uniqueclassid === $all_students_history[0]->uniqueclassid ) ) {

					if ( ( null !== $student->qcinumber ) && ( '' !== $student->qcinumber ) ) {

						// FRONT SIDE
						$html .= '<div class="teqcidb-training-walletcard-side1-non-template-layout teqcidb-training-walletcard-side1">
							<div class="teqcidb-training-walletcard-topholder">
								<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
							</div>
							<div class="teqcidb-training-walletcard-middleholder">
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-1">Qualified Credentialed Inspector</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-4">QCI No. ' . $student->qcinumber . '</p>
							</div>
							<div class="teqcidb-training-walletcard-bottomholder">
								<div class="teqcidb-training-walletcard-bottomholder-leftdiv">
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-3">' . stripslashes($student->company) . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">' . $student->contactstreetaddress . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">' . $student->contactcity . ', ' . $student->contactstate . ' ' . $student->contactzip . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-8">' . $student->phonecell . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-9">' . $student->email . '</p>
								</div>
								<div class="teqcidb-training-walletcard-bottomholder-rightdiv">
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-5">Expiration Date: ' . date('m-d-Y', strtotime($student->expirationdate)) . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">Initial Training: ' . date('m-d-Y', strtotime($student->initialtrainingdate)) . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">Most Recent Annual Update:<br/>' . $student->lastrefresherdate . '</p>
								</div>
							</div>
						</div><br/>';

						// BACK SIDE
						$html .= '<div class="teqcidb-training-walletcard-side2-non-template-layout teqcidb-training-walletcard-side2">
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">QCI Important Information</p>
							<div class="teqcidb-training-walletcard-middleholder">
								<ul>
									<li>Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.</li>
									<li>QCIs must recertify if they change employers or if their training provider is no longer certified.</li>
									<li class="wallet-card-back-last-li">
										<div class="wallet-card-back-last-div-1">For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.</div>
										<div class="wallet-card-back-last-div-2"><img style="width:50px;" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" /></div>
									</li>
								</ul>
							</div>
						</div>';

						// PAGE BREAK
						$html .= '<div class="pagebreak pagebreak-with-margin-mail-labels"></div>';

						// record that we've already added this student
						array_push( $alreadyadded, $student->uniquestudentid );
					}

				}
			}

		}
	}

	$html = $html . '
		</div>
		</div>
		<div class="teqcidb-top-level-colorbox-print-holder">
			<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
		</div>';


	wp_die( $html );

}
































			



			/*
			global $wpdb;

			$uniqueclassid = filter_var($_POST['uniqueclassid'], FILTER_SANITIZE_STRING);
			echo 'fdsfdsfdsfdfdsf'.$uniqueclassid;
			$classtype = filter_var($_POST['classtype'], FILTER_SANITIZE_STRING);
			$id = filter_var($_POST['id'], FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'], FILTER_SANITIZE_STRING);
			$instructors = ltrim($instructors, ',');
			$instructors = str_replace(',', ', ', $instructors);
			$fromdate = filter_var($_POST['fromdate'], FILTER_SANITIZE_STRING);
			$todate = filter_var($_POST['todate'], FILTER_SANITIZE_STRING);

			$passedarray = array();

			if ('' === $fromdate || '' === $todate) {
				$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
				$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid));

				foreach ($results as $value) {
					if ('passed' === $value->outcome && 'yes' === $value->registered) {
						// Also confirm they match classtype by checking the student record
						$student_table = $wpdb->prefix . 'teqcidb_students';
						$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE uniquestudentid = %s", $value->uniquestudentid));

						if ($classtype === 'refresher' && !empty($student->lastrefresherdate)) {
							$passedarray[] = $value;
						} elseif ($classtype === 'initial' && !empty($student->expirationdate)) {
							$passedarray[] = $value;
						}
					}
				}
			} else {
				$table_name = $wpdb->prefix . 'teqcidb_students';

			if ($classtype === 'refresher') {
				$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE lastrefresherdate BETWEEN %s AND %s", $fromdate, $todate));
			} elseif ($classtype === 'initial') {
				//echo 'gfdsgfdgfdgfdsgfdsgfds' . $classtype;
				//echo 'fromgfdgfd' . $fromdate;
				//echo 'togfdgfd' . $todate;
				$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE expirationdate BETWEEN %s AND %s", $fromdate, $todate));
				//var_dump(print_r($results, true));
			} else {
				$results = array(); // fallback protection
			}

			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			foreach ($results as $rangevalue) {
				$indivstudent = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE uniquestudentid = %s AND uniqueclassid = %s", $rangevalue->uniquestudentid, $uniqueclassid));
				if ($indivstudent && 'passed' === $indivstudent->outcome && 'yes' === $indivstudent->registered) {
					$passedarray[] = $indivstudent;
				}
			}
		}

	$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing" class="heyjakethisworked">
				<div id="teqcidb-top-level-colorbox-holder-for-centered-walletcards-labels">';
	//var_dump(print_r($passedarray, true));
	foreach ($passedarray as $value2) {
		$table_name = $wpdb->prefix . 'teqcidb_students';

		if ($classtype === 'initial') {
			//echo 'inside initial';
			if ('' === $fromdate || '' === $todate) {
				$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE uniquestudentid = %s", $value2->uniquestudentid));
			} else {
				$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE uniquestudentid = %s AND expirationdate BETWEEN %s AND %s", $value2->uniquestudentid, $fromdate, $todate));

				//echo 'printing student';
				//var_dump(print_r($student, true));

				if (null === $student) {
					continue;
				}
			}
		} elseif ($classtype === 'refresher') {
			//echo 'inside refresher';
			if ('' === $fromdate || '' === $todate) {
				$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE uniquestudentid = %s", $value2->uniquestudentid));
			} else {
				$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE uniquestudentid = %s AND lastrefresherdate BETWEEN %s AND %s", $value2->uniquestudentid, $fromdate, $todate));
				if (null === $student) {
					continue;
				}
			}
		} else {
			//echo 'inside next else';
			$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE uniquestudentid = %s", $value2->uniquestudentid));
		}

		if (!$student) {
			continue;
		}

		if ('01-01-1970' === $student->lastrefresherdate || '' === $student->lastrefresherdate || null === $student->lastrefresherdate) {
			$student->lastrefresherdate = 'N/A';
		} else {
			$student->lastrefresherdate = date('m-d-Y', strtotime($student->lastrefresherdate));
		}


		// Convert last refresher date to Y-m-d for comparison
		$refresherDateObj = DateTime::createFromFormat('m-d-Y', $student->lastrefresherdate);

		if ($refresherDateObj !== false) {
		    echo 'in the first if ' . $student->firstname;

		    // Format refresher date for comparison
		    $refresherDateFormatted = $refresherDateObj->format('Y-m-d');
		    echo $refresherDateFormatted;

		    // Format from and to dates for comparison
		    $adjustedFromDate = date('Y-m-d', strtotime('-1 year', strtotime($fromdate)));
		    echo $adjustedFromDate;

		    $toDateFormatted = date('Y-m-d', strtotime($todate));
		    echo $toDateFormatted;

		    // Check if refresher date is on or between adjustedFromDate and toDateFormatted
		    if ($refresherDateFormatted >= $adjustedFromDate && $refresherDateFormatted <= $toDateFormatted) {
		        echo 'in the second if';
		        continue;
		    }

		    // Optionally reformat for display
		    $student->lastrefresherdate = $refresherDateObj->format('m-d-Y');
		} else {
		    $student->lastrefresherdate = 'N/A'; // fallback for invalid format
		}
			// FRONT SIDE
			$html .= '<div class="teqcidb-training-walletcard-side1-non-template-layout teqcidb-training-walletcard-side1">
				<div class="teqcidb-training-walletcard-topholder">
					<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
				</div>
				<div class="teqcidb-training-walletcard-middleholder">
					<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-1">Qualified Credentialed Inspector</p>
					<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
					<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-4">QCI No. ' . $student->qcinumber . '</p>
				</div>
				<div class="teqcidb-training-walletcard-bottomholder">
					<div class="teqcidb-training-walletcard-bottomholder-leftdiv">
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-3">' . stripslashes($student->company) . '</p>
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">' . $student->contactstreetaddress . '</p>
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">' . $student->contactcity . ', ' . $student->contactstate . ' ' . $student->contactzip . '</p>
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-8">' . $student->phonecell . '</p>
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-9">' . $student->email . '</p>
					</div>
					<div class="teqcidb-training-walletcard-bottomholder-rightdiv">
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-5">Expiration Date: ' . date('m-d-Y', strtotime($student->expirationdate)) . '</p>
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">Initial Training: ' . date('m-d-Y', strtotime($student->initialtrainingdate)) . '</p>
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">Most Recent Annual Update:<br/>' . $student->lastrefresherdate . '</p>
					</div>
				</div>
			</div><br/>';

			// BACK SIDE
			$html .= '<div class="teqcidb-training-walletcard-side2-non-template-layout teqcidb-training-walletcard-side2">
				<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">QCI Important Information</p>
				<div class="teqcidb-training-walletcard-middleholder">
					<ul>
						<li>Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.</li>
						<li>QCIs must recertify if they change employers or if their training provider is no longer certified.</li>
						<li class="wallet-card-back-last-li">
							<div class="wallet-card-back-last-div-1">For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.</div>
							<div class="wallet-card-back-last-div-2"><img style="width:50px;" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" /></div>
						</li>
					</ul>
				</div>
			</div>';

			// PAGE BREAK
			$html .= '<div class="pagebreak pagebreak-with-margin-mail-labels"></div>';
		}

		$html .= '</div></div>
		<div class="teqcidb-top-level-colorbox-print-holder">
			<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
		</div>';

		wp_die($html);

		*/
}




		/*

function teqcidb_class_form_certification_all_walletcards_labels_action_callback(){
			global $wpdb;

			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$id = filter_var($_POST['id'],FILTER_SANITIZE_STRING);
			$instructors = filter_var($_POST['instructors'],FILTER_SANITIZE_STRING);
			$instructors = ltrim( $instructors, ',' );
			$instructors = str_replace( ',', ', ', $instructors );
			
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );

			$html = '<div id="teqcidb-top-level-colorbox-holder-for-printing">
						<div id="teqcidb-top-level-colorbox-holder-for-centered-walletcards-labels">';

			foreach ( $results as $key => $value ) {
				if ( ( 'passed' === $value->outcome ) && ( 'yes' === $value->registered )  ) {

					$table_name = $wpdb->prefix . 'teqcidb_students';
					$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $value->uniquestudentid ) );

					$margintoplabel = '';
					$printpagebreak = '';
					$backsidehtml = '';
					if( ( 0 == $key % 10 ) && ( 0 != $key ) ){
  						$printpagebreak = '
  						<div class="pagebreak pagebreak-with-margin-mail-labels"></div>';


  						for ($i=0; $i < 10; $i++) { 

  							if ( 0 === $i ) {
  								$margintoplabel = ' teqcidb-margintop-walletcards-label-class';
  							} else {
  								$margintoplabel = '';
  							}

  							// code...
	  						$backsidehtml = $backsidehtml .'
	  							<div class="teqcidb-training-walletcard-side2 ' . $margintoplabel . '">
	  								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">QCI Important Information</p>
									<div class="teqcidb-training-walletcard-middleholder">
										<ul>
											<li>Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.</li>
											<li>QCIs must recertify if they change employers or if their training provider is no longer certified.</li>
											<li class="wallet-card-back-last-li"><div class="wallet-card-back-last-div-1">For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.</div>
												<div class="wallet-card-back-last-div-2"><img style="width:50px;" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" /></div></li>
										</ul>
									</div>
								</div>';
						}

						$margintoplabel = ' teqcidb-margintop-walletcards-label-class';


					} else {
						$printpagebreak = '';
						$margintoplabel = '';
						$backsidehtml = '';
					}

					$oddlabel = '';
					if( 0 == $key % 2 ){
  						$oddlabel = ' teqcidb-odd-mail-label-class';
					} else {
						$oddlabel = ' teqcidb-even-mail-label-class';
					}

					$html = $html . $printpagebreak . $backsidehtml . '
						<div class="teqcidb-training-walletcard-side1 ' . $margintoplabel . '">
							<div class="teqcidb-training-walletcard-topholder">
								<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
							</div>
							<div class="teqcidb-training-walletcard-middleholder">
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-1">Qualified Credentialed Inspector</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">' . $student->firstname . ' ' . stripslashes($student->lastname) . '</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-4">QCI No. ' . $student->qcinumber . '</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-5">Expiration Date: ' . date('m-d-Y', strtotime( $student->expirationdate ) ) . '</p>
							</div>
							<div class="teqcidb-training-walletcard-bottomholder">
								<div class="teqcidb-training-walletcard-bottomholder-leftdiv">
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-3">' . stripslashes($student->company) . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">' . $student->contactstreetaddress . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">' . $student->contactcity . ', ' . $student->contactstate . ' ' . $student->contactzip . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-8">' . $student->phonecell . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-9">' . $student->email . '</p>
								</div>
								<div class="teqcidb-training-walletcard-bottomholder-rightdiv">
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">Initial Training: ' . date('m-d-Y', strtotime( $student->initialtrainingdate ) ) . '</p>
									<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">Most Recent Annual Update:<br/>' . date('m-d-Y', strtotime( $student->lastrefresherdate ) ) . '</p>
								</div>
							</div>
						</div>';
				}
			}

			$backsidehtml = '';
			for ($i=0; $i < 8; $i++) { 

				if ( 0 === $i ) {
					$margintoplabel = ' teqcidb-margintop-walletcards-label-class';
				} else {
					$margintoplabel = '';
				}

				// code...
				$backsidehtml = $backsidehtml .'
					<div class="teqcidb-training-walletcard-side2 ' . $margintoplabel . '">
	  								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">QCI Important Information</p>
									<div class="teqcidb-training-walletcard-middleholder">
										<ul>
											<li>Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.</li>
											<li>QCIs must recertify if they change employers or if their training provider is no longer certified.</li>
											<li class="wallet-card-back-last-li"><div class="wallet-card-back-last-div-1">For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.</div>
												<div class="wallet-card-back-last-div-2"><img style="width:50px;" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" /></div></li>
										</ul>
									</div>
								</div>';
			}

			$html = $html . $backsidehtml . '
				</div>
				</div>
				<div class="teqcidb-top-level-colorbox-print-holder">
					<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Certificate!!</button>
				</div>';


			wp_die( $html );	
		}





















		*/


		function teqcidb_class_form_walletcardfront_action_callback(){
			global $wpdb;


			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$phoneoffice = filter_var($_POST['phoneoffice'],FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
			$initialtrainingdate = filter_var($_POST['initialtrainingdate'],FILTER_SANITIZE_STRING);
			$lastrefresherdate = filter_var($_POST['lastrefresherdate'],FILTER_SANITIZE_STRING);
			$qcinumber = filter_var($_POST['qcinumber'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$associations = filter_var($_POST['associations'],FILTER_SANITIZE_STRING);

			if ( '01-01-1970' === date('m-d-Y', strtotime( $lastrefresherdate ) ) ) {
				$lastrefresherdate = 'N/A';
			} else {
				$lastrefresherdate = date('m-d-Y', strtotime( $lastrefresherdate ) );
			}

			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div id="teqcidb-top-level-colorbox-holder-for-centered-walletcards-labels" class="teqcidb-top-level-colorbox-holder-for-centered-walletcards-labels">
					<div class="teqcidb-training-walletcard-side1 teqcidb-training-walletcard-side1-individual-student-wallet-card">
						<div class="teqcidb-training-walletcard-topholder">
							<img src="' . TEQCIDB_ROOT_IMG_URL . 'te-adem.jpg" />
						</div>
						<div class="teqcidb-training-walletcard-middleholder">
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-1">Qualified Credentialed Inspector</p>
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">' . $firstname . ' ' . stripslashes($lastname) . '</p>
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-3">' . stripslashes($company) . '</p>
							<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-4">QCI No. ' . $qcinumber . '</p>
						</div>
						<div class="teqcidb-training-walletcard-bottomholder">
							<div class="teqcidb-training-walletcard-bottomholder-leftdiv">
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">' . $contactstreetaddress . '</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">' . $contactcity . ', ' . $contactstate . ' ' . $contactzip . '</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-8">' . $phonecell . '</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-9">' . $email . '</p>
							</div>
							<div class="teqcidb-training-walletcard-bottomholder-rightdiv">
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-5">Expiration Date: ' . date('m-d-Y', strtotime( $expirationdate ) ) . '</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-6">Initial Training: ' . date('m-d-Y', strtotime( $initialtrainingdate ) ) . '</p>
								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-7">Most Recent Annual Update:<br/>' . $lastrefresherdate . '</p>
							</div>
						</div>
					</div>
					<div class="teqcidb-training-walletcard-side2 teqcidb-training-walletcard-side1-individual-student-wallet-card">
						<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">QCI Important Information</p>
						<div class="teqcidb-training-walletcard-middleholder">
							<ul>
								<li>Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.</li>
								<li>QCIs must recertify if they change employers or if their training provider is no longer certified.</li>
								<li class="wallet-card-back-last-li"><div class="wallet-card-back-last-div-1">For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.</div>
									<div class="wallet-card-back-last-div-2"><img style="width:50px;" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" /></div></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Wallet Card!!</button>
			</div>';

			wp_die( $html );	
		}

		function teqcidb_class_form_maillabel_action_callback(){
			global $wpdb;


			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$phoneoffice = filter_var($_POST['phoneoffice'],FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
			$initialtrainingdate = filter_var($_POST['initialtrainingdate'],FILTER_SANITIZE_STRING);
			$lastrefresherdate = filter_var($_POST['lastrefresherdate'],FILTER_SANITIZE_STRING);
			$qcinumber = filter_var($_POST['qcinumber'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$associations = filter_var($_POST['associations'],FILTER_SANITIZE_STRING);

			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div class="teqcidb-training-maillabel-side1">
					<div class="teqcidb-training-maillabel-middleholder">
						<p class="teqcidb-training-maillabel-p" id="teqcidb-training-maillabel-p-3">' . stripslashes($company) . '</p>
						<p class="teqcidb-training-maillabel-p" id="teqcidb-training-maillabel-p-3">ATTN: ' . $firstname . ' ' . stripslashes($lastname) . '</p>
						<p class="teqcidb-training-maillabel-p" id="teqcidb-training-maillabel-p-3">' . $contactstreetaddress . '</p>
						<p class="teqcidb-training-maillabel-p" id="teqcidb-training-maillabel-p-3">' . $contactcity . ', ' . $contactstate . ' ' . $contactzip . '</p>
					</div>
				</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Mailing Label!!</button>
			</div>';
			wp_die( $html );	
		}


		function teqcidb_class_form_walletcardback_action_callback(){
			global $wpdb;


			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$phoneoffice = filter_var($_POST['phoneoffice'],FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
			$initialtrainingdate = filter_var($_POST['initialtrainingdate'],FILTER_SANITIZE_STRING);
			$lastrefresherdate = filter_var($_POST['lastrefresherdate'],FILTER_SANITIZE_STRING);
			$qcinumber = filter_var($_POST['qcinumber'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$associations = filter_var($_POST['associations'],FILTER_SANITIZE_STRING);

			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div class="teqcidb-training-walletcard-side2">
	  								<p class="teqcidb-training-walletcard-p" id="teqcidb-training-walletcard-p-2">QCI Important Information</p>
									<div class="teqcidb-training-walletcard-middleholder">
										<ul>
											<li>Initial training and annual refresher training must be obtained from the same training provider or a recognized reciprocal partner.</li>
											<li>QCIs must recertify if they change employers or if their training provider is no longer certified.</li>
											<li class="wallet-card-back-last-li"><div class="wallet-card-back-last-div-1">For more information about QCI training, including class dates and locations, call 251.666.2443 or visit training.thompsonengineering.com.</div>
												<div class="wallet-card-back-last-div-2"><img style="width:50px;" src="' . TEQCIDB_ROOT_IMG_URL . 'te-square-logo.jpg" /></div></li>
										</ul>
									</div>
								</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Wallet Card!!</button>
			</div>';

			wp_die( $html );	
		}


		function teqcidb_email_by_expir_date_action_callback(){
			global $wpdb;

			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
		
			$student_table_name = $wpdb->prefix . 'teqcidb_students';
			$student = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE expirationdate = %s", $expirationdate ) );

			$usidarray = array();
			foreach ($student as $key => $id) {
				//error_log($id->uniquestudentid);
				array_push($usidarray, $id->uniquestudentid);
			}

			wp_die( wp_json_encode($usidarray) );	
		}

		function teqcidb_email_by_class_action_callback(){
			global $wpdb;

			$classid = filter_var($_POST['classid'],FILTER_SANITIZE_STRING);
		
			$student_table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$student = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniqueclassid = %s AND registered = 'yes'", $classid ) );
error_log(print_r($student, true)  );
			$usidarray = array();
			foreach ($student as $key => $id) {
				//error_log($id->uniquestudentid);
				array_push($usidarray, $id->uniquestudentid);
			}

			wp_die( wp_json_encode($usidarray) );	
		}






























		function teqcidb_class_form_signin_action_callback(){
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

			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );

			// If in-person, provide address...
			$inpersonaddress = '';
			if ( 'in-person' === $classformat ) {
				$inpersonaddress = '
					<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
						' . $classstreetaddress . '
					</div>
					<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
						' . $classcity . ', ' . $classstate . ' ' . $classzip . '
					</div>';
			}

// Format the dates...
$startdate_array = explode('-', $classstartdate);
$tempformattedstartdate2 = $startdate_array[1] . '-' . $startdate_array[2] . '-' . $startdate_array[0];


$html = '<div class="jre-top-top-test">
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div class="teqcidb-sis-colorbox-page-header">
					<div class="teqcidb-sis-colorbox-page-header-left">
						<p>QCI Training Course SIGN IN SHEET</p>
						<p>DATE: ' . $tempformattedstartdate2 . '</p>
						<p>LOCATION: ' . $classcity . '</p>
					</div>
					<div class="teqcidb-sis-colorbox-page-header-right">
						<img src="' . TEQCIDB_ROOT_IMG_URL . 'telogo.png" />
					</div>
				</div>
				<table>
			        <thead>
			            <tr>
			            	<th class="th-signature">Name & Signature</th>
			                <th class="th-company">Company</th>
			                <th class="th-email">Email</th>
			                <th class="th-mailing-address">Mailing Address</th>
			                <th class="th-cell-phone">Cell Phone</th>
			            </tr>
			        </thead>
			        <tbody>';



				        			$student_table_name = $wpdb->prefix . 'teqcidb_students';
				        			$student_array = array();
				        			foreach( $results as $key => $registeredstudent ){

				        				$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );
				        				array_push( $student_array, $student );

				        			}

				        			$lastname = array_column( $student_array, 'lastname');
				        			array_multisort($lastname, SORT_ASC, $student_array);

				        			$pagecount = 0;
				        			foreach( $results as $key2 => $registeredstudent ){

				        				// First, if the student is approved/registered for the class...
				        				if ( 'yes' === $registeredstudent->registered ) {

				        					// Now get the actual student info
				        					//$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );

				        					$printpagebreak = '';
				        					if( ( 0 == $key2 % 4 ) && ( 0 != $key2 ) ){
				        						$pagecount++;
				          						$printpagebreak = '
				          						</table>
				          						</div>
				          						<div class="pagebreak"></div>
				          						<div id="teqcidb-top-level-colorbox-holder-for-printing">
													<div class="teqcidb-sis-colorbox-page-header">
														<div class="teqcidb-sis-colorbox-page-header-left">
															<p>QCI Training Course SIGN IN SHEET</p>
															<p>DATE: ' . $tempformattedstartdate2 . '</p>
															<p>LOCATION: ' . $classcity . '</p>
														</div>
														<div class="teqcidb-sis-colorbox-page-header-right">
															<img src="' . TEQCIDB_ROOT_IMG_URL . 'telogo.png" />
														</div>
													</div>
														<table>
													        <thead>
													            <tr>
													                <th class="th-company">Company</th>
													                <th class="th-email">Email</th>
													                <th class="th-mailing-address">Mailing Address</th>
													                <th class="th-cell-phone">Cell Phone</th>
													                <th class="th-signature">Name & Signature</th>
													            </tr>
													        </thead>
													        <tbody>';
				        					} else {
				        						$printpagebreak = '';
				        					}




				        					if ( ( '' === $student_array[$key2]->qcinumber ) || ( null === $student_array[$key2]->qcinumber ) ) {
				        						$student_array[$key2]->qcinumber = 'Not Yet Assigned';
				        					}

				        					if ( false !== stripos($student_array[$key2]->contactstreetaddress, 'Suite')){
				        						$student_array[$key2]->contactstreetaddress = str_replace('suite', 'ste', $student_array[$key2]->contactstreetaddress);
				        						$student_array[$key2]->contactstreetaddress = str_replace('Suite', 'ste', $student_array[$key2]->contactstreetaddress);
				        					}


				        					$html = $html . $printpagebreak . '
				        						<tr>
				        							<td class="td-signature">' . stripslashes($student_array[$key2]->lastname) . ', ' . stripslashes($student_array[$key2]->firstname) . '</td>
									                <td class="td-company">' . stripslashes($student_array[$key2]->company) . '</td>
									                <td class="td-email">' . stripslashes($student_array[$key2]->email) . '</br>Alternate Contact:<br/>' . stripslashes($student_array[$key2]->altcontactname) . '<br/>' . stripslashes($student_array[$key2]->altcontactemail) . '</td>
									                <td class="td-mailing-address">' . stripslashes($student_array[$key2]->contactstreetaddress) . ' ' . stripslashes($student_array[$key2]->contactcity) . ', ' . stripslashes($student_array[$key2]->contactstate) . ' ' . stripslashes($student_array[$key2]->contactzip) . '</td>
									                <td class="td-cell-phone">' . stripslashes($student_array[$key2]->phonecell) . '</td>
									            </tr>';
				        				}
				        			}





				            
				        $html = $html . '</tbody>
				    </table>
				    </div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Roster!</button>
			</div>';




/*
			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div class="teqcidb-top-level-colorbox-holder" id="teqcidb-top-level-colorbox-holder-id">
					<div class="teqcidb-top-level-colorbox-holder-header-wrapper">
						<div class="teqcidb-top-level-colorbox-holder-wrapper teqcidb-top-level-colorbox-holder-class-title-wrapper">
							<div class="teqcidb-top-level-colorbox-holder-class-title">
								Class Sign-In Sheet1 for ' . $classname . '
								<img class="teqcidb-te-logo-for-print" src="' . TEQCIDB_ROOT_IMG_URL . 'telogo.png" />
							</div>
						</div>
						<div class="teqcidb-top-level-colorbox-holder-wrapper teqcidb-top-level-colorbox-holder-class-subtitle-wrapper">
							<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
								' . ucfirst( $classtype ) . ' | ' . ucfirst( $classformat ) . ' | ' . date('m-d-Y', strtotime( $classstartdate ) ) . '
							</div>
							' . $inpersonaddress . '
						</div>
					</div>
					<div class="teqcidb-top-level-colorbox-inner-holder-instructions">Sign below your name, check your information, and print any corrections & additions</div>
					<div class="teqcidb-top-level-colorbox-inner-holder">';

			$student_table_name = $wpdb->prefix . 'teqcidb_students';
			$student_array = array();
			foreach( $results as $key => $registeredstudent ){

				$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );
				array_push( $student_array, $student );

			}

			$lastname = array_column( $student_array, 'lastname');
			array_multisort($lastname, SORT_ASC, $student_array);

			$pagecount = 0;
			foreach( $results as $key2 => $registeredstudent ){

				// First, if the student is approved/registered for the class...
				if ( 'yes' === $registeredstudent->registered ) {

					// Now get the actual student info
					//$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );

					$printpagebreak = '';
					if( ( 0 == $key2 % 3 ) && ( 0 != $key2 ) ){
						$pagecount++;
  						$printpagebreak = '
  						<br/><span class="teqcdib-print-page-span" style="font-size:10px;">Page ' . $pagecount . '</span>
  						<div class="pagebreak"></div>
  						<div class="teqcidb-top-level-colorbox-holder-header-wrapper">
							<div class="teqcidb-top-level-colorbox-holder-wrapper teqcidb-top-level-colorbox-holder-class-title-wrapper">
								<div class="teqcidb-top-level-colorbox-holder-class-title">
									Class Sign-In Sheet2 for ' . $classname . '
									<img class="teqcidb-te-logo-for-print" src="' . TEQCIDB_ROOT_IMG_URL . 'telogo.png" />
								</div>
							</div>
							<div class="teqcidb-top-level-colorbox-holder-wrapper teqcidb-top-level-colorbox-holder-class-subtitle-wrapper">
								<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
									' . ucfirst( $classtype ) . ' | ' . ucfirst( $classformat ) . ' | ' . date('m-d-Y', strtotime( $classstartdate ) ) . '
								</div>
								' . $inpersonaddress . '
							</div>
						</div>
						<div class="teqcidb-top-level-colorbox-inner-holder-instructions">Sign below your name, check your information, and print any corrections & additions</div>';
					} else {
						$printpagebreak = '';
					}




					if ( ( '' === $student_array[$key2]->qcinumber ) || ( null === $student_array[$key2]->qcinumber ) ) {
						$student_array[$key2]->qcinumber = 'Not Yet Assigned';
					}


					$html = $html . $printpagebreak . '
						<div class="teqcidb-top-level-colorbox-indiv-holder">
							<div class="teqcidb-top-level-colorbox-indiv-actual">
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Name </span><span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->lastname . ', ' . $student_array[$key2]->firstname . '</span>
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold teqcidb-top-level-colorbox-row-span-bold-signature">Signature:<span class="teqcidb-top-level-colorbox-row-span-bold-signature-line"></span></span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">QCI Number </span><span class="teqcidb-top-level-colorbox-row-span"> </span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Company </span><span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->company . ' </span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Email </span><span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->email . ' </span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<div class="teqcidb-top-level-colorbox-indiv-row-for-inlineblock">
										<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Phone </span>
									</div>
									<div class="teqcidb-top-level-colorbox-indiv-row-for-inlineblock">
										<span class="teqcidb-top-level-colorbox-row-span">Cell: ' . $student_array[$key2]->phonecell . ' </span>
										<span class="teqcidb-top-level-colorbox-row-span">Office: ' . $student_array[$key2]->phoneoffice . ' </span>
									</div>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-actual-row">
									<div class="teqcidb-top-level-colorbox-indiv-row-for-inlineblock">
										<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-bold">Address </span>
									</div>
									<div class="teqcidb-top-level-colorbox-indiv-row-for-inlineblock">
										<span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->contactstreetaddress . ' </span>
										<span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->contactcity . ', ' . $student_array[$key2]->contactstate . ' </span>
										<span class="teqcidb-top-level-colorbox-row-span">' . $student_array[$key2]->contactzip . ' </span>
									</div>
								</div>
							</div>
						</div>';
				}
			}

			$html = $html . '
					</div>
				</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print This Roster!</button>
			</div>';

*/
			wp_die( $html );	
		}


		function teqcidb_class_form_namebadge_action_callback(){
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

			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );
			error_log( print_r( $results, true ) );

			// If in-person, provide address...
			$inpersonaddress = '';
			if ( 'in-person' === $classformat ) {
				$inpersonaddress = '
					<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
						' . $classstreetaddress . '
					</div>
					<div class="teqcidb-top-level-colorbox-holder-class-subtitle">
						' . $classcity . ', ' . $classstate . ' ' . $classzip . '
					</div>';
			}

			$html = '
			<div id="teqcidb-top-level-colorbox-holder-for-printing">
				<div class="teqcidb-top-level-colorbox-holder" id="teqcidb-top-level-colorbox-holder-id">
					<div class="teqcidb-top-level-colorbox-inner-holder-namebadges">';

			$student_table_name = $wpdb->prefix . 'teqcidb_students';
			$student_array = array();
			foreach( $results as $key => $registeredstudent ){

				$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );
				array_push( $student_array, $student );

			}

			$lastname = array_column( $student_array, 'lastname');
			array_multisort($lastname, SORT_ASC, $student_array);

			$pagecount = 0;
			foreach( $results as $key2 => $registeredstudent ){

				// First, if the student is approved/registered for the class...
				if ( 'yes' === $registeredstudent->registered ) {

					// Now get the actual student info
					//$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $student_table_name WHERE uniquestudentid = %s", $registeredstudent->uniquestudentid ) );

					$printpagebreak = '';
					if( ( 0 == $key2 % 8 ) && ( 0 != $key2 ) ){
						$pagecount++;
  						$printpagebreak = '
  						<div class="pagebreak"></div>';
					} else {
						$printpagebreak = '';
					}




					if ( ( '' === $student_array[$key2]->qcinumber ) || ( null === $student_array[$key2]->qcinumber ) ) {
						$student_array[$key2]->qcinumber = 'Not Yet Assigned';
					}


					$html = $html . $printpagebreak . '
						<div class="teqcidb-top-level-colorbox-indiv-holder teqcidb-top-level-colorbox-indiv-holder-namebadge">
							<div class="teqcidb-top-level-colorbox-indiv-actual teqcidb-top-level-colorbox-indiv-actual-for-namebadges">
								<div class="teqcidb-top-level-colorbox-indiv-row-for-inlineblock">
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-name">' . $student_array[$key2]->firstname . ' ' . $student_array[$key2]->lastname . ' </span>
									<span class="teqcidb-top-level-colorbox-row-span teqcidb-top-level-colorbox-row-span-company">' . $student_array[$key2]->company . ' </span>
								</div>
								<div class="teqcidb-top-level-colorbox-indiv-row teqcidb-top-level-colorbox-indiv-row-fornamebadges">
									<img class="teqcidb-te-logo-for-print teqcidb-te-logo-for-print-namebadges" src="' . TEQCIDB_ROOT_IMG_URL . 'telogonamebadges.png" />
								</div>
							</div>
						</div>';
				}
			}

			$html = $html . '
					</div>
				</div>
			</div>
			<div class="teqcidb-top-level-colorbox-print-holder">
				<button data-stylesheetpath="' . TEQCIDB_CSS_URL . 'teqcidb-admin-print-styles.css" id="teqcidb-top-level-colorbox-print-holder-roster-button">Print These Namebadges!</button>
			</div>';


			wp_die( $html );	
		}




		

















		function teqcidb_add_new_student_frontend_action_callback(){
			global $wpdb;
			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$phoneoffice = filter_var($_POST['phoneoffice'],FILTER_SANITIZE_STRING);
			$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
			$studentimage1 = filter_var($_POST['studentimage1'],FILTER_SANITIZE_URL);
			$studentimage2 = filter_var($_POST['studentimage2'],FILTER_SANITIZE_URL);
			$initialtrainingdate = filter_var($_POST['initialtrainingdate'],FILTER_SANITIZE_STRING);
			$lastrefresherdate = filter_var($_POST['lastrefresherdate'],FILTER_SANITIZE_STRING);
			$qcinumber = filter_var($_POST['qcinumber'],FILTER_SANITIZE_STRING);
			$comments = filter_var($_POST['comments'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$fax = filter_var($_POST['fax'],FILTER_SANITIZE_STRING);
			$superstring = filter_var($_POST['superstring'],FILTER_SANITIZE_STRING);
			$associations = filter_var($_POST['associations'],FILTER_SANITIZE_STRING);
			$password = filter_var($_POST['password'],FILTER_SANITIZE_STRING);
			$altcontactname = filter_var($_POST['altcontactname'],FILTER_SANITIZE_STRING);
			$altcontactemail = filter_var($_POST['altcontactemail'],FILTER_SANITIZE_STRING);
			$altcontactphone = filter_var($_POST['altcontactphone'],FILTER_SANITIZE_STRING);


			// Make checks to see if we have a student in the DB with this exact email already.
			$table_name = $wpdb->prefix . 'teqcidb_students';
			$email_flag = false;

			// Check for duplicate email in our own custom table.
			$email_check = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE email = %s", $email ) );
			if( null !== $email_check ){
				$email_flag = true;
			}

			// Check and see if a WordPress User already exists with this email.
			if( false !== email_exists( $email ) ){
				$email_flag = true;
			}

			if ( $email_flag ) {
				wp_die( 'Whoops! There\'s already a student registered with this Email Address!' );
			} else {

				// Create a unique student ID
				$exact_time = time();
				$uniquestudentid = $email . $exact_time;

				// Building array to add to DB.
				$db_insert_array = array(
					'firstname' =>  $firstname,
					'lastname' =>  $lastname,
					'company' =>  $company,
					'contactstreetaddress' =>  $contactstreetaddress,
					'contactcity' =>  $contactcity,
					'contactstate' =>  $contactstate,
					'contactzip' =>  $contactzip,
					'phonecell' =>  $phonecell,
					'phoneoffice' =>  $phoneoffice,
					'email' =>  $email,
					'studentimage1' =>  $studentimage1,
					'studentimage2' =>  $studentimage2,
					'initialtrainingdate' =>  $initialtrainingdate,
					'qcinumber' =>  $qcinumber,
					'comments' =>  $comments,
					'expirationdate' => $expirationdate,
					'fax' => $fax,
					'uniquestudentid' => $uniquestudentid,
					'associations' => $associations,
					'altcontactname' => $altcontactname,
					'altcontactphone' => $altcontactphone,
					'altcontactemail' => $altcontactemail,
					'newregistrantflag' => 'true',
				);

				// Building mask array to add to DB.
				$db_mask_insert_array = array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				);

				$result = $wpdb->insert(  $table_name, $db_insert_array, $db_mask_insert_array );
				$lastid = $wpdb->insert_id;

				// Now create an actual WordPress User, if the insertion was successfull.
				$wp_user_id = '';
				if ( 1 === $result ) {

					$wp_user_id = wp_create_user( $firstname . '-' . $lastname . '-' . $exact_time , $password, $email );

					// WP User object
					$wp_user = new WP_User( $wp_user_id );

					$display_name_change_result = wp_update_user( array( 'ID' => $wp_user_id, 'display_name' => $firstname . ' ' . $lastname ) );

					// Set the role of this user to subscriber.
					$wp_user->set_role( 'subscriber' );

					// Now add the User's WordPress ID to our custom table, if user creation was successful.
					if ( ! is_wp_error( $wp_user_id ) ) {
						$data = array(
							'wpuserid' => $wp_user_id,
						);

						$format = array(
							'%s',
						);

						$table_name = $wpdb->prefix . 'teqcidb_students';
						$where        = array( 'ID' => ( $lastid ) );
						$where_format = array( '%d' );
						$add_wpuserid_result = $wpdb->update( $table_name, $data, $where, $format, $where_format );

						$markasviewed = "https://training.thompsonengineering.com/wp-admin/admin.php?page=TEQciDb-Options-reports&tab=reports4";

						// Now that we're done with all the user creation and database stuff, let's send the new student an email
/*
						$student_message = 
						'Hi ' . $firstname . '!

						Thanks for registering on the Thompson Engineering Stormwater Training site. Below are your login details:

						Login URL: ' . TEQCIDB_SITE_URL . '/stormwater-training-dashboard/
						Username: ' . $email . '
						Password: ' . $password . '

						Now that you\'re registered, feel free to visit ' . TEQCIDB_SITE_URL . '/stormwater-training-dashboard/ and sign up for the next Training Course.';
*/
						$student_message = 
						'Hi ' . $firstname . '!<br/><br/>
						Thanks for registering on the Thompson Engineering Stormwater Training site. Below are your login details:<br/><br/>
						<b>Username:</b> ' . $email . '<br/>
						<b>Password:</b> ' . $password . '<br/>
						<b>Alt. Contact Name:</b> ' . $altcontactname . '<br/>
						<b>Alt. Contact Phone:</b> ' . $altcontactphone . '<br/>
						<b>Alt. Contact Email:</b> ' . $altcontactemail . '<br/><br/>
						You can expect to receive an additional email from the Thompson Engineering Stormwater Training administrators within 12-24 hours with details on how to access the course.';






						// Now let's email the student their info via Sendgrid!
						//wp_mail( $email, 'Thanks for Registering!', $student_message );
						$sendgridemailobject = new \SendGrid\Mail\Mail(); 
						$sendgridemailobject->setFrom( "qci@thompsonengineering.com", "Thompson Engineering");
						$sendgridemailobject->setSubject( 'Thanks for Registering!' );
						$sendgridemailobject->addTo( $email, $firstname . ' ' . $lastname );
						$sendgridemailobject->addContent( 'text/html', $student_message );
						$sendgrid = new SendGrid("");
						try {
						    $response = $sendgrid->send($sendgridemailobject);
						    $result = '1';
						    error_log( " Successfully emailed the student their database signup email\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
						} catch (Exception $e) {
							$result = '0';
							$response = 'Caught exception: '. $e->getMessage();
							error_log( " Unsuccessfully attempted to email the student their database signup email: " . $e->getMessage() . "  \n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
						}


						// Now let's email the Thompson admins the newly-created student info via WordPress!
						$admin_message = 
						'Hi Stormwater Training Coordinators!

A new student has just registered on the Thompson Engineering Stormwater Training site. Below are the details:

First Name: ' . $firstname . '
Last Name: ' . $lastname . '
Email: ' . $email . '
Phone: ' . $phonecell . '
Company: ' . stripslashes($company) . '
Username: ' . $email . '
Password: ' . $password . '
Alt. Contact Name: ' . $altcontactname . '
Alt. Contact Phone: ' . $altcontactphone . '
Alt. Contact Email: ' . $altcontactemail . '

Be sure to visit ' . $markasviewed . ' to review this student\'s info, and to click the "mark as viewed" button.' ;



						wp_mail( 'qci@thompsonengineering.com,croton@thompsonengineering.com,jevans@highlevelmarketing.com,iporter@thompsonengineering.com', 'A New Student Has Just Registered!', $admin_message);





/*
						$student_message = 
						'Hi ' . $firstname . '!

						Thanks for registering on the Thompson Engineering Stormwater Training site. Below are your login details:

						Login URL: ' . TEQCIDB_SITE_URL . '/stormwater-training-dashboard/
						Username: ' . $email . '
						Password: ' . $password . '

						Now that you\'re registered, feel free to visit ' . TEQCIDB_SITE_URL . '/stormwater-training-dashboard/ and sign up for the next Training Course.';
*/
					
						// Now let's email the student their info via Sendgrid!
						//wp_mail( $email, 'Thanks for Registering!', $student_message );



















						// Now let's email the Thompson admins the newly-created student info via Sendgrid!
						$admin_message = 
						'Hi Stormwater Training Coordinators!<br/><br/>
						A new student has just registered on the Thompson Engineering Stormwater Training site. Below are the details:<br/><br/>
						<b>First Name:</b> ' . $firstname . '<br/>
						<b>Last Name:</b> ' . $lastname . '<br/>
						<b>Phone:</b> ' . $phonecell . '<br/>
						<b>Company:</b> ' . stripslashes($company) . '<br/>
						<b>Username:</b> ' . $email . '<br/>
						<b>Password:</b> ' . $password . '<br/>
						<b>Email:</b>' . $email . '<br/>
						<b>Alt. Contact Name:</b> ' . $altcontactname . '<br/>
						<b>Alt. Contact Phone:</b> ' . $altcontactphone . '<br/>
						<b>Alt. Contact Email:</b> ' . $altcontactemail . '<br/><br/>
						Be sure to visit ' . $markasviewed . ' to review this student\'s info, and to click the "mark as viewed" button.';

						$sendgridemailobject = new \SendGrid\Mail\Mail(); 
						$sendgridemailobject->setFrom( "qci@thompsonengineering.com", "Thompson Engineering");
						$sendgridemailobject->setSubject( 'Thanks for Registering!' );
						$sendgridemailobject->addTo( 'qci@thompsonengineering.com', $firstname . ' ' . $lastname );
						$sendgridemailobject->addContent( 'text/html', $admin_message );
						$sendgrid = new SendGrid("");
						try {
						    $response = $sendgrid->send($sendgridemailobject);
						    $result = '1';
						    error_log( " Successfully emailed the Thompson admins the student's database signup email\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
						} catch (Exception $e) {
							$result = '0';
							$response = 'Caught exception: '. $e->getMessage();
							error_log( " Unsuccessfully attempted to email the Thompson admins the student's database signup email: " . $e->getMessage() . "  \n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
						}

						// Send myself a copy for S&G.
						$sendgridemailobject = new \SendGrid\Mail\Mail(); 
						$sendgridemailobject->setFrom( "qci@thompsonengineering.com", "Thompson Engineering");
						$sendgridemailobject->setSubject( 'Thanks for Registering!' );
						$sendgridemailobject->addTo( 'jevans@highlevelmarketing.com', 'Jake Evans' );
						$sendgridemailobject->addContent( 'text/html', $admin_message );
						$sendgrid = new SendGrid("");
						try {
						    $response = $sendgrid->send($sendgridemailobject);
						} catch (Exception $e) {
							$response = 'Caught exception: '. $e->getMessage();
						}


					} else {
						error_log( $wp_user_id->get_error_message(), 3, TEQCIDB_LOGS_DIR . 'frontendusercreationlog.log' );
					}
				}
				
				wp_die( $firstname . '-' . $lastname . '-' . $exact_time );
			}	
		}

		


	function teqcidb_edit_existing_student_action_callback() {
	    global $wpdb;

	    // 1) Sanitize incoming student fields
	    $studentid            = filter_var( $_POST['studentid'],            FILTER_SANITIZE_NUMBER_INT );
	    $firstname            = sanitize_text_field( $_POST['firstname'] );
	    $lastname             = sanitize_text_field( $_POST['lastname'] );
	    $company              = sanitize_text_field( $_POST['company'] );
	    $contactstreetaddress = sanitize_text_field( $_POST['contactstreetaddress'] );
	    $contactcity          = sanitize_text_field( $_POST['contactcity'] );
	    $contactstate         = sanitize_text_field( $_POST['contactstate'] );
	    $contactzip           = sanitize_text_field( $_POST['contactzip'] );
	    $phonecell            = sanitize_text_field( $_POST['phonecell'] );
	    $phoneoffice          = sanitize_text_field( $_POST['phoneoffice'] );
	    $email                = sanitize_email( $_POST['email'] );
	    $studentimage1        = esc_url_raw( $_POST['studentimage1'] );
	    $studentimage2        = esc_url_raw( $_POST['studentimage2'] );
	    $initialtrainingdate  = sanitize_text_field( $_POST['initialtrainingdate'] );
	    $lastrefresherdate    = sanitize_text_field( $_POST['lastrefresherdate'] );
	    $qcinumber            = sanitize_text_field( $_POST['qcinumber'] );
	    $comments             = sanitize_textarea_field( $_POST['comments'] );
	    $expirationdate       = sanitize_text_field( $_POST['expirationdate'] );
	    $fax                  = sanitize_text_field( $_POST['fax'] );
	    $associations         = sanitize_text_field( $_POST['associations'] );
	    $uniquestudentid      = sanitize_text_field( $_POST['uniquestudentid'] );
	    $wpuserid             = filter_var( $_POST['wpuserid'],             FILTER_SANITIZE_NUMBER_INT );
	    $altcontactname       = sanitize_text_field( $_POST['altcontactname'] );
	    $altcontactemail      = sanitize_email( $_POST['altcontactemail'] );
	    $altcontactphone      = sanitize_text_field( $_POST['altcontactphone'] );

	    // 2) Update main students table
	    $students_table = $wpdb->prefix . 'teqcidb_students';
	    $data = [
	        'firstname'            => $firstname,
	        'lastname'             => $lastname,
	        'company'              => $company,
	        'contactstreetaddress' => $contactstreetaddress,
	        'contactcity'          => $contactcity,
	        'contactstate'         => $contactstate,
	        'contactzip'           => $contactzip,
	        'phonecell'            => $phonecell,
	        'phoneoffice'          => $phoneoffice,
	        'email'                => $email,
	        'studentimage1'        => $studentimage1,
	        'studentimage2'        => $studentimage2,
	        'initialtrainingdate'  => $initialtrainingdate,
	        'lastrefresherdate'    => $lastrefresherdate,
	        'qcinumber'            => $qcinumber,
	        'comments'             => $comments,
	        'expirationdate'       => $expirationdate,
	        'fax'                  => $fax,
	        'uniquestudentid'      => $uniquestudentid,
	        'associations'         => $associations,
	        'altcontactname'       => $altcontactname,
	        'altcontactphone'      => $altcontactphone,
	        'altcontactemail'      => $altcontactemail,
	    ];
	    $formats = array_fill( 0, count( $data ), '%s' );
	    $where   = [ 'ID' => $studentid ];
	    $where_formats = [ '%d' ];
	    $first_update_result = $wpdb->update( $students_table, $data, $where, $formats, $where_formats );

	    // 3) Update existing history entries
	    if ( ! empty( $_POST['historyEntries'] ) ) {
	        $history_entries = json_decode( stripslashes( $_POST['historyEntries'] ), true );
	        $history_table   = $wpdb->prefix . 'teqcidb_studenthistory';

	        foreach ( $history_entries as $entry ) {
	            $history_id = intval( $entry['historyId'] );
	            $hist_data = [
	                'registered'      => sanitize_text_field( $entry['adminApproved'] ),
	                'attended'        => sanitize_text_field( $entry['attended'] ),
	                'outcome'         => sanitize_text_field( $entry['outcome'] ),
	                'paymentstatus'   => sanitize_text_field( $entry['paymentStatus'] ),
	                'amountpaid'      => sanitize_text_field( $entry['amountPaid'] ),
	                'enrollmentdate'  => sanitize_text_field( $entry['enrollmentDate'] ),
	                'credentialsdate' => sanitize_text_field( $entry['credentialsDate'] ),
	            ];
	            $hist_formats   = array_fill( 0, count( $hist_data ), '%s' );
	            $hist_where     = [ 'ID' => $history_id ];
	            $hist_where_fmt = [ '%d' ];

	            $wpdb->update( $history_table, $hist_data, $hist_where, $hist_formats, $hist_where_fmt );
	        }
	    }

	    // 4) Insert brand-new history entries
	    if ( ! empty( $_POST['newHistoryEntries'] ) ) {
	        $new_entries    = json_decode( stripslashes( $_POST['newHistoryEntries'] ), true );
	        $history_table  = $wpdb->prefix . 'teqcidb_studenthistory';

	        foreach ( $new_entries as $entry ) {
	            $insert_data = [
	                'wpuserid'         => $wpuserid,
	                'uniquestudentid'  => $uniquestudentid,
	                'classname'        => sanitize_text_field( $entry['className'] ),
	                'uniqueclassid'    => sanitize_text_field( $entry['uniqueClassId'] ),
	                'registered'       => sanitize_text_field( $entry['adminApproved'] ),
	                'attended'         => sanitize_text_field( $entry['attended'] ),
	                'outcome'          => sanitize_text_field( $entry['outcome'] ),
	                'paymentstatus'    => sanitize_text_field( $entry['paymentStatus'] ),
	                'amountpaid'       => sanitize_text_field( $entry['amountPaid'] ),
	                'enrollmentdate'   => sanitize_text_field( $entry['enrollmentDate'] ),
	                'credentialsdate'  => sanitize_text_field( $entry['credentialsDate'] ),
	            ];
	            $insert_formats = array_fill( 0, count( $insert_data ), '%s' );
	            $wpdb->insert( $history_table, $insert_data, $insert_formats );
	        }
	    }

	    // 5) Return the results of the main update
	    wp_die( $first_update_result );
	}

















		function teqcidb_mark_student_viewed_action_callback(){
			// If user's email changes, change the wordpress user email as well, but only if the email address isn't blank.
			// If user's first name/last name changes, change that as well
			//


			global $wpdb;
			$studentid = filter_var($_POST['studentid'],FILTER_SANITIZE_STRING);
			$data = array(
				'newregistrantflag' =>  'false',
			);

			$format = array(
				'%s',
			);
			$where        = array( 'ID' => ( $studentid ) );
			$where_format = array( '%d' );
			$table_name = $wpdb->prefix . 'teqcidb_students';
			$first_update_result = $wpdb->update( $table_name, $data, $where, $format, $where_format );

			wp_die( $first_update_result . '---' . $studentid );

		}

		function teqcidb_mark_student_viewed_payment_action_callback(){
			// If user's email changes, change the wordpress user email as well, but only if the email address isn't blank.
			// If user's first name/last name changes, change that as well
			//


			global $wpdb;
			$studentid = filter_var($_POST['studentid'],FILTER_SANITIZE_STRING);
			$data = array(
				'newpaymentflag' =>  'false',
			);

			$format = array(
				'%s',
			);
			$where        = array( 'ID' => ( $studentid ) );
			$where_format = array( '%d' );
			$table_name = $wpdb->prefix . 'teqcidb_students';
			$first_update_result = $wpdb->update( $table_name, $data, $where, $format, $where_format );

			wp_die( $first_update_result . '---' . $studentid );

		}





		function teqcidb_save_emails_action_callback(){

			global $wpdb;
			$emailname = filter_var($_POST['emailname'],FILTER_SANITIZE_STRING);
			$emaildescription = filter_var($_POST['emaildescription'],FILTER_SANITIZE_STRING);
			$subjectline = filter_var($_POST['subjectline'],FILTER_SANITIZE_STRING);
			$fromemailaddress = filter_var($_POST['fromemailaddress'],FILTER_SANITIZE_STRING);
			$testingemailaddress = filter_var($_POST['testingemailaddress'],FILTER_SANITIZE_STRING);
			$emailmessage = stripslashes_deep( html_entity_decode( filter_var($_POST['emailmessage'],FILTER_SANITIZE_STRING), ENT_QUOTES, 'UTF-8' ) );

			// Create a unique class ID.
			$uniqueemailid =  strtolower( str_replace(' ', '', $emailname) ) . time();

			$data = array(
				'emailname' =>  $emailname,
				'emaildescription' =>  $emaildescription,
				'subjectline' =>  $subjectline,
				'fromemailaddress' =>  $fromemailaddress,
				'testingemailaddress' =>  $testingemailaddress,
				'emailmessage' =>  $emailmessage,
				'uniqueemailid' =>  $uniqueemailid,
			);

			$format = array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			);
			$table_name = $wpdb->prefix . 'teqcidb_emails';
			$result = $wpdb->insert(  $table_name, $data, $format );

			wp_die( $result );
		}

		function teqcidb_wpdocs_set_html_mail_content_type() {
		    return 'text/html';
		}

		function teqcidb_email_fromname( $email ){
			return 'Thompson Engineering';
		}

		

		function teqcidb_send_test_emails_action_callback(){

			global $wpdb;
			$emailname = filter_var($_POST['emailname'],FILTER_SANITIZE_STRING);
			$emaildescription = filter_var($_POST['emaildescription'],FILTER_SANITIZE_STRING);
			$subjectline = filter_var($_POST['subjectline'],FILTER_SANITIZE_STRING);
			$fromemailaddress = filter_var($_POST['fromemailaddress'],FILTER_SANITIZE_STRING);
			$testingemailaddress = filter_var($_POST['testingemailaddress'],FILTER_SANITIZE_STRING);
			$emailmessage = stripslashes_deep( html_entity_decode( filter_var($_POST['emailmessage'],FILTER_SANITIZE_STRING), ENT_QUOTES, 'UTF-8' ) );

			// Get any links within the message.
			$urlsArray = array();
			if ( false !== stripos( $emailmessage, '[link]' ) ) {

				$temp1 = explode('[link]', $emailmessage);
				foreach( $temp1 as $linkkey1 => $linkvalue1 ){
					if ( false !== stripos( $linkvalue1, '[endlink]' ) ) {
						$temp2 = explode('[endlink]', $linkvalue1 );
						foreach( $temp2 as $linkkey2 => $linkvalue2 ){
							if ( 0 === $linkkey2 ) {
								array_push( $urlsArray, $linkvalue2 );
							}
						}
					}
				}
			}

			// Edit the message to include <a> html.
			foreach( $urlsArray as $urlkey => $urlactual ){
				$pos = strpos( $emailmessage, '[link]' );
				if ( $pos !== false ) {
				    $emailmessage = substr_replace( $emailmessage, '<a href="'.$urlactual.'">', $pos, strlen( '[link]' ) );
				}
			}

			// Now replace with generic student info, if testing this email from the "Create an email template," or the "Edit & Delete Email Templates" tabs.
			$emailmessage = str_replace( '[firstname]', 'Ilka', $emailmessage );
			$emailmessage = str_replace( '[lastname]', 'Doe', $emailmessage );
			$emailmessage = str_replace( '[studentemail]', 'john.doe@johndoe.com', $emailmessage );
			$emailmessage = str_replace( '[studentphone]', '(111) 111-1111', $emailmessage );
			$emailmessage = str_replace( '[studentstreetaddress]', '123 Training Boulevard', $emailmessage );
			$emailmessage = str_replace( '[studentcity]', 'Wichita', $emailmessage );
			$emailmessage = str_replace( '[studentstate]', 'KS', $emailmessage );
			$emailmessage = str_replace( '[studentzip]', '67235', $emailmessage );
			$emailmessage = str_replace( '[studentcompany]', 'NASA', $emailmessage );
			$emailmessage = str_replace( '[studentexpiredate]', '01/01/2099', $emailmessage );
			$emailmessage = str_replace( '[endlink]', '</a>', $emailmessage );
			$emailmessage = nl2br( $emailmessage );

			/*
			error_log('$fromemailaddress: ' . $fromemailaddress);
			error_log('$testingemailaddress: ' . $testingemailaddress);
			error_log('$subjectline: ' . $subjectline);
			error_log('$emailmessage: ' . $emailmessage);
			*/
			$response = '';
			$email = new \SendGrid\Mail\Mail(); 
			$email->setFrom( "croton@thompsonengineering.com", "Thompson Engineering");
			if ( ( '' !== $subjectline ) && ( null != $subjectline ) ){
				$email->setSubject( $subjectline );
			} else {
				$result = 'Whoops! You forgot to add a Subject Line!';
				wp_die( $result );
			}
			$email->addTo( $testingemailaddress, "Example User");
			$email->addContent( 'text/html', $emailmessage );
			//$sendgrid = new \SendGrid(getenv(''));
			$sendgrid = new SendGrid("");
			try {
			    $response = $sendgrid->send($email);
			    error_log(var_dump($response));
			    $result = '1';
			    /*
			    error_log( $response->statusCode() );
			    error_log($response->headers());
			    error_log($response->body());
			    */
			} catch (Exception $e) {
				$result = '0';
				error_log($result);
				wp_die( $result );
				$response = 'Caught exception: '. $e->getMessage();
			    //error_log( 'Caught exception: '. $e->getMessage());
			}


			/*

			// Now change the email type, and change it right back
			add_filter( 'wp_mail_content_type', array( $this, 'teqcidb_wpdocs_set_html_mail_content_type' ) );
			add_filter('wp_mail_from_name', array( $this, 'teqcidb_email_fromname' ) );
			$result = wp_mail( $testingemailaddress, $subjectline,  $emailmessage );
			remove_filter('wp_mail_from_name', array( $this, 'teqcidb_email_fromname' ) );
			remove_filter( 'wp_mail_content_type', array( $this, 'teqcidb_wpdocs_set_html_mail_content_type' ) );
	
			wp_die( $result ); */
			error_log($result);
			wp_die( $result );
		}

		function teqcidb_send_bulk_email_action_callback(){

			global $wpdb;
			$toemailaddress = filter_var($_POST['email'],FILTER_SANITIZE_STRING);
			$altcontactemail = filter_var($_POST['altcontactemail'],FILTER_SANITIZE_STRING);
			$emailtemplate = filter_var($_POST['emailtemplate'],FILTER_SANITIZE_STRING);
			$emailmessage = stripslashes_deep( html_entity_decode( filter_var($_POST['emailmessage'],FILTER_SANITIZE_STRING), ENT_QUOTES, 'UTF-8' ) );
			$firstname = filter_var($_POST['firstname'],FILTER_SANITIZE_STRING);
			$lastname = filter_var($_POST['lastname'],FILTER_SANITIZE_STRING);
			$company = filter_var($_POST['company'],FILTER_SANITIZE_STRING);
			$contactstreetaddress = filter_var($_POST['contactstreetaddress'],FILTER_SANITIZE_STRING);
			$contactcity = filter_var($_POST['contactcity'],FILTER_SANITIZE_STRING);
			$contactstate = filter_var($_POST['contactstate'],FILTER_SANITIZE_STRING);
			$contactzip = filter_var($_POST['contactzip'],FILTER_SANITIZE_STRING);
			$phonecell = filter_var($_POST['phonecell'],FILTER_SANITIZE_STRING);
			$expirationdate = filter_var($_POST['expirationdate'],FILTER_SANITIZE_STRING);
			$subjectline = filter_var($_POST['subjectline'],FILTER_SANITIZE_STRING);
			$alsoemailaltcontact = filter_var($_POST['alsoemailaltcontact'],FILTER_SANITIZE_STRING);
			

			// If we're using one-off email messaging, or a previously-saved template.
			if ( ( 'undefined' !== $emailtemplate ) && ( '' !== $emailtemplate )      ) {

				// Let's make a call to the DB to get saved email template stuff.
				$table_name = $wpdb->prefix . 'teqcidb_emails';
				$emailmessagefromdb = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueemailid = %s", $emailtemplate ) );
				$emailmessage = $emailmessagefromdb->emailmessage;
				$subjectline = $emailmessagefromdb->subjectline;

			}

			// Now replace with generic student info, if testing this email from the "Create an email template," or the "Edit & Delete Email Templates" tabs.
			$emailmessage = str_replace( '[firstname]', $firstname, $emailmessage );
			$emailmessage = str_replace( '[lastname]', $lastname, $emailmessage );
			$emailmessage = str_replace( '[studentemail]', $email, $emailmessage );
			$emailmessage = str_replace( '[studentphone]', $phonecell, $emailmessage );
			$emailmessage = str_replace( '[studentstreetaddress]', $contactstreetaddress, $emailmessage );
			$emailmessage = str_replace( '[studentcity]', $contactcity, $emailmessage );
			$emailmessage = str_replace( '[studentstate]', $contactstate, $emailmessage );
			$emailmessage = str_replace( '[studentzip]', $contactzip, $emailmessage );
			$emailmessage = str_replace( '[studentcompany]', $company, $emailmessage );
			$emailmessage = str_replace( '[studentexpiredate]', $expirationdate, $emailmessage );
			$emailmessage = str_replace( '[link]', '</a>', $emailmessage );
			$emailmessage = str_replace( '[endlink]', '</a>', $emailmessage );
			$emailmessage = nl2br( $emailmessage );



			/*
			error_log('$fromemailaddress: ' . $fromemailaddress);
			error_log('$testingemailaddress: ' . $testingemailaddress);
			error_log('$subjectline: ' . $subjectline);
			error_log('$emailmessage: ' . $emailmessage);
			*/
			$response = '';
			$email = new \SendGrid\Mail\Mail(); 
			$email->setFrom( "croton@thompsonengineering.com", "Thompson Engineering");
			if ( ( '' !== $subjectline ) && ( null != $subjectline ) ){
				$email->setSubject( $subjectline );
			} else {
				$result = 'Whoops! You forgot to add a Subject Line!';
				wp_die( $result );
			}
			$email->addTo( $toemailaddress, "Example User");
			$email->addContent( 'text/html', $emailmessage );
			//$sendgrid = new \SendGrid(getenv(''));
			$sendgrid = new SendGrid("");
			try {
			    $response = $sendgrid->send($email);
			    error_log(var_dump($response));
			    $result = '1';
			    /*
			    error_log( $response->statusCode() );
			    error_log($response->headers());
			    error_log($response->body());
			    */
			} catch (Exception $e) {
				$result = '0';
				error_log($result);
				wp_die( $result );
				$response = 'Caught exception: '. $e->getMessage();
			    //error_log( 'Caught exception: '. $e->getMessage());
			}


			/*

			// Now change the email type, and change it right back
			add_filter( 'wp_mail_content_type', array( $this, 'teqcidb_wpdocs_set_html_mail_content_type' ) );
			add_filter('wp_mail_from_name', array( $this, 'teqcidb_email_fromname' ) );
			$result = wp_mail( $testingemailaddress, $subjectline,  $emailmessage );
			remove_filter('wp_mail_from_name', array( $this, 'teqcidb_email_fromname' ) );
			remove_filter( 'wp_mail_content_type', array( $this, 'teqcidb_wpdocs_set_html_mail_content_type' ) );
	
			wp_die( $result ); */
			error_log($result);
			wp_die( $result );

	
		}





		function teqcidb_save_email_edits_action_callback(){

			global $wpdb;
			$emailname = filter_var($_POST['emailname'],FILTER_SANITIZE_STRING);
			$emaildescription = filter_var($_POST['emaildescription'],FILTER_SANITIZE_STRING);
			$subjectline = filter_var($_POST['subjectline'],FILTER_SANITIZE_STRING);
			$fromemailaddress = filter_var($_POST['fromemailaddress'],FILTER_SANITIZE_STRING);
			$testingemailaddress = filter_var($_POST['testingemailaddress'],FILTER_SANITIZE_STRING);
			$emailmessage = stripslashes_deep( html_entity_decode( filter_var($_POST['emailmessage'],FILTER_SANITIZE_STRING), ENT_QUOTES, 'UTF-8' ) );
			$id = filter_var($_POST['id'],FILTER_SANITIZE_STRING);

			$data = array(
				'emailname' =>  $emailname,
				'emaildescription' =>  $emaildescription,
				'subjectline' =>  $subjectline,
				'fromemailaddress' =>  $fromemailaddress,
				'testingemailaddress' =>  $testingemailaddress,
				'emailmessage' =>  $emailmessage,
			);

			$format = array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			);
			$where        = array( 'ID' => $id );
			$where_format = array( '%s' );
			$table_name = $wpdb->prefix . 'teqcidb_emails';
			$first_update_result = $wpdb->update( $table_name, $data, $where, $format, $where_format );


			wp_die( $first_update_result );
		}

		



























		function teqcidb_edit_existing_class_action_callback(){
			// If user's email changes, change the wordpress user email as well, but only if the email address isn't blank.
			// If user's first name/last name changes, change that as well

			global $wpdb;
			$classname = filter_var($_POST['classname'],FILTER_SANITIZE_STRING);
			$classformat = filter_var($_POST['classformat'],FILTER_SANITIZE_STRING);
			$classtype = filter_var($_POST['classtype'],FILTER_SANITIZE_STRING);
			$classstreetaddress = filter_var($_POST['classstreetaddress'],FILTER_SANITIZE_STRING);
			$classcity = filter_var($_POST['classcity'],FILTER_SANITIZE_STRING);
			$classstate = filter_var($_POST['classstate'],FILTER_SANITIZE_STRING);
			$classzip = filter_var($_POST['classzip'],FILTER_SANITIZE_STRING);
			$classstartdate = filter_var($_POST['classstartdate'],FILTER_SANITIZE_STRING);
			$classendtime = filter_var($_POST['classendtime'],FILTER_SANITIZE_STRING);
			$classstarttime = filter_var($_POST['classstarttime'],FILTER_SANITIZE_STRING);
			$classcost = filter_var($_POST['classcost'],FILTER_SANITIZE_URL);
			$classsize = filter_var($_POST['classsize'],FILTER_SANITIZE_URL);
			$classdescription = filter_var($_POST['classdescription'],FILTER_SANITIZE_STRING);
			$classhide = filter_var($_POST['classhide'],FILTER_SANITIZE_STRING);
			$superstring = filter_var($_POST['superstring'],FILTER_SANITIZE_STRING);
			$classid = filter_var($_POST['classid'],FILTER_SANITIZE_STRING);
			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$instructorstring = filter_var($_POST['instructorstring'],FILTER_SANITIZE_STRING);

			$data = array(
				'classname' =>  $classname,
				'classformat' =>  $classformat,
				'classtype' =>  $classtype,
				'classstreetaddress' =>  $classstreetaddress,
				'classcity' =>  $classcity,
				'classstate' =>  $classstate,
				'classzip' =>  $classzip,
				'classstartdate' =>  $classstartdate,
				'classendtime' =>  $classendtime,
				'classstarttime' =>  $classstarttime,
				'classcost' =>  $classcost,
				'classsize' =>  $classsize,
				'classdescription' =>  $classdescription,
				'classhide' =>  $classhide,
				'instructors' => $instructorstring,
			);

			$format = array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			);
			$where        = array( 'uniqueclassid' => ( $uniqueclassid ) );
			$where_format = array( '%s' );
			$table_name = $wpdb->prefix . 'teqcidb_classes';
			$first_update_result = $wpdb->update( $table_name, $data, $where, $format, $where_format );


			// Work on structuring data to add edit the students associated with this class
			$superstring = explode( ';', $superstring );
/*
			// Delete all entries associated with the class first. Then we'll add them again one by one.
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
			$custom_delete_result = $wpdb->delete( $table_name, array( 'uniqueclassid' => $uniqueclassid ) );


			foreach( $superstring as $string ){

				error_log( 'in the for each 123' );
				error_log( 'here is the string' );
				error_log($string);

				if ( false !== stripos( $string, '-' ) ) {

					error_log('in the if 456');

					$subarray = explode( '-', $string );
					$subarray[7] = str_replace( '/', '-', $subarray[7] );
					$subarray[8] = str_replace( '/', '-', $subarray[8] );

					$db_insert_array = array(
						'uniquestudentid' => $subarray[0],
						'classname' => $classname,
						'wpuserid' => $subarray[1],
						'uniqueclassid' =>  $uniqueclassid,
						'registered' =>  $subarray[2],
						'attended' =>  $subarray[3],
						'outcome' =>  $subarray[4],
						'paymentstatus' =>  $subarray[5],
						'amountpaid' =>  $subarray[6],
						'enrollmentdate' =>  $subarray[7],
						'credentialsdate' =>  $subarray[8],
					);

					$db_mask_insert_array = array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					);

					$result = $wpdb->insert(  $table_name, $db_insert_array, $db_mask_insert_array );

					error_log(var_dump($result));
				}
			}
*/

			wp_die( $first_update_result );

		}


		function teqcidb_add_new_class_action_callback(){
			global $wpdb;
			//check_ajax_referer( 'teqcidb_action_callback', 'security' );

			$classname = filter_var($_POST['classname'],FILTER_SANITIZE_STRING);
			$classformat = filter_var($_POST['classformat'],FILTER_SANITIZE_STRING);
			$classtype = filter_var($_POST['classtype'],FILTER_SANITIZE_STRING);
			$classstreetaddress = filter_var($_POST['classstreetaddress'],FILTER_SANITIZE_STRING);
			$classcity = filter_var($_POST['classcity'],FILTER_SANITIZE_STRING);
			$classstate = filter_var($_POST['classstate'],FILTER_SANITIZE_STRING);
			$classzip = filter_var($_POST['classzip'],FILTER_SANITIZE_STRING);
			$classstartdate = filter_var($_POST['classstartdate'],FILTER_SANITIZE_STRING);
			$classstarttime = filter_var($_POST['classstarttime'],FILTER_SANITIZE_STRING);
			$classendtime = filter_var($_POST['classendtime'],FILTER_SANITIZE_STRING);
			$classcost = filter_var($_POST['classcost'],FILTER_SANITIZE_STRING);
			$classsize = filter_var($_POST['classsize'],FILTER_SANITIZE_STRING);
			$classdescription = filter_var($_POST['classdescription'],FILTER_SANITIZE_STRING);
			$superstring = filter_var($_POST['superstring'],FILTER_SANITIZE_STRING);
			$instructorstring = filter_var($_POST['instructorstring'],FILTER_SANITIZE_STRING);

			// Remove any dashes from a Class Name.
			$classname = str_replace( '-', ' ', $classname );


			// Make checks to see if we have a class in the DB with this exact name.
			$table_name = $wpdb->prefix . 'teqcidb_classes';
			$class_flag = false;

			// Check for duplicate class name.
			$class_check = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE classname = %s", $classname ) );

			if( null !== $class_check ){
				$class_flag = true;
			}

			if ( $class_flag ) {

				wp_die( 'Whoops! There\'s already a Class with this exact name!' );

			} else {

				// Create a unique class ID.
				$uniqueclassid =  strtolower( str_replace(' ', '', $classname) ) . time();

				// Building array to add to DB.
				$db_insert_array = array(
					'classname' =>  $classname,
					'classformat' =>  $classformat,
					'classtype' =>  $classtype,
					'classstreetaddress' =>  $classstreetaddress,
					'classcity' =>  $classcity,
					'classstate' =>  $classstate,
					'classzip' =>  $classzip,
					'classstartdate' =>  $classstartdate,
					'classstarttime' =>  $classstarttime,
					'classendtime' =>  $classendtime,
					'classcost' =>  $classcost,
					'classsize' =>  $classsize,
					'classregistrantnumber' =>  0,
					'classdescription' =>  $classdescription,
					'uniqueclassid' =>  $uniqueclassid,
					'instructors' => $instructorstring,
				);

				// Building mask array to add to DB.
				$db_mask_insert_array = array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				);

				// Add this new student to the DB. 
				$result = $wpdb->insert(  $table_name, $db_insert_array, $db_mask_insert_array );

				// Work on structuring data to add Student Information to this class.
				$studentnumber = 0;
				$superstring = explode( ';', $superstring );
				foreach( $superstring as $string ){

					if ( false !== stripos( $string, '-' ) ) {

						$studentnumber++;

						$subarray = explode( '-', $string );
						$subarray[8] = str_replace( '/', '-', $subarray[8] );
						$subarray[9] = str_replace( '/', '-', $subarray[9] );

						$db_insert_array = array(
							'wpuserid' => $subarray[7],
							'classname' => $classname,
							'uniquestudentid' => $subarray[6],
							'uniqueclassid' =>  $uniqueclassid,
							'registered' =>  $subarray[1],
							'attended' =>  $subarray[2],
							'outcome' =>  $subarray[3],
							'paymentstatus' =>  $subarray[4],
							'amountpaid' =>  $subarray[5],
							'enrollmentdate' =>  $subarray[8],
							'credentialsdate' =>  $subarray[9],
						);

						$db_mask_insert_array = array(
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
						);

						$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
						$result = $wpdb->insert(  $table_name, $db_insert_array, $db_mask_insert_array );
					}
				}

				// Now we need to update our new class with the number of students that were just added to the class.
				$table_name = $wpdb->prefix . 'teqcidb_classes'; 
				$data = array(
			        'classregistrantnumber' => $studentnumber, 
			    );
			    $format = array( '%s' ); 
			    $where = array( 'uniqueclassid' => $uniqueclassid );
			    $where_format = array( '%s' );
			    $wpdb->update( $table_name, $data, $where, $format, $where_format );









				wp_die( $result );

			}

			
		}

	}
endif;

/*



function teqcidb_settings_action_javascript() { 
	?>
  	<script type="text/javascript" >
  	"use strict";
  	jQuery(document).ready(function($) {

  		$("#teqcidb-img-remove-1").click(function(event){
  			$('#teqcidb-preview-img-1').attr('src', '<?php echo ROOT_IMG_ICONS_URL ?>'+'book-placeholder.svg');
  		});

  		$("#teqcidb-img-remove-2").click(function(event){
  			$('#teqcidb-preview-img-2').attr('src', '<?php echo ROOT_IMG_ICONS_URL ?>'+'book-placeholder.svg');
  		});



	  	$("#teqcidb-save-settings").click(function(event){

	  		$('#teqcidb-success-div').html('');
	  		$('#teqcidbplugin-spinner-storfront-lib').animate({'opacity':'1'});

	  		var callToAction = $('#teqcidb-call-to-action-input').val();
	  		var libImg = $('#teqcidb-preview-img-1').attr('src');
	  		var bookImg = $('#teqcidb-preview-img-2').attr('src');

		  	var data = {
				'action': 'teqcidb_settings_action',
				'security': '<?php echo wp_create_nonce( "teqcidb_settings_action_callback" ); ?>',
				'calltoaction':callToAction,
				'libimg':libImg,
				'bookimg':bookImg			
			};
			console.log(data);

	     	var request = $.ajax({
			    url: ajaxurl,
			    type: "POST",
			    data:data,
			    timeout: 0,
			    success: function(response) {

			    	$('#teqcidbplugin-spinner-storfront-lib').animate({'opacity':'0'});
			    	$('#teqcidb-success-div').html('<span id="teqcidbplugin-add-book-success-span">Success!</span><br/><br/> You\'ve saved your TEQciDb Settings!<div id="teqcidbplugin-addstylepak-success-thanks">Thanks for using WPBooklist! If you happen to be thrilled with TEQcidbPlugin, then by all means, <a id="teqcidbplugin-addbook-success-review-link" href="https://wordpress.org/support/plugin/teqcidbplugin/reviews/?filter=5">Feel Free to Leave a 5-Star Review Here!</a><img id="teqcidbplugin-smile-icon-1" src="http://evansclienttest.com/wp-content/plugins/teqcidbplugin/assets/img/icons/smile.png"></div>')
			    	console.log(response);
			    },
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(errorThrown);
		            console.log(textStatus);
		            console.log(jqXHR);
				}
			});

			event.preventDefault ? event.preventDefault() : event.returnValue = false;
	  	});
	});
	</script>
	<?php
}


function teqcidb_settings_action_callback(){
	global $wpdb;
	check_ajax_referer( 'teqcidb_settings_action_callback', 'security' );
	$call_to_action = filter_var($_POST['calltoaction'],FILTER_SANITIZE_STRING);
	$lib_img = filter_var($_POST['libimg'],FILTER_SANITIZE_URL);
	$book_img = filter_var($_POST['bookimg'],FILTER_SANITIZE_URL);
	$table_name = TOPLEVEL_PREFIX.'teqcidb_jre_toplevel_options';

	if($lib_img == '' || $lib_img == null || strpos($lib_img, 'placeholder.svg') !== false){
		$lib_img = 'Purchase Now!';
	}

	if($book_img == '' || $book_img == null || strpos($book_img, 'placeholder.svg') !== false){
		$book_img = 'Purchase Now!';
	}

	$data = array(
        'calltoaction' => $call_to_action, 
        'libraryimg' => $lib_img, 
        'bookimg' => $book_img 
    );
    $format = array( '%s','%s','%s'); 
    $where = array( 'ID' => 1 );
    $where_format = array( '%d' );
    echo $wpdb->update( $table_name, $data, $where, $format, $where_format );


	wp_die();
}


function teqcidb_save_default_action_javascript() { 

	$trans1 = __("Success!", 'teqcidbplugin');
	$trans2 = __("You've saved your default Toplevel WooCommerce Settings!", 'teqcidbplugin');
	$trans6 = __("Thanks for using TEQcidbPlugin, and", 'teqcidbplugin');
	$trans7 = __("be sure to check out the TEQcidbPlugin Extensions!", 'teqcidbplugin');
	$trans8 = __("If you happen to be thrilled with TEQcidbPlugin, then by all means,", 'teqcidbplugin');
	$trans9 = __("Feel Free to Leave a 5-Star Review Here!", 'teqcidbplugin');

	?>
  	<script type="text/javascript" >
  	"use strict";
  	jQuery(document).ready(function($) {
	  	$("#teqcidb-woo-settings-button").click(function(event){

	  		$('#teqcidb-woo-set-success-div').html('');
	  		$('.teqcidbplugin-spinner').animate({'opacity':'1'});

	  		var salePrice = $( "input[name='book-woo-sale-price']" ).val();
			var regularPrice = $( "input[name='book-woo-regular-price']" ).val();
			var stock = $( "input[name='book-woo-stock']" ).val();
			var length = $( "input[name='book-woo-length']" ).val();
			var width = $( "input[name='book-woo-width']" ).val();
			var height = $( "input[name='book-woo-height']" ).val();
			var weight = $( "input[name='book-woo-weight']" ).val();
			var sku = $("#teqcidbplugin-addbook-woo-sku" ).val();
			var virtual = $("input[name='teqcidbplugin-woocommerce-vert-yes']").prop('checked');
			var download = $("input[name='teqcidbplugin-woocommerce-download-yes']").prop('checked');
			var salebegin = $('#teqcidbplugin-addbook-woo-salebegin').val();
			var saleend = $('#teqcidbplugin-addbook-woo-saleend').val();
			var purchasenote = $('#teqcidbplugin-addbook-woo-note').val();
			var productcategory = $('#teqcidbplugin-woocommerce-category-select').val();
			var reviews = $('#teqcidbplugin-woocommerce-review-yes').prop('checked');
			var upsells = $('#select2-upsells').val();
			var crosssells = $('#select2-crosssells').val();

			var upsellString = '';
			var crosssellString = '';

			// Making checks to see if Toplevel extension is active
			if(upsells != undefined){
				for (var i = 0; i < upsells.length; i++) {
					upsellString = upsellString+','+upsells[i];
				};
			}

			if(crosssells != undefined){
				for (var i = 0; i < crosssells.length; i++) {
					crosssellString = crosssellString+','+crosssells[i];
				};
			}

			if(salebegin != undefined && saleend != undefined){
				// Flipping the sale date start
				if(salebegin.indexOf('-')){
					var finishedtemp = salebegin.split('-');
					salebegin = finishedtemp[0]+'-'+finishedtemp[1]+'-'+finishedtemp[2]
				}

				// Flipping the sale date end
				if(saleend.indexOf('-')){
					var finishedtemp = saleend.split('-');
					saleend = finishedtemp[0]+'-'+finishedtemp[1]+'-'+finishedtemp[2]
				}	
			}

		  	var data = {
				'action': 'teqcidb_save_action_default',
				'security': '<?php echo wp_create_nonce( "teqcidb_save_default_action_callback" ); ?>',
				'saleprice':salePrice,
				'regularprice':regularPrice,
				'stock':stock,
				'length':length,
				'width':width,
				'height':height,
				'weight':weight,
				'sku':sku,
				'virtual':virtual,
				'download':download,
				'salebegin':salebegin,
				'saleend':saleend,
				'purchasenote':purchasenote,
				'productcategory':productcategory,
				'reviews':reviews,
				'upsells':upsellString,
				'crosssells':crosssellString
			};
			console.log(data);

	     	var request = $.ajax({
			    url: ajaxurl,
			    type: "POST",
			    data:data,
			    timeout: 0,
			    success: function(response) {
			    	console.log(response);


			    	$('#teqcidb-woo-set-success-div').html("<span id='teqcidbplugin-add-book-success-span'><?php echo $trans1 ?></span><br/><br/>&nbsp;<?php echo $trans2 ?><div id='teqcidbplugin-addtemplate-success-thanks'><?php echo $trans6 ?>&nbsp;<a href='http://teqcidbplugin.com/index.php/extensions/'><?php echo $trans7 ?></a><br/><br/>&nbsp;<?php echo $trans8 ?> &nbsp;<a id='teqcidbplugin-addbook-success-review-link' href='https://wordpress.org/support/plugin/teqcidbplugin/reviews/?filter=5'><?php echo $trans9 ?></a><img id='teqcidbplugin-smile-icon-1' src='http://evansclienttest.com/wp-content/plugins/teqcidbplugin/assets/img/icons/smile.png'></div>");

			    	$('.teqcidbplugin-spinner').animate({'opacity':'0'});

			    	$('html, body').animate({
				        scrollTop: $("#teqcidb-woo-set-success-div").offset().top-100
				    }, 1000);
			    },
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(errorThrown);
		            console.log(textStatus);
		            console.log(jqXHR);
				}
			});

			event.preventDefault ? event.preventDefault() : event.returnValue = false;
	  	});
	});
	</script>
	<?php
}

// Callback function for creating backups
function teqcidb_save_default_action_callback(){
	global $wpdb;
	check_ajax_referer( 'teqcidb_save_default_action_callback', 'security' );
	$saleprice = filter_var($_POST['saleprice'],FILTER_SANITIZE_STRING);
	$regularprice = filter_var($_POST['regularprice'],FILTER_SANITIZE_STRING);
	$stock = filter_var($_POST['stock'],FILTER_SANITIZE_STRING);
	$length = filter_var($_POST['length'],FILTER_SANITIZE_STRING);
	$width = filter_var($_POST['width'],FILTER_SANITIZE_STRING);
	$height = filter_var($_POST['height'],FILTER_SANITIZE_STRING);
	$weight = filter_var($_POST['weight'],FILTER_SANITIZE_STRING);
	$sku = filter_var($_POST['sku'],FILTER_SANITIZE_STRING);
	$virtual = filter_var($_POST['virtual'],FILTER_SANITIZE_STRING);
	$download = filter_var($_POST['download'],FILTER_SANITIZE_STRING);
	$woofile = filter_var($_POST['woofile'],FILTER_SANITIZE_STRING);
	$salebegin = filter_var($_POST['salebegin'],FILTER_SANITIZE_STRING);
	$saleend = filter_var($_POST['saleend'],FILTER_SANITIZE_STRING);
	$purchasenote = filter_var($_POST['purchasenote'],FILTER_SANITIZE_STRING);
	$productcategory = filter_var($_POST['productcategory'],FILTER_SANITIZE_STRING);
	$reviews = filter_var($_POST['reviews'],FILTER_SANITIZE_STRING);
	$crosssells = filter_var($_POST['crosssells'],FILTER_SANITIZE_STRING);
	$upsells = filter_var($_POST['upsells'],FILTER_SANITIZE_STRING);


	$data = array(
		'defaultsaleprice' => $saleprice,
		'defaultprice' => $regularprice,
		'defaultstock' => $stock,
		'defaultlength' => $length,
		'defaultwidth' => $width,
		'defaultheight' => $height,
		'defaultweight' => $weight,
		'defaultsku' => $sku,
		'defaultvirtual' => $virtual,
		'defaultdownload' => $download,
		'defaultsalebegin' => $salebegin,
		'defaultsaleend' => $saleend,
		'defaultnote' => $purchasenote,
		'defaultcategory' => $productcategory,
		'defaultreviews' => $reviews,
		'defaultcrosssell' => $crosssells,
		'defaultupsell' => $upsells
	);

 	$table = $wpdb->prefix."teqcidb_jre_toplevel_options";
   	$format = array( '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'); 
    $where = array( 'ID' => 1 );
    $where_format = array( '%d' );
    $result = $wpdb->update( $table, $data, $where, $format, $where_format );

	echo $result;



	wp_die();
}


function teqcidb_upcross_pop_action_javascript() { 
	?>
  	<script type="text/javascript" >
  	"use strict";
  	jQuery(document).ready(function($) {

		  	var data = {
				'action': 'teqcidb_upcross_pop_action',
				'security': '<?php echo wp_create_nonce( "teqcidb_upcross_pop_action_callback" ); ?>',
			};

	     	var request = $.ajax({
			    url: ajaxurl,
			    type: "POST",
			    data:data,
			    timeout: 0,
			    success: function(response) {
			    	response = response.split('–sep-seperator-sep–');
			    	var upsellstitles = '';
			    	var crosssellstitles = '';


			    	if(response[0] != 'null'){
				    	upsellstitles = response[0];
				    	if(upsellstitles.includes(',')){
				    		var upsellArray = upsellstitles.split(',');
				    	} else {
				    		var upsellArray = upsellstitles;
				    	}

				    	$("#select2-upsells").val(upsellArray).trigger('change');
			    	}

			    	if(response[1] != 'null'){
				    	crosssellstitles = response[1];
				    	if(crosssellstitles.includes(',')){
				    		var upsellArray = crosssellstitles.split(',');
				    	} else {
				    		var upsellArray = crosssellstitles;
				    	}

				    	$("#select2-crosssells").val(upsellArray).trigger('change');
			    	}


			    },
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(errorThrown);
		            console.log(textStatus);
		            console.log(jqXHR);
				}
			});


	});
	</script>
	<?php
}

// Callback function for creating backups
function teqcidb_upcross_pop_action_callback(){
	global $wpdb;
	check_ajax_referer( 'teqcidb_upcross_pop_action_callback', 'security' );
		
	// Get saved settings
    $settings_table = $wpdb->prefix."teqcidb_jre_toplevel_options";
    $settings = $wpdb->get_row("SELECT * FROM $settings_table");

    echo $settings->defaultupsell.'–sep-seperator-sep–'.$settings->defaultcrosssell;

	wp_die();
}

/*
// For adding a book from the admin dashboard
add_action( 'admin_footer', 'teqcidb_action_javascript' );
add_action( 'wp_ajax_teqcidb_action', 'teqcidb_action_callback' );
add_action( 'wp_ajax_nopriv_teqcidb_action', 'teqcidb_action_callback' );


function teqcidb_action_javascript() { 
	?>
  	<script type="text/javascript" >
  	"use strict";
  	jQuery(document).ready(function($) {
	  	$("#teqcidbplugin-admin-addbook-button").click(function(event){

		  	var data = {
				'action': 'teqcidb_action',
				'security': '<?php echo wp_create_nonce( "teqcidb_action_callback" ); ?>',
			};
			console.log(data);

	     	var request = $.ajax({
			    url: ajaxurl,
			    type: "POST",
			    data:data,
			    timeout: 0,
			    success: function(response) {
			    	console.log(response);
			    },
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(errorThrown);
		            console.log(textStatus);
		            console.log(jqXHR);
				}
			});

			event.preventDefault ? event.preventDefault() : event.returnValue = false;
	  	});
	});
	</script>
	<?php
}

// Callback function for creating backups
function teqcidb_action_callback(){
	global $wpdb;
	check_ajax_referer( 'teqcidb_action_callback', 'security' );
	//$var1 = filter_var($_POST['var'],FILTER_SANITIZE_STRING);
	//$var2 = filter_var($_POST['var'],FILTER_SANITIZE_NUMBER_INT);
	echo 'hi';
	wp_die();
}*/



