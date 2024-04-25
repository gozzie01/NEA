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
    $show = $event->isBookOpen() && !has_booked($_GET['child'], $event->getId());
    //if parentbounds is null, set it to 2
    if ($event->getParentBounds() === null) {
        $parentBounds = 2;
    } else {
        $parentBounds = $event->getParentBounds();
    }
    if ($show) {
        //the form should be a simple with an earliest and latest time for arrival and departure, when one is ticked the other is greyed out
        //then the form should list below a list of classes and the associated teacher available for the child
        //the parent can then select which classes that they want to see
?>
        <script>
            // when one is checked, the other is unchecked
            // when one is unchecked, the other is checked
            //on ready alert
            const parentBounds = <?php echo $parentBounds  ?>;

            window.addEventListener('DOMContentLoaded', () => {
                const EnCheck = document.getElementById('EnCheck');
                const StCheck = document.getElementById('StCheck');

                // Initial toggle based on parentBounds
                if (parentBounds === 0) {
                    EnCheck.checked = false;
                    StCheck.checked = false;
                    //disable both
                    EnCheck.disabled = true;
                    StCheck.disabled = true;
                }
                StCheck.addEventListener('change', () => {
                    if (parentBounds === 1) {
                        if (StCheck.checked) {
                            EnCheck.checked = false;
                        }
                    }
                });

                EnCheck.addEventListener('change', () => {
                    if (parentBounds === 1) {
                        if (EnCheck.checked) {
                            StCheck.checked = false;
                        }
                    }
                });
            });



            //on submit
            function submitForm() {
                const xhttp = new XMLHttpRequest();
                const StCheck = document.getElementById('StCheck');
                const EnCheck = document.getElementById('EnCheck');
                const StTime = document.getElementById('StTime');
                const EnTime = document.getElementById('EnTime');
                const classes = [];
                const checkboxes = document.querySelectorAll('input[type=checkbox]:checked');
                checkboxes.forEach((checkbox) => {
                    classes.push(checkbox.name);
                });
                if (parentBounds === 0 && (StCheck.checked || EnCheck.checked)) {
                    alert("No bounds can be set");
                    return;
                } else if (parentBounds === 1 && !(StCheck.checked ^ EnCheck.checked) && (StCheck.checked || EnCheck.checked)) {
                    alert("Only one bound can be set");
                    return;
                }
                if (StCheck.checked) {
                    if (StTime.value === "") {
                        alert("Please select a time");
                        return;
                    }
                    if (classes.length === 0) {
                        alert("Please select at least one class");
                        return;
                    }
                }
                if (EnCheck.checked) {
                    if (EnTime.value === "") {
                        alert("Please select a time");
                        return;
                    }
                    if (classes.length === 0) {
                        alert("Please select at least one class");
                        return;
                    }
                }
                xhttp.onreadystatechange = function() {

                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById('popupbox').style.display = 'none';
                    }
                };
                xhttp.open("POST", "/parent/book.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                var sendstring = "student=<?php echo $student->getId(); ?>&event=<?php echo $event->getId(); ?> " + "&classes=" + classes;
                if (StCheck.checked) {
                    sendstring += "&starttime=" + StTime.value;
                }
                if (EnCheck.checked) {
                    sendstring += "&endtime=" + EnTime.value;
                }
                xhttp.send(sendstring);
            }
        </script>

        <body id="popup">
            <div id="popupbox" style=" position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
                <!--booking box-->

                <div style="display: flex; justify-content: space-between;">
                    <!-- -->
                    <div class="popupbook" style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; ">
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <h3>Book for: <?php echo $student->getName(); ?></h3>
                                <h4>Event: <?php echo $event->getName(); ?>, <?php echo (new DateTime($event->getStartTime()))->format("D d M H:i") . " to " . (new DateTime($event->getEndTime()))->format("H:i") ?> </h4>
                            </div>
                            <div>
                                <span id="exitButton" onclick="document.getElementById('popupbox').style.display='none'" style="color: red; float: right; font-size: 28px; font-weight: bold; cursor: pointer;" tabindex="0">&times;</span>
                            </div>
                        </div>
                        <h3>Times</h3>
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <h4>After: </h4>
                                <!-- start time selector, use event start time -->
                                <form>
                                    <input type="checkbox" id="StCheck" name="After: " value="After: " aria-label="after toggle">
                                    <input type="time" id="StTime" step="300" aria-label="available after time" />
                                </form>
                            </div>
                            <div>
                                <h4>Before: </h4>
                                <form>
                                    <input type="checkbox" id="EnCheck" name="Before: " value="Before: " aria-label="before toggle">
                                    <input type="time" id="EnTime" step="300" aria-label="available before time" />
                                </form>
                            </div>
                        </div>
                        <h4>Classes:</h4>
                        <?php
                        $classes = get_all_classes_of_student_for_event($student->getId(), $event->getId());
                        foreach ($classes as $class) {
                            if (isset($class->getTeachers()[0])) {
                                $teacher = new Teacher($class->getTeachers()[0]);
                                $teacher->update();
                                echo "<div style='display: flex; justify-content: space-between;'>";
                                echo "<div>";
                                echo "<h5>" . $class->getName() . "</h5>";
                                echo "<h6>" . $teacher->getName() . "</h6>";
                                echo "</div>";
                                echo "<div>";
                                echo "<input type='checkbox' id='" . $class->getId() . "' name='" . $class->getId() . "' value='" . $class->getId() . "' aria-label='toggle" . $class->getName() . "'>";
                                echo "</div>";
                                echo "</div>";
                            }
                        }
                        //submit button

                        ?>
                        <button onclick="submitForm()" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </body>
<?php
    }
}
?>