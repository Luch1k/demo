<?php
require 'db.php';
require 'functions.php';

$errors = [];

// Handle admin login
if (!is_admin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($login === 'admin' && $password === 'education') {
            // Fetch admin user from DB
            $stmt = $pdo->prepare("SELECT id, role FROM users WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['role'] === 'admin') {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['login'] = $login;
                $_SESSION['role'] = 'admin';
                header('Location: admin.php');
                exit;
            } else {
                $errors[] = 'Пользователь не найден или не является администратором.';
            }
        } else {
            $errors[] = 'Неверный логин или пароль.';
        }
    }
} else {
    // Admin is logged in, handle filtering, pagination, status update

    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['status'])) {
        $application_id = (int)$_POST['application_id'];
        $status = $_POST['status'];
        if (in_array($status, ['Новая', 'Идёт обучение', 'Обучение завершено'])) {
            $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
            $stmt->execute([$status, $application_id]);
            $_SESSION['notification'] = "Статус заявки #$application_id обновлен на '$status'.";
            header('Location: admin.php');
            exit;
        }
    }

    // Filtering
    $filter_status = $_GET['status'] ?? '';

    $params = [];
    $where = '';
    if (in_array($filter_status, ['Новая', 'Идёт обучение', 'Обучение завершено'])) {
        $where = 'WHERE a.status = ?';
        $params[] = $filter_status;
    }

    // Pagination
    $per_page = 10;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $per_page;

    // Count total
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a $where");
    $count_stmt->execute($params);
    $total = $count_stmt->fetchColumn();

    // Fetch applications with user and course info
    $sql = "
        SELECT a.id, u.full_name, u.login, c.name AS course_name, a.start_date, a.payment_method, a.status, a.review, a.created_at
        FROM applications a
        JOIN users u ON a.user_id = u.id
        JOIN courses c ON a.course_id = c.id
        $where
        ORDER BY a.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total pages
    $total_pages = ceil($total / $per_page);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Панель администратора - Корочки.есть</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
<div class="container mt-4">
<?php if (!is_admin()): ?>
    <h1 class="mb-4">Вход администратора</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?=htmlspecialchars($error)?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="admin.php" novalidate>
        <div class="mb-3">
            <label class="form-label">Логин:</label>
            <input type="text" name="login" class="form-control" required />
        </div>
        <div class="mb-3">
            <label class="form-label">Пароль:</label>
            <input type="password" name="password" class="form-control" required />
        </div>
        <button type="submit" class="btn btn-primary">Войти</button>
    </form>
<?php else: ?>
    <h1 class="mb-4">Панель администратора</h1>
    <p>Добро пожаловать, <?=htmlspecialchars($_SESSION['login'])?>! <a href="logout.php" class="btn btn-link">Выйти</a></p>

    <?php if (isset($_SESSION['notification'])): ?>
        <div class="alert alert-success"><?=htmlspecialchars($_SESSION['notification'])?></div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>

    <form method="get" action="admin.php" class="mb-3">
        <label class="form-label">Фильтр по статусу:</label>
        <select name="status" onchange="this.form.submit()" class="form-select w-auto d-inline-block ms-2">
            <option value="">Все</option>
            <option value="Новая" <?= $filter_status === 'Новая' ? 'selected' : '' ?>>Новая</option>
            <option value="Идёт обучение" <?= $filter_status === 'Идёт обучение' ? 'selected' : '' ?>>Идёт обучение</option>
            <option value="Обучение завершено" <?= $filter_status === 'Обучение завершено' ? 'selected' : '' ?>>Обучение завершено</option>
        </select>
    </form>

    <?php if (empty($applications)): ?>
        <p>Заявок нет.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Логин</th>
                    <th>Курс</th>
                    <th>Дата начала</th>
                    <th>Оплата</th>
                    <th>Статус</th>
                    <th>Отзыв</th>
                    <th>Дата подачи</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= $app['id'] ?></td>
                        <td><?= htmlspecialchars($app['full_name']) ?></td>
                        <td><?= htmlspecialchars($app['login']) ?></td>
                        <td><?= htmlspecialchars($app['course_name']) ?></td>
                        <td><?= htmlspecialchars($app['start_date']) ?></td>
                        <td><?= htmlspecialchars($app['payment_method']) ?></td>
                        <td><?= htmlspecialchars($app['status']) ?></td>
                        <td><?= nl2br(htmlspecialchars($app['review'])) ?></td>
                        <td><?= htmlspecialchars($app['created_at']) ?></td>
                        <td>
                            <form method="post" action="admin.php" style="margin:0;">
                                <input type="hidden" name="application_id" value="<?= $app['id'] ?>" />
                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                    <option value="Новая" <?= $app['status'] === 'Новая' ? 'selected' : '' ?>>Новая</option>
                                    <option value="Идёт обучение" <?= $app['status'] === 'Идёт обучение' ? 'selected' : '' ?>>Идёт обучение</option>
                                    <option value="Обучение завершено" <?= $app['status'] === 'Обучение завершено' ? 'selected' : '' ?>>Обучение завершено</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="admin.php?page=<?= $i ?>&status=<?= urlencode($filter_status) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
</div>
</body>
</html>
