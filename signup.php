<?php
session_start();

// if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token_article_add']) {
//     die('Erreur : Token invalide');
// }

// unset($_SESSION['token_article_add']);

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
        echo '<p>Your user was added</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="" method="post">
        <input type="text" name="email" id="email" placeholder="Insert your email">
        <input type="text" name="password" id="password" placeholder="Insert your password">
        <br>
        <button type="submit">Submit</button>
    </form>
</body>

</html>