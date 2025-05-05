<?php
    include 'init.php';

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
</head>
<body>
    
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
        Email: <input type="text" name="loginEmail">
        Password: <input type="password" name="loginPassword">
        <input type="submit">
    </form>

    <a href="signup.php">Sign Up</a>
</body>
</html>