<?php
require 'db.php';
require 'functions.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'login' => trim($_POST['login'] ?? ''),
        'password' => $_POST['password'] ?? '',
    ];

    $errors = validate_registration($data, $pdo);

    if (empty($errors)) {
        // Hash password
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (full_name, phone, email, login, password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['full_name'],
            $data['phone'],
            $data['email'],
            $data['login'],
            $password_hash
        ]);

        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Регистрация - Корочки.есть</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <h1>Регистрация</h1>
    <?php if ($success): ?>
        <p class="success">Регистрация прошла успешно. <a href="login.php">Войти</a></p>
    <?php else: ?>
        <form method="post" action="register.php" novalidate>
            <label>ФИО:<br />
                <input type="text" name="full_name" value="<?=htmlspecialchars($_POST['full_name'] ?? '')?>" required />
                <span class="error"><?= $errors['full_name'] ?? '' ?></span>
            </label><br />

            <label>Телефон:<br />
                <input type="text" name="phone" placeholder="+XXXXXXXXXXX" value="<?=htmlspecialchars($_POST['phone'] ?? '')?>" required />
                <span class="error"><?= $errors['phone'] ?? '' ?></span>
            </label><br />

            <label>Email:<br />
                <input type="email" name="email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>" required />
                <span class="error"><?= $errors['email'] ?? '' ?></span>
            </label><br />

            <label>Логин:<br />
                <input type="text" name="login" value="<?=htmlspecialchars($_POST['login'] ?? '')?>" required />
                <span class="error"><?= $errors['login'] ?? '' ?></span>
            </label><br />

            <label>Пароль:<br />
                <input type="password" name="password" required />
                <span class="error"><?= $errors['password'] ?? '' ?></span>
            </label><br />

            <button type="submit">Зарегистрироваться</button>
        </form>
        <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
    <?php endif; ?>
</body>
</html>
