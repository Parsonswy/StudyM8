<?php
/*
  Receive AuthResponseToken from client, that it rececieved from Google
  Token is async encrypted with data
*/

class LoginHandler{
    private $_Google_Client;
    private $_Mysqli;

    private $_userGAPIToken;
      public function setUserGAPIToken($token){$this->_userGAPIToken = $token;}
    private $_rawGUserData;//Pre Validation with Google OATH
    private $_GUserData;//Post Validation with Google OATH
    private $_subject;//Google Subject ID (post token Validation / real_escaped)

    private $_errorMessage;
      public function getErrorMessage(){return $this->_errorMessage;}

    public function __construct(){
      //Get Google API Client, Create Instance, Load Creds
      require("/var/www/studym8/latest/vendor/autoload.php");
      $this->_Google_Client = new Google_Client();
      $this->_Google_Client->setAuthConfig("/var/www/.html/client_secret_apps.googleusercontent.com.json");
    }

    private function setErrorMessage($msg){
      $this->_errorMessage = $msg;
    }

/************************************************************
*
* Google Auth Server Interaction
*
*************************************************************/
    //
    //"Main" authentication function. Decodes token, calls support functions to validate with Google
    //
    public function procIDToken(){
      $this->_rawGUserData = $this->_Google_Client->verifyIDToken($this->_userGAPIToken);
      if(!$this->_rawGUserData){
        $this->setErrorMessage("Error decoding token data.");
        return false;
      }//Die if error decrypint token

      if(!$this->validateGoogleToken()){
        $this->_errorMessage = "Invalid token.";
        return false;
      }

      return true;
    }

    //
    //Send token back to Google for verification
    //Verifies API token and token issuer
    private function validateGoogleToken(){
      //Requests API - Used for HTTP/S requests
      require("/var/www/studym8/latest/vendor/rmccue/requests/library/Requests.php");
      Requests::register_autoloader();
      $url = "https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=" . $this->_userGAPIToken;
      $request = Requests::get($url, array('Accept' => 'application/json'));

      if($request->status_code != 200){
        $this->setErrorMessage("Error validating credentials");
        return false;
      }//Die if Google Oath down or finds token error

      $gData = json_decode($request->body);

      //Verify token was for StudyM8 (audience)
      if(!$gData->aud === "714276037632-o78r4g32of31cbpaa59jd279vg5sbrqm.apps.googleusercontent.com"){
        $this->setErrorMessage("Error validating credentials");
        return false;
      }

      //Verify token signed by Google account servers
      if(!($gData->iss === "https://accounts.google.com" || $gData->iss === "accounts.google.com" )){
        $this->setErrorMessage("Error validating credentials");
        return false;
      }
      $this->_GUserData = $gData;
      return true;
    }

/************************************************************
*
* StudyM8 Database interaction
*
*************************************************************/
    //
    //Check if account exists under GSubject ID
    //Used to determine account creation or login
    //1: New User, 2: Returning user
    public function accountExists(){
      if(!$this->initDataSQL())
        return false;

      $this->_subject = $this->_Mysqli->real_escape_string($this->_GUserData->sub);

      $query = $this->_Mysqli->query("SELECT `sm8ID` FROM `M8_Users` WHERE `subject`=$this->_subject");
      if(!$query->num_rows)
        return 1;

      return 2;
    }

    public function createAccount(){
      $name = $this->_Mysqli->real_escape_string($this->_GUserData->name);
      $email = $this->_Mysqli->real_escape_string($this->_GUserData->email);
      $query = $this->_Mysqli->query("INSERT INTO `M8_Users` VALUES(NULL,'$this->_subject','$name','$email','','','','" . time() . "')");
      if(!$this->_Mysqli->affected_rows){
        $this->_errorMessage = "A database error occured while attempting to create your account.";
        return false;
      }

      return true;
    }

    //
    //Get data from servers
    //Set sessions / cookies
    public function loginToAccount(){
      $query = $this->_Mysqli->query("SELECT `sm8ID`,`name`,`email`,`gAPI_accessToken`,`sm8GFolder`,`lastLogin` FROM `M8_Users` WHERE `subject`=$this->_subject");
      if($query->num_rows != 1){
        $this->_errorMessage = "Error contacting logon server.";
        return false;
      }

      $rows = $query->fetch_assoc();
      session_start();
      $_SESSION["SM8ID"] = $rows["sm8ID"];
      $_SESSION["SM8NAME"] = $rows["name"];
      $_SESSION["SM8Email"] = $rows["email"];
      $_SESSION["gAPI_Token"] = $rows["gAPI_accessToken"];
      $_SESSION["sm8GFolder"] = $rows["sm8GFolder"];
      $_SESSION["sm8FATDB"] = $rows["sm8ID"] . "_SM8_FAT";

      $SM8_Token = md5($rows["sm8ID"] . $rows["lastLogin"] . $_SERVER["HTTP_CF_CONNECTING_IP"]);

      //30 day expire, https, http header access only
      setcookie("SM8SUB",$this->_GUserData->sub,(time() + 60 * 60 * 24 * 30), "/", "studym8.org", true, true);
      setcookie("SM8TK",$SM8_Token,(time() + 60 * 60 * 24 * 30), "/", "studym8.org", true, true);

      $this->_Mysqli->query("UPDATE `M8_Users` SET `sessionID`='" . session_ID() . "' WHERE `subject`='$this->_subject'");
      echo $this->_Mysqli->error . "</br>";
	  if(!$this->_Mysqli->affected_rows){
        $this->sessionDestroy();
        $this->_errorMessage = "Error contacting session server for initializiation";
        return false;
      }

      return true;
    }

    //wipe session
    private function sessionDestroy(){
      session_destroy();
      setcookie("SM8SUB",null,time()-3600,"","studym8.org",true,true);
      setcookie("SM8TK",null,time()-3600,"","studym8.org",true,true);
      $this->_Mysqli->query("UPDATE `M8_Users` SET `sessionID`='' WHERE `subject`=$this->_subject");
    }

    //
    //Mysqli resource to access StudyM8 user data
    //
    private function initDataSQL(){
      require("/var/www/.html/mysqli.php");
      if($this->_Mysqli = sqlConnect(1))
        return true;

      $this->_errorMessage = "Error contacting StudyM8 database.";
      return false;
    }

/************************************************************
*
* Logout
*
*************************************************************/
    //"Main" function for user logout
    //Revoke gAPI Access Token
    //Remove session / cookie data
    public function logoutOfAccount(){

    }
/************************************************************
*
* Outside noraml class stack flow
*
*************************************************************/

}
?>
