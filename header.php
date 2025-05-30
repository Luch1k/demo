<?php
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Корочки.есть</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
<header>
    <h1>Корочки.есть</h1>
    <nav>
        <?php if (is_logged_in()): ?>
            <?php if (basename($_SERVER['SCRIPT_NAME']) === 'profile.php'): ?>
                <a href="index.php">Вернуться на главную страницу</a>
            <?php else: ?>
                <a href="profile.php">Профиль</a>
            <?php endif; ?>
            <a href="apply.php">Создать заявку</a>
            <?php if (!in_array(basename($_SERVER['SCRIPT_NAME']), ['authorized.php', 'profile.php'])): ?>
                <a href="logout.php">Выйти</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php">Вход</a>
        <?php endif; ?>
    </nav>
</header>
</body>
</html>
