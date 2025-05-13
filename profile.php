<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email, role_id, profile_image_id, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found.");
}

$user = $result->fetch_assoc();

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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center font-sans text-gray-800">
    <div class="bg-white shadow-lg rounded-lg p-8 max-w-lg w-full border border-red-300">
        <h2 class="text-3xl font-bold text-center text-red-600 mb-6">👤 Профил</h2>

        <div class="flex flex-col items-center text-center space-y-4">
            <?php
            $profileImagePath = "uploads/profiles/" . $user['profile_image_id'] . ".png";
            if ($user['profile_image_id'] && file_exists($profileImagePath)): ?>
                <img src="<?= $profileImagePath ?>" alt="Profile Picture"
                     class="w-28 h-28 rounded-full border-4 border-red-300 shadow-md">
            <?php else: ?>
                <div class="w-28 h-28 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                    No image
                </div>
            <?php endif; ?>

            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($role) ?></p>
            <p><strong>Registered on:</strong> <?= $user['created_at'] ?></p>

            <div class="mt-6 space-x-4">
                <a href="index.php" class="text-sm text-red-600 hover:underline">← Обратно към началната</a>
                <a href="logout.php" class="text-sm bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">🚪 Изход</a>
            </div>
        </div>
    </div>
</body>
</html>
