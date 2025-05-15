<?php
include 'init.php';
include 'user/function/fetchUserId.php';

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
$stmt = $conn->prepare("SELECT * FROM task WHERE USER_Id = :id");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calendar</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; width: 14.28%; vertical-align: top; padding: 5px; height: 100px; }
        th { background: #eee; }
        .calendar-nav { margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
        .calendar-nav select, .calendar-nav button { font-size: 1em; }
        .today-cell { background: #ffe082 !important; border: 2px solid #ff9800 !important; }
        .priority-cell { background: #ffcccc !important; }
        .modal-bg {
            display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.4); z-index: 1000; justify-content: center; align-items: center;
        }
        .modal {
            background: #fff; padding: 20px; border-radius: 8px; min-width: 300px; max-width: 90vw;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .modal-close { float: right; cursor: pointer; font-size: 1.2em; }
        .task-desc-list { margin: 0; padding: 0 0 0 20px; }
        .task-detail { margin-bottom: 15px; }
        .task-label { font-weight: bold; }
        .task-actions { margin-top: 5px; }
        .edit-form input[type="text"], .edit-form input[type="date"] { width: 90%; margin-bottom: 5px; }
        .add-form input[type="text"], .add-form input[type="date"] { width: 90%; margin-bottom: 5px; }
        .add-task-btn { margin-top: 10px; }
    </style>
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
                for (var i = 0; i < details.length; i++) {
                    var t = details[i];
                    html += '<div class="task-detail" id="task-detail-'+t.id+'">';
                    html += '<div><span class="task-label">Task:</span> ' + escapeHtml(t.name) + '</div>';
                    html += '<div><span class="task-label">Description:</span> ' + escapeHtml(t.desc) + '</div>';
                    html += '<div><span class="task-label">Start Date:</span> ' + escapeHtml(t.start) + '</div>';
                    html += '<div><span class="task-label">Finish Date:</span> ' + escapeHtml(t.finish) + '</div>';
                    html += '<div><span class="task-label">Priority:</span> ' + (t.priority === "on" ? "Yes" : "No") + '</div>';
                    html += '<div class="task-actions">';
                    html += '<button onclick="showEditForm('+t.id+',\''+escapeJs(t.name)+'\',\''+escapeJs(t.desc)+'\',\''+t.start+'\',\''+t.finish+'\',\''+t.priority+'\')">Edit</button> ';
                    html += '<form method="POST" style="display:inline" onsubmit="return confirm(\'Delete this task?\')">';
                    html += '<input type="hidden" name="delete_task_id" value="'+t.id+'">';
                    html += '<button type="submit">Delete</button>';
                    html += '</form>';
                    html += '</div>';
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
        function escapeHtml(text) {
            var map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        function escapeJs(text) {
            return String(text).replace(/'/g,"\\'").replace(/"/g,'\\"');
        }
    </script>
</head>
<body>
    <h2><?= date('F Y', strtotime("$year-$month-01")) ?> Task Calendar</h2>
    <a href="homepage.php">
        <button type="button">Return to Home</button>
    </a>

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
    </div>

    <table>
        <thead>
            <tr>
                <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th>
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