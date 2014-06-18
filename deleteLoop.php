<?php  
    /*  Programmed by Robert Kety,
        This PHP script deletes the row related to the view and loop ID it receives via POST.  */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Import variable from POST */
    $vid = $_POST['vid'];
    $lid = $_POST['lid'];
    
    /* DELETE loop rows for view in viewLoopsFrame table */
    if(!$mysqli->query("DELETE FROM viewLoopsFrame WHERE vid = $vid AND lid = $lid;")){
        echo "loop DELETE failed: (" . $mysqli->errno . ") " . $mysqli->error; 
    }
    
    /* User feedback */
    printf("Loop Deleted");
    
    /* Close database connection */
    $mysqli->close();
?>