<?php
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
require("/var/www/.html/mysqli.php");
if(!$Mysqli = sqlConnect(1))
  exit("Database Error! Unable verify user access");

$subject = $Mysqli->real_escape_string($_COOKIE["SM8SUB"]);

$query = $Mysqli->query("SELECT `sm8ID`,`name`,`email`,`gAPI_accessToken`,`sm8GFolder`,`lastLogin` FROM `M8_Users` WHERE `subject`='$subject'");
if($query->num_rows != 1){
  exit("Error contacting logon server.");
}

$rows = $query->fetch_assoc();
$SM8_Token = md5($rows["sm8ID"] . $rows["lastLogin"] . $_SERVER["HTTP_CF_CONNECTING_IP"]);

if(!$_COOKIE["SM8TK"] === $SM8_Token){
  setcookie("SM8SUB",0,(time()-3600), "/", "studym8.org", true, true);
  setcookie("SM8TK",0,(time()-3600), "/", "studym8.org", true, true);
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
  GLOBAL $rows;
  $_SESSION["SM8ID"] = $rows["sm8ID"];
  $_SESSION["SM8NAME"] = $rows["name"];
  $_SESSION["SM8Email"] = $rows["email"];
  $_SESSION["gAPI_Token"] = $rows["gAPI_accessToken"];
  $_SESSION["sm8GFolder"] = $rows["sm8GFolder"];
  $_SESSION["sm8FATDB"] = $rows["sm8ID"] . "_SM8_FAT";
}
?>
