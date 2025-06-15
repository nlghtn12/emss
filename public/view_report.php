<?php
session_start();
require_once '../config/database.php';

$deleteStmt = $pdo->prepare("DELETE FROM reports WHERE submitted_at < NOW() - INTERVAL 30 DAY");
$deleteStmt->execute();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die('Access denied');
}

$stmt = $pdo->query("SELECT r.*, e.name FROM reports r JOIN employees e ON r.employee_id = e.id ORDER BY r.submitted_at DESC");
$reports = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Reports</title>
    <style>
        .report {
            border: 1px solid #ccc;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            background-color: #f9f9f9;
        }
        .report h4 {
            margin: 0 0 6px;
        }
        .report small {
            color: #666;
        }
    </style>
</head>
<body>
<p><a href="dashboard.php">Back to Dashboard</a></p>
    <h2>Submitted Employee Concerns</h2>

    <?php foreach ($reports as $report): ?>
        <div class="report">
            <h4><?= htmlspecialchars($report['subject']) ?> <small>from <?= htmlspecialchars($report['name']) ?></small></h4>
            <p><?= nl2br(htmlspecialchars($report['message'])) ?></p>
            <small>Submitted: <?= $report['submitted_at'] ?></small>
        </div>
    <?php endforeach; ?>
</body>
</html>
