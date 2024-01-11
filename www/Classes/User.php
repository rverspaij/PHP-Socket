<?php

namespace Classes;

use PragmaRX\Google2FA\Google2FA;
use mysqli;

// User class for managing user-related operations
class User
{
    // Database connection parameters
    private $host;
    private $user;
    private $password;
    private $db;

    // Public properties for database connection, user's Google key, stored salt, and hashed password
    public $connection;
    public $userGoogleKey;
    public $storedSalt;
    public $hashedPassword;

    // Constructor for initializing a database connection
    public function __construct()
    {
        // Set database parameters
        $this->host = 'db';
        $this->user = 'root';
        $this->password = 'password01';
        $this->db = 'test';

        // Create a new MySQLi database connection
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->db);

        // Check if connection successful
        if ($this->connection->connect_error) {
            die("Error: " . $this->connection->connect_error);
        }
    }

    // Function to create a new user
    public function createUser($postData)
    {
        // Hash the password and generate a salt
        $hashedPassword = sha1($postData["password"]);
        $salt = bin2hex(random_bytes(16));
        $password = $hashedPassword . $salt;

        // Check if the user already exists in the database
        $userFound = $this->connection->query("select * from users WHERE email like '%" .
            $postData['email'] . "%'");
        if ($userFound->num_rows == 0) {
            // Check password requirements
            $checkPass = (new User)->checkPassword($postData['password'], $postData['cpassword']);
            if ($checkPass) {
                // Generate a new Google 2FA secret key
                $google2fa = new Google2FA();
                $userKey = $google2fa->generateSecretKey();

                $currentTimestamp = date("Y-m-d H:i:s");

                // Insert user data into the database
                $insertQuery = "INSERT INTO users (email, password, salt, name, google_key, created_at) VALUES ('" . $postData['email'] . "', '" . $password . "', '" . $salt . "', '" . $postData['name'] . "', '" . $userKey . "', '" . $currentTimestamp . "')";

                if ($this->connection->query($insertQuery)) {
                    // User creation successful
                    echo "<script>alert('Save this code: $userKey in google authenticator app for future authentication!')</script>";
                } else {
                    // User creation failed, handle the error
                    echo "<script>alert('Something went wrong when creating account!')</script>";
                }
            } else {
                echo "<script>alert('Password did not match requirements: minimum length 8, use uppercase and lowercase, use digits and special character!')</script>";
            }
        } else {
            echo "<script>alert('User already has an account')</script>";
        }
    }

    // Function to check password complexity
    public function checkPassword($password, $cpassword)
    {
        // Password complexity requirements
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

        // Check if the passwords match
        if ($password != $cpassword) {
            return false;
        }

        // Password meets all requirements
        return true;
    }

    // Function to authenticate a user based on their email
    public function authenticateUser($postData)
    {
        // Query the database to find a user with the provided email address
        $userFound = $this->connection->query("select * from users WHERE email like '%" .
            $postData['email'] . "%'");

        // Check if user was found in the database
        if ($userFound) {
            if ($userFound->num_rows) {
                // Iterate through each row and retrieve user's Google key from database
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

                    // Close result set
                    $userFound->close();

                    // Return true to indicate successful authentication
                    return true;
                }
            }
        }
    }

    // Function to retrieve Google key from a user's account
    public function getKeyFromUserAccount($postData)
    {
        // Query database to find a user with provided email
        $userFound = $this->connection->query("select * from users WHERE email like '%" .
            $postData['email'] . "%'");

        // Check if a user was found in database
        if ($userFound) {
            if ($userFound->num_rows) {
                // Iterate through each row and retrieve and store the user's Google key from the database
                while ($row = $userFound->fetch_assoc()) {
                    return $row["google_key"];
                }

                // Close result
                $userFound->close();
                // Indicate that the user was found
                return true;
            }
        }
    }

    // Function to add Google key to a user's account
    public function addKeyToUserAccount($post, $key)
    {
        $this->connection->query("update users set google_key='" . $key . "' where email ='" . $post['email'] . "'");
    }
}
