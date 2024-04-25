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
//check that the child in the get belongs to the parent
if (isset($_GET['child'])) {
    if (!student_belongs_to_parent(intval($_GET['child']), intval($_SESSION['parent']))) {
        header('Location: /parent/index.php');
        exit();
    }
}