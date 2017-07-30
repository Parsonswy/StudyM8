<?php
/*
  Receive AuthResponseToken from client, that it rececieved from Google
  Token is async encrypted with data
*/
//Get Google API Client
require("./../../exAPIS/GAPI/vendor/autoload.php");
$client = new Google_Client();
$client->setAuthConfig("./../../exAPIS/GAPI/client_secret_714276037632-o78r4g32of31cbpaa59jd279vg5sbrqm.apps.googleusercontent.com.json");

$user_idToken = $_POST["OID"];//encrypted auth string from user
echo $user_idToken;
echo var_dump($_POST);
$client->setAccessToken($user_idToken);
$payload = $client->verifyIdToken();
if ($payload) {
  //$userid = $payload['sub']
  exit(json_encode($payload));
} else {
  exit(json_encode(array("RESP"=>"Invalid ID Token")));
}
?>
