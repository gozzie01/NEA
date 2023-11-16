<?php
if (!is_logged_in()) {
    header("Location: /login.php");
    exit();
}
if (!is_teacher()) {
    header("Location: /index.php");
    exit();
}
