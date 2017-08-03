<?php
  //LoginHandler Class Driver
  //Receives AJAX Requests from user and passes to LoginHandler for Google Login
  /****************************************************/
  if(!$token = $_POST["OID"]) //Don't do anything without token
    exit("Empty Request.");


    require("./LoginHandler.php");
    $LoginHandler = new LoginHandler();
    $LoginHandler->setUserGAPIToken($token);

    if(!$LoginHandler->procIDToken()){
      exit($LoginHandler->getErrorMessage());
    }
 ?>
