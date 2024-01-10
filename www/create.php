<?php
$loginCheck = false;
// Start output buffering to prevent immediate output to browser.
ob_start();

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

if (!empty($_POST)) {
    if ((new user())->createUser($_POST)) {
        $loginCheck = true;
    }
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
            height: 375px;
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
            <div id="login-row" class="row justify-content-center align-items-center">
                <div id="login-column" class="col-md-6">
                    <div id="login-box" class="col-md-12">
                        <form id="login-form" class="form" action="create.php" method="post">
                            <h3 class="text-center text-info">Create Account</h3>
                            <div class="form-group">
                                <label for="name" class="text-info">Name:</label><br>
                                <input type="text" name="name" id="name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email" class="text-info"> Email:</label><br>
                                <input type="text" name="email" id="password" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="password" class="text-info">Password:</label><br>
                                <input type="password" name="password" id="password" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cpassword" class="text-info">Confirm Password:</label><br>
                                <input type="password" name="cpassword" id="cpassword" class="form-control">
                            </div>
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-info btn-md" value="Create Account">
                                <input type="submit" name="submit" class="btn btn-danger btn-md" value="Cancel">
                            </div>
                            <br />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>