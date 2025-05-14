<?php 
    $email = $_SESSION["currentUserEmail"];

    $stmt = $conn -> prepare("SELECT * FROM user WHERE USER_Email = :email");
    $stmt -> bindParam(":email", $email);
    $stmt -> execute();
    $result = $stmt -> fetch(PDO::FETCH_ASSOC);
    $id = $result["USER_Id"];
?>