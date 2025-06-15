<?php
session_start();
require_once '../config/database.php';
require_once '../libs/tcpdf/tcpdf.php';

if (!isset($_SESSION['user'])) {
    die('Access denied');
}

$user = $_SESSION['user'];

// Admin can download their own payslip by ID, or employees get their own
if ($user['role'] === 'admin' && isset($_GET['employee_id'])) {
    $stmt = $pdo->prepare("SELECT p.*, e.name FROM payslips p JOIN employees e ON p.employee_id = e.id WHERE p.employee_id = ? ORDER BY issue_date DESC LIMIT 1");
    $stmt->execute([$_GET['employee_id']]);
} else {
    $stmt = $pdo->prepare("SELECT p.*, e.name FROM payslips p JOIN employees e ON p.employee_id = e.id WHERE employee_id = ? ORDER BY issue_date DESC LIMIT 1");
    $stmt->execute([$user['id']]);
}

$payslip = $stmt->fetch();

if (!$payslip) {
    die("No payslip available.");
}

$pdf = new TCPDF();
$pdf->AddPage();

$html = '<h2>Payslip</h2>';
$html .= '<strong>Employee:</strong> ' . htmlspecialchars($payslip['name']) . '<br>';
$html .= '<strong>Basic Salary:</strong> ' . number_format($payslip['basic_salary'], 2) . '<br>';
$html .= '<strong>Deductions:</strong> ' . number_format($payslip['deductions'], 2) . '<br>';
$html .= '<strong>Net Pay:</strong> ' . number_format($payslip['net_pay'], 2) . '<br>';
$html .= '<strong>Issue Date:</strong> ' . htmlspecialchars($payslip['issue_date']) . '<br>';

$pdf->writeHTML($html);
$pdf->Output('payslip.pdf', 'D'); // force download
