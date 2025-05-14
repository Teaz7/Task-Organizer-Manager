<?php include '../init.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
       include '../user/function/fetchUserId.php';

        $stmt = $conn->prepare("DELETE FROM task WHERE TASK_Name=:val1 AND USER_Id=:val2");
        $stmt->bindParam(':val1', $_POST["removeTaskName"]);
        $stmt->bindParam(':val2', $id);

        $result = $stmt -> execute();

        header('Location: taskhomepage.php');
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
        Enter Task Name to remove: <input type="text" name="removeTaskName">
        <input type="submit">
    </form>
    <a href="taskhomepage.php">Go Back</a> <br>

    <div>
        Search Id:
            <input type="text" onkeyup="search(this.value)">
    </div> <br>
    <div id="searchResults"></div>

    <script>
        function search(input) {
            if (input.length == 0) {
                document.getElementById("searchResults").innerHTML = "";
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (this.readyState==4 && this.status==200) {
                    document.getElementById("searchResults").innerHTML = this.responseText;
                }
            }
            xhr.open("GET", "view/search.php?keyword=" + input, true);
            xhr.send();
        }
    </script>
</body>
</html>