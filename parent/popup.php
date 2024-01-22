<?php
//this is the popup.php file, it is included on the parent/index.php page but only when it is needed
//the popup is to display when the parent has an event (parents evening), to book for the currently selected child
//the popup is a form that allows the parent to select an eariliest OR latest time for arrival/departure for the event
//the form is then submitted to this page which will update the database with the new times

//check if the user is logged in
require_once('../utils.php');
require_once('./putils.php');
if (isset($_GET['child']) && get_next_event_of_child($_GET['child']) !== null) {
    $student = new Student($_GET['child']);
    $student->update();
    $event = new Event((int)get_next_event_of_child($_GET['child']));
    $event->update();
    if ($event->isBookOpen()) {

        //the form should be a simple with an earliest and latest time for arrival and departure, when one is ticked the other is greyed out
        //then the form should list below a list of classes and the associated teacher available for the child
        //the parent can then select which classes that they want to see
?>

        <body id="popup">
            <div id="popupbox" style=" position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
                <!--booking box-->
                <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
                    <h2>Book for <?php echo $student->getName(); ?></h2>
                    <h3>Times</h3>
                    <h4>Start</h4>
                    <!-- start time selector, use event start time -->
                    <form>
                        <input type="time" step="300" min="18:00" max="21:00"/>
                    </form>
                    <h4>End</h4>
                    <form>
                        <input type="time" step="300" min="18:00" max="21:00"/>
                    </form>
                    <h4>Classes</h4>
                </div>
            </div>
        </body>
<?php
    }
}
?>