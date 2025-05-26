<?php
$conn = new mysqli("localhost", "root", "", "tom_db");

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT PROD_Photo FROM product WHERE PROD_Id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($image);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && $image) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($image);
        header("Content-Type: $mimeType");
        echo $image;
    } else {
        http_response_code(404);
        echo "Image not found.";
    }
}
