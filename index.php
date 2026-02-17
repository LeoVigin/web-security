<?php
// if (!isset($_GET['slug']) || empty($_GET['slug'])) {
//     die('Erreur : Slug non fourni');
// }

// $slug = htmlspecialchars($_GET['slug']);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=tortue-ninja', 'root', '');
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

$verif_post = $pdo->prepare('SELECT * FROM post WHERE id, title, slug, content, image  = :id, :title, :slug, :content, :image');
// $verif_post->execute(['slug' => $slug]);

// if ($verif_post->rowCount() == 0) {
//     die('Erreur : No posts found');
// }

$post = $verif_post->fetch();
?>

<?= $post['title'] ?>
<p>
    <?= $post['content'] ?>
</p>
<img>
<?= $post['image'] ?></img>