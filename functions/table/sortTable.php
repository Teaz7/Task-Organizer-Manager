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

    $stmt = $conn->prepare("SELECT * FROM task WHERE USER_Id = :id ORDER BY $orderBy");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    include 'tasktable.php';
?>
