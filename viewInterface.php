<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['views']) && 
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
<!-- Programmed by Robert Kety,
     This was the most complicated part of this website.  The following HTML script creates the editTables
     interface for modifying views.  The complex relationship of views, loops, frames, and their associated
     sprites combined with conditional appearance of form elements made this a complex undertaking. 
     Fortunately, the end result is a very user-friendly and reactive form that assists in view management.
     It's important to remember the following: 
        Sprites occupy frames, frames compose loops, and loops compose views.
        It's possible to have a view with no loops and loops with no frames.
        Frames and loops can only be added/removed from the end of the list (like a stack). 
        Deleting a View or Loop will also cascade deletion to children (i.e., loops and frames); however,
        sprites are never removed from the database on deletion.  Sprites are stored on the file system 
        and should be removed by users with privileges on both the web server and database. -->
<div class="left"> 
    <div id="viewForm">
        <table>
            <!-- View -->
            <tr class="buttonHeight">
                <td>View ID:</td>
                <td>
                    <select id="vid">
                        <option value="addView" selected>Add a New View</option>
                        <optgroup label="Modify View" class="viewList">
                        </optgroup>
                    </select>
                </td>
                <td>
                    <input class="hidden quickfade opaque buttonWidth" type="button" value="Delete View" id="deleteView"/>
                    <input class="quickfade buttonWidth" type="button" value="Add View" id="addView"/>
                </td>                
            </tr>
            <tr class="buttonHeight">
                <td>View Name:</td>
                <td><input id="scriptName" type="text" required /></td>
                <td><input class="hidden opaque quickfade buttonWidth" type="button" value="Update View" id="updateView"/></td>
            </tr>
            <!-- Loop -->
            <tr class="buttonHeight">
                <td class="loopRow hidden opaque quickfade">Loop List:</td>
                <td>
                    <select id="lid" class="loopRow hidden opaque quickfade">
                        <option value="addLoop" selected>Add a new Loop</option>
                        <optgroup label="Modify Loop" class="loopList"></optgroup>
                    </select>
                </td>
                <td>
                    <input class="loopRow hidden opaque quickfade buttonWidth" type="button" value="Add Loop" id="modifyLoop"/>
                </td>
            </tr>
            <!-- Frame -->
            <tr class="buttonHeight">
                <td class="frameRow hidden opaque quickfade">Frame List:</td>
                <td>
                    <select id="sid" class="frameRow hidden opaque quickfade">
                        <option value="addFrame" selected>Add a new Frame</option>
                        <optgroup label="Modify Frame" class="frameList"></optgroup>
                    </select>
                </td>
                <td>
                    <input class="frameRow hidden opaque quickfade buttonWidth" type="button" value="Add Frame" id="modifyFrame"/>
                    <form id="uploadSprite" class="hidden" action="addFrame.php" method="POST" enctype="multipart/form-data">
                        <input id="sprite" class="hidden" type="file"/>                        
                    </form>
                </td>
            </tr>
            <!-- User feedback -->
            <tr>
                <td colspan=3 id="progress" class="darkblue textcenter fade opaque">Add/Update Complete!</td>
            </tr>
        </table>
    </div>
</div>
<!-- This will receive preview content -->
<div class="fat right" id="preview"></div>
<script>
    /* Clear form on load */
    clearForm();
    
    /* Ready functions */
    $(function(){
        /* When a user selects a new view */
        $("#vid").on('change', function(){  
            if($("#vid").val() == ""){
                $("#addView").attr('selected', 'selected');
            }
            
            /* Populate form */
            /* Output script name in appropriate element */
            getScriptName($("#vid").val());
            
            /* Toggle Add, Delete, Update buttons and Loops and Frames selections, where appropriate */
            if(($("#vid").val() == "addView") && !($(".loopRow:first").hasClass("hidden"))){
                toggleButton("view");   //Hides View Buttons 
                toggleLoops();          //Hides loops
                if(!($(".frameRow:first").hasClass("hidden"))){
                    toggleFrames();     //Hides frames
                }
            }
            else if(($("#vid").val() !== "addView") && ($(".loopRow:first").hasClass("hidden"))){
                toggleButton("view");   //Displays View Buttons       
                getLoops($("#vid").val());  //Populate select with loop option for selected view ID
                toggleLoops();          //Displays loops
            }
            else if(($("#vid").val() !== "addView") && !($(".loopRow:first").hasClass("hidden"))){
                toggleLoops();
                if(!($(".frameRow:first").hasClass("hidden"))){
                    toggleFrames();
                }
                setTimeout(function(){
                    getLoops($("#vid").val());   //Populate select with loop option for selected view ID
                    toggleLoops();
                }, 250);    //Delay for fade transition
            }
        });
        
        /* When the user selects a new loop */
        $("#lid").on('change', function(){
            if($("#lid").val() == ""){
                $("#addLoop").attr('selected', 'selected');
            }
            
            /* Toggle Add/Delete button accordingly */
            toggleButton("loop");
            
            /* Toggle frames selection accordingly */
            if(($("#lid").val() == "addLoop") && !($(".frameRow:first").hasClass("hidden"))){
                toggleFrames();      //Hides Frames
            }
            else if (($("#lid").val() !== "addLoop") && ($(".frameRow:first").hasClass("hidden"))){
                /* Populate select with frame options for selected view and loop ID */
                getFrames($("#vid").val(), $("#lid").val());  
                toggleFrames();      //Displays Frames
            }            
            else if(($("#lid").val() !== "addLoop") && !($(".frameRow:first").hasClass("hidden"))){
                toggleFrames();
                setTimeout(function(){
                    /* Populate select with frame options for selected view and loop ID */
                    getFrames($("#vid").val(), $("#lid").val());
                    toggleFrames();
                    $("#preview").empty();
                }, 250);    //Delay for fade transition
            }
        });
        
        /* When the user selects a new frame */
        $("#sid").on('change', function(){
            if($("#sid").val() == ""){
                $("#addFrame").attr('selected', 'selected');
            }
            
            /* Toggle Add/Delete button accordingly */
            toggleButton("frame");
            
            /* Display or remove sprite preview */
            if($("#sid").val() !== "addFrame"){
                previewSprite($("#sid").val());
            }
            else{
                $("#preview").empty();
            }
        });
        
        /* User selects 'Add Frame' button */
        $("#sprite").on('change', function(){
            /* Ensure file meets size and MIME type standard */
            if(this.files[0] !== undefined){
                var file = this.files[0];
                var name = file.name;
                var size = file.size;
                var type = file.type;
                
                if(size > 2048000){
                    alert("File size is greater than 2MB.  Please select a smaller file");
                    $(':file').val("");
                    updateFileLabel("");
                }
                else if((type !== "image/bmp") && (type !== "image/png")){
                    alert("Invalid MIME type.  Please select an image file that is either .bmp or .png");
                    $(':file').val("");
                    updateFileLabel("");
                }
                else{
                    $("#uploadSprite").submit(); //Submit sprite if file is acceptable
                }
            }
        });
        
        /*  User selects 'Add View' button
            This function also handles when the user clicks the 'Update View' button
            (Redirected from function)*/
        $("#addView").click(function(){
            /* Add Button Clicked */
            if(($("#vid").val() == "addView") && ($("#scriptName").val() !== "")){
                addView("", $("#scriptName").val());
            }
            else{ /* Update Button Clicked */
                addView($("#vid").val(), $("#scriptName").val());
            }
        });
        
        /* When the user selects the 'Update Button' (Redirects to addView function) */
        $("#updateView").click(function(){
            $("#addView").trigger("click");
        });
        
        /* User selects Delete View button */
        $("#deleteView").click(function(){
            deleteView($("#vid").val());            
        });
        
        /* User selects Add or Delete Loop buttons */
        $("#modifyLoop").click(function(){
            if(($("#modifyLoop").val() == "Add Loop") && (!$("#modifyLoop").hasClass("opaque"))){
                addLoop($("#vid").val());   //Add Loop Button Clicked
            }
            else if(($("#modifyLoop").val() == "Delete Loop") && (!$("#modifyLoop").hasClass("opaque"))){
                deleteLoop($("#vid").val(), $("#lid").val());   //Delete Loop clicked
            }
        });
        
        /* User selects Add or Delete Frame button */
        $("#modifyFrame").click(function(){
            if(($("#modifyFrame").val() == "Add Frame") && (!$("#modifyFrame").hasClass("opaque"))){
                $("#sprite").click();   //Triggers file input button for Add Frame
            }
            else if(($("#modifyFrame").val() == "Delete Frame") && (!$("#modifyFrame").hasClass("opaque"))){
                deleteFrame($("#vid").val(), $("#lid").val(), $("#sid").val()); //Delete Frame clicked
            }
        });
    });
    
    /* Huge thanks to: http://blog.teamtreehouse.com/uploading-files-ajax */
    /* Variables used in form submit function */
    var form = document.getElementById('uploadSprite');
    var fileSelect = document.getElementById('sprite');
    
    /* Form submit function */
    form.onsubmit = function(event) {
        /* Prevent normal POST execution of form on submit */
        event.preventDefault();
        
        // Create a new FormData object.
        var formData = new FormData();
        
        /* Attach file to formData, if selected */
        if($('#sprite').val() !== ""){
            // Get the selected files from the input.
            var files = fileSelect.files;
            
            var file = files[0];
            
            // Check the file type (already confirmed via jQuery)
            
            // Add the file to the request.
            formData.append('sprite', file, file.name);
        }
        
        // Add remaining form data to request
        formData.append('vid', $("#vid").val());
        formData.append('lid', $("#lid").val());
        
        // Set up the request.
        var xhr = new XMLHttpRequest();
        
        // Open the connection.
        xhr.open('POST', 'addFrame.php', true);
        
        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status !== 200) {
                alert('An error occurred!');
            }
        };
        
        // Send the Data.
        xhr.send(formData);
        
        //User feedback to confirm successful add/update
        submitComplete("Frame Added!");        
    }
    
    /* Toggles supporting Add/Delete/Update buttons for Views, Loops, and Frames */
    function toggleButton(section){
        switch (section){
            case "view":
                if(!($("#addView").hasClass("opaque"))){    //Remove Add View and add Delete and Update View
                    $("#addView").toggleClass("opaque");
                    setTimeout(function(){
                        $("#addView").toggleClass("hidden");
                    }, 250);  //Delay for fade transition
                    setTimeout(function(){
                        $("#deleteView").toggleClass("hidden");
                        $("#updateView").toggleClass("hidden");
                    }, 250);  //Delay for fade transition     
                    setTimeout(function(){
                        $("#deleteView").toggleClass("opaque");
                        $("#updateView").toggleClass("opaque");
                    }, 500);  //Delay for fade transitions
                }
                else{                                      //Remove Delete and Update View and add Add View
                    $("#deleteView").toggleClass("opaque");
                    $("#updateView").toggleClass("opaque");
                    setTimeout(function(){
                        $("#deleteView").toggleClass("hidden");
                        $("#updateView").toggleClass("hidden");
                    }, 250);  //Delay for fade transition
                    setTimeout(function(){
                        $("#addView").toggleClass("hidden");
                    }, 250);  //Delay for fade transition
                    setTimeout(function(){
                        $("#addView").toggleClass("opaque");
                    }, 500);  //Delay for fade transitions
                }
                break;
            case "loop":
                /* Toggle label for modifyLoop between Add/Delete with fade transitions */
                if($("#modifyLoop").hasClass("hidden")){
                    if($("#lid option:last-child").is(":selected")){
                        $("#modifyLoop").toggleClass("hidden");
                        $("#modifyLoop").val("Delete Loop");
                        setTimeout(function(){
                            $("#modifyLoop").toggleClass("opaque");                        
                        }, 250);  //Delay for fade transition
                    }
                    else if($("#lid").val() == "addLoop"){
                        $("#modifyLoop").toggleClass("hidden");
                        $("#modifyLoop").val("Add Loop");
                        setTimeout(function(){
                        $("#modifyLoop").toggleClass("opaque");                            
                        }, 250);  //Delay for fade transition
                    }
                }
                else{
                    $("#modifyLoop").toggleClass("opaque");
                    setTimeout(function(){
                        $("#modifyLoop").toggleClass("hidden");                       
                        if($("#lid option:last-child").is(":selected")){
                            $("#modifyLoop").toggleClass("hidden");
                            $("#modifyLoop").val("Delete Loop");
                            setTimeout(function(){
                                $("#modifyLoop").toggleClass("opaque");                        
                            }, 250);  //Delay for fade transition
                        }
                        else if($("#lid").val() == "addLoop"){
                            $("#modifyLoop").toggleClass("hidden");
                            $("#modifyLoop").val("Add Loop");
                            setTimeout(function(){
                                $("#modifyLoop").toggleClass("opaque");
                            }, 250);  //Delay for fade transition
                        }
                        else{
                            $("#modifyLoop").val("Add Loop");
                        }
                    }, 250);  //Delay for fade transition
                }
                break;
            case "frame":
                /* Toggle modifyFrame button between Add/Delete with fade transitions */
                if($("#modifyFrame").hasClass("hidden")){
                    if($("#sid option:last-child").is(":selected")){
                        $("#modifyFrame").toggleClass("hidden");
                        $("#modifyFrame").val("Delete Frame");
                        setTimeout(function(){
                            $("#modifyFrame").toggleClass("opaque");                        
                        }, 250);  //Delay for fade transition
                    }
                    else if($("#sid").val() == "addFrame"){
                        $("#modifyFrame").toggleClass("hidden");
                        $("#modifyFrame").val("Add Frame");
                        setTimeout(function(){
                        $("#modifyFrame").toggleClass("opaque");                            
                        }, 250);  //Delay for fade transition
                    }
                }
                else{
                    $("#modifyFrame").toggleClass("opaque");
                    setTimeout(function(){
                        $("#modifyFrame").toggleClass("hidden");                       
                        if($("#sid option:last-child").is(":selected")){
                            $("#modifyFrame").toggleClass("hidden");
                            $("#modifyFrame").val("Delete Frame");
                            setTimeout(function(){
                                $("#modifyFrame").toggleClass("opaque");                        
                            }, 250);  //Delay for fade transition
                        }
                        else if($("#sid").val() == "addFrame"){
                            $("#modifyFrame").toggleClass("hidden");
                            $("#modifyFrame").val("Add Frame");
                            setTimeout(function(){
                                $("#modifyFrame").toggleClass("opaque");
                            }, 250);  //Delay for fade transition
                        }
                        else{
                            $("#modifyFrame").val("Add Frame");
                        }
                    }, 250);   //Delay for fade transition
                }
                break;
            default:
                alert("ERROR in parsing argument of toggleButton()");
                break;
        }
    }
    
    /* Toggles visibility of loops row */
    function toggleLoops(){
        if(($(".loopRow:first").hasClass("hidden"))){
            $(".loopRow").toggleClass("hidden");
            $("#modifyLoop").val("Add Loop");  //Reset modifyLoop button for new instance
            setTimeout(function(){
                $(".loopRow").toggleClass("opaque");
            }, 500);
        }
        else if($("#modifyLoop").hasClass("hidden")){
            $("#modifyLoop").toggleClass("opaque");
            $(".loopRow").toggleClass("opaque");
            setTimeout(function(){
                $("#modifyLoop").val("Add Loop");  //Reset modifyLoop button for new instance
                $("#modifyLoop").toggleClass("hidden");
                $(".loopRow").toggleClass("hidden");
            }, 250);
        }
        else{
            $(".loopRow").toggleClass("opaque");
            setTimeout(function(){
                $(".loopRow").toggleClass("hidden");
            }, 250);
        }
        $(".loopList").empty();
    }
    
    /* Toggles visibility of frames row */
    function toggleFrames(){
        if(($(".frameRow:first").hasClass("hidden"))){
            $(".frameRow").toggleClass("hidden");
            $("#modifyFrame").val("Add Frame");  //Reset modifyFrame button for new instance
            setTimeout(function(){
                $(".frameRow").toggleClass("opaque");
            }, 500);            
        }
        else if($("#modifyFrame").hasClass("hidden")){
            $("#modifyFrame").toggleClass("opaque");
            $(".frameRow").toggleClass("opaque");
            setTimeout(function(){
                $("#modifyFrame").val("Add Frame");  //Reset modifyFrame button for new instance
                $("#modifyFrame").toggleClass("hidden");
                $(".frameRow").toggleClass("hidden");
            }, 250);
        }
        else{
            $(".frameRow").toggleClass("opaque");
            setTimeout(function(){
                $(".frameRow").toggleClass("hidden");
            }, 250);
        }
        $(".frameList").empty();
        $("#preview").empty();
    }
        
    /* Reset form to original (empty) values and visibility */
    function clearForm(){
        $(".viewList").load("viewList.php");
        $("#scriptName").val("");
        $("#sprite").val("");
        
        if(!$(".frameRow:first").hasClass("hidden")){
            toggleLoops();
            toggleFrames();
            toggleButton("view");
        }
        else if(!$(".loopRow:first").hasClass("hidden")){
            toggleLoops();
            toggleButton("view");
        }
    }
    
    /* Fetch scriptName of target view ID and output */
    function getScriptName(viewID){
        $.ajax({
            type: 'POST',
            url: 'getScriptName.php',
            data: {
                'vid':viewID
            },
            success: function(output){
                $("#scriptName").val(output);
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
    
    /* Populate loop options for selected view ID */
    function getLoops(viewID){
        $.ajax({
            type: 'POST',
            url: 'loopsList.php',
            data:{
                'vid':viewID
            },
            success: function(output){
                $(".loopList").empty().append(output);
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
    
    /* Populate frame options for selected view and loop ID */
    function getFrames(viewID, loopID){
        $.ajax({
            type: 'POST',
            url: 'framesList.php',
            data:{
                'vid':viewID,
                'lid':loopID
            },
            success: function(output){
                $(".frameList").empty().append(output);
                if(output == ""){
                    $(".frameList").empty().append("<option disabled>No Frames Added</option>");
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
    
    /* Load sprite preview in target div */
    function previewSprite(spriteID){
        $.ajax({
            type: 'POST',
            url: 'previewSprite.php',
            data:{
                'sid':spriteID
            },
            success: function(output){
                $("#preview").empty().append(output);
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
    
    /* Delete target view */
    function deleteView(viewID){
        $.ajax({
            type: 'POST',
            url: 'deleteView.php',
            data:{
                'vid':viewID
            },
            success: function(confirmation){
                submitComplete(confirmation);
                toggleButton("view");        
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
    
    /* Delete target loop */
    function deleteLoop(viewID, loopID){
        $.ajax({
            type: 'POST',
            url: 'deleteLoop.php',
            data:{
                'vid':viewID,
                'lid':loopID
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
    
    /* Delete target frame */
    function deleteFrame(viewID, loopID, frameID){
        $.ajax({
            type: 'POST',
            url: 'deleteFrame.php',
            data:{
                'vid':viewID,
                'lid':loopID,
                'sid':frameID
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
    
    /* Add/Update target view */
    function addView(vid, scriptName){
        $.ajax({
            type: 'POST',
            url: 'addView.php',
            data:{
                'vid':vid,
                'scriptName':scriptName
            },
            success: function(confirmation){
                submitComplete(confirmation);
                if(vid !== ""){
                    toggleButton("view");  
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
    
    /* Add target loop */
    function addLoop(vid){
        $.ajax({
            type: 'POST',
            url: 'addLoop.php',
            data:{
                'vid':vid
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
    
    /* Temporarily output user feedback message */
    function submitComplete(userFeedback){
        clearForm();
        $("#progress").empty().append(userFeedback);
        $("#progress").toggleClass("opaque");
        setTimeout(function(){
            $("#progress").toggleClass("opaque");
        }, 3000);
    }
</script>