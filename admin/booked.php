<?php
require_once '../utils.php';
require_once '../classdefs.php';
//check if the user is logged in
require_once './autils.php';
//
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gettabledata'])) {
    echo "a";
    //if post has event id set then get the event id
    if (isset($_POST['eventid'])) {
        $eventid = $_POST['eventid'];
        //use get timess by event id
        $timess = get_all_Slots_of_event($eventid);
        echo count($timess);
    } else {
        //use get all timess
        $timess = get_all_Slots();
    }
    //if the timess array is not empty
    if ($timess) {
        foreach ($timess as $timess) {
            echo "<tr>";
            echo "<td>" . $timess->getId() . "</td>";
            echo "<td>" . $timess->getEventID() . "</td>";
            echo "<td>" . $timess->getStartTime() . "</td>";
            echo "<td>" . $timess->getDuration() . "</td>";
            echo "<td style='width: 12%'>" . $timess->getParent() . "</td>";
            echo "<td style='width: 11%'>" . $timess->getStudent() . "</td>";
            echo "<td style='width: 11%'>" . $timess->getTeacher() . "</td>";
            echo "<td style='width: 11%'>" . $timess->getClass() . "</td>";
            echo "<td style='width: 9%'><button type='button' id='delete" . $timess->getId() . "' class='btn btn-danger'>Delete</button></td>";
            echo "</tr>";
        }
    }

    exit();
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getStudentSelector'])) {
    if (isset($_POST['event'])) {
        $event = $_POST['event'];
        $eventc = new Event($event);
        $eventc->update();
        $students = get_all_students_in_year($eventc->getYear());
        $output = "";
        foreach ($students as $student) {
            $output = $output . "<option value=" . $student->getId() . ">" . $student->getName() . "</option>";
        }
        echo $output;
        exit();
    } else {
        $students = get_all_students();
        $output = "";
        foreach ($students as $student) {
            $output = $output . "<option value=" . $student->getId() . ">" . $student->getName() . "</option>";
        }
        echo $output;
        exit();
    }
}


if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getEventSelector'])) {
    $events = get_all_events();
    $output = "";
    foreach ($events as $event) {
        $output = $output . "<option value=" . $event->getId() . ">" . $event->getName() . "</option>";
    }
    echo $output;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getParentSelector'])) {
    $parents = get_all_parents();
    $output = "";
    foreach ($parents as $parent) {
        $output = $output . "<option value=" . $parent->getId() . ">" . $parent->getName() . "</option>";
    }
    echo $output;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getTeacherSelector'])) {
    $teachers = get_all_teachers();
    $output = "";
    foreach ($teachers as $teacher) {
        $output = $output . "<option value=" . $teacher->getId() . ">" . $teacher->getName() . "</option>";
    }
    echo $output;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getClassSelector'])) {
    $classes = get_all_classes();
    $output = "";
    foreach ($classes as $class) {
        $output = $output . "<option value=" . $class->getId() . ">" . $class->getName() . "</option>";
    }
    echo $output;
    exit();
}


//get the times id from the request
//if its a post get the post id if not try to get it from get
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gettimesID'])) {
    try {
        $id = $_POST['id'];
        $id = intval($id);
        $times = new PrefSlot($id);
        $times->update();
        $response = array(
            "id" => $times->getId(),
            "startTime" => $times->getStartTime(),
            "endTime" => $times->getEndTime(),
            "teacher" => $times->getTeacher(),
            "event" => $times->getEvent(),
            "class" => $times->getClass(),
            "student" => $times->getStudent(),
            "parent" => $times->getParent()
        );
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array(
            "error" => "times does not exist" . $e->getMessage()
        );
        echo json_encode($response);
    }
    exit();
}

//if the request is a post and the id is set, check if the times exists, if it does update it, if not create it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updatetimes'])) {
    try {
        $id = $_POST['id'];
        $startTime = date("Y-m-d H:i:s", strtotime($_POST['startTime']));
        $endTime = date("Y-m-d H:i:s", strtotime($_POST['endTime']));
        $teacher = $_POST['teacher'];
        $event = $_POST['event'];
        $class = $_POST['class'];
        $student = $_POST['student'];
        $parent = $_POST['parent'];
        //check if the times exists
        if (times_exists($id)) {
            //update the times
            $respon = update_times($id, $startTime, $endTime, $teacher, $event, $class, $student, $parent);
        } else {
            //create the times
            $respon = create_times($startTime, $endTime, $teacher, $event, $class, $student, $parent);
        }
        if ($respon) {
            $response = array(
                "success" => "times updated successfully"
            );
            echo json_encode($response);
            exit();
        } else {
            $response = array(
                "error" => "times could not be updated"
            );
            echo json_encode($response);
            exit();
        }
    } catch (Exception $e) {
        $response = array(
            "error" => "times could not be updated",
            "error2" => $e->getMessage()
        );
        echo json_encode($response);
        exit();
    }
}
//if the request is a post and the id is set, check if the times exists, if it does delete it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletetimes'])) {
    $id = $_POST['id'];
    //check if the times exists
    try {
        if (times_exists($id)) {
            //delete the times
            $respon = delete_times($id);
        } else {
            $response = array(
                "error" => "times does not exist"
            );
            echo json_encode($response);
            exit();
        }
        if ($respon) {
            $response = array(
                "success" => "times deleted successfully"
            );
            echo json_encode($response);
            exit();
        } else {
            $response = array(
                "error" => "times does not exist"
            );
            echo json_encode($response);
            exit();
        }
    } catch (Exception $e) {
        $response = array(
            "error" => "times does not exist"
        );
        echo json_encode($response);
        exit();
    }
}
//if its a post with no data just die :)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <title>Admin</title>
    <script>
        function searchupdate() {
            // Declare variables
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("myInput");
            filter = input.value.toUpperCase();
            table = document.getElementsByTagName("table")[0];
            tr = table.getElementsByTagName("tr");
            // Loop through all table rows, and hide those who don't match the search query
            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) == -1) {
                        tr[i].style.display = "none";
                    } else {
                        tr[i].style.display = "";
                    }
                }
            }
        }
        //on document load
        $(document).ready(function() {
            //set the data in the select
            var event = -1;
            try {
                const searchParams = new URLSearchParams(window.location.search);
                if (searchParams.has('event')) {
                    event = searchParams.get('event');
                }
            } catch (error) {
                alert(error);
            }
            let requests = [];
            try {
                requests = [
                    $.ajax({
                        type: "POST",
                        url: "/admin/booked.php",
                        data: {
                            getStudentSelector: true,
                            event: event
                        }
                    }),
                    $.ajax({
                        type: "POST",
                        url: "/admin/booked.php",
                        data: {
                            getParentSelector: true
                        }
                    }),
                    $.ajax({
                        type: "POST",
                        url: "/admin/booked.php",
                        data: {
                            getEventSelector: true
                        }
                    }),
                    $.ajax({
                        type: "POST",
                        url: "/admin/booked.php",
                        data: {
                            getTeacherSelector: true
                        }
                    }),
                    $.ajax({
                        type: "POST",
                        url: "/admin/booked.php",
                        data: {
                            getClassSelector: true,
                            event: event
                        }

                    })
                ];
            } catch (error) {
                alert(error);
            }
            try {
                Promise.all(requests).then(function(responses) {
                    $('#timesStudent').html(responses[0]);
                    $('#timesParent').html(responses[1]);
                    $('#timesEvent').html(responses[2]);
                    $('#timesTeacher').html(responses[3]);
                    $('#timesClass').html(responses[4]);
                    $('#mainbody').html(responses[5]);
                });
            } catch (error) {
                alert(error);
            }
            updateTable();
            //add the options to account selector and student selector
            //make the multiselects searchable and 

            $('#timesEvent').select2({
                theme: "bootstrap-5",
                placeholder: "Select an event",
                allowClear: true,
                width: '100%'
            });
            $('#timesParent').select2({
                theme: "bootstrap-5",
                placeholder: "Select a parent",
                allowClear: true,
                width: '100%'
            });
            $('#timesTeacher').select2({
                theme: "bootstrap-5",
                placeholder: "Select a teacher",
                allowClear: true,
                width: '100%'
            });
            $('#timesStudent').select2({
                theme: "bootstrap-5",
                placeholder: "Select a student",
                allowClear: true,
                width: '100%'
            });
            $('#timesClass').select2({
                theme: "bootstrap-5",
                placeholder: "Select a class",
                allowClear: true,
                width: '100%'
            });
            //clear the edit form
        });

        function updateTable() {
            $.ajax({
                type: "POST",
                url: "/admin/booked.php",
                data: {
                    gettabledata: true
                },
                success: function(data) {
                    $('#mainbody').html(data);
                }
            });
        }
        //when a times is selected from the table, update the edit form to show the times's details
        $(document).on('click', 'tr', function() {
            if ($(this).find("th").length > 0) {
                return;
            }
            //get the times id from the row
            var timesID = $(this).find("td:first").html();
            //print the id to the console
            console.log(timesID);
            $(this).addClass('table-danger').siblings().removeClass('table-danger');
            //get the times object from the database
            $.ajax({
                type: "POST",
                url: "/admin/booked.php",
                data: {
                    gettimesID: true,
                    id: timesID
                },
                success: function(data) {
                    //parse the json response
                    var times = JSON.parse(data);
                    //set the values of the edit form to the times's details
                    $('#timesID').val(times.id);
                    $('#timesStartTime').val(times.startTime);
                    $('#timesEndTime').val(times.endTime);
                    $('#timesTeacher').val(times.teacher);
                    $('#timesEvent').val(times.event);
                    $('#timesClass').val(times.class);
                    $('#timesStudent').val(times.student);
                    $('#timesParent').val(times.parent);
                    //update the selectors
                    $('#timesEvent').trigger('change');
                    $('#timesParent').trigger('change');
                    $('#timesTeacher').trigger('change');
                    $('#timesStudent').trigger('change');
                    $('#timesClass').trigger('change');

                    //change the text in the submit button to update
                    $('#submitFormButton').text("Update");
                }
            });
        });
        //clear button to stop selecting a times
        $(document).on('click', '#clear', function() {
            //remove the selected class from the table
            $('tr').removeClass('table-danger');
            const searchParams = new URLSearchParams(window.location.search);
            //clear the edit form
            $('#timesID').val("");
            $('#timesStartTime').val("");
            $('#timesEndTime').val("");
            $('#timesTeacher').val("");
            if (searchParams.has('event')) {
                $('#timesEvent').val(searchParams.get('event'));
            } else {
                $('#timesEvent').val("");
            }
            $('#timesClass').val("");
            $('#timesStudent').val("");
            $('#timesParent').val("");
            //update the selectors
            $('#timesEvent').trigger('change');
            $('#timesParent').trigger('change');
            $('#timesTeacher').trigger('change');
            $('#timesStudent').trigger('change');
            $('#timesClass').trigger('change');


            //change the text in the submit button to add
            $('#submitFormButton').text("Add");
        });
        //on ready clear the edit form
        $(document).ready(function() {
            $('#clear').click();
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            $('.table-scroll tbody').css('height', height);
        });
        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - $('.table-scroll tbody').offset().top;
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
        //on submit do nothing, let the button press function handle it
        $(document).on('submit', '#timesUpdateForm', function(e) {
            e.preventDefault();
        });
        $(document).on('click', '#submitFormButton', function() {
            //if the button says add
            if ($(this).text() == "Add") {
                //submit the form using xhttprequest
                var $id = $('#timesID').val();
                var $startTime = $('#timesStartTime').val();
                var $endTime = $('#timesEndTime').val();
                var $teacher = $('#timesTeacher').val();
                var $event = $('#timesEvent').val();
                var $class = $('#timesClass').val();
                var $student = $('#timesStudent').val();
                var $parent = $('#timesParent').val();

                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    //if the request is complete
                    if (this.readyState == 4) {
                        //if the request was successful
                        if (this.status == 200) {
                            //if the request was successful then we can redirect the user to the index page
                            var response = JSON.parse(this.responseText);
                            if (response.error) {
                                $('#error').html(response.error);
                            } else {
                                if (response.success) {
                                    $('#error').html(response.success);
                                    $('#clear').click();
                                    updateTable();
                                }
                            }
                        } else {
                            //if the request was not successful then we can display an error message
                            alert(this.responseText);
                        }
                    }
                };
                xhttp.open("POST", "/admin/booked.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("updatetimes=true&id=" + $id + "&startTime=" + $startTime + "&endTime=" + $endTime + "&teacher=" + $teacher + "&event=" + $event + "&class=" + $class + "&student=" + $student + "&parent=" + $parent);
            } else {
                //if the button says update
                //show the confirmation popup
                $('#confirm').show();
                //get the times id from the form
                var timesID = $('#timesID').val();
                //print the id to the console
                console.log(timesID);
                //get the times object from the database
                $.ajax({
                    type: "POST",
                    url: "/admin/booked.php",
                    data: {
                        gettimesID: true,
                        id: timesID
                    },
                    success: function(data) {
                        //parse the json response
                        var times = JSON.parse(data);
                        //set the text of the confirmation popup
                        $('#confirmText').text("Are you sure you want to update " + times.name + "?");
                    }
                });
            }
        });
        //on click of the confirm button
        $(document).on('click', '#confirmButton', function() {
            //get the times id from the form
            var $id = $('#timesID').val();
            var $startTime = $('#timesStartTime').val();
            var $endTime = $('#timesEndTime').val();
            var $teacher = $('#timesTeacher').val();
            var $event = $('#timesEvent').val();
            var $class = $('#timesClass').val();
            var $student = $('#timesStudent').val();
            var $parent = $('#timesParent').val();

            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                //if the request is complete
                if (this.readyState == 4) {
                    //if the request was successful
                    if (this.status == 200) {
                        //if the request was successful then we can redirect the user to the index page
                        var response = JSON.parse(this.responseText);
                        if (response.error) {
                            $('#error').html(response.error);
                        } else {
                            if (response.success) {
                                $('#error').html(response.success);
                                $('#clear').click();
                                $('#confirm').hide();
                                updateTable();
                            }
                        }
                    } else {
                        //if the request was not successful then we can display an error message
                        alert(this.responseText);
                    }
                }
            };
            xhttp.open("POST", "/admin/booked.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("updatetimes=true&id=" + $id + "&startTime=" + $startTime + "&endTime=" + $endTime + "&teacher=" + $teacher + "&event=" + $event + "&class=" + $class + "&student=" + $student + "&parent=" + $parent);
        });
        //on click of the cancel button
        $(document).on('click', '#cancelButton', function() {
            //hide the confirmation popup
            $('#confirm').hide();
        });
        $(document).on('click', 'button[id^="delete"]', function() {
            //get the teacher id from the button id
            var timesID = $(this).attr('id').replace('delete', '');
            //print the id to the console
            console.log(timesID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/booked.php",
                data: {
                    gettimesID: true,
                    id: timesID
                },
                success: function(data) {
                    //parse the json response
                    var times = JSON.parse(data);
                    //set the text of the confirmation popup
                    $('#confirmDeletionText').text("Are you sure you want to delete " + times.name + "?");
                }
            });
            //show the confirmation popup
            $('#confirmDeletion').show();
        });
        //on click of the confirm button
        $(document).on('click', '#confirmDeletionButton', function() {
            //get the teacher id from the form
            var timesID = $('#timesID').val();
            //print the id to the console
            console.log(timesID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/booked.php",
                data: {
                    deletetimes: true,
                    id: timesID
                },
                success: function(data) {
                    //parse the json response
                    var response = JSON.parse(data);

                    //set the text of the confirmation popup
                    if (response.error) {
                        $('#error').html(response.error);
                    } else {
                        if (response.success) {
                            $('#error').html(response.success);
                            $('#clear').click();
                            $('#confirmDeletion').hide();
                            updateTable();
                        }
                    }
                }
            });
        });
        //on click of the cancel button
        $(document).on('click', '#cancelDeletionButton', function() {
            //hide the confirmation popup
            $('#confirmDeletion').hide();
        });
    </script>
    <style>
        .well {
            background: none;
            height: 320px;
        }

        .table-scroll tbody {
            overflow-y: scroll;
            height: 200px;
        }

        .table-scroll tr {
            table-layout: fixed;
            display: inline-table;
        }

        .timesForm input {
            margin: 2px;
        }
    </style>
</head>
<?php include_once '../admin/nav.php'; ?>
<!--searchable table of -->

<body>
    <div style="display: flex; margin-top: 2pt;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>Slots</h1>
                    <p>Here you can view all the slots</p>
                    <input class="form-control" id="myInput" type="text" onkeyup="searchupdate()" placeholder="Search..">
                    <div class="well">
                        <table class="table table-striped table-scroll table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Slot ID</th>
                                    <th scope="col">Event ID</th>
                                    <th scope="col">Start Time</th>
                                    <th scope="col">End Time</th>
                                    <th scope="col">Parent ID</th>
                                    <th scope="col">Student ID</th>
                                    <th scope="col">Teacher ID</th>
                                    <th scope="col">Class Name</th>
                                    <th scope="col" style="width: 10%;">Delete</th>
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
                <h1>Edit</h1>
                <form id="timesUpdateForm" class="timesForm">
                    <!--times id, event, parent, teacher, student, class-->
                    <input class="form-control" id="timesID" name="timesID" placeholder="ID">
                    <!--selectors for event, parent, teacher, student, class-->
                    <select class="form-select" id="timesEvent" name="timesEvent">
                    </select>
                    <select class="form-select" id="timesParent" name="timesParent">
                    </select>
                    <select class="form-select" id="timesTeacher" name="timesTeacher">
                    </select>
                    <select class="form-select" id="timesStudent" name="timesStudent">
                    </select>
                    <select class="form-select" id="timesClass" name="timesClass">
                    </select>
                    <!--date selector-->
                    <input class="form-control" id="timesStartTime" name="timesStartTime" placeholder="Start Time">
                    <input class="form-control" id="timesEndTime" name="timesEndTime" placeholder="End Time">

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