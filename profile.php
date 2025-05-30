<?php
require 'db.php';
require 'functions.php';

require_login();

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT full_name, email, phone, login FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

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

<?php include 'header.php'; ?>

<main>
    <h1>Профиль пользователя</h1>
    <p><strong>Логин:</strong> <?=htmlspecialchars($user['login'])?></p>
    <p><strong>ФИО:</strong> <?=htmlspecialchars($user['full_name'])?></p>
    <p><strong>Email:</strong> <?=htmlspecialchars($user['email'])?></p>
    <p><strong>Телефон:</strong> <?=htmlspecialchars($user['phone'])?></p>

    <p><a href="logout.php">Выйти</a></p>

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
                                    <form method="post" action="profile.php">
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
</main>

<?php include 'footer.php'; ?>
