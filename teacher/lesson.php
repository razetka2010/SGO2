<?php
session_start();
require_once '../lib/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../auth.php");
    exit();
}
$sql_subjects = "SELECT DISTINCT subject FROM journal";
$result_subjects = $conn->query($sql_subjects);
$selected_subject = $_GET['subject'] ?? '';
if(isset($_POST['addLesson'])){
    $subject = $_POST['subject'];
    $theme = $_POST['theme'];
       $sql_insert = "INSERT INTO lessons (subject, theme) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ss", $subject, $theme);
    $stmt_insert->execute();
    $stmt_insert->close();
    header("Location: lesson.php?subject=".$selected_subject);
    exit();
}
if(isset($_POST['editLesson'])){
    $editId = $_POST['editId'];
     $theme = $_POST['theme'];
       $sql_update = "UPDATE lessons SET theme = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $theme, $editId);
        $stmt_update->execute();
        $stmt_update->close();
      header("Location: lesson.php?subject=".$selected_subject);
    exit();
}
if(isset($_GET['delete'])){
      $deleteId = $_GET['delete'];
    $sql_delete = "DELETE FROM lessons WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $deleteId);
        $stmt_delete->execute();
        $stmt_delete->close();
        header("Location: lesson.php?subject=".$selected_subject);
        exit();
}
if (!empty($selected_subject)) {
    $sql = "SELECT * FROM lessons WHERE subject = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_subject);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Темы уроков</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="teacher-container">
        <h1>Темы уроков</h1>
           <form method="GET">
            <label for="subject">Предмет:</label>
            <select name="subject" id="subject" required>
                <option value="">Выберите предмет</option>
                <?php while ($subject_row = $result_subjects->fetch_assoc()): ?>
                    <option value="<?php echo $subject_row['subject']; ?>" <?php if ($selected_subject == $subject_row['subject']) echo 'selected'; ?>><?php echo $subject_row['subject']; ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Показать темы уроков</button>
        </form>
          <?php if (!empty($selected_subject)): ?>
              <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Тема урока</th>
                         <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                   <?php if($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                           <tr>
                                <td><?php echo $row['id']; ?></td>
                                 <td><?php echo $row['theme']; ?></td>
                                 <td>
                                      <button onclick="openEditLessonModal(<?php echo $row['id']; ?>, '<?php echo $row['theme']; ?>')">Редактировать</button>
                                     <a href="lesson.php?delete=<?php echo $row['id']; ?>&subject=<?php echo $selected_subject; ?>" onclick="return confirm('Вы уверены?');">Удалить</a>
                                 </td>
                           </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                         <tr><td colspan="3">Нет уроков по данному предмету</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
             <br>
              <button onclick="document.getElementById('addLessonModal').style.display='block'">Добавить тему урока</button>
              <?php endif; ?>
    </div>
       <!-- Add Lesson Modal -->
    <div id="addLessonModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addLessonModal').style.display='none'">&times;</span>
            <h2>Добавить тему урока</h2>
            <form method="post">
                  <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
                <input type="text" name="theme" placeholder="Тема урока" required>
                <button type="submit" name="addLesson">Добавить</button>
            </form>
        </div>
    </div>
     <!-- Edit Lesson Modal -->
    <div id="editLessonModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editLessonModal').style.display='none'">&times;</span>
            <h2>Редактировать тему урока</h2>
            <form method="post">
                 <input type="hidden" name="editId" id="editId">
                <input type="text" name="theme" id="editTheme" placeholder="Тема урока" required>
                <button type="submit" name="editLesson">Сохранить</button>
            </form>
        </div>
    </div>
<script>
     function openEditLessonModal(id, theme) {
        document.getElementById('editId').value = id;
         document.getElementById('editTheme').value = theme;
          document.getElementById('editLessonModal').style.display = 'block';
    }
      var modal = document.getElementById("addLessonModal");
        var modalEdit = document.getElementById("editLessonModal");
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