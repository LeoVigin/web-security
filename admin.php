<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('HTTP/1.1 403 Forbidden');
    echo '<p>Accès refusé : vous devez être administrateur pour publier.</p>';
    exit;
}

if (!isset($_SESSION['admin_post_token'])) {
    try {
        $_SESSION['admin_post_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['admin_post_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=tortue-ninja', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}
// GET ALL POSTS
$stmtpost = $pdo->query('SELECT id, title, slug, content, image FROM post');

$posts = $stmtpost->fetchAll(PDO::FETCH_ASSOC);

if (!$posts) {
    die('Erreur : No posts found');
}

// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || $_POST['token'] !== ($_SESSION['admin_post_token'] ?? null)) {
        die('<p>Token invalide : action non autorisée.</p>');
    }

    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $image = isset($_POST['image']) ? trim($_POST['image']) : '';
    $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';

    if ($title === '' || $content === '' || $slug === '') {
        die('<p>Le titre, le slug et le contenu sont requis.</p>');
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM post WHERE slug = :slug');
    $stmt->execute(['slug' => $slug]);
    $count = (int) $stmt->fetchColumn();
    if ($count > 0) {
        die('<p>Ce slug existe déjà. Choisissez-en un autre.</p>');
    }

    $insert = $pdo->prepare('INSERT INTO post (title, slug, content, image) VALUES (:title, :slug, :content, :image)');
    try {
        $insert->execute([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'image' => $image
        ]);
        // invalidate token after successful use
        if (isset($_SESSION['admin_post_token'])) {
            unset($_SESSION['admin_post_token']);
        }
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        die('<p>Erreur lors de la création de l\'article.</p>');
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
    <main>
        <article>
            <h1>Créer un article</h1>
            <form method="post" action="">
                <input type="hidden" name="token"
                    value="<?php echo htmlspecialchars($_SESSION['admin_post_token'] ?? '', ENT_QUOTES); ?>">
                <label for="title">Titre</label>
                <input type="text" id="title" name="title" placeholder="Titre de l'article" required>

                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" placeholder="slug" required>

                <label for="content">Contenu</label>
                <textarea id="content" name="content" rows="8" placeholder="Contenu de l'article" required></textarea>

                <label for="image">Image (URL)</label>
                <input type="text" id="image" name="image" placeholder="https://...">

                <button type="submit">Publier</button>
            </form>
        </article>
        <article>
            <?php
            foreach ($posts as $post) {
                echo '<h1>' . htmlspecialchars($post['title']) . '</h1>';
                echo '<p>' . htmlspecialchars($post['content']) . '</p>';
                echo '<img src="' . htmlspecialchars($post['image']) . '" alt=""><hr>';
            }
            ?>
        </article>
    </main>
</body>

</html>