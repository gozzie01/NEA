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
        <script>
            // when one is checked, the other is unchecked
            // when one is unchecked, the other is checked
            //on ready alert
            window.addEventListener('DOMContentLoaded', () => {
                const EnCheck = document.getElementById('EnCheck');
                const StCheck = document.getElementById('StCheck');
                StCheck.addEventListener('change', () => {
                    if (StCheck.checked) {
                        EnCheck.checked = false;
                    } else {
                        EnCheck.checked = true;
                    }
                });
                EnCheck.addEventListener('change', () => {
                    if (EnCheck.checked) {
                        StCheck.checked = false;
                    } else {
                        StCheck.checked = true;
                    }
                });
            });
        </script>

        <body id="popup">
            <div id="popupbox" style=" position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
                <!--booking box-->
                <div style="display: flex; justify-content: space-between;">
                    <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 45%;">
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <h3>Book for: <?php echo $student->getName(); ?></h3>
                            </div>
                            <div>
                                <span onclick="document.getElementById('popupbox').style.display='none'" style="color: red; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                            </div>
                        </div>
                        <h3>Times</h3>
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <h4>After: </h4>
                                <!-- start time selector, use event start time -->
                                <form>
                                    <input type="checkbox" id="StCheck" name="After: " value="After: " checked>
                                    <input type="time" id="StTime" step="300" min="18:00" max="21:00" />
                                </form>
                            </div>
                            <div>
                                <h4>Before: </h4>
                                <form>
                                    <input type="checkbox" id="EnCheck" name="Before: " value="Before: ">
                                    <input type="time" id="EnTime" step="300" min="18:00" max="21:00" />
                                </form>
                            </div>
                        </div>
                        <h4>Classes</h4>
                    </div>
                </div>
            </div>
        </body>
<?php
    }
}
?>