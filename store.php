<?php
include 'init.php'; // Ensure this calls session_start() at its beginning
include 'functions/fetchUserId.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($conn) || !$conn) {
    die('Database connection not established.');
}

$store_id = isset($_GET['STORE_Id']) ? intval($_GET['STORE_Id']) : 1;
$user_id = $id;

$products = [];
$ownedProducts = []; // Stores array of PROD_Id that the user owns

if ($store_id > 0) {
    // Fetch products for the store
    $stmt = $conn->prepare("SELECT PROD_Id, PROD_Name, PROD_Amount FROM product WHERE STORE_Id = :store_id");
    if ($stmt->execute([':store_id' => $store_id])) {
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "SQL Error fetching products: " . htmlspecialchars($errorInfo[2]);
    }

    // Fetch IDs of products owned by the user
    if ($user_id) {
        $stmt = $conn->prepare("SELECT prod_id FROM purchases WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $ownedProducts = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetches just the prod_id column into a simple array
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
            justify-content: flex-start; /* Aligns items to the start */
        }
        .product-item {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 20px;
            width: 220px; /* Fixed width for consistent sizing */
            text-align: center;
            transition: box-shadow 0.2s;
            display: flex; /* For flex column layout */
            flex-direction: column; /* Stack children vertically */
            justify-content: space-between; /* Distribute space for children */
        }
        .product-item:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .product-name {
            font-size: 1.1rem;
            margin-bottom: 10px; /* Adjusted margin */
            font-weight: 600;
            color: #2d3e50;
        }
        .product-amount {
            font-size: 0.98rem;
            margin-bottom: 15px; /* Adjusted margin */
            color: #555;
        }
        .product-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
            background: #fafafa;
            margin: 0 auto 15px auto; /* Center image and add bottom margin */
        }
        .product-item button { /* Style for buttons inside product item */
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background-color: #5cb85c;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s;
            margin-top: auto; /* Pushes button to the bottom if item is flex column */
        }
        .product-item button:hover {
            background-color: #4cae4c;
        }
        .product-item button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        @media (max-width: 700px) {
            .product-list {
                flex-direction: column;
                align-items: center;
                padding: 20px 15px; /* Adjusted padding */
            }
            .product-item {
                width: 90%; /* More responsive width */
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

<button onclick="resetPet()">Reset Pet</button> <?php if (empty($products)): ?>
    <p style="text-align:center; margin:30px; font-size: 1.2em;">No products found for this store or store not selected.</p>
<?php else: ?>
    <div class="product-list">
        <?php foreach ($products as $product): 
            $prodId = htmlspecialchars($product['PROD_Id']);
            // Check if the current product's ID is in the array of owned product IDs
            $isOwned = in_array($product['PROD_Id'], $ownedProducts);
        ?>
            <div class="product-item">
                <div> <div class="product-name"><?= htmlspecialchars($product['PROD_Name']) ?></div>
                    <div class="product-amount">Points: <?= htmlspecialchars($product['PROD_Amount']) ?></div>
                </div>
                <img
                    class="product-img"
                    src="image.php?id=<?= urlencode($prodId) ?>"
                    alt="<?= htmlspecialchars($product['PROD_Name']) ?> Image" <?php if ($isOwned): ?>
                        onclick="selectPet(<?= $prodId ?>)"
                        style="cursor:pointer;" title="Select this pet"
                    <?php else: ?>
                        title="Buy to activate and select as pet"
                        style="cursor:default;" <?php endif; ?>
                />
                <?php if ($isOwned): ?>
                    <button disabled title="You already own this pet">Owned</button>
                <?php else: ?>
                    <button onclick="buyProduct(<?= $prodId ?>)">Buy</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script src="virtualPet.js"></script>
<script>
function selectPet(prodId) {
    const imgUrl = `image.php?id=${prodId}`;
    localStorage.setItem('selectedPetImage', imgUrl);

    fetch('setSelectedPet.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `prodId=${encodeURIComponent(prodId)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof createPet === 'function') {
                createPet({ image: imgUrl }); // Create/update pet on current page
                alert('Pet selected!'); // Optional: feedback to user
            } else {
                console.warn('createPet function not found, but pet selection was saved.');
            }
        } else {
            alert('Failed to select pet: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error selecting pet:', error);
        alert('An error occurred while selecting the pet.');
    });
}

function resetPet() {
    localStorage.removeItem('selectedPetImage');
    // Optionally, tell the server to clear the selected pet from the session
    // fetch('clearSelectedPet.php', { method: 'POST' }); // See note below

    // Remove visual elements of the pet from the page
    const petElement = document.getElementById('pet'); // Assuming your pet has id 'pet'
    if (petElement) petElement.remove();
    const speechElement = document.getElementById('speech'); // Assuming speech bubble has id 'speech'
    if (speechElement) speechElement.remove();
    
    alert('Pet has been reset.'); // Feedback
}

function buyProduct(prodId) {
    fetch('buyProduct.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `prodId=${encodeURIComponent(prodId)}`
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message); // Show message from server (success or error)
        if (data.status === 'success') {
            location.reload(); // Reload to refresh ownership status and UI
        }
    })
    .catch(error => {
        console.error('Error buying product:', error);
        alert('An error occurred while trying to buy the product.');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const selectedImage = localStorage.getItem('selectedPetImage');
    if (selectedImage && typeof createPet === 'function') {
        createPet({ image: selectedImage });
    } else if (selectedImage) {
        console.warn('Found selectedPetImage in localStorage, but createPet function is not available.');
    }
});
</script>
</body>
</html>