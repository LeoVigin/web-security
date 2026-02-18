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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Russo+One&display=swap" rel="stylesheet">
    <title>Document</title>
</head>

<body>
    <?php
    include "includes/header.html";
    ?>
    <main class="main">
        <section class="post-section">
            <?php
            foreach ($posts as $post) {
                ?>
                <article class="post">
                    <?php

                    echo '<h1 class="title">' . htmlspecialchars($post['title']) . '</h1>';
                    echo '<p class="post-content text">' . htmlspecialchars($post['content']) . '</p>';
                    echo '<img class="post-img" src="' . htmlspecialchars($post['image']) . '" alt="">';
                    ?>
                </article>
                <?php
            }
            ?>
        </section>
    </main>
    <?php
    include "includes/footer.html";
    ?>
</body>

</html>