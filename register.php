<?php
session_start();
require_once 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $roleResult = $conn->query("SELECT id FROM roles WHERE name = 'user'");
    if ($roleResult->num_rows > 0) {
        $role_id = $roleResult->fetch_assoc()['id'];
    } else {
        die("Role 'user' not found.");
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $email, $password, $role_id);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        $uploadDir = "uploads/profiles/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $targetFile = $uploadDir . $user_id . "." . $imageFileType;

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
            $updateStmt = $conn->prepare("UPDATE users SET profile_image_id = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $user_id, $user_id);
            $updateStmt->execute();
            $updateStmt->close();

            $message = "Registration successful!";
        } else {
            $message = "Image upload failed, but user registered.";
        }
    } else {
        $message = "Registration failed: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Gallery</title>
</head>
<body>
    <h2>Register</h2>
    <?php if ($message): ?>
        <p style="color:<?= strpos($message, 'successful') !== false ? 'green' : 'red' ?>;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Profile Picture:</label><br>
        <input type="file" name="profile_image" accept="image/*" required><br><br>

        <button type="submit">Register</button>
    </form>
    <a href="login.php">login</a>
</body>
</html>
