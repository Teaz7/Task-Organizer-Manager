<?php
    include 'init.php';
    include 'functions/fetchUserId.php';

    $stmt = $conn -> prepare("SELECT * FROM task WHERE USER_Id = :id");
    $stmt -> bindParam(":id", $id);
    $stmt -> execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="heading">
        <div>
            <a href="store.php">Store</a>
            <a href="calendar.php">Calendar</a>
            <a href="user\userhomepage.php">User Profile</a>
            <a href="index.php">Log Out</a>
        </div>
    </div>
    
    <br>
    <table>
        <thead>
            <tr>
                <th>Task ID</th>
                <th>Task Name</th>
                <th>Task Description</th>
                <th>Task Priority Class</th>
                <th>Task Status</th>
                <th>Task Date Start</th>
                <th>Task Date Finish</th>
                <th>Task Progress</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($results as $res) { ?>
                <tr>
                    <td> <?= $res['TASK_Id'] ?> </td>
                    <td> <?= $res['TASK_Name'] ?> </td>
                    <td> <?= $res['TASK_Description'] ?> </td>
                    <td> <?= $res['TASK_PriorityClass'] ?> </td>
                    <td> <?= $res['TASK_Status'] ?> </td>
                    <td> <?= $res['TASK_DateStart'] ?> </td>
                    <td> <?= $res['TASK_DateFinish'] ?> </td>
                    <td> <?= $res['TASK_Progress'] ?> </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>