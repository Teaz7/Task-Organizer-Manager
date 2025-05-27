<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "tom_db";
    $masterport = 3306;
    $slaveport = 3307;

    try {
        $masterConn = new PDO("mysql:host=$servername;dbname=$dbname; port=$masterport", $username, $password);
        $masterConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $slaveConn = new PDO("mysql:host=$servername;dbname=$dbname; port=$slaveport", $username, $password);
        $slaveConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Connection failed: " . $e -> getMessage();
        exit();
    }

    if (!isset($_SESSION['has_refreshed'])) {
        $_SESSION['has_refreshed'] = true;

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
?>