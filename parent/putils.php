<?php
if (!is_logged_in()) {
    header('Location: /login.php');
    exit();
}
if (!is_parent()) {
    header('Location: /index.php');
    exit();
}
if (isset($_SESSION['student'])) {
    global $childid;
    $childid = intval($_SESSION['student']);
}
//check that the student in the get belongs to the parent
if (isset($_GET['student'])) {
    if (!student_belongs_to_parent(intval($_GET['student']), intval($_SESSION['parent']))) {
        header('Location: /parent/index.php');
        exit();
    }
}