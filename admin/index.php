<?php
require_once '../utils.php';
//check if the user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    die();
}
//check if admin else send to index
if (!is_admin()) {
    header("Location: /index.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Admin</title>
</head>

<body>
    <?php require_once '../admin/nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Admin</h1>
                <p>Welcome to the admin page</p>
            </div>
        </div>
    </div>