<?php
session_start(); // Ensure session is started

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect the user to the login page (or any other page)
header('Location: login.php');
exit(); // Ensure no further code is executed after the redirect
?>
