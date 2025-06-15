<?php
$serverName = "localhost";
$database = "Gajiro";
$username = ""; // Your database username
$password = ""; // Your database password

try {
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $password = $_POST["password"]; // Ideally should be hashed in production
        $is_creator = isset($_POST["is_creator"]) ? 1 : 0;

        // 1. Insert into users first
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_creator) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $is_creator]);

        // 2. Retrieve the newly inserted user's ID
        $userId = $conn->lastInsertId();

        // 3. If the person is a creator, insert into creators
        if ($is_creator == 1) {
            $studio_name = $name ."'s Studio";
            $bio = "New creator on Gajiro.";

            $stmt = $conn->prepare("INSERT INTO creators (user_id, studio_name, bio) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $studio_name, $bio]);
        }

        header("Location: login.html");
        exit();

    } else {
        echo "Invalid request method.";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
