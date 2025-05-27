<?php
    include 'init.php';
    $_SESSION["currentUserEmail"] = "";

    $stmt = $slaveConn -> prepare(
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
    <title>User Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styleIndex.css">
</head>
<body>
    <div class="dashboard">
        <h2 class="section-title">Choose an Account</h2>
        <?php
            $loginStatus = "";
            foreach($result as $res) { 
                if ($res['USER_LoginStatus'] == 1) {
                    $loginStatus = "Logged In";
                } else {
                    $loginStatus = "Logged Out";
                }
        ?>
            <a class="user-card" href="functions/checkLogin.php?email=<?= $res['USER_Email'] ?>">
                <div class="user-info">
                    <?= $res['USER_FName']." ".$res['USER_LName'] ?><br>
                    <?= $res['USER_Email'] ?>
                </div>
                <div class="status"><?= $loginStatus ?></div>
            </a>
        <?php } ?>
        <div class="signup-container">
            <a class="signup-link" href="signup.php">Sign Up</a>
        </div>

    </div>
</body>
</html>
