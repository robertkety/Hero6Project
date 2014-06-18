<?php  
    /*  Programmed by Robert Kety,
        This PHP script retrieves the description data in the room table for the room 
        ID received via POST. */
        
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Receive variables from POST */
    $roomID = $_POST['rid'];
    
    if($roomID !== "addRoom"){  //"addRoom" is not a valid room ID.  This protects against bad queries
        /* Collect description for room ID */
        if (!($stmt = $mysqli->prepare("SELECT description FROM room WHERE id = $roomID"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }                    
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }

        /*  Bind parameters to variable, fetch, and rename variable */
        /*  Renaming isn't necessary, but I find it useful for my programming style -
            I like having the original variable available to use like a constant.  */
        $out_description = NULL;
        if (!$stmt->bind_result($out_description)) {
            echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }        
        while($stmt->fetch()) {
            $roomDescription = $out_description;
        }
    }
    else{   //If the room has not been added, it cannot have a description
        $roomDescription = "";
    }
    
    echo $roomDescription;
    
    /* Close database connection */
    $mysqli->close();
?>