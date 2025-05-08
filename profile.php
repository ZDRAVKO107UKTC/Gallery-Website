<?php
session_start();
require_once 'db.php';

// Only logged-in users can access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user's info
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email, role_id, profile_image_id, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found.");
}

$user = $result->fetch_assoc();

// Get role name
$roleStmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
$roleStmt->bind_param("i", $user['role_id']);
$roleStmt->execute();
$roleResult = $roleStmt->get_result();
$role = $roleResult->fetch_assoc()['name'];

$stmt->close();
$roleStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
</head>
<body>
    <h2>👤 Your Profile</h2>
    <?php
        $profileImagePath = "uploads/profiles/" . $user['profile_image_id'] . ".png";
        if ($user['profile_image_id'] && file_exists($profileImagePath)): ?>
            <img src="<?= $profileImagePath ?>" width="100" height="100" style="border-radius:50%;">   

    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($role) ?></p>
    <p><strong>Registered on:</strong> <?= $user['created_at'] ?></p>

    <p><strong>Profile Picture:</strong><br>
        <?php else: ?>
            <br>No image uploaded.
        <?php endif; ?>
    </p>

    <p>
        <a href="index.php">← Back to Home</a><br>
        <a href="logout.php">🚪 Logout</a>
    </p>
</body>
</html>
