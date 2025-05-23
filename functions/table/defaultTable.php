<?php
    include(__DIR__ . '/../../init.php');
    include(__DIR__ . '/../fetchUserId.php');

    $stmt = $conn->prepare("SELECT * FROM task WHERE USER_Id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    include 'tasktable.php';
?>
