<?php
include 'init.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($conn) || !$conn) {
    die('Database connection not established.');
}

$store_id = isset($_GET['STORE_Id']) ? intval($_GET['STORE_Id']) : 1;

$products = [];
if ($store_id > 0) {
    $stmt = $conn->prepare("SELECT PROD_Id, PROD_Name, PROD_Amount FROM product WHERE STORE_Id = :store_id");
    if ($stmt->execute([':store_id' => $store_id])) {
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "SQL Error: " . htmlspecialchars($errorInfo[2]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Store Products</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .heading {
            background: #2d3e50;
            color: #fff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .heading h1 {
            margin: 0;
            font-size: 2rem;
        }
        .heading a {
            color: #fff;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
            transition: color 0.2s;
        }
        .heading a:hover {
            color: #ffb347;
        }
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            padding: 40px 30px;
            justify-content: flex-start;
        }
        .product-item {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 20px;
            width: 220px;
            text-align: center;
            transition: box-shadow 0.2s;
            cursor: pointer;
        }
        .product-item:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .product-name {
            font-size: 1.1rem;
            margin-bottom: 15px;
            font-weight: 600;
            color: #2d3e50;
        }
        .product-amount {
            font-size: 0.98rem;
            margin-bottom: 10px;
            color: #555;
        }
        .product-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
            background: #fafafa;
        }
        @media (max-width: 700px) {
            .product-list {
                flex-direction: column;
                align-items: center;
                padding: 20px 5px;
            }
            .product-item {
                width: 90%;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="heading">
        <h1>TOM: Task Organizer Manager</h1>
        <div>
            <a href="homepage.php">Home</a>
            <a href="calendar.php">Calendar</a>
            <a href="user/userhomepage.php">User Profile</a>
            <a href="index.php">Log Out</a>
        </div>
    </div>

    <button onclick="resetPet()">Reset Pet</button>

    <?php if (empty($products)): ?>
        <p style="margin:30px;">No products found for this store.</p>
    <?php else: ?>
        <div class="product-list">
            <?php foreach ($products as $product): ?>
                <div class="product-item" onclick="selectPet(<?= htmlspecialchars($product['PROD_Id']) ?>)">
                    <div class="product-name"><?= htmlspecialchars($product['PROD_Name']) ?></div>
                    <div class="product-amount">Amount: <?= htmlspecialchars($product['PROD_Amount']) ?></div>
                    <img
                        class="product-img"
                        src="image.php?id=<?= urlencode($product['PROD_Id']) ?>"
                        alt="Product Image"
                    />
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<script src="virtualPet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectedImage = localStorage.getItem('selectedPetImage');
    if (selectedImage && typeof createPet === 'function') {
        createPet({ image: selectedImage });
    } else {
        console.warn('createPet function not available or no selected image.');
    }
});
</script>


    <script>
        function selectPet(prodId) {
            const imgUrl = `image.php?id=${prodId}`;
            localStorage.setItem('selectedPetImage', imgUrl);

            // Send selected pet to PHP session
            fetch('setSelectedPet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `prodId=${encodeURIComponent(prodId)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') {
                    console.error('Failed to save to session:', data.message);
                }
            });

            createPet({ image: imgUrl });
        }

        function resetPet() {
            localStorage.removeItem('selectedPetImage');
            const existingPet = document.getElementById('pet');
            if (existingPet) existingPet.remove();

            const speech = document.getElementById('speech');
            if (speech) speech.remove();
        }
    </script>

</body>
</html>
