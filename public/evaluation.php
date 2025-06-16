<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    die('Access denied');
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Evaluation</title>
</head>
<body>
<p><a href="dashboard.php">Back to Dashboard</a></p>

<?php
// ADMIN VIEW
if ($user['role'] === 'admin') {
    // Evaluation form view
    if (isset($_GET['evaluate'])) {
        $employee_id = $_GET['evaluate'];
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$employee_id]);
        $employee = $stmt->fetch();

        if (!$employee) {
            die("Employee not found.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_eval'])) {
            $stmt = $pdo->prepare("INSERT INTO evaluations (employee_id, goal, reality, way_forward, remarks, evaluator_id, evaluated_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $employee_id,
                $_POST['goal'],
                $_POST['reality'],
                $_POST['way_forward'],
                $_POST['remarks'],
                $user['id']
            ]);
            echo "<p style='color: green;'>Evaluation submitted successfully.</p>";
        }

        echo "<h2>Evaluate: " . htmlspecialchars($employee['name']) . "</h2>";
        ?>
        <form method="POST">
            <label>Goal:<br><textarea name="goal" required></textarea></label><br>
            <label>Reality:<br><textarea name="reality" required></textarea></label><br>
            <label>Way Forward:<br><textarea name="way_forward" required></textarea></label><br>
            <label>Remarks:<br><textarea name="remarks"></textarea></label><br><br>
            <button type="submit" name="submit_eval">Submit Evaluation</button>
        </form>
        <br><a href="evaluation.php">Back to list</a>
        <?php
    } else {
        // Employee list
        if (isset($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $stmt = $pdo->prepare("SELECT id, name, department FROM employees WHERE role = 'employee' AND name LIKE ?");
            $stmt->execute([$search]);
            $employees = $stmt->fetchAll();
        } else {
            $employees = $pdo->query("SELECT id, name, department FROM employees WHERE role = 'employee'")->fetchAll();
        }

        echo "<h2>Employee List</h2>";
        echo '<form method="GET"><input type="text" name="search" placeholder="Search employee..."><button type="submit">Search</button></form>';
        echo "<table border='1'><tr><th>Name</th><th>Department</th><th>Action</th><th>Latest Evaluation</th><th>Download PDF</th></tr>";

        foreach ($employees as $emp) {
            echo "<tr>
                <td>" . htmlspecialchars($emp['name']) . "</td>
                <td>" . htmlspecialchars($emp['department']) . "</td>
                <td><a href='evaluation.php?evaluate=" . $emp['id'] . "'>Evaluate</a></td>";

            $stmt = $pdo->prepare("SELECT * FROM evaluations WHERE employee_id = ? ORDER BY evaluated_at DESC LIMIT 1");
            $stmt->execute([$emp['id']]);
            $evaluation = $stmt->fetch();

            echo "<td>" . ($evaluation ? nl2br(htmlspecialchars($evaluation['goal'])) : 'No evaluation') . "</td>";
            echo "<td>";
            if ($evaluation) {
                echo "<form method='POST' action='evaluation_pdf.php' target='_blank'>
                        <input type='hidden' name='emp_id' value='" . $emp['id'] . "'>
                        <button type='submit' name='admin_download_pdf'>Download PDF</button>
                      </form>";
            } else {
                echo "-";
            }
            echo "</td></tr>";
        }
        echo "</table>";
    }
}

// EMPLOYEE VIEW
else {
    $stmt = $pdo->prepare("SELECT * FROM evaluations WHERE employee_id = ? ORDER BY evaluated_at DESC LIMIT 1");
    $stmt->execute([$user['id']]);
    $evaluation = $stmt->fetch();

    echo "<h2>My Evaluation</h2>";
    if ($evaluation) {
        echo "<table border='1' cellpadding='5'>
            <tr><td><strong>Goal:</strong></td><td>" . nl2br(htmlspecialchars($evaluation['goal'])) . "</td></tr>
            <tr><td><strong>Reality:</strong></td><td>" . nl2br(htmlspecialchars($evaluation['reality'])) . "</td></tr>
            <tr><td><strong>Way Forward:</strong></td><td>" . nl2br(htmlspecialchars($evaluation['way_forward'])) . "</td></tr>
            <tr><td><strong>Remarks:</strong></td><td>" . nl2br(htmlspecialchars($evaluation['remarks'])) . "</td></tr>
            <tr><td><strong>Evaluated At:</strong></td><td>" . htmlspecialchars($evaluation['evaluated_at']) . "</td></tr>
        </table><br>
        <form method='POST' action='evaluation_pdf.php' target='_blank'>
            <input type='hidden' name='emp_id' value='" . $user['id'] . "'>
            <button name='employee_download_pdf'>Download as PDF</button>
        </form>";
    } else {
        echo "<p>No evaluation available yet.</p>";
    }
}
?>
</body>
</html>
