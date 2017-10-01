<?php
	require("/var/www/StudyM8/StudyM8_Globals.php");
	require("/var/www/StudyM8/accounts/proc/checkLogin.php");
  //Cases corispond to primary functions in AccoutnSetup.php
	session_start();//TODO:remove when login check is fixed
  if(!ISSET($_GET["cfg"]))
    $cfg = null;
  else
    $cfg = $_GET["cfg"];

require("./AccountSetup.php");
$AccountSetup = new AccountSetup();

switch($cfg){
  case "GDrive_API_Init_CFG"://Create API Access Token Request
    $AccountSetup->GDrive_API_Init_CFG();
  break; case "GDrive_API_Setup"://Post Access Token - Configure Drive
    //$code is defined in oAuthCallback, which calls this script and triggers case
    if(!$AccountSetup->GDrive_API_Setup($code))
      exit("[SM8]" . $AccountSetup->getErrorMessage());
		header("https://studym8.org/study/StudyM8.php");//Redirt after sucess
    exit("OK");
  break;default:
    //TODO: Display default account settings page
  break;
}
?>
