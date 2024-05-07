<?php
require_once '../utils.php';
require_once '../classdefs.php';
//check if the user is logged in
require_once './tutils.php';
//
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Teacher</title>
</head>
<?php require_once '../includes.php'; ?>
<?php require_once './nav.php'; ?>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Welcome Teacher</h1>
            </div>
        </div>
        <?php
        if (is_pastoral()) {
            foreach (get_all_events() as $event) {
                //check the event has started
                if (new DateTime($event->getStartTime()) < new DateTime()) {
                    continue;
                }
                $event->update();
                $wantedStudents = get_wanted_students_without_prefslot($event->getID());
                //if count is not zero display a warning with the number of students left to book and the name of the event
                if (count($wantedStudents) > 0 && $event->getStatus()!=4) {
        ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning" role="alert">
                                <h4 class="alert-heading">Warning!</h4>
                                <p><?php echo count($wantedStudents); ?> student(s) have been marked as wanted for <?php echo $event->getName(); ?> but have not yet booked. Please contact them and book for them if necessary.</p>
                            </div>
                        </div>
                    </div>
        <?php
                }
            }
        }
        ?>
    </div>
</body>

</html>