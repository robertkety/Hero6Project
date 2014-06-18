<?php
    /*  Programmed by Robert Kety, but a huge thanks to Maria De Bruyn for her PHPASS tutorial
        This script creates a user account in the database based on details received via
        POST. */
    
    ini_set('display_errors', 'On');
    include 'pw.php';   //Externalized password to read-only file to protect database
    
    /* PHPASS - Hashing algorithm */
    require("PasswordHash.php");
    
    /* Create password hash instance */
    $hasher = new PasswordHash(8, false);
    
    /* Retrive login variables from POST */
    $password = (string) htmlspecialchars($_POST["password"]);
    $username = (string) htmlspecialchars($_POST["username"]);
    $rooms = $_POST["rooms"];
    $dialogs = $_POST["dialogs"];
    $chars = $_POST["chars"];
    $views = $_POST["views"];
    $edit = $_POST["edit"];
    
    /* Protection from denial-of-service attacks */
    if (strlen($password) > 72) {
        die("Password must be 72 characters or less");
    }
    
    /* Hash password */
    $hash = $hasher->HashPassword($password);
    
    /* Confirm proper hash length, connect to database, and add user to database */
    if(strlen($hash) >= 20){
        /* Connect to database */
        $mysqli = new mysqli("oniddb.cws.oregonstate.edu", "ketyr-db", $myPassword, "ketyr-db");
        if($mysqli->connect_errno){
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "<br/>";
        }
        else{   //Connection success, insert the log in variables into the database (use hashed password variable)
            if(!$mysqli->query("INSERT INTO users (username, passwordHash, rooms, views, characters, dialogs, editTables) ".
                " VALUES ('$username', '$hash', $rooms, $views, $chars, $dialogs, $edit);")){
                echo "INSERT failed: (" . $mysqli->errno . ") " . $mysqli->error; 
            }
            else{
                echo 1;
            }
        }
    } else {
        echo "Hash length error";
    }
    
    /* Close database connection */
    $mysqli->close();
?>