<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

$currentUserId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages — NomadNest</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
</head>
<body>

    <div class="messages-layout">

    <aside class="conversation-sidebar">
        <div id="conversationsList"></div>
    </aside>

    <section class="chat-section">

        <div id="messagesThread"></div>

        <div class="message-input">
            <input type="text" id="messageText" placeholder="Write message...">
            <button id="sendMessageBtn">Send</button>
        </div>

    </section>

</div>

    <script>
        const CURRENT_USER_ID = <?= $currentUserId ?>;
    </script>
    <script src="/nomadnest/js/messages.js"></script>
</body>
</html>
