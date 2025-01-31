<?php
session_start();
require_once '../lib/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../auth.php");
    exit();
}
$student_id = $_SESSION['user_id'];
$sql_student = "SELECT class FROM students WHERE user_id = ?";
$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$result_student = $stmt_student->get_result();
$student_data = $result_student->fetch_assoc();
$student_class = $student_data['class'];
$stmt_student->close();

$sql_schedule = "SELECT * FROM schedule WHERE class = ?";
$stmt_schedule = $conn->prepare($sql_schedule);
$stmt_schedule->bind_param("s", $student_class);
$stmt_schedule->execute();
$result_schedule = $stmt_schedule->get_result();
$stmt_schedule->close();

$sql_marks = "SELECT journal.subject, journal.mark FROM journal
       JOIN students ON journal.student_id = students.id
       WHERE students.user_id = ?";
$stmt_marks = $conn->prepare($sql_marks);
$stmt_marks->bind_param("i", $student_id);
$stmt_marks->execute();
$result_marks = $stmt_marks->get_result();
$stmt_marks->close();
$marks = [];
while ($row = $result_marks->fetch_assoc()) {
    $marks[$row['subject']] = $row['mark'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель ученика</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="student-container">
        <h1>Панель ученика</h1>
         <h2>Расписание</h2>
           <table border="1">
            <thead>
                <tr>
                    <th>Предмет</th>
                    <th>Время</th>
                    <th>Учитель</th>
                </tr>
            </thead>
            <tbody>
             <?php if($result_schedule->num_rows > 0): ?>
                <?php while ($row = $result_schedule->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['subject']; ?></td>
                        <td><?php echo $row['time']; ?></td>
                          <td><?php echo $row['teacher']; ?></td>
                    </tr>
                 <?php endwhile; ?>
                <?php else: ?>
                   <tr><td colspan="3">Нет расписания для вашего класса</td></tr>
             <?php endif; ?>
            </tbody>
        </table>
        <h2>Текущие оценки</h2>
         <table border="1">
            <thead>
                <tr>
                    <th>Предмет</th>
                    <th>Оценка</th>
                </tr>
            </thead>
            <tbody>
              <?php if (!empty($marks)): ?>
                    <?php foreach ($marks as $subject => $mark): ?>
                     <tr>
                          <td><?php echo $subject; ?></td>
                          <td><?php echo $mark; ?></td>
                    </tr>
                    <?php endforeach; ?>
              <?php else: ?>
                    <tr><td colspan="2">Нет оценок</td></tr>
               <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>