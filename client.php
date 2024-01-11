<?php

$host = "127.0.0.1"; // Replace with the IP address or hostname of your server
$port = 8920;        // Replace with the port number on which your server is listening

// Create a TCP/IP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($socket === false) {
    echo "Failed to create socket: " . socket_strerror(socket_last_error()) . "\n";
    exit;
}

// Connect to the server
if (!socket_connect($socket, $host, $port)) {
    echo "Failed to connect to server: " . socket_strerror(socket_last_error($socket)) . "\n";
    exit;
}

// Send data to the server
$message = "Hello, server!";
socket_write($socket, $message, strlen($message));

// Read the response from the server
$response = socket_read($socket, 1024);

echo "Server response: " . $response . "\n";

// Close the socket
socket_close($socket);

?>