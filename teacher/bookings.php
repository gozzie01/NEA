<?php
require_once('../utils.php');
require_once('./tutils.php');
$teacher = $_SESSION['teacher'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        $(document).ready(function() {
            alert("height")
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            $('.table-scroll tbody').css('height', height);
        });

        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            alert(height)
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
    </script>
    <?php
    require_once('../includes.php');
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
<?php require_once('../teacher/nav.php'); ?>
<br>

<body>
    <div class="container-fluid" style="width: 100%;">
        <div class="row" style="width: 100%;">
            <div class="col-md-6" style="width: 100%;">
                <h1>Parents' Evenings</h1>
                <div class="well">
                    <table class="table table-striped table-scroll">
                        <thead>
                            <tr>
                                <th scope="col">EventID</th>
                                <th scope="col">Event</th>
                                <th scope="col">Number of students</th>
                                <th scope="col">Number of attempted</th>
                                <th scope="col">Number Booked</th>
                                <th scope="col">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $events = get_all_events_of_teacher($teacher);
                            foreach ($events as $event) {
                                $event->update();
                                $numberTotal = get_number_of_students_of_event_of_teacher($event->getID(), $teacher);
                                $numberAttempted = get_number_of_prefSlots_of_event_of_teacher($event->getID(), $teacher);
                                $numberBooked = get_number_of_slots_of_event_of_teacher($event->getID(), $teacher);
                            ?>
                                <tr>
                                    <td><?php echo $event->getID(); ?></td>
                                    <td><?php echo $event->getName(); ?></td>
                                    <td><?php echo $numberTotal; ?></td>
                                    <td><?php echo $numberAttempted; ?></td>
                                    <td><?php echo $numberBooked; ?></td>
                                    <td>
                                        <?php
                                        if ($event->getStatus() < 3) {
                                        ?>
                                            <a href="details.php?event=<?php echo $event->getID(); ?>" class="btn btn-primary">View</a>
                                        <?php
                                        } elseif ($event->getStatus() == 4) {
                                        ?>
                                            <a href="detailsBooked.php?event=<?php echo $event->getID(); ?>" class="btn btn-primary">View</a>
                                        <?php
                                        } else {
                                        ?>
                                            Event is being booked
                                        <?php
                                        }
                                        ?>
                                    </td>
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