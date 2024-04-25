<?php
require_once '../utils.php';
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
            $students = get_wanted_students_without_prefslot(4);
            foreach ($students as $student) {
                echo $student;
        ?>
                <!-- students marked as wanted who are yet to book -->
        <?php
            }
        }
        ?>
    </div>
</body>

</html>