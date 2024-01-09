<?php

// Start output buffering to prevent immediate output to browser.
ob_start();

// Start session.
session_start();
require "vendor/autoload.php";
require "Classes/User.php";
require "Classes/GoogleAuth.php";

// Import classes for easy use in code.
use Classes\User;
use Classes\GoogleAuth;


// Configure PHP to display all errors and warnings.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Check if HTTP POST is not empty
if(!empty($_POST))
{
    // Create a new User instance and attempt to authenticate the user.
    if(!(new User())->authenticateUser($_POST))
    {
        echo '<script>document.getElementById("error-message").innerHTML = "Invalid username or password";</script>';
    } else {
        new GoogleAuth($_POST);
    }
}

if($_GET)
{
    if(isset($_GET["code"]) && $_GET["code"] != "")
    {
        (new GoogleAuth)->verifyFromGoogle($_GET["code"]);
        return;
    }

    session_destroy();
    echo "Canceled Scuccessfuly";
    header("location: index.php");
    exit;
}

?>


<!DOCTYPE html>
<html lang="eng">
<head>
    <title>2factor Authentication Example</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #17a2b8;
            height: 100vh;
        }
        #login .container #login-row #login-column #login-box {
            margin-top: 120px;
            max-width: 600px;
            height: 320px;
            border: 1px solid #9C9C9C;
            background-color: #EAEAEA;
        }
        #login .container #login-row #login-column #login-box #login-form {
            padding: 20px;
        }
        #login .container #login-row #login-column #login-box #login-form #register-link {
            margin-top: -85px;
        }
    </style>
</head>
<body>
    <div id="login">
        <div class="container">
            <?php 
            
            if(isset($_SESSION["userKey"])){
            ?>
                <h3 class="text-center text-black pt-5"></h3>
                <h3 class="text-center text-black pt-5">
                </h3>
            <?php
            }



            if(!empty($_SESSION["message"])){
                ?>
                <h3 class="alert alert-<?php echo $_SESSION["status"]?>">
                    <?php echo $_SESSION["message"]; ?>
                </h3>
                <?php
            }
            
            if(isset($_SESSION["userKey"])){
                // Html for collecting google authenticator code.
                ?>
                <div id="login-row" class="row justify-content-center align-items-center">
                <div id="login-column" class="col-md-6">
                    <div id="login-box" class="col-md-12">
                        <form id="login-form" class="form" action="index.php" method="get">
                            <h3 class="text-center text-info">Check 2factor Authentication</h3>
                            <div class="form-group">
                                <label for="code" class="text-info">Google code:</label><br>
                                <input type="text" name="code" id="code" class="form-control">
                            </div>
                            <br />
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-info btn-md" value="Login">
                            </div>
                            <br />
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-danger btn-md" value="Cancel">
                            </div>
                        </form>
                    </div>
                </div>
            </div>



            <?php } else {
                
                // Html for login email and password.
                ?>
                <div id="login-row" class="row justify-content-center align-items-center">
                <div id="login-column" class="col-md-6">
                    <div id="login-box" class="col-md-12">
                        <form id="login-form" class="form" action="index.php" method="post">
                            <h3 class="text-center text-info">Login</h3>
                            <label for="error-message" class="text-danger"></label>
                            <div class="form-group">
                                <label for="username" class="text-info">Email:</label><br>
                                <input type="text" name="email" id="username" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="password" class="text-info">Password:</label><br>
                                <input type="text" name="password" id="password" class="form-control">
                            </div>
                            <br />
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-info btn-md" value="Login">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php }?>



        </div>
    </div>
</body>
</html>


<?php
    ob_end_flush();
?>