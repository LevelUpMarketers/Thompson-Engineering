<?php
/**
 * TEQcidbPlugin Book Display Options Form Tab Class - class-teqcidbplugin-book-display-options-form.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes/Classes
 * @version  6.1.5.
 */

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TEQcidbPlugin_Authorize_Payments', false ) ) :

	/**
	 * TEQcidbPlugin_Authorize_Payments Class.
	 */
	class TEQcidbPlugin_Authorize_Payments {


		/**
		 * Class Constructor - Simply calls the Translations
		 */
		public function __construct() {

			global $wpdb;
			$this->xboxccnumber = '';
			$this->xboxnameoncard = '';
			$this->xboxexpiremonth = '';
			$this->xboxexpireyears = '';
			$this->classname = '';
			$this->xboxcvv = '';
			$this->paymentamount = '';
			$this->uniquestudentid = '';
			$this->uniqueclassid = '';
			$this->billingstreetaddress = '';
			$this->billingcity = '';
			$this->billingstate = '';
			$this->billingzipcode = '';
			$this->xboxfirstnameoncard = '';
			$this->xboxlastnameoncard = '';
			$this->billingcompany = '';
			$this->billingphonecell = '';
			$this->billingfax = '';
			$this->billingemail = '';
			$this->userbillingtableid = '';
			$this->paymentamount = '';
			$this->paymentamount = '';


		}


		function teqcidb_successful_payment_email_to_student( $classname, $transid, $amount, $email, $name ){

			$admin_message = 
						'Hi ' . $name . '
Thanks for completing your payment! Below are payment details for your records:
Class Name: ' . $classname . '
Transaction ID Number: ' . $transid . '
Amount: ' . $amount . '
A Thompson Engineering Training adminstrator will be verifying your placement in the class, and once finalized, you\'ll receive an email with more class information.';

        error_log('||||||| EMAIL MESSAGE ||||||||');
        error_log($admin_message);
        error_log($email);

        $sent = wp_mail( $email, 'Thanks for your Payment!', $admin_message );
        error_log("|||||| EMAIL |||||");
        error_log(print_r($sent,true));

		}




/*
		/**
		 * Handles basic online payments.

		function teqcidb_make_class_payment_frontend_action_callback(){
			global $wpdb;

			// Let's get some info from our Transaction History table first.
			$table_name = $wpdb->prefix . 'teqcidb_invoicehistory';
			$max_id = $wpdb->get_var( 'SELECT max(ID) FROM `' . $wpdb->prefix . 'teqcidb_invoicehistory`' );

			//include following files for the utilities.
			include 'paytrace/PhpApiSettings.php';
			include 'paytrace/Utilities.php';
			include 'paytrace/Json.php';

			//call a function of Utilities.php to generate oAuth toaken
			$oauth_result = oAuthTokenGenerator();

			$oauth_moveforward = isFoundOAuthTokenError($oauth_result);

			if(!$oauth_moveforward){

				//Decode the Raw Json response.
				$json = jsonDecode($oauth_result['temp_json_response']); 

				//set Authentication value based on the successful oAuth response.
				//Add a space between 'Bearer' and access _token 
				$oauth_token = sprintf("Bearer %s",$json['access_token']);


error_log( 'LOOK HERE' );
error_log( $oauth_token );

				$response = ProtectAuthTokenGenerator($oauth_token);
				$response['orig_oauth_token'] = $oauth_token;

				wp_die( json_encode( $response ) );
			 
			}			
		}
*/


	/*	
		/**
		 * Handles basic online payments.

		function teqcidb_make_class_payment_frontend_actual_action_callback(){

			include 'paytrace/PhpApiSettings.php';

			global $wpdb;

			$hpf_token = filter_var($_POST['hpf_token'],FILTER_SANITIZE_STRING);
			$enc_key = filter_var($_POST['enc_key'],FILTER_SANITIZE_STRING);
			$billingname = filter_var($_POST['billingname'],FILTER_SANITIZE_STRING);
			$billingstreetaddress = filter_var($_POST['billingstreetaddress'],FILTER_SANITIZE_STRING);
			$billingcity = filter_var($_POST['billingcity'],FILTER_SANITIZE_STRING);
			$billingstate = filter_var($_POST['billingstate'],FILTER_SANITIZE_STRING);
			$billingemail = filter_var($_POST['billingemail'],FILTER_SANITIZE_STRING);
			$billingzipcode = filter_var($_POST['billingzipcode'],FILTER_SANITIZE_STRING);
			$amount = filter_var($_POST['amount'],FILTER_SANITIZE_STRING);
			$classname = filter_var($_POST['classname'],FILTER_SANITIZE_STRING);
			$uniqueclassid = filter_var($_POST['uniqueclassid'],FILTER_SANITIZE_STRING);
			$uniquestudentid = filter_var($_POST['uniquestudentid'],FILTER_SANITIZE_STRING);
			$wpuserid = filter_var($_POST['wpuserid'],FILTER_SANITIZE_STRING);
			$oauth_token = filter_var($_POST['oauth'],FILTER_SANITIZE_STRING);
			
			$table_name = $wpdb->prefix . 'teqcidb_studenthistory';
      // Check for duplicate email.
      $has_signed_for_class = $wpdb->get_results("
        SELECT * FROM $table_name 
        WHERE `uniqueclassid` = '$uniqueclassid'
        AND `uniquestudentid` = '$uniquestudentid';
      ");
      
      error_log("||||| has_signed_for_class ||||||");
      error_log(count($has_signed_for_class) > 0);
      
      if(count($has_signed_for_class) > 0) {
        $response = [
          "repsonse_code" => 001,
          "approval_message" => "User has already registered for the class"
        ];
        wp_die( json_encode($response) );
      }

			// First build the request data
	       $hpf_token = $hpf_token;
	       $enc_key = $enc_key;
	       $amount = $_POST['amount'];
	        $request_data = array(
	                        "amount" => $amount,
	                        "hpf_token"=> $hpf_token,
	                        "enc_key"=> $enc_key,
	                        "integrator_id"=>"9623516x0w5W",
	                        "billing_address"=> array(
	                            "name"=> $billingname,
	                            "street_address"=> $billingstreetaddress,
	                            "city"=> $billingcity,
	                            "state"=> $billingstate,
	                            "zip"=> $billingzipcode)
	                        );

          $origin_request_data = $request_data;
	        $request_data = json_encode($request_data);


	         // Now actually process the sale
	         // array variable to store the Response value, httpstatus code and curl error.
	         $result = array(
	                      'temp_json_response' => '',
	                      'curl_error' => '',
	                      'http_status_code' => '');

	         // create a new cURL resource
	         $ch = curl_init();

	         // set the header
	         //$header = array ('Content-type: application/json','Authorization:'.$oauth_token);
	         $header[] = 'Content-type: application/json';
	         $header[] = 'Authorization:'.$oauth_token;
	         // set URL and other appropriate options for curl to make the request
	         curl_setopt($ch, CURLOPT_URL, URL_PROTECT_SALE);
	         curl_setopt($ch, CURLOPT_POST, true);
	         curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	         curl_setopt($ch, CURLOPT_HEADER, true);
	         curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	         curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);
	         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	         // following SSL settings should be removed in production code.
	         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
	         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

	         //Execute the request
	         $response = curl_exec($ch);
	         $curl_error = curl_error($ch).' '. curl_errno($ch);


	         if($response === false){
	             $result['curl_error'] = $curl_error;

	             // close cURL resource, and free up system resources
	             curl_close($ch);
	             return $result ;
	         }
	         //collect the output data.

	         $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	         $result['json_response'] = substr($response,$header_size);
	         $result['temp_json_response'] = $response ;
	         $result['http_status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	         
	         $response_decoded = json_decode($result['json_response']);
	         $response_decoded->date = date('Y-m-d H:i:s', time());
	         
	         $response_with_date = json_encode($response_decoded);
	         
	         error_log("|||||||| response_decoded |||||||||");
	         error_log(print_r($response_decoded, true));
	         
	         if($response_decoded->response_code == 101) {
	          $insert_table = $wpdb->prefix . "teqcidb_studenthistory";
            $insert_values = [
              "uniquestudentid" => $uniquestudentid, 
              "classname" => $classname, 
              "wpuserid" => $wpuserid, 
              "uniqueclassid" => $uniqueclassid, 
              "registered" => "Pending Approval", 
              "paymentstatus" => "Paid in Full", 
              "amountpaid" => $amount, 
              "enrollmentdate" => date('Y-m-d H:i:s', time()), 
              "transactionid" => $response_decoded->transaction_id
              
            ];
            $wpdb->insert( $insert_table, $insert_values );
            
	          $insert_table = $wpdb->prefix . "teqcidb_invoicehistory";
            $insert_values = [
              "uniquestudentid" => $uniquestudentid, 
              "uniqueclassid" => $uniqueclassid, 
              "classname" => $classname, 
              "wpuserid" => $wpuserid, 
              "amountactuallypaid" => $amount, 
              "transid" => $response_decoded->transaction_id,
              "transtime" => date('Y-m-d H:i:s', time()),
            ];
            $wpdb->insert( $insert_table, $insert_values );

            $this->teqcidb_successful_payment_email_to_student( $classname, $response_decoded->transaction_id, "$".$amount, $billingemail, $billingname );
	         }
	         

	         // close cURL resource, and free up system resources
	         curl_close($ch);

	         wp_die( $response_with_date );

		}
*/




	//OLD AUTHORIZE.NET PAYMENT GATEWAY DEAL BELOW!
		/**
		 * Handles basic online payments.
		 **/
		
		function teqcidb_make_class_payment_frontend_action_callback(){

			global $wpdb;
			$table_name = $wpdb->prefix . 'teqcidb_invoicehistory';
			$max_id = $wpdb->get_var( 'SELECT max(ID) FROM `' . $wpdb->prefix . 'teqcidb_invoicehistory`' );
			$xboxccnumber = filter_var( $_POST['xboxccnumber'],FILTER_SANITIZE_STRING );
			$xboxfirstnameoncard = filter_var( $_POST['xboxfirstnameoncard'],FILTER_SANITIZE_STRING );
			$xboxlastnameoncard = filter_var( $_POST['xboxlastnameoncard'],FILTER_SANITIZE_STRING );
			$xboxexpiremonth = filter_var( $_POST['xboxexpiremonth'],FILTER_SANITIZE_STRING );
			$xboxexpireyears = filter_var( $_POST['xboxexpireyear'],FILTER_SANITIZE_STRING );
			$classname = filter_var( $_POST['classname'],FILTER_SANITIZE_STRING );
			$xboxcvv = filter_var( $_POST['xboxcvv'],FILTER_SANITIZE_STRING );
			$paymentamount = filter_var( $_POST['paymentamount'],FILTER_SANITIZE_STRING );
			$uniquestudentid = filter_var( $_POST['uniquestudentid'],FILTER_SANITIZE_STRING );
			$uniqueclassid = filter_var( $_POST['uniqueclassid'],FILTER_SANITIZE_STRING );
			$billingstreetaddress = filter_var( $_POST['billingstreetaddress'],FILTER_SANITIZE_STRING );
			$billingcity = filter_var( $_POST['billingcity'],FILTER_SANITIZE_STRING );
			$billingstate = filter_var( $_POST['billingstate'],FILTER_SANITIZE_STRING );
			$billingzipcode = filter_var( $_POST['billingzipcode'],FILTER_SANITIZE_STRING );
			$billingcompany = filter_var( $_POST['billingcompany'],FILTER_SANITIZE_STRING );
			$billingphonecell = filter_var( $_POST['billingphonecell'],FILTER_SANITIZE_STRING );
			$billingfax = filter_var( $_POST['billingfax'],FILTER_SANITIZE_STRING );
			$billingemail = filter_var( $_POST['billingemail'],FILTER_SANITIZE_STRING );
			$userbillingtableid = filter_var( $_POST['userbillingtableid'],FILTER_SANITIZE_STRING );
			$emailreceiptsto = filter_var( $_POST['emailreceiptsto'],FILTER_SANITIZE_STRING );
			$paymentamount = str_replace('$', '', $paymentamount );
			$paymentamount = (float)$paymentamount;

			error_log($emailreceiptsto);
			error_log( $emailreceiptsto . " WTF!!!\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );

			// Modify the expiration year to include "20" at the beginning if the user didn't type in the full year.
			if ( strlen( $xboxexpireyears ) == 2 ) {
			    $xboxexpireyears = "20" . $xboxexpireyears;
			}

		
			// Common setup for API credentials  
			$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();   
			$merchantAuthentication->setName( "" );   
			$merchantAuthentication->setTransactionKey( "" );   
			$refId = 'ref' . time();
			// Create the payment data for a credit card
			$creditCard = new AnetAPI\CreditCardType();
			$creditCard->setCardNumber( $xboxccnumber );  
			$creditCard->setExpirationDate( $xboxexpireyears . "-" . $xboxexpiremonth );
			$creditCard->setCardCode( $xboxcvv );
			// Add the payment data to the paymentType object.
			$paymentOne = new AnetAPI\PaymentType();
			$paymentOne->setCreditCard( $creditCard );
			// Create order information
  		$order = new AnetAPI\OrderType();
  		$order->setInvoiceNumber( $max_id );
  		$order->setDescription( $classname );
  		// Set the customer's Bill To address
	    $customerAddress = new AnetAPI\CustomerAddressType();
	    $customerAddress->setFirstName( $xboxfirstnameoncard );
	    $customerAddress->setLastName( $xboxlastnameoncard );
	    $customerAddress->setCompany( $billingcompany );
	    $customerAddress->setAddress( $billingstreetaddress );
	    $customerAddress->setCity( $billingcity );
	    $customerAddress->setState( $billingstate );
	    $customerAddress->setZip( $billingzipcode );
	    $customerAddress->setCountry( 'USA' );
	    // Set the customer's identifying information
	    $customerData = new AnetAPI\CustomerDataType();
	    $customerData->setType( 'individual' );
	    $customerData->setId( $userbillingtableid );
	    $customerData->setEmail( $billingemail );
			// Create a TransactionRequestType object and add the previous objects to it
			$transactionRequestType = new AnetAPI\TransactionRequestType();
			$transactionRequestType->setTransactionType( "authCaptureTransaction" );   
			$transactionRequestType->setAmount( $paymentamount );
			$transactionRequestType->setOrder( $order );
			$transactionRequestType->setPayment( $paymentOne );
			$transactionRequestType->setBillTo( $customerAddress);
  		$transactionRequestType->setCustomer( $customerData);
  		// Assemble the complete transaction request
			$request = new AnetAPI\CreateTransactionRequest();
			$request->setMerchantAuthentication( $merchantAuthentication );
			$request->setRefId( $refId);
			$request->setTransactionRequest( $transactionRequestType );
			// Create the controller and get the response
			$controller = new AnetController\CreateTransactionController( $request );
			$response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION );  

			if ( $response != null) {
	        // Check to see if the API request was successfully received and acted upon
	        if ( $response->getMessages()->getResultCode() == "Ok") {
            // Since the API request was successful, look for a transaction response and parse it to display the results of authorizing the card.
            $tresponse = $response->getTransactionResponse();
		        
		        if ( $tresponse != null && $tresponse->getMessages() != null ) {
            	$message1  =  "\n\n" . ' Time of Logging: ' . $refId . "\n";
            	$message2  =  ' ' . $xboxfirstnameoncard . ' ' . $xboxlastnameoncard . "\n";
            	$message3  =  ' teqcidb_students Table ID #: ' . $userbillingtableid . "\n";
            	$message4  =  ' Class Name: ' . $classname . "\n";
            	$message5  =  ' Amount Charged: ' . $paymentamount . "\n";
              $message6  =  ' Successfully created transaction with Transaction ID: ' . $tresponse->getTransId() . "\n";
              $message7  = ' Transaction Response Code: ' . $tresponse->getResponseCode() . "\n";
              $message8  = ' Message Code: ' . $tresponse->getMessages()[0]->getCode() . "\n";
              $message9  = ' Auth Code: ' . $tresponse->getAuthCode() . "\n";
              $message10 = ' Description: ' . $tresponse->getMessages()[0]->getDescription() . "\n\n";
              $message11 = ' Email: ' . $billingemail . "\n";
              $message12 = ' Phone: ' . $billingphonecell . "\n";

              // Record info in the log.
              error_log( $message1 . $message2 . $message3 . $message4 . $message5 . $message11 . $message12 . $message6 . $message7 . $message8 . $message9 . $message10, 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
              
							// Now let's update the user's Billing info in the teqcidb_students table.
							$data = array(
								'billingstreetaddress' => $billingstreetaddress,
								'billingcity' => $billingcity,
								'billingstate' => $billingstate,
								'billingzip' => $billingzipcode,
							);
							$format = array(
								'%s',
								'%s',
								'%s',
								'%s',
							);
							$table_name = $wpdb->prefix . 'teqcidb_students';
							$where = array( 'uniquestudentid' => ( $uniquestudentid ) );
							$where_format = array( '%s' );
							$update_billing_result = $wpdb->update( $table_name, $data, $where, $format, $where_format );

							// Record info in the log.
							if ( 1 === $update_billing_result ) {
								error_log( " Succesfully updated the Student's Billing information in the teqcidb_student table.\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							}

							// Now we need to insert this class into the studenthistory table.
							$table_name = $wpdb->prefix . 'teqcidb_studenthistory';

		          $db_insert_array = array(
								'uniquestudentid' => $uniquestudentid,
								'classname' => $classname,
								'wpuserid' => $userbillingtableid,
								'uniqueclassid' => $uniqueclassid,
								'registered' => 'pending',
								'attended' => 'upcoming',
								'outcome' => 'upcoming',
								'paymentstatus' => 'pending',
								'amountpaid' => $paymentamount,
								'enrollmentdate' => date("Y/m/d"),
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

							if ( 1 === $result ) {
								error_log( " Successfully added a new record into the teqcidb_studenthistory table\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							} else {
								error_log( " Unsuccessfully attempted to add a new record into the teqcidb_studenthistory table\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							}

							// Now we need to update the class itself and add one new registrant to the Class so we don't overbook. First we'll get the current number of registrants, then add one to that, then update that column in the 'classes' table.

							$table_name = $wpdb->prefix . 'teqcidb_classes';

							$updateregistrants = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniqueclassid = %s", $uniqueclassid ) );
							$newcurrentregistrants = (int)$updateregistrants->classregistrantnumber + 1;
							$data = array(
					        'classregistrantnumber' => $newcurrentregistrants, 
					    );
					    $format = array( '%s' ); 
					    $where = array( 'uniqueclassid' => $uniqueclassid );
					    $where_format = array( '%s' );
					    $result = $wpdb->update( $table_name, $data, $where, $format, $where_format );

					    if ( 1 === $result ) {
								error_log( " Successfully updated the number of registered students for this class by 1\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							}
							
							// if the Transaction was flagged as "suspicious" and must be manually completed by the admin.
							if ( '252' === $tresponse->getMessages()[0]->getCode() ) {
								//wp_die( '1----Success! Thank you for your payment. You\'ll receive an email receipt of your payment shortly. Your Transaction ID number is: ' . $tresponse->getTransId() );
							}

							// Now let's email the student some information for their records.
							$response = '';

							if ( 'inperson' === $updateregistrants->classformat ) {

								// Format the dates...
			          $startdate_array = explode('-', $updateregistrants->classstartdate);
			          $tempformattedstartdate = $startdate_array[1] . '-' . $startdate_array[2] . '-' . $startdate_array[0];
			         // Format Times.
          			$tempformattedstarttime = date('g:i a', strtotime($updateregistrants->classstarttime));


							$admin_message = 
							'Hi ' . $xboxfirstnameoncard . ',<br/><br/>
							Thanks for completing your payment! Below are payment details for your records:<br/><br/>
							<b>Class Name:</b> ' . $classname . '<br/>
							<b>Transaction ID Number:</b> ' . $tresponse->getTransId() . '<br/>
							<b>Amount:</b> $' . $paymentamount . '.00<br/><br/>
							This email is to confirm that you are enrolled in a Thompson Engineering QCI training course.  This course will take place on  ' . $tempformattedstartdate . ' at ' . $tempformattedstarttime . '.  The address is ' . $updateregistrants->classstreetaddress . ',  ' . $updateregistrants->classcity . ', ' . $updateregistrants->classstate . ' ' . $updateregistrants->classzip . '. You will receive a confirmation email with additional class details prior to the date of the class.';
							} else {
							$admin_message = 
							'Hi ' . $xboxfirstnameoncard . ',<br/><br/>
							Thanks for completing your payment! Below are payment details for your records:<br/><br/>
							<b>Class Name:</b> ' . $classname . '<br/>
							<b>Transaction ID Number:</b> ' . $tresponse->getTransId() . '<br/>
							<b>Amount:</b> $' . $paymentamount . '.00<br/><br/>
							This email is to confirm that you are enrolled in a Thompson Engineering QCI training course.  You will receive an email within the next 24 hours from trainer@learn.trakstar.com inviting you to the course.  Click on the link inside that invitation email to access the course.  If you have taken an online course from us in the past and do not remember your Trakstar password, please click on the “Forgot Password?” link on the Trakstar login page to reset your password. ';
							}


							// Now start emailing receipts to others if any have been specified...
							// Initialize the emails array
							$emailsarray = [];

							// Check if $emailreceiptsto has content and populate the array accordingly
							if (!empty($emailreceiptsto)) {
									error_log( " in first if\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							    $emailsarray = array_filter(array_map('trim', explode(',', $emailreceiptsto)));
							}

							// Loop through emails if any, or handle empty scenario
							if (!empty($emailsarray)) {
									error_log( " in second if\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							    foreach ($emailsarray as $indivemail) {
							    	error_log( " in foreach\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							        if (is_email($indivemail)) {
							        	error_log( " in third if\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );

														$additionalemailreceipts = new \SendGrid\Mail\Mail(); 
														$additionalemailreceipts->setFrom( "qci@thompsonengineering.com", "Thompson Engineering");
														$additionalemailreceipts->setSubject( 'Thank you for your payment!' );
														$additionalemailreceipts->addTo( $indivemail, $xboxfirstnameoncard . ' ' . $xboxlastnameoncard );
														$additionalemailreceipts->addContent( 'text/html', $admin_message );
														$sendgrid = new SendGrid("");
														try {
													    $response = $sendgrid->send($additionalemailreceipts);
													    $result = '1';
													    error_log( " sssSuccessfully emailed the student their confirmation/next steps email\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
														} catch (Exception $e) {
															$result = '0';
															$response = 'Caught exception: '. $e->getMessage();
															error_log( " Unsuccessfully attempted to email the student their confirmation/next steps email: " . $e->getMessage() . "  \n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
														}
							            // Logic for each valid email
							            // e.g., wp_mail($indivemail, $subject, $message);
							        } else {
							            // Handle invalid emails
							            error_log( " Invalid email detected\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							        }
							    }
							} else {
							    // Handle scenario where no valid emails are available
								error_log( " No email addresses provided or all invalid\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							}

							// Now send the regular, original emails to the logged-in person...
							$email = new \SendGrid\Mail\Mail(); 
							$email->setFrom( "qci@thompsonengineering.com", "Thompson Engineering");
							$email->setSubject( 'Thank you for your payment!' );
							$email->addTo( $billingemail, $xboxfirstnameoncard . ' ' . $xboxlastnameoncard );
							$email->addContent( 'text/html', $admin_message );
							$sendgrid = new SendGrid("");
							try {
						    $response = $sendgrid->send($email);
						    $result = '1';
						    error_log( " Yo - Successfully emailed the student their confirmation/next steps email\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							} catch (Exception $e) {
								$result = '0';
								$response = 'Caught exception: '. $e->getMessage();
								error_log( " Unsuccessfully attempted to email the student their confirmation/next steps email: " . $e->getMessage() . "  \n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							}

							// Now let's send a copy of that same email to Thompson admins through SENDGRID, with additional info about who the logged-in user actually is, so Thompson admins can associate the authorize.net email billing info on the card that was used, wwith the student and/or the person sigining them up (aka, the logged-in user).
							$table_name = $wpdb->prefix . 'teqcidb_students';
							$student = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE uniquestudentid = %s", $uniquestudentid ) );

							if ( 'inperson' === $updateregistrants->classformat ) {

								// Format the dates...
			          $startdate_array = explode('-', $updateregistrants->classstartdate);
			          $tempformattedstartdate = $startdate_array[1] . '-' . $startdate_array[2] . '-' . $startdate_array[0];
			         // Format Times.
          			$tempformattedstarttime = date('g:i a', strtotime($updateregistrants->classstarttime));




							$admin_message = 
							'Hi ' . $xboxfirstnameoncard . ',<br/><br/>
							Thanks for completing your payment! Below are payment details for your records:<br/><br/>
							<b>Class Name:</b> ' . $classname . '<br/>
							<b>Transaction ID Number:</b> ' . $tresponse->getTransId() . '<br/>
							<b>Amount:</b> $' . $paymentamount . '.00<br/><br/>
							<b>Logged-In Person\'s QCI Number:</b> ' . $student->qcinumber . '<br/><br/>
							<b>Logged-In Person\'s Expiration Date:</b> ' . date('m-d-Y', strtotime( $student->expirationdate ) ) . '<br/><br/>
							<b>Logged-In Person\'s Company:</b> ' . $student->company . '<br/><br/>
							<b>Logged-In Person Accomplishing Payment:</b> ' . $student->firstname . ' ' . $student->lastname . ' ' . $student->email . '<br/><br/>
							This email is to confirm that you are enrolled in a Thompson Engineering QCI training course.  This course will take place on  ' . $tempformattedstartdate . ' at ' . $tempformattedstarttime . '.  The address is ' . $updateregistrants->classstreetaddress . ',  ' . $updateregistrants->classcity . ', ' . $updateregistrants->classstate . ' ' . $updateregistrants->classzip . '. You will receive a confirmation email with additional class details prior to the date of the class.';




							} else {



							$admin_message = 
							'Hi ' . $xboxfirstnameoncard . ',<br/><br/>
							Thanks for completing your payment! Below are payment details for your records:<br/><br/>
							<b>Class Name:</b> ' . $classname . '<br/>
							<b>Transaction ID Number:</b> ' . $tresponse->getTransId() . '<br/>
							<b>Amount:</b> $' . $paymentamount . '.00<br/><br/>
							<b>Logged-In Person\'s QCI Number:</b> ' . $student->qcinumber . '<br/><br/>
							<b>Logged-In Person\'s Expiration Date:</b> ' . date('m-d-Y', strtotime( $student->expirationdate ) ) . '<br/><br/>
							<b>Logged-In Person\'s Company:</b> ' . $student->company . '<br/><br/>
							<b>Logged-In Person Accomplishing Payment:</b> ' . $student->firstname . ' ' . $student->lastname . ' ' . $student->email . '<br/><br/>
							This email is to confirm that you are enrolled in a Thompson Engineering QCI training course.  You will receive an email within the next 24 hours from trainer@learn.trakstar.com inviting you to the course.  Click on the link inside that invitation email to access the course.  If you have taken an online course from us in the past and do not remember your Trakstar password, please click on the “Forgot Password?” link on the Trakstar login page to reset your password. '; 
							}

/*

							$email = new \SendGrid\Mail\Mail(); 
							$email->setFrom( "qci@thompsonengineering.com", "Thompson Engineering");
							$email->setSubject( 'Someone registered and paid online!' );
							$email->addTo( "qci@thompsonengineering.com", 'Stormwater Training Coordinators' );
							$email->addContent( 'text/html', $admin_message );
							$sendgrid = new SendGrid("");
							try {
						    $response = $sendgrid->send($email);
						    $result = '1';
						    error_log( " Sent Thompson admins a copy of the student's confirmation/next steps email via Sendgrid\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							} catch (Exception $e) {
								$result = '0';
								$response = 'Caught exception: '. $e->getMessage();
								error_log( " Unsuccessfully attempted to send Thompson admins a copy of the student's confirmation/next steps email via Sendgrid\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
							}

							// Send myself a copy.
							$email = new \SendGrid\Mail\Mail(); 
							$email->setFrom( "qci@thompsonengineering.com", "Thompson Engineering");
							$email->setSubject( 'Someone registered and paid online!' );
							$email->addTo( "jevans@highlevelmarketing.com", 'Stormwater Training Coordinators' );
							$email->addContent( 'text/html', $admin_message );
							$sendgrid = new SendGrid("");
							try {
						    $response = $sendgrid->send($email);
						    $result = '1';
							} catch (Exception $e) {
								$result = '0';
								$response = 'Caught exception: '. $e->getMessage();
							}
*/
							// Now let's send a copy of that same email the Thompson through regualr WordPress functions, as emails from Sendgrid might be blocked.
						//	$headers[] = 'Content-Type: text/html; charset=UTF-8';
						//	$headers[] = 'From: Thompson Engineering <qci@thompsonengineering.com>';

			      //  $sent = wp_mail( "qci@thompsonengineering.com", 'Someone registered and paid online!', $admin_message, $headers );

			        //$sent_to_cindy = wp_mail( "croton@thompsonengineering.com", 'Someone registered and paid online!', $admin_message, $headers );

			        //$sent_to_ilka = wp_mail( "iporter@thompsonengineering.com", 'Someone registered and paid online!', $admin_message, $headers );
/*

			        $diferent_headers_test[] = 'Content-Type: text/html; charset=UTF-8';
			        $diferent_headers_test[] = 'From: Training Website <noreply@training.thompsonengineering.com>';

			        $sent_2 = wp_mail( "qci@thompsonengineering.com", 'Someone registered and paid online!', $admin_message, $diferent_headers_test );

			        $sent_to_cindy_2 = wp_mail( "croton@thompsonengineering.com", 'Someone registered and paid online!', $admin_message, $diferent_headers_test );

			        $sent_to_ilka_2 = wp_mail( "iporter@thompsonengineering.com", 'Someone registered and paid online!', $admin_message, $diferent_headers_test );

			        if ( $sent ) {
			        	error_log( " Sent Thompson admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        } else {
			        	error_log( " Unsuccessfully attempted to send Thompson admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        }

			        if ( $sent_to_cindy ) {
			        	error_log( " Sent Cindy admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        } else {
			        	error_log( " Unsuccessfully attempted to send Cindy admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        }

			        if ( $sent_to_ilka ) {
			        	error_log( " Sent ilka admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        } else {
			        	error_log( " Unsuccessfully attempted to send ilka admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        }



if ( $sent2 ) {
			        	error_log( " Sent2 Thompson admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        } else {
			        	error_log( " Unsuccessfully attempted to send Thompson admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        }

			        if ( $sent_to_cindy_2 ) {
			        	error_log( " Sent Cindy2 admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        } else {
			        	error_log( " Unsuccessfully attempted to send Cindy admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        }

			        if ( $sent_to_ilka_2 ) {
			        	error_log( " Sent ilka2 admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        } else {
			        	error_log( " Unsuccessfully attempted to send ilka admins a copy of the student's confirmation/next steps email via wp_mail()\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
			        }

*/


// Now let's email the Thompson admins the newly-made transaction info via WordPress!
						$paid_admin_message = 
						'Hi ' . $xboxfirstnameoncard . ',

Thanks for completing your payment! Below are payment details for your records:

Class Name: ' . $classname . '
Transaction ID Number: ' . $tresponse->getTransId() . '
Amount: $' . $paymentamount . '.00
Logged-In Person Accomplishing Payment: ' . $student->firstname . ' ' . $student->lastname . ' ' . $student->email . ' 
Logged-In Person\'s Company: ' . $student->company . ' 
Logged-In Person\'s QCI Number: ' . $student->qcinumber . '
Logged-In Person\'s Expiration Date: ' . date('m-d-Y', strtotime( $student->expirationdate ) ) . '
Alternate Contact Name: ' . $student->altcontactname . '
Alternate Contact Email: ' . $student->altcontactemail . '
Alternate Contact Phone: ' . $student->altcontactphone . '

Be sure to visit ' . $markasviewed . ' to review this student\'s info, and to click the "mark as viewed" button.' ;



						wp_mail( 'qci@thompsonengineering.com,croton@thompsonengineering.com,jevans@highlevelmarketing.com,iporter@thompsonengineering.com', 'Someone registered and paid online!', $paid_admin_message);











			        // Send myself a copy.
			       // $sent = wp_mail( "jevans@highlevelmarketing.com", 'Someone registered and paid online!', $admin_message, $headers );

			        // now set the newpaymentflag.
							$data = array(
								'newpaymentflag' =>  'true',
							);

							$format = array(
								'%s',
							);
							$where        = array( 'uniquestudentid' => $uniquestudentid );
							$where_format = array( '%s' );
							$table_name = $wpdb->prefix . 'teqcidb_students';
							$first_update_result = $wpdb->update( $table_name, $data, $where, $format, $where_format );


							// If everything went perfectly ok!
							wp_die( '2----Success! Thank you for your payment. You\'ll receive an email receipt of your payment shortly. Your Transaction ID number is: ' . $tresponse->getTransId() );
							error_log( " Reporting to the Student that everything was succesful. Here's their Transaction ID: " . $tresponse->getTransId() . " \n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );

						


            } else {
	            if ( $tresponse->getErrors() != null) {
	            	$message1 =  "\n\n" . ' Time of Logging: ' . $refId . "\n";
	            	$message2 =  ' ' . $xboxfirstnameoncard . ' ' . $xboxlastnameoncard . "\n";
	            	$message3 =  ' teqcidb_students Table ID #: ' . $userbillingtableid . "\n";
	            	$message4 =  ' Class Name: ' . $classname . "\n";
	            	$message5 =  ' Amount Charged: ' . $paymentamount . "\n";
	              $message6 = " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
	              $message7 = " Text to Ctrl+F  : 3----error\n";
	              $message8 = " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n\n";
	              error_log( ' Something went wrong: ' . $message1 . $message2 . $message3 . $message4 . $message5 . $message6 . $message7 . $message8, 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
	              wp_die( '3----error.' . $tresponse->getErrors()[0]->getErrorText() . '<br/><br/>Looks like there was an error while completing your payment. Please double-check the information above and try again. Be sure to provide the billing information that is associated with the payment method you\'re attempting to use.' );
	            }
            }
            // Or, print errors if the API request wasn't successful
	        } else {
            $tresponse = $response->getTransactionResponse();
        
            if ( $tresponse != null && $tresponse->getErrors() != null) {
                $message1 =  "\n\n" . ' Time of Logging: ' . $refId . "\n";
	            	$message2 =  ' ' . $xboxfirstnameoncard . ' ' . $xboxlastnameoncard . "\n";
	            	$message3 =  ' teqcidb_students Table ID #: ' . $userbillingtableid . "\n";
	            	$message4 =  ' Class Name: ' . $classname . "\n";
	            	$message5 =  ' Amount Charged: ' . $paymentamount . "\n";
                $message6 = " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                $message7 = " Text to Ctrl+F  : 4----error\n";
                $message8 = " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n\n";
                error_log( ' Something went wrong: ' . $message1 . $message2 . $message3 . $message4 . $message5 . $message6 . $message7 . $message8, 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
                wp_die( '4----error.' . $tresponse->getErrors()[0]->getErrorText() . '<br/><br/>Looks like there was an error while completing your payment. Please double-check the information above and try again. Be sure to provide the billing information that is associated with the payment method you\'re attempting to use.' );
            } else {
                $message1 = "\n\n Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
                $message3 = " Text to Ctrl+F  : 5----error\n";
                $message2 = " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n\n";
                error_log( ' Something went wrong: ' . $message1 . $message2 . $message3, 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
                wp_die( '5----error.' . $tresponse->getErrors()[0]->getErrorText() . '<br/><br/>Looks like there was an error while completing your payment. Please double-check the information above and try again. Be sure to provide the billing information that is associated with the payment method you\'re attempting to use.' );
            }
	        }
		    } else {
		    	error_log( "\n\n No response returned \n\n", 3, TEQCIDB_LOGS_DIR . 'paymentlog.log' );
		    	wp_die( '6----error.' . $tresponse->getErrors()[0]->getErrorText() . '<br/><br/>Looks like there was an error while completing your payment. Please double-checkrmation provided above and try again. Be sure to provide the billing information that is associated with the payment method you\'re attempting to use.' );
		 	}

			wp_die( 'yo yo yo' );
						
		}
	


	}
endif;