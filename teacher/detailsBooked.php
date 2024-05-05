<?php
require_once '../utils.php';
require_once '../classdefs.php';
//check if the user is logged in
require_once './tutils.php';
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Booked</title>
</head>
<?php require_once './nav.php'; ?>

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
                                <th>Student</th>
                                <th>Parent</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $slots = get_all_slots_of_event_of_teacher($event->getID(), $student->getID());
                            foreach ($slots as $slot) {
                                $class = new Class_($slot->getClass());
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