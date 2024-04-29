<?php
require_once 'utils.php';
require_once 'classdefs.php';
//check if the user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}
//check if the user is an teacher
if (is_teacher()) {
    header("Location: /teacher/index.php");
    exit();
}
//check if the user is an admin
if (is_admin()) {
    header("Location: /admin/index.php");
    exit();
}
//check if the user is an parent
if (is_parent()) {
    header("Location: /parent/index.php");
    exit();
}
//if we are here something has gone wrong, this should never happen, but just in case we will display an error message and tell the user to try again later
header("Location: /logout.php");
echo "Something has gone wrong, please try again later";
exit();
