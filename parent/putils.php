<?php
if (!is_logged_in()) {
    header('Location: /login.php');
    exit();
}
if (!is_parent()) {
    header('Location: /index.php');
    exit();
}
if (isset($_SESSION['child'])) {
    global $childid;
    $childid = intval($_SESSION['child']);
}
