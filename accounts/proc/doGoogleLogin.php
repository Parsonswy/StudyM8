<?php
require("/var/www/StudyM8/StudyM8_Globals.php");
require("./LoginHandler.php");
//LoginHandler Class Driver
//Receives AJAX Requests from user and passes to LoginHandler for Google Login
/****************************************************/
if(!$token = $_POST["OID"]) //Don't do anything without token
  exit("Empty Request.");

  $LoginHandler = new LoginHandler();
  $LoginHandler->setUserGAPIToken($token);

  if(!$LoginHandler->procIDToken()){
    exit($LoginHandler->getErrorMessage());
  }

  $accountStatus = $LoginHandler->accountExists();
  if(!$accountStatus)
    exit($LoginHandler->getErrorMessage());

  //Create
  if($accountStatus == 1){
    if(!$LoginHandler->createAccount())
      exit($LoginHandler->getErrorMessage());

    //Redirect to account settings page for first time setup
    header("Location: https://" . DOMAIN . "/accounts/accountSettings.php?config=init");
    $accountStatus = 2; //Allow to continue and login below
    //echo "[DEBUG]Account Created";
  }

  //Login
  if($accountStatus == 2){
    if(!$LoginHandler->loginToAccount())
      exit($LoginHandler->getErrorMessage());
  }
  //echo "[DEBUG]Login Sucesful!";
 ?>
