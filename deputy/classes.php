<?php
session_start();
require_once '../lib/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'deputy') {
    header("Location: ../auth.php");
    exit();
}
$sql_classes = "SELECT DISTINCT class FROM students";
$result_classes = $conn->query($sql_classes);

$selected_class = $_GET['class'] ?? '';
$selected_subject = $_GET['subject'] ?? '';

$sql_subjects = "SELECT DISTINCT subject FROM journal";
$result_subjects = $conn->query($sql_subjects);

if (!empty($selected_class) && !empty($selected_subject)) {
    $sql_marks = "SELECT students.name AS student_name, journal.mark FROM journal
        JOIN students ON journal.student_id = students.id
        WHERE students.class = ? AND journal.subject = ?";
    $stmt_marks = $conn->prepare($sql_marks);
    $stmt_marks->bind_param("ss", $selected_class, $selected_subject);
    $stmt_marks->execute();
    $result_marks = $stmt_marks->get_result();
    $stmt_marks->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр оценок</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="deputy-container">
        <h1>Просмотр оценок</h1>
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
            <button type="submit">Показать оценки</button>
        </form>
        <?php if (!empty($selected_class) && !empty($selected_subject)): ?>
            <?php if ($result_marks && $result_marks->num_rows > 0): ?>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Ученик</th>
                            <th>Оценка</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($mark_row = $result_marks->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $mark_row['student_name']; ?></td>
                            <td><?php echo $mark_row['mark']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>В классе нет учеников или оценок по выбранному предмету.</p>
                <?php endif; ?>
            <?php endif; ?>
    </div>
</body>
</html>