<?php include '../init.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dateStart = $_POST["AddTaskDateStart"];
        $dateEnd = $_POST["AddTaskDateEnd"];
        if ($dateEnd > $dateStart) {
            $taskName = $_POST["AddTaskName"];
            $taskDesc = $_POST["AddTaskDescription"];
            $taskPrio = $_POST["AddTaskPriority"];

            $stmt = $conn -> prepare(
                "INSERT INTO task 
                (TASK_Name, TASK_Description, TASK_PriorityClass, TASK_Status, TASK_DateStart, TASK_DateFinish, TASK_Progress, CAL_Id, USER_Id)
                VALUES
                (:val1, :val2, :val3, :val4, :val5, :val6, :val7, 
                (SELECT CAL_Id from calendar WHERE USER_Id=:val8),
                (SELECT USER_Id from user WHERE USER_Email=:val9));
                "
            );
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