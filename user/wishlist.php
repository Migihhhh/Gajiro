<?php

include("header.php");

$server = "localhost";
$database = "Gajiro";
$username = ""; // Your database username
$password = ""; // Your database password

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

// User must be authenticated first
if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$userId = $_SESSION['user_id']; // Currently authenticated user's id

// Handle removal from wishlist
if (isset($_POST['remove'])) {
    $gameId = $_POST['game_id'];

    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$userId, $gameId]);

    header("Location: wishlist.php");
    exit;
}

// Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    $gameId = $_POST['game_id'];

    $stmt = $pdo->prepare(" INSERT INTO cart (user_id, game_id, added_at) VALUES (?, ?, GETDATE()) ");
    $stmt->execute([$userId, $gameId]);

    header("Location: cart.php");
    exit;
}

// Retrieve wishlist items
$stmt = $pdo->prepare("
    SELECT g.* FROM wishlist w
    JOIN games g ON w.game_id = g.game_id
    WHERE w.user_id = ?
");

$stmt->execute([$userId]);
$wishlist = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Your Wishlist - Gajiro</title>
    <link rel="stylesheet" href="main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1b2838;
            color: #c6d4df;
        }

        .card {
            background-color: #2a475e;
            color: #c6d4df;
            border: none;
        }

        .dropdown-menu {
            background-color: #2a475e;
            color: #c6d4df;
        }

        .dropdown-item {
            color: #c6d4df;
        }

        .dropdown-item:hover {
            background-color: #3c6382;
        }

        .btn {
            color: #c6d4df;
        }

        .btn-add {
            width: 100px;
        }

        .game-img {
            width: 100px;
            height: auto;
            border-radius: 10px;
        }

        .navbar {
            background-color: #171a21;
        }

        .navbar-brand,
        .nav-link {
            color: #c6d4df !important;
        }

        .nav-link:hover {
            color: #ffffff !important;
        }

        .text-warning {
            color: #ffd700 !important;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container-fluid">
            <a class="navbar-brand text-light" href="main.php">Gajiro</a>

            <div class="collapse navbar-collapse show" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link custom-link text-light" href="main.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link custom-link text-light" href="new_release.html">New
                            Release</a></li>
                    <li class="nav-item"><a class="nav-link custom-link text-light" href="about.html">About</a></li>
                </ul>

                <div class="d-flex">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link custom-link text-light" href="cart.php">Cart</a></li>
                        <li class="nav-item"><a class="nav-link custom-link text-light" href="wishlist.php">Wishlish</a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle custom-link text-light" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Mabuhay, <?= htmlentities($Username) ?>!
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="">Profile</a></li>
                                <li><a class="dropdown-item" href="">Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </nav>

    <!-- Wishlist Section -->
    <div class="container mt-5">
        <h2 class="mb-4">❤️ Your Wishlist</h2>

        <div class="row g-3">
            <?php foreach ($wishlist as $game): ?>
                <div class="col-md-12">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <img src="<?= htmlentities($game['image_url']); ?>" class="game-img me-3" alt="Game Image">
                        <div class="flex-grow-1">
                            <h5 class="mb-1"><?= htmlentities($game['title']); ?></h5>
                            <p class="mb-0 text-warning">₱<?= number_format($game['price'], 2); ?></p>
                        </div>
                        <form action="wishlist.php" method="POST" class="me-2">
                            <input name="game_id" type="hidden" value="<?= $game['game_id']; ?>">
                            <button class="btn btn-outline-light btn-sm" name="add_to_cart" type="submit">
                                Add to Cart
                            </button>
                        </form>

                        <form action="wishlist.php" method="POST">
                            <input name="game_id" type="hidden" value="<?= $game['game_id']; ?>">
                            <button class="btn btn-outline-danger btn-sm" name="remove" type="submit">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/js/bootstrap.bundle.min.js"></script>
</body>

</html>