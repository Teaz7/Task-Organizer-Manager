<?php
    include 'init.php';
    $stmt = $conn -> prepare(
        "SELECT * FROM task WHERE TASK_Progress = 100"
    );
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
        <h1>TOM: Task Organizer Manager</h1>
        <div>
            <a href="homepage.php">Home</a>
            <a href="store.php">Store</a>
            <a href="calendar.php">Calendar</a>
            <a href="user\userhomepage.php">User Profile</a>
            <a href="index.php">Switch Account</a>
            <a href="functions\logout.php">Log Out</a>
        </div>
    </div>
    <br>
    <table>
        <thead>
            <td>Task Id</td>
            <td>Task Name</td>
            <td>Task Description</td>
            <td>Task Date Start</td>
            <td>Task Date Finish</td>
        </thead>
        <tbody>
            <?php foreach($results as $res): ?>
                <tr>
                    <td><?= $res["TASK_Id"] ?></td>
                    <td><?= $res["TASK_Name"] ?></td>
                    <td><?= $res["TASK_Description"] ?></td>
                    <td><?= $res["TASK_DateStart"] ?></td>
                    <td><?= $res["TASK_DateFinish"] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>