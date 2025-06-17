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

$gameId = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM games WHERE game_id = ? AND creator_id = ?");
$stmt->execute([$gameId, $creatorId]);
$game = $stmt->fetch();

if ($game === false) {
    die("Not your game.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $imageURL = $_POST['image_url'];

    $stmt = $pdo->prepare("UPDATE games SET title = ?, description = ?, price = ?, image_url = ? WHERE game_id = ?");
    $stmt->execute([$title, $description, $price, $imageURL, $gameId]);

    header("Location: admin_games.php"); // Redirect back
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit <?= htmlentities($game['title']) ?> - Gajiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1b2838;
            color: #c6d4df;
        }

        .form-label {
            color: #c6d4df;
        }

        .form-control {
            background: #2a475e;
            color: #c6d4df;
            border: none;
        }

        .form-control::placeholder {
            color: #97a9c5;
        }

        .btn-primary {
            background: #3c6382;
            border: none;
        }

        .btn-primary:hover {
            background: #4a79a5;
        }

        .btn-secondary {
            background: #2a475e;
            color: #c6d4df;
            border: none;
        }

        .btn-secondary:hover {
            background: #3c6382;
        }

        h2 {
            color: #ffffff;
            margin-bottom: 30px;
        }

        .container {
            margin-bottom: 50px;
        }

        .card {
            background: #1f1f1f;
            color: #c6d4df;
            padding: 20px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px 5px #000;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <h2>Edit <?= htmlentities($game['title']) ?></h2>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input id="title" name="title" class="form-control" value="<?= htmlentities($game['title']) ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label for="desc" class="form-label">Description</label>
                    <textarea id="desc" name="description" class="form-control" rows="4"
                        required><?= htmlentities($game['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price (₱)</label>
                    <input id="price" name="price" type="number" class="form-control"
                        value="<?= htmlentities($game['price']) ?>" required min="0">
                </div>

                <div class="mb-3">
                    <label for="image_url" class="form-label">Image URL</label>
                    <input id="image_url" name="image_url" type="url" class="form-control"
                        value="<?= htmlentities($game['image_url']) ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="admin_games.php" class="btn btn-secondary ml-2">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>