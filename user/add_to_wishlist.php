<?php
include('header.php'); // starts session and checks for user

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit;
}

$gameId = $_GET['game_id']; // game to add

$serverName = "localhost";
$database = "Gajiro";
$dbUsername = "";
$dbPass = "";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $dbUsername, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if already in wishlist first
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$_SESSION['user_id'], $gameId]);

    if ($stmt->fetch()) {
        // Already in wishlist
        header('Location: wishlist.php'); 
        exit;
    }
  
    // Otherwise, insert it with current datetime
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, game_id, added_at) VALUES (?, ?, GETDATE())");
    $stmt->execute([$_SESSION['user_id'], $gameId]);

    header('Location: wishlist.php'); // After adding, view wishlist
} catch (PDOException $e) {
    echo "Error adding to wishlist: " . $e->getMessage();
}
?>
