<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['rooms'])){
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

    /*  Programmed by Robert Kety,
        This combination PHP and HTML script creates the rooms interface where
        users can browse the available rooms that exist in the database and 
        preview an image of the room.  */
    
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
?>

<!-- Destination for row count -->
<div id="top"></div>

<!-- Remaining Content -->
<div id="bottom">
    <!-- Left-hand div containing a table of SQL content -->
    <div id="left" class="slim left">
        <table id="dispRows">
            <?php
                /* This script outputs the row data for each room ID in HTML table format */
                /* The first row contains column headers */
                printf("<tr id=\"topRow\"><td>Room ID</td><td>Description</td><td></td></tr>");
                
                /* Collect room and roomBackground information from database */
                if (!($stmt = $mysqli->prepare("SELECT id, description, path, fileName, extension FROM ".
                    "room LEFT JOIN roomBackground ON rid = id;"))) {
                    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
                }                    
                if (!$stmt->execute()) {
                    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
                }

                /*  Bind parameters to variable, fetch, and rename variable */
                /*  Renaming isn't necessary, but I find it useful for my programming style -
                    I like having the original variable available to use like a constant.  */
                $out_id    = NULL;
                $out_description = NULL;
                $out_path = NULL;
                $out_fileName = NULL;
                $out_extension = NULL;
                $idCount = 0;
                if (!$stmt->bind_result($out_id, $out_description, $out_path, $out_fileName, $out_extension)) {
                    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
                }                
                while($stmt->fetch()) {
                    $id = $out_id;
                    /*  This is an additional layer of protection. UPDATE and INSERT scripts already protect 
                        against special characters. */
                    $description = htmlspecialchars($out_description);  //Protect against special chars from database
                    $path = htmlspecialchars($out_path);    //Protect against special chars from database
                    $fileName = htmlspecialchars($out_fileName);    //Protect against special chars from database
                    $extension = htmlspecialchars($out_extension);  //Protect against special chars from database
                    
                    /* Room data on this row including preview button and associated Javascript function */
                    printf("<tr>");
                        printf("<td>%d</td><td>%s</td>", $id, $description);
                        if($out_fileName !== NULL){
                            printf("<td><input type=\"Submit\" value=\"Preview\" id=\"%s\"/>", $fileName);
                            printf("<script>");
                                printf("$(\"#%s\").click(function(){", $fileName);
                                    printf("getPreview($id); ");
                                printf("});");
                                
                            printf("</script></td>");
                        }
                        else{
                            printf("<td>n/a</td>"); //No preview button if background image is not in database
                        }
                    printf("</tr>");
                    $idCount++; //Count rows to handle empty table output and record number of rows returned
                }
                
                /* Handle empty table output */
                if($idCount == 0){
                    printf("<tr><td colspan = 3>Empty Table</td></tr>");
                }
                
                /* Diplay number of rows returned */
                printf("<script>");
                    printf("$(\"#top\").empty().append(\"%s rows returned\");", $idCount);
                printf("</script>");
                
                /* Close database connection */
                $mysqli->close();
            ?>
        </table>
    </div>
    
    <!-- Right-hand div for receiving previews of a selected room -->
    <div id="right" class="fat right fixed">
        <div id="preview">
            Click a 'Preview' Button to view a Room
        </div>
    </div>
</div>
<script>
    /*  Retrieve background image in HTML format based on room ID and fade 
        transition appropriately */
    function getPreview(roomID){
        $.ajax({
            type: 'POST',
            url: 'viewRoom.php',
            data: {
                'rid':roomID
            },
            success: function(output){
                $("#viewRoom").toggleClass("opaque"); 
                $("#roomTitle").toggleClass("opaque"); 
                setTimeout(function(){ 
                    $("#preview").empty().append(output);
                }, 250); //Delay for image load time
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
        
        setTimeout(function(){ 
            $("#viewRoom").toggleClass("opaque"); 
            $("#roomTitle").toggleClass("opaque"); 
        }, 500);
    }
</script>