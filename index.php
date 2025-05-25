<?php
    include 'init.php';
    $_SESSION["currentUserEmail"] = "";

    $stmt = $conn -> prepare(
        "SELECT * FROM user"
    );
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        $loginStatus = "";
        foreach($result as $res) { 
            if ($res['USER_LoginStatus'] == 1) {
                $loginStatus = "Logged In";
            } else {
                $loginStatus = "Logged Out";
            }
    ?>
        <a href="functions/checkLogin.php?email=<?= $res['USER_Email'] ?>">
            <div>
                <div>
                    <?= $res['USER_FName']." ".$res['USER_LName'] ?> <br>
                    <?= $res['USER_Email'] ?> <br>    
                </div>
                <div>
                    <?= $loginStatus ?>    
                </div>
            </div> <br>
        </a>
        
    <?php } ?>
</body>
</html>