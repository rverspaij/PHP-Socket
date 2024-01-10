<?php

namespace Classes;

use PragmaRX\Google2FA\Google2FA;

use mysqli;


class User
{
    private $host;
    private $user;
    private $password;
    private $db;


    public $connection;
    public $userGoogleKey;

    public $storedSalt;
    public $hashedPassword;

    // Constructor for a database connection class.
    public function __construct()
    {

        // Set database parameters.
        $this->host = 'db';
        $this->user = 'root';
        $this->password = 'password01';
        $this->db = 'test';

        // Create a new MySQLi database connection.
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->db);

        // Check if connection successful.
        if ($this->connection->connect_error) {
            die("Error: " . $this->connection->connect_error);
        }
    }

    public function createUser($postData)
    {
        $hashedPassword = sha1($postData["password"]);
        $salt = bin2hex(random_bytes(16));
        $password = $hashedPassword . $salt;

        $userFound = $this->connection->query("select * from users WHERE email like '%" .
            $postData['email'] . "%'");
        if ($userFound->num_rows == 0) {
            $checkPass = (new User)->checkPassword($postData['password'], $postData['cpassword']);
            if ($checkPass) {
                $google2fa = new Google2FA();
                $userKey = $google2fa->generateSecretKey();

                $currentTimestamp = date("Y-m-d H:i:s");

                $insertQuery = "INSERT INTO users (email, password, salt, name, google_key, created_at) VALUES ('" . $postData['email'] . "', '" . $password . "', '" . $salt . "', '" . $postData['name'] . "', '" . $userKey . "', '" . $currentTimestamp . "')";

                if ($this->connection->query($insertQuery)) {
                    // User creation successful
                    echo "User created successfully.";
                } else {
                    // User creation failed, handle the error
                    echo "Error creating user: " . $this->connection->error;
                }
            } else {
                echo "<script>alert('Password did not match requirements: minimum length 8, use uppercase and lowercase, use digits and special character!')</script>";
            }
        } else {
            echo "<script>alert('User already has an account')</script>";
        }
    }

    public function checkPassword($password, $cpassword)
    {
        $minLength = 8;
        $minUpperCase = 1;
        $minLowerCase = 1;
        $minDigits = 1;
        $minSpecialChars = 1;

        // Check minimum length
        if (strlen($password) < $minLength) {
            return false;
        }

        // Check for at least one uppercase letter
        if (preg_match_all('/[A-Z]/', $password) < $minUpperCase) {
            return false;
        }

        // Check for at least one lowercase letter
        if (preg_match_all('/[a-z]/', $password) < $minLowerCase) {
            return false;
        }

        // Check for at least one digit
        if (preg_match_all('/[0-9]/', $password) < $minDigits) {
            return false;
        }

        // Check for at least one special character
        $specialChars = str_split('!@#$%^&*()-_=+[]{}|;:,.<>?/');
        $countSpecialChars = count(array_intersect(str_split($password), $specialChars));
        if ($countSpecialChars < $minSpecialChars) {
            return false;
        }

        if ($password != $cpassword) {
            return false;
        }
        return true;
    }


    // Function to authenticate a user based on their email.
    public function authenticateUser($postData)
    {
        // Query the database to find a user with the provided email address.
        $userFound = $this->connection->query("select * from users WHERE email like '%" .
            $postData['email'] . "%'");

        // Check if user was found in the database.
        if ($userFound) {
            if ($userFound->num_rows) {
                // Iterate through each row and retrieve user's Google key from database.
                while ($row = $userFound->fetch_assoc()) {
                    $passwordCheck = $row['password'];
                    $storedSalt = $row['salt'];
                }
                $unhashPassword = $postData['password'];
                $passwordWithHash = sha1($unhashPassword);
                $passwordWithSalt = $passwordWithHash . $storedSalt;
                if ($passwordCheck == $passwordWithSalt) {
                    while ($row = $userFound->fetch_assoc()) {
                        $this->userGoogleKey = $row["google_key"];
                    }

                    // Close result set.
                    $userFound->close();

                    // Return true to indicate successful authentication.
                    return true;
                }
            }
        }
    }

    // Function to retrieve Google key from a user's account.
    public function getKeyFromUserAccount($postData)
    {
        // Query database to find a user with provided.
        $userFound = $this->connection->query("select * from users WHERE email like '%" .
            $postData['email'] . "%'");

        // Check if a user was found in database.
        if ($userFound) {
            if ($userFound->num_rows) {
                // Iterate through each row. And retrieve and store the user's Google key from the database.
                while ($row = $userFound->fetch_assoc()) {
                    return $row["google_key"];
                }

                // Close result.
                $userFound->close();
                // Indicate that the user was found.
                return true;
            }
        }
    }


    public function addKeyToUserAccount($post, $key)
    {
        $this->connection->query("update users set google_key='" . $key . "' where email ='" . $post['email'] . "'");
    }
}