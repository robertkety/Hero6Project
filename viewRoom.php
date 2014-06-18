<?php  
    /*  Programmed by Robert Kety,
        This PHP script outputs an HTML formatted image of the target room background based on room ID received
        via POST.  */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Receive variable via POST */
    $rid = $_POST['rid'];
    
    /* Collect file details and description for room background and room */
    if (!($stmt = $mysqli->prepare("SELECT id, description, path, fileName, extension FROM room INNER JOIN ".
        "roomBackground ON rid = room.id WHERE ".
        "rid = ".$rid.";"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }                    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_id    = NULL;
    $out_description = NULL;
    $out_path = NULL;
    $out_fileName = NULL;
    $out_extension = NULL;
    $idCount = 0;
    if (!$stmt->bind_result($out_id, $out_description, $out_path, $out_fileName, $out_extension)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($stmt->fetch()) {
        $id = $out_id;
        /* This is an additionl layer of protection.  INSERT and UPDATE scripts already protect against 
           special characters.  */
        $description = htmlspecialchars($out_description);  //Protect against special chars from database
        $path = htmlspecialchars($out_path);  //Protect against special chars from database
        $fileName = htmlspecialchars($out_fileName);   //Protect against special chars from database
        $extension = htmlspecialchars($out_extension);   //Protect against special chars from database
        
        /* Output image and description HTML code */
        echo "<img class=\"quickfade opaque\" id=\"viewRoom\" src=\"$path$fileName$extension\" alt=\"Room $id\"/><div class=\"quickfade opaque\" id=\"roomTitle\">Room $id - $description</div>";
        
        $idCount++; //Count rows to handle empty table output
    }
    
    /* Handle empty table output */
    if($idCount == 0){
        echo "Room not on file";
    }
    
    /* Close database connection */
    $mysqli->close();
?>