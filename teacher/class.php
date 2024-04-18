<?php
require_once '../utils.php';
//check if the user is logged in
require_once './tutils.php';
if (!isset($_GET['id']) && class__exists(intval($_GET['id']))) {
    header("Location: /classes.php");
    die();
}
$id = $_GET["id"];
$class = new Class_($id);
$class->update();
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Class <?php echo $class->getName(); ?></title>
    <style>
        .well {
            background: none;
            height: 320px;
        }

        .table-scroll tbody {
            overflow-y: scroll;
            height: 200px;
        }

        .table-scroll tr {
            table-layout: fixed;
            display: inline-table;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('#clear').click();
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            $('.table-scroll tbody').css('height', height);
            updateTable();
        });
        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            //just tbody
            $('.table-scroll tbody').css('height', height);
            //adjust size of well
            var heightwell = $(window).height() - $('.well').offset().top;
            $('.well').css('height', heightwell);

        });
    </script>
</head>
<?php require_once './nav.php'; ?>

<body>
    <!--display information about the class-->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Class <?php echo $class->getName(); ?></h1>
                <!--display a list of students in the class-->
                <h2>Students</h2>
                <div class="well">
                    <table class="table table-striped table-scroll">
                        <thead>
                            <tr>
                                <th scope="col">Student ID</th>
                                <th scope="col">Student Name</th>
                                <?php if (get_next_event_of_class($class->getId()) != null) { ?>
                                    <th scope="col">Wanted</th>
                                <?php } ?>
                            <tr>
                        </thead>
                        <tbody>
                            <?php
                            $students = $class->getStudents();
                            foreach ($students as $student) {
                                $student = new Student($student);
                                $student->update();
                            ?>
                                <tr>
                                    <th scope="row"><?php echo $student->getId(); ?></th>
                                    <td><?php echo $student->getName(); ?></td>
                                    <?php if (get_next_event_of_class($class->getId()) != null) { ?>
                                        <td>
                                            <form><input type="checkbox"></input></form>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>