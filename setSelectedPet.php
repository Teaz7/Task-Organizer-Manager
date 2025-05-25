<?php
session_start();
header('Content-Type: application/json');

// DEBUGGING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Check prodId is present
if (!isset($_POST['prodId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID not provided.']);
    exit;
}

$prodId = intval($_POST['prodId']);
if ($prodId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
    exit;
}

// DB + user session
require_once 'init.php';
require_once 'functions/fetchUserId.php';

$userId = $id ?? null;
if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated.']);
    exit;
}

// Check if user owns the product
$stmt = $conn->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = :user_id AND prod_id = :prod_id");
$stmt->execute([':user_id' => $userId, ':prod_id' => $prodId]);
$owned = $stmt->fetchColumn();

if (!$owned) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You do not own this product.',
        'userId' => $userId,
        'prodId' => $prodId
    ]);
    exit;
}

// ✅ Save the selected pet to the database
$stmt = $conn->prepare("UPDATE user SET USER_selectedPetID = :prod_id WHERE USER_Id = :user_id");
$stmt->execute([':prod_id' => $prodId, ':user_id' => $userId]);

// ✅ Also store in session
$_SESSION['USER_selectedPetID'] = $prodId;

echo json_encode(['status' => 'success', 'message' => 'Pet selected successfully.']);
