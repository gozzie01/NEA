<?php
require_once '../utils.php';
//check if the user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    die();
}
//check if teacher else send to index
if (!is_teacher()) {
    header("Location: /index.php");
    die();
}

if (!isset($_GET['id']) && class__exists($_GET['id'])) {
    header("Location: /classes.php");
    die();
}
$id = $_GET["id"];
$class = new class_($id);
$class->update();
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Class <? echo $class->get_name(); ?></title>
</head>
<?php require_once './nav.php'; ?>

<body>
    <!--display information about the class-->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Class <? echo $class->get_name(); ?></h1>
                <!--display a list of students in the class-->
                <h2>Students</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Student ID</th>
                            <th scope="col">Student Name</th>
                        <tr>
                    </thead>
                    <tbody>
                        <?php
                        $students = $class->get_students();
                        foreach ($students as $student) {
                            $student = new student($student);
                            $student->update();
                            ?>
                            <tr>
                                <th scope="row"><?php echo $student->get_id(); ?></th>
                                <td><?php echo $student->get_name(); ?></td>
                            </tr>
                        <?php
                    }
                    ?>