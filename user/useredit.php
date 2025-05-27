<?php
    include '../init.php';
    include '../functions/fetchUserId.php';

    $fName = $result["USER_FName"];
    $lName = $result["USER_LName"];
    $age = $result["USER_Age"];
    $email = $result["USER_Email"];
    $gender = $result["USER_Gender"];
    $occupation = $result["USER_Occupation"];
    $establishment = $result["USER_Establishment"];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fName = $_POST["editFName"];
        $lName = $_POST["editLName"];
        $age = $_POST["editAge"];
        $email = $_POST["editEmail"];
        $gender = $_POST["editGender"];
        $occupation = $_POST["editOccupation"];
        $establishment = $_POST["editEstablishment"];

        $stmt = $masterConn -> prepare(
            "UPDATE user SET USER_FName = :val1, USER_LName = :val2, USER_Age = :val3, USER_Email = :val4, 
            USER_Gender = :val5, USER_Occupation = :val6, USER_Establishment = :val7 WHERE USER_Id = :val8"
        );
        $stmt->bindParam(":val1", $fName);
        $stmt->bindParam(":val2", $lName);
        $stmt->bindParam(":val3", $age);
        $stmt->bindParam(":val4", $email);
        $stmt->bindParam(":val5", $gender);
        $stmt->bindParam(":val6", $occupation);
        $stmt->bindParam(":val7", $establishment);
        $stmt->bindParam(":val8", $id);
        $stmt->execute();

        header('Location: userhomepage.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../styleUser.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="input-form">
        <h2>TOM: Task Organizer Manager</h2>
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
            <table>
                <tr><td>First Name: </td><td><input type="text" name="editFName" value="<?= $fName ?>" required></td></tr>
                <tr><td>Last Name: </td><td><input type="text" name="editLName" value="<?= $lName ?>" required></td></tr>
                <tr><td>User's Age: </td><td><input type="number" name="editAge" value="<?= $age ?>" required></td></tr>
                <tr><td>Email Address: </td><td><input type="email" name="editEmail" value="<?= $email ?>" required></td></tr>
                <tr><td>Gender: </td><td><input type="text" name="editGender" value="<?= $gender ?>" required></td></tr>
                <tr><td>Occupation: </td><td><input type="text" name="editOccupation" value="<?= $occupation ?>" required></td></tr>
                <tr><td>Establishment: </td><td><input type="text" name="editEstablishment" value="<?= $establishment ?>" required></td></tr>
            </table>
            <div class="form-submit"><input type="submit" value="Update Account"></div>
        </form>

        <a href="userhomepage.php">Go Back</a>    
    </div>
</body>
</html>