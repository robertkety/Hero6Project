<?php 
    /*  Programmed by Robert Kety,
        This PHP script outputs HTML code for each room ID and description in the room
        table.
        This script is intended to be loaded inside a select element.  Output will be 
        option tags for previously mentioned select tag.  */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Collect room and description information from database */
    if (!($stmt = $mysqli->prepare("SELECT id, description FROM room;"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }                    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_id    = NULL;
    $out_description = NULL;
    if (!$stmt->bind_result($out_id, $out_description)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($stmt->fetch()) {
        $id = $out_id;
        $description = $out_description;
        
        /* Output option */
        printf("<option value=\"".$id."\">".$id." - ".$description."</option>");
    }
    
    /* Close database connection */
    $mysqli->close();
?>