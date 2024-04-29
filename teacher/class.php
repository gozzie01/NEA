<?php
require_once '../utils.php';
//check if the user is logged in
require_once './tutils.php';
$id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setWanted'])) {
    try {
        set_wanted_for_class($_POST['class'], $_POST['students']);
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo $e->getMessage();
        exit();
    }
    exit();
}
if (!isset($_GET['id']) && class__exists(intval($_GET['id']))) {
    header("Location: /classes.php");
    exit();
}
$id = $_GET["id"];
$class = new Class_($id);
$class->update();
$event = get_next_event_of_class($class->getId());
if ($event != null) {
    $wanted = get_wanted_for_class($class->getId());
}
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
            var height = $(window).height() - $('.table-scroll tbody').offset().top - 60;
            $('.table-scroll tbody').css('height', height);
            updateTable();
            //on click of the save button

        });
        $(document).ready(function() {
            $('#save').click(function() {
                var students = [];
                //get all the students
                $('.table-scroll tbody tr').each(function() {
                    if ($(this).find('input').prop('checked')) {
                        students.push($(this).find('th').text());
                    }
                });
                //send the data to the server
                $.ajax({
                    url: '/teacher/class.php',
                    type: 'POST',
                    data: {
                        setWanted: true,
                        students: students,
                        class: <?php echo $id; ?>
                    },
                    success: function(data) {},
                    error: function(data) {
                        alert(data.responseText)
                    }

                });
            });
            $('#clear').click(function() {
                $('.table-scroll tbody tr').each(function() {
                    $(this).find('input').prop('checked', false);
                });
            });
        });
        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - $('.table-scroll tbody').offset().top - 60;
            //just tbody
            $('.table-scroll tbody').css('height', height);
            //adjust size of well
            var heightwell = $(window).height() - $('.well').offset().top - 60;
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
                                    <?php if ($event != null) { ?>
                                        <td>
                                            <form>
                                                <input type="checkbox" <?php
                                                                        if (isset($wanted) &&   $wanted != null) {
                                                                            echo in_array($student->getId(), $wanted) ? 'checked' : '';
                                                                        }
                                                                        ?>>
                                            </form>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <div>
                        <button class="btn btn-primary" id="clear">Clear</button>
                        <button class="btn btn-primary" id="save">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>