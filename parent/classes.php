<?php
require_once('../utils.php');
require_once('./putils.php');
if (!isset($_GET['child'])) {
    header('Location: ../parent/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        $(document).ready(function() {
            alert("height")
            var height = $(window).height() - 240;
            $('.table-scroll tbody').css('height', height);
        });

        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - 240;
            alert(height)
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
    </script>
    <?php
    require_once('../includes.php');
?>
    <title>Parent Classes</title>
    <style type="text/css">
        .well {
            background: none;
            height: 320px;
        }

        .table-scroll tbody {
            overflow-y: scroll;
            height: 400px;
        }

        .table-scroll tr {
            table-layout: fixed;
            display: inline-table;
        }

        .ClassForm input {
            margin: 2px;
        }
    </style>
</head>
<?php require_once('../parent/nav.php'); ?>

<body>
    <div class="container-fluid" style="width: 100%;">
        <div class="row" style="width: 100%;">
            <div class="col-md-6" style="width: 100%;">
                <h1>Classes</h1>
                <div class="well">
                    <table class="table table-striped table-scroll">
                        <thead>
                            <tr>
                                <th scope="col">Class</th>
                                <th scope="col">Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $student = new Student(intval($_GET['child']));
$student->update();
$classes = $student->getClasses();
foreach ($classes as $class) {
    $class = intval($class);
    $clas = new Class_($class);
    $clas->update();
    $teachers = $clas->getTeachers();
    echo "<tr>";
    echo "<td>" . $clas->getName() . "</td>";
    echo "<td>";
    foreach ($teachers as $teacher) {
        $teacher = intval($teacher);
        $teach = new Teacher($teacher);
        $teach->update();
        echo $teach->getName() . " ";
    }
    echo "</tr>";
}
?>
                        </tbody>
                    </table>
                </div>
            </div>