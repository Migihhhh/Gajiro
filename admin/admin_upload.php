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

// Check if this user is a creator
$stmt = $pdo->prepare("SELECT creator_id FROM creators WHERE user_id = ?");
$stmt->execute([$userId]);
$creatorId = $stmt->fetchColumn();

if ($creatorId === false) {
    // If not a creator, create a new creator profile first
    $studio_name = "Studio of User $userId";
    $bio = "New creator on Gajiro.";
    $stmt = $pdo->prepare(" INSERT INTO creators (user_id, studio_name, bio) VALUES (?, ?, ?) ");
    $stmt->execute([$userId, $studio_name, $bio]);
    $creatorId = $pdo->lastInsertId();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $imageURL = $_POST['image_url'];

    // Now insert into games
    $stmt = $pdo->prepare(" INSERT INTO games (creator_id, title, description, price, image_url, created_at) VALUES (?, ?, ?, ?, ?, GETDATE()) ");
    $stmt->execute([$creatorId, $title, $description, $price, $imageURL]);

    header("Location: admin_games.php"); // Redirect after adding
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Upload Game - Gajiro Developer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #121212;
            color: #bfc0c0;
        }

        .navbar-brand,
        .nav-link {
            color: #bfc0c0 !important;
        }

        .form-label {
            color: white;
        }

        .form-control {
            background-color: #1f1f1f;
            border: 1px solid #444;
            color: #bfc0c0;
        }

        .form-control::placeholder {
            color: #7a7a7a;
        }

        .btn-custom {
            background-color: #3a3f44;
            color: #bfc0c0;
        }

        .btn-custom:hover {
            background-color: #4a4f55;
        }

        h2 {
            color: white;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-dark px-3">
        <a class="navbar-brand" href="#">Gajiro Dev Panel</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_games.php">Manage Games</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_upload.php">Upload Game</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Upload Form -->
    <div class="container mt-5 mb-5">
        <h2 class="mb-4">Upload a New Game</h2>
        <form action="admin_upload.php" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Game Title</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Enter game name" required>
            </div>

            <div class="mb-3">
                <label for="desc" class="form-label">Description</label>
                <textarea class="form-control" id="desc" name="description" rows="4"
                    placeholder="Short description of the game" required></textarea>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price (₱)</label>
                <input type="number" class="form-control" id="price" name="price" placeholder="e.g. 299" required>
            </div>

            <div class="mb-3">
                <label for="cover_url" class="form-label">Cover Image URL</label>
                <input type="url" class="form-control" id="cover_url" name="image_url"
                    placeholder="https://example.com/image.jpg" required>
            </div>

            <button type="submit" class="btn btn-custom">Submit Game</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>