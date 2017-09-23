<?php
/*
* Communication endpoint for document (resource) management by users
* Driver class for resource management classes
*/

//Verify user is logged in
require("/var/www/studym8/latest/accounts/proc/checkLogin.php");

//reportUploadError()
$errData = array();
$errCount = 0;

switch($_POST["action"]){
  case "create":
    //Loop through all uploaded files and pass reference to data array
    for($i = 0; $i < count($_FILES); $i++){
      if($_FILE["SM8_UploadItems_Form_FilePool_Upload_" . $i]["error"] == 0){
        $csr = new CreateStudyResource($_FILE["SM8_UploadItems_Form_FilePool_Upload_" . $i], $i);
        if(!$csr)//if server error'd
          reportUploadError($csr->getErrorMessage());
        continue;
      }
      reportUploadError($_FILE["SM8_UploadItems_Form_FilePool_Upload_" . $i]["error"]);
    }
  break;case "delete":

  break;case "edit":

  break;default:
    exit("NULL Request");
  break;
}

if($errCount > 0){
  echo "Errors occured during file upload. Some files may not have processed <br/><pre>";
  var_dump($errData);
  echo "</pre>";
}
/*******************************************************************************
Helper functions
*******************************************************************************/
function reportUploadError($err){
  array_push($errData, $err);.
  $errCount++;
}
 ?>
