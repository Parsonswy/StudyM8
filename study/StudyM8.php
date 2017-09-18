<?php
//Security Check
require("/var/www/studym8/latest/accounts/proc/checkLogin.php")
?>
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="./Study.css"/>
  </head>
  <body onload="calculateNormals();" onresize="calculateNormals();" onclick="toggleFields();">
    <div class="SM8_Browser_Wrap" id="SM8_Browser_Wrap">
      <div class="SM8_Header">
        Header
      </div>

      <div class="SM8_Browser">
        <div class="SM8_FileListing">
          File 1
        </div>
        <div class="SM8_FileListing">
          File 2
        </div>
        <div class="SM8_FileListing">
          File 3
        </div>
      </div>

      <!-- Action button in corner -->
      <div id="SM8_AddItem" class="SM8_AddItem" onclick="SM8_AddItemInflate();">
        +
      </div>

      <!-- Popup menu from action button-->
      <div id="SM8_AddItem_Options" class="SM8_AddItem_Options">
        <div id="SM8_AddItem_Option_Upload" class="SM8_AddItem_Option" onclick="SM8_UploadItemsInfalte(); SM8_AddItemPop();">
          Upload Files
        </div>
      </div>

      <!--File Upload Popup Form-->
      <div class="SM8_UploadItems_Wrap" id="SM8_UploadItems_Wrap">
      <form id="SM8_UploadItems_Form_FileDeck_Form" enctype="multipart/form-data" method="post" name="FileDeck_Form">
        <div class="SM8_UploadItems_Form">
          <div id="SM8_UploadItems_Form_FileDeck">
              <!--Populated with drop down divs by JS as files are added to the pool-->
          </div>
        </div><!--File Upload Wrapper-->
		<div class="SM8_UploadItems_ControlRow">
			<div id="SM8_UploadItems_ControlRow_AddFile" onclick="fileDeck_AddFile();">
				Add New File
			</div>
			<div id="SM8_UploadItems_ControlRow_SubmitForm">
				<input type="submit" value="Submit Files"/>
			</div>
		</div>
		</form><!--TODO:Position properly and fix <close> order-->
		<div class="SM8_UploadItems_Opaque" onclick="SM8_UploadItemsPop();"></div><!--background-->
		</div><!--ItemUpload Form Wrapper-->
      </div><!-- Main Browswer Window Wrapper-->

      <div class="SM8_Homerow">
        home row
      </div>
    </div>
    <script type="text/javascript" src="./UserClinet/StudyM8_Client.js"></script>
    <script type="text/javascript" src="./UserClient/StudyM8_Visuals.js"></script>
  </body>
</html>
