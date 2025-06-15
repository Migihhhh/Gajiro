<?php
$server = "localhost";
$database = "Gajiro";
$username = "";
$password = "";

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

$userId = 6;

// Handle removal if form is submitted
if (isset($_POST['delete'])) {
    $itemId = $_POST['item_id'];

    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->execute([$itemId]);

    header("Location: cart.php");
    exit;
}

// Retrieve all items from the cart with game details
$stmt = $pdo->prepare("
    SELECT cart.*, games.title, games.image_url, games.price
    FROM cart
    JOIN games ON cart.game_id = games.game_id
    WHERE cart.user_id = ?
");

$stmt->execute([$userId]);
$items = $stmt->fetchAll();

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Cart - Gajiro</title>
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

        .btn-checkout {
            width: 100%;
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
            <a class="navbar-brand text-light" href="main.html">Gajiro</a>
            <div class="collapse navbar-collapse show" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link custom-link text-light" href="main.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link custom-link text-light" href="new_release.html">New
                            Release</a></li>
                    <li class="nav-item"><a class="nav-link custom-link text-light" href="about.html">About</a></li>
                </ul>

                <div class="d-flex">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link custom-link text-light" href="cart.php">Cart</a></li>
                        <li class="nav-item"><a class="nav-link custom-link text-light"
                                href="wishlist.html">Wishlish</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle custom-link text-light" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Mabuhay, User!
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

    <!-- Cart Section -->
    <div class="container mt-5">
        <h2 class="mb-4">🛒 Your Cart</h2>

        <div class="row g-3">
            <?php foreach ($items as $item): ?>
                <div class="col-md-12">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <img src="<?php echo htmlentities($item['image_url']); ?>" class="game-img me-3" alt="Game Image">
                        <div class="flex-grow-1">
                            <h5 class="mb-1"><?php echo htmlentities($item['title']); ?></h5>
                            <p class="mb-0 text-warning">₱<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <form action="cart.php" method="POST">
                            <input name="item_id" type="hidden" value="<?php echo $item['cart_id']; ?>">
                            <button class="btn btn-outline-danger btn-sm ms-auto" name="delete" type="submit">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
                <?php $total += $item['price']; ?>
            <?php endforeach; ?>
            <div class="col-md-12 mt-4">
                <div class="card p-4">
                    <h4>Total: ₱<?php echo number_format($total, 2); ?></h4>
                    <a href="checkout.php" class="btn btn-success btn-checkout mt-3">Checkout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/js/bootstrap.bundle.min.js"></script>
</body>

</html>