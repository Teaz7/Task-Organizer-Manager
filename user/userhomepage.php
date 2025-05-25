<?php 
    include '../init.php';
    include '../functions/fetchUserId.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../style.css"> 
</head>
<body>
    <div class="heading">
        <h1>TOM: Task Organizer Manager</h1><br><br>
    </div>
    <div>
       <div class="user-profile">
            <h2>USER PROFILE</h2><br>
            <table>
                <tr><td>Account ID: </td><td><?= $result["USER_Id"] ?></td></tr>
                <tr><td>Username: </td><td><?= $result["USER_FName"]." ".$result["USER_LName"] ?></td></tr>
                <tr><td>Age: </td><td><?= $result["USER_Age"] ?></td></tr>
                <tr><td>Email Address: </td><td><?= $result["USER_Email"] ?></td></tr>
                <tr><td>Occupation: </td><td><?= $result["USER_Occupation"] ?></td></tr>
                <tr><td>Establishment: </td><td><?= $result["USER_Establishment"] ?></td></tr>
                <tr><td>Current Points: </td><td><?= $result["USER_Points"]." Points" ?></td></tr>
            </table>
        </div> 
    </div>

    <div class="button-group">
        <a href="useredit.php">Edit Account Details</a>
        <a href="userremove.php">Remove Account</a><br>
        <a href="../homepage.php">Go Back</a>
        <a href="../index.php">Switch Account</a>
        <a href="../functions/logout.php">Log Out</a>
    </div>
    
    
</body>
</html>