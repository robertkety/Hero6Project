<?php  
    /*  Programmed by Robert Kety,
        This PHP script deletes the row related to the view ID it receives via POST.  */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Import variable from POST */
    $vid = $_POST['vid'];
    
    /* DELETE rows for view ID - FK's are set to cascade or set null where appropriate */    
    if(!$mysqli->query("DELETE FROM view WHERE id = $vid;")){
        echo "view DELETE failed: (" . $mysqli->errno . ") " . $mysqli->error; 
    }
    
    /* User feedback */
    printf("View Deleted");
    
    /* Close database connection */
    $mysqli->close();
?>