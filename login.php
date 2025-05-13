<?php
session_start();
require_once 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT users.id, users.username, users.password, roles.name AS role 
                            FROM users 
                            JOIN roles ON users.role_id = roles.id 
                            WHERE LOWER(users.email) = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: Admin/admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $message = "❌ Грешна парола.";
        }
    } else {
        $message = "❌ Няма потребител с този имейл.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Вход в Галерията</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-red-50 min-h-screen flex items-center justify-center text-gray-800 font-sans">
    <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-md border border-red-400">
        <h2 class="text-3xl font-bold text-red-600 text-center mb-6">🔐 Вход</h2>

        <?php if ($message): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="space-y-4">
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

            <button type="submit"
                    class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-200">
                Влез
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            Нямаш акаунт?
            <a href="register.php" class="text-red-600 hover:underline">Регистрирай се тук</a>
        </p>
    </div>
</body>
</html>
