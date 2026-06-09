<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

$email    = trim($_POST["email"]    ?? "");
$password = $_POST["password"] ?? "";

if (empty($email) || empty($password)) {
    header("Location: login.html?error=missing_fields");
    exit;
}

// Look up user by email
$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $hashed_password);
$stmt->fetch();
$stmt->close();

// Verify password against stored hash
if ($user_id && password_verify($password, $hashed_password)) {

    // Fetch name from athletes table if available
    $name = $email; // fallback
    $stmt2 = $conn->prepare("SELECT full_name FROM athletes WHERE user_id = ? LIMIT 1");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $stmt2->bind_result($full_name);
    $stmt2->fetch();
    $stmt2->close();
    if ($full_name) $name = $full_name;

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    $_SESSION["user_id"] = $user_id;
    $_SESSION["email"]   = $email;
    $_SESSION["name"]    = $name;

    header("Location: Dashboard_page.php");
    exit;
} else {
    header("Location: login.html?error=invalid_credentials");
    exit;
}

$conn->close();
?>