<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Protect Update Customer</title>
    </head>
    <body>
        <br>
            <a href="Default.html">Back to Home </a>
        <br>
 <?php
    // Access the encyrpted request fields and store the Encrypted Value
    // Send those values in the API request by prepending KeyName with 'encrypted_'.

    include 'PhpApiSettings.php';
    include 'Utilities.php';
    include 'Json.php';


    //call a function of Utilities.php to generate oAuth toaken
    $oauth_result = oAuthTokenGenerator();

    $oauth_moveforward = isFoundOAuthTokenError($oauth_result);

    //If IsFoundOAuthTokenError results True, means no error
    //next is to move forward for the actual request

    if(!$oauth_moveforward){
        //Decode the Raw Json response.
        $json = jsonDecode($oauth_result['temp_json_response']);
        //displayOAuth($json);
        //set Authentication value based on the successful oAuth response.
        //Add a space between 'Bearer' and access _token
        $oauth_token = sprintf("Bearer %s",$json['access_token']);

    }




      buildTransaction($oauth_token);

    //end of main script

    function buildTransaction($oauth_token){
        // Build the request data
        $request_data = buildRequestData();

        //call to make the actual request
        $result = processTransaction($oauth_token,$request_data, URL_PROTECT_UPDATE_CUSTOMER);


        //check the result
        verifyTransactionResult($result);
    }


    function buildRequestData(){
       //you can assign the values from any input source fields instead of hard coded values.
       $hpf_token = $_POST['HPF_Token'];
       $enc_key = $_POST['enc_key'];
       $customer_id = $_POST['customerId'];
       $name = $_POST['name'];
       $address = $_POST['address'];
       $city = $_POST['city'];
       $state = $_POST['state'];
       $zip = $_POST['zip'];
       $request_data = array(
                       "customer_id" => $customer_id,
                       "hpf_token"=> $hpf_token,
                       "enc_key"=> $enc_key,
                       "integrator_id"=>"9623516x0w5W",
                       "billing_address"=> array(
                           "name"=> $name,
                           "street_address"=> $address,
                           "city"=> $city,
                           "state"=> $state,
                           "zip"=> $zip)
                            );

        $request_data = json_encode($request_data);

        //optional : Display the Jason response - this may be helpful during initial testing.
        displayRawJsonRequest($request_data);

        return $request_data ;
    }

    //This function is to verify the Transaction result
    function verifyTransactionResult($trans_result){

    //Handle curl level error, ExitOnCurlError
    if($trans_result['curl_error'] ){
        echo "<br>Error occcured : ";
        echo '<br>curl error with Transaction request: ' . $trans_result['curl_error'] ;
        exit();
    }

    //If we reach here, we have been able to communicate with the service,
    //next is decode the json response and then review Http Status code, response_code and success of the response

    $json = jsonDecode($trans_result['temp_json_response']);

    if($trans_result['http_status_code'] != 200){
        if($json['success'] === false){
            echo "<br><br>Transaction Error occurred : ";

            //Optional : display Http status code and message
            displayHttpStatus($trans_result['http_status_code']);

            //Optional :to display raw json response
            displayRawJsonResponse($trans_result['temp_json_response']);

            echo "<br>Keyed sale :  failed !";
            //to display individual keys of unsuccessful Transaction Json response
            displayKeyedTransactionError($json) ;
        }
        else {
            //In case of some other error occurred, next is to just utilize the http code and message.
            echo "<br><br> Request Error occurred !" ;
            displayHttpStatus($trans_result['http_status_code']);
        }
    }
    else
    {
        // Optional : to display raw json response - this may be helpful with initial testing.
        displayRawJsonResponse($trans_result['temp_json_response']);

        // Do your code when Response is available and based on the response_code.
        // Please refer PayTrace-Error page for possible errors and Response Codes

        // For transation successfully approved
        if($json['success']== true && $json['response_code'] == 161){

            echo "<br><br>Keyed sale :  Success !";
            displayHttpStatus($trans_result['http_status_code']);
            //to display individual keys of successful OAuth Json response
            displayKeyedTransactionResponse($json);
       }
       else{
            //Do you code here for any additional verification such as - Avs-response and CSC_response as needed.
            //Please refer PayTrace-Error page for possible errors and Response Codes
            //success = true and response_code == 103 approved but voided because of CSC did not match.
       }
    }

    }


    //This function displays keyed transaction successful response.
    function displayKeyedTransactionResponse($json_string){

        //optional : Display the output

        echo "<br><br> Keyed Sale Response : ";
        //since php interprets boolean value as 1 for true and 0 for false when accessed.
        echo "<br>success : ";
        echo $json_string['success'] ? 'true' : 'false';
        echo "<br>response_code : ".$json_string['response_code'] ;
        echo "<br>status_message : ".$json_string['status_message'] ;
        echo "<br>customer_id : ".$json_string['customer_id'];

    }


    //This function displays keyed transaction error response.
    function displayKeyedTransactionError($json_string){
        //optional : Display the output
        echo "<br><br> Keyed Sale Response : ";
        //since php interprets boolean value as 1 for true and 0 for false when accessed.
        echo "<br>success : ";
        echo $json_string['success'] ? 'true' : 'false';
        echo "<br>response_code : ".$json_string['response_code'] ;
        echo "<br>status_message : ".$json_string['status_message'] ;

        if(isset ($json_string['errors']))
        {
          //to check the actual API errors and get the individual error keys
          echo "<br>API Errors : " ;


          foreach($json_string['errors'] as $error =>$no_of_errors )
          {
              //Do you code here as an action based on the particular error number
              //you can access the error key with $error in the loop as shown below.
              echo "<br>". $error;
              // to access the error message in array assosicated with each key.
              foreach($no_of_errors as $item)
              {
                 //Optional - error message with each individual error key.
                  echo "  " . $item ;
              }
          }
      }

    }


  ?>
    </body>
</html>
