<?php
require 'auth_check.php';

var_dump($_SESSION);

$name  = htmlspecialchars($_SESSION["name"]  ?? "");
$email = htmlspecialchars($_SESSION["email"] ?? "");

?>
