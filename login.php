<?php
require 'db.php';
require 'functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $errors[] = 'Пожалуйста, заполните все поля.';
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Authentication successful
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login'] = $login;
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: authorized.php');
            }
            exit;
        } else {
            $errors[] = 'Неверный логин или пароль.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Вход - Корочки.есть</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <h1>Вход</h1>
    <?php if ($errors): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?=htmlspecialchars($error)?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="login.php" novalidate>
        <label>Логин:<br />
            <input type="text" name="login" value="<?=htmlspecialchars($_POST['login'] ?? '')?>" required />
        </label><br />
        <label>Пароль:<br />
            <input type="password" name="password" required />
        </label><br />
        <button type="submit">Войти</button>
    </form>
    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
</body>
</html>
