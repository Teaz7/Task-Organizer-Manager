<?php 
$email = $_SESSION["currentUserEmail"];

$stmt = $conn->prepare("SELECT * FROM user WHERE USER_Email = :email");
$stmt->bindParam(":email", $email);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $id = $result["USER_Id"];
    $_SESSION['USER_selectedPetID'] = $result['USER_selectedPetID']; // ✅ This line sets session for pet!
} else {
    // fail-safe
    $id = null;
}
?>
