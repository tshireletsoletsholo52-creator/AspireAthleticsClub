<?php
// Include this file at the very top of any page that requires login.
// If the user is not logged in, they get sent to the login page.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION["user_id"])) {
    header("Location: login.html?error=login_required");
    exit;
}
?>