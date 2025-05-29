<?php
require 'db.php';
require 'functions.php';

require_login();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Fetch courses for dropdown
$stmt = $pdo->query("SELECT id, name FROM courses ORDER BY name");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    // Validate course_id
    $valid_course = false;
    foreach ($courses as $course) {
        if ($course['id'] === $course_id) {
            $valid_course = true;
            break;
        }
    }
    if (!$valid_course) {
        $errors['course_id'] = 'Выберите корректный курс.';
    }

    // Validate start_date (YYYY-MM-DD)
    if (!$start_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        $errors['start_date'] = 'Выберите корректную дату начала.';
    }

    // Validate payment_method
    if (!in_array($payment_method, ['Наличные', 'Банковский перевод'])) {
        $errors['payment_method'] = 'Выберите способ оплаты.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO applications (user_id, course_id, start_date, payment_method) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $course_id, $start_date, $payment_method]);
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Подать заявку - Корочки.есть</title>
    <link rel="stylesheet" href="styles.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ENjdO4Dr2bkBIFxQpeoYz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Подать заявку на курс</h1>
    <p><a href="dashboard.php" class="btn btn-link">Назад в личный кабинет</a> | <a href="logout.php" class="btn btn-link">Выйти</a></p>

    <?php if ($success): ?>
        <div class="alert alert-success">Заявка успешно отправлена. <a href="dashboard.php">Вернуться в личный кабинет</a></div>
    <?php else: ?>
        <form method="post" action="apply.php" novalidate>
            <div class="mb-3">
                <label class="form-label">Курс:</label>
                <select name="course_id" class="form-select" required>
                    <option value="">-- Выберите курс --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>" <?= (isset($_POST['course_id']) && $_POST['course_id'] == $course['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-danger"><?= $errors['course_id'] ?? '' ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Дата начала:</label>
                <input type="date" name="start_date" value="<?=htmlspecialchars($_POST['start_date'] ?? '')?>" class="form-control" required />
                <div class="text-danger"><?= $errors['start_date'] ?? '' ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Способ оплаты:</label><br />
                <div class="form-check form-check-inline">
                    <input type="radio" name="payment_method" value="Наличные" id="payment_cash" class="form-check-input" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'Наличные') ? 'checked' : '' ?> required />
                    <label for="payment_cash" class="form-check-label">Наличные</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" name="payment_method" value="Банковский перевод" id="payment_bank" class="form-check-input" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'Банковский перевод') ? 'checked' : '' ?> />
                    <label for="payment_bank" class="form-check-label">Банковский перевод</label>
                </div>
                <div class="text-danger"><?= $errors['payment_method'] ?? '' ?></div>
            </div>

            <button type="submit" class="btn btn-primary">Отправить заявку</button>
        </form>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+3oBO+z0I1p6jizoUksdQRVvoxMfoo" crossorigin="anonymous"></script>
</body>
</html>
