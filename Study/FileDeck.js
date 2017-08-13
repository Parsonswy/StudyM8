var FilePool = document.getElementById("SM8_UploadItems_Form_FilePool");//Upload Button Wrapper
var FileDeck = document.getElementById("SM8_UploadItems_Form_FileDeck");//Selected File List

var Form = document.getElementById("SM8_UploadItems_Form_FileDeck_Form");
Form.addEventListener("submit", function(ev){
  SM8_ProcessFilePool();
  ev.preventDefault();//Prevent form submission
});

var FileDeck_configured = false;
var FileDeck_FilesOnDeck = 0;
/*Get configuration information from server on first load*/
function fileDeck__construct(){
  if(FileDeck_configured)
    return;

  fileDeck__construct_GetSubjectData();
}

function fileDeck__construct_GetSubjectData(){
  var xmlHttp = new XMLHttpRequest();
  xmlHttp.open("./GetStudyData.php", "POST");
  xmlHttp.onreadystatechange = function(){
    if(xmlHttp.status == 200 && xmlHttp.readystate == 4){
      //TODO: Process JSON with Section / Unit information
    }
  }
}

/***********************************************************
* Post initialization functions hadling user interaction with form
************************************************************/
//Hide upload btn, add to file list, create new upload btn
function fileDeck_AddFile(upf){
  var form = fileDeck_ConfigureBoard(upf.value, upf.value);
    console.log("[B1]");
  upf.style.display="none";
    console.log("[B2]");
  FileDeck.innerHTML += form;
    console.log("[B3]");
  FileDeck_FilesOnDeck++;
    console.log("[B4]");
  FilePool.innerHTML += '<input class="SM8_UploadItems_Form_FilePool_Upload" name="FilePool_Item' + FileDeck_FilesOnDeck + '" type="file" accept="audio/*, video/*, image/*, pdf" onchange="fileDeck_AddFile(this);"/>';
    console.log("[B5]");
}

//TODO: File Name overflow pushes collapse arrow off screen
function fileDeck_ConfigureBoard(filename, docname,){
  var form = '\
  <div class="SM8_UploadItems_Form_FileDeck_Board">\
    <div class="SM8_UploadItems_Form_FileDeck_BoardHeader" onclick="SM8_UploadItems_Form_FileDeck_BoardInflate(this);">\
      ' + filename + '\
      <span style="float:right;"> < </span>\
    </div>\
    <div class="SM8_UploadItems_Form_FileDeck_BoardContent">\
      <div class="SM8_UploadItems_Form_FileDeck_BoardContentItem">\
        Entry Name: <input type="text" name="1_FDeck_EntryName" value="' + docname + '" placeholder="Name for file entry in system"/>\
      </div>\
      <div class="SM8_UploadItems_Form_FileDeck_BoardContentItem">\
        Section: <select name="1_FDeck_Section">\
                  <option value="Class_1">Math</option>\
                  <option value="Class_1">English</option>\
                <select>\
        Unit: <select name="1_FDeck_Unit">\
                <option value="Class_1_Unit_1">Unit 1</section>\
                <option value="Class_1_Unit_2">Unit 2</section>\
              </select>\
      </div>\
      <div class="SM8_UploadItems_Form_FileDeck_BoardContentItem">\
        Tags: <input type="text" name="1_Tags" value=""/ placeholder="Seperate with commas">\
      </div>\
      <div class="SM8_UploadItems_Form_FileDeck_BoardContentItem">\
        Descritpion: <textarea style="resize:none;" rows="5" cols="45" name="1_Description" value=""/ placeholder="Descrption / Summary / and or notes."></textarea>\
      </div>\
    </div>\
  </div>';

  return form;
}

/***********************************************************
* Functions hading user submission of form
************************************************************/
//Deck is pre upload when stuff exists in div
//Pool is file grouping / post submission
function SM8_ProcessFilePool(){
  var formData = new FormData(Form);//Auto creates obj with data already in fields

  var xmlHttp = new XMLHttpRequest();
  xmlHttp.open("POST","./proc/CreateStudyResource.php",true);
  xmlHttp.onreadystatechange = function(){
    if(xmlHttp.status == 200 && xmlHttp.readystate == 4){
      console.log("[DEBUG]File Upload Sucesful - Reponse data: " + xmlHttp.reponseText);
    }else{
      console.log("[DEBUG]File Upload Failed - Reponse data: " + xmlHttp.reponseText);
    }
  }

  xmlHttp.send(formData);//Send auto filled form data object
}
