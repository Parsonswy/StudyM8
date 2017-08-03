<?php
/*
  Receive AuthResponseToken from client, that it rececieved from Google
  Token is async encrypted with data
*/

class LoginHandler{
    private $_Google_Client;

    private $_userGAPIToken;
      public function setUserGAPIToken($token){$this->_userGAPIToken = $token;}
    private $_rawGUserData;//Pre Validation with Google OATH
    private $_GUserData;//Post Validation with Google OATH

    private $_errorMessage;
      public function getErrorMessage(){return $this->_errorMessage;}

    public function __construct(){
      //Get Google API Client, Create Instance, Load Creds
      require("./../../exAPIS/GAPI/vendor/autoload.php");
      $this->_Google_Client = new Google_Client();
      $this->_Google_Client->setAuthConfig("./../../exAPIS/GAPI/client_secret_714276037632-o78r4g32of31cbpaa59jd279vg5sbrqm.apps.googleusercontent.com.json");
    }

    private function setErrorMessage($msg){
      $this->_errorMessage = $msg;
    }

    //
    //"Main" function. Decodes token, calls support functions to validate with Google
    //
    public function procIDToken(){
      $this->_rawGUserData = $this->_Google_Client->verifyIDToken($this->_userGAPIToken);
      if(!$this->_rawGUserData){
        $this->setErrorMessage("Error decoding token data.");
        return false;
      }//Die if error decrypint token

      if(!$this->validateGoogleToken())
        return false;

      echo "[OK]" . $this->_GUserData->sub;
    }

    //
    //Send token back to Google for verification
    //Verifies API token and token issuer
    private function validateGoogleToken(){
      //Requests API - Used for HTTP/S requests
      require("./../../exAPIS/Requests/library/Requests.php");
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
}
?>
