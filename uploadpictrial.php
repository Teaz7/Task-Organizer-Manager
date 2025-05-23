<?php
$conn = new mysqli("localhost", "root", "", "tom_db");

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit']) && isset($_FILES['photos'])) {
    $photos = $_FILES['photos'];

    for ($i = 0; $i < count($photos['name']); $i++) {
        $tmpName = $photos['tmp_name'][$i];
        $fileName = $photos['name'][$i];

        if (file_exists($tmpName)) {
            $imageData = file_get_contents($tmpName);
            $productName = pathinfo($fileName, PATHINFO_FILENAME);

            // Optional: detect MIME type
            $mimeType = mime_content_type($tmpName);

            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO product (PROD_Name, PROD_Category, STORE_Id, PROD_Photo) VALUES (?, 'Pet', 1, ?)");
            $null = NULL;
            $stmt->bind_param("sb", $productName, $null);
            $stmt->send_long_data(1, $imageData);

            if ($stmt->execute()) {
                echo "Uploaded: " . htmlspecialchars($fileName) . "<br>";
            } else {
                echo "Failed to upload " . htmlspecialchars($fileName) . ": " . $stmt->error . "<br>";
            }
        }
    }
} else {
    echo "No files uploaded.";
}
?>

<!-- Upload form -->
<form action="uploadpictrial.php" method="post" enctype="multipart/form-data">
    <input type="file" name="photos[]" multiple required>
    <input type="submit" name="submit" value="Upload Products">
</form>
