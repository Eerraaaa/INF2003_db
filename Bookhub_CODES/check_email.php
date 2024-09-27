<?php
// check_email.php
require 'lib/connection.php'; // Ensure secure database connection

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["email"])) {
    $email = $_POST["email"];

    // Prepare and execute query
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Since you're sending a JSON content type, make sure to encode your response as JSON
        echo json_encode(['status' => 'exists']);
    } else {
        echo json_encode(['status' => 'not_exists']);
    }
} else {
    // Proper JSON response for error or direct access
    echo json_encode(['status' => 'error']);
}
?>
