<?php  
    /*  Programmed by Robert Kety,
        This PHP script is designed to receive a .bmp or .png image file via POST along
        with two variables referring to the view ID and loop ID of an existing loop in
        the viewLoopsFrame table.  This script will effect the addition or replacement
        of the new file and insert the appropriate reference information into the sprite
        and viewLoopsFrame tables. Uploaded images must be regulated for size, MIME, etc. 
        BEFORE sending to this script.  I repeat, this script does not regulate the 
        uploaded file - it simply moves it to the web server. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /*  Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }

    /*  Receive variables from POST */
    $vid = $_POST['vid'];
    $lid = $_POST['lid'];
    
    /*  Select the next highest integer for the new frame number (viewLoopsFrame table) */
    if(!($maxFrame = $mysqli->prepare("SELECT MAX(frameNum) FROM viewLoopsFrame WHERE vid = $vid AND lid = $lid;"))){
        echo "Prepare failed: (" . $maxFrame->errno . ") " . $maxFrame->error;
    }
    if (!$maxFrame->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_frameNum = NULL;
    if (!$maxFrame->bind_result($out_frameNum)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($maxFrame->fetch()){
        $frameNum = $out_frameNum;
    }
    
    /*  Increment frameNum for new insert to viewLoopsFrame table */
    $frameNum = $frameNum + 1;
    
    /*  Select the next highest integer for the new sprite (sprite table) */
    if(!($maxID = $mysqli->prepare("SELECT MAX(id) FROM sprite;"))){
        echo "Prepare failed: (" . $maxID->errno . ") " . $maxID->error;
    }
    if (!$maxID->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_sid = NULL;
    if (!$maxID->bind_result($out_sid)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($maxID->fetch()){
        $sid = $out_sid;
    }
    
    /*  Increment sid for new insert to sprite table */
    $sid = $sid + 1;
    
    /*  Upload sprite to web server and record file information in sprite table */
    if(isset($_FILES['sprite'])){
        /*  Explode $sprite of "sample.bmp" to variables containing "sample" and "bmp" */
        $sprite = explode(".", $_FILES['sprite']['name']);
        $fileName = current($sprite);
        $extension = end($sprite);  
        
        /*  Remove existing files of the same name and similar type */
        if (file_exists("images/sprites/$sid.bmp")){
            unlink("images/sprites/$sid.bmp");
        }
        else if(file_exists("images/sprites/$sid.png")){
            unlink("images/sprites/$sid.png");
        }
        
        /*  Move new file to appropriate directory, rename to match naming format, and 
           change the file permissions. */
        move_uploaded_file($_FILES['sprite']["tmp_name"], "images/sprites/" . "$sid.$extension");
        chmod("images/sprites/$sid.$extension", 0755);
        
        /*  Record file information in sprite table */
        if(!$mysqli->query("INSERT INTO sprite (id, fileName, extension) VALUES ($sid, '$sid', '.$extension');")){
            echo "sprite Insert failed: (" . $mysqli->errno . ") " . $mysqli->error; 
        }
    }
    else{
        /*  Error message for upload failure - Sprite file is required to create new Frame */
        echo "File not received via POST - Upload Failed";
    }
    
    /*  INSERT row of new frame information to viewLoopsFrame table */
    if(!$mysqli->query("INSERT INTO viewLoopsFrame (vid, lid, frameNum, sid) VALUES ($vid, $lid, $frameNum, $sid)")){
        echo "viewLoopsFrame Insert failed: (" . $mysqli->errno . ") " . $mysqli->error; 
    }
    
    /*  Output for submitComplete() user feedback */
    echo "Frame Added";
    
    /*  Close database connection */
    $mysqli->close();
?>