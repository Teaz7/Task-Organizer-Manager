<?php
    include(__DIR__ . '/../../init.php');
    include(__DIR__ . '/../fetchUserId.php');

    $sort = $_GET['sort'] ?? 'sortID';

    $columns = [
        'sortID' => 'TASK_Id',
        'sortName' => 'TASK_Name',
        'sortDateStart' => 'TASK_DateStart',
        'sortDateEnd' => 'TASK_DateFinish',
        'sortProgress' => 'TASK_Progress'
    ];

    $orderBy = $columns[$sort] ?? 'TASK_Id';
    if ($_SESSION["taskDisplay"] == 0) {
        $stmt = $conn->prepare("SELECT * FROM task WHERE USER_Id = :id AND TASK_Progress < 100 ORDER BY $orderBy");
    } else {
        $stmt = $conn->prepare("SELECT * FROM task WHERE USER_Id = :id AND TASK_Progress = 100 ORDER BY $orderBy");
    }
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    include 'tasktable.php';
?>
