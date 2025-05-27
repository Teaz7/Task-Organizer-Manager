<?php
include 'init.php';

if (isset($_GET['id'])) {
    $stmt = $slaveConn->prepare("SELECT PROD_Photo FROM product WHERE PROD_Id = ?");
    $stmt->bindParam(1, $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();

    $image = $stmt->fetchColumn();

    if ($image) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($image);
        header("Content-Type: $mimeType");
        echo $image;
    } else {
        http_response_code(404);
        echo "Image not found.";
    }
}