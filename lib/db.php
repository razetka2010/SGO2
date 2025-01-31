<?php
// Настройки подключения к базе данных
$db_host = "localhost"; // Хост базы данных (обычно "localhost")
$db_user = "your_db_user"; // Имя пользователя базы данных (замените на свое)
$db_pass = "your_db_password"; // Пароль пользователя базы данных (замените на свой)
$db_name = "your_db_name"; // Имя базы данных (замените на свое)

// Создаем подключение к базе данных
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Проверяем, удалось ли подключение
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error); // Выводим сообщение об ошибке и останавливаем скрипт
}

// Устанавливаем кодировку для правильного отображения символов
$conn->set_charset("utf8mb4");

// Функция для безопасного выполнения SQL-запросов с параметрами
function executeQuery($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql); // Подготавливаем SQL-запрос
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $conn->error); // Если запрос не удалось подготовить, выводим ошибку
    }
    if ($params) {
       // Определяем типы параметров (целое число, дробное число или строка)
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_double($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params); // Связываем параметры с запросом
        if ($stmt->errno) {
           die("Ошибка привязки параметров: " . $stmt->error); // Выводим ошибку, если не удалось связать параметры
        }
    }
    $stmt->execute(); // Выполняем запрос
    if ($stmt->errno) {
        die("Ошибка выполнения запроса: " . $stmt->error); // Выводим ошибку, если запрос не удалось выполнить
    }
    $result = $stmt->get_result(); // Получаем результат запроса
    if (!$result && $stmt->errno) {
        die("Ошибка получения результата: " . $stmt->error); // Выводим ошибку, если не удалось получить результат
    }
    $stmt->close(); // Закрываем подготовленный запрос
    return $result; // Возвращаем результат
}

// Функция для получения одной строки из результата запроса
function fetchOne($conn, $sql, $params = []) {
   $result = executeQuery($conn, $sql, $params); // Выполняем запрос с помощью executeQuery
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc(); // Возвращаем первую строку в виде ассоциативного массива
    }
    return null; // Если строк нет, возвращаем null
}

// Функция для получения всех строк из результата запроса
function fetchAll($conn, $sql, $params = []) {
    $result = executeQuery($conn, $sql, $params); // Выполняем запрос с помощью executeQuery
    if ($result && $result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC); // Возвращаем все строки в виде массива ассоциативных массивов
    }
    return []; // Если строк нет, возвращаем пустой массив
}

// Функция для выполнения запросов INSERT, UPDATE, DELETE
function executeNonQuery($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql); // Подготавливаем SQL-запрос
        if (!$stmt) {
            die("Ошибка подготовки запроса: " . $conn->error); // Если запрос не удалось подготовить, выводим ошибку
        }
    if ($params) {
        // Определяем типы параметров (целое число, дробное число или строка)
         $types = '';
         foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            }elseif (is_double($param)) {
                $types .= 'd';
             }else{
               $types .= 's';
           }
        }
        $stmt->bind_param($types, ...$params); // Связываем параметры с запросом
         if ($stmt->errno) {
           die("Ошибка привязки параметров: " . $stmt->error);  // Выводим ошибку, если не удалось связать параметры
        }
    }
    $result = $stmt->execute(); // Выполняем запрос
     if ($stmt->errno) {
        die("Ошибка выполнения запроса: " . $stmt->error); // Выводим ошибку, если запрос не удалось выполнить
    }
    $stmt->close(); // Закрываем подготовленный запрос
    return $result; // Возвращаем результат выполнения
}

// Функция для получения ID последней вставленной записи
function getLastInsertedId($conn) {
    return $conn->insert_id; // Возвращаем ID последней вставленной записи
}
?>