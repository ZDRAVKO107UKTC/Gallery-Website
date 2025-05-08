<?php
session_start();
require_once 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT users.id, users.username, users.password, roles.name AS role 
                            FROM users 
                            JOIN roles ON users.role_id = roles.id 
                            WHERE users.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Success
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
            $message = "Invalid password.";
        }
    } else {
        $message = "No user found with this email.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Gallery</title>
</head>
<body>
    <h2>Login</h2>
    <?php if ($message): ?>
        <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
    <a href="register.php">register</a>
</body>
</html>
