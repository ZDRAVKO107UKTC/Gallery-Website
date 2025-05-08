<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = (int)$_POST['role_id'];

    // Step 1: Create user (without image)
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $email, $password, $role_id);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Step 2: Handle profile picture upload
        $uploadDir = "uploads/profiles/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] === 0) {
            $imageExt = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            $targetFile = $uploadDir . $user_id . "." . $imageExt;

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
                // Update profile_image_id = user_id
                $updateStmt = $conn->prepare("UPDATE users SET profile_image_id = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $user_id, $user_id);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                $message = "User created, but image upload failed.";
            }
        }

        $message = "User created successfully.";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Load available roles
$roles = $conn->query("SELECT * FROM roles");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User - Admin</title>
</head>
<body>
    <h2>Create New User</h2>
    <p><a href="admin_dashboard.php">← Back to Admin Dashboard</a></p>

    <?php if ($message): ?>
        <p style="color:<?= strpos($message, 'success') !== false ? 'green' : 'red' ?>;">
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

        <label>Role:</label><br>
        <select name="role_id" required>
            <?php while ($role = $roles->fetch_assoc()): ?>
                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Profile Picture:</label><br>
        <input type="file" name="profile_image" accept="image/*"><br><br>

        <button type="submit">Create User</button>
    </form>
</body>
</html>
