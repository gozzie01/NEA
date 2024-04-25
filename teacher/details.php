<?php
require_once '../utils.php';
//check if the user is logged in
require_once './tutils.php';
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Classes</title>
</head>
<?php require_once './nav.php'; ?>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Classes</h1>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Class ID</th>
                            <th scope="col">Class Name</th>
                            <th scope="col">Teacher(s) Name(s)</th>
                            <th scope="col">Students</th>
                            <th scope="col">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $classes = get_all_classes_of_teacher_of_event($_SESSION['teacher'], $_GET['event']);
                        foreach ($classes as $Class) {
                        ?>
                            <tr>
                                <th scope="row"><?php echo $Class->getID(); ?></th>
                                <td><?php echo $Class->getName(); ?></td>
                                <?php
                                $teachers = $Class->getTeachers();
                                $teacher_names = array();
                                foreach ($teachers as $teacher) {
                                    $sql = "SELECT Name FROM `Teacher` WHERE `id` = ?";
                                    $stmt = $GLOBALS['db']->prepare($sql);
                                    $stmt->bind_param("i", $teacher);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $row = $result->fetch_assoc();
                                    array_push($teacher_names, $row['Name']);
                                    $stmt->close();
                                }
                                $teacher_string = implode("<br>", $teacher_names);
                                ?>
                                <td><?php echo $teacher_string; ?></td>
                                <td><?php echo count($Class->getStudents()); ?></td>
                                <td><a href="class.php?id=<?php echo $Class->getID(); ?>">View</a></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
```