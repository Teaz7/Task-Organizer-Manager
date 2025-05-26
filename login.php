
<?php
    include 'init.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_SESSION["currentUserEmail"] ?? ''; // Using null coalescing for safety
        $password = $_POST["loginPassword"] ?? ''; // Using null coalescing for safety

        try {
            $stmt = $conn->prepare(
                "SELECT * FROM user WHERE USER_Email = :email"
            );
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $loginError = false; 

            // Plain-text password comparison - DANGER: This is insecure.
            if ($user && ($user['USER_Email'] == $email) && ($user['USER_Password'] === $password)) {
                $stmt = $conn -> prepare(
                    "UPDATE user SET USER_LoginStatus=1 WHERE USER_Email=:email"
                );
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                header('Location: homepage.php');
                exit(); // Always exit after a header redirect
            } else {
                $loginError = true; // Set flag instead of echo
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage(); // Changed to getMessage() for proper error string
        }
    }

    // This ensures that if currentUserEmail is not set, it defaults to empty string
    $displayEmail = $_SESSION["currentUserEmail"] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - TOM</title>
  <link rel="stylesheet" href="style2.css" />
</head>
<body>
  <div class="input-form">
    <h2>TOM: Task Organizer Manager</h2>
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
      <div class="form-group">
        <label for="loginEmail">Email:</label>
        <input type="text" id="loginEmail" name="loginEmail" value="<?= htmlspecialchars($displayEmail) ?>" readonly>
      </div>

      <div class="form-group">
        <label for="loginPassword">Password:</label>
        <input 
          type="password" 
          id="loginPassword" 
          name="loginPassword" 
          required
          <?php if (!empty($loginError)) echo 'placeholder="Invalid Password"'; ?>
        >
      </div>

      <div class="form-submit">
        <input type="submit" value="Login">
      </div>
    </form>
    
    <a href="index.php">Choose Another Account</a>
    <a href="retrieve_password.php">Forgot Password?</a>
  </div>
</body>
</html>

