<?php
    include '../init.php';
    include '../functions/fetchUserId.php';
    
    //1: purchases + reminder + reward
    //2: task(remove first) + store + calendar
    //3: user
    
    $stmt = $masterConn -> prepare(
        "DELETE FROM purchases WHERE USER_Id = :id;
                DELETE FROM reminder WHERE TASK_Id IN (
                    SELECT TASK_Id FROM task WHERE USER_Id = :id
                );
                DELETE FROM reward WHERE USER_Id = :id;
                
                DELETE FROM task WHERE USER_Id = :id;
                DELETE FROM store WHERE USER_Id = :id;
                DELETE FROM calendar WHERE USER_Id = :id;
                
                DELETE FROM user WHERE USER_Id = :id;"
    );
    $stmt -> bindParam(":id", $id);
    $stmt -> execute();

    header('Location: ../index.php');
?>