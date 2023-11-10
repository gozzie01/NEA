<?php
require_once '../utils.php';
//check if the user is logged in
if (!is_logged_in()) {
    header("Location: ../login.php");
    die();
}
//check if parent else send to index
if (!is_parent()) {
    header("Location: ../index.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Parent</title>
</head>
<?php require_once '../parent/nav.php'; ?>

<body>
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Parent</h1>
                <p>Welcome to the parent page</p>
            </div>
        </div>
    </div>
</body>

</html>