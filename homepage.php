<?php
    include 'init.php';
    include 'functions/fetchUserId.php';
    include 'functions/table/getTable.php';
    $selectedId = $_SESSION['USER_selectedPetID'] ?? null;

    $stmt = $conn -> prepare("SELECT * FROM task WHERE USER_Id = :id");
    $stmt -> bindParam(":id", $id);
    $stmt -> execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="heading">
        <h1>TOM: Task Organizer Manager</h1>
        <div>
            <a href="store.php">Store</a>
            <a href="calendar.php">Calendar</a>
            <a href="user\userhomepage.php">User Profile</a>
            <a href="index.php">Switch Account</a>
            <a href="functions\logout.php">Log Out</a>
        </div>
    </div>
    <br>

    <div>
        Search By 
        <select id="searchType" name="searchType">
            <option value="searchID">Task ID</option>
            <option value="searchDescription">Task Desription</option>
            <option value="searchName">Task Name</option>
            <option value="searchDateStart">Task Date Start</option>
            <option value="searchDateEnd">Task Task Finish</option>
        </select>: 
        <input type="text" id="searchInput" onkeyup="search(this.value)">
        Sort By:
        <select id="tableSort" name="tableSort">
            <option value="sortID">Task ID</option>
            <option value="sortName">Task Name</option>
            <option value="sortDateStart">Task Date Start</option>
            <option value="sortDateEnd">Task Task Finish</option>
            <option value="sortProgress">Task Progress</option>
        </select>
    </div>
    <br>

    <div id="searchResults">
        <?= $defaultTable ?>
    </div>
    
 
<script src="virtualPet.js"></script>
<script>
    function search(input) {
        const searchType = document.querySelector('select[name="searchType"]').value;
        if (input.length === 0) {
            fetch('functions/table/defaultTable.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById("searchResults").innerHTML = data;
            });
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (this.readyState==4 && this.status==200) {
                document.getElementById("searchResults").innerHTML = this.responseText;
            }
        }
        xhr.open("GET", `functions/search.php?keyword=${encodeURIComponent(input)}&type=${encodeURIComponent(searchType)}`, true);
        xhr.send();
    };

    function loadSortedTable() {
        const sortBy = document.querySelector('select[name="tableSort"]').value;

        fetch(`functions/table/sortTable.php?sort=${encodeURIComponent(sortBy)}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById("searchResults").innerHTML = data;
            });
    }

    document.querySelector('select[name="tableSort"]').addEventListener('change', loadSortedTable);

    window.addEventListener('DOMContentLoaded', () => {
        const selectedId = <?= json_encode($selectedId) ?>;
        if (selectedId && typeof createPet === 'function') {
            const imgUrl = `image.php?id=${selectedId}`;
            createPet({ image: imgUrl });
        }
    });
</script>

</body>
</html>