<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['characters'])){
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
        This combination PHP and HTML script creates the characters interface where
        users can browse the available characters that exist in the database and 
        preview their starting room and the first frame of their normal view.  */
    
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
?>
<!-- The HTML for this page is designed as two side-by-side div containers. The left-side-by-side 
     is populated with existing character information in a table format and the right-side is 
     reserved for displaying previews.  -->
<div id="top"> <!-- This will receive the number of rows returned. -->
</div>
<div id="bottom">   <!-- This contains the table of characters in the database -->
    <div id="left" class="half left">
        <table id="dispRows">
            <?php
                /*  This script populates the character table with information from the
                    database in a table format.  */
                    
                /* Table header - contains column titles */
                printf("<tr id=\"topRow\"><td>ID</td><td>Character Script Name</td>");
                printf("<td>Character Real Name</td><td>Speech Color</td>");
                printf("<td>Starting Room</td><td></td></tr>");
                
                /* Collect character and speech color from characters and color tables */
                if (!($stmt = $mysqli->prepare("SELECT characters.id, scriptName, realName, ".
                    "startingRid, color.id, red, green, blue FROM characters INNER JOIN ".
                    "color ON color.id = speechColor;"))) {
                    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
                }                    
                if (!$stmt->execute()) {
                    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
                }

                /*  Bind parameters to variable, fetch, and rename variable */
                /*  Renaming isn't necessary, but I find it useful for my programming style -
                    I like having the original variable available to use like a constant.  */
                $out_id          = NULL;
                $out_scriptName  = NULL;
                $out_realName    = NULL;
                $out_startingRid = NULL;
                $out_colorID     = NULL;
                $out_red         = NULL;
                $out_green       = NULL;
                $out_blue        = NULL;
                $idCount = 0;
                if (!$stmt->bind_result($out_id, $out_scriptName, $out_realName, $out_startingRid, $out_colorID, $out_red, $out_green, $out_blue)) {
                    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
                }
                while($stmt->fetch()) {
                    /*  This is an additional layer of protection.  The scripts that modify the database already
                        contain protection against special characters. */
                    $scriptName = htmlspecialchars($out_scriptName);  //Protect against special chars from database
                    $realName = htmlspecialchars($out_realName);  //Protect against special chars from database
                    
                    $id = $out_id;
                    $startingRid = $out_startingRid;
                    $colorID = $out_colorID;
                    $red = $out_red;
                    $green = $out_green; 
                    $blue = $out_blue;
                    
                    /* Output columns for this row in table format */
                    printf("<tr>");
                        printf("<td>%d</td><td>%s</td><td>%s</td>", $id, $scriptName, $realName);
                        printf("<td id=\"color_%s\">%d</td>", $scriptName, $colorID);
                        if($startingRid !== NULL){    
                            printf("<td><input type=\"Submit\" value=\"%s\" id=\"room_%s%s\"/></td>", $startingRid, $id, $startingRid);
                        }
                        else{
                            printf("<td>n/a</td>");
                        }
                        /* There will always be a view available for a character */
                        printf("<td><input type=\"Submit\" value=\"View\" id=\"view_%s\"/>", $id);
                        
                        /* Supporting Javascript for room and view buttons in this column */
                        printf("<script>");
                            if($startingRid !== NULL){    
                                printf("$(\"#room_%s%s\").click(function(){", $id, $startingRid);
                                    printf("getPreview($startingRid); ");
                                printf("});");
                            }
                            /* There will always be a view available for a character */
                            printf("$(\"#view_%s\").click(function(){", $id);
                                printf("$(\"#viewTitle\").empty().append(\"Views for %s\").addClass(\"addLowerMargin\");", $scriptName);
                                printf("$(\"#preview\").empty().load(\"charViews.php?charID=%d\");", $id);                                
                            printf("});");
                            
                            /* Customize speech color column text to match speech color */
                            printf("$(\"#color_".$scriptName."\").css(\"color\", ");
                            printf("\"rgb(".$red.", ".$green.", ".$blue.")\");");
                            printf("$(\"#color_".$scriptName."\").css(\"font-weight\", ");
                            printf("\"bold\");");
                        printf("</script></td>");
                    printf("</tr>");
                    $idCount++; //Count rows for output to #top div
                }
                
                /* Handle empty table output */
                if($idCount == 0){
                    printf("<tr><td colspan = 3>Empty Table</td></tr>");
                }
                
                /* Output number of rows returned */
                printf("<script>");
                    printf("$(\"#top\").empty().append(\"%s rows returned\");", $idCount);
                printf("</script>");
                
                /*  Close database connection */
                $mysqli->close();
            ?>
        </table>
    </div>
    <div id="right" class="half right fixed">
        <div id="viewTitle">
        </div>
        <!-- Default preview content consists of instructions for loading previews -->
        <div id="preview">
            Click a 'Views' Button to inspect views for that character or<br/>
            Click a button with a room number to view the starting room
        </div>        
    </div>
</div>
<script>
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
                }, 250); //Delay loading content until fade transition completes
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
        }, 500);    //Delay transition until fadeout and append is complete
    }
</script>