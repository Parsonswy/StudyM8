/*
* CSS togles / "animations"
*/

var SM8_AddItemInflated = false;

function calculateNormals(){
  SM8_Browser_Wrap = document.getElementById("SM8_Browser_Wrap");
  SM8_AddItem = document.getElementById("SM8_AddItem");
  SM8_AddItem_Options = document.getElementById("SM8_AddItem_Options");
  SM8_UploadItems_Wrap = document.getElementById("SM8_UploadItems_Wrap");

  //Page Wrapper
  SM8_Browser_Wrap.style.height = (window.innerHeight) + "px";
  SM8_Browser_Wrap.style.width = (window.innerWidth) + "px";

  //Z-seperator / popup wrapper / obscure
  SM8_UploadItems_Wrap.style.width = (window.innerWidth) + "px";
  SM8_UploadItems_Wrap.style.height = (window.innerHeight) + "px";

  //Add Item Button
  SM8_AddItem.style.top = (window.innerHeight - 115) + "px";
  SM8_AddItem.style.left = (window.innerWidth - 70) + "px";

  //Add Item Button Popup
  SM8_AddItem_Options.style.top = (window.innerHeight - 115) + "px";
  SM8_AddItem_Options.style.left = (window.innerWidth - 120) + "px";
}

function toggleFields(){
  /*if(SM8_AddItemInflated)
    SM8_AddItemPop();*/
}

//Option list popup
function SM8_AddItemInflate(){
  SM8_AddItem_Options.style.display = "block";
  SM8_AddItemInflated = true;
}
function SM8_AddItemPop(){
  SM8_AddItem_Options.style.display = "none";
  SM8_AddItemInflated = true;
}

//Toggle Upload window prompt
function SM8_UploadItemsInfalte(){
  SM8_UploadItems_Wrap.style.display = "block";
}
function SM8_UploadItemsPop(){
  SM8_UploadItems_Wrap.style.display = "none";
}

//Toggle file input form
function SM8_UploadItems_Form_FileDeck_BoardInflate(elem){
  elem.parentNode.style.height="225px";
  elem.onclick=function() {SM8_UploadItems_Form_FileDeck_BoardPop(this);}
}
function SM8_UploadItems_Form_FileDeck_BoardPop(elem){
  elem.parentNode.style.height="20px";
  elem.onclick=function() {SM8_UploadItems_Form_FileDeck_BoardInflate(this);}
}
