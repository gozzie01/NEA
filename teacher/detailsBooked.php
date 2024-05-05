<?php
require_once '../utils.php';
require_once '../classdefs.php';
//check if the user is logged in
require_once './tutils.php';
//event and student details check

if (!isset($_GET['event'])) {
    header('Location: ../teacher/index.php');
    exit();
}

$event = new Event($_GET['event']);
$event->update();
$teacher = new Teacher($_SESSION['teacher']);
$teacher->update();
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
                            $slots = get_all_slots_of_event_of_teacher($event->getID(), $teacher->getID());
                            foreach ($slots as $slot) {
                                $class = new Class_($slot->getClass());
                                $class->update();
                                $student = new Student($slot->getStudent());
                                $student->update();
                                $parent = new Parent_($slot->getParent());
                                $parent->update();
                                echo "<tr>";
                                echo "<td>" . $class->getName() . "</td>";
                                echo "<td>" . $student->getName() . "</td>";
                                echo "<td>" . $parent->getName() . "</td>";
                                echo "<td>" . $slot->getStartTime() . "</td>";
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