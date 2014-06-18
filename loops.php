<?php  
    /*  Programmed by Robert Kety,
        This PHP script displays loop data associated with the view ID received via 
        GET.  The loops, frames, and charViews PHP pages are my only use of GET for 
        this site.  It is included to demonstrate capability for this type of call. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }

    /* Receive variables via GET */
    $viewID = $_GET['viewID'];
    
    /* Collect loop data from view and loops tables */
    if (!($stmt = $mysqli->prepare("SELECT loops.id, loops.direction FROM view INNER JOIN ".
        "viewLoopsFrame ON view.id = vid INNER JOIN loops ON loops.id = lid WHERE ".
        "view.id = ".$viewID." GROUP BY lid;"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_loopID    = NULL;
    $out_direction  = NULL;
    $idCount = 0;
    if (!$stmt->bind_result($out_loopID, $out_direction)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($stmt->fetch()) {
        $loopID = $out_loopID;
        $direction = $out_direction;
        
        /*  Outputs table framework for displaying loop data (relies on frames.php for inner content). 
            This output will act as a toggling drop down list for viewing sprites in a loop.  */
        printf("<div class=\"loop\">");
            /* Arrow graphic is licensed as public domain, courtesy: http://opengameart.org/users/inanzen */
            printf("<img src=\"images/arrow.png\" id=\"hideArrow_$loopID\" class=\"hideArrow\" alt=\"Toggle dropdown\"/>\n");
            printf("<span class=\"loopTitle\">Loop $loopID - $direction</span>\n");
            printf("<table><tr><td id=\"loopRow_$loopID\" class=\"hidden\">");
            printf("</td></tr></table>");
            printf("<script>");
                printf("$(\"#loopRow_%s\").empty().load(\"frames.php?viewID=%s&loopID=%s&allFrames=1\");", $loopID, $viewID, $loopID);
                printf("$(\"#hideArrow_%s\").click(function(){", $loopID);
                    printf("$(\"#loopRow_%s\").toggleClass(\"hidden\");", $loopID);
                    printf("$(this).toggleClass(\"rotate\");");
                printf("});");
            printf("</script>");
        printf("</div>");
        $idCount++; //Count rows to handle empty table output
    }
    
    /* Handle empty table output */
    if($idCount == 0){
        printf("<tr><td colspan = 3>Empty Table</td></tr>");
    }    
    
    /*  Close database connection */
    $mysqli->close();
?>