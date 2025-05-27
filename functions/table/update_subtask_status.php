<?php
header('Content-Type: application/json');

require_once '../../init.php'; // Adjust path as needed

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$subtaskId = isset($_POST['subtaskId']) ? intval($_POST['subtaskId']) : 0;
$status = isset($_POST['status']) ? intval($_POST['status']) : 0;
$taskId = isset($_POST['taskId']) ? intval($_POST['taskId']) : 0;

if (!$subtaskId || !$taskId) {
    echo json_encode(['success' => false, 'message' => 'Missing subtask or task ID.']);
    exit;
}

try {
    // Update subtask status
    $stmt = $conn->prepare("UPDATE subtask SET SUBTASK_Status = :status WHERE SUBTASK_Id = :subtaskId AND TASK_Id = :taskId");
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->bindParam(':subtaskId', $subtaskId, PDO::PARAM_INT);
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
    $stmt->execute();

    // Recalculate progress for the parent task
    $totalStmt = $conn->prepare("SELECT COUNT(*) FROM subtask WHERE TASK_Id = :taskId");
    $totalStmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
    $totalStmt->execute();
    $total = $totalStmt->fetchColumn();

    $completedStmt = $conn->prepare("SELECT COUNT(*) FROM subtask WHERE TASK_Id = :taskId AND SUBTASK_Status = 1");
    $completedStmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
    $completedStmt->execute();
    $completed = $completedStmt->fetchColumn();

    $progress = ($total > 0) ? round(($completed / $total) * 100) : 0;

    // Update task progress
    $updateTask = $conn->prepare("UPDATE task SET TASK_Progress = :progress WHERE TASK_Id = :taskId");
    $updateTask->bindParam(':progress', $progress, PDO::PARAM_INT);
    $updateTask->bindParam(':taskId', $taskId, PDO::PARAM_INT);
    $updateTask->execute();

    echo json_encode(['success' => true, 'newProgress' => $progress]);
} catch (PDOException $e) {
    error_log("Error updating subtask status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}