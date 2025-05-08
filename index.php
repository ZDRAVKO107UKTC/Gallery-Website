<?php
session_start();
require_once 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Gallery - Home</title>
</head>
<body>
    <h1>🎨 Online Gallery System</h1>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Guest View -->
        <p>Welcome, guest!</p>
        <p><a href="login.php">🔐 Login</a> | <a href="register.php">📝 Register</a></p>

    <?php elseif ($_SESSION['role'] === 'admin'): ?>
        <!-- Admin View -->
        <h3>Welcome, Admin <?= htmlspecialchars($_SESSION['username']) ?>!</h3>
        <p>
            <a href="admin/admin_dashboard.php">⚙️ Go to Admin Dashboard</a><br>
            <a href="logout.php">🚪 Logout</a>
        </p>

    <?php elseif ($_SESSION['role'] === 'user'): ?>
        <!-- User View -->
        <h3>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h3>
        <p><a href="logout.php">🚪 Logout</a></p>

        <h3>Your Gallery</h3>
        <?php
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM gallery_images WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>

        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="5">
                <tr>
                    <th>Image</th><th>Caption</th><th>Uploaded</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><img src="<?= $row['image_path'] ?>" width="100"></td>
                        <td><?= htmlspecialchars($row['caption']) ?></td>
                        <td><?= $row['uploaded_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No images uploaded yet.</p>
        <?php endif; ?>
        <?php $stmt->close(); ?>

    <?php endif; ?>
    <a href="profile.php">My acc</a>
</body>
</html>
