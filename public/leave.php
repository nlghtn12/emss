<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    die('Access denied');
}

$user = $_SESSION['user'];
$error = "";
$success = "";

// Handle leave request by employee
if ($user['role'] === 'employee' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO leaves (employee_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user['id'], $_POST['start_date'], $_POST['end_date'], $_POST['reason']]);
    $success = "Leave request submitted.";
}

// Handle admin approval/rejection
if ($user['role'] === 'admin' && isset($_GET['action'], $_GET['id'])) {
    $status = ($_GET['action'] === 'approve') ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE leaves SET status = ? WHERE id = ?");
    $stmt->execute([$status, $_GET['id']]);
    $success = "Leave request has been $status.";
}

if ($user['role'] === 'admin') {
    $stmt = $pdo->query("SELECT l.*, e.name FROM leaves l JOIN employees e ON l.employee_id = e.id ORDER BY l.id DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM leaves WHERE employee_id = ? ORDER BY id DESC");
    $stmt->execute([$user['id']]);
}

$leaves = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>Leave Requests</title></head>
<body>
<p><a href="dashboard.php">Back to Dashboard</a></p>
<h2><?= $user['role'] === 'admin' ? 'Manage Leave Requests' : 'Request Leave' ?></h2>

<?php if (!empty($success)): ?>
    <div style="background: #d4edda; padding: 10px; color: #155724;"><?= $success ?></div>
<?php endif; ?>

<?php if ($user['role'] === 'employee'): ?>
<form method="POST">
    <label>Start Date: <input type="date" name="start_date" required></label><br>
    <label>End Date: <input type="date" name="end_date" required></label><br>
    <label>Reason: <textarea name="reason" required></textarea></label><br>
    <button type="submit">Submit Leave Request</button>
</form>
<hr>
<?php endif; ?>

<h3><?= $user['role'] === 'admin' ? 'Leave Requests' : 'My Leave History' ?></h3>
<table border="1">
<tr>
    <?php if ($user['role'] === 'admin'): ?>
        <th>Employee</th>
    <?php endif; ?>
    <th>Start Date</th><th>End Date</th><th>Reason</th><th>Status</th>
    <?php if ($user['role'] === 'admin'): ?>
        <th>Actions</th>
    <?php endif; ?>
</tr>
<?php foreach ($leaves as $l): ?>
<tr>
    <?php if ($user['role'] === 'admin'): ?>
        <td><?= htmlspecialchars($l['name']) ?></td>
    <?php endif; ?>
    <td><?= htmlspecialchars($l['start_date']) ?></td>
    <td><?= htmlspecialchars($l['end_date']) ?></td>
    <td><?= htmlspecialchars($l['reason']) ?></td>
    <td><?= htmlspecialchars($l['status']) ?></td>
    <?php if ($user['role'] === 'admin'): ?>
        <td>
            <?php if ($l['status'] === 'pending'): ?>
                <a href="?action=approve&id=<?= $l['id'] ?>">Approve</a> |
                <a href="?action=reject&id=<?= $l['id'] ?>">Reject</a>
            <?php else: ?>
                <em>No actions</em>
            <?php endif; ?>
        </td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>