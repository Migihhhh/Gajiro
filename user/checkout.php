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

// connect to Microsoft SQL (PDO + sqlsrv)
try {
    $pdo = new PDO("sqlsrv:server=$server;Database=$database", $username, $password, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$userId = $_SESSION['user_id']; // Currently authenticated user's id

// Retrieve all items from the user's cart
$stmt = $pdo->prepare("
    SELECT cart.*, games.title, games.image_url, games.price
    FROM cart
    JOIN games ON cart.game_id = games.game_id
    WHERE cart.user_id = ?
");

$stmt->execute([$userId]);
$items = $stmt->fetchAll();

if ($items) {
    // Begin transaction
    $pdo->beginTransaction();

    try {
        foreach ($items as $item) {
            // Insert into payments
            $stmt = $pdo->prepare(" INSERT INTO payment (user_id, game_id, amount, paid_at) VALUES (?, ?, ?, GETDATE()) ");
            $stmt->execute([$userId, $item['game_id'], $item['price']]);
        }
        // Clear the user's cart afterwards
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);

        $pdo->commit();

        header("Location: main.php"); // Redirect back to main page
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Checkout failed: " . $e->getMessage());
    }
} else {
    // If there were no items in the cart
    header("Location: main.php");
    exit;
}

?>