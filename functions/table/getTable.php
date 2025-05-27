<?php
    include(__DIR__ . '/../../init.php');
    include(__DIR__ . '/../fetchUserId.php');
    
    $selectedId = $_SESSION['USER_selectedPetID'] ?? null;

    if ($_SESSION["taskDisplay"] == 0) {
        $stmt = $conn->prepare("SELECT * FROM task WHERE USER_Id = :id AND TASK_Progress < 100");
    } else {
        $stmt = $conn->prepare("SELECT * FROM task WHERE USER_Id = :id  AND TASK_Progress = 100");
    }
    $stmt -> bindParam(":id", $id);
    $stmt -> execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_start();
    
    include 'tasktable.php';
    
    $defaultTable = ob_get_clean();
?>
