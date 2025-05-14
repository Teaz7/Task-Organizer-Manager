<?php 
    include '../init.php';
    include 'function/fetchUserId.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div>
        <h3>USER PROFILE</h3>
        ID: <?= $result["USER_Id"] ?> <br>
        Username: <?= $result["USER_FName"]." ".$result["USER_LName"] ?> <br>
        Age: <?= $result["USER_Age"] ?> <br>
        Email Address: <?= $result["USER_Email"] ?> <br>
        Occupation: <?= $result["USER_Occupation"] ?> <br>
        Establishment: <?= $result["USER_Establishment"] ?> <br>
        Current Points: <?= $result["USER_Points"] ?> <br><br>
    </div>
    <a href="useredit.php">Edit Account Details</a> <br>
    <a href="userremove.php">Remove Account</a> <br><br>
    <a href="../homepage.php">Go Back</a> <br>
    <a href="../index.php">Log Out</a> <br>
</body>
</html>