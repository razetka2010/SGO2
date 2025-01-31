<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'deputy') {
    header("Location: ../auth.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель завуча</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="deputy-container">
        <h1>Панель завуча</h1>
        <nav>
            <a href="schedule.php">Расписание</a>
            <a href="classes.php">Классы</a>
            <a href="poster.php">Афиша</a>
            <a href="../logout.php">Выход</a>
        </nav>
    </div>
</body>
</html>