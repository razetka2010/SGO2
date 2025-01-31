<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$userRole = $_SESSION['user_role'];
switch ($userRole) {
    case 'admin':
        header("Location: admin/index.php");
        break;
    case 'teacher':
        header("Location: teacher/index.php");
        break;
    case 'deputy':
        header("Location: deputy/index.php");
        break;
    case 'student':
        header("Location: student/index.php");
        break;
    default:
        echo "Unknown role";
}

exit();
?>