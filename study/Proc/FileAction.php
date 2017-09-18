<?php
  class FileAction{
    protected $_dataSQLi
    protected $_webSQLi;

    protected $_Google_Client;
    protected $_Google_Drive_Service;

    protected $_fileGID;//Google ID
    protected $_fileSID;//SM8 ID
    protected $_fileParent;

    protected $_fileSection;//#
    protected $_fileSection_string;//Translated value
    protected $_fileUnit;//#
    protected $_fileUnit_string;//Translated value
    protected $_fileTags;

    protected $_fileName;
    protected $_fileDescription;
    protected $_filePreview; //TODO: Make a thing

    public function __construct(){

    }

    protected function initWebSQL(){

    }

    protected function initDataSQL(){

    }

    private function initGoogleClient(){
      require("/var/www/studym8/latest/vendor/autoload.php");
      $this->_Google_Client = new Google_Client();
      $this->_Google_Client->setAuthConfig("/var/www/.html/client_secret_apps.googleusercontent.com.json");
      $this->_Google_Client->setIncludeGrantedScopes(true);
      $this->_Google_Client->setAccessType("offline");
      $this->_Google_Client->addScope(Google_Service_Drive::DRIVE_FILE);//Probably right
      $this->_Google_Client->setAccessToken($_SESSION["gAPI_accessToken"]);
    }

    private function initDriveService(){
      $this->_Google_Drive_Service = new Google_Service_Drive($this->_Google_Client);
    }
  }
?>
