<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$updated = false;

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $username = $_POST['username'];
    $password = $_POST['password'] ?? '';

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE employees SET name=?, email=?, age=?, username=?, password=? WHERE id=?");
        $stmt->execute([$name, $email, $age, $username, $hashed, $user['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE employees SET name=?, email=?, age=?, username=? WHERE id=?");
        $stmt->execute([$name, $email, $age, $username, $user['id']]);
    }

    // Refresh session data
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id=?");
    $stmt->execute([$user['id']]);
    $newUser = $stmt->fetch();
    if ($newUser) {
        $_SESSION['user'] = $newUser;
        $user = $newUser;
        $updated = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <script>
        function enableEdit() {
            const fields = document.querySelectorAll('.editable');
            fields.forEach(field => field.removeAttribute('readonly'));
            document.getElementById('saveBtn').style.display = 'inline';
            document.getElementById('editBtn').style.display = 'none';
        }
    </script>
</head>
<body>
    <h2>My Profile</h2>
    <?php if ($updated): ?>
        <p style="color:green;">Profile updated successfully.</p>
    <?php endif; ?>

    <form method="POST">
        <label>Name: <input type="text" class="editable" name="name" value="<?= htmlspecialchars($user['name']) ?>" readonly required></label><br>
        <label>Email: <input type="email" class="editable" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly required></label><br>
        <label>Age: <input type="number" class="editable" name="age" value="<?= htmlspecialchars($user['age']) ?>" readonly required></label><br>
        <label>Username: <input type="text" class="editable" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly required></label><br>
        <label>Password: <input type="password" class="editable" name="password" placeholder="Leave blank to keep current password" readonly></label><br>
        <label>Department: <input type="text" value="<?= htmlspecialchars($user['department']) ?>" readonly></label><br><br>

        <button type="button" id="editBtn" onclick="enableEdit()">Edit</button>
        <button type="submit" id="saveBtn" name="save" style="display:none;">Save</button>
    </form>
</body>
</html>
