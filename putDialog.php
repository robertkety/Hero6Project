<?php  
    /*  Programmed by Robert Kety,
        This PHP script UPDATES or INSERTS to the dialog table from the variables received
        via POST. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Import variable from POST */
    $cid = $_POST['cid'];
    $did = $_POST['did'];
    /* Protect against special characters for string variables */
    $escapeString = (string) htmlspecialchars($_POST['scriptName']);
    $scriptName = "'".$escapeString."'";
    
    /* Compensate for empty strings in scriptName */
    if($scriptName == "''"){
        $scriptName = "'dDialog'";
    }
   
   if($did !== "addDialog"){    //"addDialog" is not a valid $did.  This protects against bad queries
        /* UPDATE dialog */
        if(!$mysqli->query("UPDATE dialog SET scriptName = $scriptName, cid = $cid WHERE id = $did;")){
            echo "dialog UPDATE failed: (" . $mysqli->errno . ") " . $mysqli->error; 
        }
        
        /* Output user feedback */
        echo "Dialog Updated";
    }
    else{   //"addDialog" requires us to INSERT instead of UPDATE the dialog table
        /* INSERT dialog */
        if($cid !== "NULL"){    //The user has supplied a character belong to this dialog
            if(!$mysqli->query("INSERT INTO dialog (scriptName, cid) VALUES ($scriptName, $cid);")){
                echo "dialog INSERT failed: (" . $mysqli->errno . ") " . $mysqli->error; 
            }
            
            /* Output user feedback */
        echo "Dialog Added";
        }
        else{   //Dialog is not assigned to a character (cid is NULL)
            if(!$mysqli->query("INSERT INTO dialog (scriptName) VALUES ($scriptName);")){
                echo "dialog INSERT failed: (" . $mysqli->errno . ") " . $mysqli->error; 
            }
            
        /* Output user feedback */
        echo "Dialog Added";
        }
    }
    
    /* Close database connection */
    $mysqli->close();
?>