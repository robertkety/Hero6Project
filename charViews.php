<?php  
    /*  Programmed by Robert Kety,
        This PHP script outputs the first frame of each available view based on the character ID it
        receives via POST.  This script is intended to be called on a load into a preview element
        so the user can review a sample of the existing views. This script also includes one of three
        GET calls for this website.  I wanted to demonstrate my ability in this area while 
        keeping my data secure.  */
        
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /*  Receive variables from GET */
    $charID = $_GET['charID'];
    
    /* Collect character and view information from characters and view tables. */
    if (!($stmt = $mysqli->prepare("SELECT characters.id, view.id, view.scriptName, blinkVid, ".
        "idleVid, normalVid, speechVid, thinkVid FROM characters INNER JOIN ".
        "view ON blinkVid = view.id OR ".
        "idleVid = view.id OR ".
        "normalVid = view.id OR ".
        "speechVid = view.id OR ".
        "thinkVid = view.id WHERE ".
        "characters.id = ".$charID.";"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }                    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_cid         = NULL;
    $out_vid         = NULL;
    $out_vScriptName = NULL;
    $out_blink       = NULL;
    $out_idle        = NULL;
    $out_normal      = NULL;
    $out_speech      = NULL;
    $out_think       = NULL;
    $idCount = 0;
    if (!$stmt->bind_result($out_cid, $out_vid, $out_vScriptName, $out_blink, $out_idle, $out_normal, $out_speech, $out_think)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($stmt->fetch()) {
        /* This is an additional layer of protection - INSERT and UPDATE scripts
           already protect against special characters. */
        $vScriptName  = htmlspecialchars($out_vScriptName);
        
        $cid = $out_cid;
        $vid  = $out_vid;
        $blink = $out_blink;
        $idle = $out_idle;
        $normal = $out_normal;
        $speech = $out_speech;
        $think = $out_think;
        
        /*  Outputs first frame of first loop of respective view, when available.
            I prefer to use POST calls, but included one GET script for practice. 
            Since this is only fetching data, it should be secure. */
        if($blink === $vid){
            printf("<span class=\"loopTitle\">Blink View - %s (id: %d)</span><table><tr id=\"blinkRow_%s\"></tr></table>", $vScriptName, $vid, $vid);
            printf("<script>");
                printf("$(\"#blinkRow_%s\").empty().load(\"frames.php?viewID=%s&loopID=0&allFrames=0\");", $vid, $vid);
            printf("</script>");
        }
        if($idle === $vid){
            printf("<span class=\"loopTitle\">Idle View - %s (id: %d)</span><table><tr id=\"idleRow_%s\"></tr></table>", $vScriptName, $vid, $vid);
            printf("<script>");
                printf("$(\"#idleRow_%s\").empty().load(\"frames.php?viewID=%s&loopID=0&allFrames=0\");", $vid, $vid);
            printf("</script>");
        }
        if($normal === $vid){
            printf("<span class=\"loopTitle\">Normal View - %s (id: %d)</span><table><tr id=\"normalRow_%s\"></tr></table>", $vScriptName, $vid, $vid);
            printf("<script>");
                printf("$(\"#normalRow_%s\").empty().load(\"frames.php?viewID=%s&loopID=0&allFrames=0\");", $vid, $vid);
            printf("</script>");
        }
        if($speech === $vid){
            printf("<span class=\"loopTitle\">Speech View - %s (id: %d)</span><table><tr id=\"speechRow_%s\"></tr></table>", $vScriptName, $vid, $vid);
            printf("<script>");
                printf("$(\"#speechRow_%s\").empty().load(\"frames.php?viewID=%s&loopID=0&allFrames=0\");", $vid, $vid);
            printf("</script>");
        }
        if($think === $vid){
            printf("<span class=\"loopTitle\">Think View - %s (id: %d)</span><table><tr id=\"thinkRow_%s\"></tr></table>", $vScriptName, $vid, $vid);
            printf("<script>");
                printf("$(\"#thinkRow_%s\").empty().load(\"frames.php?viewID=%s&loopID=0&allFrames=0\");", $vid, $vid);
            printf("</script>");
        }
        
        $idCount++; //Count rows for output of number of rows returned
    }
    
    /* Handle empty table output */
    if($idCount == 0){
        printf("<td colspan = 3>No Views</td>");
    }
    
    /* Close database connection */
    $mysqli->close();
?>