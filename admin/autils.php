<?php
if (!is_logged_in()) {
    header("Location: /login.php");
    exit();
}
//check if admin else send to index
if (!is_admin()) {
    header("Location: /index.php");
    exit();
}
//check for a complete.json file in root
if (file_exists("../complete.json")) {

}
