<?php
session_start();
require_once '../config/database.php';
require_once '../libs/tcpdf/tcpdf.php';

if (!isset($_SESSION['user']) || !isset($_POST['emp_id'])) {
    die("Access denied.");
}

$user = $_SESSION['user'];
$emp_id = (int)$_POST['emp_id'];

// Admins can download any, employees only their own
if ($user['role'] !== 'admin' && $user['id'] !== $emp_id) {
    die("Unauthorized access.");
}

$stmt = $pdo->prepare("SELECT e.name, e.department, ev.* FROM evaluations ev JOIN employees e ON ev.employee_id = e.id WHERE ev.employee_id = ? ORDER BY ev.evaluated_at DESC LIMIT 1");
$stmt->execute([$emp_id]);
$evaluation = $stmt->fetch();

if (!$evaluation) {
    die("No evaluation found.");
}

$pdf = new TCPDF();
$pdf->AddPage();

$html = '<h2>Performance Evaluation</h2>';
$html .= '<strong>Employee:</strong> ' . htmlspecialchars($evaluation['name']) . '<br>';
$html .= '<strong>Department:</strong> ' . htmlspecialchars($evaluation['department']) . '<br>';
$html .= '<strong>Evaluated At:</strong> ' . htmlspecialchars($evaluation['evaluated_at']) . '<br><br>';
$html .= '<strong>Goal:</strong><br>' . nl2br(htmlspecialchars($evaluation['goal'])) . '<br><br>';
$html .= '<strong>Reality:</strong><br>' . nl2br(htmlspecialchars($evaluation['reality'])) . '<br><br>';
$html .= '<strong>Way Forward:</strong><br>' . nl2br(htmlspecialchars($evaluation['way_forward'])) . '<br><br>';
$html .= '<strong>Remarks:</strong><br>' . nl2br(htmlspecialchars($evaluation['remarks'])) . '<br>';

$pdf->writeHTML($html);
$pdf->Output('evaluation.pdf', 'D');
$pdf->Output('evaluation_' . preg_replace('/\s+/', '_', $evaluation['name']) . '.pdf', 'D');
exit;
?>
