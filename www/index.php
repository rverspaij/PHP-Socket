<?php
$loginCheck = false;
// Start output buffering to prevent immediate output to the browser.
ob_start();

// Start a session.
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

// Check if the HTTP POST request is not empty.
if (!empty($_POST)) {
    // Create a new User instance and attempt to authenticate the user.
    if (!(new User())->authenticateUser($_POST)) {
        $loginCheck = true;
    } else {
        // If authentication is successful, create a new GoogleAuth instance.
        new GoogleAuth($_POST);
    }
}

// Check if there are GET parameters.
if ($_GET) {
    if (isset($_GET["code"]) && $_GET["code"] != "") {
        // If a "code" parameter is present, verify it with Google.
        (new GoogleAuth)->verifyFromGoogle($_GET["code"]);
        return;
    }

    // If no "code" parameter is present, perform logout actions.
    session_destroy();
    echo "Canceled Successfully";
    header("location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="eng">

<head>
    <title>2-factor Authentication Example</title>
    <!-- Include Bootstrap CSS from CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <!-- Custom styling -->
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
    <!-- Login Form Container -->
    <div id="login">
        <div class="container">
            <?php
            // Check if the user is already logged in.
            if (isset($_SESSION["userKey"])) {
                ?>
                <h3 class="text-center text-black pt-5"></h3>
                <h3 class="text-center text-black pt-5">
                </h3>
                <?php
            }

            // Check if the user is already logged in.
            if (isset($_SESSION["userKey"])) {
                // HTML for collecting Google Authenticator code.
                ?>
                <div id="login-row" class="row justify-content-center align-items-center">
                    <div id="login-column" class="col-md-6">
                        <div id="login-box" class="col-md-12">
                            <form id="login-form" class="form" action="index.php" method="get">
                                <h3 class="text-center text-info">Check 2-factor Authentication</h3>
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
                                    <input type="submit" name="submit" class="btn btn-danger btn-md" onclick="goBack()"
                                        value="Cancel">
                                </div>
                                <script>
                                    function goBack() {
                                        window.location.href = 'localhost:99'
                                    }
                                </script>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } else {
                // HTML for login with email and password.
                ?>
                <div id="login-row" class="row justify-content-center align-items-center">
                    <div id="login-column" class="col-md-6">
                        <div id="login-box" class="col-md-12">
                            <form id="login-form" class="form" action="index.php" method="post">
                                <h3 class="text-center text-info">Login</h3>
                                <div class="form-group">
                                    <label for="username" class="text-info">Email:</label><br>
                                    <input type="text" name="email" id="username" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="password" class="text-info">Password:</label><br>
                                    <input type="password" name="password" id="password" class="form-control">
                                </div>
                                <div class="form-group">
                                    <?php
                                    // Display an error message if login is unsuccessful.
                                    if ($loginCheck == true) {
                                        echo "<p style='color: red;'>Incorrect username or password!</p>";
                                    }
                                    ?>
                                </div>
                                <br />
                                <div class="form-group">
                                    <input type="submit" name="submit" class="btn btn-info btn-md" value="Login">
                                    <input type="button" name="cancel" class="btn btn-info btn-md" onclick="goCreate()"
                                        value="Create Account" style="background-color: #17a2b8">
                                </div>
                                <script>
                                    function goCreate() {
                                        window.location.href = 'http://localhost:99/create.php';
                                    }
                                </script>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</body>

</html>

<?php
// End output buffering and flush the buffer.
ob_end_flush();
?>