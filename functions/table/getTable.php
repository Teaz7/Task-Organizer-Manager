<?php
    include(__DIR__ . '/../../init.php');
    include(__DIR__ . '/../fetchUserId.php');
    
<<<<<<< HEAD
    $selectedId = $_SESSION['USER_selectedPetID'] ?? null;
=======
    $selectedId = $_SESSION['USER_selectedID'] ?? null;
>>>>>>> aa209fa74829ba286e0e56fe47a4d0cf701f04ed

    $stmt = $conn -> prepare("SELECT * FROM task WHERE USER_Id = :id");
    $stmt -> bindParam(":id", $id);
    $stmt -> execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_start();
    
    include 'tasktable.php';
    
    $defaultTable = ob_get_clean();
?>
