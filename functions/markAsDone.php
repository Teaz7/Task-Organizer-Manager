<?php
    include '../init.php';
    include 'fetchUserId.php';
    $taskId = $_GET["id"];
    
    $stmt = $conn -> prepare(
        "UPDATE user SET USER_Points = USER_Points + 5 WHERE USER_Id = :userid;
        UPDATE task SET TASK_Progress = 100 WHERE TASK_Id = :taskid"
    );
    $stmt -> bindParam(":userid", $id);
    $stmt -> bindParam(":taskid", $taskId);
    $stmt -> execute();

    header('Location: ../homepage.php');    
?>