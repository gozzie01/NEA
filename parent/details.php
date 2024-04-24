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