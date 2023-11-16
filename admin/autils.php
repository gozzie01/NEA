<?php
if (!is_logged_in()) {
    header("Location: /login.php");
    die();
}
//check if admin else send to index
if (!is_admin()) {
    header("Location: /index.php");
    die();
}
?>