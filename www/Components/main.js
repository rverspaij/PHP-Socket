(function () {
    // Function to send a message through the WebSocket
    function sendMessage(message) {
        socket.send(message);
    }

    // Function to parse a received message from JSON format
    function parseMessage(message) {
        var msg = { type: "", sender: "", text: "" };
        try {
            // Attempt to parse the message as JSON
            msg = JSON.parse(message);
        } catch (e) {
            // If parsing fails, return false
            return false;
        }
        return msg;
    }

    // Function to append a parsed message to the chat box
    function appendMessage(message) {
        var parsedMsg;
        // Get the container element where messages will be displayed
        var msgContainer = document.querySelector(".messages");

        if ((parsedMsg = parseMessage(message))) {
            // If the message is successfully parsed, create HTML elements for the message
            var msgElem, senderElem, textElem;
            var sender, text;

            // Create a new <div> element for the message
            msgElem = document.createElement("div");
            msgElem.classList.add('msg');
            msgElem.classList.add('msg-' + parsedMsg.type);

            // Create <span> elements for sender and text
            senderElem = document.createElement("span");
            senderElem.classList.add("msg-sender");

            textElem = document.createElement("span");
            textElem.classList.add("msg-text");

            // Create text nodes for sender and text content
            sender = document.createTextNode(parsedMsg.sender + ': ');
            text = document.createTextNode(parsedMsg.text);

            // Append text nodes to corresponding <span> elements
            senderElem.appendChild(sender);
            textElem.appendChild(text);

            // Append <span> elements to the main <div> for the message
            msgElem.appendChild(senderElem);
            msgElem.appendChild(textElem);

            // Append the message <div> to the messages container
            msgContainer.appendChild(msgElem);
        }
    }

    // Function to set up the chat forms and actions
    function setup() {
        var sender = '';
        var joinForm = document.querySelector('form.join-form');
        var msgForm = document.querySelector('form.msg-form');
        var closeForm = document.querySelector('form.close-form');

        // Event listener for join form submission
        function joinFormSubmit(event) {
            event.preventDefault();
            // Get the sender's name from the input field
            sender = document.getElementById('sender').value;
            // Create a join message and send it through the WebSocket
            var joinMsg = {
                type: "join",
                sender: sender,
                text: sender + ' joined the chat!'
            };

            sendMessage(JSON.stringify(joinMsg));
            // Hide the join form, show the message form and close form
            joinForm.classList.add('hidden');
            msgForm.classList.remove('hidden');
            closeForm.classList.remove('hidden');
        }

        joinForm.addEventListener('submit', joinFormSubmit);

        // Event listener for message form submission
        function msgFormSubmit(event) {
            event.preventDefault();
            var msgField, msgText, msg;
            // Get the message from the input field
            msgField = document.getElementById('msg');
            msgText = msgField.value;
            // Create a normal message and send it through the WebSocket
            msg = {
                type: "normal",
                sender: sender,
                text: msgText
            };
            msg = JSON.stringify(msg);
            sendMessage(msg);
            // Clear the message input field
            msgField.value = '';
        }

        msgForm.addEventListener('submit', msgFormSubmit);

        // Event listener for close form submission (closing the chat)
        function closeFormSubmit(event) {
            event.preventDefault();
            // Close the WebSocket and reload the page
            socket.close();
            window.location.reload();
        }

        closeForm.addEventListener('submit', closeFormSubmit);
    }

    // Create a WebSocket instance and define event listeners
    let socket = new WebSocket("ws://localhost:8920");

    // Event listener for socket open
    var socketOpen = (e) => {
        console.log("Connected to the socket");
        // Display a join message when connected to the chat server
        var msg = {
            type: 'join',
            sender: 'Browser',
            text: 'Connected to the chat server'
        }
        // Append the message to the chat box and set up the chat forms
        appendMessage(JSON.stringify(msg));
        setup();
    }

    // Event listener for receiving messages from the socket
    var socketMessage = (e) => {
        console.log(`Message from socket: ${e.data}`);
        // Append the received message to the chat box
        appendMessage(e.data);
    }

    // Event listener for socket close
    var socketClose = (e) => {
        var msg;
        console.log(e);
        if (e.wasClean) {
            console.log("The connection closed cleanly");
            // Display a message when the connection closes cleanly
            msg = {
                type: 'left',
                sender: 'Browser',
                text: 'The connection closed cleanly'
            }
        } else {
            console.log("The connection closed for some reason");
            // Display a message when the connection closes for some reason
            msg = {
                type: 'left',
                sender: 'Browser',
                text: 'The connection closed for some reason'
            }
        }
        // Append the message to the chat box
        appendMessage(JSON.stringify(msg));
    }

    // Event listener for socket error
    var socketError = (e) => {
        console.log("WebSocket Error");
        console.log(e);
    }

    // Attach event listeners to the WebSocket instance
    socket.addEventListener("open", socketOpen);
    socket.addEventListener("message", socketMessage);
    socket.addEventListener("close", socketClose);
    socket.addEventListener("error", socketError);

})();
