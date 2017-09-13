<?php
  //Endpoint for retreiving incoming oAuth related requests
  //Gets data from Google
  //Determines course of action bassed off of $_SESSION["oAuth"] var
  //Actions match corrisponding cases in switch statements (accountSettings.php)
  session_start();
  if(!ISSET($_SESSION["oAuth_Action"]))
    exit("[SM8]An API sorting error has occured.");

  $code = @$_GET["code"];
  $error = @$_GET["error"];
  if(!(strlen($code) > 1))
    exit("[SM8]An API error has occured - " . $error)
//TODO: Asses weather this is needed, or can be removed since all cases might just set the get varible to the session variable value
  switch($_SESSION["oAuth_Action"]){
    case "GDrive_InitSetup":
      $_GET["cfg"] = "GDrive_InitSetup";
      require("./accountSettings.php");
    break; default:
      exit("[SM8]An API sorting error has occured.");
    break;
  }
?>
