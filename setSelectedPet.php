<?php
// session_start(); // Should be handled by init.php
include 'init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;
    $prodId_raw = $_POST['prodId'] ?? null;

    if (!$userId || $prodId_raw === null || !is_numeric($prodId_raw)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input. User or Product ID missing or invalid.']);
        exit;
    }
    $prodId = intval($prodId_raw);

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
        exit;
    }

    // Check ownership
    $stmt = $conn->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = :user_id AND prod_id = :prod_id");
    $stmt->execute(['user_id' => $userId, 'prod_id' => $prodId]);
    
    if ($stmt->fetchColumn() > 0) { // If count is greater than 0, user owns it
        $_SESSION['selectedPetId'] = $prodId; // Store as integer
        echo json_encode(['status' => 'success', 'message' => 'Pet selected successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'You do not own this pet. Purchase it first.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>