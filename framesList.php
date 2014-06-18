<?php 
    /*  Programmed by Robert Kety,
        This PHP script outputs HTML code for each frame ID in the viewLoopsFrame table
        for rows containing the view and loop ID received via POST.  This script is 
        intended to be loaded inside a select element.  Output will be option tags 
        for previously mentioned select tag.  */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /*  Receive variables from POST */
    $vid = $_POST['vid'];
    $lid = $_POST['lid'];
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /*  Collect frame numbers and sprite IDs from viewLoopsFrame table for target view and 
        loop ID */
    if (!($stmt = $mysqli->prepare("SELECT frameNum, sid FROM viewLoopsFrame WHERE ".
        "vid = $vid AND lid = $lid;"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }                    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_frameNum = NULL;
    $out_sid = NULL;    
    if (!$stmt->bind_result($out_frameNum, $out_sid)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($stmt->fetch()) {
        $frameNum = $out_frameNum;
        $sid = $out_sid;
        if($sid !== NULL){  //Only display options for frames that exist
            printf("<option value=\"$sid\">Frame $frameNum</option>");
        }
    }
    
    /* Close database connection */
    $mysqli->close();
?>