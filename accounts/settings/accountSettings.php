<?php
/****************************************
* Dynamic page where user can modify / manage their account
* Passes all actions through to class: AccountSetup
****************************************/
//default: noraml account page
//G1: send access token request / redirect for prompting
//G2[code || error]: From Google, authorization for drive
if($_GET["config"]){$cfg = $_GET["cfg"];}
else{$cfg = "default"}

require("/var/www/html/accounts/proc/checkLogin.php");

require("./AccountSetup.php");
$AccountSetup = new AccountSetup();

switch($cfg){
  case "G1":

      $AccountSetup->oAuthG1();//GAPI, access token request, redirect user

  break; ;default:

    //oAuth Response from Google
    if($_GET["code"])
      oAuthG2(true, $_GET["code"]);
    if($_GET["error"])
      oAuthG2(false, $_GET["error"]);

  break;
}

function oAuthG2($stat, $msg){
  if($stat)
    exit("[GAPI]Error" . $msg);

  if($AccountSetup->oAuthG2())
    exit("Database Created");

  exit("[SM8]Error ". $AccountSetup->getErrorMessage());
}
?>
