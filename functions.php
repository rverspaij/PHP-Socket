<?php

// Function to unmask received WebSocket data.
function unmask($text)
{
	// Looking how long the length of the message is.
	$length = @ord($text[1]) & 127;

	// Extract masks and data based on the length of the payload.
	if ($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	} elseif ($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	} else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}

	// Unmask the data using XOR operation with masks.
	$unmaskedText = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$unmaskedText .= $data[$i] ^ $masks[$i % 4];
	}

	return $unmaskedText;
}

// Function to pack data for WebSocket transmission.
function pack_data($text)
{
	// Setting up the control byte for a websocket frame.
	$b1 = 0x80 | (0x1 & 0x0f);

	// Calculating the length of the data to be sent.
	$length = strlen($text);

	// Construct the WebSocket frame header based on the length of the payload.
	if ($length <= 125) {
		$header = pack('CC', $b1, $length);
	} elseif ($length > 125 && $length < 65536) {
		$header = pack('CCn', $b1, 126, $length);
	} elseif ($length >= 65536) {
		$header = pack('CCNN', $b1, 127, $length);
	}

	return $header . $text;
}

// Function to perform WebSocket handshake.
function handshake($request_header, $sock, $host_name, $port)
{
	// Parse the WebSocket upgrade request header.
	$headers = array();
	$lines = preg_split("/\r\n/", $request_header);
	foreach ($lines as $line) {
		$line = chop($line);
		if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
			$headers[$matches[1]] = $matches[2];
		}
	}

	// Extract the Sec-WebSocket-Key for generating the response.
	$sec_key = $headers['Sec-WebSocket-Key'];

	// Generate and encode the Sec-WebSocket-Accept response header.
	$sec_accept = base64_encode(pack('H*', sha1($sec_key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

	// Construct the WebSocket handshake response header.
	$response_header = "HTTP/1.1 101 Switching Protocols\r\n" .
		"Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"Sec-WebSocket-Accept:$sec_accept\r\n\r\n";

	// Send the response header to complete the handshake.
	socket_write($sock, $response_header, strlen($response_header));
}
