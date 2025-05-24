<?php
    include 'init.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_SESSION["currentUserEmail"];
        $password = $_POST["loginPassword"];

        try {
            $stmt = $conn->prepare(
                "SELECT * FROM user WHERE USER_Email = :email"
            );
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (($user['USER_Email'] == $email) && ($user['USER_Password'] === $password)) {
                $stmt = $conn -> prepare(
                    "UPDATE user SET USER_LoginStatus=1 WHERE USER_Email=:email"
                );
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                header('Location: homepage.php');
            } else {
                echo "Invalid Username or Password";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e;
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
                <tr><td>Email: </td><td><input type="text" name="loginEmail" value="<?= $_SESSION["currentUserEmail"] ?>" readonly></td></tr>
                <tr><td>Password: </td><td><input type="password" name="loginPassword" required></td></tr>
            </table>
            <div class="form-submit"><input type="submit" value="Login"></div>
        </form>

        <a href="index.php">Choose Another Account</a>
        <a href="signup.php">Sign Up</a>
    </div>
</body>
</html>