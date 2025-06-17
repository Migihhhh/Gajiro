<?php
include('header.php'); // starts session and sets $Username (optional)

$server = "localhost";
$database = "Gajiro";
$username = ""; // Your database username
$password = ""; // Your database password

$options = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];
$pdo = new PDO("sqlsrv:server=$server;Database=$database", $username, $password, $options);

// User must be authenticated first
if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$userId = $_SESSION['user_id']; // Currently authenticated user's id

$gameId = $_GET['id']; // The game's ID from URL

$stmt = $pdo->prepare("SELECT * FROM games WHERE game_id = ?");
$stmt->execute([$gameId]);
$game = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['buy'])) {
        // Handle "Buy Now"
        $gameId = $_POST['game_id'];

        $stmt = $pdo->prepare(" INSERT INTO cart (user_id, game_id, added_at) VALUES (?, ?, GETDATE()) ");
        $stmt->execute([$userId, $gameId]);

        header("Location: cart.php"); // Redirect directly to cart
        exit;

    } elseif (isset($_POST['add'])) {
        // Handle "Add to Wishlist"

        $gameId = $_POST['game_id'];

        // Check if already in wishlist first
        $stmt = $pdo->prepare("SELECT * FROM wishlist WHERE user_id = ? AND game_id = ?");
        $stmt->execute([$userId, $gameId]);

        if ($stmt->fetch()) {
            // Already in wishlist
            header("Location: wishlist.php");
            exit;
        }

        // Otherwise, insert it
        $stmt = $pdo->prepare(" INSERT INTO wishlist (user_id, game_id, added_at) VALUES (?, ?, GETDATE()) ");
        $stmt->execute([$userId, $gameId]);

        header("Location: wishlist.php"); // After adding, view wishlist
        exit;

    } elseif (isset($_POST['submit_review'])) {
        // Handle Review submission
        $rating = $_POST['rating'];
        $review = $_POST['review'];

        $stmt = $pdo->prepare(" INSERT INTO ratings (user_id, game_id, rating, review, rated_at) VALUES (?, ?, ?, ?, GETDATE()) ");
        $stmt->execute([$userId, $gameId, $rating, $review]);

        header("Location: game_details.php?id=$gameId"); // refresh page to show the new review
        exit;
    }
}

?>

<!-- game-details.html -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Game Details - Gajiro</title>
    <link rel="stylesheet" href="main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #121212;
            color: white;
        }

        .game-img {
            max-width: 100%;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand text-light" href="main.php">Gajiro</a>

            <!-- Nav Links on the left -->
            <div class="collapse navbar-collapse show" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link custom-link text-light" href="main.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-link text-light" href="under_construction.html">New Release</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-link text-light" href="under_construction.html">About</a>
                    </li>
                </ul>

                <!-- Sign In button on the right -->
                <div class="d-flex">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link custom-link text-light" href="cart.php">Cart</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link custom-link text-light" href="wishlist.php">Wishlish</a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle custom-link text-light" href="#" id="userDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Mabuhay, <?= htmlentities($Username) ?>!
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="under_construction.html">Profile</a></li>
                                <li><a class="dropdown-item" href="under_construction.html">Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>



    <div class="container mt-5">
        <a href="main.php" class="btn btn-outline-light mb-3">&larr; Back to Home</a>
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo htmlentities($game['image_url']); ?>" class="game-img"
                    alt="<?php echo htmlentities($game['title']); ?>">
            </div>
            <div class="col-md-6">
                <h1><?php echo htmlentities($game['title']); ?></h1>
                <p class="lead"><?php echo htmlentities($game['description']); ?></p>
                <h4>₱<?php echo number_format($game['price'], 2); ?></h4>

                <form action="" method="POST">
                    <input name="game_id" type="hidden" value="<?php echo $game['game_id']; ?>">
                    <button class="btn btn-warning" name="add" type="submit">
                        Add to Wishlist
                    </button>
                    <button class="btn btn-success" name="buy" type="submit">
                        Buy Now
                    </button>
                </form>
            </div>
        </div>
        <hr class="text-light mt-5" />
        <h3>Description</h3>
        <p><?php echo nl2br(htmlentities($game['description'])); ?></p>

        <hr class="text-light" />

        <h3 class="text-light mt-5">Reviews</h3>

        <?php
        $stmt = $pdo->prepare("
    SELECT r.*, u.username FROM ratings r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.game_id = ?
    ORDER BY rated_at DESC
");

        $stmt->execute([$gameId]);
        $reviews = $stmt->fetchAll();

        foreach ($reviews as $rev) {
            echo '
    <div class="card bg-dark text-light mb-3">
        <div class="card-body">
            <h5>' . str_repeat('★', $rev['rating']) . '</h5>
            <p>' . htmlentities($rev['review']) . '</p>
            <footer class="blockquote-footer text-light">by <cite>' . htmlentities($rev['username']) . '</cite></footer>
            <small>' . $rev['rated_at'] . '</small>
        </div>
    </div>';
        }
        ?>


        <h3 class="text-light mt-5">Leave a Review</h3>

        <form action="" method="POST" class="text-light">
            <input name="game_id" type="hidden" value="<?php echo $game['game_id']; ?>">
            <div class="mb-3">
                <label class="form-label">Your Rating:</label>
                <select name="rating" class="form-select bg-dark text-light" required>
                    <option value="">Select stars</option>
                    <option value="5">★★★★★ - Excellent</option>
                    <option value="4">★★★★☆ - Good</option>
                    <option value="3">★★★☆☆ - Okay</option>
                    <option value="2">★★☆☆☆ - Bad</option>
                    <option value="1">★☆☆☆☆ - Terrible</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Comment (optional):</label>
                <textarea name="review" class="form-control bg-dark text-light" rows="3"
                    placeholder="Write something..."></textarea>
            </div>

            <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
        </form>

    </div>


    <!-- Feedback Section -->

    <hr class="text-light" />

    <div class="container mt-5">
        <h2 class="text-light mb-4">Similar Games</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Game Card 1 -->
            <div class="col">
                <a href="game_details.html" class="text-decoration-none">
                    <div class="card bg-dark text-light h-100">
                        <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="Game 1">
                        <div class="card-body">
                            <h5 class="card-title">Tala: Stars of the Bayan</h5>
                            <p class="card-text">Explore the stars with Tala, a Filipina space explorer in a pixel-style
                                RPG.</p>
                            <p class="card-text"><strong>₱299.00</strong></p>
                            <p class="card-text text-warning">★★★★☆ (4.5)</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Game Card 2 -->
            <div class="col">
                <a href="#" class="text-decoration-none">
                    <div class="card bg-dark text-light h-100">
                        <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="Game 2">
                        <div class="card-body">
                            <h5 class="card-title">Baryo Brawl</h5>
                            <p class="card-text">A fighting game set in a chaotic Filipino town fiesta. Tsinelas as
                                weapons!
                            </p>
                            <p class="card-text"><strong>₱199.00</strong></p>
                            <p class="card-text text-warning">★★★☆☆ (3.8)</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Game Card 3 -->
            <div class="col">
                <a href="#" class="text-decoration-none">
                    <div class="card bg-dark text-light h-100">
                        <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="Game 3">
                        <div class="card-body">
                            <h5 class="card-title">Manila Drift</h5>
                            <p class="card-text">Race through the chaotic streets of Manila with your custom jeepney!
                            </p>
                            <p class="card-text"><strong>₱249.00</strong></p>
                            <p class="card-text text-warning">★★★★★ (4.9)</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>