<?php include '../init.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $stmt = $conn->prepare("DELETE FROM task WHERE TASK_Id=:val1");
        $stmt->bindParam(':val1', $_POST["removeTaskId"]);

        $result = $stmt -> execute();

        header('Location: taskhomepage.php');
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
        Task ID: <input type="number" name="removeTaskId">
        <input type="submit">
    </form>
</body>
</html>