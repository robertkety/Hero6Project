<?php 
    /* Verify session, password, and privilege before loading */
    ini_set('display_errors', 'On');
    session_start(); 
    
    if ((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified'])) && ($_SESSION['editTables'])){
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
?>
<!-- The HTML for this page is designed as a secondary navigation bar controlled by buttons.
     The first button is a master reset which returns the database to its original content. -->
<div id="buttons">
    <?php
        if($rooms && $views && $chars && $dialogs){
            echo "Restore the Hero6 Database to its original content <br/>\n";
            echo "(<u>ALL UPDATES WILL BE LOST</u>): <input type=\"Submit\" value=\"Restore Database\" id=\"reset\" />\n";
            echo "<br/>\n";
            echo "<br/>\n";
        }
        if($rooms){
            echo "<input type=\"Submit\" value=\"Add/Edit Rooms\" id=\"rooms\"/>\n";
        }
        if($views){
            echo "<input type=\"Submit\" value=\"Add/Edit Views\" id=\"views\"/>\n"; 
        }
        if($chars){
            echo "<input type=\"Submit\" value=\"Add/Edit Characters\" id=\"characters\"/>\n";
        }
        if($dialogs){
            echo "<input type=\"Submit\" value=\"Add/Edit Dialogs\" id=\"dialogs\"/>\n";
        }
    ?>
    <hr/>
    <br/>
    <div id="editInterface"></div>    
</div>
<script>
    /*  Ready functions controlling the click events for each button and disabled/enabled
        state for each button based on button selected.  */
    $(function(){
        /* Loads script that resets database to original content */
        $("#reset").click(function(){
            <?php
                if($rooms && $views && $chars && $dialogs){
                    echo "$(\"#editInterface\").empty().load(\"reset.php\");\n";
                }
            ?>
            enableAllButtons();
        });
        
        /* Loads script for editing room data */
        $("#rooms").click(function(){
            <?php
                if($rooms){
                    echo "$(\"#editInterface\").empty().load(\"roomInterface.php\");\n";
                }
            ?>
            enableAllButtons();
            $("#rooms").attr('disabled', 'disabled');
        });
        
        /* Loads script for editing view data */
        $("#views").click(function(){
            <?php
                if($views){
                    echo "$(\"#editInterface\").empty().load(\"viewInterface.php\");\n";
                }
            ?>
            enableAllButtons();
            $("#views").attr('disabled', 'disabled');
        });
        
        /* Loads script for editing character data */
        $("#characters").click(function(){
            <?php
                if($chars){
                    echo "$(\"#editInterface\").empty().load(\"charInterface.php\");\n";
                }
            ?>
            enableAllButtons();
            $("#characters").attr('disabled', 'disabled');
        });
        
        /* Loads script for editing dialog data */
        $("#dialogs").click(function(){
            <?php
                if($dialogs){
                    echo "$(\"#editInterface\").empty().load(\"dialogInterface.php\");\n";
                }
            ?>
            enableAllButtons();
            $("#dialogs").attr('disabled', 'disabled');
        });
    });    
    
    /* This function enables every button in the navigation bar */
    function enableAllButtons(){
        $("#rooms").removeAttr('disabled');
        $("#views").removeAttr('disabled');
        $("#characters").removeAttr('disabled');
        $("#dialogs").removeAttr('disabled');
    }
</script>