<?php
require_once '../utils.php';
require_once '../classdefs.php';
//check if the user is logged in
require_once './tutils.php';
//check if the user is a pastoral teacher
if (!is_pastoral()) {
    header("Location: /teacher/index.php");
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getStudents'])) {
    //get all students and add the id and name to the select
    foreach (get_all_students() as $student) {
        echo "<option value='" . $student->getID() . "'>" . $student->getName() . "</option>";
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getEvents'])) {
    //get all events and add the id and name to the select
    foreach (get_all_events() as $event) {
        echo "<option value='" . $event->getID() . "'>" . $event->getName() . "</option>";
    }
    exit();
}

//get all parents of the student and add the id and name to the select
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getParents'])) {
    $student = new Student($_POST['studentID']);
    $student->update();
    foreach ($student->getParents() as $parent) {
        $parent = new Parent_($parent);
        $parent->update();
        echo "<option value='" . $parent->getID() . "'>" . $parent->getName() . "</option>";
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getClasses'])) {
    //get the classes of the student for the event and add the id and name to the select
    $student = new Student($_POST['studentID']);
    $student->update();
    $event = new Event($_POST['eventID']);
    $event->update();
    $classes = get_all_classes_of_student_for_event($student->getID(), $event->getID());
    foreach ($classes as $class) {
        $class->update();
        echo "<option value='" . $class->getID() . "'>" . $class->getName() . "</option>";
    }
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&    isset($_POST['getTimes'])) {
    //get start and end times of the event
    $event = new Event($_POST['eventID']);
    $event->update();
    echo $event->getStartTime() . " " . $event->getEndTime();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getTableHTML'])) {
    foreach (get_all_events() as $event) {
        foreach (get_wanted_students_without_prefslot($event->getID()) as $student) {
            $student = new Student($student);
            $wantedClasses = get_wanted_classids_of_student($student->getID());
            $student->update();
            echo "<tr>";
            echo "<td>" . $student->getID() . "</td>";
            echo "<td>" . $student->getName() . "</td>";
            //put id in td tag so can be grabbed by js later
            echo "<td id='" . $event->getID()  . "'>" . $event->getName() . "</td>";
            echo "<td>";
            foreach ($wantedClasses as $class) {
                $class = new Class_($class);
                $class->update();
                echo $class->getName() . "<br>";
            }
            echo "</td>";
            echo "</tr>";
        }
    }
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addWanted'])) {
    //add a new prefslot for the student
    $student = $_POST['studentID'];
    $event = new Event($_POST['eventID']);
    $event->update();
    $parent = $_POST['parentID'];
    $class = $_POST['classID'];
    $teacher = get_teacher_of_class_of_event($class, $event->getID());
    $arriveTime = $_POST['arriveTime'];
    $leaveTime = $_POST['leaveTime'];
    //set arrive and leave time to the date of the event using start time
    $startTime = strtotime($event->getStartTime());
    $arriveTime = date("Y-m-d H:i:s", strtotime($arriveTime, $startTime));
    $leaveTime = date("Y-m-d H:i:s", strtotime($leaveTime, $startTime));
    //if class is a list of classes, add a prefslot for each class
    if (is_array($class)) {
        foreach ($class as $c) {
            $response = create_prefslot($arriveTime, $leaveTime, $teacher, $event->getID(), $c, $student, $parent);
        }
    } else {
        $response = create_prefslot($arriveTime, $leaveTime, $teacher, $event->getID(), $class, $student, $parent);
    }

    if (isset($response) && $response) {
        echo "success";
    } else {
        echo "error";
    }
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Teacher</title>
    <?php require_once '../includes.php'; ?>

    <script>
        $(document).ready(function() {
            updateTable();
            document.getElementById("prefSlotUpdateForm").addEventListener("submit", function(e) {
                e.preventDefault();
                //check if all fields are filled
                var studentID = document.getElementById("studentID").value;
                var eventID = document.getElementById("eventID").value;
                var parentID = document.getElementById("parentID").value;
                var classID = document.getElementById("classID").value;
                var arriveTime = document.getElementById("arriveTime").value;
                var leaveTime = document.getElementById("leaveTime").value;
                if (studentID == "" || eventID == "" || parentID == "" || classID == "" || arriveTime == "" || leaveTime == "") {
                    document.getElementById("error").innerHTML = "Please fill in all fields";
                    return;
                }
                //just submit the form
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        if (this.responseText == "success") {
                            alert(this.responseText);
                            updateTable();
                            document.getElementById("prefSlotUpdateForm").reset();
                        } else {
                            alert(this.responseText);
                            document.getElementById("error").innerHTML = "Error adding slot";
                        }
                    }
                };
                xhttp.open("POST", "/teacher/pastoral.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("addWanted=true&studentID=" + studentID + "&eventID=" + eventID + "&parentID=" + parentID + "&classID=" + classID + "&arriveTime=" + arriveTime + "&leaveTime=" + leaveTime);
            });
            //overite clear button
            document.getElementById("clear").addEventListener("click", function() {
                //set all options to default
                document.getElementById("prefSlotUpdateForm").reset();
            });
            //fill the student select
            var studentSelect = document.getElementById("studentID");
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    studentSelect.innerHTML = this.responseText;
                }
            };
            xhttp.open("POST", "/teacher/pastoral.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("getStudents=true");

            //fill the event select
            var eventSelect = document.getElementById("eventID");
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    eventSelect.innerHTML = this.responseText;
                }
            };
            xhttp.open("POST", "/teacher/pastoral.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("getEvents=true");

            //fill the parent select when student is selected
            studentSelect.addEventListener("change", function() {
                var parentSelect = document.getElementById("parentID");
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        parentSelect.innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "/teacher/pastoral.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("getParents=true&studentID=" + studentSelect.value);
                if (eventSelect.value == "") {
                    return;
                }
                var classSelect = document.getElementById("classID");
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        classSelect.innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "/teacher/pastoral.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("getClasses=true&studentID=" + studentSelect.value + "&eventID=" + eventSelect.value);
            });

            //fill the class select when student and event is selected
            eventSelect.addEventListener("change", function() {
                if (studentSelect.value == "") {
                    return;
                }
                var classSelect = document.getElementById("classID");
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        classSelect.innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "/teacher/pastoral.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("getClasses=true&studentID=" + studentSelect.value + "&eventID=" + eventSelect.value);
                //set arrive and leave times
                var arriveTime = document.getElementById("arriveTime");
                var leaveTime = document.getElementById("leaveTime");
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        var times = this.responseText.split(" ");
                        arriveTime.value = times[1];
                        leaveTime.value = times[3];
                    }
                };
                xhttp.open("POST", "/teacher/pastoral.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("getTimes=true&eventID=" + eventSelect.value);

            });
            //on table row click load student and event into form
            document.getElementById("mainbody").addEventListener("click", function(e) {
                if (e.target.tagName == "TD") {

                    var studentID = e.target.parentElement.children[0].textContent;
                    var eventID = e.target.id;
                    var studentSelect = document.getElementById("studentID");
                    var eventSelect = document.getElementById("eventID");
                    for (var i = 0; i < studentSelect.options.length; i++) {
                        if (studentSelect.options[i].value == studentID) {
                            studentSelect.selectedIndex = i;
                            break;
                        }
                    }
                    for (var i = 0; i < eventSelect.options.length; i++) {
                        if (eventSelect.options[i].value == eventID) {
                            eventSelect.selectedIndex = i;
                            break;
                        }
                    }
                    //load parents and classes
                    var parentSelect = document.getElementById("parentID");
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            parentSelect.innerHTML = this.responseText;
                        }
                    };
                    xhttp.open("POST", "/teacher/pastoral.php", true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send("getParents=true&studentID=" + studentSelect.value);
                    var classSelect = document.getElementById("classID");
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            classSelect.innerHTML = this.responseText;
                        }
                    };
                    xhttp.open("POST", "/teacher/pastoral.php", true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send("getClasses=true&studentID=" + studentSelect.value + "&eventID=" + eventSelect.value);

                    var arriveTime = document.getElementById("arriveTime");
                    var leaveTime = document.getElementById("leaveTime");
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            var times = this.responseText.split(" ");
                            arriveTime.value = times[1];
                            leaveTime.value = times[3];
                        }
                    };
                    xhttp.open("POST", "/teacher/pastoral.php", true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send("getTimes=true&eventID=" + eventSelect.value);
                }
            });
            $('#classID').select2({
                theme: "bootstrap-5",
                placeholder: "Select Class",
                width: "100%",
                allowClear: true,
                multiple: true
            });
        });


        function searchupdate() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("myInput");
            filter = input.value.toUpperCase();
            table = document.querySelector("table");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td");
                for (var j = 0; j < td.length; j++) {
                    var tdata = td[j];
                    if (tdata) {
                        txtValue = tdata.textContent || tdata.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            }
        }


        function updateTable() {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("mainbody").innerHTML = this.responseText;
                }
            };
            xhttp.open("POST", "/teacher/pastoral.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("getTableHTML=true");
        }
    </script>
</head>
<?php require_once './nav.php'; ?>

<body>
    <!-- page will display a list of students who are wanted but have no bookings so the pastoral teacher can contact them and book for them -->

    <div style="display: flex; margin-top: 2pt;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>Pastoral</h1>
                    <input class="form-control" id="myInput" type="text" onkeyup="searchupdate()" placeholder="Search..">
                    <div class="well">
                        <table class="table table-striped table-scroll table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Student ID</th>
                                    <th scope="col">Student Name</th>
                                    <th scope="col">Event</th>
                                    <th scope="col">Wanted Classes</th>
                                </tr>
                            </thead>
                            <tbody id="mainbody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!--add a edit page on the right, non-functional for now-->
        <div class="container" style="max-width: 25%;">
            <div class="col">
                <h1>Quick Add</h1>
                <p class="hide-on-small">If a new parent/carer is required please contact an administrator</p>
                <form id="prefSlotUpdateForm" class="prefSlotForm">
                    <!--prefSlot id, event, parent, teacher, student, classes-->
                    <div class="form-group">
                        <label for="studentID">Student</label>
                        <select class="form-control" id="studentID" name="studentID" required>
                            <option value="">Select a student</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="eventID">Event</label>
                        <select class="form-control" id="eventID" name="eventID" required>
                            <option value="">Select an event</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="parentID">Parent</label>
                        <select class="form-control" id="parentID" name="parentID" required>
                            <option value="">Select a parent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="classID">Class</label>
                        <select class="form-control" id="classID" name="classID" required>
                            <option value="">Select a class</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <!--time picker-->
                        <label for="time">Arrive/Leave Times</label>
                        <input type="time" class="form-control" id="arriveTime" name="arriveTime" required>
                    </div>
                    <div class="form-group">
                        <input type="time" class="form-control" id="leaveTime" name="leaveTime" required>
                    </div>

                    <button type="submit" id="submitFormButton" class="btn btn-primary">Add</button>
                    <!--clear button-->
                    <button type="button" id="clear" class="btn btn-primary">Clear</button>
                    <div id="error"></div>
                </form>
            </div>
        </div>
    </div>
    <!--hidden confirmation popup make it in the middle of the screen grey everything behind-->
    <div id="confirm" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmText">Are you sure you want to update this Slot?</p>
            <button type="button" id="confirmButton" class="btn btn-primary">Confirm</button>
            <button type="button" id="cancelButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
    <div id="confirmDeletion" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmDeletionText">Are you sure you want to delete this Slot?</p>
            <button type="button" id="confirmDeletionButton" class="btn btn-danger">Confirm</button>
            <button type="button" id="cancelDeletionButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
</body>