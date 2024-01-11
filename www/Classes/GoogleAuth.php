<?php
namespace Classes;

use PragmaRX\Google2FA\Google2FA;


class GoogleAuth
{
    public $google2fa;
    public $userKey;

    // Constructor for a class, initizalizing with an optional $post parameter.
    public function __construct($post = null)
    {
        // Create new instance of the Google2FA class.
        $this->google2fa = new Google2FA();

        // Check if the user's secret key is stored in the session.
        if (!isset($_SESSION["userKey"])) {
            // get key from database if exists.
            $this->userKey = (new User)->getKeyFromUserAccount($post);

            // Generate a new secret key and add to user account in database.
            if (!$this->userKey) {
                $this->userKey = $this->google2fa->generateSecretKey();


                (new User)->addKeyToUserAccount($post, $this->userKey);
            }

            // Store the key in the session variable.
            $_SESSION["userKey"] = $this->userKey;
        } else {
            // Retrieve the user's key
            $this->userKey = $_SESSION["userKey"];
        }
    }

    // Verifying two-factor authentication code from Google.
    public function verifyFromGoogle($code)
    {
        // Code will be considered valid if it is generated in the last 8 time steps.
        $window = 8;

        // Verify the code against the stored  user key.
        $valid = $this->google2fa->verifyKey($_SESSION["userKey"], $code, $window);

        // If the the code is valid redirect to chat page.
        if ($valid) {
            $_SESSION['status'] = 'success';
            header('location: chat.php');
            exit;
        }

        // If the code is not valid send error message and status
        $_SESSION['message'] = '2factor Authentication Failed';
        $_SESSION['status'] = 'danger';

        header("location: index.php");
        exit;
    }
}