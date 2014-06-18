<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['rooms']) &&
        ($_SESSION['views']) && ($_SESSION['characters']) && ($_SESSION['dialogs']) && ($_SESSION['editTables'])){
        $username = $_SESSION['username'];
        $rooms = $_SESSION['rooms'];
        $views = $_SESSION['views'];
        $chars = $_SESSION['characters'];
        $dialogs = $_SESSION['dialogs'];
        $editTables = $_SESSION['editTables'];
    }
    else{
        session_destroy();  //Destroys session created by session_start
        setcookie("PHPSESSID","",time()-3600,"/"); //Delete session cookie (From: http://www.webdeveloper.com/forum/showthread.php?172149-How-to-remove-PHPSESSID-cookie)
        header("Location: login.php");        
    }
    
    /*  Programmed by Robert Kety,
        This PHP script resets the database to the tables and rows recorded in the Hero6 SQL file
        stored on the web server. */
    
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* Connect to database */
    $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
    }
    
    /* Thanks to http://scriptcult.com/subcategory_4/article_558-execute-mysql-sql-dump-files-via-php-mysqli.htm */
    $sql = file_get_contents('Hero6.sql');
    
    if (!$sql){
        die ('Error opening file');
    }
    
    mysqli_multi_query($mysqli, $sql);
    
    /* Output user feedback */
    printf("Database reset to original content");
    
    /* Close database connection */
    $mysqli->close();
?>