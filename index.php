<?php
    include 'init.php';
    $_SESSION["currentUserEmail"] = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST["loginEmail"];
        $password = $_POST["loginPassword"];

        try {
            $stmt = $conn->prepare(
                "SELECT * FROM user WHERE USER_Email = :email"
            );
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (($user['USER_Email'] == $email) && ($user['USER_Password'] === $password)) {
                $_SESSION["currentUserEmail"] = $email;
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
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
        Email: <input type="text" name="loginEmail" required> <br>
        Password: <input type="password" name="loginPassword" required> <br>
        <input type="submit">
    </form>

    <a href="signup.php">Sign Up</a>
</body>
</html>