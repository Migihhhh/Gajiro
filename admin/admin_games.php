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

$userId = $_SESSION['user_id']; // now it's dynamic, not hardcoded

// find this user's creatorId first
$stmt = $pdo->prepare("SELECT creator_id FROM creators WHERE user_id = ?");
$stmt->execute([$userId]);
$creatorId = $stmt->fetchColumn();

if ($creatorId === false) {
    die("This user is not a creator.");
}

// now fetch all games by this creator
$stmt = $pdo->prepare("SELECT * FROM games WHERE creator_id = ?");
$stmt->execute([$creatorId]);
$games = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manage Games - Gajiro Developer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #121212;
            color: #bfc0c0
        }

        .navbar-brand,
        .nav-link {
            color: #bfc0c0 !important
        }

        table {
            color: #bfc0c0
        }

        th {
            color: #ffffff
        }

        .table-dark th,
        .table-dark td {
            background: #1f1f1f
        }

        .btn-edit {
            background: #3a6ea5;
            color: #ffffff
        }

        .btn-delete {
            background: #a53a3a;
            color: #ffffff
        }

        .game-thumb {
            height: 60px;
            border-radius: 4px
        }

        h2 {
            color: #ffffff
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

    <!-- Game Management Table -->
    <div class="container mt-5 mb-5">
        <h2 class="mb-4">Your Uploaded Games</h2>
        <div class="table-responsive">
            <table class="table table-dark table-bordered">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Price (₱)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($games as $game): ?>
                        <tr>
                            <td><img src="<?= htmlentities($game['image_url']) ?>" class="game-thumb" alt="Game Cover" />
                            </td>
                            <td><?= htmlentities($game['title']) ?></td>
                            <td>₱<?= number_format($game['price'], 2) ?></td>
                            <td>
                                <a href="edit_game.php?id=<?= $game['game_id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                <a href="delete_game.php?id=<?= $game['game_id'] ?>" class="btn btn-sm btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this game?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>