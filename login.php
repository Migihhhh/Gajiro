<?php
session_start();

$serverName = "localhost";
$database = "Gajiro";
$username = "";
$password = "";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = trim($_POST["email"]);
        $passwordInput = trim($_POST["password"]);

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user["password"] === $passwordInput) {
            // Store in sessions with lowercase keys
            $_SESSION["user_id"] = $user["user_id"]; 
            $_SESSION["username"] = $user["username"]; 
            $_SESSION["is_creator"] = $user["is_creator"]; 
 
            if ($user["is_creator"]) {
                header("Location: admin/admin_dashboard.php");
            } else {
                header("Location: user/main.php");
            }
            exit();
        } else {
            echo "Incorrect email or password.";
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>