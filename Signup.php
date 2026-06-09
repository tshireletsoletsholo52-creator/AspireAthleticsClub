<?php
<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: Sign_up.html");
    exit;
}

$first_name = trim($_POST["first_name"] ?? "");
$last_name  = trim($_POST["last_name"]  ?? "");
$email      = trim($_POST["email"]      ?? "");
$password   = $_POST["password"]        ?? "";
$confirm    = $_POST["confirm_password"] ?? "";

if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    header("Location: Sign_up.html?error=missing_fields");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: Sign_up.html?error=invalid_email");
    exit;
}

if (strlen($password) < 8) {
    header("Location: Sign_up.html?error=short_password");
    exit;
}

if ($password !== $confirm) {
    header("Location: Sign_up.html?error=password_mismatch");
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: Sign_up.html?error=email_taken");
    exit;
}
$stmt->close();

$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $hashed_password);

if (!$stmt->execute()) {
    $stmt->close();
    header("Location: Sign_up.html?error=server_error");
    exit;
}

$user_id = $conn->insert_id;
$stmt->close();

$full_name = trim($first_name . " " . $last_name);

// Set session immediately before anything else can fail
session_regenerate_id(true);
$_SESSION["user_id"] = $user_id;
$_SESSION["email"]   = $email;
$_SESSION["name"]    = $full_name;

// Save name in athletes table (non-critical, wrapped safely)
$stmt2 = $conn->prepare("INSERT INTO athletes (user_id, full_name) VALUES (?, ?)");
if ($stmt2) {
    $stmt2->bind_param("is", $user_id, $full_name);
    $stmt2->execute();
    $stmt2->close();
}

$conn->close();

die("Signup failed — email: $email | user found: " . ($user_id ? "YES, id=$user_id" : "NO"));
