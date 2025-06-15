<?php
session_start();

$serverName = "localhost";
$database = "Gajiro";
$dbUsername = ""; // your DB username
$dbPassword = ""; // your DB password

$Username = "User";

// Check if the user is already authenticated
if (isset($_SESSION['user_id'])) { // make sure this matches exactly what you set during login
    $userId = $_SESSION['user_id'];

    try {
        $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $dbUsername, $dbPassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && !empty($result['username'])) {
            $Username = $result['username']; // Update Username to fetched name
        }
    } catch (PDOException $e) {
        // Handle gracefully if something goes awry
        $Username = "User";
        // You may want to log this: error_log($e->getMessage()); 
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gajiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="main.css">
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
                        <a class="nav-link custom-link text-light" href="#">New Release</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-link text-light" href="#">About</a>
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
                                <li><a class="dropdown-item" href="#">Profile</a></li>
                                <li><a class="dropdown-item" href="#">Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../login.html">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- main -->
    <!-- Popular Games -->
    <div class="container mt-5">
        <h2 class="text-light mb-4">Popular Games</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            $serverName = "localhost";
            $database = "Gajiro";
            $username = "";
            $password = "";

            try {
                $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = "SELECT game_id, title, description, price, image_url FROM games";
                $stmt = $conn->query($sql);

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '
                <div class="col">
                    <a href="game_details.php?id=' . $row["game_id"] . '" class="text-decoration-none">
                        <div class="card bg-dark text-light h-100">
                            <img src="' . htmlspecialchars($row["image_url"]) . '" class="card-img-top" alt="' . htmlspecialchars($row["title"]) . '">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($row["title"]) . '</h5>
                                <p class="card-text">' . htmlspecialchars($row["description"]) . '</p>
                                <p class="card-text"><strong>₱' . number_format($row["price"], 2) . '</strong></p>
                            </div>
                        </div>
                    </a>
                </div>';
                }
            } catch (PDOException $e) {
                echo '<p class="text-danger">Database error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
        crossorigin="anonymous"></script>
</body>

</html>