<?php  
    /*  Programmed by Robert Kety,
        This PHP script retrieves the row data in the characters table for the character 
        ID received via POST. This also includes fetching color data from the colors 
        table and formatting it to hexadecimal format (i.e., #000000). */
        
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /*  Receive variables from POST */
    $cid = $_POST['cid'];
    
    /* Fetch character details from characters and color tables */
    if (!($stmt = $mysqli->prepare("SELECT scriptName, realName, red, green, blue, blinkVid, idleVid, ".
        "normalVid, speechVid, thinkVid, startingRid, startingX, startingY FROM characters INNER JOIN ".
        "color ON color.id = speechColor WHERE characters.id = $cid;"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }                    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_scriptName = NULL;
    $out_realName = NULL;
    $out_red = NULL;
    $out_green = NULL;
    $out_blue = NULL;
    $out_blinkVid = NULL;
    $out_idleVid = NULL;
    $out_normalVid = NULL;
    $out_speechVid = NULL;
    $out_thinkVid = NULL;
    $out_startingRid = NULL;
    $out_startingX = NULL;
    $out_startingY = NULL;
    if (!$stmt->bind_result($out_scriptName, $out_realName, $out_red, $out_green, $out_blue, $out_blinkVid, $out_idleVid, $out_normalVid, $out_speechVid, $out_thinkVid, $out_startingRid, $out_startingX, $out_startingY)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($stmt->fetch()) {
        $scriptName = $out_scriptName;
        $realName = $out_realName;
        $blinkVid = $out_blinkVid;
        $idleVid = $out_idleVid;
        $normalVid = $out_normalVid;
        $speechVid = $out_speechVid;
        $thinkVid = $out_thinkVid; 
        $startingRid = $out_startingRid;
        $startingX = $out_startingX;
        $startingY = $out_startingY;
        $speechColor = "#"; //Initializes string with '#'
        
        /* Convert rgb to hexadecimal format */
        if($out_red < 16){
            $speechColor .= "0";
            $speechColor .= (string) dechex($out_red);
        }
        else{
            $speechColor .= (string) dechex($out_red);
        }
        
        if($out_green < 16){
            $speechColor .= "0";
            $speechColor .= (string) dechex($out_green);
        }
        else{
            $speechColor .= (string) dechex($out_green);
        }
        
        if($out_blue < 16){
            $speechColor .= "0";
            $speechColor .= (string) dechex($out_blue);
        }
        else{
            $speechColor .= (string) dechex($out_blue);
        }        
    }

    /* Returns row data in comma delimited format */
    echo $scriptName;
    echo ",";
    echo $realName;
    echo ",";
    echo $speechColor;
    echo ",";
    echo $blinkVid;
    echo ",";
    echo $idleVid;
    echo ",";
    echo $normalVid;
    echo ",";
    echo $speechVid;
    echo ",";
    echo $thinkVid;
    echo ",";
    echo $startingRid;
    echo ",";
    echo $startingX;
    echo ",";
    echo $startingY;
    
    /* Close database connection */
    $mysqli->close();
?>