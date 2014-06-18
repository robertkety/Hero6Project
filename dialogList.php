<?php 
    /*  Programmed by Robert Kety,
        This PHP script outputs HTML code for each dialog ID in the dialog table.
        This script is intended to be loaded inside a select element.  Output will be 
        option tags for previously mentioned select tag.  */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Collect ID and scriptName information from database */
    if (!($stmt = $mysqli->prepare("SELECT id, scriptName, cid FROM dialog;"))) {
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
    $out_cid    = NULL;
    if (!$stmt->bind_result($out_id, $out_scriptName, $out_cid)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($stmt->fetch()) {
        $id = $out_id;
        $scriptName = $out_scriptName;
        $cid = $out_cid;
        
        /* Output option */
        printf("<option value=\"".$id."\">".$id." - ".$scriptName."</option>");
    }
    
    /* Close database connection */
    $mysqli->close();
?>