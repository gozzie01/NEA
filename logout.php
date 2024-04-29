<?php
require_once 'utils.php';
require_once 'classdefs.php';
//check if the user is logged in
if (!is_logged_in()) {
    header("Location: ../login.php");
    exit();
} else {
    destroy_token();
}
//unset the session variables
session_unset();
//destroy the session
session_destroy();
//unset the token on the database
//redirect the user to the login page
header("Location: ../login.php");
exit();
