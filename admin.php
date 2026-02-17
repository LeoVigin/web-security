<?php
if (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = htmlspecialchars($_POST['email']);
}

if (isset($_POST['password']) && !empty($_POST['password'])) {
    $password = htmlspecialchars($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, []);
}
?>
<?php
session_start();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $image = isset($_POST['image']) ? trim($_POST['image']) : '';

    if ($title === '' || $content === '') {
        $msg = '<p>Le titre et le contenu sont requis.</p>';
    } else {

        $slug = strtolower($title);
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');

        try {
            $pdo = new PDO('mysql:host=localhost;dbname=tortue-ninja', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Erreur : ' . $e->getMessage());
        }

        $baseSlug = $slug;
        $i = 1;
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM post WHERE slug = :slug');
        while (true) {
            $stmt->execute(['slug' => $slug]);
            $count = (int)$stmt->fetchColumn();
            if ($count === 0) break;
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        $insert = $pdo->prepare('INSERT INTO post (title, slug, content, image) VALUES (:title, :slug, :content, :image)');
        try {
            $insert->execute([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'image' => $image
            ]);
            $msg = '<p>Article créé avec succès.</p>';
        } catch (PDOException $e) {
            $msg = '<p>Erreur lors de la création de l\'article.</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Créer un article</title>
</head>

<body>
    <h1>Créer un article</h1>
    <?php echo $msg; ?>
    <form method="post" action="">
        <label for="title">Titre</label>
        <input type="text" id="title" name="title" placeholder="Titre de l'article" required>

        <label for="content">Contenu</label>
        <textarea id="content" name="content" rows="8" placeholder="Contenu de l'article" required></textarea>

        <label for="image">Image (URL)</label>
        <input type="text" id="image" name="image" placeholder="https://...">

        <button type="submit">Publier</button>
    </form>
</body>

</html>