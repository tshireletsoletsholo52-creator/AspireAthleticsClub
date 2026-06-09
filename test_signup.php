<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php';

$email    = "test@test.com";
$password = password_hash("password123", PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $password);

if ($stmt->execute()) {
    echo "SUCCESS - user inserted! Check phpMyAdmin now.";
} else {
    echo "FAILED - error: " . $stmt->error;
}
?>