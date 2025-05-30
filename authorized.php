<?php
require 'functions.php';
require_login();
?>

<?php include 'header.php'; ?>

<main>
    <h2>Авторизованная страница</h2>
    <p>Добро пожаловать, <?=htmlspecialchars($_SESSION['login'])?>!</p>
</main>

<?php include 'footer.php'; ?>
