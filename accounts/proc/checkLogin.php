<?php
require("/var/www/StudyM8/StudyM8_Globals.php");
require("/var/www/.html/mysqli.php");
/*
* Check if user is logged in or not
* return accordingly
* TODO: Obviously make better
  hash sm8ID, last login, ip for verif
  make sure session gAPI_Token is Set
  make sure session sm8GFolder is set
  make sure session database is set (FAT)
*/
if(!isset($_COOKIE["SM8SUB"]))
  exit("No Login");

/**********************************
	Validate SM8 Token
********************************/
if(!$Mysqli = sqlConnect(1))
  exit("Database Error! Unable verify user access");

$subject = $Mysqli->real_escape_string($_COOKIE["SM8SUB"]);

$query = $Mysqli->query("SELECT `sm8ID`,`name`,`email`,`gAPI_accessToken`,`sm8GFolder`,`gAPI_refreshToken`,`lastLogin` FROM `M8_Users` WHERE `subject`='$subject'");
if($Mysqli->num_rows != 1){
  exit("Error contacting logon server.");
}

$rows = $query->fetch_assoc();
$SM8_Token = md5($rows["sm8ID"] . $rows["lastLogin"] . REMOTE_IP);

if(!$_COOKIE["SM8TK"] === $SM8_Token){
  setcookie("SM8SUB",0,(time()-3600), "/", DOMAIN, true, true);
  setcookie("SM8TK",0,(time()-3600), "/", DOMAIN, true, true);
  session_destroy();
  exit("No Login");
}

/***********************************
	Reassign session data variables
**********************************/
session_start();
if(count($_SESSION) != 6)
  reassignSession();

function reassignSession(){
  GLOBAL $rows, $subject;
  $_SESSION["SM8ID"] = $rows["sm8ID"];
  $_SESSION["SM8NAME"] = $rows["name"];
  $_SESSION["SM8Email"] = $rows["email"];
  $_SESSION["gAPI_Token"] = $rows["gAPI_accessToken"];
  $_SESSION["gAPI_Refresh_Token"] = $rows["gAPI_refreshToken"];
  $_SESSION["sm8GFolder"] = $rows["sm8GFolder"];
  $_SESSION["sm8FATDB"] = $subject . "_SM8_FAT";
}
?>
