<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../auth.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель учителя</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="teacher-container">
        <h1>Панель учителя</h1>
        <nav>
            <a href="journal.php">Журнал</a>
            <a href="lesson.php">Темы уроков</a>
            <a href="../logout.php">Выход</a>
        </nav>
    </div>
</body>
</html>