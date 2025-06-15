<?php
session_start();

$server = "localhost";
$database = "Gajiro";
$username = ""; // Your dbUsername
$password = ""; // Your dbPass

$options = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];
$pdo = new PDO("sqlsrv:server=$server;Database=$database", $username, $password);

// User must be authenticated first
if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$userId = $_SESSION['user_id']; // Currently authenticated user's id

// find this user's creatorId first
$stmt = $pdo->prepare("SELECT creator_id FROM creators WHERE user_id = ?");
$stmt->execute([$userId]);
$creatorId = $stmt->fetchColumn();

if ($creatorId === false) {
    die("This user is not a creator.");
}

$gameId = $_GET['id']; // The game we want to delete

// First delete related reviews
$stmt = $pdo->prepare("DELETE FROM ratings WHERE game_id = ?");
$stmt->execute([$gameId]);

// Next delete related wishlist entries
$stmt = $pdo->prepare("DELETE FROM wishlist WHERE game_id = ?");
$stmt->execute([$gameId]);

// Next delete related payments
$stmt = $pdo->prepare("DELETE FROM payment WHERE game_id = ?");
$stmt->execute([$gameId]);

// Finally delete the game itself
$stmt = $pdo->prepare("DELETE FROM games WHERE game_id = ? AND creator_id = ?");
$stmt->execute([$gameId, $creatorId]);

header("Location: admin_games.php"); // Redirect back
exit;

?>
