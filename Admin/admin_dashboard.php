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
</head>
<body>
    <h2>Admin Dashboard</h2>
    <h3>Users</h3>
    <p><a href="create_user.php">Create New User</a></p>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th> Profile picture</th><th>Action</th>
        </tr>
        <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role_id'] == 1 ? 'Admin' : 'User' ?></td>
                <td>
                    <?php if ($user['profile_image_id']): ?>
                        <img src="../uploads/profiles/<?= $user['profile_image_id'] ?>.png" width="50" alt="Profile Image">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td>
                    <!-- Optional: Add edit.php -->
                    <a href="edit_user.php?id=<?= $user['id'] ?>">Edit</a> |
                    <a href="?delete_user=<?= $user['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>

                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3> Gallery Images</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th><th>User</th><th>Image</th><th>Action</th>
        </tr>
        <?php while ($img = $images->fetch_assoc()): ?>
            <tr>
                <td><?= $img['id'] ?></td>
                <td><?= htmlspecialchars($img['username']) ?></td>
                <td><img src="../<?= htmlspecialchars($img['image_path']) ?>" width="120"></td>
                <td>
                    <a href="?delete_image=<?= $img['id'] ?>" onclick="return confirm('Delete this image?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <a href="../logout.php">logout</a>
</body>
</html>
