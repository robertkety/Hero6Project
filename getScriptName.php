<?php  
    /*  Programmed by Robert Kety,
        This PHP script retrieves the script name for the view ID received via POST. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Receive variable via POST */
    $viewID = $_POST['vid'];
    
    if($viewID !== "addView"){  //"addView" is not a valid view ID.  This is to protect from bad queries
        /* Collect script name from view table */
        if (!($stmt = $mysqli->prepare("SELECT scriptName FROM view WHERE id = $viewID"))){
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }                    
        if (!$stmt->execute()){
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }

        /*  Bind parameters to variable, fetch, and rename variable */
        /*  Renaming isn't necessary, but I find it useful for my programming style -
            I like having the original variable available to use like a constant.  */
        $out_scriptName = NULL;
        if (!$stmt->bind_result($out_scriptName)) {
            echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }        
        while($stmt->fetch()){
            $scriptName = $out_scriptName;
        }
    }
    else{   //If a view has not been added, it cannot have a script name.
        $scriptName = "";
    }
    
    /* Output script name for the target view */
    echo $scriptName;
    
    /* Close database connection */
    $mysqli->close();
?>