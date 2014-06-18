<?php 
    /*  Programmed by Robert Kety, but a huge thanks to Maria De Bruyn for her PHPASS tutorial
        This PHP script verifies login information received via POST against stored login information in the database. */
    ini_set('display_errors', 'On');
    session_start(); 
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    if (isset($_SESSION['sessionExists'])){
        /* PHPASS - Hashing algorithm */
        require("PasswordHash.php");
            
        
        /* Create password hash instance */
        $hasher = new PasswordHash(8, false);
        
        /* Retrive login variables from POST */
        $password = (string) htmlspecialchars($_POST['password']);
        $username = (string) htmlspecialchars($_POST['username']);
        
        /* Protection from denial-of-service attacks */
        if (strlen($password) > 72) {
            die("Password must be 72 characters or less");
        }
    
        /* Connect to database */
        $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
        if($mysqli->connect_errno){
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
        }
        else{   //Connection success, confirm valid login information
            /* Verify user exists, retrieve password hash, and collect permissions */
            if (!($stmt = $mysqli->prepare("SELECT COUNT(id), passwordHash, rooms, views, characters, dialogs, editTables FROM ".
                "users WHERE username = '$username';"))) {
                echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
            }                    
            if (!$stmt->execute()) {
                echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
            }

            /*  Bind parameters to variable, fetch, and rename variable */
            /*  Renaming isn't necessary, but I find it useful for my programming style -
                I like having the original variable available to use like a constant.  */
            $out_count = NULL;
            $out_passwordHash = NULL;
            $out_rooms = NULL;
            $out_views = NULL;
            $out_characters = NULL;
            $out_dialogs = NULL;
            $out_editTables = NULL;
            if (!$stmt->bind_result($out_count, $out_passwordHash, $out_rooms, $out_views, $out_characters, $out_dialogs, $out_editTables)) {
                echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
            }    
            /* Fetch rows from table */
            while($stmt->fetch()) {
                $count = $out_count;
                $passwordHash = $out_passwordHash;
                $rooms = $out_rooms;
                $views = $out_views;
                $characters = $out_characters;
                $dialogs = $out_dialogs;
                $editTables = $out_editTables;                
            }
            
            /* Verify password received via POST against hash stored in database */
            $passVerified = $hasher->CheckPassword($password, $passwordHash);
            
            if(($count > 0) && ($passVerified)){
                $_SESSION['username'] = $username;  //Used for "Welcome back..." message
                $_SESSION['passVerified'] = true;   //Used in session verification
                /* Permissions attributes */
                $_SESSION['rooms'] = $rooms;
                $_SESSION['views'] = $views;
                $_SESSION['characters'] = $characters;
                $_SESSION['dialogs'] = $dialogs;
                $_SESSION['editTables'] = $editTables;
            
                echo 1; //TRUE!
            }
            else{
                echo 0; //FALSE!
            }
        }
        
        /* Close database connection */
        $mysqli->close();
    }
    else{
        session_destroy();  //Destroys session created by session_start
        setcookie("PHPSESSID","",time()-3600,"/"); //Delete session cookie (From: http://www.webdeveloper.com/forum/showthread.php?172149-How-to-remove-PHPSESSID-cookie)
        echo 0; //FALSE!
    }
?>