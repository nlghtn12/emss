<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    exit;
}

$user = $_SESSION['user'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Post announcement
    if ($_POST['action'] === 'post' && $user['role'] === 'admin') {
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
        echo "Announcement posted.";
        exit;
    }

    // Delete
    if ($_POST['action'] === 'delete' && $user['role'] === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo "Announcement deleted.";
        exit;
    }
}

// Fetch announcements (GET)
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY post_date DESC");
$announcements = $stmt->fetchAll();

foreach ($announcements as $a) {
    echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
    echo "<p><strong>" . htmlspecialchars($a['posted_by']) . ":</strong> " . nl2br(htmlspecialchars($a['content'])) . "</p>";

    if (!empty($a['media'])) {
        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $a['media'])) {
            echo "<img src='../uploads/{$a['media']}' width='200'><br>";
        } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $a['media'])) {
            echo "<video width='320' controls><source src='../uploads/{$a['media']}' type='video/mp4'></video><br>";
        }
    }

    echo "<small>Posted on {$a['post_date']}</small><br>";

    if ($user['role'] === 'admin') {
        echo "<a href='#' class='delete-btn' data-id='{$a['id']}'>Delete</a>";
    }

    echo "</div>";
}