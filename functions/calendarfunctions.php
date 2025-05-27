<?php

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task_id'])) {
    $deleteId = intval($_POST['delete_task_id']);
    $stmt = $conn->prepare("DELETE FROM task WHERE TASK_Id = :id AND USER_Id = :user");
    $stmt->bindParam(":id", $deleteId);
    $stmt->bindParam(":user", $id);
    $stmt->execute();
    header("Location: calendar.php?month=" . ($_GET['month'] ?? date('m')) . "&year=" . ($_GET['year'] ?? date('Y')));
    exit;
}

// Handle edit request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task_id'])) {
    $editId = intval($_POST['edit_task_id']);
    $name = $_POST['edit_task_name'];
    $desc = $_POST['edit_task_desc'];
    $start = $_POST['edit_task_start'];
    $finish = $_POST['edit_task_finish'];
    $prio = isset($_POST['edit_task_priority']) ? 'on' : '';
    $stmt = $conn->prepare("UPDATE task SET TASK_Name=:name, TASK_Description=:desc, TASK_DateStart=:start, TASK_DateFinish=:finish, TASK_PriorityClass=:prio WHERE TASK_Id=:id AND USER_Id=:user");
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":desc", $desc);
    $stmt->bindParam(":start", $start);
    $stmt->bindParam(":finish", $finish);
    $stmt->bindParam(":prio", $prio);
    $stmt->bindParam(":id", $editId);
    $stmt->bindParam(":user", $id);
    $stmt->execute();
    header("Location: calendar.php?month=" . ($_GET['month'] ?? date('m')) . "&year=" . ($_GET['year'] ?? date('Y')));
    exit;
}

// Handle add task request (from modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task_date'])) {
    $dateStart = $_POST["add_task_date_start"];
    $dateEnd = $_POST["add_task_date_end"];
    if ($dateEnd >= $dateStart) {
        $taskName = $_POST["add_task_name"];
        $taskDesc = $_POST["add_task_desc"];
        $taskPrio = isset($_POST["add_task_priority"]) ? 'on' : '';
        $taskStatus = "Unfinished";
        $taskProgress = 0;

        // Get CAL_Id for this user
        $stmt = $conn->prepare("SELECT CAL_Id FROM calendar WHERE USER_Id=:uid LIMIT 1");
        $stmt->bindParam(":uid", $id);
        $stmt->execute();
        $cal = $stmt->fetch(PDO::FETCH_ASSOC);
        $calId = $cal ? $cal['CAL_Id'] : null;

        $stmt = $conn->prepare(
            "INSERT INTO task 
            (TASK_Name, TASK_Description, TASK_PriorityClass, TASK_Status, TASK_DateStart, TASK_DateFinish, TASK_Progress, CAL_Id, USER_Id)
            VALUES
            (:val1, :val2, :val3, :val4, :val5, :val6, :val7, :val8, :val9)"
        );
        $stmt->bindParam(":val1", $taskName);
        $stmt->bindParam(":val2", $taskDesc);
        $stmt->bindParam(":val3", $taskPrio);
        $stmt->bindParam(":val4", $taskStatus);
        $stmt->bindParam(":val5", $dateStart);
        $stmt->bindParam(":val6", $dateEnd);
        $stmt->bindParam(":val7", $taskProgress);
        $stmt->bindParam(":val8", $calId);
        $stmt->bindParam(":val9", $id);
        $stmt->execute();
    }
    header("Location: calendar.php?month=" . ($_GET['month'] ?? date('m')) . "&year=" . ($_GET['year'] ?? date('Y')));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reminder'])) {
    $reminderText = $_POST["reminder_text"];
    $reminderDate = $_POST["reminder_date"];
    $taskId = $_POST["task_id"];

    $stmt = $conn->prepare(
        "INSERT INTO reminder (REMIND_Text, REMIND_Date, TASK_Id) VALUES (:val1, :val2, :val3)"
    );
    $stmt->bindParam(":val1", $reminderText);
    $stmt->bindParam(":val2", $reminderDate);
    $stmt->bindParam(":val3", $taskId);
    $stmt->execute();

    header("Location: calendar.php?month=" . ($_GET['month'] ?? date('m')) . "&year=" . ($_GET['year'] ?? date('Y')));
    exit;
}


?>