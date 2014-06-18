<?php 
    /*  Programmed by Robert Kety,
        This PHP script outputs HTML code for each loop ID and direction for each loop
        referenced by the view ID received via POST.
        This script is intended to be loaded inside a select element.  Output will be 
        option tags for previously mentioned select tag.  */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Receive variables via POST */
    $vid = $_POST['vid'];
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Collect each loop number and direction for the target view ID */
    if (!($stmt = $mysqli->prepare("SELECT lid, direction FROM loops INNER JOIN ".
        "viewLoopsFrame ON lid=loops.id WHERE ".
        "vid = $vid GROUP BY lid;"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }                    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_lid    = NULL;
    $out_direction = NULL;    
    if (!$stmt->bind_result($out_lid, $out_direction)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    while($stmt->fetch()) {
        $lid = $out_lid;
        $direction = $out_direction;
        
        /* Output option */
        printf("<option value=\"".$lid."\">".$lid." - ".$direction."</option>");
    }
    
    /* Close database connection */
    $mysqli->close();
?>