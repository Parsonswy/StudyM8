function onSignIn(googleUser) {
  var profile = googleUser.getBasicProfile();
  console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
  console.log('Name: ' + profile.getName());
  console.log('Image URL: ' + profile.getImageUrl());
  console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
  reportSignonToServer(googleUser.getAuthResponse().id_token);
  //console.log(googleUser.getAuthResponse());
}

function signOut() {
    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut().then(function () {
      console.log('User signed out.');
    });
}

//OAuth Token sent to server to report login
function reportSignonToServer(token){
  var host = extractHostname(window.location);
  var xhttp = new XMLHttpRequest();
  xhttp.open("POST","https://" . host . "/accounts/proc/doGoogleLogin.php",true);

  xhttp.onreadystatechange = function() {
   if (this.readyState == 4 && this.status == 200) {
     console.log(JSON.parse(this.responseText));
     window.location = "https://" . host . "/study/StudyM8.php";
   }
 };

  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send("OID=" + token);
}

function extractHostname(url) {
    var hostname;

    //find & remove "protocol://"
    if (url.indexOf("://") > -1)
        hostname = url.split('/')[2];
    else
        hostname = url.split('/')[0];

    //find & remove "?"
    hostname = hostname.split('?')[0];
    return hostname;
}
