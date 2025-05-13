<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

if (!isset($_GET['id'])) {
    die("No user ID provided.");
}

$user_id = (int)$_GET['id'];
$message = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows !== 1) {
    die("User not found.");
}

$user = $userResult->fetch_assoc();

$roles = $conn->query("SELECT * FROM roles");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = (int)$_POST['role_id'];

    $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role_id = ? WHERE id = ?");
    $updateStmt->bind_param("ssii", $username, $email, $role_id, $user_id);

    if ($updateStmt->execute()) {
        $message = "✅ Потребителят е обновен успешно.";
        $user['username'] = $username;
        $user['email'] = $email;
        $user['role_id'] = $role_id;
    } else {
        $message = "❌ Грешка при обновяване: " . $updateStmt->error;
    }

    $updateStmt->close();
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Редакция на потребител</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-red-50 min-h-screen flex items-center justify-center font-sans text-gray-800">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full border border-red-400">
        <h2 class="text-2xl font-bold text-center text-red-600 mb-6">✏️ Редакция на потребител</h2>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded <?= strpos($message, 'успешно') !== false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Потребителско име:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                       class="w-full px-3 py-2 border border-red-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>

            <div>
                <label class="block font-medium mb-1">Имейл:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                       class="w-full px-3 py-2 border border-red-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>

            <div>
                <label class="block font-medium mb-1">Роля:</label>
                <select name="role_id" required
                        class="w-full px-3 py-2 border border-red-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400">
                    <?php while ($role = $roles->fetch_assoc()): ?>
                        <option value="<?= $role['id'] ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit"
                    class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-200">
                Обнови
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="admin_dashboard.php" class="text-red-600 hover:underline">← Назад към админ панела</a>
        </div>
    </div>
</body>
</html>
