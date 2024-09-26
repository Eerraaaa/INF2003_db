<?php

$config = parse_ini_file('/var/www/private/db-config.ini');

$host = $config['servername'];
$user = $config['username'];
$pass = $config['password'];
$db   = $config['dbname'];

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
