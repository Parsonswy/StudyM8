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
  var xhttp = new XMLHttpRequest();
  xhttp.open("POST","https://studym8.org/accounts/proc/doGoogleLogin.php",true);

  xhttp.onreadystatechange = function() {
   if (this.readyState == 4 && this.status == 200) {
     console.log(JSON.parse(this.responseText));
   }
 };

  xhttp.send("OID=" + token);
}
