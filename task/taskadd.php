<?php include '../init.php'?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
        First Name: <input type="text" name="signUpFName" required>
        Last Name: <input type="text" name="signUpLName" required>
        Age: <input type="number" name="signUpAge" required>
        Email: <input type="email" name="signUpEmail" required>
        Gender: <input type="text" name="signUpGender" required>
        Occupation: <input type="text" name="signUpOccupation" required>
        Establishment: <input type="text" name="signUpEstablishment" required>
        Password: <input type="password" name="signUpPassword" required>
        Confirm Password: <Input type="password" name="confirmPassword" required>
        <input type="submit">
    </form>
</body>
</html>