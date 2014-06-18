<?php  
    /*  Programmed by Robert Kety,
        This PHP script receives room ID, description, destination information for 8 directions,
        and a background image file via POST.  This script determines if the data should be used
        to update existing information or insert new instances.  The script manages modifications
        to the room table and its related tables (i.e., roomBackground and roomExitDirection). 
        Uploaded images must be regulated for size, MIME, etc. BEFORE sending to this script.
        I repeat, this script does not regulate the uploaded file - it simply moves it to the
        web server.  */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /*  Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }

    /*  Receive variables from POST and protect database against special characters. */
    $rid = $_POST['rid'];
    $escapeString = (string) htmlspecialchars($_POST['description']);
    $description = "'".$escapeString."'";
    $pathDestination = array("", $_POST['n'], $_POST['ne'], $_POST['e'], $_POST['se'], $_POST['s'], $_POST['sw'], $_POST['w'], $_POST['nw']);
    
    /* If adding a room, select the next highest integer for its room id */
    if($rid == "addRoom"){
        $addRoom = true;
        
        if(!($maxID = $mysqli->prepare("SELECT MAX(id) FROM room;"))){
            echo "Prepare failed: (" . $maxID->errno . ") " . $maxID->error;
        }
        if (!$maxID->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        
        /*  Bind parameters to variable, fetch, and rename variable */
        /*  Renaming isn't necessary, but I find it useful for my programming style -
            I like having the original variable available to use like a constant.  */
        $out_rid = NULL;
        if (!$maxID->bind_result($out_rid)) {
            echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        while($maxID->fetch()){
            $rid = $out_rid;
        }
        
        /*  Increment variable */
        $rid = $rid + 1;
    }
    else{
        $addRoom = false;
        $rid = (int) $rid;
    }

    /* Determine if the room id exists (UPDATE) or needs to be added (INSERT) */
    if(!($countRooms = $mysqli->prepare("SELECT COUNT(room.id) FROM room WHERE room.id = $rid;"))){
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$countRooms->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_roomCount = NULL;
    if (!$countRooms->bind_result($out_roomCount)) {
        echo "Binding output parameters failed: (" . $countRooms->errno . ") " . $countRooms->error;
    }
    while($countRooms->fetch()){
        $roomCount = $out_roomCount;
    }
    
    /* UPDATE or INSERT based on room id existence in room table */
    if($roomCount == 1){    //Room exists, then update with form information
        if($description !== "''"){  //Empty strings will contain two single quotes
            if(!$mysqli->query("UPDATE room SET description = $description WHERE id = $rid;")){
                echo "Update failed: (" . $mysqli->errno . ") " . $mysqli->error; 
            }
        }
        else{
            echo "No description to update"; //Error message for trivial case - this shouldn't be possible
        }
    }    
    else{       //Room does not exist, so create it with form information
        if(!$mysqli->query("INSERT INTO room (id, description) VALUES ($rid, $description);")){
            echo "Insert failed: (" . $mysqli->errno . ") " . $mysqli->error; 
        }        
    }
    
    /*  For each of the eight directions, determine if the roomExitDirection table must be
        UPDATEd or INSERTed with the destinationRoom data passed into the pathDestination array. */
    for($i = 1; $i < 9; $i++){        
        /*  Determine if roomExitDirection should be INSERTed or UPDATEd 
            UPDATE when count == 1 (row exists), otherwise INSERT */
        if(!($countExit = $mysqli->prepare("SELECT COUNT(edid) FROM roomExitDirection WHERE ".
            "originRid = $rid AND edid = $i;"))){
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        if (!$countExit->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        
        /*  Bind parameters to variable, fetch, and rename variable */
        /*  Renaming isn't necessary, but I find it useful for my programming style -
            I like having the original variable available to use like a constant.  */
        $out_exitCount = NULL;
        if (!$countExit->bind_result($out_exitCount)) {
            echo "Binding output parameters failed: (" . $countExit->errno . ") " . $countExit->error;
        }
        while($countExit->fetch()){
            $exitCount = $out_exitCount;
        }
        
        /* UPDATE or INSERT roomExitDirection based on exit direction count */
        if($exitCount == 1){    //Exit direction exists, then update with form information
            if($pathDestination[$i] !== "none"){
                if(!$mysqli->query("UPDATE roomExitDirection SET destinationRoom = $pathDestination[$i] WHERE ".
                    "originRid = $rid AND edid = $i;")){
                    echo "Update failed: (" . $mysqli->errno . ") " . $mysqli->error; 
                }
            }
            else{   //Remove row from roomExitDirection if destination no longer exists
                if(!$mysqli->query("DELETE FROM roomExitDirection WHERE ".
                    "originRid = $rid AND edid = $i;")){
                    echo "Update failed: (" . $mysqli->errno . ") " . $mysqli->error; 
                }
            }
        }    
        else{   //Exit direction does not exist, so create it with form information
            if($pathDestination[$i] !== "none"){
                if(!$mysqli->query("INSERT INTO roomExitDirection (originRid, edid, destinationRoom) VALUES ($rid, $i, $pathDestination[$i]);")){
                    echo "Insert failed: (" . $mysqli->errno . ") " . $mysqli->error; 
                }
            }
        }
    }
    $file = 'feedback.txt';
    $content = file_get_contents($file);
    if(!$addRoom){
    $content .= "bad\n";
    }else{
    $content .= "good\n";
    }
    file_put_contents($file, $content);
    
    /*  Receive background image file sent via POST, move it to the web server, and record
        reference information in the roomBackground table. */    
    if(isset($_FILES['background'])){
        /*  Explode variable of "sample.bmp" to two variables containing "sample" and "bmp" */
        $background = explode(".", $_FILES['background']['name']);
        $fileName = current($background);
        $extension = end($background);  
        
        /*  Remove existing files of the same name and similar type */
        if (file_exists("images/backgrounds/$rid.bmp")){
            unlink("images/backgrounds/$rid.bmp");
        }
        else if(file_exists("images/backgrounds/$rid.png")){
            unlink("images/backgrounds/$rid.png");
        }
        
        /*  Move new file to appropriate directory, rename to match naming format, and 
           change the file permissions. */
        move_uploaded_file($_FILES['background']["tmp_name"], "images/backgrounds/" . "$rid.$extension");
        chmod("images/backgrounds/$rid.$extension", 0755);
        
        $fileName = "$rid";     //Change to new file name
    }
    else{   //Rooms do not require an image upload.
        if($addRoom){   //Backgrounds for new rooms are set to a default image when no file is uploaded
            $fileName = "default";
            $extension = "bmp";
        }
        else{   //Backgrounds for existing rooms can only be changed by uploading a new image.
            $fileName = "";
            $extension = "";
        }
    }
    
    /*  Determine if roomBackground should be INSERTed or UPDATEd */
    if(!($countBackground = $mysqli->prepare("SELECT COUNT(rid) FROM roomBackground WHERE ".
        "rid = $rid AND bid = 1;"))){      //Only supporting single background in this version. For multiple backgrounds remove bid condition and add for-loop later
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$countBackground->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_backgroundCount = NULL;
    if (!$countBackground->bind_result($out_backgroundCount)) {
        echo "Binding output parameters failed: (" . $countBackground->errno . ") " . $countBackground->error;
    }    
    while($countBackground->fetch()){
        $backgroundCount = $out_backgroundCount;
    }
    
    /*  UPDATE or INSERT roomBackground based on background count */
    if($backgroundCount > 0){   //Room Background exists, then update with form information (File upload is separate)
        if($fileName !== ""){   //Only update roomBackground when new image is uploaded
            if(!$mysqli->query("UPDATE roomBackground SET fileName = '$fileName', extension = '.$extension' WHERE ".
                "rid = $rid AND bid = 1;")){    //Path is constant and should not be modified
                echo "Update failed: (" . $mysqli->errno . ") " . $mysqli->error; 
            }
        }
    }    
    else{   //Room Background does not exist, so create it with form information (File upload is separate)
        if($fileName !== ""){   //Trivial condition, added for reciprocity with UPDATE - this shouldn't happen
            if(!$mysqli->query("INSERT INTO roomBackground (rid, bid, fileName, extension) VALUES ($rid, 1, '$fileName', '.$extension');")){
                echo "Insert failed: (" . $mysqli->errno . ") " . $mysqli->error; 
            }
        }
    } 
    
    /*  Close database connection */
    $mysqli->close();
?>