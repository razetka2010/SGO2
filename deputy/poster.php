<?php
session_start();
require_once '../lib/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'deputy') {
    header("Location: ../auth.php");
    exit();
}
$uploadDir = '../images/';
if (isset($_POST['uploadPoster'])) {
    $file_name = $_FILES['poster_file']['name'];
    $file_tmp = $_FILES['poster_file']['tmp_name'];
    $file_path = $uploadDir . $file_name;
        move_uploaded_file($file_tmp, $file_path);
        $sql_update = "UPDATE settings SET poster_path = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $file_path);
        $stmt_update->execute();
         $stmt_update->close();
          header("Location: poster.php");
        exit();
}
$sql = "SELECT poster_path FROM settings";
$result = $conn->query($sql);
$poster_path = "";
if ($result->num_rows > 0) {
  $row =  $result->fetch_assoc();
  $poster_path = $row['poster_path'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление афишей</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="deputy-container">
        <h1>Управление афишей</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="poster_file" required>
            <button type="submit" name="uploadPoster">Загрузить афишу</button>
        </form>
    <?php if (!empty($poster_path)) { ?>
         <img src="" alt="Афиша" style="max-width: 300px;">
    <?php }?>
    </div>
</body>
</html>