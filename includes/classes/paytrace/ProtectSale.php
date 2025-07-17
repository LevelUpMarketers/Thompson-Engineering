<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->

<html>
    <head>
      <script src='https://protect.paytrace.com/js/protect.min.js'></script>
        <meta charset="UTF-8">
        <title>Protect Sale</title>
    </head>
    <style>
    div {
      width: 55%;
      height: 280px;
    }

    input {
  border: 2px solid #EF9F6D;
  color:#5D99CA;
  width: 10%;
  padding: 6px 10px;
  box-sizing: border-box;

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
        <form id="ProtectForm"  action="ProtectSaleJSON.php" method="post"  >
            <div id='pt_hpf_form'>

              <script>

                PTPayment.setup({
                  styles:
                  {
                   'code': {
                    'font_color':'#5D99CA',
                    'border_color':'#EF9F6D',
                    'label_color':'#EF9F6D',
                    'label_size':'20px',
                    'background_color':'white',
                    'border_style':'dotted',
                    'font_size':'15pt',
                    'height':'30px',
                    'width':'100px'
                   },
                   'cc': {
                    'font_color':'#5D99CA',
                    'border_color':'#EF9F6D',
                    'label_color':'#EF9F6D',
                    'label_size':'20px',
                    'background_color':'white',
                    'border_style':'solid',
                    'font_size':'15pt',
                    'height':'30px',
                    'width':'300px'
                   },
                   'exp': {
                    'font_color':'#5D99CA',
                    'border_color':'#EF9F6D',
                    'label_color':'#EF9F6D',
                    'label_size':'20px',
                    'background_color':'white',
                    'border_style':'dashed',
                    'font_size':'15pt',
                    'height':'30px',
                    'width':'85px',
                    'type':'dropdown'
                   }
                   },
                  authorization:
                  {
                      'clientKey': "<?php echo $clientkey;?>"
                  }
                }).then(function(instance){
                  PTPayment.getControl("securityCode").label.text("CSC");
                  PTPayment.getControl("creditCard").label.text("CC#");
                  PTPayment.getControl("expiration").label.text("Exp Date");
                  //PTPayment.style({'cc': {'label_color': 'red'}});
                  //PTPayment.style({'code': {'label_color': 'red'}});
                  //PTPayment.style({'exp': {'label_color': 'red'}});
                  //PTPayment.style({'exp':{'type':'dropdown'}});

                  //PTPayment.theme('horizontal');
                  // this can be any event we chose. We will use the submit event and stop any default event handling and prevent event handling bubbling.
                  document.getElementById("ProtectForm").addEventListener("submit",function(e){
                   e.preventDefault();
                   e.stopPropagation();

                  // To trigger the validation of sensitive data payment fields within the iframe before calling the tokenization process:
                  PTPayment.validate(function(validationErrors) {
                   if (validationErrors.length >= 1) {
                    if (validationErrors[0]['responseCode'] == '35') {
                     // Handle validation Errors here
                     // This is an example of using dynamic styling to show the Credit card number entered is invalid
                     instance.style({'cc': {'border_color': 'red'}});
                    }
                   } else {
                     // no error so tokenize
                     instance.process()
                     .then( (r) => {
                        submitPayment(r);
                        }, (err) => {
                        handleError(err);
                        });
                   }
              });

              });
            });


function handleError(err){


  document.write(JSON.stringify(err));
}

function submitPayment(r){

  var hpf_token = document.getElementById("HPF_Token");
  var enc_key = document.getElementById("enc_key");
  hpf_token.value = r.message.hpf_token;
  enc_key.value = r.message.enc_key;
  document.getElementById("ProtectForm").submit();

}
              </script>
            </div>
            <input type="txt" id=HPF_Token name= HPF_Token hidden>
            <input type="txt" id=enc_key name = enc_key hidden>
            <input type="txt" id=amount name = amount value="Amount"><br><br>
            <input type="submit" value="Submit" id="SubmitButton"/>

        </form>

        <br>
            <a href="Default.html">Back to Home </a>
        <br>

    </body>
</html>
