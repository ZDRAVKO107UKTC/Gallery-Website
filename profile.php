<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_profile_image'])) {
    $uploadDir = "uploads/profiles/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageExt = strtolower(pathinfo($_FILES["new_profile_image"]["name"], PATHINFO_EXTENSION));
    $targetPath = $uploadDir . $user_id . ".png";

    if (move_uploaded_file($_FILES["new_profile_image"]["tmp_name"], $targetPath)) {
        $stmt = $conn->prepare("UPDATE users SET profile_image_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = "✅ Профилната снимка е обновена.";
    } else {
        $message = "❌ Грешка при качване на снимката.";
    }
}

// Handle image removal
if (isset($_POST['remove_image'])) {
    $imagePath = "uploads/profiles/$user_id.png";
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
    $stmt = $conn->prepare("UPDATE users SET profile_image_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    $message = "🗑️ Профилната снимка беше премахната.";
}

// Fetch updated user data
$stmt = $conn->prepare("SELECT username, email, role_id, profile_image_id, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Моят Профил</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center font-sans text-gray-800">
    <div class="bg-white shadow-lg rounded-lg p-8 max-w-lg w-full border border-red-300">
        <h2 class="text-3xl font-bold text-center text-red-600 mb-4">👤 Профил</h2>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded <?= str_contains($message, '✅') || str_contains($message, '🗑️') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

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

            <p><strong>Потребител:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Роля:</strong> <?= htmlspecialchars($role) ?></p>
            <p><strong>Регистрация:</strong> <?= $user['created_at'] ?></p>

            <!-- Upload new image -->
            <form method="POST" enctype="multipart/form-data" class="w-full">
                <label class="block font-medium mb-1 mt-4">Смени профилната снимка:</label>
                <input type="file" name="new_profile_image" accept="image/*"
                       class="w-full border border-red-300 p-2 rounded mb-2">
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 w-full">
                    Качи нова снимка
                </button>
            </form>

            <!-- Remove image -->
            <?php if ($user['profile_image_id'] && file_exists($profileImagePath)): ?>
                <form method="POST" class="w-full mt-2">
                    <button type="submit" name="remove_image"
                            class="w-full bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                        Премахни снимката
                    </button>
                </form>
            <?php endif; ?>

            <!-- Navigation -->
            <div class="mt-6 space-x-4">
                <a href="index.php" class="text-sm text-red-600 hover:underline">← Обратно към началната</a>
                <a href="logout.php" class="text-sm bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">🚪 Изход</a>
            </div>
        </div>
    </div>
</body>
</html>
