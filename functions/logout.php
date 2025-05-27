<?php
    include '../init.php';
    $stmt = $masterConn -> prepare(
        "UPDATE user SET USER_LoginStatus=0 WHERE USER_Email=:email"
    );
    $stmt->bindParam(':email', $_SESSION["currentUserEmail"]);
    $stmt->execute();

    header('Location: ../index.php');
?>