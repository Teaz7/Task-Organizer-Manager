<?php
include 'init.php';

$message = '';
$showPasswordForm = false;
$userIdToReset = null;

$firstName = '';
$lastName = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'confirm_details') {
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($firstName) || empty($lastName) || empty($email)) {
            $message = "Please fill in all fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address format.";
        } else {
            try {
                $stmt = $slaveConn->prepare(
                    "SELECT USER_Id FROM user WHERE BINARY USER_FName = :fname AND BINARY USER_LName = :lname AND BINARY USER_Email = :email"
                );
                $stmt->bindParam(':fname', $firstName);
                $stmt->bindParam(':lname', $lastName);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $_SESSION['user_id_to_reset'] = $user['USER_Id'];
                    $showPasswordForm = true;
                    $message = "Details confirmed! Please enter your new password.";
                } else {
                    $message = "No account found with the exact details provided. Please check your first name, last name, and email, including case and spaces.";
                }

            } catch (PDOException $e) {
                $message = "An error occurred. Please try again later.";
                error_log("Password confirmation error: " . $e->getMessage());
            }
        }
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        if (!isset($_SESSION['user_id_to_reset'])) {
            $message = "Invalid reset request. Please start from the beginning.";
            header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF']));
            exit();
        }

        $userIdToReset = $_SESSION['user_id_to_reset'];
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($newPassword) || empty($confirmPassword)) {
            $message = "Please fill in both new password fields.";
            $showPasswordForm = true;
        } elseif ($newPassword !== $confirmPassword) {
            $message = "Passwords do not match.";
            $showPasswordForm = true;
        } elseif (strlen($newPassword) < 8) {
            $message = "Password must be at least 8 characters long.";
            $showPasswordForm = true;
        } else {
            try {
                // Storing plain-text password
                $plainTextPassword = $newPassword;

                $updateStmt = $masterConn->prepare("UPDATE user SET USER_Password = :plainTextPassword WHERE USER_Id = :userId");
                $updateStmt->bindParam(':plainTextPassword', $plainTextPassword);
                $updateStmt->bindParam(':userId', $userIdToReset);
                $updateStmt->execute();

                $message = "Your password has been successfully reset. You can now log in with your new password.";
                unset($_SESSION['user_id_to_reset']);

                header('Location: login.php?reset=success');
                exit();

            } catch (PDOException $e) {
                $message = "An error occurred during password update. Please try again later.";
                error_log("Password update error: " . $e->getMessage());
                $showPasswordForm = true;
            }
        }
    }
} else {
    unset($_SESSION['user_id_to_reset']);
    $showPasswordForm = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style2.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #202124;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #e8eaed;
        }

        .input-form {
            background: rgba(255, 255, 255, 0.25); 
            width: 400px;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(12px); 
            -webkit-backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }

        h2 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 600;
        }

        p {
            font-size: 14px;
            color: #000;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #000;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: none;
            background-color: #f2f3f4;
            color: #333;
            font-size: 14px;
        }

        .form-submit {
            margin-top: 10px;
        }

        .form-submit input[type="submit"] {
            background-color: ##007bff;
            color: #fff;
            border: none;
            padding: 10px;
            width: 100%;
            font-weight: bold;
            border-radius: 8px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .form-submit input[type="submit"]:hover {
            background-color: #0056b3;
            cursor: pointer;
        }

        a {
            color: #8ab4f8;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-top: 10px;
        }

        a:hover {
            text-decoration: underline;
        }

        .password-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
            font-size: 0.9em;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="input-form">
        <h2>Reset Your Password</h2>

        <?php if ($message): ?>
            <p style="color: <?= (strpos($message, 'successfully reset') !== false) ? 'green' : 'red'; ?>; font-weight: bold;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <?php if (!$showPasswordForm): ?>
            <p>Please enter your exact first name, last name, and email address as you registered them.</p>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="confirm_details">

                <div class="form-group">
                    <label for="firstName">First Name:</label>
                    <input type="text" name="firstName" id="firstName" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name:</label>
                    <input type="text" name="lastName" id="lastName" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="form-submit">
                    <input type="submit" value="Confirm Details">
                </div>
            </form>
        <?php else: ?>
            <p>Please enter and confirm your new password.</p>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <div class="password-container">
                        <input type="password" name="new_password" id="new_password" required>
                        <span class="password-toggle" onclick="togglePasswordVisibility('new_password', this)">Show</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <div class="password-container">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password', this)">Show</span>
                    </div>
                </div>
                <div class="form-submit">
                    <input type="submit" value="Set New Password">
                </div>
            </form>
        <?php endif; ?>

        <br>
        <a href="login.php">Back to Login</a>
    </div>

    <script>
        function togglePasswordVisibility(fieldId, toggleElement) {
            const passwordField = document.getElementById(fieldId);
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleElement.textContent = "Hide";
            } else {
                passwordField.type = "password";
                toggleElement.textContent = "Show";
            }
        }
    </script>
</body>
</html>
