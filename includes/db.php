<?php
$SERVER = 'localhost';
$username = 'root';
$password = '';
$db_name = 'ai_learning_platform';

$conn = mysqli_connect($SERVER, $username, $password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
