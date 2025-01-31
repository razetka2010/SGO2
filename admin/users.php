<?php
session_start();
require_once '../lib/db.php';

// Проверка роли
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth.php");
    exit();
}

// Add User
if (isset($_POST['addUser'])) {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $name = htmlspecialchars(trim($_POST['name']));
    $role = $_POST['role'];

    // Валидация данных
     if (empty($login) || empty($password) || empty($name) || empty($role)) {
         $error = 'Пожалуйста, заполните все поля.';
      } else {
       // Хеширование пароля
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

       $sql_insert = "INSERT INTO users (login, password, name, role) VALUES (?, ?, ?, ?)";
       $stmt_insert = $conn->prepare($sql_insert);
       $stmt_insert->bind_param("ssss", $login, $hashedPassword, $name, $role);
          if($stmt_insert->execute()){
                $user_id = $conn->insert_id;
              if ($role == 'student') {
                 $class = $_POST['class'];
                  $letter = $_POST['letter'];
                   if (empty($class) || empty($letter)) {
                     $error = 'Пожалуйста, заполните поля класса и буквы для учеников.';
                    }else {
                       $sql_student = "INSERT INTO students (user_id, class, letter) VALUES (?, ?, ?)";
                         $stmt_student = $conn->prepare($sql_student);
                          $stmt_student->bind_param("iss", $user_id, $class, $letter);
                            if($stmt_student->execute()){
                                header("Location: users.php");
                              exit();
                            }else{
                                 $error = "Ошибка при добавлении ученика в таблицу student.";
                            }
                         $stmt_student->close();
                   }

                 }else{
                      header("Location: users.php");
                      exit();
                 }

           }else{
             $error = "Ошибка при добавлении пользователя.";
           }
        $stmt_insert->close();
     }
}

// Edit User
if (isset($_POST['editUser'])) {
    $editId = $_POST['editId'];
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $name = htmlspecialchars(trim($_POST['name']));
    $role = $_POST['role'];

    // Валидация
    if (empty($login) || empty($name) || empty($role)  ) {
      $error = 'Пожалуйста, заполните все поля.';
    }else{
       $sql_update = "UPDATE users SET login = ?, name = ?, role = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssi", $login, $name, $role, $editId);
           if($stmt_update->execute()){
                 if (!empty($password)) {
                     $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                     $sql_update_password = "UPDATE users SET password = ? WHERE id = ?";
                      $stmt_update_password = $conn->prepare($sql_update_password);
                      $stmt_update_password->bind_param("si", $hashedPassword, $editId);
                       $stmt_update_password->execute();
                       $stmt_update_password->close();
                   }

                   if ($role == 'student') {
                        $class = $_POST['class'];
                         $letter = $_POST['letter'];
                         if (empty($class) || empty($letter)) {
                             $error = 'Пожалуйста, заполните поля класса и буквы для учеников.';
                         }else{
                           $sql_student_update = "UPDATE students SET class = ?, letter = ? WHERE user_id = ?";
                           $stmt_student_update = $conn->prepare($sql_student_update);
                            $stmt_student_update->bind_param("ssi", $class, $letter, $editId);
                                if(!$stmt_student_update->execute()){
                                  $error = "Ошибка при редактировании ученика в таблице student.";
                                }
                           $stmt_student_update->close();
                           header("Location: users.php");
                         exit();
                        }
                   }else{
                       $sql_student_delete = "DELETE FROM students WHERE user_id = ?";
                       $stmt_student_delete = $conn->prepare($sql_student_delete);
                        $stmt_student_delete->bind_param("i", $editId);
                        $stmt_student_delete->execute();
                         $stmt_student_delete->close();
                        header("Location: users.php");
                         exit();
                    }

           }else{
               $error = "Ошибка при редактировании пользователя.";
           }
        $stmt_update->close();
    }
}

// Delete User
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $sql_delete = "DELETE FROM users WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $deleteId);
       if($stmt_delete->execute()){
          $sql_student_delete = "DELETE FROM students WHERE user_id = ?";
           $stmt_student_delete = $conn->prepare($sql_student_delete);
           $stmt_student_delete->bind_param("i", $deleteId);
           $stmt_student_delete->execute();
          $stmt_student_delete->close();
           header("Location: users.php");
          exit();
        }else{
          $error = "Ошибка при удалении пользователя.";
        }
    $stmt_delete->close();
}

// Fetch Users
$sql = "SELECT users.*, students.class, students.letter FROM users LEFT JOIN students ON users.id = students.user_id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Управление пользователями</h1>
          <?php if (isset($error)): ?>
          <p style="color: red;"><?php echo $error; ?></p>
         <?php endif; ?>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Логин</th>
                    <th>ФИО</th>
                    <th>Роль</th>
                    <th>Класс</th>
                    <th>Буква</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['login']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['role']; ?></td>
                        <td><?php echo $row['class']; ?></td>
                        <td><?php echo $row['letter']; ?></td>
                        <td>
                            <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['login'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>', '<?php echo $row['role']; ?>','<?php echo $row['class']; ?>','<?php echo $row['letter']; ?>')">Редактировать</button>
                            <a href="users.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Вы уверены?');">Удалить</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">Нет пользователей</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <br>
        <button onclick="document.getElementById('addUserModal').style.display='block'">Добавить пользователя</button>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addUserModal').style.display='none'">&times;</span>
            <h2>Добавить пользователя</h2>
            <form method="post">
                <input type="text" name="login" placeholder="Логин" required><br>
                <input type="password" name="password" placeholder="Пароль" required><br>
                <input type="text" name="name" placeholder="ФИО" required><br>
                <select name="role" required>
                    <option value="admin">Админ</option>
                    <option value="teacher">Учитель</option>
                    <option value="deputy">Завуч</option>
                    <option value="student">Ученик</option>
                </select><br>
                <div id="studentFields" style="display: none;">
                   <input type="number" name="class" placeholder="Класс"><br>
                   <input type="text" name="letter" placeholder="Буква">
                </div>
                <br>
                <button type="submit" name="addUser">Добавить</button>
            </form>
        </div>
    </div>
    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editUserModal').style.display='none'">&times;</span>
            <h2>Редактировать пользователя</h2>
             <form method="post">
                <input type="hidden" name="editId" id="editId">
                <input type="text" name="login" id="editLogin" placeholder="Логин" required><br>
                 <input type="password" name="password" id="editPassword" placeholder="Пароль (оставьте пустым, чтобы не менять)"><br>
                <input type="text" name="name" id="editName" placeholder="ФИО" required><br>
                <select name="role" id="editRole" required>
                    <option value="admin">Админ</option>
                    <option value="teacher">Учитель</option>
                    <option value="deputy">Завуч</option>
                    <option value="student">Ученик</option>
                </select><br>
                 <div id="studentEditFields" style="display: none;">
                   <input type="number" name="class" id="editClass" placeholder="Класс"><br>
                   <input type="text" name="letter" id="editLetter" placeholder="Буква">
                </div>
                <br>
                <button type="submit" name="editUser">Сохранить</button>
            </form>
        </div>
    </div>
    <script>
    document.querySelector('select[name="role"]').addEventListener('change', function() {
        var studentFields = document.getElementById('studentFields');
        if (this.value === 'student') {
            studentFields.style.display = 'block';
        } else {
            studentFields.style.display = 'none';
        }
    });
     document.getElementById('editRole').addEventListener('change', function() {
           var studentEditFields = document.getElementById('studentEditFields');
            if (this.value === 'student') {
                studentEditFields.style.display = 'block';
            } else {
                studentEditFields.style.display = 'none';
            }
        });
    function openEditModal(id, login, name, role, classValue, letter) {
        document.getElementById('editId').value = id;
        document.getElementById('editLogin').value = login;
         document.getElementById('editPassword').value = '';
        document.getElementById('editName').value = name;
        document.getElementById('editRole').value = role;
        if(role == 'student'){
              document.getElementById('studentEditFields').style.display = 'block';
               document.getElementById('editClass').value = classValue;
               document.getElementById('editLetter').value = letter;
        }else{
               document.getElementById('studentEditFields').style.display = 'none';
               document.getElementById('editClass').value = '';
               document.getElementById('editLetter').value = '';
        }
        document.getElementById('editUserModal').style.display = 'block';
    }
     var modal = document.getElementById("addUserModal");
    var modalEdit = document.getElementById("editUserModal");
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