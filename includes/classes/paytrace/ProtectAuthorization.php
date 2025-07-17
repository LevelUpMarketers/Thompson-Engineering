<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->

<html>
    <head>
        <meta charset="UTF-8">
        <script src='https://protect.paytrace.com/js/protect.min.js'></script>
        <title>Protect Authorization</title>
    </head>
    <style>
    div {
      height: 200px;
      width: 70%;
    }
    </style>
    <body>
      <?php

     /* This code will shows how to access and make a request of OAuth token  */

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
     //call a function in Utilities.php to generate Protect Auth toaken
     $clientKeyResult= ProtectAuthTokenGenerator($oauth_token);
     $json = jsonDecode($clientKeyResult['temp_json_response']);
     //displayProtectAuth($json);
     $clientkey = $json['clientKey'];



     //function to display the individual keys of successful OAuth Json response
     function displayOAuth($json_string){

         //Display the output
         echo "<br><br> OAuth Response : ";
         echo "<br>access_token : ".$json_string['access_token'] ;
         echo "<br>token_type : ".$json_string['token_type'] ;
         echo "<br>expires_in : ".$json_string['expires_in'] ;
     }

     //function to display the individual keys of successful OAuth Json response
     function displayProtectAuth($json_string){

         //Display the output
         echo "<br><br> OAuth Response : ";
         echo "<br><br>clientkey : ".$json_string['clientKey'] ;

     }

      ?>
        <form id="ProtectForm"  action="ProtectAuthorizationJSON.php" method="post"  >
            <div id='pt_hpf_form'>

              <script>

                PTPayment.setup({

                  authorization:
                  {
                      clientKey:'<?php echo $clientkey;?>'
                  }
                }).then(function(instance){
                  document.getElementById("ProtectForm").addEventListener("submit",function(e){
                    e.preventDefault();
                    e.stopPropagation();


      PTPayment.process()
      .then( (r) => {
submitPayment(r);
}, (err) => {
handleError(err);
});

});
});

function handleError(err){
  if (err.reason.length >= 1) {
      if (err.reason[0]['responseCode'] == '35') {

          PTPayment.style({'cc': {'border_color': 'red'}})
      }
    }
  //document.write(JSON.stringify(err));
}

function submitPayment(r){

  var hpf_token = document.getElementById("HPF_Token");
  var enc_key = document.getElementById("enc_key");
  var oAuth = document.getElementById("oAuth");
  hpf_token.value = r.message.hpf_token;
  enc_key.value = r.message.enc_key;
  //document.write ("hpf token = " + hpf_token.value + " enc_key = " + enc_key.value + "oAuth = " + oAuth.value);
  document.getElementById("ProtectForm").submit();

}
              </script>
            </div>
            <input type="txt" id=HPF_Token name= HPF_Token hidden>
            <input type="txt" id=enc_key name = enc_key hidden>
            <input type="txt" id=oAuth name = oAuth hidden>
            <p>Amount:<input type="txt" id=amount name = amount ></p>
            <input type="submit" value="Submit" id="SubmitButton"/>

        </form>

        <br>
            <a href="Default.html">Back to Home </a>
        <br>

    </body>
</html>
