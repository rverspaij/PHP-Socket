<?php

// Secret key for authentication (Note: This is just a placeholder and may not be secure)
$secretKey = "test";

// Server configuration
$address = '0.0.0.0';
$port = 8920;
$null = NULL;

// Include custom functions
include 'functions.php';

// Create a TCP/IP socket
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($sock, $address, $port);
socket_listen($sock);

// Arrays to store connected clients and connections
$members = [];
$connections = [];
$connections[] = $sock;

// Display server information
echo "Listening for new connections on port $port: " . "\n";

// Main server loop
while (true) {

    // Set up arrays for socket_select
    $reads = $writes = $exceptions = $connections;
    socket_select($reads, $writes, $exceptions, 0);

    // Check for new incoming connections
    if (in_array($sock, $reads)) {
        $new_connection = socket_accept($sock);
        $header = socket_read($new_connection, 1024);

        handshake($header, $new_connection, $address, $port);
        $connections[] = $new_connection;

        // Notify the new client to enter a name
        $reply = [
            "type" => "join",
            "sender" => "Server",
            "text" => "enter name to join... \n"
        ];
        $reply = pack_data(json_encode($reply));
        socket_write($new_connection, $reply, strlen($reply));

        // Remove the server socket from the read array
        $firstIndex = array_search($sock, $reads);
        unset($reads[$firstIndex]);
    }

    // Check for data from existing connections
    foreach ($reads as $key => $value) {

        $data = socket_read($value, 1024);

        // Check for errors during socket read
        if ($data === false) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);
            echo "Socket Read Error: [$errorCode] $errorMessage\n";
        }

        // Process received data
        if (!empty($data)) {
            $message = unmask($data);
            $decoded_message = json_decode($message, true);
            if ($decoded_message) {
                if (isset($decoded_message['text'])) {
                    if ($decoded_message['type'] === 'join') {
                        // Add new member to the list
                        $members[$key] = [
                            'name' => $decoded_message['sender'],
                            'connection' => $value
                        ];
                    }
                    // Broadcast the message to all connected members
                    $maskedMessage = pack_data($message);
                    foreach ($members as $mkey => $mvalue) {
                        socket_write($mvalue['connection'], $maskedMessage, strlen($maskedMessage));
                    }
                }
            }
        } else if ($data === '') {
            // Handle disconnection
            echo "disconnected " . $key . " \n";
            unset($connections[$key]);
            if (array_key_exists($key, $members)) {

                // Notify other members about the disconnection
                $message = [
                    "type" => "left",
                    "sender" => "Server",
                    "text" => $members[$key]['name'] . " left the chat \n"
                ];
                $maskedMessage = pack_data(json_encode($message));
                unset($members[$key]);
                foreach ($members as $mkey => $mvalue) {
                    socket_write($mvalue['connection'], $maskedMessage, strlen($maskedMessage));
                }
            }
            socket_close($value);
        }
    }
}
?>