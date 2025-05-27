<?php
/**
 * File: render_task_table.php (or similar, like getTable.php)
 * Description: Renders the HTML table for tasks and their subtasks.
 *
 * Assumptions:
 * - $conn: A valid PDO database connection object is available.
 * - $results: An array of task data (fetched from the database for the current user) is available.
 * Each element in $results is an associative array representing a task.
 * - Session is started if user-specific data is needed beyond what's in $results.
 * - init.php (or equivalent for DB connection) has been included.
 */

// Ensure $conn and $results are available. If this script is called directly or via AJAX,
// you would typically include 'init.php' and fetch $results here based on user_id and any filters.
// For example:
// session_start();
// include '../../init.php'; // Adjust path as needed
// if (!isset($_SESSION['user_id'])) { die("User not authenticated."); }
// $loggedInUserId = $_SESSION['user_id'];
// $stmt = $conn->prepare("SELECT * FROM task WHERE USER_Id = :userId ORDER BY TASK_Id DESC");
// $stmt->bindParam(":userId", $loggedInUserId, PDO::PARAM_INT);
// $stmt->execute();
// $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($conn) || !isset($results)) {
    die("Database connection or task data is not available.");
}
?>
<head>
    <link rel="stylesheet" href="tasktable.css">
</head>

<div class="task-container">
    <?php if (empty($results)): ?>
        <div class="task-wrapper">
        <div class="no-tasks">No tasks found.</div>
        
    <?php else: ?>
        <?php foreach ($results as $res): 
            $taskId = $res['TASK_Id'];
            $progress = (int)$res['TASK_Progress'];
            $taskPrio = empty($res['TASK_PriorityClass']) ? "Default" : htmlspecialchars($res['TASK_PriorityClass']);

            // Fetch subtasks
            $subtasks = [];
            try {
                $subtaskStmt = $conn->prepare("SELECT SUBTASK_Id, SUBTASK_Name, SUBTASK_Status FROM subtask WHERE TASK_Id = :taskId ORDER BY SUBTASK_Id ASC");
                $subtaskStmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
                $subtaskStmt->execute();
                $subtasks = $subtaskStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error fetching subtasks: " . $e->getMessage());
            }
        ?>
        <div class="task-card">
    <div class="progress-ring">
        <svg class="progress-ring-circle" width="60" height="60">
            <circle cx="30" cy="30" r="26" stroke="#eee" stroke-width="6" fill="none"/>
            <circle class="progress-circle" cx="30" cy="30" r="26" stroke="#28a745" stroke-width="6" fill="none"
                    stroke-dasharray="163.36" stroke-dashoffset="<?= 163.36 - 163.36 * $progress / 100 ?>" />
            <text x="30" y="35" text-anchor="middle" fill="#000" font-size="14"><?= $progress ?>%</text>
        </svg>
    </div>
    <div class="task-details">
        <h3><?= htmlspecialchars($res['TASK_Name']) ?></h3>
        <p><strong>Priority:</strong> <?= ($res['TASK_PriorityClass'] === 'on') ? 'Yes' : 'No' ?></p> <?= htmlspecialchars($res['TASK_PriorityClass']) ?></small> -->
        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($res['TASK_Description'])) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($res['TASK_Status']) ?></p>
        <p><strong>Start:</strong> <?= htmlspecialchars($res['TASK_DateStart']) ?> |
           <strong>Finish:</strong> <?= htmlspecialchars($res['TASK_DateFinish']) ?></p>

        <?php if (!empty($subtasks)): ?>
            <ul class="subtask-list">
                <?php foreach ($subtasks as $sub): ?>
                <li>
                    <input type="checkbox"
                        class="subtask-checkbox"
                        id="subtask-check-<?= $sub['SUBTASK_Id'] ?>"
                        data-subtask-id="<?= $sub['SUBTASK_Id'] ?>"
                        data-task-id="<?= $taskId ?>"
                        <?= !empty($sub['SUBTASK_Status']) ? 'checked' : '' ?>>
                    <label for="subtask-check-<?= $sub['SUBTASK_Id'] ?>">↳ <?= htmlspecialchars($sub['SUBTASK_Name']) ?></label>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form class="add-subtask-form" data-task-id="<?= $taskId ?>">
            <input type="text" name="subtaskName" placeholder="Add subtask..." required>
            <button type="submit">➕</button>
        </form>
        <a href="functions/markAsDone.php?id=<?= $taskId ?>">Mark As Done</a>
</div>

 
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>
</div>
<script>
// This script block would ideally be part of the main page (e.g., homepage.php)
// and use event delegation if the table content is dynamically reloaded (e.g., by search/sort).
// If this table is always static or reloaded with its script, this direct binding is okay.

// Function to attach event listeners. Call this if you dynamically reload the table.
function initializeTaskTableInteractions(containerSelector) {
    const container = document.querySelector(containerSelector) || document;

    // Handle subtask status checkbox changes
    container.querySelectorAll('.subtask-checkbox').forEach(checkbox => {
        // Prevent adding multiple listeners if this function is called multiple times on same elements
        if (checkbox.dataset.listenerAttached === 'true') return;

        checkbox.addEventListener('change', async function() {
            const subtaskId = this.dataset.subtaskId;
            const taskId = this.dataset.taskId;
            const isChecked = this.checked ? 1 : 0;
            
            this.disabled = true; // Prevent double clicks

            try {
                const response = await fetch('functions/table/updateSubtask.php', { // Adjusted path to match your folder structure
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `subtaskId=${encodeURIComponent(subtaskId)}&status=${encodeURIComponent(isChecked)}&taskId=${encodeURIComponent(taskId)}`
                });
                let result;
                try {
                    result = await response.json();
                } catch (e) {
                    alert('Server did not return valid JSON. Check updateSubtask.php output.');
                    console.error('Invalid JSON from server:', await response.text());
                    this.checked = !isChecked;
                    this.disabled = false;
                    return;
                }

                if (result.success) {
                    const progressCell = document.querySelector(`.task-progress[data-task-id="${taskId}"]`);
                    if (progressCell) {
                        progressCell.textContent = result.newProgress + '%';
                    }
                } else {
                    alert('Failed to update subtask status: ' + (result.message || 'Unknown error'));
                    this.checked = !isChecked; // Revert checkbox on failure
                }
            } catch (error) {
                alert('Error updating subtask status. Check console.');
                console.error('Update subtask error:', error);
                this.checked = !isChecked; // Revert checkbox on error
            } finally {
                this.disabled = false; // Re-enable checkbox
            }
        });
        checkbox.dataset.listenerAttached = 'true';
    });

    // Handle "Add Subtask" form submissions
    container.querySelectorAll('.add-subtask-form').forEach(form => {
        if (form.dataset.listenerAttached === 'true') return;

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const taskId = this.dataset.taskId;
            const input = this.querySelector('input[name="subtaskName"]');
            const subtaskName = input.value.trim();
            const submitButton = this.querySelector('button[type="submit"]');

            if (!subtaskName) {
                alert("Subtask name cannot be empty.");
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Adding...';

            try {
                const formData = new FormData(); // FormData is good for file uploads, but for simple fields, URL-encoded is also fine.
                                                // The backend addSubtask.php uses $_POST, so FormData works.
                formData.append('taskId', taskId);
                formData.append('subtaskName', subtaskName);

                const response = await fetch('functions/table/addSubtask.php', { // Ensure this path is correct
                    method: 'POST',
                    body: formData
                });
                const text = await response.text(); // addSubtask.php returns plain text

                if (response.ok && text.toLowerCase().includes('subtask added')) {
                    // alert(text); // Optional: show success message from server
                    location.reload(); // Reload the page to see the new subtask and updated progress
                } else {
                    alert('Failed to add subtask: ' + text);
                }
            } catch (error) {
                alert('Error adding subtask. Check console.');
                console.error('Add subtask error:', error);
            } finally {
                submitButton.disabled = false;
                if (submitButton) submitButton.textContent = '➕';
                input.value = ''; // Clear input field
            }
        });
        form.dataset.listenerAttached = 'true';
    });
}

// If this script is part of a dynamically loaded content,
// call initializeTaskTableInteractions('#idOfYourTableContainer') after loading.
// If it's on the main page load, you can call it directly:
document.addEventListener('DOMContentLoaded', () => {
    initializeTaskTableInteractions('body'); // Or a more specific container for your table
});
</script>