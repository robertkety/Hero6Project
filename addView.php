<?php  
    /*  Programmed by Robert Kety,
        This PHP script receives a view ID and scriptName via POST and determines if
        it should UPDATE or INSERT a new view to the view table. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }

    /*  Receive variables from POST and protect database against special characters. */
    $vid = $_POST['vid'];
    $escapeString = (string) htmlspecialchars($_POST['scriptName']);
    $scriptName = "'".$escapeString."'";
    
    /* If adding a view, select the next highest integer for its view id */
    if($vid == ""){
        $addView = true;
        
        if(!($maxID = $mysqli->prepare("SELECT MAX(id) FROM view;"))){
            echo "Prepare failed: (" . $maxID->errno . ") " . $maxID->error;
        }
        if (!$maxID->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        
        /*  Bind parameters to variable, fetch, and rename variable */
        /*  Renaming isn't necessary, but I find it useful for my programming style -
            I like having the original variable available to use like a constant.  */
        $out_vid = NULL;
        if (!$maxID->bind_result($out_vid)) {
            echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        while($maxID->fetch()){
            $vid = $out_vid;
        }
        
        /* Increment variable */
        $vid = $vid + 1;
    }
    else{   //Not adding a view, simply updating
        $addView = false;
        $vid = (int) $vid;
    }

    /* Determine if the view id exists (UPDATE) or needs to be added (INSERT) */
    if(!($countViews = $mysqli->prepare("SELECT COUNT(view.id) FROM view WHERE view.id = $vid;"))){
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$countViews->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_viewCount = NULL;
    if (!$countViews->bind_result($out_viewCount)) {
        echo "Binding output parameters failed: (" . $countViews->errno . ") " . $countViews->error;
    }    
    while($countViews->fetch()){
        $viewCount = $out_viewCount;
    }
    
    /* UPDATE or INSERT based on view id existence in view table */
    if($viewCount == 1){    //View exists, then update with form information
        if($scriptName !== "''"){   //Empty strings will still have two single-quotes
            if(!$mysqli->query("UPDATE view SET scriptName = $scriptName WHERE id = $vid;")){
                echo "Update failed: (" . $mysqli->errno . ") " . $mysqli->error; 
            }
            
            /*  Output for submitComplete() user feedback */
            echo "View Updated";
        }
        else{   //Trivial case - this should not happen
            echo "No scriptName to update";
        }
    }    
    else{       //View does not exist, so create it with form information
        if($scriptName !== "''"){   //Empty strings will still have two single-quotes
            if(!$mysqli->query("INSERT INTO view (id, scriptName) VALUES ($vid, $scriptName);")){
                echo "Insert failed: (" . $mysqli->errno . ") " . $mysqli->error; 
            }
        }
        else{   //INSERT w/o including a scriptName
            if(!$mysqli->query("INSERT INTO view (id) VALUES ($vid);")){
                echo "Insert failed: (" . $mysqli->errno . ") " . $mysqli->error; 
            }
        }
        
        /*  Output for submitComplete() user feedback */
        echo "View Added";
    }
    
    /* Close database connection */
    $mysqli->close();
?>