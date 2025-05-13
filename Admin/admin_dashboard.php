<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

if (isset($_GET['delete_user'])) {
    $userId = (int)$_GET['delete_user'];
    $conn->query("DELETE FROM users WHERE id = $userId");
    $conn->query("DELETE FROM gallery_images WHERE user_id = $userId");
}

if (isset($_GET['delete_image'])) {
    $imageId = (int)$_GET['delete_image'];

    $result = $conn->query("SELECT image_path FROM gallery_images WHERE id = $imageId");
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $imagePath = $row['image_path'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $conn->query("DELETE FROM gallery_images WHERE id = $imageId");
}

$users = $conn->query("SELECT * FROM users");
$images = $conn->query("SELECT gallery_images.id, gallery_images.image_path, users.username 
                        FROM gallery_images 
                        JOIN users ON gallery_images.user_id = users.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-red-50 min-h-screen font-sans text-gray-800">
    <div class="max-w-6xl mx-auto p-6">
        <h2 class="text-3xl font-bold text-center text-red-700 mb-6">⚙️ Admin Dashboard</h2>

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-red-600">👥 Потребители</h3>
            <a href="create_user.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">➕ Създай потребител</a>
        </div>

        <div class="overflow-x-auto mb-10">
            <table class="min-w-full bg-white border border-red-200 text-sm rounded shadow">
                <thead class="bg-red-100 text-left">
                    <tr>
                        <th class="px-4 py-2 border">ID</th>
                        <th class="px-4 py-2 border">Потребител</th>
                        <th class="px-4 py-2 border">Имейл</th>
                        <th class="px-4 py-2 border">Роля</th>
                        <th class="px-4 py-2 border">Снимка</th>
                        <th class="px-4 py-2 border">Действия</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr class="hover:bg-red-50">
                        <td class="px-4 py-2 border"><?= $user['id'] ?></td>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-4 py-2 border"><?= $user['role_id'] == 1 ? 'Admin' : 'User' ?></td>
                        <td class="px-4 py-2 border text-center">
                            <?php if ($user['profile_image_id']): ?>
                                <img src="../uploads/profiles/<?= $user['profile_image_id'] ?>.png" alt="Profile Image" class="w-12 h-12 rounded-full mx-auto">
                            <?php else: ?>
                                <span class="text-gray-400 italic">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2 border">
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:underline">Edit</a> |
                            <a href="?delete_user=<?= $user['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <h3 class="text-xl font-semibold text-red-600 mb-3">🖼️ Галерия</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-red-200 text-sm rounded shadow">
                <thead class="bg-red-100 text-left">
                    <tr>
                        <th class="px-4 py-2 border">ID</th>
                        <th class="px-4 py-2 border">Потребител</th>
                        <th class="px-4 py-2 border">Снимка</th>
                        <th class="px-4 py-2 border">Действие</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($img = $images->fetch_assoc()): ?>
                    <tr class="hover:bg-red-50">
                        <td class="px-4 py-2 border"><?= $img['id'] ?></td>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($img['username']) ?></td>
                        <td class="px-4 py-2 border">
                            <img src="../<?= htmlspecialchars($img['image_path']) ?>" class="w-28 rounded">
                        </td>
                        <td class="px-4 py-2 border">
                            <a href="?delete_image=<?= $img['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this image?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6 text-center">
            <a href="../logout.php" class="text-sm bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">🚪 Изход</a>
        </div>
    </div>
</body>
</html>
