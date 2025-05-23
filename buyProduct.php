<?php
include 'init.php';
include 'functions/fetchUserId.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$user_id =$id; // Assuming $id is fetched from the session or database
$prod_id_raw = $_POST['prodId'] ?? null;

// Debugging (uncomment if needed)
error_log('user_id: ' . var_export($user_id, true));
// error_log('prodId: ' . var_export($prod_id_raw, true));

if (!$user_id || empty($prod_id_raw) || !is_numeric($prod_id_raw)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input: User not logged in or Product ID missing/invalid.']);
    exit;
}

$prod_id = intval($prod_id_raw);

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

try {
    // Check if the product is already owned by the user
    $stmtCheckOwnership = $conn->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = :user_id AND prod_id = :prod_id");
    $stmtCheckOwnership->execute(['user_id' => $user_id, 'prod_id' => $prod_id]);
    if ($stmtCheckOwnership->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You already own this product.']);
        exit;
    }

    // Fetch product cost
    $stmtProduct = $conn->prepare("SELECT PROD_Amount FROM product WHERE PROD_Id = :prod_id");
    $stmtProduct->execute(['prod_id' => $prod_id]);
    $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        exit;
    }
    $product_cost = $product['PROD_Amount'];

    // Get user points
    $stmtUser = $conn->prepare("SELECT USER_Points FROM user WHERE USER_ID = :user_id");
    $stmtUser->execute(['user_id' => $user_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit;
    }

    if ($user['USER_Points'] < $product_cost) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient points to purchase this product.']);
        exit;
    }

    // Proceed with transaction
    $conn->beginTransaction();

    // Deduct points
    $stmtUpdatePoints = $conn->prepare("UPDATE user SET USER_Points = USER_Points - :cost WHERE USER_ID = :user_id");
    $stmtUpdatePoints->execute(['cost' => $product_cost, 'user_id' => $user_id]);

    // Record purchase
    $stmtInsertPurchase = $conn->prepare("INSERT INTO purchases (user_id, prod_id, purchased_at) VALUES (:user_id, :prod_id, NOW())");
    $stmtInsertPurchase->execute(['user_id' => $user_id, 'prod_id' => $prod_id]);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Product purchased successfully!']);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    // error_log('Purchase failed (PDO): ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Purchase failed due to a database error. Please try again.']);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    // error_log('Purchase failed (General): ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred during purchase.']);
}
?>