<?php
session_start();
require_once '../init.php';
require_once '../functions/fetchUserId.php';


if (!$id) {
    die("You must be logged in to view the leaderboard.");
}

// Fetch current user info
$stmt = $slaveConn->prepare("SELECT 	USER_FName, USER_Points FROM user WHERE USER_Id = :id");
$stmt->execute([':id' => $id]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    die("User not found.");
}

// Fetch top 10 users by points
$topStmt = $slaveConn->query("
    SELECT 	USER_FName, USER_Points 
    FROM user 
    ORDER BY USER_Points DESC 
    LIMIT 10
");
$topUsers = $topStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current user rank
$rankStmt = $slaveConn->prepare("
    SELECT COUNT(*) + 1 AS rank 
    FROM user 
    WHERE USER_Points > :points
");
$rankStmt->execute([':points' => $currentUser['USER_Points']]);
$currentRank = $rankStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Leaderboard</title>
    <link rel="stylesheet" href="../styleLeaderBoard.css">
</head>
<body>

<div class="leaderboard-container">
    <h2>🏆 Leaderboard - Top 10</h2>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>User</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topUsers as $index => $user): 
                $highlight = ($user['USER_FName'] === $currentUser['USER_FName']) ? 'highlight' : '';
            ?>
            <tr class="<?= $highlight ?>">
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($user['USER_FName']) ?></td>
                <td><?= $user['USER_Points'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="rank-info">
        <?= htmlspecialchars($currentUser['USER_FName']) ?>'s Rank: <strong>#<?= $currentRank ?></strong>
    </div>

    <a class="back-button" href="userhomepage.php">Back to Homepage</a>
</div>

</body>
</html>
