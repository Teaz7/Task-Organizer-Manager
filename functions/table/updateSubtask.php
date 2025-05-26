<?php
include '../../init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$subtaskId = $_POST['subtaskId'] ?? null;
$status = isset($_POST['status']) ? (int)$_POST['status'] : null;
$taskId = $_POST['taskId'] ?? null;

if ($subtaskId && $taskId !== null && $status !== null) {
    try {
        // Update subtask status
        $stmt = $conn->prepare("UPDATE subtask SET SUBTASK_Status = :status WHERE SUBTASK_Id = :id");
        $stmt->execute([':status' => $status, ':id' => $subtaskId]);

        // Recalculate task progress
        $countStmt = $conn->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(SUBTASK_Status),0) AS done FROM subtask WHERE TASK_Id = :taskId");
        $countStmt->execute([':taskId' => $taskId]);
        $row = $countStmt->fetch(PDO::FETCH_ASSOC);

        $total = (int)($row['total'] ?? 0);
        $done = (int)($row['done'] ?? 0);
        $progress = $total > 0 ? round(($done / $total) * 100) : 0;

        // Update task progress
        $updateTask = $conn->prepare("UPDATE task SET TASK_Progress = :progress WHERE TASK_Id = :taskId");
        $updateTask->execute([':progress' => $progress, ':taskId' => $taskId]);

        echo json_encode([
            'success' => true,
            'newProgress' => $progress
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Missing parameters'
    ]);
}
?>
