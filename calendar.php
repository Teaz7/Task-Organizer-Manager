<?php
include 'init.php';
include 'functions/fetchUserId.php';
include 'functions/calendarfunctions.php';

// Get month and year from GET params, or use current
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Adjust month/year if out of bounds
if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

// Fetch tasks
$stmt = $conn->prepare("SELECT * FROM task WHERE USER_Id = :id AND TASK_Progress < 100");
$stmt->bindParam(":id", $id);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group tasks by date
$taskByDate = [];
$priorityDates = [];
foreach ($tasks as $task) {
    $date = $task['TASK_DateFinish'];
    if (!isset($taskByDate[$date])) {
        $taskByDate[$date] = [];
    }
    $taskByDate[$date][] = $task['TASK_Name'];
    if (!empty($task['TASK_PriorityClass']) && $task['TASK_PriorityClass'] == 'on') {
        $priorityDates[$date] = true;
    }
}

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = date('N', strtotime("$year-$month-01"));

// For navigation
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

$today = date('Y-m-d');
$currentMonth = date('m');
$currentYear = date('Y');

// Prepare task details by date
$taskDetailsByDate = [];
foreach ($tasks as $task) {
    $date = $task['TASK_DateFinish'];
    if (!isset($taskDetailsByDate[$date])) {
        $taskDetailsByDate[$date] = [];
    }
    $taskDetailsByDate[$date][] = [
        'id' => $task['TASK_Id'],
        'name' => $task['TASK_Name'],
        'desc' => $task['TASK_Description'],
        'start' => $task['TASK_DateStart'],
        'finish' => $task['TASK_DateFinish'],
        'priority' => isset($task['TASK_PriorityClass']) ? $task['TASK_PriorityClass'] : 'N/A',
    ];
}

$tomorrow = date('Y-m-d', strtotime('+1 day'));
$stmt = $conn->prepare("SELECT TASK_Name, TASK_DateFinish FROM task WHERE TASK_DateFinish = :tomorrow");
$stmt->bindParam(':tomorrow', $tomorrow);
$stmt->execute();
$tasksDueTomorrow = $stmt->fetchAll(PDO::FETCH_ASSOC);

$reminderTexts = [];

foreach ($tasks as $task) {
    if ($task['TASK_DateFinish'] === $tomorrow) {
        $taskId = $task['TASK_Id'];
        $stmt = $conn->prepare("SELECT REMIND_Text FROM reminder WHERE TASK_Id = :taskid");
        $stmt->bindParam(":taskid", $taskId);
        $stmt->execute();
        $reminders = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($reminders) {
            foreach ($reminders as $reminder) {
                $reminderTexts[] = [
                    'task' => $task['TASK_Name'],
                    'text' => $reminder
                ];
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calendar</title>
    <link rel="stylesheet" href="style.css">
    <script>
        window.taskDetails = <?= json_encode($taskDetailsByDate) ?>;
        function goToMonth() {
            var month = document.getElementById('monthSelect').value;
            var year = document.getElementById('yearSelect').value;
            window.location.href = '?month=' + month + '&year=' + year;
        }

        function goToToday() {
            window.location.href = '?month=<?= $currentMonth ?>&year=<?= $currentYear ?>';
        }

        function showTaskDesc(date) {
            var details = window.taskDetails[date];
            var modal = document.getElementById('modal');
            var modalBg = document.getElementById('modal-bg');
            var html = '';
            if (details && details.length > 0) {
                html += '<div class="task-heading">TASKS</div>';
                for (var i = 0; i < details.length; i++) {
                    var t = details[i];
                    html += '<div class="task-detail" id="task-detail-'+t.id+'">';
                    html += '<div><span class="task-label">Task:</span> ' + escapeHtml(t.name) + '</div>';
                    html += '<div><span class="task-label">Description:</span> ' + escapeHtml(t.desc) + '</div>';
                    html += '<div><span class="task-label">Start Date:</span> ' + escapeHtml(t.start) + '</div>';
                    html += '<div><span class="task-label finish-date">Finish Date:</span> ' + escapeHtml(t.finish) + '</div>';
                    html += '<div><span class="task-label">Priority:</span> ' + (t.priority === "on" ? "Yes" : "No") + '</div>';
                    html += '<div class="task-actions">';
                    html += '<button onclick="showEditForm('+t.id+',\''+escapeJs(t.name)+'\',\''+escapeJs(t.desc)+'\',\''+t.start+'\',\''+t.finish+'\',\''+t.priority+'\')">Edit</button> ';
                    html += '<form method="POST" style="display:inline" onsubmit="return confirm(\'Are you sure you want to delete ' + escapeHtml(t.name) + '?\')">';
                    html += '<input type="hidden" name="delete_task_id" value="'+t.id+'">';
                    html += '<button type="submit">Delete</button>';
                    html += '</form>';
                    html += '<button onclick="showReminderForm(\'' + date + '\', ' + t.id + ')">Add Reminder</button>';
                    html += '</div>';
                    html += '<div id="reminder-form"></div>';
                    html += '<div id="edit-form-'+t.id+'"></div>';
                    html += '</div>';
                }
            } else {
                html = 'No task for this day<br>';
            }
            // Add button to show add form
            html += '<button class="add-task-btn" onclick="showAddForm(\''+date+'\')">Add Task for this day</button>';
            html += '<div id="add-form"></div>';
            document.getElementById('modal-content').innerHTML = html;
            modalBg.style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('modal-bg').style.display = 'none';
        }
        function showEditForm(id, name, desc, start, finish, prio) {
            var html = '';
            html += '<form class="edit-form" method="POST">';
            html += '<input type="hidden" name="edit_task_id" value="'+id+'">';
            html += 'Task Name: <input type="text" name="edit_task_name" value="'+escapeHtml(name)+'" required><br>';
            html += 'Description: <input type="text" name="edit_task_desc" value="'+escapeHtml(desc)+'" required><br>';
            html += 'Start Date: <input type="date" name="edit_task_start" value="'+start+'" required><br>';
            html += 'Finish Date: <input type="date" name="edit_task_finish" value="'+finish+'" required><br>';
            html += 'Priority: <input type="checkbox" name="edit_task_priority" '+(prio==="on"?"checked":"")+'><br>';
            html += '<button type="submit">Save</button> ';
            html += '<button type="button" onclick="hideEditForm('+id+')">Cancel</button>';
            html += '</form>';
            document.getElementById('edit-form-'+id).innerHTML = html;
        }
        function hideEditForm(id) {
            document.getElementById('edit-form-'+id).innerHTML = '';
        }
        function showAddForm(date) {
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            var todayStr = yyyy + '-' + mm + '-' + dd;

            var html = '';
            html += '<form class="add-form" method="POST">';
            html += '<input type="hidden" name="add_task_date" value="1">';
            html += 'Task Name: <input type="text" name="add_task_name" required><br>';
            html += 'Description: <input type="text" name="add_task_desc" required><br>';
            html += 'Start Date: <input type="date" name="add_task_date_start" value="'+todayStr+'" required><br>';
            html += 'End Date: <input type="date" name="add_task_date_end" value="'+date+'" required><br>';
            html += 'Priority: <input type="checkbox" name="add_task_priority"><br>';
            html += '<button type="submit">Add Task</button> ';
            html += '<button type="button" onclick="hideAddForm()">Cancel</button>';
            html += '</form>';
            document.getElementById('add-form').innerHTML = html;
        }
        function hideAddForm() {
            document.getElementById('add-form').innerHTML = '';
        }
        function showReminderForm(taskDate, taskId) {
            var dateObj = new Date(taskDate);
            dateObj.setDate(dateObj.getDate() - 1);

            var yyyy = dateObj.getFullYear();
            var mm = String(dateObj.getMonth() + 1).padStart(2, '0');
            var dd = String(dateObj.getDate()).padStart(2, '0');
            var reminderDate = yyyy + '-' + mm + '-' + dd;

            var html = '';
            html += '<form class="reminder-form" method="POST">';
            html += '<input type="hidden" name="add_reminder" value="1">';
            html += '<input type="hidden" name="task_id" value="' + taskId + '">';
            html += 'Reminder Text: <input type="text" name="reminder_text" required><br>';
            html += 'Reminder Date: <input type="date" name="reminder_date" value="' + reminderDate + '" required><br>';
            html += '<button type="submit">Add Reminder</button> ';
            html += '<button type="button" onclick="hideReminderForm()">Cancel</button>';
            html += '</form>';

            document.getElementById('reminder-form').innerHTML = html;
        }
        function hideReminderForm() {
            document.getElementById('reminder-form').innerHTML = '';
        }
        function escapeHtml(text) {
            var map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        function escapeJs(text) {
            return String(text).replace(/'/g,"\\'").replace(/"/g,'\\"');
        }
        window.remindersDueTomorrow = <?= json_encode($reminderTexts) ?>;
        window.addEventListener('DOMContentLoaded', function () {
            if (window.remindersDueTomorrow && window.remindersDueTomorrow.length > 0) {
                let message = "🔔 Reminders for tasks due tomorrow:\n\n";
                window.remindersDueTomorrow.forEach(item => {
                    message += `• ${item.task}: ${item.text}\n`;
                });
                alert(message);
            }
        });
    </script>
</head>
<body>
    <div class="heading"><div>
        <h1>TOM: Task Organizer Manager</h1>
        <h3><?= strtoupper(date('F Y', strtotime("$year-$month-01"))) ?> TASK CALENDAR</h>

        <div class="calendar-nav">
            <button onclick="window.location.href='?month=<?= $prevMonth ?>&year=<?= $prevYear ?>'">&laquo; Prev</button>
            <select id="monthSelect" onchange="goToMonth()">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $selected = ($m == $month) ? 'selected' : '';
                    echo "<option value='$m' $selected>" . date('F', mktime(0,0,0,$m,1)) . "</option>";
                }
                ?>
            </select>
            <select id="yearSelect" onchange="goToMonth()">
                <?php
                $startYear = $year - 5;
                $endYear = $year + 5;
                for ($y = $startYear; $y <= $endYear; $y++) {
                    $selected = ($y == $year) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
            <button onclick="window.location.href='?month=<?= $nextMonth ?>&year=<?= $nextYear ?>'">Next &raquo;</button>
            <button onclick="goToToday()">Today</button>

            <a href="homepage.php">Return to Home</a>
        </div></div>

    </div>

    <table>
        <thead>
            <tr>
                <th>MONDAY</th><th>TUESDAY</th><th>WEDNESDAY</th><th>THURSDAY</th><th>FRIDAY</th><th>SATURDAY</th><th>SUNDAY</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php
                for ($i = 1; $i < $firstDayOfMonth; $i++) {
                    echo "<td></td>";
                }
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $isToday = ($currentDate === $today);
                    $hasPriority = isset($priorityDates[$currentDate]);
                    $cellClass = '';
                    if ($isToday) {
                        $cellClass = 'today-cell';
                    } elseif ($hasPriority) {
                        $cellClass = 'priority-cell';
                    }
                    echo "<td class='$cellClass' style='cursor:pointer' onclick=\"showTaskDesc('$currentDate')\"><strong>$day</strong><br>";
                    if (isset($taskByDate[$currentDate])) {
                        foreach ($taskByDate[$currentDate] as $taskName) {
                            echo "- " . htmlspecialchars($taskName) . "<br>";
                        }
                    }
                    echo "</td>";
                    if ((($day + $firstDayOfMonth - 1) % 7) == 0) {
                        echo "</tr><tr>";
                    }
                }
                $remainingCells = (7 - ($day + $firstDayOfMonth - 1) % 7) % 7;
                for ($i = 0; $i < $remainingCells; $i++) {
                    echo "<td></td>";
                }
                ?>
            </tr>
        </tbody>
    </table>

    <!-- Modal for task descriptions and add form -->
    <div id="modal-bg" class="modal-bg" onclick="closeModal()">
        <div class="modal" id="modal" onclick="event.stopPropagation()">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div id="modal-content"></div>
        </div>
    </div>
</body>
</html>