<?php
include_once('../utils.php');
//check if the user is logged in
include_once('./putils.php');
//display the previous events of the parent
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
    <title>Past Events</title>
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
<?php require_once('../parent/nav.php'); ?>
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
                                <th scope="col">Event</th>
                                <th scope="col">Number of selected</th>
                                <th scope="col">Number Booked</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $child = $_GET['child'];
                            $events = get_all_events_of_student($child);
                            foreach ($events as $event) {
                                $numberSelected = get_number_of_prefSlots_of_event_of_student($event->getID(), $child);
                                $numberBooked = get_number_of_slots_of_event_of_student($event->getID(), $child);
                            ?>
                                <tr>
                                    <td><?php echo $event->getName(); ?></td>
                                    <td><?php echo $numberSelected; ?></td>
                                    <td><?php echo $numberBooked; ?></td>
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