<?php
/*************************************
* Handle first time setup tasks / events
* Handle account setting changes
*************************************/
class AccountSetup{
  private $_Google_Client;  //General / Account API Calls
  private $_Google_Drive_Client;  //Drive API Calls
  private $_Mysqli; //Database link

  private $_subjectID;
  private $_gFolderID;

  private $_errorMessage;
    public function getErrorMessage(){return $this->_errorMessage;}

  public function __construct(){

  }

  private function setErrorMessage($msg){
    $this->_errorMessage = $msg;
  }
  ///////////////////////////////////////////
  //  Google Accounts API Service Client
  //
  private function initGoogleClient($token){
    require("./../../exAPIS/GAPI/vendor/autoload.php");
    $this->_Google_Client = new Google_Client();
    $this->_Google_Client->setAuthConfig("./../../exAPIS/GAPI/client_secret_714276037632-o78r4g32of31cbpaa59jd279vg5sbrqm.apps.googleusercontent.com.json");
    $this->_Google_Client->setIncludeGrantedScopes(true);
    $this->_Google_Client->setAccessType("offline")
    $this->_Google_Client->addScope(Google_Service_Drive::DRIVE_FILE);//Probably right

    if($token)//oAuthG2
      $this->_Google_Client->setAccessToken($token);
  }

  /////////////////////////////////////////
  // Drive API Service Client
  //
  private function initDriveClient(){
    $this->_Google_Drive_Client = new Google_Service_Drive($this->_Google_Client);
  }

  ///////////////////////////////////////////
  //  SM8 Database link
  //
  private function initMysql($type){
    require("/var/www/.html/mysqli.php");
    if($this->_Mysqli = sqlConnect($type))
      return true;

    $this->setErrorMessage("[SM8DB]Error Contacting Database");
    return false;
  }

  /////////////////////////////////////////
  //Sets Google Drive Permission Scope
  //Sends users to Google to allow access
  public function oAuthG1(){
    $this->initGoogleClient();
    $Google_Client->setRedirectUri("https://studym8.org/accounts/settings/accountSettings.php");
    $authUrl = $Google_Client->createAuthUrl();
    header("Location: " . $authUrl);
    exit();
  }


  ////////////////////////////////////////
  // 2nd part of oAuth authentication
  //  Takes received auth code and exchanges it for access token
  public function oAuthG2(){
    $this->initGoogleClient();
    if(!$this->initMysql(1);//SM8 User data db link)
      return false;

    $this->_Google_Client->authenticate($msg);
    $accessToken = $this->_Google_Client->getAccessToken();

    $_SESSION["gAPI_Token"] = $accessToken;

    $this->_subjectID = $this->_Mysqli->real_escape_string($_COOKIE["SM8SUB"]);
    $query = $this->_Mysqli->query("UPDATE `M8_Users` SET `gAPI_accessToken`=$accessToken WHERE `subject`=$this->_subjectID");

    if(!$this->setupGDrive())
      return false;

    if(!$this->setupSM8Db())
      return false;

    return true;
  }

  /////////////////////////////////////////
  //First time drive setup
  //
  private function setupGDrive(){
    $fData = new Google_Service_Drive_DriveFile(array("title" => "StudyM8 Storage","mimeType"="application/vnd.google-apps.folder"));
    $file = $this->_Google_Drive_Client->files->insert($fData, array("fields"=>"id"));

    $query = $this->_Mysqli->query("UPDATE `M8_Users` SET `sm8GFolder`=$file->id WHERE `subject`=$this->_subjectID");
    $this->_gFolderID = $file->id;

    //If M8 DB not responsive, delete folder and abort
    if(!$this->_Mysqli->affected_rows){
      $this->_Google_Drive_Client->files->delete($file->id);
      $this->setErrorMessage("SM8 Database Error");
      return false;
    }

    return true;
  }

  /////////////////////////////////////////
  // Create users data table if doesn't already exist
  //
  private function setupSM8Db(){
    $query = $this->_Mysqli->query("SHOW TABLES LIKE " . $Subject . "_SM8_FAT");
    $rows = $query->fetch_assoc()
    if(count($rows) == 1)
      return true;

    $tableName = $this->_subjectID . "_SM8_FAT";

    //Create table
    $this->_Mysqli->query("CREATE TABLE `StudyM8_UserData`.`$tableName` ( `rid` BIGINT NOT NULL AUTO_INCREMENT , `fileID` VARCHAR(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'GFile ID' , `rName` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL , `rSection` INT NOT NULL COMMENT 'Translated from index table' , `rUnit` INT NOT NULL COMMENT 'Translated from index table' , `rTags` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL , `rDescription` VARCHAR(2048) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL , PRIMARY KEY (`rid`), INDEX (`rName`), INDEX (`rSection`, `rUnit`), UNIQUE (`fileID`), FULLTEXT (`rDescription`), FULLTEXT (`rTags`)) ENGINE = InnoDB;");

    return true;
  }

}
?>
