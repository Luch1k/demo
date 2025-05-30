<?php
require 'functions.php';

if (is_logged_in()) {
    header('Location: authorized.php');
    exit;
} else {
    header('Location: guest.php');
    exit;
}
