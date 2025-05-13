<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = (int)$_POST['role_id'];

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $email, $password, $role_id);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        $uploadDir = "uploads/profiles/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] === 0) {
            $imageExt = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            $targetFile = $uploadDir . $user_id . "." . $imageExt;

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
                $updateStmt = $conn->prepare("UPDATE users SET profile_image_id = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $user_id, $user_id);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                $message = "User created, but image upload failed.";
            }
        }

        $message = "✅ Потребителят е създаден успешно.";
    } else {
        $message = "❌ Грешка: " . $stmt->error;
    }

    $stmt->close();
}

$roles = $conn->query("SELECT * FROM roles");
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Създаване на потребител</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-red-50 min-h-screen flex items-center justify-center font-sans text-gray-800">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full border border-red-400">
        <h2 class="text-2xl font-bold text-center text-red-600 mb-6">➕ Създай потребител</h2>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded <?= strpos($message, 'успешно') !== false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Потребителско име:</label>
                <input type="text" name="username" required
                       class="w-full px-3 py-2 border border-red-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>

            <div>
                <label class="block font-medium mb-1">Имейл:</label>
                <input type="email" name="email" required
                       class="w-full px-3 py-2 border border-red-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>

            <div>
                <label class="block font-medium mb-1">Парола:</label>
                <input type="password" name="password" required
                       class="w-full px-3 py-2 border border-red-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>

            <div>
                <label class="block font-medium mb-1">Роля:</label>
                <select name="role_id" required
                        class="w-full px-3 py-2 border border-red-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400">
                    <?php while ($role = $roles->fetch_assoc()): ?>
                        <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-1">Профилна снимка:</label>
                <input type="file" name="profile_image" accept="image/*"
                       class="w-full border border-red-300 p-2 rounded bg-white">
            </div>

            <button type="submit"
                    class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-200">
                Създай потребител
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="admin_dashboard.php" class="text-red-600 hover:underline">← Назад към админ панела</a>
        </div>
    </div>
</body>
</html>
