<?php 
    /*  Programmed by Robert Kety,
        This HTML and PHP combination script initiates a session and requests form validated login information. 
        Valid login information is forwarded to a verification script whose response determines if the browser
        forwards to the website index page or resets while displaying an invalid login message.  The user also
        has an option to register an account. 
        Depending on extra time, this site may also include a link to a demo adventure game via jDOSbox */
    ini_set('display_errors', 'On');
    session_start();
    $_SESSION['sessionExists'] = true;  //Additional protection for session verification used in other scripts
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!-- Custom style sheet by Robert Kety for this website -->
        <link id="cssLink" rel="stylesheet" type="text/css" href="css/project.css">        
        <!-- This website uses jQuery! -->
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <title>Login Page - Hero6 Progress</title>
    </head>
    <body>
        <div id="nav">
            <table class="normal">
                <tr>
                    <td colspan=2>
                        <div id="heading">Hero6 Progress</div>
                    </td>
                </tr>
                <tr>
                    <td class="singleWidth">
                        <span id="subheading">by Robert Kety</span>
                    </td>     
                    <td class="textright" id="threepwood">
                        <span>What's an adventure game?</span>
                    </td>
                </tr>
            </table>
        </div>
        <div id="mainContent">
            <form id="loginForm" class="centerOnPage">
                <table>
                    <tr>
                        <td>Username:</td>
                        <td><input type="text" name="username" id="username" /></td>                    
                    </tr>
                    <tr>
                        <td></td>
                        <td id="validName"></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input type="password" id="password" name="password" /></td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td class="textcenter" class="columnWidth">
                            <input type="button" id="register" value="Register" />
                            <input type="submit" id="loginUser" value="Login" />
                        </td>
                        <td id="invalidLogin" class="hidden opaque fade"></td>
                    </tr>
                </table>          
            </form>
        </div>
        <script>
            /* Clear form on load */
            clearForm();
            
            /* Ready functions */
            $(function(){
                $("#username, #password").on('change keyup', function(event){
                    /* Thanks to: http://stackoverflow.com/questions/10281962/is-there-a-minlength-validation-attribute-in-html5 */
                    $("#loginForm").validate({
                        rules:{
                            username:{
                                required:true,
                                minlength:4,
                                maxlength:10
                            },
                            password:{
                                required:true,
                                minlength:8,
                                maxlength:20
                            }
                        },
                        submitHandler: function(){
                            event.preventDefault();
                        } 
                    });
                });    

                $("#register").on('click', function(){
                    window.location.href='register.php';
                });
                
                $("#loginUser").on('click', function(event){
                    event.preventDefault();
                    if(($("#username").val().length >= 4) && ($("#password").val().length >= 8)){
                        loginUser();
                    }
                    else{                        
                        $("#invalidLogin").empty().append("Invalid Login");
                        $("#invalidLogin").toggleClass("hidden");
                        $("#invalidLogin").toggleClass("opaque");
                        setTimeout(function(){
                            $("#invalidLogin").toggleClass("opaque");
                            setTimeout(function(){
                                $("#invalidLogin").toggleClass("hidden");
                                $("#invalidLogin").empty();
                            }, 1000);
                        }, 1000);
                    }
                });
                
                $("#threepwood").on('click', function(){
                    window.location.href = 'monkey.html';
                });
            });
            
            /* Send form verified user name and password for credential verification */
            function loginUser(){
                $.ajax({
                    type: 'POST',
                    url: 'verifyLogin.php',
                    data: {
                        username:$("#username").val(),
                        password:$("#password").val()
                    },
                    success: function(output){
                        if(output === "1"){  //Forward to site (site will redirect to login page if login is not verified)
                            window.location.href='index.php';
                        }
                        else{   //Display invalid login message
                            $("#invalidLogin").empty().append("Invalid Login");
                            $("#invalidLogin").toggleClass("hidden");
                            $("#invalidLogin").toggleClass("opaque");
                            setTimeout(function(){
                                $("#invalidLogin").toggleClass("opaque");
                                setTimeout(function(){
                                    $("#invalidLogin").toggleClass("hidden");
                                    $("#invalidLogin").empty();
                                }, 1000);
                            }, 1000);
                        }
                    },
                    error: function(xhr, err){  //Thanks to: http://stackoverflow.com/questions/8041148/show-proper-error-messages-with-jquery-ajax
                        var fullPath = window.location.protocol + "//" + window.location.host, 
                            pathArray = window.location.pathname.split( '/' );
                        
                        /* Thanks to http://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/ */
                        pathArray[pathArray.length - 1] = page;
                        pathArray.shift();
                        for(var i = 0; i < pathArray.length; i++)
                            fullPath += "/" + pathArray[i];
                        
                        console.log("The requested page was: " + fullPath + "\nThe error number returned was: " + xhr.status + "\nThe error message was: " + xhr.responseText);
                    }
                });
            }
            
            /* Clears user name and password inputs */
            function clearForm(){
                $("#username").val("");
                $("#password").val("");                
            }
        </script>
    </body>
</html>