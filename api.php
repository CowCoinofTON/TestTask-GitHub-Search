<?php
require 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $searchQuery = trim($input['query']);

    // Поиск в базе данных
    $stmt = $pdo->prepare('SELECT * FROM search_results WHERE search_query = ?');
    $stmt->execute([$searchQuery]);
    $result = $stmt->fetch();

    if ($result) {
        echo json_encode(json_decode($result['result'], true));
    } else {
        // Если результата нет, делаем запрос к GitHub API
        $url = "https://api.github.com/search/repositories?q=" . urlencode($searchQuery);
        $context = stream_context_create(['http' => ['user_agent' => 'PHP']]);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        // Сохраняем в базе данных
        $stmt = $pdo->prepare('INSERT INTO search_results (search_query, result) VALUES (?, ?)');
        $stmt->execute([$searchQuery, json_encode($data)]);

        echo json_encode($data);
    }
} elseif ($method == 'GET') {
    // Получить все результаты поиска
    $stmt = $pdo->query('SELECT * FROM search_results');
    $results = $stmt->fetchAll();

    $response = array_map(function($result) {
        return [
            'query' => $result['search_query'],
            'result' => json_decode($result['result'], true)
        ];
    }, $results);

    echo json_encode($response);
} elseif ($method == 'DELETE') {
    // Удаление результата поиска
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $pdo->prepare('DELETE FROM search_results WHERE id = ?');
        $stmt->execute([$id]);

        echo json_encode(['message' => 'Результат удалён']);
    } else {
        echo json_encode(['message' => 'ID не указан'], JSON_UNESCAPED_UNICODE);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Метод не поддерживается']);
}