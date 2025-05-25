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
                $stmt = $conn->prepare(
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

                $updateStmt = $conn->prepare("UPDATE user SET USER_Password = :plainTextPassword WHERE USER_Id = :userId");
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
    <link rel="stylesheet" href="style.css">
    <style>
        /* Optional CSS for the eye icon if you use one instead of a checkbox */
        .password-container {
            position: relative;
            display: inline-block; /* To contain the eye icon relative to the input */
        }
        .password-toggle {
            position: absolute;
            right: 10px; /* Adjust as needed */
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none; /* Prevent text selection on the icon */
            font-size: 0.9em; /* Adjust icon size */
            color: #555;
        }
        /* Style for the checkbox if you use that instead */
        .show-password-checkbox-container {
            display: flex;
            align-items: center;
            margin-top: 5px;
            font-size: 0.9em;
            color: #555;
        }
        .show-password-checkbox-container input[type="checkbox"] {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="input-form">
        <h2>Reset Your Password</h2>

        <?php if ($message): ?>
            <p style="color: <?= (strpos($message, 'successfully reset') !== false) ? 'green' : 'red'; ?>; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (!$showPasswordForm): ?>
            <p>Please enter your exact first name, last name, and email address as you registered them.</p>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="confirm_details">
                <table>
                    <tr>
                        <td>First Name:</td>
                        <td><input type="text" name="firstName" required></td>
                    </tr>
                    <tr>
                        <td>Last Name:</td>
                        <td><input type="text" name="lastName" required></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><input type="email" name="email" required></td>
                    </tr>
                </table>
                <div class="form-submit"><input type="submit" value="Confirm Details"></div>
            </form>
        <?php else: ?>
            <p>Please enter and confirm your new password.</p>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST" autocomplete="off">
                <table>
                    <tr>
                        <td>New Password:</td>
                        <td>
                            <div class="password-container">
                                <input type="password" name="new_password" id="new_password" required>
                                <span class="password-toggle" onclick="togglePasswordVisibility('new_password', this)">Show</span>
                                </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Confirm Password:</td>
                        <td>
                            <div class="password-container">
                                <input type="password" name="confirm_password" id="confirm_password" required>
                                <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password', this)">Show</span>
                                </div>
                        </td>
                    </tr>
                </table>
                <div class="form-submit"><input type="submit" value="Set New Password"></div>
            </form>
        <?php endif; ?>

        <br>
        <a href="login.php">Back to Login</a>
    </div>

    <script>
        // JavaScript function to toggle password visibility using a text link/span
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

        // Alternative JavaScript function for a checkbox
        /*
        function togglePasswordVisibilityCheckbox(fieldId, checkboxElement) {
            const passwordField = document.getElementById(fieldId);
            if (checkboxElement.checked) {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
        */
    </script>
</body>
</html>