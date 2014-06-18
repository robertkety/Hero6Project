<?php  
    /*  Programmed by Robert Kety,
        This PHP script deletes the row related to the dialog ID it receives via POST.  */
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Import variable from POST */
    $did = $_POST['did'];
    
    /* DELETE row for dialog in dialog table */
    if(!$mysqli->query("DELETE FROM dialog WHERE id = $did;")){
        echo "dialog DELETE failed: (" . $mysqli->errno . ") " . $mysqli->error; 
    }
    
    /* User feedback */
    printf("Dialog Deleted");
    
    /* Close database connection */
    $mysqli->close();
?>