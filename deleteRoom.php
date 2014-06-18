<?php  
    /*  Programmed by Robert Kety,
        This PHP script deletes the row related to the room ID it receives via POST.  */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Import variable from POST */
    $rid = $_POST['rid'];
    
    /* DELETE rows for room ID - FK's are set to cascade or set null where appropriate */
    if(!$mysqli->query("DELETE FROM room WHERE id = $rid;")){
        echo "room DELETE failed: (" . $mysqli->errno . ") " . $mysqli->error; 
    }
    
    /* User feedback */
    printf("Room Deleted");
    
    /* Close database connection */
    $mysqli->close();
?>