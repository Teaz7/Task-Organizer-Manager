<?php
    include '../../init.php';
    include '../../user/function/fetchUserId.php';
    $search = $_GET['keyword'];
    $stmt = $conn -> prepare (
        "SELECT * FROM task WHERE TASK_Name LIKE :val1 AND USER_Id = :id"
    );
    $stmt -> execute([':val1' => "%$search%", ':id' => $id]);
    $result = $stmt -> fetchAll();
    if (empty($result)) {
        echo "No results found";
        exit;
    }
    include 'tablehead.php';
    ob_start();
    foreach ($result as $res) { ?>
        <tr>
            <td><?= $res['TASK_Id'] ?></td>
            <td><?= $res['TASK_Name'] ?></td>
            <td><?= $res['TASK_Status'] ?></td>
            <td><?= $res['TASK_DateStart'] ?></td>
            <td><?= $res['TASK_DateFinish'] ?></td>
            <td><?= $res['TASK_Progress'] ?></td>
        </tr>
        <?php echo ob_get_clean();
    }
    include 'tabletail.php';
?>