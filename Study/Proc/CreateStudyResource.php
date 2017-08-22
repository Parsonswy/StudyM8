<?php
/*
* Handles new resoruces being uploaded by client to system
* Actual file is directly uploaded to Google
*/
  class CreateStudyResource{
      private $_Mysqli;
      private $_errorMessage;
        public function getErrorMessage(){return $this->_errorMessage;}

      private $_file;
      private $_fileName;
      private $_fileType;
      private $_fileLocation;
      private $_fileSize;

      //Resource Parameters (User Suppllied)
      private $_rpName
      private $_rpSecion;
      private $_rpUnit;
      private $_rpTags;
      private $_rpDescription;

      private $_rpID; //StudyM8 Database ID
      private $_rp_GID;//Google ID
      private $_Google_Client;//Google Client API
      private $_GDrive_Client;//API GDrive Client from library
      //Requires reference to $_FILE array with data, parameter set in _POST ($paramInstace_FDeck_EntryName)
      //Driver will skip file if error in upload
      //
      public function __construct($file, $paramInstance){
        $this->_file = $file;
        $this->_fileName = $file["name"];
        $this->_fileType = $file["type"];
        $this->_fileLocation = $file["tmp_name"];
        $this->_fileSize = $file["size"];

        if(!$this->initDataSQL()){
          //Error message set in initDataSQL();
          return false;
        }
        $this->configRParameters($paramInstance);//Form data sync

        if(!$this->insertToM8Database())
          return false;//erorr message set in insertToM8Database()

        $this->loadGoogleClient();
        if(!$this->sendToGoogle())
          return false;
      }

      //
      //Mysqli resource to access StudyM8 file data
      //
      private function initDataSQL(){
        require("/var/www/.html/mysqli.php");
        if($this->_Mysqli = sqlConnect(2))
          return true;

        $this->setErrorMessage("[SM8DB] Error contacting StudyM8 database.");
        return false;
      }

      //////////////////////
      //Clearn RPs in $p[aram]i[nstance]
      //Set conrisponding properties
      private function configRParameters($pi){
        $this->_rpName = $this->_Mysqli->real_escape_string($_POST[$pi . "_FDeck_EntryName"]);
        $this->_rpSection = $this->_Mysqli->real_escape_string($_POST[$pi . "_FDeck_Section"]);
        $this->_rpUnit = $this->_Mysqli->real_escape_string($_POST[$pi . "_FDeck_Unit"]);
        $this->_rpTags = $this->_Mysqli->real_escape_string($_POST[$pi . "_FDeck_Tags"]);
        $this->_rpNotes = $this->_Mysqli->real_escape_string($_POST[$pi . "_FDeck_Description"]);
      }

      /////////////////////////////////////////
      //Insert resource parameters to StudyM8 Database
      //
      private function insertToM8Database(){
        $query = $this->_Mysqli->query("INSERT INTO `$_SESSION['database']` VALUES('',
                                                                      'proc',
                                                                      $this->_rpName,
                                                                      '0'
                                                                      $this->_rpSection,
                                                                      $this->_rpUnit,
                                                                      $this->_rpTags,
                                                                      $this->_rpNotes,
                                                                      '0')");
        if(!$this->_Mysqli->affected_rows){
          $this->setErrorMessage("Database error! - " . $this->_Mysqli->error);
          return false;
        }

        $this->_rpID = $this->_Mysqli->insert_id;

        return true;
      }

      private function reportToSM8DB($fileID){
        $query = $this->_Mysqli->query("UPDATE `$_SESSION['database']` SET `fileID`=$fileID WHERE `rid`=$this->_rpID");
        if(!$this->_Mysqli->affected_rows == 1){
          $this->setErrorMessage("[SM8DB]Error Contacting Database");
          return fasle;
        }
        return true;
      }

      private function loadGoogleClient(){
        require("./../../exAPIS/GAPI/vendor/autoload.php");
        $this->_Google_Client = new Google_Client();
        $this->_Google_Client->setAuthConfig("./../../exAPIS/GAPI/client_secret_714276037632-o78r4g32of31cbpaa59jd279vg5sbrqm.apps.googleusercontent.com.json");
        $this->_Google_Client->setIncludeGrantedScopes(true);
        $this->_Google_Client->setAccessType("offline")
        $this->_Google_Client->addScope(Google_Service_Drive::DRIVE_FILE);//Probably right
        $this->_Google_Client->setAccessToken($_SESSION["gAPI_Token"]);

        $this->_GDrive_Client = new Google_Service_Drive($this->_Google_Client);//Create Drive service client with auth'd _Google_Client
      }

      //////////////////////////////////////////
      //Upload file to Google Drive
      //
      private function sendToGoogle(){
        $file = new Google_Service_Drive_DriveFile();
        $file->setTitle($this->_rpName);
        $file->setDescription($this->_rpDescription);
        $file->setMimeType($this->_fileType);

        $parent = new Google_Service_Drive_ParentReference();
        $parent->setId($_SESSION["sm8GFolder"]);

        $file->setParents(array($parent));

        try{
          $fData = file_get_contents($this->_fileLocation);
          $gFile = $this->_Google_Drive_Client->files->insert($file,array("data"=>$fData,"$mimeType"=>$this->_fileType));
        }catch(Exception $e){
          $this->setErrorMessage($e->getMessage());
          return false;
        }

        //Send file ID to SM8Db
        if($this->reportToSM8DB($gFile->getId()))
          return true;

        //Delete because of SM8 DB error - prevent ghost files with no resource link to Studym8
        $this->_Google_Drive_Client->files->delete($file->id);
        //ErrorMessage Set in reportToSM8DB();
        return false;
      }

      private function setErrorMessage($err){
        $this->_errorMessage = $err;
      }
  }
 ?>
