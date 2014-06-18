<?php  
    /*  Programmed by Robert Kety,
        This PHP script retrieves the room ID for the destination of each exit direction from an origin
        room ID (i.e., the destinationRoom variable for each room ID and direction ID).  It requires
        an origin room ID and a direction variable (string) sent via POST. Output is either an integer
        representation for that room ID or the string: "none". */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Receive variables via POST */
    $origin = $_POST['origin'];
    $direction = $_POST['direction'];
    
    /* No easy enums in PHP. Used a switch case instead. */
    switch($direction){
        case 'n':
            $direction = 1;
            break;
        case 'ne':
            $direction = 2;
            break;
        case 'e':
            $direction = 3;
            break;
        case 'se':
            $direction = 4;
            break;
        case 's':
            $direction = 5;
            break;
        case 'sw':
            $direction = 6;
            break;
        case 'w':
            $direction = 7;
            break;        
        case 'nw':
            $direction = 8;
            break;
        default:
            $direction = 0;
            break;
    }
    
    if($origin !== "addRoom"){  //"addRoom" is not a valid origin room ID.  This is to protect from bad queries
        /* Collect destination room ID from roomExitDirection table */
        if (!($stmt = $mysqli->prepare("SELECT destinationRoom FROM roomExitDirection WHERE ".
            "originRid = $origin AND edid = $direction"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }                    
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }

        /*  Bind parameters to variable, fetch, and rename variable */
        /*  Renaming isn't necessary, but I find it useful for my programming style -
            I like having the original variable available to use like a constant.  */
        $out_destination = NULL;
        if (!$stmt->bind_result($out_destination)) {
            echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }        
        while($stmt->fetch()) {
            $destination = $out_destination;
        }

        /* NULL results should be saved as "none" */
        if($out_destination == ""){
            $destination = "none";
        }
    }
    else{   //Rooms that have not been added cannot have a destination room
        $destination = "none";
    }
    
    /* Output destination room ID */
    echo $destination;
    
    /* Close database connection */
    $mysqli->close();
?>