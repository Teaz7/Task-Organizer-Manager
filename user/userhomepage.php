<?php 
    include '../init.php';
    include '../functions/fetchUserId.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Profile</title>
  <link rel="stylesheet" href="../styleUser.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <header class="header">
    <h1>TOM: Task Organizer Manager</h1>
  </header>

  <main class="main-container">
    <section class="profile-card">
      <h2>User Profile</h2>
      <table class="profile-table">
        <tr><td>Account ID:</td><td><?= $result["USER_Id"] ?></td></tr>
        <tr><td>Username:</td><td><?= $result["USER_FName"]." ".$result["USER_LName"] ?></td></tr>
        <tr><td>Age:</td><td><?= $result["USER_Age"] ?></td></tr>
        <tr><td>Email Address:</td><td><?= $result["USER_Email"] ?></td></tr>
        <tr><td>Occupation:</td><td><?= $result["USER_Occupation"] ?></td></tr>
        <tr><td>Establishment:</td><td><?= $result["USER_Establishment"] ?></td></tr>
        <tr><td>Current Points:</td><td><?= $result["USER_Points"]." Points" ?></td></tr>
        <tr><td>Leaderboard:</td><td><a href="leaderboard.php">View Leaderboard</a></td></tr>
      </table>
    </section>

    <div class="action-buttons">
      <a href="useredit.php">Edit Account</a>
      <a href="userremove.php" onclick="return verifyOption('Are you sure you want to delete this account?')">Remove Account</a>
      <a href="../homepage.php">Go Back</a>
      <a href="../index.php">Switch Account</a>
      <a href="../functions/logout.php">Log Out</a>
    </div>
  </main>

  <script>
    function verifyOption(promptText) {
      return confirm(promptText);
    }
  </script>
</body>
</html>

