<?php 
    /*  Programmed by Robert Kety,
        This PHP script destroys the verified session and redirects to the login page so a user can initiate a new session */
        
    ini_set('display_errors', 'On');
    session_start(); 
    session_destroy();  //Destroys session created by session_start
    setcookie("PHPSESSID","",time()-3600,"/"); //Delete session cookie (From: http://www.webdeveloper.com/forum/showthread.php?172149-How-to-remove-PHPSESSID-cookie)
    header("Location: login.php");   //Redirect to login page
?>