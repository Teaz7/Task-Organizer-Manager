<?php
    include '../init.php';

    $_SESSION["currentUserEmail"] = $_GET['email'];
    $stmt = $conn -> prepare(
        "SELECT * FROM user WHERE USER_Email = :email"
    );
    $stmt -> bindParam(":email", $_SESSION["currentUserEmail"]);
    $stmt -> execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['USER_LoginStatus'] == 1) {
        header('Location: ../homepage.php');
    } else {
        header('Location: ../login.php');
    }
?>