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

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || $_POST['token'] !== ($_SESSION['admin_post_token'] ?? null)) {
        die('<p>Token invalide : action non autorisée.</p>');
    }

    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $image = isset($_POST['image']) ? trim($_POST['image']) : '';
    $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $edit_slug = isset($_POST['edit_slug']) ? trim($_POST['edit_slug']) : null;

    if ($title === '' || $content === '' || $slug === '') {
        die('<p>Le titre, le slug et le contenu sont requis.</p>');
    }

    try {
        if ($edit_slug) {
            $update = $pdo->prepare('UPDATE post SET title = :title, slug = :slug, content = :content, image = :image WHERE slug = :old_slug');
            $update->execute([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'image' => $image,
                'old_slug' => $edit_slug
            ]);
        } else {
            $insert = $pdo->prepare('INSERT INTO post (title, slug, content, image) VALUES (:title, :slug, :content, :image)');
            $insert->execute([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'image' => $image
            ]);
        }
        if (isset($_SESSION['admin_post_token'])) {
            unset($_SESSION['admin_post_token']);
        }
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        die('<p>Erreur lors du traitement de l\'article.</p>');
    }
}
?>

<?php
$update = false;
$editPost = null;
if (isset($_GET['edit'])) {
    $edit_slug = $_GET['edit'];
    $stmt = $pdo->prepare('SELECT id, title, slug, content, image FROM post WHERE slug = :slug');
    $stmt->execute(['slug' => $edit_slug]);
    $editPost = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($editPost) {
        $update = true;
        $title = $editPost['title'];
        $slug = $editPost['slug'];
        $content = $editPost['content'];
        $image = $editPost['image'];
    }
}
?>

<?php
if (isset($_GET['del'])) {
    if (!isset($_GET['token']) || $_GET['token'] !== ($_SESSION['admin_post_token'] ?? null)) {
        die('<p>Token invalide : suppression non autorisée.</p>');
    }

    $del_slug = $_GET['del'];
    $stmt = $pdo->prepare('DELETE FROM post WHERE slug = :slug');
    try {
        $stmt->execute(['slug' => $del_slug]);
        if (isset($_SESSION['admin_post_token'])) {
            unset($_SESSION['admin_post_token']);
        }
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        die('<p>Erreur lors de la suppression de l\'article.</p>');
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
            <h1><?php echo $update ? 'Modifier un article' : 'Créer un article'; ?></h1>
            <form method="post" action="">
                <input type="hidden" name="token"
                    value="<?php echo htmlspecialchars($_SESSION['admin_post_token'] ?? '', ENT_QUOTES); ?>">
                <?php if ($update): ?>
                    <input type="hidden" name="edit_slug" value="<?php echo htmlspecialchars($editPost['slug'], ENT_QUOTES); ?>">
                <?php endif; ?>
                <label for="title">Titre</label>
                <input type="text" id="title" name="title" placeholder="Titre de l'article" value="<?php echo htmlspecialchars($update ? $title : '', ENT_QUOTES); ?>" required>

                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" placeholder="slug" value="<?php echo htmlspecialchars($update ? $slug : '', ENT_QUOTES); ?>" required>

                <label for="content">Contenu</label>
                <textarea id="content" name="content" rows="8" placeholder="Contenu de l'article" required><?php echo htmlspecialchars($update ? $content : '', ENT_QUOTES); ?></textarea>

                <label for="image">Image (URL)</label>
                <input type="text" id="image" name="image" placeholder="https://..." value="<?php echo htmlspecialchars($update ? $image : '', ENT_QUOTES); ?>">

                <button type="submit"><?php echo $update ? 'Mettre à jour' : 'Publier'; ?></button>
            </form>
        </article>
        <article>
            <?php
            foreach ($posts as $post) {
                echo '<h1>' . htmlspecialchars($post['title']) . '</h1>';
                echo '<p>' . htmlspecialchars($post['content']) . '</p>';
                if (!empty($post['image'])) {
                    echo '<img src="' . htmlspecialchars($post['image']) . '" alt="' . htmlspecialchars($post['title']) . '">';
                }
                echo '<a href="?edit=' . htmlspecialchars($post['slug']) . '">Modifier</a> ';
                echo '<a href="?del=' . htmlspecialchars($post['slug']) . '&token=' . htmlspecialchars($_SESSION['admin_post_token'] ?? '') . '">Supprimer</a><hr>';
            }
            ?>
        </article>
    </main>
</body>

</html>