<!DOCTYPE html>
<!--  Programmed by Robert Kety,
      This HTML script is the registration form for creating a new user account.  Users determine their own permissions
      via this form.  This is a constraint for the CS494 project guideline: "the content of pages will be different based 
      on the user that is logged in." Providing permission determination during registration provides the grader an 
      opportunity to inspect the implementation of this constraint.  
      Depending on extra time, this site may also include a link to a demo adventure game via jDOSbox -->
<html>
    <head>
        <meta charset="UTF-8">
        <!-- Custom style sheet by Robert Kety for this website -->
        <link id="cssLink" rel="stylesheet" type="text/css" href="css/project.css">        
        <!-- This website uses jQuery! -->
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <title>Registration Page - Hero6 Progress</title>
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
        <div class="centerOnPage">
            <form id="addUser">
                <table>
                    <tr>
                        <td>Username:</td>
                        <td><input type="text" id="username" name="username" /></td>                    
                    </tr>
                    <tr>
                        <td></td>
                        <td id="validName"></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input type="password" id="password" name="password" /></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td id="validPass"></td>
                    </tr>
                </table>
                <table>
                    <tr><td colspan=2 class="textcenter darkblue">Table Access Privileges</td><td></td></tr>
                    <tr>
                        <td class="textright">Rooms: <input type="checkbox" value="rooms" id="rooms" /></td>
                        <td class="textright">Dialogs: <input type="checkbox" value="dialogs" id="dialogs" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="textright">Characters: <input type="checkbox" value="chars" id="chars" /></td>
                        <td class="textright">Views: <input type="checkbox" value="views" id="views" /></td>   
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=2 class="textright">Edit Access: <input type="checkbox" value="edit" id="edit" /></td>
                        <td id="verifySelect"></td>                    
                    </tr>
                    <tr>
                        <td colspan=2 class="textcenter"><input type="submit" id="submitUser" value="Register" /></td>
                        <td id="verifyForm" class="hidden opaque fade"></td>
                    </tr>
                </table>
            </form>
        </div>
        <script>
            /* Clear form on load */
            clearForm();
            
            /* Ready functions */
            $(function(){
                $("#username").on('change keyup', function(event){
                    /* Thanks to: http://stackoverflow.com/questions/10281962/is-there-a-minlength-validation-attribute-in-html5 */
                    $("#addUser").validate({
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
                            },
                            submitHandler: function(){
                                event.preventDefault();
                            }
                        }
                    });   //Validate form
                    
                    $testName = $("#username").val();
                    
                    if($testName !== ""){
                        if($testName.length >= 4){
                            /* Thanks to: http://stackoverflow.com/questions/5316697/jquery-return-data-after-ajax-call-success */
                            verifyUsername($testName, function(output){
                                if(output){
                                    $("#validName").empty().append("Good!");   //Valid user name (length and not in use)
                                }
                                else{
                                    $("#validName").empty().append("Username already in use. Please select another.");
                                }
                            });
                        }
                        else{
                            $("#validName").empty();
                        }
                    }
                });
                
                $("#password").on('change keyup', function(event){
                    /* Thanks to: http://stackoverflow.com/questions/5316697/jquery-return-data-after-ajax-call-success */
                    $("#addUser").validate({
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
                            },
                            submitHandler: function(){
                                event.preventDefault();
                            }
                        }
                    });   //Validate form
                    
                    $testPass = $("#password").val();
                    
                    if($testPass !== ""){
                        if($testPass.length >= 8){
                            $("#validPass").empty().append("Good!");       //Valid length                     
                        }
                        else{
                            $("#validPass").empty();
                        }
                    }
                });
                
                $("#edit").on('change', function(){
                    if($("#edit").prop('checked')){
                        /* Prevent user from selecting the edit capability without selecting a table first */
                        if((!$("#rooms").prop('checked')) && (!$("#dialogs").prop('checked')) && 
                            (!$("#chars").prop('checked')) && (!$("#views").prop('checked'))){
                            $("#verifySelect").empty().append("You must select at least one table for edit access");
                        }
                        else{
                            $("#verifySelect").empty();
                        }
                    }
                    else{
                        $("#verifySelect").empty()
                    }
                });
                
                $("#addUser").submit(function(event){
                    /* Allow form submission when validated */
                    if(($("#username").val().length >= 4) && ($("#password").val().length >= 8) && 
                        ($("#verifySelect").text() == "") && ($("#validName").text() == "Good!")){
                        registerUser();
                    }
                    else{     //Display error message      
                        $("#verifyForm").empty().append("Please correct errors before submitting");
                        $("#verifyForm").toggleClass("hidden");
                        $("#verifyForm").toggleClass("opaque");
                        setTimeout(function(){
                            $("#verifyForm").toggleClass("opaque");
                            setTimeout(function(){
                                $("#verifyForm").toggleClass("hidden");
                                $("#verifyForm").empty();
                            }, 1000);
                        }, 1000);
                    }
                    
                    return false;   //I don't believe it. Thanks, http://stackoverflow.com/questions/1263852/prevent-form-redirect-or-refresh-on-submit
                });
                
                $("#threepwood").on('click', function(){
                    window.location.href = 'monkey.html';
                });
            });
                
            /* Resets form */
            function clearForm(){
                $("#username").val("");
                $("#password").val("");
                $("#rooms").attr('checked', false);
                $("#dialogs").attr('checked', false);
                $("#chars").attr('checked', false);
                $("#views").attr('checked', false);
                $("#edit").attr('checked', false);
            }
            
            /* POST to php script and receive bool on username availability */
            /* Thanks to: http://stackoverflow.com/questions/5316697/jquery-return-data-after-ajax-call-success */
            function verifyUsername(username, handleData){
                
                var output = $.ajax({
                    type: 'POST',
                    url: 'verifyUser.php',
                    data: {
                        'username':username
                    },
                    success: function(output){   
                        handleData(output);
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
            
            /* POST to php script that redirects to login page on success */
            function registerUser(){
                $.ajax({
                    type: 'POST',
                    url: 'addUser.php',
                    data: {
                        'username':$("#username").val(),
                        'password':$("#password").val(),
                        'rooms':$("#rooms").prop('checked'),
                        'dialogs':$("#dialogs").prop('checked'),
                        'chars':$("#chars").prop('checked'),
                        'views':$("#views").prop('checked'),
                        'edit':$("#edit").prop('checked')
                    },
                    success: function(output){
                        if(output === "1"){ //Redirect to login page on successful submission
                            window.location.href='login.php';
                        }
                        else{   //Display error message
                            $("#verifyForm").empty().append(output);
                            $("#verifyForm").toggleClass("hidden");
                            $("#verifyForm").toggleClass("opaque");
                            setTimeout(function(){
                                $("#verifyForm").toggleClass("opaque");
                                setTimeout(function(){
                                    $("#verifyForm").toggleClass("hidden");
                                    $("#verifyForm").empty();
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
        </script>
    </body>
</html>