<?php
session_start();

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>EMS Dashboard</title>
</head>
<body>
    
    <h2>Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>
    <p>Today is <?= date('l, F j, Y') ?></p>

    <?php if ($user['role'] === 'admin'): ?>
        <ul>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="announcement.php">Announcement</a></li>
            <li><a href="employee.php">Manage Employees</a></li>
            <li><a href="payslip.php">View Payslips</a></li>
            <li><a href="attendance.php">Manage Attendance</a></li>
            <li><a href="leave.php">Approve Leaves</a></li>
            <li><a href="view_report.php">Employee Reports</a></li>
            <li><a href="evaluation.php">Coaching</a></li>

        </ul>
    <?php else: ?>
        <ul>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="announcement.php">Announcement</a></li>
            <li><a href="attendance.php">My Attendance</a></li>
            <li><a href="leave.php">My Leave Requests</a></li>
            <li><a href="payslip.php">My Payslips</a></li>
            <li><a href="report.php">Report to Admin</a></li>
            <li><a href="evaluation.php">Coaching</a></li>
        </ul>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
