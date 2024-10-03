<?php
// Разрешить CORS
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Обработка preflight запроса
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit; // Завершение выполнения скрипта
}

// Подключение к базе данных
$host = '127.0.0.1'; 
$db = 'comments_db';
$user = 'root'; 
$pass = ''; 
$charset = 'utf8mb4'; 

// Создание DSN (Data Source Name) для PDO
$dsn = "mysql:host=$host;charset=$charset"; 
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
    PDO::ATTR_EMULATE_PREPARES   => false, 
];

// Попытка подключения к серверу MySQL
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Ошибка подключения к серверу MySQL: " . $e->getMessage());
}

// Создание базы данных, если она не существует
$createDatabaseQuery = "CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$pdo->exec($createDatabaseQuery);

// Подключение к созданной базе данных
$dsn = "mysql:host=$host;dbname=$db;charset=$charset"; 
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Создание таблицы, если она не существует
$createTableQuery = "
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB;
";

$pdo->exec($createTableQuery);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = str_replace('/index.php', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($requestMethod == 'GET' && $path == '/comments-api/comments') {
    $stmt = $pdo->query("SELECT * FROM comments");
    $comments = $stmt->fetchAll();
    header('Content-Type: application/json');
    echo json_encode($comments);
    exit;
}

if ($requestMethod == 'POST' && $path == '/comments-api/comments') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['name']) && isset($data['comment_text'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (name, comment_text, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$data['name'], $data['comment_text']]);
            $commentId = $pdo->lastInsertId();
            http_response_code(201);
            echo json_encode(['status' => 'Комментарий добавлен', 'id' => $commentId]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Недостаточно данных для добавления комментария']);
    }
    exit;
}

if ($requestMethod == 'DELETE' && preg_match('/\/comments-api\/comments\/(\d+)/', $path, $matches)) {
    $commentId = $matches[1];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);
    http_response_code(204);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Маршрут не найден']);
