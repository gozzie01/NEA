<?php
//check if the user is logged in
require_once('../utils.php');
require_once('./putils.php');
//this is where the post for the form is handled
/*                xhttp.send("student=<?php echo $student->getId(); ?>&event=<?php echo $event->getId(); ?>&time=" + EnTime.value + "&classes=" + classes);*/
if (isset($_POST['student']) && isset($_POST['event'])  && isset($_POST['classes'])) {
    if (isset($_POST['resend'])) {
        destroy_all_prefSlots_of_student_of_event($_POST['student'], $_POST['event']);
    }
    $student = new Student($_POST['student']);
    $student->update();
    $event = new Event($_POST['event']);
    $event->update();
    $classes = explode(",", $_POST['classes']);
    //get the parent id of the session
    $parent_id = intval($_SESSION['parent']);
    //if start time is set, then the end time is set to the end of the event
    if (isset($_POST['starttime'])) {
        //set the start time to the time that was posted on the day of the event
        $starttime = $event->getStartTime();
        $starttime = new DateTime($starttime);
        // Split the time into hours and minutes
        list($hours, $minutes) = explode(':', $_POST['starttime']);
        // Set the time
        $starttime->setTime((int)$hours, (int)$minutes);
        if ($starttime > $event->getEndTime()) {
            $starttime = $event->getStartTime();
            $starttime = new DateTime($starttime);
        }
        //check that it is before the posted end time if its set
        if (isset($_POST['endtime'])) {
            list($hoursEnd, $minutesEnd) = explode(':', $_POST['endtime']);
            //check hours if its less than the end time
            if ($hours < $hoursEnd) {
                $starttime = $event->getStartTime();
                $starttime = new DateTime($starttime);
            } else if ($hours == $hoursEnd) {
                //check minutes if its less than the end time
                if ($minutes < $minutesEnd) {
                    $starttime = $event->getStartTime();
                    $starttime = new DateTime($starttime);
                }
            }
        }
        //convert to string
        $starttime = $starttime->format('Y-m-d H:i:s');
    } else {
        $starttime = $event->getStartTime();
    }
    if (isset($_POST['endtime'])) {
        //set the end time to the time that was posted on the day of the event
        $endtime = $event->getEndTime();
        $endtime = new DateTime($endtime);
        //ensur
        // Split the time into hours and minutes
        list($hours, $minutes) = explode(':', $_POST['endtime']);
        //ensure that this is before the end of the event and after the start and after the posted start time if its set
        // Set the time
        $endtime->setTime((int)$hours, (int)$minutes);
        if ($endtime < new DateTime($starttime)) {
            $endtime = $event->getEndTime();
            $endtime = new DateTime($endtime);
        }
        //convert to string
        $endtime = $endtime->format('Y-m-d H:i:s');
    } else {
        $endtime = $event->getEndTime();
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
    exit();
}
