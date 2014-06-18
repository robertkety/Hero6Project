<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['dialogs'])){
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
        This combination PHP and HTML script creates the dialogs interface where
        users can browse the available dialogs that exist in the database.  */
    
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
?>

<!-- The HTML for this page is designed as two side-by-side div containers. The left-side-by-side 
     is populated with existing dialog information in a table format and the right-side is 
     reserved for displaying previews. No display data is available at this time. 
     This form has been modified via CSS to appear as a single centered div. -->
<div id="top" class="normal center"></div>  <!-- This will receive the number of rows returned. -->
<div id="left" class="normal center">
    <table id="dispRows">
        <?php
            /*  This script populates the dialog table with information from the
                database in a table format.  */
                    
            /* Table header - contains column titles */
            printf("<tr id=\"topRow\"><td>Dialog ID</td><td>Dialog Name</td>");
            printf("<td>Character ID</td><td>Character Script Name</td>");
            printf("<td>Character Real Name</td></tr>");
            
            /* Collect dialog and character from dialog and characters tables */
            if (!($stmt = $mysqli->prepare("SELECT dialog.id, dialog.scriptName, ".
                "characters.id, characters.scriptName, characters.realName FROM dialog ".
                "INNER JOIN characters ON characters.id = cid;"))) {
                echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
            }                    
            if (!$stmt->execute()) {
                echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
            }

            /*  Bind parameters to variable, fetch, and rename variable */
            /*  Renaming isn't necessary, but I find it useful for my programming style -
                I like having the original variable available to use like a constant.  */
            $out_did         = NULL;
            $out_dScriptName = NULL;
            $out_cid         = NULL;
            $out_cScriptName = NULL;
            $out_realName    = NULL;
            $idCount = 0;
            if (!$stmt->bind_result($out_did, $out_dScriptName, $out_cid, $out_cScriptName, $out_realName)){
                echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
            }
            while($stmt->fetch()) {
                /*  This is an additional layer of protection.  The scripts that modify the database already
                    contain protection against special characters. */
                $cScriptName = htmlspecialchars($out_cScriptName);  //Protect against special chars from database
                $realName = htmlspecialchars($out_realName);  //Protect against special chars from database
                $dScriptName = htmlspecialchars($out_dScriptName);  //Protect against special chars from database

                $cid = $out_cid;
                $did = $out_did;
                
                /* Output columns for this row in table format */
                printf("<tr>");
                    if($did !== NULL){
                        printf("<td>%d</td><td>%s</td>", $did, $dScriptName);
                    }
                    else{
                        printf("<td>n/a</td><td>No dialog</td>");
                    }
                    printf("<td>%d</td><td>%s</td><td>%s</td>", $cid, $cScriptName, $realName);
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
<!-- Although this is not used, it is included for future compatibility -->
<div id="right" class="hidden right"></div>