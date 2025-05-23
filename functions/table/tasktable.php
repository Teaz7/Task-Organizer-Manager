<table>
    <thead>
        <tr>
            <th>Task ID</th>
            <th>Task Name</th>
            <th>Task Description</th>
            <th>Task Priority Class</th>
            <th>Task Status</th>
            <th>Task Date Start</th>
            <th>Task Date Finish</th>
            <th>Task Progress</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($results as $res) { 
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
        <?php } ?>
    </tbody>
</table>