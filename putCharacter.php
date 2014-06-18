<?php  
    /*  Programmed by Robert Kety,
        This PHP script UPDATES or INSERTS to the characters table from the variables received
        via POST. The script also includes an algorithm which translates the rgb color variable
        to the closest rgb color available in the 32-bit color palette.  The regulated rgb
        variables are then used to determine the color ID to be stored in the characters table. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Import variables from POST */
    $cid = $_POST['cid'];
    $blinkVid = $_POST['blinkVid'];
    $idleVid = $_POST['idleVid'];
    $normalVid = $_POST['normalVid'];
    $speechColor = $_POST['speechColor'];
    $speechVid = $_POST['speechVid'];
    $thinkVid = $_POST['thinkVid'];
    $startingRid = $_POST['startingRid'];
    $startingX = $_POST['startingX'];
    $startingY = $_POST['startingY'];
    /* Protect against special characters for string variables */
    $escapeString = (string) htmlspecialchars($_POST['scriptName']);
    $scriptName = "'".$escapeString."'";
    $escapeString = (string) htmlspecialchars($_POST['realName']);
    $realName = "'".$escapeString."'";
    
    /* Compensate for empty strings in scriptName and realName */
    if($scriptName == "''"){
        /* This is regulated by defaults in the table, but is included here to simplify the PHP script */
        $scriptName = "'cChar'";    
    }
    if($realName == "''"){
        /* This is regulated by defaults in the table, but is included here to simplify the PHP script */
        $realName = "'New character'";
    }
    
    /* Convert speechColor to closest rgb value in color table */
    /* Isolate red, green, and blue values from speech color variable */
    $speechColor = substr($speechColor, 4);
    $colorArr = explode(")", $speechColor);
    $colorArr = explode(", ", $colorArr[0]);
    $red = $colorArr[0];
    $green = $colorArr[1];
    $blue = $colorArr[2];
    
    /* Round numerical values for compatibility with colors table*/
    if((($red % 8) > 0) && (($red % 8) < 4)){
        $red = $red - ($red % 8);
    }
    else if (($red % 8) >= 4){
        $red = $red + (8 - ($red % 8));
    }
    if((($green % 4) > 0) && (($green % 4) < 2)){
        $green = $green - ($green % 4);
    }
    else if (($green % 4) >= 2){
        $green = $green + (4 - ($green % 4));
    }
    if((($blue % 8) > 0) && (($blue % 8) < 4)){
        $blue = $blue - ($blue % 8);
    }
    else if (($blue % 8) >= 4){
        $blue = $blue + (8 - ($blue % 8));
    }
    
    /*  Handle rgb values for colors.id 0 through 31 (0-31 are reserved for 8-bit colors) 
        It's possible that these reserved 8-bit colors can expand to 255, but not without
        losing more 32-bit colors. */
    $colorSearch = true;
    if($red > 248){
        if($green > 248){
            if($blue > 248){
                $speechColor = 15;
                $colorSearch = false;
            }
            else if($blue == 88){   //88 instead of due to rounding algorithm
                $speechColor = 14;
                $colorSearch = false;
            }
        }
        else if($green == 84){  //Not affected by rounding algorithm
            if($blue > 248){
                $speechColor = 13;
                $colorSearch = false;
            }
            else if($blue == 88){   //88 instead of due to rounding algorithm
                $speechColor = 12;
                $colorSearch = false;
            }
        }
    }    
    else if($red == 88){   //88 instead of due to rounding algorithm
        if($green > 248){
            if($blue > 248){
                $speechColor = 11;
                $colorSearch = false;
            }
            else if($blue == 88){   //88 instead of due to rounding algorithm
                $speechColor = 10;
                $colorSearch = false;
            }
        }
        else if($green == 84){  //Not affected by rounding algorithm
            if($blue > 248){
                $speechColor = 9;
                $colorSearch = false;
            }
            else if($blue == 88){   //88 instead of due to rounding algorithm
                $speechColor = 8;
                $colorSearch = false;
            }
        }
    }
    
    /* Only search for color ID when not matched to colors 0 through 31 */
    if($colorSearch){
        /* Handle overflow as a result of rounding for colors.id 32 through 65535 */
        if($blue > 248){
            $blue = 0;
            $green = $green + 4;
        }
        if($green > 252){
            $green = 0;
            $red = $red + 8;
        }
        if($red > 248){
            $red = 248;
        }
        /* Handle underflow as a result of colors.id 0 through 31 being reserved for 8-bit colors */
        if(($red == 0) && ($green < 4)){
            $green = 4;
        }
    
        /* Locate speechColor from color table */
        if(!($colorSearch = $mysqli->prepare("SELECT id FROM color WHERE red = $red AND green = $green AND blue = $blue;"))){
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }                    
        if (!$colorSearch->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }

        /*  Bind parameters to variable, fetch, and rename variable */
        /*  Renaming isn't necessary, but I find it useful for my programming style -
            I like having the original variable available to use like a constant.  */
        $out_id    = NULL;
        if (!$colorSearch->bind_result($out_id)) {
            echo "Binding output parameters failed: (" . $colorSearch->errno . ") " . $colorSearch->error;
        }        
        while($colorSearch->fetch()) {
            $speechColor = $out_id;
        }
    }
    
    if($cid !== "addChar"){ //"addChar" is not a valid character ID.  This protects against bad queries
        /* UPDATE character */
        if(!$mysqli->query("UPDATE characters SET scriptName = $scriptName, blinkVid = $blinkVid, idleVid = $idleVid, ".
            "normalVid = $normalVid, speechColor = $speechColor, speechVid = $speechVid, thinkVid = $thinkVid, ".
            "realName = $realName, startingRid = $startingRid, startingX = $startingX, startingY = $startingY ".
            "WHERE id = $cid;")){
            echo "characters UPDATE failed: (" . $mysqli->errno . ") " . $mysqli->error; 
        }
    
        /* Output user feedback */
        echo "Character Updated";
    }
    else{   //"addChar" value in $cid requires us to INSERT instead of UPDATE
        /* INSERT character */
        if(!$mysqli->query("INSERT INTO characters (scriptName, blinkVid, idleVid, normalVid, speechColor, speechVid, ".
            "thinkVid, realName, startingRid, startingX, startingY) VALUES ($scriptName, $blinkVid, $idleVid, ".
            "$normalVid, $speechColor, $speechVid, $thinkVid, $realName, $startingRid, $startingX, $startingY);")){
            echo "characters INSERT failed: (" . $mysqli->errno . ") " . $mysqli->error; 
        }
        
        /* Output user feedback */
        echo "Character Added";
    }
    
    /* Close database connection */
    $mysqli->close();
?>