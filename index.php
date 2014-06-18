<?php
    ini_set('display_errors', 'On');
    session_start();
    if((isset($_SESSION['sessionExists'])) && (isset($_SESSION['passVerified']))){
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
        //header("Location: login.php");    //Suzanne Thrasher experienced redirect issues possibly related to this
        redirect("login.php");              //Alternative redirect, attempting to correct error from Suzanne Thrasher
    }
    
    /* Thanks http://stackoverflow.com/questions/8689471/alternative-to-header-for-re-directs-in-php */
    function redirect($url){
        if (!headers_sent())
        {    
            header('Location: '.$url);
            exit;
            }
        else
            {  
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$url.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
            echo '</noscript>';
            exit;
        }
    }
?>
<!DOCTYPE HTML>
<HTML>
    <!-- Programmed by Robert Kety,
         This PHP file is mainly HTML code.  It's purpose is to create the navigation bar and container in
         which all child code will be displayed. -->
    <head>
        <meta charset="utf-8"/>
        <title>CS275 and CS494 - Final Projects</title>
        <!-- This website uses jQuery! -->
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <!-- This website uses the jQuery plugin, ColorPicker! http://www.eyecon.ro/colorpicker/ -->
        <script type="text/javascript" src="js/colorpicker.js"></script>
        <!-- Custom style sheets for ColorPicker -->
        <link rel="stylesheet" media="screen" type="text/css" href="css/colorpicker.css" />
        <link rel="stylesheet" media="screen" type="text/css" href="css/layout.css">
        <!-- Custom style sheet by Robert Kety for this website -->
        <link id="cssLink" rel="stylesheet" type="text/css" href="css/project.css">
        <script>
            /* Script handling for navigation bar links */
            $(document).ready(function() {
                $("#viewRooms").click(function(){
                    <?php
                        if($rooms){
                            echo "clearNavTabs();";
                            echo "$(\"#viewRooms\").addClass(\"active\");";                    
                            echo "$(\"#content\").empty().load(\"rooms.php\");";                            
                        }
                        else{
                            echo "$(\"#viewRooms\").empty();";
                        }
                    ?>
                });
                $("#viewViews").click(function(){
                    <?php
                        if($views){
                            echo "clearNavTabs();";
                            echo "$(\"#viewViews\").addClass(\"active\");";                    
                            echo "$(\"#content\").empty().load(\"views.php\");";                            
                        }
                        else{
                            echo "$(\"#viewViews\").empty();";
                        }
                    ?>
                });
                $("#viewCharacters").click(function(){
                    <?php
                        if($chars){
                            echo "clearNavTabs();";
                            echo "$(\"#viewCharacters\").addClass(\"active\");";                    
                            echo "$(\"#content\").empty().load(\"characters.php\");";                            
                        }
                        else{
                            echo "$(\"#viewCharacters\").empty();";
                        }
                    ?>
                });
                $("#viewDialogs").click(function(){
                    <?php
                        if($dialogs){
                            echo "clearNavTabs();";
                            echo "$(\"#viewDialogs\").addClass(\"active\");";                    
                            echo "$(\"#content\").empty().load(\"dialogs.php\");";                            
                        }
                        else{
                            echo "$(\"#viewDialogs\").empty();";
                        }
                    ?>
                });
                $("#editTables").click(function(){
                    <?php
                        if($editTables){
                            echo "clearNavTabs();";
                            echo "$(\"#editTables\").addClass(\"active\");";                    
                            echo "$(\"#content\").empty().load(\"editTables.php\");";                            
                        }
                        else{
                            echo "$(\"#editTables\").empty();";
                        }
                    ?>
                });
                $("#subheading").click(function(){
                    window.open('mailto:ketyr@onid.oregonstate.edu', '_blank');
                });
                
                $("#logout").click(function(){
                    window.location.href='logout.php';
                });
                
                /* Reset 'active' class for all navigation bar options (removes highlight) */
                function clearNavTabs(){
                    $("#editTables").removeClass("active");
                    $("#viewRooms").removeClass("active");
                    $("#viewViews").removeClass("active");
                    $("#viewCharacters").removeClass("active");
                    $("#viewDialogs").removeClass("active");
                };
            });
        </script>
    </head>
    <body>
        <!-- Navigation Bar -->
        <div id="nav">
            <table>
                <tr>
                    <td colspan=5>
                        <div id="heading">Hero6 Progress</div>
                    </td>
                    <td colspan=2 class="darkblue columnWidth textright">
                        Welcome back, <?php echo $username; ?>!
                    </td>
                </tr>
                <tr>
                    <td class="singleWidth">
                        <span id="subheading">by Robert Kety</span>
                    </td>
                    <td class="singleWidth textcenter">
                        <?php 
                            if($rooms){ 
                                echo "<span id=\"viewRooms\">Rooms</span>"; 
                            }
                        ?>
                    </td>
                    <td class="singleWidth textcenter">
                        <?php 
                            if($views){
                                echo "<span id=\"viewViews\">Views</span>";
                            }
                        ?>
                    </td>
                    <td class="singleWidth textcenter">
                        <?php 
                            if($chars){
                                echo "<span id=\"viewCharacters\">Characters</span>";
                            }
                        ?>
                    </td>
                    <td class="singleWidth textcenter">
                        <?php 
                            if($dialogs){
                                echo "<span id=\"viewDialogs\">Dialogs</span>";
                            }
                        ?>
                    </td>
                    <td class="singleWidth textcenter">
                        <?php 
                            if($editTables){
                                echo "<span id=\"editTables\">Edit Tables</span>";
                            }
                        ?>
                    </td>
                    <td class="textright doubleWidth">
                        <span id="logout">Logout</span>
                    </td>  
                </tr>
            </table>
        </div>
        
        <!-- Destination of PHP page content -->
        <div id="content"><div class="textcenter">Please select from the above content choices</div></div>
    </body>
</HTML>