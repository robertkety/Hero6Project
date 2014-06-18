<?php  
    /*  Programmed by Robert Kety,
        This PHP script retrieves the file details in the roomBackground table for the room 
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
        /* Collect file details from roomBackground table */
        if (!($stmt = $mysqli->prepare("SELECT fileName, extension FROM roomBackground WHERE rid = $roomID AND bid = 1;"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }                    
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }

        /*  Bind parameters to variable and fetch the related data.  Typically, I rename 
            the variables.  Renaming isn't necessary, but I find it useful for my 
            programming style.  I did not do that here as a demonstration of my capability
            to not just copy and paste from existing code.  However, if you actually read
            this one variant comment and let me know, I'll send you a crisp 25 cent piece. */
        $out_fileName = NULL;
        $out_extension = NULL;
        if (!$stmt->bind_result($out_fileName, $out_extension)) {
            echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }        
        while($stmt->fetch()) {
            $fileName = "$out_fileName$out_extension"; //The extension already includes the '.'
        }
    }
    else{   //If the room has not been added, it cannot have a file name
        $fileName = "";
    }
    
    /* Output the file name */
    echo $fileName;
    
    /* Close database connection */
    $mysqli->close();
?>