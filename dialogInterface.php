<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['dialogs']) && 
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
        This HTML and JS script is the editTables interface related to the database dialog table.
        It's designed to give modification control over the tables and data related to dialogs.  
        -->
<!-- The HTML for this page is designed as two side-by-side div containers. The left-side-by-side 
     is populated with existing character information in a table format and the right-side is 
     reserved for displaying previews. No display data is available at this time. -->
<div class="left"> 
    <!-- Although not a 'form' tag, #charForm fulfils a similar purpose -->
    <div id="dialogForm"> <!-- Thanks to Soroush Ghorashi for the tip on using div tag instead form -->
        <table>
            <!-- Label, dialog select input, and modify dialog button on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Dialog ID:</td>
                <td>
                    <select id="did" class="columnWidth">
                        <option value="addDialog" selected>Add a New Dialog</option>
                        <optgroup label="Modify Dialog" class="dialogList">
                        </optgroup>
                    </select>
                </td>
                <td><input type="button" value="Add Dialog" id="modifyDialog" class="quickfade" /></td>
            </tr>
            <!-- Label, dialog script name, and delete dialog button on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Script Name:</td>
                <td class="columnWidth"><input id="scriptName" type="text" class="normal" required /></td>
                <td><input type="button" value="Delete Dialog" id ="deleteDialog" class="hidden quickfade opaque"/></td>
            </tr>
            <!-- Label and selection list of characters on this row -->
            <tr class="buttonHeight">
                <td class="columnWidth">Character:</td>
                <td>
                    <select id="charSpeaking" class="charList columnWidth"></select>
                </td>
                <td></td>
            </tr>
            <!-- This row receives progress feedback from submitComplete() -->
            <tr><td colspan=3 id="progress" class="darkblue textcenter fade opaque"></td></tr>
        </table>
    </div>
</div>
<div class="fat right" id="preview"></div>
<script>
    /* Clear form on load */
    clearForm();
    
    /* Ready functions */
    $(function(){
        /* When user changes dialog selection */ 
        $("#did").on('change', function(){
            $currentDialog = $("#did").val();
            
            /*  Toggle modify and delete character buttons and populate form data based
                on character selection. */
            if(($currentDialog !== "addDialog") && (!$("#deleteDialog").hasClass("hidden"))){
                populateForm($currentDialog);                
            }
            else if(($currentDialog !== "addDialog") && ($("#deleteDialog").hasClass("hidden"))){
                toggleButtons();
                populateForm($currentDialog);                
            }
            else{
                clearForm();
                toggleButtons();                
            }
        });
        
        /* When user clicks on the 'Delete' Button */
        $("#deleteDialog").on('click', function(){
            if(!$("#deleteDialog").hasClass("opaque")){
                deleteDialog($("#did").val());
            }
        });
        
        /* When user clicks on 'Add' Button or 'Update' Button */
        $("#modifyDialog").on('click', function(){
            putDialog($("#did").val(), $("#scriptName").val(), $("#charSpeaking").val());
        });
    });
    
    /* Calls script to delete dialog selection from database */
    function deleteDialog(dialogID){
        $.ajax({
            type: 'POST',
            url: 'deleteDialog.php',
            data: {
                'did':dialogID
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
        additional dialog. */
    function putDialog(dialogID, scriptName, charID){
        $.ajax({
            type: 'POST',
            url: 'putDialog.php',
            data: {
                'did':dialogID,
                'scriptName':scriptName,
                'cid':charID,                
            },
            success: function(output){
                toggleButtons();
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
        if($("#modifyDialog").val() == "Add Dialog"){
            $("#modifyDialog").toggleClass("opaque");
            $("#deleteDialog").toggleClass("hidden");
            setTimeout(function(){
                $("#modifyDialog").val("Update Dialog");
                $("#modifyDialog").toggleClass("opaque");
                $("#deleteDialog").toggleClass("opaque");
            }, 300);
        }
        else{
            $("#deleteDialog").toggleClass("opaque");
            $("#modifyDialog").toggleClass("opaque");
            setTimeout(function(){
                $("#modifyDialog").val("Add Dialog");
                $("#modifyDialog").toggleClass("opaque");
                $("#deleteDialog").toggleClass("hidden");
            }, 300);
        }
    }
    
    /* Get form data from script based on dialog selection and populate respective elements with data */
    function populateForm(dialogID){
        $.ajax({
            type: 'POST',
            url: 'getDialog.php',
            data: {
                'did':dialogID
            },
            success: function(output){
                /* Output will be comma delimited */
                var arr = output.split(',');
                
                scriptName = arr[0];
                charID = arr[1];
                
                /* Adjust import of NULL data to match existing existing framework */
                if(charID == ""){
                    charID = "NULL"
                }
                
                $("#scriptName").val(scriptName);
                $("#charSpeaking").val(charID);
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
    
    /* Resets form to default contents */
    function clearForm(){
        $("[class*=dialogList]").empty().load("dialogList.php");
        $("#scriptName").val("");
        $.get("charList.php", function(data){
            $("[class*=charList]").append("<option value=\"NULL\" selected>None</option>");
            $("[class*=charList]").append(data);            
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
