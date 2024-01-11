<?php
// Start or resume the session
session_start();

// Check if the user is not authenticated, redirect to index.php
if (!isset($_SESSION['userKey'])) {
    header('Location: index.php');
}

// Configure PHP to display all errors and warnings.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Check if the logout form is submitted
if (isset($_POST['logout'])) {
    // Perform logout actions
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <!-- Link to external stylesheet -->
    <link rel="stylesheet" href="Components\style.css">
</head>

<body>
    <!-- HTML body starts here -->
    <div class="container">
        <div class="chat-box">
            <!-- Container for displaying chat messages -->
            <div class="messages"></div>

            <!-- Form for joining the chat -->
            <form action="" class="join-form">
                <input type="text" name="sender" id="sender" placeholder="Enter name">
                <button type="submit">Join Chat</button>
            </form>

            <!-- Form for sending messages (initially hidden) -->
            <form action="" method="post" class="msg-form hidden">
                <input type="text" name="msg" id="msg" placeholder="Write message">
                <button type="submit">Send</button>
            </form>

            <!-- Logout form (initially hidden) -->
            <form action="" method="post" class="join-form" class="msg-form hidden">
                <button type="submit" name="logout">Logout</button>
            </form>
        </div>
    </div>

    <!-- Include the JavaScript file -->
    <script src="Components\main.js"></script>
</body>

</html>