<?php 
    include 'init.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($_POST["signUpPassword"] == $_POST["confirmPassword"]) {
            $fName = $_POST["signUpFName"];
            $lName = $_POST["signUpLName"];
            $password = $_POST["signUpPassword"];
            $age = $_POST["signUpAge"];
            $email = $_POST["signUpEmail"];
            $gender = $_POST["signUpGender"];
            $occupation = $_POST["signUpOccupation"];
            $establishment = $_POST["signUpEstablishment"];

            $stmt = $conn -> prepare(
                "INSERT INTO user
                (USER_FName, USER_LName, USER_Password, USER_Age, USER_Email, USER_Gender, USER_Occupation, USER_Establishment)
                VALUES
                (:val1, :val2, :val3, :val4, :val5, :val6, :val7, :val8);"
            );

            $stmt -> bindParam(":val1", $fName);
            $stmt -> bindParam(":val2", $lName);
            $stmt -> bindParam(":val3", $password);
            $stmt -> bindParam(":val4", $age);
            $stmt -> bindParam(":val5", $email);
            $stmt -> bindParam(":val6", $gender);
            $stmt -> bindParam(":val7", $occupation);
            $stmt -> bindParam(":val8", $establishment);

            $stmt -> execute();
            $userId = $conn->lastInsertId();

            $stmt = $conn -> prepare(
                "INSERT INTO store
                (STORE_NumOfProducts, USER_Id) VALUES
                (:val1, :val2);"
            );
            $numOfProducts = 0;
            $stmt -> bindParam(":val1", $numOfProducts);
            $stmt -> bindParam(":val2", $userId);
            $stmt -> execute();

            $stmt = $conn -> prepare(
                "INSERT INTO calendar
                (CAL_NumOfTask, USER_Id) VALUES
                (:val1, :val2);"
            );
            $numOfTasks = 0;
            $stmt -> bindParam(":val1", $numOfTasks);
            $stmt -> bindParam(":val2", $userId);
            $stmt -> execute();
            
            header('Location: index.php');
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="input-form">
        <h2>TOM: Task Organizer Manager</h2>
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
            <table>
                <tr><td>First Name: </td><td><input type="text" name="signUpFName" required></td></tr>
                <tr><td>Last Name: </td><td><input type="text" name="signUpLName" required></td></tr>
                <tr><td>User's Age: </td><td><input type="number" name="signUpAge" required></td></tr>
                <tr><td>Email Address: </td><td><input type="email" name="signUpEmail" required></td></tr>
                <tr><td>Gender: </td><td><input type="text" name="signUpGender" required></td></tr>
                <tr><td>Occupation: </td><td><input type="text" name="signUpOccupation" required></td></tr>
                <tr><td>Establishment: </td><td><input type="text" name="signUpEstablishment" required></td></tr>
                <tr><td>Password: </td><td><input type="password" name="signUpPassword" required></td></tr>
                <tr><td>Confirm Password: </td><td><Input type="password" name="confirmPassword" required></td></tr>
            </table>
            <div class="form-submit"><input type="submit" value="Create Account"></div>
        </form>

        <a href="index.php">Go Back</a>    
    </div>
</body>
</html>