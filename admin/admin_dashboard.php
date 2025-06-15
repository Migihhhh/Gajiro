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
$username = $_SESSION['username']; // Currently authenticated user's Username

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
    <title>Developer Dashboard - Gajiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #bfc0c0
        }

        .navbar-brand,
        .nav-link {
            color: #bfc0c0 !important
        }

        .card {
            background-color: #1c1c1c;
            border: none;
            color: #bfc0c0
        }

        .card h5 {
            color: #ffffff
        }

        .btn-custom {
            background: #3a3f44;
            color: #bfc0c0
        }

        .btn-custom:hover {
            background: #4a4f55
        }

        .dashboard-header {
            color: #ffffff;
            border-bottom: 1px solid #333;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-dark px-3">
        <a class="navbar-brand" href="">Gajiro Dev Panel</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_games.php">Manage Games</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_upload.php">Upload Game</a></li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="nav-link">Mabuhay, <?= htmlentities($username) ?>!</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mt-5">
        <h2 class="dashboard-header">Developer Dashboard</h2>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card p-3">
                    <h5>Total Games</h5>
                    <p class="display-6"><?= count($games) ?></p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3">
                    <h5>Total Wishlist Adds</h5>
                    <?php
                    $totalWishlist = 0;
                    foreach ($games as $game) {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE game_id = ?");
                        $stmt->execute([$game['game_id']]);
                        $totalWishlist += $stmt->fetchColumn();
                    }
                    ?>
                    <p class="display-6"><?= $totalWishlist ?></p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3">
                    <h5>Average Rating</h5>
                    <?php
                    $total = 0;
                    $count = 0;
                    foreach ($games as $game) {
                        $stmt = $pdo->prepare("SELECT AVG(rating) AS avg FROM ratings WHERE game_id = ?");
                        $stmt->execute([$game['game_id']]);
                        $avg = $stmt->fetchColumn();

                        if ($avg !== false && $avg !== null) {
                            $total += $avg;
                            $count++;
                        }
                    }
                    $average = $count > 0 ? $total / $count : 0;
                    ?>
                    <p class="display-6"><?= number_format($average, 1) ?> ★</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3">
                    <h5>Total Orders</h5>
                    <?php
                    $totalSales = 0;
                    $totalOrders = 0;
                    foreach ($games as $game) {
                        $stmt = $pdo->prepare("SELECT SUM(amount) AS total, COUNT(*) AS orders FROM payment WHERE game_id = ?");
                        $stmt->execute([$game['game_id']]);
                        $payment = $stmt->fetch();

                        $totalSales += ($payment['total'] !== null) ? $payment['total'] : 0;
                        $totalOrders += ($payment['orders'] !== null) ? $payment['orders'] : 0;
                    }
                    ?>
                    <p class="display-6"><?= $totalOrders ?> (₱<?= number_format($totalSales, 2) ?>)</p>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h4 class="text-light mb-3">Quick Actions</h4>
            <div class="d-flex flex-wrap gap-3">
                <a href="admin_upload.php" class="btn btn-custom">➕ Upload New Game</a>
                <a href="admin_games.php" class="btn btn-custom">🎮 Manage My Games</a>
            </div>
        </div>

        <h2 class="text-light mt-5">Feedback on Your Games</h2>

        <?php foreach ($games as $game): ?>
            <div class="card bg-dark text-light mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= htmlentities($game['title']) ?> </h5>
                    <?php
                    // Average Rating
                    $stmt = $pdo->prepare("SELECT AVG(rating) AS avg FROM ratings WHERE game_id = ?");
                    $stmt->execute([$game['game_id']]);
                    $avg = $stmt->fetchColumn();

                    ?>
                    <small>Average Rating: <strong class="text-warning"><?= number_format($avg, 1) ?> ★</strong></small>
                </div>
                <div class="card-body">
                    <?php
                    // Get all reviews for this game
                    $stmt = $pdo->prepare("SELECT r.*, u.Username FROM ratings r JOIN users u ON r.user_id = u.User_ID WHERE r.game_id = ?");
                    $stmt->execute([$game['game_id']]);
                    $reviews = $stmt->fetchAll();

                    foreach ($reviews as $review):
                        ?>
                        <div class="mb-3 border-bottom pb-2">
                            <p class="mb-1">
                                <?= str_repeat('★', $review['rating']) ?>         <?= str_repeat('☆', 5 - $review['rating']) ?>
                            </p>
                            <p>"<?= htmlentities($review['review']) ?>"</p>
                            <small class="text-muted">by <?= htmlentities($review['Username']) ?> on
                                <?= $review['rated_at'] ?></small>
                        </div>
                        <?php
                    endforeach;
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>