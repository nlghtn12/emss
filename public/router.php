<?php
session_start();
$routes = [
    '' => 'login.php',
    'login' => 'login.php',
    'logout' => 'logout.php',
    'dashboard' => 'dashboard.php',
    'attendance' => 'attendance.php',
    'leave' => 'leave.php',
    'payslip' => 'payslip.php',
    'profile' => 'profile.php',          
    'announcement' => 'announcement.php',
    'report' => 'report.php',
    'view_report' => 'view_report.php'
];


$page = $_GET['page'] ?? '';
if (array_key_exists($page, $routes)) {
    require $routes[$page];
} else {
    http_response_code(404);
    echo "404 Not Found";
}
?>