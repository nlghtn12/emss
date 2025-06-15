<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    die('Access denied');
}

$user = $_SESSION['user'];
$search = $_GET['search'] ?? '';

$payslips = [];

if ($user['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT p.*, e.name FROM payslips p JOIN employees e ON p.employee_id = e.id WHERE p.employee_id = ?");
    $stmt->execute([$user['id']]);
    $adminPayslip = $stmt->fetchAll();

    $where = "WHERE p.employee_id != ?";
    $params = [$user['id']];

    if (!empty($search)) {
        $where .= " AND e.name LIKE ?";
        $params[] = "%$search%";
    }

    $stmt = $pdo->prepare("SELECT p.*, e.name FROM payslips p JOIN employees e ON p.employee_id = e.id $where");
    $stmt->execute($params);
    $otherPayslips = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT p.*, e.name FROM payslips p JOIN employees e ON p.employee_id = e.id WHERE p.employee_id = ?");
    $stmt->execute([$user['id']]);
    $payslips = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head><title>Payslips</title></head>
<body>
<p><a href="dashboard.php">Back to Dashboard</a></p>
<h2><?= $user['role'] === 'admin' ? 'Payslips Overview' : 'My Payslips' ?></h2>

<?php if ($user['role'] === 'admin'): ?>
    <h3>Admin Payslip</h3>
    <table border="1">
        <tr>
            <th>Employee</th><th>Basic Salary</th><th>Deductions</th><th>Net Pay</th><th>Issue Date</th><th>PDF</th>
        </tr>
        <?php foreach ($adminPayslip as $pay): ?>
        <tr>
            <td><?= htmlspecialchars($pay['name']) ?></td>
            <td><?= htmlspecialchars($pay['basic_salary']) ?></td>
            <td><?= htmlspecialchars($pay['deductions']) ?></td>
            <td><?= htmlspecialchars($pay['net_pay']) ?></td>
            <td><?= htmlspecialchars($pay['issue_date']) ?></td>
            <td><a href="download_payslip.php?employee_id=<?= $user['id'] ?>">Download PDF</a></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Employee Payslips</h3>
    <form method="GET">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search employee name">
        <button type="submit">Search</button>
    </form>
    <br>
    <table border="1">
        <tr>
            <th>Employee</th><th>Basic Salary</th><th>Deductions</th><th>Net Pay</th><th>Issue Date</th><th>PDF</th>
        </tr>
        <?php foreach ($otherPayslips as $pay): ?>
        <tr>
            <td><?= htmlspecialchars($pay['name']) ?></td>
            <td><?= htmlspecialchars($pay['basic_salary']) ?></td>
            <td><?= htmlspecialchars($pay['deductions']) ?></td>
            <td><?= htmlspecialchars($pay['net_pay']) ?></td>
            <td><?= htmlspecialchars($pay['issue_date']) ?></td>
            <td><a href="download_payslip.php?employee_id=<?= $pay['employee_id'] ?>">Download PDF</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <table border="1">
        <tr>
            <th>Employee</th><th>Basic Salary</th><th>Deductions</th><th>Net Pay</th><th>Issue Date</th><th>Action</th>
        </tr>
        <?php foreach ($payslips as $pay): ?>
        <tr>
            <td><?= htmlspecialchars($pay['name']) ?></td>
            <td><?= htmlspecialchars($pay['basic_salary']) ?></td>
            <td><?= htmlspecialchars($pay['deductions']) ?></td>
            <td><?= htmlspecialchars($pay['net_pay']) ?></td>
            <td><?= htmlspecialchars($pay['issue_date']) ?></td>
            <td><a href="download_payslip.php" target="_blank">Download PDF</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>


</body>
</html>
