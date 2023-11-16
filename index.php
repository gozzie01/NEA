<?php
require_once 'utils.php';
//check if the user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    die();
}
//check if the user is an teacher
if (is_teacher()) {
    header("Location: /teacher/index.php");
    die();
}
//check if the user is an admin
if (is_admin()) {
    header("Location: /admin/index.php");
    die();
}
//check if the user is an parent
if (is_parent()) {
    header("Location: /parent/index.php");
    die();
}
//if we are here something has gone wrong, this should never happen, but just in case we will display an error message and tell the user to try agajn later
header("Location: /logout.php");
echo "Something has gone wrong, please try again later";
die();
