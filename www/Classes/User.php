<?php


    namespace Classes;
    use mysqli;


    class User
    {
        private $host;
        private $user;
        private $password;
        private $db;


        public $connection;
        public $userGoogleKey;
        public $passwordCheck;

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
            if($this->connection->connect_error)
            {
                die("Error: ". $this->connection->connect_error);
            }
        }


        // Function to authenticate a user based on their email.
        public function authenticateUser($postData)
        {
            // Query the database to find a user with the provided email address.
            $userFound = $this->connection->query("select * from users WHERE email like '%". 
                $postData['email']."%'");

            // Check if user was found in the database.
            if($userFound)
            {
                if($userFound->num_rows)
                {
                    // Iterate through each row and retrieve user's Google key from database.
                    while($row = $userFound->fetch_assoc()){
                        $this->passwordCheck = $row['password'];
                    }
                    if($this->passwordCheck == $postData['password']) {
                        while($row =  $userFound->fetch_assoc()){
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
            $userFound = $this->connection->query("select * from users WHERE email like '%". 
                $postData['email']."%'");

            // Check if a user was found in database.
            if($userFound)
            {
                if($userFound->num_rows)
                {
                    // Iterate through each row. And retrieve and store the user's Google key from the database.
                    while($row =  $userFound->fetch_assoc()){
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
            $this->connection->query("update users set google_key='".$key."' where email ='".$post['email']."'");
        }
    }