<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    die('Access denied');
}

$user = $_SESSION['user'];
$message = "";

// Handle new announcement post
if ($user['role'] === 'admin' && isset($_POST['post'])) {
    $content = $_POST['content'];
    $media = '';

    if (!empty($_FILES['media']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . basename($_FILES['media']['name']);
        move_uploaded_file($_FILES['media']['tmp_name'], $target_file);
        $media = basename($_FILES['media']['name']);
    }

    $stmt = $pdo->prepare("INSERT INTO announcements (content, media, posted_by, post_date) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$content, $media, $user['name']]);
    $message = "Announcement posted.";
}

// Handle delete
if ($user['role'] === 'admin' && isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "Announcement deleted.";
}

// Load announcements
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY post_date DESC");
$announcements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Announcements</title>
</head>
<body>
<p><a href="dashboard.php">Back to Dashboard</a></p>
    <h2>Announcements</h2>
    <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

    <?php if ($user['role'] === 'admin'): ?>
        <form method="POST" enctype="multipart/form-data">
            <textarea name="content" placeholder="Write announcement..." required></textarea><br>
            <input type="file" name="media" accept="image/*,video/*"><br>
            <button type="submit" name="post">Post</button>
        </form><hr>
    <?php endif; ?>

    <?php foreach ($announcements as $a): ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <p><strong><?= htmlspecialchars($a['posted_by']) ?>:</strong> <?= nl2br(htmlspecialchars($a['content'])) ?></p>
            <?php if (!empty($a['media'])): ?>
                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $a['media'])): ?>
                    <img src="../uploads/<?= $a['media'] ?>" width="200"><br>
                <?php elseif (preg_match('/\.(mp4|webm|ogg)$/i', $a['media'])): ?>
                    <video width="320" controls>
                        <source src="../uploads/<?= $a['media'] ?>" type="video/mp4">
                    </video><br>
                <?php endif; ?>
            <?php endif; ?>
            <small>Posted on <?= $a['post_date'] ?></small><br>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="?delete=<?= $a['id'] ?>" onclick="return confirm('Delete this announcement?')">Delete</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
