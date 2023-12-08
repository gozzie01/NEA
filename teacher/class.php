<?php
require_once '../utils.php';
//check if the user is logged in
require_once './tutils.php';
if (!isset($_GET['id']) && class__exists(intval($_GET['id']))) {
    header("Location: /classes.php");
    die();
}
$id = $_GET["id"];
$class = new Class_($id);
$class->update();
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Class <?php echo $class->getName(); ?></title>
</head>
<?php require_once './nav.php'; ?>

<body>
    <!--display information about the class-->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Class <?php echo $class->getName(); ?></h1>
                <!--display a list of students in the class-->
                <h2>Students</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Student ID</th>
                            <th scope="col">Student Name</th>
                            <?php if (get_next_event_of_class($class->getId()) != null) { ?>
                                <th scope="col">Wanted</th>
                            <?php } ?>
                        <tr>
                    </thead>
                    <tbody>
                        <?php
                        $students = $class->getStudents();
                        foreach ($students as $student) {
                            $student = new Student($student);
                            $student->update();
                        ?>
                            <tr>
                                <th scope="row"><?php echo $student->getId(); ?></th>
                                <td><?php echo $student->getName(); ?></td>
                                <td><form><input type="checkbox"></input></form></td>
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
