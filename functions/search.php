<?php
    include '../init.php';
    include 'fetchUserId.php';

    $search = $_GET['keyword'];
    $type = $_GET['type'];
    $searchfilter = "%$search%";
    $columns = [
        'searchID' => 'TASK_Id',
        'searchName' => 'TASK_Name',
        'searchDescription' => 'TASK_Description',
        'searchDateStart' => 'TASK_DateStart',
        'searchDateEnd' => 'TASK_DateFinish'
    ];
    $column = $columns[$type] ?? 'TASK_Name';

    $stmt = $conn -> prepare(
        "SELECT * FROM task WHERE USER_Id = :id AND $column LIKE :search"
    );
    $stmt -> bindParam(":id", $id);
    $stmt -> bindParam(":search", $searchfilter);
    $stmt -> execute();

    $result = $stmt -> fetchAll();
    if (empty($result)) {
        echo "No results found";
        exit;
    }
?>

<table>
    <thead>
        <tr>
            <th>Task ID</th>
            <th>Task Name</th>
            <th>Task Description</th>
            <th>Task Priority Class</th>
            <th>Task Status</th>
            <th>Task Date Start</th>
            <th>Task Date End</th>
            <th>Task Progress</th>
        </tr>
    </thead>
    <tbody>

<?php
    ob_start();
    foreach ($result as $res) { 
        $taskPrio = "";
            if (is_null($res['TASK_PriorityClass'])) {
                $taskPrio = "Unprioritized";
            } else {
                $taskPrio = "Prioritized";
            }
?>
        <tr>
            <td><?= $res['TASK_Id'] ?></td>
            <td><?= $res['TASK_Name'] ?></td>
            <td><?= $res['TASK_Description'] ?></td>
            <td><?= $taskPrio ?></td>
            <td><?= $res['TASK_Status'] ?></td>
            <td><?= $res['TASK_DateStart'] ?></td>
            <td><?= $res['TASK_DateFinish'] ?></td>
            <td><?= $res['TASK_Progress'] ?></td>
        </tr>
<?php } echo ob_get_clean(); ?>
    </tbody>
</table>