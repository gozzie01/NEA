<?php
require_once '../utils.php';
//check if the user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    die();
}
//check if admin else send to index
if (!is_teacher()) {
    header("Location: /index.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
    <?php require_once '../includes.php'; ?>
    