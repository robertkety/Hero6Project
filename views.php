<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['views'])){
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
        This combination PHP and HTML script creates the views interface where
        users can browse the available views that exist in the database and 
        preview the sprite images of the view (ordered by loops and frames).  */
    
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
<div id="top"></div><!-- Displays the number of rows returned -->
<div id="bottom">
    <div id="left" class="slim left">
        <table id="dispRows">
            <?php
                /* This script outputs the view id, scriptName, and preview button in HTML
                   table format. */
                /* The first row contains column headers */
                printf("<tr id=\"topRow\"><td>View ID</td><td>View Name</td><td></td></tr>");
                
                /* Collect table rows in descending order for output of last 10 rows */
                if (!($stmt = $mysqli->prepare("SELECT id, scriptName FROM view;"))) {
                    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
                }                    
                if (!$stmt->execute()) {
                    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
                }

                /*  Bind parameters to variable, fetch, and rename variable */
                /*  Renaming isn't necessary, but I find it useful for my programming style -
                    I like having the original variable available to use like a constant.  */
                $out_id    = NULL;
                $out_scriptName = NULL;
                $idCount = 0;
                if (!$stmt->bind_result($out_id, $out_scriptName)) {
                    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
                }
                while($stmt->fetch()) {
                    $id = $out_id;
                    /* This is an additionl layer of protection.  INSERT and UPDATE scripts already protect against 
                       special characters.  */
                    $scriptName = htmlspecialchars($out_scriptName);  //Protect against special chars from database
                    
                    /* Output row data and associated preview button Javascript */
                    printf("<tr>");
                        printf("<td>%d</td><td>%s</td>", $id, $scriptName);
                        printf("<td><input type=\"Submit\" value=\"Preview\" id=\"%s\"/>", $id);
                        printf("<script>");
                            printf("$(\"#%s\").click(function(){", $id);
                                printf("$(\"#viewTitle\").empty().append(\"View %s - %s\").addClass(\"addLowerMargin\");", $id, $scriptName);
                                printf("$(\"#preview\").empty().load(\"loops.php?viewID=%s\");", $id);                                
                            printf("});");
                        printf("</script></td>");
                    printf("</tr>");
                    
                    $idCount++; //Count rows to handle empty table and number of rows returned
                }
                
                /* Handle empty table output */
                if($idCount == 0){
                    printf("<tr><td colspan = 3>Empty Table</td></tr>");
                }
                
                /* Output number of rows returned */
                printf("<script>");
                    printf("$(\"#top\").empty().append(\"%s rows returned\");", $idCount);
                printf("</script>");
            ?>
        </table>
    </div>
    <div id="right" class="half right fixed">
        <div id="viewTitle">
        </div>
        <div id="preview">
            Click a 'Preview' Button to inspect a View
        </div>        
    </div>
</div>