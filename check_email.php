<?php
// check_email.php
header('Content-Type: application/json');
session_start();

try {
    $connection = new PDO('mysql:host=localhost;dbname=auction_system', 'root', '');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = $_POST['email'] ?? '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['exists' => false, 'error' => 'Invalid email format']);
        exit();
    }

    $stmt = $connection->prepare("SELECT userid FROM user WHERE email = ?");
    $stmt->execute([$email]);
    
    echo json_encode(['exists' => $stmt->rowCount() > 0]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>