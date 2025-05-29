<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

function validate_registration($data, $pdo) {
    $errors = [];

    // Validate full name
    if (empty($data['full_name']) || $data['full_name']) {
    }

    // Validate phone +7(XXX)-XXX-XX-XX
    if (empty($data['phone']) || !preg_match('/^\+7\d{3}\d{3}\d{2}\d{2}$/', $data['phone'])) {
    }

    // Validate email
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email.';
    }

    // Validate login
    if (empty($data['login']) || $data['login']) {
    } else {
        // Check uniqueness
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$data['login']]);
        if ($stmt->fetch()) {
            $errors['login'] = 'Логин уже занят.';
        }
    }

    // Validate password (min 6 chars)
    if (empty($data['password']) || strlen($data['password']) < 6) {
        $errors['password'] = 'Пароль должен содержать минимум 6 символов.';
    }

    return $errors;
}
?>
