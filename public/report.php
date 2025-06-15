<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee') {
    die('Access denied');
}

$user = $_SESSION['user'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO reports (employee_id, subject, message) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $_POST['subject'], $_POST['message']]);
    $message = "Report submitted successfully.";
}
?>

<!DOCTYPE html>
<html>
<head><title>Report to Admin</title></head>
<body>
<p><a href="dashboard.php">Back to Dashboard</a></p>
    <h2>Submit a Report</h2>
    <?php if ($message) echo "<p style='color: green;'>$message</p>"; ?>
    <form method="POST">
        <label>Subject: <input type="text" name="subject" required></label><br><br>
        <label>Message:<br>
            <textarea name="message" rows="5" cols="50" required></textarea>
        </label><br><br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
