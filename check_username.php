<?php
// check_username.php
header('Content-Type: application/json');
session_start();

try {
    $connection = new PDO('mysql:host=localhost;dbname=auction_system', 'root', '');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $username = $_POST['username'] ?? '';
    
    if (empty($username)) {
        echo json_encode(['exists' => false, 'error' => 'Username is required']);
        exit();
    }

    $stmt = $connection->prepare("SELECT userid FROM user WHERE username = ?");
    $stmt->execute([$username]);
    
    echo json_encode(['exists' => $stmt->rowCount() > 0]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>