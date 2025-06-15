<?php
session_start();

$Username = "User";

if (isset($_SESSION['user_id'])) {
    $serverName = "localhost";
    $database = "Gajiro";
    $dbUsername = ""; // your dbUsername
    $dbPassword = ""; // your dbPass

    try {
        $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $dbUsername, $dbPassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && !empty($result['username'])) {
            $Username = $result['username'];
        }
    } catch (PDOException $e) {
        $Username = "User";
    }
}
?>