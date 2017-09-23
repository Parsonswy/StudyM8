<?php
require("./FileAction.php");
/*
* Handles new resoruces being uploaded by client to system
* Actual file is directly uploaded to Google
* FileAction constructor creates SQL resources and Drive API resources
*/
  class CreateStudyResource extends FileAction{
      private $_file;
      private $_fileName;
      private $_fileType;
      private $_fileLocation;
      private $_fileSize;

      private $_Google_Drive_Service;//API GDrive Client from library
      //Requires reference to $_FILE array with data, parameter set in _POST ($paramInstace_FDeck_EntryName)
      //Driver will skip file if error in upload
      //
      public function __construct($file, $paramInstance){
        //SQL / GAPI resources
        parent::__construct();
        $this->_file = $file;
        $this->_fileName = $file["name"];
        $this->_fileType = $file["type"];
        $this->_fileLocation = $file["tmp_name"];
        $this->_fileSize = $file["size"];

        $this->configRParameters($paramInstance);//Form data sync

        if(!$this->insertToM8Database())
          return false;//erorr message set in insertToM8Database()

        if(!$this->sendToGoogle())
          return false;
      }

      //////////////////////
      //Clearn RPs in $p[aram]i[nstance]
      //Set conrisponding properties
      private function configRParameters($pi){
        $this->_rpName = $this->_dataSQLi->real_escape_string($_POST[$pi . "_FDeck_EntryName"]);
        $this->_rpSection = $this->_dataSQLi->real_escape_string($_POST[$pi . "_FDeck_Section"]);
        $this->_rpUnit = $this->_dataSQLi->real_escape_string($_POST[$pi . "_FDeck_Unit"]);
        $this->_rpTags = $this->_dataSQLi->real_escape_string($_POST[$pi . "_FDeck_Tags"]);
        $this->_rpNotes = $this->_dataSQLi->real_escape_string($_POST[$pi . "_FDeck_Description"]);
      }

      /////////////////////////////////////////
      //Insert resource parameters to StudyM8 Database
      //
      private function insertToM8Database(){
        $query = $this->_dataSQLi->query("INSERT INTO `$_SESSION['database']` VALUES('',
                                                                      'proc',
                                                                      $this->_rpName,
                                                                      '0'
                                                                      $this->_rpSection,
                                                                      $this->_rpUnit,
                                                                      $this->_rpTags,
                                                                      $this->_rpNotes,
                                                                      '0')");
        if(!$this->_dataSQLi->affected_rows){
          $this->setErrorMessage("Database error! - " . $this->_dataSQLi->error);
          return false;
        }

        $this->_rpID = $this->_dataSQLi->insert_id;

        return true;
      }

      private function reportToSM8DB($fileID){
        $query = $this->_dataSQLi->query("UPDATE `$_SESSION['database']` SET `fileID`=$fileID WHERE `rid`=$this->_rpID");
        if(!$this->_dataSQLi->affected_rows == 1){
          $this->setErrorMessage("[SM8DB]Error Contacting Database");
          return fasle;
        }
        return true;
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
          $gFile = $this->_Google_Drive_Service->files->insert($file,array("data"=>$fData,"$mimeType"=>$this->_fileType));
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
  }
 ?>
