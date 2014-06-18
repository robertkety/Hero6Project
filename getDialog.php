<?php 
    /*  Programmed by Robert Kety,
        This PHP script retrieves the script name and character ID in the dialog table for the dialog 
        ID received via POST. */
        
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Receive variables from POST */
    $did = $_POST['did'];
    
    /* Collect scriptName and character ID from dialog table */
    if (!($stmt = $mysqli->prepare("SELECT scriptName, cid FROM dialog WHERE id = $did;"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }                    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_scriptName = NULL;
    $out_cid    = NULL;
    if (!$stmt->bind_result($out_scriptName, $out_cid)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }    
    /* Fetch rows from table */
    while($stmt->fetch()) {
        $scriptName = $out_scriptName;
        $cid = $out_cid;
    }
    
    /* Output is in comma delimited format */
    echo $scriptName;
    echo ",";
    echo $cid;
    
    /* Close database connection */
    $mysqli->close();
?>