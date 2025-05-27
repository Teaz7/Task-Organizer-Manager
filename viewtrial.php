<?php
include 'init.php';

// Query products
$result = $slaveConn->query("SELECT PROD_Id, PROD_Name FROM product WHERE STORE_Id = 1");

echo "<h2>Product List</h2>";

while ($row = $result->fetch_assoc()) {
    echo "<div style='margin-bottom: 20px;'>";
    echo "<strong>" . htmlspecialchars($row['PROD_Name']) . "</strong><br>";
    echo '<img src="image.php?id=' . $row['PROD_Id'] . '" alt="Product Image" style="max-width:200px;">';
    echo "</div>";
}
