<?php
require_once '../utils.php';
//check if the user is logged in
require_once './autils.php';
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <meta charset="UTF-8">
    <title>Event Management</title>
    <link rel="stylesheet" href="../style.css">
</head>
<?php include_once '../admin/nav.php'; ?>

<body>
    <div class="main">
        <h1>Event Management</h1>
        <!--this page is for makeing new events and editing existing ones-->
        <!--contain a list of classes for the selected year group-->
        <!--contain a list of teachers-->