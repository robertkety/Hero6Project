<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['rooms']) && 
        ($_SESSION['editTables'])){
        $username = $_SESSION['username'];
        $rooms = $_SESSION['rooms'];
        $views = $_SESSION['views'];
        $chars = $_SESSION['characters'];
        $dialogs = $_SESSION['dialogs'];
        $editTables = $_SESSION['editTables'];
    }
    else{
        session_destroy();  //Destroys session created by session_start
        setcookie("PHPSESSID","",time()-3600,"/"); //Delete session cookie (From: http://www.webdeveloper.com/forum/showthread.php?172149-How-to-remove-PHPSESSID-cookie)
        header("Location: login.php");        
    }
?>    
<!--    Programmed by Robert Kety,
        This HTML and JS script is the editTables interface related to the database room table.
        It's designed to give modification control over the tables and data related to rooms.  
        -->
<!-- The HTML for this page is designed as two side-by-side div containers. The left-side-by-side 
     is populated with existing character information in a table format and the right-side is 
     reserved for displaying previews. -->
<div class="left"> 
    <form id="roomForm" action="addRoom.php" method="POST" enctype="multipart/form-data">
        <table>
            <!-- Room selection -->
            <tr>
                <td>Room ID:</td>
                <td>
                    <select id="rid">
                        <option value="addRoom" selected>Add a New Room</option>
                        <optgroup label="Modify Room" class="justRoomsList">
                        </optgroup>
                    </select>
                </td>
            </tr>
            <!-- Room Name input (required) -->
            <tr>
                <td>Room Name:</td>
                <td><input id="description" type="text" required /></td>
            </tr>
            <!-- File input for uploading background image -->
            <tr>
                <td>Background Image:</td>
                <td>
                    <input id="background" name="background" type="file"/>
                    <label id="fileLabel">Please choose a file</label>
                </td>
            </tr>
            <!-- Room Selection for each of the eight directions -->
            <tr><td>North to Room:</td><td>
                <select id="n" class="roomList">
                </select>
            </td></tr>
            <tr><td>Northeast to Room:</td><td>
                <select id="ne" class="roomList">
                </select>
            </td></tr>
            <tr><td>East to Room:</td><td>
                <select id="e" class="roomList">
                </select>
            </td></tr>
            <tr><td>Southeast to Room:</td><td>
                <select id="se" class="roomList">
                </select>
            </td></tr>
            <tr><td>South to Room:</td><td>
                <select id="s" class="roomList">
                </select>
            </td></tr>
            <tr><td>Southwest to Room:</td><td>
                <select id="sw" class="roomList">
                </select>
            </td></tr>
            <tr><td>West to Room:</td><td>
                <select id="w" class="roomList">
                </select>
            </td></tr>
            <tr><td>Northwest to Room:</td><td>
                <select id="nw" class="roomList">
                </select>
            </td></tr>
            <!-- Delete and Add/Update Buttons -->
            <tr>
                <td class="textcenter"><input id="deleteRoom" type="button" value="Delete Room" class="hidden opaque quickfade" /></td>
                <td class="textcenter"><input id="submitRoom" type="submit" value="Add/Update Room"/></td>
            </tr>
            <!-- User feedback for progress -->
            <tr>
                <td colspan=2 id="progress" class="darkblue textcenter fade opaque">Add/Update Complete!</td>
            </tr>
        </table>
    </form>
</div>
<!-- Receives image preview of room -->
<div class="fat right" id="preview"></div>
<script>
    /* Reset form on load */
    clearForm();
    
    /* Ready functions */
    $(function(){
        /* When a user selects a new room ID */
        $("#rid").on('change', function(){        
            $currentRoom = $("#rid").val();
            /* Populate form with existing content for that room */
            getDescription($currentRoom);
            getRoom($currentRoom, "n");
            getRoom($currentRoom, "ne");
            getRoom($currentRoom, "e");
            getRoom($currentRoom, "se");
            getRoom($currentRoom, "s");
            getRoom($currentRoom, "sw");
            getRoom($currentRoom, "w");
            getRoom($currentRoom, "nw");
            updateFileLabel($currentRoom);
            
            /* Toggle preview and delete button where appropriate */
            if($currentRoom !== "addRoom"){
                $("#preview").append(getPreview($currentRoom));
                
                if($("#deleteRoom").hasClass("hidden")){
                    toggleButton();
                }
            }
            else{
                toggleButton();
                $("#viewRoom").toggleClass("opaque"); 
                $("#roomTitle").toggleClass("opaque"); 
                setTimeout(function(){ 
                    $("#preview").empty();
                }, 250);    //Delay for fade
            }
        });
        
        /* Courtesy to:  http://stackoverflow.com/questions/166221/how-can-i-upload-files-asynchronously-with-jquery?rq=1 */
        /* When the user select a file */
        $(':file').change(function(){
            /* Confirm selected file is valid size and MIME */
            if(this.files[0] !== undefined){
                var file = this.files[0];
                var name = file.name;
                var size = file.size;
                var type = file.type;
                
                if(size > 2048000){
                    alert("File size is greater than 2MB.  Please select a smaller file");
                    $(':file').val("");
                    updateFileLabel("");    //Reset file input label
                }
                else if((type !== "image/bmp") && (type !== "image/png")){
                    alert("Invalid MIME type.  Please select an image file that is either .bmp or .png");
                    $(':file').val("");
                    updateFileLabel("");    //Reset file input label
                }
            }
            
            updateFileLabel("");    //Reset file input label
        });
        
        /* When the user clicks the Delete Room button */
        $("#deleteRoom").click(function(){
            deleteRoom($("#rid").val());            
        });
    });
    
    /* Huge thanks to: http://blog.teamtreehouse.com/uploading-files-ajax */
    /* Variables used in form submit function */
    var form = document.getElementById('roomForm');
    var fileSelect = document.getElementById('background');
    var uploadButton = document.getElementById('submitRoom');
    
    form.onsubmit = function(event) {
        /* Prevent loading the target on submit */
        event.preventDefault();
        
        // Create a new FormData object.
        var formData = new FormData();
        
        /* Include file in POST, if selected by user */
        if($('#background').val() !== ""){
            // Get the selected files from the input.
            var files = fileSelect.files;
            
            var file = files[0];
            
            // Check the file type (already confirmed via jQuery)
            
            // Add the file to the request.
            formData.append('background', file, file.name);
        }
        
        // Add remaining form data to request
        formData.append('rid', $("#rid").val());
        formData.append('description', $("#description").val());
        formData.append('n', $("#n").val());
        formData.append('ne', $("#ne").val());
        formData.append('e', $("#e").val());
        formData.append('se', $("#se").val());
        formData.append('s', $("#s").val());
        formData.append('sw', $("#sw").val());
        formData.append('w', $("#w").val());
        formData.append('nw', $("#nw").val());
        
        // Set up the request.
        var xhr = new XMLHttpRequest();
        
        // Open the connection.
        xhr.open('POST', 'addRoom.php', true);
        
        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status !== 200) {
                alert('An error occurred!');
            }
        };
        
        // Send the Data.
        xhr.send(formData);
        
        //User feedback to confirm successful add/update
        if($("#rid").val() == "addRoom"){
            submitComplete("Room Added!");
        }
        else{
            submitComplete("Room Updated!");
        }
    }
    
    /* Toggle Delete button visibility */
    function toggleButton(){
        if($("#deleteRoom").hasClass("hidden")){
            $("#deleteRoom").toggleClass("hidden");
            setTimeout(function(){
                $("#deleteRoom").toggleClass("opaque");
            }, 100);    //Pause for visibility transition
        }
        else{
            $("#deleteRoom").toggleClass("opaque");
            setTimeout(function(){
                $("#deleteRoom").toggleClass("hidden");
            }, 250);    //Pause for fade transition
        }
    }
    
    /* Resets the form to initial (empty) state */
    function clearForm(){
        $noDirection = "";

        $.get("roomList.php", function(data){
            $("[class*=roomList]").append("<option value=\"none\" selected>Nowhere</option>");
            $("[class*=roomList]").append(data);      
            $("[class*=justRoomsList]").append(data);
        });
        //$("[class=roomList]").load("roomList.php");
        $("#description").val("");
        $("#background").val("");
        updateFileLabel("");
        
        if(!$("#deleteRoom").hasClass("hidden")){
            toggleButton();
        }
        
        $("#viewRoom").toggleClass("opaque");
        $("#roomTitle").toggleClass("opaque");
        setTimeout(function(){
            $("#preview").empty();
        }, 250);
    }
    
    /* Retrieve image in HTML format and fade transition appropriately on success */
    function getPreview(roomID){
        $.ajax({
            type: 'POST',
            url: 'viewRoom.php',
            data: {
                'rid':roomID
            },
            success: function(output){
                if(!$("#viewRoom").hasClass("opaque")){
                    $("#viewRoom").toggleClass("opaque"); 
                    $("#roomTitle").toggleClass("opaque");
                } 
                setTimeout(function(){ 
                    $("#preview").empty().append(output);
                    setTimeout(function(){ 
                        $("#viewRoom").toggleClass("opaque"); 
                        $("#roomTitle").toggleClass("opaque");
                    }, 100);    //Delay for load
                }, 250);    //Delay for fade out
            },
            error: function(xhr, err){  //Thanks to: http://stackoverflow.com/questions/8041148/show-proper-error-messages-with-jquery-ajax
                var fullPath = window.location.protocol + "//" + window.location.host, 
                    pathArray = window.location.pathname.split( '/' );
                
                /* Thanks to http://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/ */
                pathArray[pathArray.length - 1] = page;
                pathArray.shift();
                for(var i = 0; i < pathArray.length; i++)
                    fullPath += "/" + pathArray[i];
                
                console.log("The requested page was: " + fullPath + "\nThe error number returned was: " + xhr.status + "\nThe error message was: " + xhr.responseText);
            }
        })
    }
    
    /*  Retrieve room description based on room ID.  
        Output to form on success */
    function getDescription(roomID){       
        $.ajax({
            type: 'POST',
            url: 'getDescription.php',
            data: {
                'rid':roomID
            },
            success: function(output){
                $("#description").val(output);
            },
            error: function(xhr, err){  //Thanks to: http://stackoverflow.com/questions/8041148/show-proper-error-messages-with-jquery-ajax
                var fullPath = window.location.protocol + "//" + window.location.host, 
                    pathArray = window.location.pathname.split( '/' );
                
                /* Thanks to http://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/ */
                pathArray[pathArray.length - 1] = page;
                pathArray.shift();
                for(var i = 0; i < pathArray.length; i++)
                    fullPath += "/" + pathArray[i];
                
                console.log("The requested page was: " + fullPath + "\nThe error number returned was: " + xhr.status + "\nThe error message was: " + xhr.responseText);
            }
        });
    }
    
    /*  Retrieve destination room based on origin room and direction. 
        Output to form on success */
    function getRoom(origin, direction){
        $.ajax({
            type: 'POST',
            url: 'getRoom.php',
            data:{
                'origin':origin,
                'direction':direction
            },
            success: function(destination){
                $optionID = "#" + direction;
                $($optionID).val(destination);
            },
            error: function(xhr, err){  //Thanks to: http://stackoverflow.com/questions/8041148/show-proper-error-messages-with-jquery-ajax
                var fullPath = window.location.protocol + "//" + window.location.host, 
                    pathArray = window.location.pathname.split( '/' );
                
                /* Thanks to http://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/ */
                pathArray[pathArray.length - 1] = page;
                pathArray.shift();
                for(var i = 0; i < pathArray.length; i++)
                    fullPath += "/" + pathArray[i];
                
                console.log("The requested page was: " + fullPath + "\nThe error number returned was: " + xhr.status + "\nThe error message was: " + xhr.responseText);
            }
        });
    }
    
    /* Delete room from room table and associated tables in database */
    function deleteRoom(roomID){
        if(roomID !== "none"){
            $.ajax({
                type: 'POST',
                url: 'deleteRoom.php',
                data:{
                    'rid':roomID
                },
                success: function(confirmation){
                    submitComplete(confirmation);
                },
                error: function(xhr, err){  //Thanks to: http://stackoverflow.com/questions/8041148/show-proper-error-messages-with-jquery-ajax
                    var fullPath = window.location.protocol + "//" + window.location.host, 
                        pathArray = window.location.pathname.split( '/' );
                    
                    /* Thanks to http://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/ */
                    pathArray[pathArray.length - 1] = page;
                    pathArray.shift();
                    for(var i = 0; i < pathArray.length; i++)
                        fullPath += "/" + pathArray[i];
                    
                    console.log("The requested page was: " + fullPath + "\nThe error number returned was: " + xhr.status + "\nThe error message was: " + xhr.responseText);
                }
            });
        }
    }
    
    /*  Update custom label text for file input with default message, user selected 
        file name, or the stored file name for the selected room ID */
    function updateFileLabel(roomID){
        if(roomID !== ""){
            /* Retrieve file name stored in database */
            $.ajax({
                type: 'POST',
                url: 'getFileName.php',
                data:{
                    'rid':roomID
                },
                success: function(fileName){
                    if(fileName !== ""){
                        $("#fileLabel").empty().append(fileName);
                    }
                    else{
                        updateFileLabel("");
                    }
                },
                error: function(xhr, err){  //Thanks to: http://stackoverflow.com/questions/8041148/show-proper-error-messages-with-jquery-ajax
                    var fullPath = window.location.protocol + "//" + window.location.host, 
                        pathArray = window.location.pathname.split( '/' );
                    
                    /* Thanks to http://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/ */
                    pathArray[pathArray.length - 1] = page;
                    pathArray.shift();
                    for(var i = 0; i < pathArray.length; i++)
                        fullPath += "/" + pathArray[i];
                    
                    console.log("The requested page was: " + fullPath + "\nThe error number returned was: " + xhr.status + "\nThe error message was: " + xhr.responseText);
                }
            });
        }
        else{
            if($("#background").val() == ""){
                /* Default label message */
                $("#fileLabel").empty().append("Please choose a file");
            }
            else{
                /* User-selected file name */
                var fileName = $("#background").val();
                
                fileName = fileName.split('\\')[2];
                $("#fileLabel").empty().append(fileName);
            }
        }
    }
    
    /* Temporarily display user feedback message in progress div */
    function submitComplete(userFeedback){
        $("#progress").empty().append(userFeedback);
        $("#progress").toggleClass("opaque");
        setTimeout(function(){
            $("#progress").toggleClass("opaque");            
        }, 3000);
        setTimeout(function(){
            clearForm();
        }, 1000);   //Delay reset until database updates
    }
</script>