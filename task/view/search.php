<?php
    $stmt = $conn -> prepare ("SELECT * FROM task WHERE TASK_Id = :val1");
    $stmt -> execute([':val1' => "%$search%"]);
    $result = $stmt -> fetchAll();
    if (empty($result)) {
        echo "No results found";
        exit;
    }
    include 'tablehead.php';
    ob_start();
    foreach ($result as $res) {
        $id = $res['TASK_Id'];
        include 'results.php';
        echo ob_get_clean();
    }
    include 'tabletail.php';
?>