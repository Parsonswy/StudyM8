<?php
  class FileAction{
    protected $_dataSQLi;
    protected $_webSQLi;

    protected $_errorMessage;
    public function getErrorMessage(){return $this->_errorMessage;}
    protected function setErrorMessage($err){$this->_errorMessage = $err;}

    protected $_Google_Client;
    protected $_Google_Drive_Service;

    protected $_rpGID;//Google ID
    protected $_rpID;//SM8 ID
    protected $_rpParent;//Parent folder (SM8)

    protected $_rpSection;//#
    protected $_rpSection_string;//Translated value
    protected $_rpUnit;//#
    protected $_rpUnit_string;//Translated value
    protected $_rpTags;

    protected $_rpName;
    protected $_rpDescription;
    protected $_rpPreview; //TODO: Make a thing
    protected $_rpType;
    protected $_rpSize;

    public function __construct(){

    }

    protected function init(){
      if(!$this->initWebSQL()){
        $this->setErrorMessage("[SM8]Databaes error");
        return false;
      }

      if(!$this->initDataSQL()){
        $this->setErrorMessage("[SM8]Databaes error");
        return false;
      }

      $this->initGoogleClient();
      $this->initDriveService();
      return true;
    }
    //SQL init functions loaded in checkLogin.php
    protected function initWebSQL(){
      if($this->_dataSQLi = sqlConnect(1))
        return true;

      $this->setErrorMessage("[SM8DB] Error contacting StudyM8 database.");
      return false;
    }

    protected function initDataSQL(){
      if($this->_dataSQLi = sqlConnect(2))
        return true;

      $this->setErrorMessage("[SM8DB] Error contacting StudyM8 database.");
      return false;
    }

    private function initGoogleClient(){
      require("/var/www/studym8/latest/vendor/autoload.php");
      $this->_Google_Client = new Google_Client();
      $this->_Google_Client->setAuthConfig("/var/www/.html/client_secret_apps.googleusercontent.com.json");
      $this->_Google_Client->setIncludeGrantedScopes(true);
      $this->_Google_Client->setAccessType("offline");
      $this->_Google_Client->addScope(Google_Service_Drive::DRIVE_FILE);//Probably right
      $this->_Google_Client->setAccessToken($_SESSION["gAPI_Token"]);
      $this->_Google_Client->refreshToken($_SESSION["gAPI_Token"]);
    }

    private function initDriveService(){
      $this->_Google_Drive_Service = new Google_Service_Drive($this->_Google_Client);
    }
  }
?>
