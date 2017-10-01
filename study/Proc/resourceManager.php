<?php
require("/var/www/StudyM8/StudyM8_Globals.php");
require("/var/www/StudyM8/latest/accounts/proc/checkLogin.php");
require("./CreateStudyResource.php");
/*
* Communication endpoint for document (resource) management by users
* Driver class for resource management classes
*/

//reportUploadError()
$errData = array();

echo "<pre>";
var_dump($_POST);
echo "</pre>";

switch($_POST["action"]){
  case "create_upload":
    //Loop through all uploaded files and pass reference to data array
    for($i = 0; $i < count($_FILES); $i++){
      if($_FILES["SM8_UploadItems_Form_FilePool_Upload_" . $i]["error"] == 0){
        $csr = new CreateStudyResource($_FILES["SM8_UploadItems_Form_FilePool_Upload_" . $i], $i);
				$rp = $csr->processResource();
        if(!$rp)//if server error'd
          reportUploadError($csr->getErrorMessage());
        continue;
      }
      reportUploadError($_FILES["SM8_UploadItems_Form_FilePool_Upload_" . $i]["error"]);
    }
  break;case "delete":

  break;case "edit":

  break;default:
    exit("NULL Request");
  break;
}

if(count($errData) > 0){
  echo "Errors occured during file upload. Some files may not have processed <br/><pre>";
	echo $rp;
  var_dump($errData);
  echo "</pre>";
}
/*******************************************************************************
Helper functions
*******************************************************************************/
function reportUploadError($err){
	GLOBAL $errData;
  array_push($errData, $err);
}
 ?>
