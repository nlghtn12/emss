<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die('Access denied');
}

// Insert new employee
if (isset($_POST['create'])) {
    $stmt = $pdo->prepare("INSERT INTO employees (name, email, age, username, password, department, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['email'],
        $_POST['age'],
        $_POST['username'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $_POST['department'],
        $_POST['role']
    ]);

    $employee_id = $pdo->lastInsertId();
    $basic = $_POST['basic_salary'];
    $deductions = $_POST['deductions'];
    $net = $basic - $deductions;

    $stmt2 = $pdo->prepare("INSERT INTO payslips (employee_id, basic_salary, deductions, net_pay, issue_date) VALUES (?, ?, ?, ?, CURDATE())");
    $stmt2->execute([$employee_id, $basic, $deductions, $net]);

    header("Location: employee.php");
    exit;
}

// Delete employee
if (isset($_GET['delete'])) {
    $employeeId = $_GET['delete'];

    // Delete dependent records first
    $pdo->prepare("DELETE FROM leaves WHERE employee_id = ?")->execute([$employeeId]);
    $pdo->prepare("DELETE FROM attendance WHERE employee_id = ?")->execute([$employeeId]);
    $pdo->prepare("DELETE FROM payslips WHERE employee_id = ?")->execute([$employeeId]);
    $pdo->prepare("DELETE FROM announcements WHERE created_by = ?")->execute([$employeeId]);
    $pdo->prepare("DELETE FROM reports WHERE employee_id = ?")->execute([$employeeId]); // if reports table exists

    // Then delete the employee
    $pdo->prepare("DELETE FROM employees WHERE id = ?")->execute([$employeeId]);

    header("Location: employee.php");
    exit;
}


// Update employee
if (isset($_POST['update'])) {
    $stmt = $pdo->prepare("UPDATE employees SET name=?, email=?, age=?, username=?, department=?, role=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['email'],
        $_POST['age'],
        $_POST['username'],
        $_POST['department'],
        $_POST['role'],
        $_POST['id']
    ]);

    $net = $_POST['basic_salary'] - $_POST['deductions'];
    $stmt2 = $pdo->prepare("UPDATE payslips SET basic_salary=?, deductions=?, net_pay=? WHERE employee_id=?");
    $stmt2->execute([$_POST['basic_salary'], $_POST['deductions'], $net, $_POST['id']]);

    header("Location: employee.php");
    exit;
}

// Cancel editing
if (isset($_GET['cancel'])) {
    header("Location: employee.php");
    exit;
}

// Edit form
$edit = null;
$payslip = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();

    $stmt2 = $pdo->prepare("SELECT * FROM payslips WHERE employee_id = ?");
    $stmt2->execute([$_GET['edit']]);
    $payslip = $stmt2->fetch();
}

// Pagination
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$where = "";
$params = [];
if (!empty($search)) {
    $where = "WHERE name LIKE ? OR email LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM employees $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$pages = ceil($total / $limit);

$stmt = $pdo->prepare("SELECT * FROM employees $where LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$employees = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head><title>Manage Employees</title></head>
<body>
<p><a href="dashboard.php">Back to Dashboard</a></p>
<h2><?= $edit ? 'Edit Employee' : 'Add New Employee' ?></h2>

<form method="POST">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
    <label>Name: <input type="text" name="name" value="<?= $edit['name'] ?? '' ?>" required></label><br>
    <label>Email: <input type="email" name="email" value="<?= $edit['email'] ?? '' ?>" required></label><br>
    <label>Age: <input type="number" name="age" value="<?= $edit['age'] ?? '' ?>" required></label><br>
    <label>Username: <input type="text" name="username" value="<?= $edit['username'] ?? '' ?>" required></label><br>
    <?php if (!$edit): ?>
        <label>Password: <input type="password" name="password" required></label><br>
    <?php endif; ?>
    <label>Department: <input type="text" name="department" value="<?= $edit['department'] ?? '' ?>" required></label><br>
    <label>Role:
        <select name="role">
            <option value="employee" <?= ($edit['role'] ?? '') === 'employee' ? 'selected' : '' ?>>Employee</option>
            <option value="admin" <?= ($edit['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </label><br>
    <label>Basic Salary: <input type="number" step="0.01" name="basic_salary" value="<?= $payslip['basic_salary'] ?? '' ?>" required></label><br>
    <label>Deductions: <input type="number" step="0.01" name="deductions" value="<?= $payslip['deductions'] ?? '' ?>" required></label><br><br>
    <button type="submit" name="<?= $edit ? 'update' : 'create' ?>">
        <?= $edit ? 'Update' : 'Add' ?> Employee
    </button>
    <?php if ($edit): ?>
        <a href="?cancel=1" style="margin-left: 15px;">Cancel</a>
    <?php endif; ?>
</form>

<h2>Employees</h2>
<form method="GET">
    <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search by name or email">
    <button type="submit">Search</button>
</form><br>

<table border="1">
<tr><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Password (Hashed)</th><th>Actions</th></tr>
<?php foreach ($employees as $emp): ?>
<tr>
    <td><?= htmlspecialchars($emp['name']) ?></td>
    <td><?= htmlspecialchars($emp['email']) ?></td>
    <td><?= htmlspecialchars($emp['username']) ?></td>
    <td><?= htmlspecialchars($emp['role']) ?></td>
    <td><?= $emp['password'] ?></td>
    <td>
        <a href="?edit=<?= $emp['id'] ?>">Edit</a> |
        <a href="?delete=<?= $emp['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<br><div>
<?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
<?php endfor; ?>
</div>
</body>
</html>
