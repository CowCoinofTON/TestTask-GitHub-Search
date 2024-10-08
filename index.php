<?php
require 'db.php';

$searchQuery = '';
$results = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $searchQuery = trim($_POST['query']);

	if(!empty($searchQuery)){
		// Поиск в локальной базе данных
		$stmt = $pdo->prepare('SELECT * FROM search_results WHERE search_query = ?');
		$stmt->execute([$searchQuery]);
		$result = $stmt->fetch();

		if ($result) {
			// Если результат найден в базе данных
			$results = json_decode($result['result'], true);
		} else {
			// Если результата нет в базе данных, делаем запрос к GitHub API
			$url = "https://api.github.com/search/repositories?q=" . urlencode($searchQuery);
			$context = stream_context_create(['http' => ['user_agent' => 'PHP']]);
			$response = file_get_contents($url, false, $context);
			$data = json_decode($response, true);

			// Сохраняем в базе данных
			$stmt = $pdo->prepare('INSERT INTO search_results (search_query, result) VALUES (?, ?)');
			$stmt->execute([$searchQuery, json_encode($data)]);

			$results = $data;
		}
	}else{
		echo '<script> alert("Вы вводите пустой запрос");</script>';
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.min.css?v=1.0.1" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
    <title>Тестовое: GitHub Search</title>
	
	<style>
        footer {
            position: fixed;
            bottom: 0;
            right: 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-top-left-radius: 10px;
        }
    </style>
	
</head>
<body>
<div class="container">
    <h1 class="mt-5">Поиск проектов на GitHub</h1>
    <form method="POST" action="index.php">
        <div class="form-group">
            <input type="text" name="query" class="form-control" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Введите текст для поиска...">
        </div>
		<br>
        <center><button type="submit" class="btn btn-primary">Поиск</button></center>
    </form>
    <?php if (!empty($results)): ?>
    <div class="row mt-4">
        <?php foreach ($results['items'] as $repo): ?>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($repo['name']); ?></h5>
                    <p class="card-text">Автор: <?php echo htmlspecialchars($repo['owner']['login']); ?></p>
                    <p class="card-text">Звезды: <?php echo $repo['stargazers_count']; ?></p>
                    <p class="card-text">Просмотры: <?php echo $repo['watchers_count']; ?></p>
                    <a href="<?php echo htmlspecialchars($repo['html_url']); ?>" class="btn btn-primary" target="_blank">Перейти на GitHub</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
  <!-- Footer -->
  <footer>
    <p>Автор: <a href="https://t.me/jonsdoofen">@jonsdoofen</a></p>
</footer>
           

<script src="js/bootstrap.min.js?v=1.0.1"></script>
</body>
</html>