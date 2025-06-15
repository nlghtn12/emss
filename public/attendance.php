
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include '../config/database.php';
$user = $_SESSION['user'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clock_in'])) {
        $check = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = CURDATE()");
        $check->execute([$user['id']]);
        if ($check->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, time_in, date) VALUES (?, NOW(), CURDATE())");
            $stmt->execute([$user['id']]);
            $message = "Clock-in recorded.";
        } else {
            $message = "You already clocked in today.";
        }
    } elseif (isset($_POST['clock_out'])) {
        $stmt = $pdo->prepare("UPDATE attendance SET time_out = NOW() WHERE employee_id = ? AND date = CURDATE() AND time_out IS NULL");
        $stmt->execute([$user['id']]);
        $message = "Clock-out recorded.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? ORDER BY date DESC");
$stmt->execute([$user['id']]);
$records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance</title>
</head>
<body>
<p><a href="dashboard.php">Back to Dashboard</a></p>
    <h2>Attendance for <?php echo htmlspecialchars($user['name']); ?></h2>
    <form method="POST">
        <button type="submit" name="clock_in">Clock In</button>
        <button type="submit" name="clock_out">Clock Out</button>
    </form>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    <table border="1">
        <tr><th>Date</th><th>Time In</th><th>Time Out</th></tr>
        <?php foreach ($records as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
                <td><?php echo htmlspecialchars($row['time_in']); ?></td>
                <td><?php echo htmlspecialchars($row['time_out']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
