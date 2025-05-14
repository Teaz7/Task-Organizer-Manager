<?php include '../init.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $stmt = $conn -> prepare(
            "UPDATE task SET
            TASK_Name=:val1, TASK_Description=:val2, TASK_PriorityClass=:val3, 
            TASK_Status=:val4, TASK_DateStart=:val5, TASK_DateFinish=:val6, TASK_Progress=:val7
            WHERE TASK_Id=:val8;"
        );
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div>
        Search Task Name:
            <input type="text" onkeyup="search(this.value)">
    </div>
    <br>
    <div id="searchResults"></div>
    <a href="taskhomepage.php">Go Back</a>

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