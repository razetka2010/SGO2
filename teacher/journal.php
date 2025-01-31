<?php
session_start();
require_once '../lib/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../auth.php");
    exit();
}
$sql_classes = "SELECT DISTINCT class FROM students";
$result_classes = $conn->query($sql_classes);
$sql_subjects = "SELECT DISTINCT subject FROM journal";
$result_subjects = $conn->query($sql_subjects);
$selected_class = $_GET['class'] ?? '';
$selected_subject = $_GET['subject'] ?? '';
if (isset($_POST['addMark'])) {
     $student_id = $_POST['student_id'];
      $mark = $_POST['mark'];
    $sql_insert = "INSERT INTO journal (student_id, subject, mark) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isi", $student_id, $selected_subject, $mark);
    $stmt_insert->execute();
    $stmt_insert->close();
    header("Location: journal.php?class=".$selected_class."&subject=".$selected_subject);
    exit();
}
if(isset($_POST['editMark'])){
   $editId = $_POST['editId'];
   $mark = $_POST['mark'];
     $sql_update = "UPDATE journal SET mark = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $mark, $editId);
        $stmt_update->execute();
        $stmt_update->close();
      header("Location: journal.php?class=".$selected_class."&subject=".$selected_subject);
    exit();
}
if(isset($_GET['delete'])){
   $deleteId = $_GET['delete'];
    $sql_delete = "DELETE FROM journal WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $deleteId);
        $stmt_delete->execute();
        $stmt_delete->close();
      header("Location: journal.php?class=".$selected_class."&subject=".$selected_subject);
    exit();
}
if (!empty($selected_class) && !empty($selected_subject)) {
    $sql = "SELECT students.id as student_id, users.name, journal.id as
    journal_id, journal.mark, attendance.attendance_date FROM students
            JOIN users ON students.user_id = users.id
            LEFT JOIN journal ON students.id = journal.student_id AND journal.subject = ?
             LEFT JOIN attendance ON students.id = attendance.student_id AND attendance.attendance_date = CURDATE()
             WHERE students.class = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $selected_subject, $selected_class);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}
// Handle Attendance
if (isset($_POST['updateAttendance'])) {
    $student_id = $_POST['student_id'];
      $attendance_date = date("Y-m-d");
    if (isset($_POST['attendance'])) {
       $sql_check = "SELECT * FROM attendance WHERE student_id = ? AND attendance_date = ?";
      $stmt_check = $conn->prepare($sql_check);
      $stmt_check->bind_param("is", $student_id, $attendance_date);
      $stmt_check->execute();
      $check_result = $stmt_check->get_result();
      $stmt_check->close();
      if ($check_result->num_rows > 0) {
           $sql_update = "UPDATE attendance SET attendance_date = ? WHERE student_id = ?";
           $stmt_update = $conn->prepare($sql_update);
           $stmt_update->bind_param("si", $attendance_date, $student_id);
           $stmt_update->execute();
           $stmt_update->close();
      } else{
         $sql_insert = "INSERT INTO attendance (student_id, attendance_date) VALUES (?, ?)";
         $stmt_insert = $conn->prepare($sql_insert);
         $stmt_insert->bind_param("is", $student_id, $attendance_date);
         $stmt_insert->execute();
         $stmt_insert->close();
      }
    }else{
          $sql_delete = "DELETE FROM attendance WHERE student_id = ? AND attendance_date = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("is", $student_id, $attendance_date);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
     header("Location: journal.php?class=".$selected_class."&subject=".$selected_subject);
    exit();
}
if (isset($_POST['addHomework'])) {
     $uploadDir = '../data/';
     $file_name = $_FILES['homework_file']['name'];
      $file_tmp = $_FILES['homework_file']['tmp_name'];
     $file_path = $uploadDir . $file_name;
    if(move_uploaded_file($file_tmp, $file_path)){
         $sql_update = "UPDATE journal SET homework_path = ? WHERE student_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $file_path, $student_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    header("Location: journal.php?class=".$selected_class."&subject=".$selected_subject);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Журнал</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="teacher-container">
        <h1>Журнал</h1>
           <form method="GET">
            <label for="class">Класс:</label>
            <select name="class" id="class" required>
                <option value="">Выберите класс</option>
                <?php while ($class_row = $result_classes->fetch_assoc()): ?>
                    <option value="<?php echo $class_row['class']; ?>" <?php if ($selected_class == $class_row['class']) echo 'selected'; ?>><?php echo $class_row['class']; ?></option>
                <?php endwhile; ?>
            </select>
              <label for="subject">Предмет:</label>
            <select name="subject" id="subject" required>
                <option value="">Выберите предмет</option>
                <?php while ($subject_row = $result_subjects->fetch_assoc()): ?>
                    <option value="<?php echo $subject_row['subject']; ?>" <?php if ($selected_subject == $subject_row['subject']) echo 'selected'; ?>><?php echo $subject_row['subject']; ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Показать журнал</button>
        </form>
        <?php if (!empty($selected_class) && !empty($selected_subject)): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Ученик</th>
                        <th>Оценка</th>
                          <th>Посещаемость</th>
                        <th>Действия</th>
                           <th>Домашнее задание</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                         <td>
                            <?php if (!empty($row['mark'])) { ?>
                                 <?php echo $row['mark']; ?>
                                 <button onclick="openEditMarkModal(<?php echo $row['journal_id']; ?>, <?php echo $row['mark']; ?>)">Редактировать оценку</button>
                                  <a href="journal.php?delete=<?php echo $row['journal_id']; ?>&class=<?php echo $selected_class; ?>&subject=<?php echo $selected_subject; ?>" onclick="return confirm('Вы уверены?');">Удалить оценку</a>
                            <?php }else{ ?>
                              <button onclick="openAddMarkModal(<?php echo $row['student_id']; ?>)">Добавить оценку</button>
                            <?php } ?>
                        </td>
                       <td>
                            <form method="post">
                                <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                <input type="checkbox" name="attendance" value="1" <?php if ($row['attendance_date']) echo 'checked'; ?> onchange="this.form.submit()">
                                <button type="submit" name="updateAttendance" style="display:none;"></button>
                            </form>
                        </td>
                             <td>
                            <button onclick="openAddHomeworkModal(<?php echo $row['student_id']; ?>)">Загрузить ДЗ</button>
                            </td>
                             <td>
                                <?php if (!empty($row['homework_path'])) { ?>
                                  <a href="<?php echo $row['homework_path']; ?>" download>Скачать ДЗ</a>
                             <?php } ?>
                            </td>
                    </tr>
                <?php endwhile; ?>
                <?php else: ?>
                     <tr><td colspan="6">Нет учеников в данном классе</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <?php endif; ?>
    </div>
    <!-- Add Mark Modal -->
    <div id="addMarkModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addMarkModal').style.display='none'">&times;</span>
            <h2>Добавить оценку</h2>
            <form method="post">
                 <input type="hidden" name="student_id" id="addMarkStudentId">
                 <input type="number" name="mark" placeholder="Оценка" required>
                <button type="submit" name="addMark">Добавить</button>
            </form>
        </div>
    </div>
    <!-- Edit Mark Modal -->
    <div id="editMarkModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editMarkModal').style.display='none'">&times;</span>
            <h2>Редактировать оценку</h2>
            <form method="post">
                 <input type="hidden" name="editId" id="editId">
                <input type="number" name="mark" id="editMark" placeholder="Оценка" required>
                <button type="submit" name="editMark">Сохранить</button>
            </form>
        </div>
    </div>
     <!-- Add Homework Modal -->
    <div id="addHomeworkModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addHomeworkModal').style.display='none'">&times;</span>
            <h2>Загрузить домашнее задание</h2>
           <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="student_id" id="addHomeworkStudentId">
                <input type="file" name="homework_file" required>
                <button type="submit" name="addHomework">Загрузить</button>
            </form>
        </div>
    </div>
     <script>
  function openAddMarkModal(studentId){
       document.getElementById('addMarkStudentId').value = studentId;
       document.getElementById('addMarkModal').style.display = 'block';
  }
        function openEditMarkModal(id, mark) {
        document.getElementById('editId').value = id;
         document.getElementById('editMark').value = mark;
        document.getElementById('editMarkModal').style.display = 'block';
    }
        function openAddHomeworkModal(studentId) {
         document.getElementById('addHomeworkStudentId').value = studentId;
       document.getElementById('addHomeworkModal').style.display = 'block';
    }
     var modal = document.getElementById("addMarkModal");
       var modalEdit = document.getElementById("editMarkModal");
     var modalHomework = document.getElementById("addHomeworkModal");
    var close = document.querySelectorAll(".close");
     close.forEach(function(closeButton) {
        closeButton.onclick = function() {
             modal.style.display = "none";
              modalEdit.style.display = "none";
               modalHomework.style.display = "none";
        }
    });
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
       if (event.target == modalEdit) {
            modalEdit.style.display = "none";
        }
       if (event.target == modalHomework) {
            modalHomework.style.display = "none";
        }
    }
</script>
</body>
</html>