<?php
session_start();
require_once 'db.php';

$message = '';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $caption = trim($_POST['caption']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = "uploads/gallery/$user_id/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . "." . $imageExt;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("INSERT INTO gallery_images (user_id, image_path, caption) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $targetPath, $caption);
            $stmt->execute();
            $stmt->close();
            $message = "✅ Image uploaded successfully!";
        } else {
            $message = "❌ Failed to upload the image.";
        }
    } else {
        $message = "❌ Please select a valid image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Gallery - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-red-100 font-sans text-gray-900">
    <div class="max-w-4xl mx-auto p-6 mt-8 bg-white rounded shadow">
        <h1 class="text-3xl font-bold mb-4 text-center">🎨 Online Gallery System</h1>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Guest View -->
            <p class="text-center">Welcome, guest!</p>
            <div class="text-center mt-4">
                <a href="login.php" class="text-blue-600 hover:underline">🔐 Login</a> | 
                <a href="register.php" class="text-blue-600 hover:underline">📝 Register</a>
            </div>

        <?php elseif ($_SESSION['role'] === 'admin'): ?>
            <!-- Admin View -->
            <div class="text-center">
                <h3 class="text-xl font-semibold">Welcome, Admin <?= htmlspecialchars($_SESSION['username']) ?>!</h3>
                <div class="mt-4">
                    <a href="admin/admin_dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">⚙️ Admin Dashboard</a>
                    <a href="logout.php" class="ml-3 text-red-600 hover:underline">🚪 Logout</a>
                </div>
            </div>

        <?php elseif ($_SESSION['role'] === 'user'): ?>
            <!-- User View -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h3>
                <p class="mt-2">
                    <a href="profile.php" class="text-blue-600 hover:underline">👤 My Account</a> |
                    <a href="logout.php" class="text-red-600 hover:underline">🚪 Logout</a>
                </p>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 p-3 rounded <?= strpos($message, '✅') !== false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Upload form -->
            <h3 class="text-lg font-semibold mb-2">📤 Upload New Image</h3>
            <form method="post" enctype="multipart/form-data" class="space-y-4 mb-8">
                <div>
                    <label class="block mb-1 font-medium">Select Image:</label>
                    <input type="file" name="image" accept="image/*" required class="border border-gray-300 p-2 w-full rounded">
                </div>
                <div>
                    <label class="block mb-1 font-medium">Description:</label>
                    <textarea name="caption" rows="3" class="border border-gray-300 p-2 w-full rounded" placeholder="Enter a caption (optional)"></textarea>
                </div>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Upload</button>
            </form>

            <!-- User gallery -->
            <h3 class="text-lg font-semibold mb-2">🖼️ Your Gallery</h3>
            <?php
            $stmt = $conn->prepare("SELECT * FROM gallery_images WHERE user_id = ? ORDER BY uploaded_at DESC");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            ?>

            <?php if ($result->num_rows > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="border border-gray-200 rounded p-3 bg-gray-50">
                            <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Gallery Image" class="w-full h-auto mb-2 rounded">
                            <p class="text-sm text-gray-700 mb-1"><?= htmlspecialchars($row['caption']) ?></p>
                            <p class="text-xs text-gray-500">Uploaded: <?= $row['uploaded_at'] ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No images uploaded yet.</p>
            <?php endif; ?>
            <?php $stmt->close(); ?>

        <?php endif; ?>
    </div>
</body>
</html>
