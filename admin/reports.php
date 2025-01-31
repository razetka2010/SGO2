<?php
session_start();
require_once '../lib/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth.php");
    exit();
}

$uploadDir = '../data/';

// Add Report
if (isset($_POST['addReport'])) {
    $name = $_POST['name'];
    $file_name = $_FILES['report_file']['name'];
    $file_tmp = $_FILES['report_file']['tmp_name'];
    $file_path = $uploadDir . $file_name;

    if (move_uploaded_file($file_tmp, $file_path)) {
        $sql_insert = "INSERT INTO reports (name, file_path) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ss", $name, $file_path);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
     header("Location: reports.php");
    exit();
}

// Edit Report
if (isset($_POST['editReport'])) {
    $reportId = $_POST['editId'];
    $name = $_POST['name'];

    $file_name = $_FILES['report_file']['name'];
    $file_tmp = $_FILES['report_file']['tmp_name'];
      if($file_name != ''){
          $file_path = $uploadDir . $file_name;
          move_uploaded_file($file_tmp, $file_path);
          $sql_update = "UPDATE reports SET name = ?, file_path = ? WHERE id = ?";
         $stmt_update = $conn->prepare($sql_update);
         $stmt_update->bind_param("ssi", $name, $file_path, $reportId);
         $stmt_update->execute();
         $stmt_update->close();
      }else{
            $sql_update = "UPDATE reports SET name = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $name, $reportId);
            $stmt_update->execute();
            $stmt_update->close();
      }
    header("Location: reports.php");
     exit();
}
// Delete Report
if (isset($_GET['delete'])) {
    $reportId = $_GET['delete'];

    $sql_select = "SELECT file_path FROM reports WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $reportId);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $file_data = $result->fetch_assoc();
    $file_path = $file_data['file_path'];
    $stmt_select->close();


    if(file_exists($file_path)){
        unlink($file_path);
    }

    $sql_delete = "DELETE FROM reports WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $reportId);
    $stmt_delete->execute();
    $stmt_delete->close();
    header("Location: reports.php");
    exit();
}
$sql = "SELECT * FROM reports";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление отчётами</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Управление отчётами</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Ссылка на файл</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><a href="<?php echo $row['file_path']; ?>" download>Скачать</a></td>
                        <td>
                            <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo $row['name']; ?>')">Редактировать</button>
                            <a href="reports.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Вы уверены?');">Удалить</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">Нет отчётов</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <br>
        <button onclick="document.getElementById('addReportModal').style.display='block'">Добавить отчёт</button>
    </div>
      <!-- Add Report Modal -->
    <div id="addReportModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addReportModal').style.display='none'">&times;</span>
            <h2>Добавить отчёт</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Название отчёта" required><br>
                <input type="file" name="report_file" required><br>
                <button type="submit" name="addReport">Добавить</button>
            </form>
        </div>
    </div>
    <!-- Edit Report Modal -->
    <div id="editReportModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editReportModal').style.display='none'">&times;</span>
            <h2>Редактировать отчёт</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="editId" id="editId">
                <input type="text" name="name" id="editName" placeholder="Название отчёта" required><br>
                 <input type="file" name="report_file"><br>
                <button type="submit" name="editReport">Сохранить</button>
            </form>
        </div>
    </div>
<script>
     function openEditModal(id, name) {
        document.getElementById('editId').value = id;
         document.getElementById('editName').value = name;
         document.getElementById('editReportModal').style.display = 'block';
    }
     var modal = document.getElementById("addReportModal");
     var modalEdit = document.getElementById("editReportModal");
    var close = document.querySelectorAll(".close");
     close.forEach(function(closeButton) {
        closeButton.onclick = function() {
            modal.style.display = "none";
             modalEdit.style.display = "none";
        }
    });
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
        if (event.target == modalEdit) {
            modalEdit.style.display = "none";
        }
    }
</script>
</body>
</html>