<?php
session_start();
require_once 'lib/db.php'; // Подключаем файл с подключением к БД

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: dashboard.php");
        exit();
    } else {
      echo "<p style='color:red;'>Неверный логин или пароль.</p>";
    }
} else {
   echo "<p style='color:red;'>Неверный логин или пароль.</p>";
}
?>