<?php
session_start();
require_once '../lib/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'deputy') {
    header("Location: ../auth.php");
    exit();
}
// Add Schedule
if (isset($_POST['addSchedule'])) {
    $class = $_POST['class'];
    $subject = $_POST['subject'];
    $time = $_POST['time'];
    $teacher = $_POST['teacher'];
    $sql_insert = "INSERT INTO schedule (class, subject, time, teacher) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssss", $class, $subject, $time, $teacher);
    $stmt_insert->execute();
    $stmt_insert->close();
    header("Location: schedule.php");
    exit();
}
// Edit Schedule
if (isset($_POST['editSchedule'])) {
    $scheduleId = $_POST['editId'];
    $class = $_POST['class'];
    $subject = $_POST['subject'];
    $time = $_POST['time'];
    $teacher = $_POST['teacher'];
    $sql_update = "UPDATE schedule SET class = ?, subject = ?, time = ?, teacher = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssi", $class, $subject, $time, $teacher, $scheduleId);
    $stmt_update->execute();
    $stmt_update->close();
      header("Location: schedule.php");
    exit();
}
// Delete Schedule
if (isset($_GET['delete'])) {
    $scheduleId = $_GET['delete'];
    $sql_delete = "DELETE FROM schedule WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $scheduleId);
    $stmt_delete->execute();
    $stmt_delete->close();
     header("Location: schedule.php");
    exit();
}
$sql = "SELECT * FROM schedule";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <</th>
                    <th>Класс</th>
                    <th>Предмет</th>
                    <th>Время</th>
                    <th>Учитель</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['class']; ?></td>
                        <td><?php echo $row['subject']; ?></td>
                        <td><?php echo $row['time']; ?></td>
                        <td><?php echo $row['teacher']; ?></td>
                        <td>
                            <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo $row['class']; ?>', '<?php echo $row['subject']; ?>', '<?php echo $row['time']; ?>', '<?php echo $row['teacher']; ?>')">Редактировать</button>
                            <a href="schedule.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Вы уверены?');">Удалить</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">Нет расписания</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <br>
        <button onclick="document.getElementById('addScheduleModal').style.display='block'">Добавить расписание</button>
    </div>
       <!-- Add Schedule Modal -->
    <div id="addScheduleModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addScheduleModal').style.display='none'">&times;</span>
            <h2>Добавить расписание</h2>
            <form method="post">
                <input type="text" name="class" placeholder="Класс" required><br>
                <input type="text" name="subject" placeholder="Предмет" required><br>
                <input type="text" name="time" placeholder="Время" required><br>
                <input type="text" name="teacher" placeholder="Учитель" required><br>
                <button type="submit" name="addSchedule">Добавить</button>
            </form>
        </div>
    </div>
    <!-- Edit Schedule Modal -->
    <div id="editScheduleModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editScheduleModal').style.display='none'">&times;</span>
            <h2>Редактировать расписание</h2>
             <form method="post">
                <input type="hidden" name="editId" id="editId">
                <input type="text" name="class" id="editClass" placeholder="Класс" required><br>
                <input type="text" name="subject" id="editSubject" placeholder="Предмет" required><br>
                <input type="text" name="time" id="editTime" placeholder="Время" required><br>
                <input type="text" name="teacher" id="editTeacher" placeholder="Учитель" required><br>
                <button type="submit" name="editSchedule">Сохранить</button>
            </form>
        </div>
    </div>
<script>
   function openEditModal(id, classValue, subject, time, teacher) {
        document.getElementById('editId').value = id;
         document.getElementById('editClass').value = classValue;
         document.getElementById('editSubject').value = subject;
         document.getElementById('editTime').value = time;
         document.getElementById('editTeacher').value = teacher;
        document.getElementById('editScheduleModal').style.display = 'block';
    }
     var modal = document.getElementById("addScheduleModal");
    var modalEdit = document.getElementById("editScheduleModal");
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