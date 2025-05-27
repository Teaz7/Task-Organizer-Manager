<?php
include '../../init.php';

$taskId = $_POST['taskId'] ?? null;
$subtaskName = trim($_POST['subtaskName'] ?? '');

if ($taskId && $subtaskName !== '') {
    // Insert new subtask
    $stmt = $masterConn->prepare("INSERT INTO subtask (TASK_Id, SUBTASK_Name) VALUES (:taskId, :name)");
    $stmt->execute([
        ':taskId' => $taskId,
        ':name' => $subtaskName
    ]);

    // Recalculate progress
    $countStmt = $slaveConn->prepare("SELECT COUNT(*) AS total, SUM(SUBTASK_Status) AS done FROM subtask WHERE TASK_Id = :taskId");
    $countStmt->execute([':taskId' => $taskId]);
    $row = $countStmt->fetch(PDO::FETCH_ASSOC);

    $total = $row['total'] ?? 0;
    $done = $row['done'] ?? 0;
    $progress = $total > 0 ? round(($done / $total) * 100) : 0;

    // Update task progress
    $updateTask = $masterConn->prepare("UPDATE task SET TASK_Progress = :progress WHERE TASK_Id = :taskId");
    $updateTask->execute([':progress' => $progress, ':taskId' => $taskId]);

    echo "Subtask added and progress updated.";
} else {
    http_response_code(400);
    echo "Invalid input.";
}
