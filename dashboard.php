<?php
require 'db.php';
require 'functions.php';

require_login();

$user_id = $_SESSION['user_id'];

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['review'])) {
    $application_id = (int)$_POST['application_id'];
    $review = trim($_POST['review']);

    // Check if application belongs to user and status is 'Обучение завершено'
    $stmt = $pdo->prepare("SELECT status FROM applications WHERE id = ? AND user_id = ?");
    $stmt->execute([$application_id, $user_id]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($app && $app['status'] === 'Обучение завершено') {
        $stmt = $pdo->prepare("UPDATE applications SET review = ? WHERE id = ?");
        $stmt->execute([$review, $application_id]);
    }
}

// Fetch user applications with course names
$stmt = $pdo->prepare("
    SELECT a.id, c.name AS course_name, a.start_date, a.payment_method, a.status, a.review
    FROM applications a
    JOIN courses c ON a.course_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Личный кабинет - Корочки.есть</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <h1>Личный кабинет</h1>
    <p>Добро пожаловать, <?=htmlspecialchars($_SESSION['login'])?>! <a href="logout.php">Выйти</a></p>
    <p><a href="apply.php">Подать заявку на курс</a></p>

    <h2>Ваши заявки</h2>
    <?php if (empty($applications)): ?>
        <p>Заявок пока нет.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Курс</th>
                    <th>Дата начала</th>
                    <th>Способ оплаты</th>
                    <th>Статус</th>
                    <th>Отзыв</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?=htmlspecialchars($app['course_name'])?></td>
                        <td><?=htmlspecialchars($app['start_date'])?></td>
                        <td><?=htmlspecialchars($app['payment_method'])?></td>
                        <td><?=htmlspecialchars($app['status'])?></td>
                        <td>
                            <?php if ($app['status'] === 'Обучение завершено'): ?>
                                <?php if ($app['review']): ?>
                                    <?=nl2br(htmlspecialchars($app['review']))?>
                                <?php else: ?>
                                    <form method="post" action="dashboard.php">
                                        <input type="hidden" name="application_id" value="<?= $app['id'] ?>" />
                                        <textarea name="review" rows="3" cols="30" required></textarea><br />
                                        <button type="submit">Оставить отзыв</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                Отзыв доступен после завершения обучения
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
