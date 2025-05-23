<?php
    include(__DIR__ . '/../../init.php');
    include(__DIR__ . '/../fetchUserId.php');
    
    $selectedId = $_SESSION['selected_product_id'] ?? null;

    $stmt = $conn -> prepare("SELECT * FROM task WHERE USER_Id = :id");
    $stmt -> bindParam(":id", $id);
    $stmt -> execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_start();
    
    include 'tasktable.php';
    
    $defaultTable = ob_get_clean();
?>
