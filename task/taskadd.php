<?php include '../init.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dateStart = $_POST["AddTaskDateStart"];
        $dateEnd = $_POST["AddTaskDateEnd"];
        if ($dateEnd > $dateStart) {
            $taskName = $_POST["AddTaskName"];
            $taskDesc = $_POST["AddTaskDescription"];
            $taskPrio = $_POST["AddTaskPriority"];
            $taskStatus = "Unfinished";
            $taskProgress = 0;

            $stmt = $conn -> prepare(
                "SELECT USER_Id FROM user WHERE USER_Email = :val1;"
            );

            $stmt -> bindParam(":val1", $_SESSION["currentUserEmail"]);
            $stmt -> execute();
            $result = $stmt -> fetch(PDO::FETCH_ASSOC);

            $userId = $result["USER_Id"];

            $stmt = $conn -> prepare(
                "INSERT INTO task 
                (TASK_Name, TASK_Description, TASK_PriorityClass, TASK_Status, TASK_DateStart, TASK_DateFinish, TASK_Progress, CAL_Id, USER_Id)
                VALUES
                (:val1, :val2, :val3, :val4, :val5, :val6, :val7, 
                (SELECT CAL_Id from calendar WHERE USER_Id=:val8), :val9);
                "
            );

            $stmt -> bindParam(":val1", $taskName);
            $stmt -> bindParam(":val2", $taskDesc);
            $stmt -> bindParam(":val3", $taskPrio);
            $stmt -> bindParam(":val4", $taskStatus);
            $stmt -> bindParam(":val5", $dateStart);
            $stmt -> bindParam(":val6", $dateEnd);
            $stmt -> bindParam(":val7", $taskProgress);
            $stmt -> bindParam(":val8", $userId);
            $stmt -> bindParam(":val9", $userId);

            $result = $stmt -> execute();
            header('Location: taskhomepage.php');
        }
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
        Task Name: <input type="text" name="AddTaskName" required>
        Task Description: <input type="text" name="AddTaskDescription" required>
        Task Priority: <input type="checkbox" name="AddTaskPriority" required>
        Task Date Start: <input type="date" name="AddTaskDateStart" required>
        Task Date End: <input type="date" name="AddTaskDateEnd" required>
        <input type="submit">
    </form>
</body>
</html>