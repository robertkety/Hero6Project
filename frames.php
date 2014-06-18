<?php  
    /*  Programmed by Robert Kety,
        This PHP script displays sprites associated with the view and loop ID received via 
        GET.  The loops, frames, and charViews PHP pages are my only use of GET for this site.  
        It is included to demonstrate capability for this type of call. 
        This script will return all sprites in a loop when allFrames is true or a single
        sprite when allFrames is false. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /*  Receive variables from GET */
    $viewID = $_GET['viewID'];
    $loopID = $_GET['loopID'];
    $allFrames = $_GET['allFrames'];
    
    /*  Collect file details and display information (i.e., flipped) for sprite based on
        view ID, loop ID, and frame number */
    if (!($stmt = $mysqli->prepare("SELECT flipped, path, filename, extension FROM view INNER JOIN ".
        "viewLoopsFrame ON view.id = vid INNER JOIN ".
        "loops ON loops.id = lid INNER JOIN ".
        "sprite ON sprite.id = sid WHERE ".
        "view.id = $viewID AND loops.id = $loopID GROUP BY ".
        "vid, lid, frameNum;"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }                    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    /* Thanks to: http://foundationphp.com/blog/2011/09/24/using-num_rows-with-a-mysqli-prepared-statement/ */
    $stmt->store_result();
    
    /* Variable counter used in regulating the number of sprites displayed in each row. */
    $idCount = 0;
    $rowCount = $stmt->num_rows;    //Number of rows in query
    $rows = 0;                      //Number of iterations in row output (counter)
    
    /* Start a table for images */
    printf("<table><tr>");
    
    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_flipped   = NULL;
    $out_path      = NULL;
    $out_fileName  = NULL;
    $out_extension = NULL;
    $firstFrame = true;
    if (!$stmt->bind_result($out_flipped, $out_path, $out_fileName, $out_extension)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($stmt->fetch()) {
        /*  This is an additional layer of protection.  UPDATE and INSERT scripts already
            include protection against special characters. */
        $path      = htmlspecialchars($out_path);
        $fileName  = htmlspecialchars($out_fileName);
        $extension = htmlspecialchars($out_extension);
        
        /*  To save space in the master sprite file (of the game, not the database), 
            frames may be set to "flipped".  This allows the use of an existing 
            sprites flipped on its vertical axis. This flipped class is controlled
            via CSS to display the sprite in a similar fashion as the game engine. */
        if($out_flipped){
            $flipped = "class=\"flipped\"";
        }
        else{
            $flipped = "";
        }
        
        /* Only 6 sprites per row */
        if(($allFrames) && ($idCount % 6 == 0) && ($idCount !== 0)){
            printf("</tr><tr>");
            $rows++;
        }
        
        if($allFrames){ //Display all frames in loop
            printf("<td");
            /* Compensate for number of columns in final row */
            if(($idCount == ($rowCount - 1)) && ($rows > 0)){
                $colspan = 6 - ($idCount % 6);
                if($colspan != 0){
                    printf(" colspan = $colspan");
                }
            }
            printf("><img $flipped src=\"$path$fileName$extension\" alt=\"Sprite $fileName\"/></td>");
        }
        else if ($firstFrame){  //Display first frame of loop
            printf("<td><img %s src=\"%s%s%s\" alt=\"Sprite $fileName\"/></td>", $flipped, $path, $fileName, $extension);
            $firstFrame = false;
        }
        
        $idCount++; //Count rows to assist in handling empty table output
    }
    
    /* End the table */
    printf("</tr></table>");
    
    
    /* Handle empty table output */
    if($idCount == 0){
        printf("<td colspan = 3>Empty Loop</td>");
    }
    
    /*  Close database connection */
    $mysqli->close();
?>