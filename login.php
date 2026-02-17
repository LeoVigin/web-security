<?php
session_start();

if (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = htmlspecialchars($_POST['email']);
}

if (isset($_POST['password']) && !empty($_POST['password'])) {
    $password = $_POST['password'];
}

if (isset($email) && isset($password)) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=tortue-ninja', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('Erreur : ' . $e->getMessage());
    }

    $verif = $pdo->prepare('SELECT * FROM user WHERE email = :email');
    $verif->execute(['email' => $email]);

    if ($verif->rowCount() === 0) {
        $error = 'This email is not registered';
    } else {
        $user = $verif->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'] ?? null;
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
    <?php if (isset($error)) : ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <input type="email" name="email" id="email" placeholder="Insert your email">
        <input type="password" name="password" id="password" placeholder="Insert your password">
        <br>
        <button type="submit">Login</button>
    </form>
</body>

</html>