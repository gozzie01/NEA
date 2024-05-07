<?php
require_once '../utils.php';
require_once '../classdefs.php';
require_once './putils.php';
//event and student details check
if (!isset($_GET['student'])) {
    header('Location: ../parent/index.php');
    exit();
}
if (!isset($_GET['event'])) {
    header('Location: ../parent/index.php');
    exit();
}
//this page is going to display the timetable for the event of the currently selected student
$event = new Event($_GET['event']);
$student = new Student($_GET['student']);
$event->update();
$student->update();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        $(document).ready(function() {
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            $('.table-scroll tbody').css('height', height);
        });

        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
    </script>
    <?php
    require_once '../includes.php';
    ?>
    <title>Event Details</title>
    <style>
        .well {
            background: none;
            height: 320px;
        }

        .table-scroll tbody {
            overflow-y: scroll;
            height: 320px;
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
    <!-- Event Details -->
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="well">
                    <table class="table table-striped table-scroll">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Teacher</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $slots = get_all_slots_of_event_of_student($event->getID(), $student->getId());
                            foreach ($slots as $slot) {
                                $class = new Class_($slot->getClass());
                                $class->update();
                                echo "<tr>";
                                echo "<td>" . $class->getName() . "</td>";
                                echo "<td>" . $slot->getTeacherName() . "</td>";
                                echo "<td>" . $slot->getStartTime(). "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>