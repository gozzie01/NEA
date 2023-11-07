<?php
require_once 'utils.php';
//check if the user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    die();
}
//unset the session variables
session_unset();
//destroy the session
session_destroy();
//redirect the user to the login page
header("Location: login.php");
die();
