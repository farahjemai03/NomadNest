<?php
// api/logout.php
// GET or POST — destroys the session and redirects to home

session_start();
$_SESSION = [];
session_destroy();

header('Location: /index.php');
exit;
