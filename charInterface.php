<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['characters']) && 
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
        This HTML and JS script is the editTables interface related to the database characters table.
        It's designed to give modification control over the tables and data related to characters.  
        -->
<!-- The HTML for this page is designed as two side-by-side div containers. The left-side-by-side 
     is populated with existing character information in a table format and the right-side is 
     reserved for displaying previews.  -->
<div class="left"> 
    <!-- Although not a 'form' tag, #charForm fulfils a similar purpose -->
    <div id="charForm"> <!-- Thanks to Soroush Ghorashi for the tip on using div tag instead form -->
        <table>
            <!-- Label, character select input, and modify character button on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Character ID:</td>
                <td>
                    <select id="cid" class="columnWidth">
                        <option value="addChar" selected>Add a New Character</option>
                        <optgroup label="Modify Character" class="charList">
                        </optgroup>
                    </select>
                </td>
                <td><input type="button" value="Add Character" id="modifyChar" class="quickfade" /></td>
            </tr>
            <!-- Label, character script name, and delete character button on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Script Name:</td>
                <td class="columnWidth"><input id="scriptName" type="text" class="normal" required /></td>
                <td><input type="button" value="Delete Character" id ="deleteChar" class="hidden quickfade opaque"/></td>
            </tr>
            <!-- Label and character real name on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Character Name:</td>
                <td class="columnWidth"><input id="realName" type="text" class="normal" value="New character" required /></td>
                <td></td>
            </tr>
            <!-- Label and color selector plugin for speech color on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Speech Color:</td>
                <td>
                    <div id="customWidget">
                        <div id="colorSelector2">
                            <div id="speechColor"></div>
                        </div>
                        <div id="colorpickerHolder2">
                        </div>
                    </div>
                </td>
                <td></td>
            </tr>
            <!-- Label and view select input on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Blink View:</td>
                <td>
                    <select id="blink" class="viewList columnWidth"></select>
                </td>
                <td></td>
            </tr>
            <!-- Label and view select input on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Idle View:</td>
                <td>
                    <select id="idle" class="viewList columnWidth"></select>
                </td>
                <td></td>
            </tr>
            <!-- Label and view select input on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Normal View:</td>
                <td>
                    <select id="normal" class="viewList columnWidth"></select>
                </td>
                <td></td>
            </tr>            
            <!-- Label and view select input on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Speech View:</td>
                <td>
                    <select id="speech" class="viewList columnWidth"></select>
                </td>
                <td></td>
            </tr>            
            <!-- Label and view select input on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Think View:</td>
                <td>
                    <select id="think" class="viewList columnWidth"></select>
                </td>
                <td></td>
            </tr>
            <!-- Label and room select input on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Starting Room:</td>
                <td>
                    <select id="startRoom" class="roomList columnWidth"></select>
                </td>
                <td></td>
            </tr>
            <!-- Label and X-Position for starting room -->
            <tr class="buttonHeight">
                <td class="columnWidth">Starting Room X-Position:</td>
                <td class="columnWidth"><input id="startX" type="number" class="normal" value=160 required /></td>
                <td></td>
            </tr>
            <!-- Label and Y-Position for starting room -->
            <tr class="buttonHeight">
                <td class="columnWidth">Starting Room Y-Position:</td>
                <td class="columnWidth"><input id="startY" type="number" class="normal" value=120 required /></td>
                <td></td>
            </tr>
            <!-- This row receives progress feedback from submitComplete() -->
            <tr><td colspan=3 id="progress" class="darkblue textcenter fade opaque"></td></tr>
        </table>
    </div>
</div>
<!-- Receives preview content -->
<div class="fat right" id="preview"></div>
<script>
    /* Clear form on load */
    clearForm();
    
    /* Ready functions */
    $(function(){
        /* When user changes character selection */ 
        $("#cid").on('change', function(){
            $currentChar = $("#cid").val();
            
            /*  Toggle modify and delete character buttons and populate form data based
                on character selection. */
            if(($currentChar !== "addChar") && (!$("#deleteChar").hasClass("hidden"))){
                populateForm($currentChar);                
            }
            else if(($currentChar !== "addChar") && ($("#deleteChar").hasClass("hidden"))){
                toggleButtons();
                populateForm($currentChar);                
            }
            else{
                toggleButtons();
                clearForm();
            }
        });
        
        /* When user clicks on 'Delete' Button */
        $("#deleteChar").on('click', function(){
            if(!$("#deleteChar").hasClass("opaque")){
                deleteChar($("#cid").val());
            }
        });
        
        /* When user clicks on 'Add' Button or 'Update' Button */
        $("#modifyChar").on('click', function(){
            putChar($("#cid").val(), $("#scriptName").val(), $("#blink").val(), $("#idle").val(), $("#normal").val(), $("#speechColor").css('background-color'), $("#speech").val(), $("#think").val(), $("#realName").val(), $("#startRoom").val(), $("#startX").val(), $("#startY").val());
        });
        
        $("#startX").on('change', function(){
            if(($("#startX").val() == undefined) || ($("#startX").val() == "")){
                $("#startX").val(0);
            }
        });
        
        $("#startY").on('change', function(){
            if(($("#startY").val() == undefined) || ($("#startY").val() == "")){
                $("#startY").val(0);
            }
        });
    });
    
    /* Get form data from script based on character selection and populate respective elements with data */
    function populateForm(charID){
        $.ajax({
            type: 'POST',
            url: 'getCharacter.php',
            data: {
                'cid':charID
            },
            success: function(output){
                /* Output will be comma delimited */
                var arr = output.split(',');
                
                scriptName = arr[0];
                realName = arr[1];
                speechColor = arr[2];
                blinkVid = arr[3];
                idleVid = arr[4];
                normalVid = arr[5];
                speechVid = arr[6];
                thinkVid = arr[7];
                startingRid = arr[8];
                startingX = arr[9];
                startingY = arr[10];
                
                $("#scriptName").val(scriptName);
                $("#realName").val(realName);
                
                /* Speech color must be changed via #speechColor and #colorpickerHolder2 */
                $("#speechColor").css('background-color', speechColor);
                $('#colorpickerHolder2').ColorPickerSetColor(speechColor);
                
                /* Populate view-related select inputs */
                if(blinkVid !== ""){
                    $("#blink").val(blinkVid);
                }
                else{
                    $("#blink").val("NULL");
                }
                
                if(idleVid !== ""){
                    $("#idle").val(idleVid);
                }
                else{
                    $("#idle").val("NULL");
                }
                
                if(normalVid !== ""){
                    $("#normal").val(normalVid);
                }
                else{
                    $("#normal").val("NULL");
                }
                
                if(speechVid !== ""){
                    $("#speech").val(speechVid);
                }
                else{
                    $("#speech").val("NULL");
                }
                
                if(thinkVid !== ""){
                    $("#think").val(thinkVid);
                }
                else{
                    $("#think").val("NULL");
                }
                
                /* Populate room-related select input */
                if(startingRid !== ""){
                    $("#startRoom").val(startingRid);
                }
                else{
                    $("#startRoom").val("NULL");
                }
                
                $("#startX").val(startingX);
                $("#startY").val(startingY);
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
    
    /* Calls script to delete character selection from database */
    function deleteChar(charID){
        $.ajax({
            type: 'POST',
            url: 'deleteCharacter.php',
            data: {
                'cid':charID
            },
            success: function(output){
                clearForm();
                submitComplete(output);
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
    
    /*  Calls script to modify database with new form information for selected or 
        additional character. */
    function putChar(charID, scriptName, blinkVid, idleVid, normalVid, speechColor, speechVid, thinkVid, realName, startingRid, startingX, startingY){
        $.ajax({
            type: 'POST',
            url: 'putCharacter.php',
            data: {
                'cid':charID,
                'scriptName':scriptName,
                'blinkVid':blinkVid,
                'idleVid':idleVid,
                'normalVid':normalVid,
                'speechColor':speechColor,
                'speechVid':speechVid,
                'thinkVid':thinkVid,
                'realName':realName,
                'startingRid':startingRid,
                'startingX': startingX,
                'startingY': startingY
            },
            success: function(output){
                clearForm();
                submitComplete(output);
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
    
    /*  Using opaque fades, this function transitions the modify button between 
        'Add' state and 'Update' state and transitions delete button between 
        hidden and visible.  */
    function toggleButtons(){
        if($("#modifyChar").val() == "Add Character"){
            $("#modifyChar").toggleClass("opaque");
            $("#deleteChar").toggleClass("hidden");
            setTimeout(function(){
                $("#modifyChar").val("Update Character");
                $("#modifyChar").toggleClass("opaque");
                $("#deleteChar").toggleClass("opaque");
            }, 300);
        }
        else{
            $("#deleteChar").toggleClass("opaque");
            $("#modifyChar").toggleClass("opaque");
            setTimeout(function(){
                $("#modifyChar").val("Add Character");
                $("#modifyChar").toggleClass("opaque");
                $("#deleteChar").toggleClass("hidden");
            }, 300);
        }
    }
    
    /* Resets form to default contents */
    function clearForm(){
        if($("#colorpickerHolder2").css('height') !== "0px"){
            $("#colorSelector2").trigger('click');
        }
        
        $("[class*=charList]").empty().load("charList.php");
        /* Thanks to http://stackoverflow.com/questions/5958607/how-to-load-php-through-jquery-and-append-it-to-an-existing-div-container */
        $.get("viewList.php", function(data){
            $("[class*=viewList]").append("<option value=\"NULL\" selected>None</option>");
            $("[class*=viewList]").append(data);            
        });
        $.get("roomList.php", function(data){
            $("[class*=roomList]").append("<option value=\"NULL\" selected>Nowhere</option>");
            $("[class*=roomList]").append(data);            
        });
        $("#scriptName").val("");
        $("#realName").val("");
        $("#speechColor").css('background-color', '#FE5454');
        $("#colorpickerHolder2").ColorPickerSetColor('#FE5454');
        $("#startX").val(160);
        $("#startY").val(120);
        
        /* Code for jQuery plugin - ColorPicker */
        var widt = false;
        $('#colorpickerHolder2').ColorPicker({
			flat: true,
			color: '#FE5454',
			onSubmit: function(hsb, hex, rgb) {
				$('#colorSelector2 div').css('background-color', '#' + hex);
			}
        });
            
        $('#colorpickerHolder2>div').css('position', 'absolute');
        
        $('#speechColor').css('background-color', '#FE5454');
        
        $('#colorSelector2').bind('click', function() {
            $('#colorpickerHolder2').stop().animate({
                height: widt ? 0 : 173
            }, 500);
            
            widt = !widt;
        });        
    }
    
    /* Provides temporarily visible user feedback regarding progress of requested action */
    function submitComplete(userFeedback){
        $("#progress").empty().append(userFeedback);
        $("#progress").toggleClass("opaque");
        setTimeout(function(){
            $("#progress").toggleClass("opaque");
        }, 3000);
    }
</script>