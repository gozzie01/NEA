<?php
require_once '../utils.php';
require_once './putils.php';
//event and child details check
if (!isset($_GET['child'])) {
    header('Location: ../parent/index.php');
    exit();
}
if (!isset($_GET['event'])) {
    header('Location: ../parent/index.php');
    exit();
}
$event = new Event($_GET['event']);
$child = new Student($_GET['child']);
$event->update();
$child->update();
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
<?php require_once '../parent/nav.php'; ?>

<body>
    <!-- Event Details -->
    <!-- at the top should be the event details (start time, end time and name) -->
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Event Details</h1>
                <!-- in text boxes in columns -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="eventname">Event Name</label>
                            <input type="text" class="form-control" id="eventname" value="<?php echo $event->getName(); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <!-- date -->
                            <label for="date">Date</label>
                            <input type="text" class="form-control" id="date" value="<?php echo date('d/m/Y', strtotime($event->getStartTime())); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <!-- start time -->
                            <label for="starttime">Start Time</label>
                            <input type="text" class="form-control" id="starttime" value="<?php echo date('H:i', strtotime($event->getStartTime())); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <!-- end time -->
                            <label for="endtime">End Time</label>
                            <input type="text" class="form-control" id="endtime" value="<?php echo date('H:i', strtotime($event->getEndTime())); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <h1>Classes</h1>
                <div class="well">
                    <table class="table table-striped table-scroll">
                        <thead>
                            <tr>
                                <th scope="col">Class</th>
                                <th scope="col">Teacher</th>
                                <th scope="col">Selected</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $classes = get_all_classes_of_student_for_event($_GET['child'], $_GET['event']);
                            $slots = get_all_prefslots_of_event_of_student($_GET['child'], $_GET['event']);
                            foreach ($classes as $class) {
                                $class->update();
                                $teachers = $class->getTeachers();
                            ?>
                                <tr>
                                    <td><?php echo $class->getName(); ?></td>
                                    <td>
                                        <?php
                                        //print with comma if more than one teacher
                                        $first = true;
                                        foreach ($teachers as $teacher) {
                                            $teacher = new Teacher($teacher);
                                            $teacher->update();
                                            if ($first) {
                                                echo $teacher->getName();
                                                $first = false;
                                            } else {
                                                echo ", " . $teacher->getName();
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <input type="checkbox"
                                        <?php
                                        foreach ($slots as $slot) {
                                            if ($slot->getClass() == $class->getId()) {
                                                echo "checked";
                                            }
                                        }
                                        ?> disabled >
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <!-- show popup button -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="document.getElementById('popupbox').style.display=''">
                        View Popup
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
<!-- popup -->
<?php
include_once 'popup2.php';
?>