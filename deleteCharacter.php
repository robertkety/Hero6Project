<?php  
    /*  Programmed by Robert Kety,
        This PHP script deletes the row for the character ID passed via POST.  */
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Import variable from POST */
    $cid = $_POST['cid'];
    
    /* DELETE row for character in characters table (dialog FK cascades to NULL) */
    if(!$mysqli->query("DELETE FROM characters WHERE id = $cid;")){
        echo "characters DELETE failed: (" . $mysqli->errno . ") " . $mysqli->error; 
    }
    
    /* User feedback */
    printf("Character Deleted");
    
    /* Close database connection */
    $mysqli->close();
?>