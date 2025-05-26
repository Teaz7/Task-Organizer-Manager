<?php
session_start();
require_once '../init.php';
require_once '../functions/fetchUserId.php';


if (!$id) {
    die("You must be logged in to view the leaderboard.");
}

// Fetch current user info
$stmt = $conn->prepare("SELECT 	USER_FName, USER_Points FROM user WHERE USER_Id = :id");
$stmt->execute([':id' => $id]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    die("User not found.");
}

// Fetch top 10 users by points
$topStmt = $conn->query("
    SELECT 	USER_FName, USER_Points 
    FROM user 
    ORDER BY USER_Points DESC 
    LIMIT 10
");
$topUsers = $topStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current user rank
$rankStmt = $conn->prepare("
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
    <title>Leaderboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 30px;
        }
        .leaderboard-container {
            max-width: 600px;
            background: white;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #2d3e50;
            color: white;
        }
        .highlight {
            background-color: #d1f7d1;
            font-weight: bold;
        }
        .rank-info {
            margin-top: 30px;
            text-align: center;
            font-size: 1.1rem;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #2d3e50;
        }
    </style>
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

    <a class="back-link" href="userhomepage.php">← Back to User Homepage</a>
</div>

</body>
</html>
