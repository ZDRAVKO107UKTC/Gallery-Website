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

            $message = "✅ Регистрацията е успешна!";
        } else {
            $message = "⚠️ Снимката не беше качена, но регистрацията е успешна.";
        }
    } else {
        $message = "❌ Грешка при регистрация: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Регистрация - Галерия</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-red-50 min-h-screen flex items-center justify-center font-sans text-gray-800">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full border border-red-400">
        <h2 class="text-3xl font-bold text-red-600 text-center mb-6">📝 Регистрация</h2>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded <?= strpos($message, 'успешна') !== false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
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
                <label class="block font-medium mb-1">Профилна снимка:</label>
                <input type="file" name="profile_image" accept="image/*" required
                       class="w-full border border-red-300 p-2 rounded bg-white">
            </div>

            <button type="submit"
                    class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-200">
                Регистрирай се
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            Вече имаш акаунт?
            <a href="login.php" class="text-red-600 hover:underline">Влез тук</a>
        </p>
    </div>
</body>
</html>
