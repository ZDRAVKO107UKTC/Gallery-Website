<?php
session_start();
require_once '../db.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

if (!isset($_GET['id'])) {
    die("No user ID provided.");
}

$user_id = (int)$_GET['id'];
$message = '';

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows !== 1) {
    die("User not found.");
}

$user = $userResult->fetch_assoc();

// Get roles for dropdown
$roles = $conn->query("SELECT * FROM roles");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = (int)$_POST['role_id'];

    $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role_id = ? WHERE id = ?");
    $updateStmt->bind_param("ssii", $username, $email, $role_id, $user_id);

    if ($updateStmt->execute()) {
        $message = "User updated successfully.";
        // Refresh data
        $user['username'] = $username;
        $user['email'] = $email;
        $user['role_id'] = $role_id;
    } else {
        $message = "Update failed: " . $updateStmt->error;
    }

    $updateStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
</head>
<body>
    <h2>Edit User</h2>
    <p><a href="admin_dashboard.php">← Back to Admin Dashboard</a></p>

    <?php if ($message): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:</label><br>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

        <label>Role:</label><br>
        <select name="role_id" required>
            <?php while ($role = $roles->fetch_assoc()): ?>
                <option value="<?= $role['id'] ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($role['name']) ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <button type="submit">Update</button>
    </form>
</body>
</html>
