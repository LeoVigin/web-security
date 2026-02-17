<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=tortue-ninja', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

$stmt = $pdo->query('SELECT id, title, slug, content, image FROM post');

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$posts) {
    die('Erreur : No posts found');
}

foreach ($posts as $post) {
    echo '<h1>' . htmlspecialchars($post['title']) . '</h1>';
    echo '<p>' . htmlspecialchars($post['content']) . '</p>';
    echo '<img src="' . htmlspecialchars($post['image']) . '" alt=""><hr>';
}
