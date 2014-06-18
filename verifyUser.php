<?php
    /*  Programmed by Robert Kety, but a huge thanks to Maria De Bruyn for her PHPASS tutorial
        This script tests the validity of a username against existing database content. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Receive variable from POST */
    $username = $_POST['username'];
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if($mysqli->connect_errno){
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    else{   //Connection success, insert the log in variables into the database (use hashed password variable)
        /* Determine if user with the same username already exists */
        if(!($count = $mysqli->prepare("SELECT COUNT(id) FROM users WHERE username = '$username';"))){
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        if (!$count->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        
        /*  Bind parameters to variable, fetch, and rename variable */
        /*  Renaming isn't necessary, but I find it useful for my programming style -
            I like having the original variable available to use like a constant.  */
        $out_Count = NULL;
        if (!$count->bind_result($out_Count)) {
            echo "Binding output parameters failed: (" . $count->errno . ") " . $count->error;
        }    
        while($count->fetch()){
            $isValid = !($out_Count);
        }
        
        echo $isValid;
    }
    
    /* Close database connection */
    $mysqli->close();
?>