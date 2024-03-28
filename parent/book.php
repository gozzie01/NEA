<?php
//check if the user is logged in
require_once('../utils.php');
require_once('./putils.php');
//this is where the post for the form is handled
/*                xhttp.send("student=<?php echo $student->getId(); ?>&event=<?php echo $event->getId(); ?>&time=" + EnTime.value + "&classes=" + classes);*/
if (isset($_POST['student']) && isset($_POST['event']) && (isset($_POST['endtime']) || isset($_POST['starttime'])) && isset($_POST['classes'])) {
    $student = new Student($_POST['student']);
    $student->update();
    $event = new Event($_POST['event']);
    $event->update();
    $classes = explode(",", $_POST['classes']);
    //get the parent id of the session
    $parent_id = intval($_SESSION['parent']);
    //if start time is set, then the end time is set to the end of the event
    if (isset($_POST['starttime'])) {
        $endtime = $event->getEndTime();
        //set the start time to the time that was posted on the day of the event
        $starttime = $event->getStartTime();
        $starttime = new DateTime($starttime);
        $starttime->setTime(intval($_POST['starttime']), 0);
        //convert to string
        $starttime = $starttime->format('Y-m-d H:i:s');
    } else {
        $starttime = $event->getStartTime();
        $endtime = $event->getEndTime();
        //set the end time to the time that was posted on the day of the event
        $endtime = new DateTime($endtime);
        $endtime->setTime(intval($_POST['endtime']), 0);
        //convert to string
        $endtime = $endtime->format('Y-m-d H:i:s');
    }
    foreach ($classes as $class) {
        //check that class is an int
        if (is_numeric($class)) {

            $teacher = get_teacher_of_class_of_event($class, $event->getId());
            //if its not set, then the teacher of the class is the teacher
            if ($teacher == null) {
                $classa = new Class_($class);
                $classa->update();
                $teacher = $classa->getTeachers()[0];
            }
            create_prefSlot($starttime, $endtime, $teacher, $event->getID(), $class, $student->getId(), $parent_id);
        }
    }
    die();
}
