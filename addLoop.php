<?php  
    /*  Programmed by Robert Kety,
        This PHP script is designed to receive a view ID via POST and INSERT a new loop
        for that view in viewLoopsFrame.  This requires the determination of the next
        highest loop ID number, the existence and possible addition of a matching loop
        ID in the loops table, and the insertion to the viewLoopsFrame table. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /*  Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }

    /*  Receive variables from POST */
    $vid = $_POST['vid'];
    
    /*  Select the next highest integer in the view ID for the new loop */
    if(!($maxID = $mysqli->prepare("SELECT MAX(lid) FROM viewLoopsFrame WHERE vid = $vid;"))){
        echo "Prepare failed: (" . $maxID->errno . ") " . $maxID->error;
    }
    if (!$maxID->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_lid = NULL;
    if (!$maxID->bind_result($out_lid)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($maxID->fetch()){
        $lid = $out_lid;
    }
    
    /*  Increment frameNum for new insert to viewLoopsFrame table */
    $lid = $lid + 1;

    /*  Determine if loops table should expand to accommodate more loop 
        directions by counting the existing number of loop IDs and comparing
        it to the MAX loop ID derived above (i.e., $lid) */
    if(!($countLoops = $mysqli->prepare("SELECT COUNT(id) FROM loops;"))){
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$countLoops->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_loopsCount = NULL;
    if (!$countLoops->bind_result($out_loopsCount)) {
        echo "Binding output parameters failed: (" . $countLoops->errno . ") " . $countLoops->error;
    }    
    while($countLoops->fetch()){
        $loopsCount = $out_loopsCount;
    }
    
    /*  Insert new direction to loops table, using default direction value,
        when necessary. */
    if($loopsCount <= $lid){
        if(!$mysqli->query("INSERT INTO loops (id) VALUES ($lid)")){
            echo "loops Insert failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
    }
    
    /*  INSERT new loop instance to viewLoopsFrame - Loops start with frameNum
        set to zero and require an adjustment to 'sql_mode' during INSERT. */
    if(!$mysqli->multi_query("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'; ".
        "INSERT INTO viewLoopsFrame VALUES ($vid, $lid, 0, NULL, NULL); ".
        "SET SESSION sql_mode = '';")){
        echo "viewLoopsFrame Insert failed: (" . $mysqli->errno . ") " . $mysqli->error; 
    }
    
    /*  Output for submitComplete() user feedback */
    echo "Loop Added";
    
    /*  Close database connection */
    $mysqli->close();
?>