<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['prodId']) && is_numeric($_POST['prodId'])) {
        $_SESSION['selectedPetId'] = intval($_POST['prodId']);
        echo json_encode(['status' => 'success']);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}
