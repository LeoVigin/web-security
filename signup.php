<?php
session_start();

if (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = htmlspecialchars($_POST['email']);
}

if (isset($_POST['password']) && !empty($_POST['password'])) {
    $password = htmlspecialchars($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, []);
}

if (isset($email) && isset($password)) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=tortue-ninja', 'root', '');
    } catch (PDOException $e) {
        die('Erreur : ' . $e->getMessage());
    }

    $verif_email = $pdo->prepare('SELECT * FROM user WHERE email = :email');
    $verif_email->execute(['email' => $email]);

    if ($verif_email->rowCount() > 0) {
        echo '<p>This email is already used</p>';
    } else {
        $insert = $pdo->prepare('INSERT INTO user (email, password, role) VALUES (:email, :password, :role)');
        $insert->execute([
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'User'
        ]);
        header('Location: login.php');
    }
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
    <title>Sign Up</title>
</head>

<body>
    <?php
    include "includes/header.html";
    ?>
    <main>
        <section class="form-section">
            <form class="form" action="" method="post">
                <h1 class="title">Create your account</h1>
                <input class="text" type="text" name="email" id="email" placeholder="Insert your email">
                <input class="text" type="text" name="password" id="password" placeholder="Insert your password">
                <br>
                <button class="text" type="submit">Sign up</button>
            </form>
        </section>
    </main>
    <footer>
        <p class="footer text">@TortueNinja2026</p>
    </footer>
</body>

</html>