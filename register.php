<?php
$serverName = "localhost";
$database = "Gajiro";
$username = ""; // Use your SQL Server credentials
$password = ""; // Use your SQL Server password

try {
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $password = $_POST["password"]; // Plaintext for demo
        $is_creator = isset($_POST["is_creator"]) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_creator) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $is_creator]);

        header("Location: login.html");
        exit();
    } else {
        echo "Invalid request method.";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>