<?php 
    /*  Programmed by Robert Kety,
        This PHP script outputs the target sprite (received via sprite ID in POST) in a
        table format along with the label, "Frame Preview" */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Receive variables via POST */
    $sid = $_POST['sid'];
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Collect loops numbers and direction */
    if (!($stmt = $mysqli->prepare("SELECT path, fileName, extension FROM sprite WHERE ".
        "id = $sid;"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }                    
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    /*  Bind parameters to variable, fetch, and rename variable */
    /*  Renaming isn't necessary, but I find it useful for my programming style -
        I like having the original variable available to use like a constant.  */
    $out_path    = NULL;
    $out_fileName    = NULL;
    $out_extension    = NULL;    
    if (!$stmt->bind_result($out_path, $out_fileName, $out_extension)) {
        echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }    
    while($stmt->fetch()) {
        /* Output table */
        printf("<table><tr><td class=\"darkblue\">Frame Preview:</td><td></td></tr>".
            "<tr><td></td><td class=\"textcenter\"><img src=\"$out_path$out_fileName$out_extension\" alt=\"Sprite $out_fileName\" /></td></tr></table>");
    }
    
    /* Close database connection */
    $mysqli->close();
?>