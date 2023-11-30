<?php
require_once '../utils.php';
//check if the user is logged in
require_once './autils.php';
//
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gettabledata'])) {
    try {
        //if post has event id set then get the event id
        if (isset($_POST['eventid'])) {
            $eventid = $_POST['eventid'];
            //use get prefSlots by event id
            $prefSlots = get_all_PrefSlots_of_event($eventid);
        } else {
            //use get all prefSlots
            $prefSlots = get_all_PrefSlots();
        }
        //if the prefSlots array is not empty
        if ($prefSlots) {
            foreach ($prefSlots as $prefSlots) {
                echo "<tr>";
                echo "<td>" . $prefSlots->getId() . "</td>";
                echo "<td>" . $prefSlots->getEvent() . "</td>";
                echo "<td>" . $prefSlots->getStartTime() . "</td>";
                echo "<td>" . $prefSlots->getEndTime() . "</td>";
                echo "<td style='width: 12%'>" . $prefSlots->getParent() . "</td>";
                echo "<td style='width: 11%'>" . $prefSlots->getStudent() . "</td>";
                echo "<td style='width: 11%'>" . $prefSlots->getTeacher() . "</td>";
                echo "<td style='width: 11%'>" . $prefSlots->getClass() . "</td>";
                echo "<td style='width: 9%'><button type='button' id='delete" . $prefSlots->getId() . "' class='btn btn-danger'>Delete</button></td>";
                echo "</tr>";
            }
        }
    } catch (Exception $e) {
    }
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getStudentSelector'])) {
    $students = get_all_students();
    $output = "";
    foreach ($students as $student) {
        $output = $output . "<option value=" . $student->getId() . ">" . $student->getName() . "</option>";
    }
    echo $output;
    die();
}


if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getEventSelector'])) {
    $events = get_all_events();
    $output = "";
    foreach ($events as $event) {
        $output = $output . "<option value=" . $event->getId() . ">" . $event->getName() . "</option>";
    }
    echo $output;
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getParentSelector'])) {
    $parents = get_all_parents();
    $output = "";
    foreach ($parents as $parent) {
        $output = $output . "<option value=" . $parent->getId() . ">" . $parent->getName() . "</option>";
    }
    echo $output;
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getTeacherSelector'])) {
    $teachers = get_all_teachers();
    $output = "";
    foreach ($teachers as $teacher) {
        $output = $output . "<option value=" . $teacher->getId() . ">" . $teacher->getName() . "</option>";
    }
    echo $output;
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['getClassSelector'])) {
    $classes = get_all_classes();
    $output = "";
    foreach ($classes as $class) {
        $output = $output . "<option value=" . $class->getId() . ">" . $class->getName() . "</option>";
    }
    echo $output;
    die();
}


//get the prefSlot id from the request
//if its a post get the post id if not try to get it from get
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getprefSlotID'])) {
    try {
        $id = $_POST['id'];
        $id = intval($id);
        $prefSlot = new PrefSlot($id);
        $prefSlot->update();
        $response = array(
            "id" => $prefSlot->getId(),
            "startTime" => $prefSlot->getStartTime(),
            "endTime" => $prefSlot->getEndTime(),
            "teacher" => $prefSlot->getTeacher(),
            "event" => $prefSlot->getEvent(),
            "class" => $prefSlot->getClass(),
            "student" => $prefSlot->getStudent(),
            "parent" => $prefSlot->getParent()
        );
        echo json_encode($response);
    } catch (Exception $e) {
        $response = array(
            "error" => "prefSlot does not exist" . $e->getMessage()
        );
        echo json_encode($response);
    }
    die();
}

//if the request is a post and the id is set, check if the prefSlot exists, if it does update it, if not create it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateprefSlot'])) {
    try {
        $id = $_POST['id'];
        $startTime = date("Y-m-d H:i:s", strtotime($_POST['startTime']));
        $endTime = date("Y-m-d H:i:s", strtotime($_POST['endTime']));
        $teacher = $_POST['teacher'];
        $event = $_POST['event'];
        $class = $_POST['class'];
        $student = $_POST['student'];
        $parent = $_POST['parent'];
        //check if the prefSlot exists
        if (prefSlot_exists($id)) {
            //update the prefSlot
            $respon = update_prefSlot($id, $startTime, $endTime, $teacher, $event, $class, $student, $parent);
        } else {
            //create the prefSlot
            $respon = create_prefSlot($startTime, $endTime, $teacher, $event, $class, $student, $parent);
        }
        if ($respon) {
            $response = array(
                "success" => "prefSlot updated successfully"
            );
            echo json_encode($response);
            die();
        } else {
            $response = array(
                "error" => "prefSlot could not be updated"
            );
            echo json_encode($response);
            die();
        }
    } catch (Exception $e) {
        $response = array(
            "error" => "prefSlot could not be updated"
        );
        echo json_encode($response);
        die();
    }
}
//if the request is a post and the id is set, check if the prefSlot exists, if it does delete it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteprefSlot'])) {
    $id = $_POST['id'];
    //check if the prefSlot exists
    try {
        if (prefSlot_exists($id)) {
            //delete the prefSlot
            $respon = delete_prefSlot($id);
        } else {
            $response = array(
                "error" => "prefSlot does not exist"
            );
            echo json_encode($response);
            die();
        }
        if ($respon) {
            $response = array(
                "success" => "prefSlot deleted successfully"
            );
            echo json_encode($response);
            die();
        } else {
            $response = array(
                "error" => "prefSlot does not exist"
            );
            echo json_encode($response);
            die();
        }
    } catch (Exception $e) {
        $response = array(
            "error" => "prefSlot does not exist"
        );
        echo json_encode($response);
        die();
    }
}
//if its a post with no data just die :)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    die();
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
            let requests = [
                $.ajax({
                    type: "POST",
                    url: "/admin/bookings.php",
                    data: {
                        getStudentSelector: true
                    }
                }),
                $.ajax({
                    type: "POST",
                    url: "/admin/bookings.php",
                    data: {
                        getParentSelector: true
                    }
                }),
                $.ajax({
                    type: "POST",
                    url: "/admin/bookings.php",
                    data: {
                        getEventSelector: true
                    }
                }),
                $.ajax({
                    type: "POST",
                    url: "/admin/bookings.php",
                    data: {
                        getTeacherSelector: true
                    }
                }),
                $.ajax({
                    type: "POST",
                    url: "/admin/bookings.php",
                    data: {
                        getClassSelector: true
                    }

                }),
                $.ajax({
                type: "POST",
                url: "/admin/bookings.php",
                data: {
                    gettabledata: true
                }})
            ];

            Promise.all(requests).then(function(responses) {
                $('#prefSlotStudent').html(responses[0]);
                $('#prefSlotParent').html(responses[1]);
                $('#prefSlotEvent').html(responses[2]);
                $('#prefSlotTeacher').html(responses[3]);
                $('#prefSlotClass').html(responses[4]);
                $('#mainbody').html(responses[5]);
            });
            //add the options to account selector and student selector
            //make the multiselects searchable and 

            $('#prefSlotEvent').select2({
                theme: "bootstrap-5",
                placeholder: "Select an event",
                allowClear: true,
                width: '100%'
            });
            $('#prefSlotParent').select2({
                theme: "bootstrap-5",
                placeholder: "Select a parent",
                allowClear: true,
                width: '100%'
            });
            $('#prefSlotTeacher').select2({
                theme: "bootstrap-5",
                placeholder: "Select a teacher",
                allowClear: true,
                width: '100%'
            });
            $('#prefSlotStudent').select2({
                theme: "bootstrap-5",
                placeholder: "Select a student",
                allowClear: true,
                width: '100%'
            });
            $('#prefSlotClass').select2({
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
                url: "/admin/bookings.php",
                data: {
                    gettabledata: true
                },
                success: function(data) {
                    $('#mainbody').html(data);
                }
            });
        }
        //when a prefSlot is selected from the table, update the edit form to show the prefSlot's details
        $(document).on('click', 'tr', function() {
            if ($(this).find("th").length > 0) {
                return;
            }
            //get the prefSlot id from the row
            var prefSlotID = $(this).find("td:first").html();
            //print the id to the console
            console.log(prefSlotID);
            $(this).addClass('table-danger').siblings().removeClass('table-danger');
            //get the prefSlot object from the database
            $.ajax({
                type: "POST",
                url: "/admin/bookings.php",
                data: {
                    getprefSlotID: true,
                    id: prefSlotID
                },
                success: function(data) {
                    //parse the json response
                    var prefSlot = JSON.parse(data);
                    //set the values of the edit form to the prefSlot's details
                    $('#prefSlotID').val(prefSlot.id);
                    $('#prefSlotStartTime').val(prefSlot.startTime);
                    $('#prefSlotEndTime').val(prefSlot.endTime);
                    $('#prefSlotTeacher').val(prefSlot.teacher);
                    $('#prefSlotEvent').val(prefSlot.event);
                    $('#prefSlotClass').val(prefSlot.class);
                    $('#prefSlotStudent').val(prefSlot.student);
                    $('#prefSlotParent').val(prefSlot.parent);
                    //update the selectors
                    $('#prefSlotEvent').trigger('change');
                    $('#prefSlotParent').trigger('change');
                    $('#prefSlotTeacher').trigger('change');
                    $('#prefSlotStudent').trigger('change');
                    $('#prefSlotClass').trigger('change');

                    //change the text in the submit button to update
                    $('#submitFormButton').text("Update");
                }
            });
        });
        //clear button to stop selecting a prefSlot
        $(document).on('click', '#clear', function() {
            //remove the selected class from the table
            $('tr').removeClass('table-danger');
            //clear the edit form
            $('#prefSlotID').val("");
            $('#prefSlotStartTime').val("");
            $('#prefSlotEndTime').val("");
            $('#prefSlotTeacher').val("");
            $('#prefSlotEvent').val("");
            $('#prefSlotClass').val("");
            $('#prefSlotStudent').val("");
            $('#prefSlotParent').val("");
            //update the selectors
            $('#prefSlotEvent').trigger('change');
            $('#prefSlotParent').trigger('change');
            $('#prefSlotTeacher').trigger('change');
            $('#prefSlotStudent').trigger('change');
            $('#prefSlotClass').trigger('change');


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
        $(document).on('submit', '#prefSlotUpdateForm', function(e) {
            e.prprefSlotDefault();
        });
        $(document).on('click', '#submitFormButton', function() {
            //if the button says add
            if ($(this).text() == "Add") {
                //submit the form using xhttprequest
                var $id = $('#prefSlotID').val();
                var $startTime = $('#prefSlotStartTime').val();
                var $endTime = $('#prefSlotEndTime').val();
                var $teacher = $('#prefSlotTeacher').val();
                var $event = $('#prefSlotEvent').val();
                var $class = $('#prefSlotClass').val();
                var $student = $('#prefSlotStudent').val();
                var $parent = $('#prefSlotParent').val();

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
                xhttp.open("POST", "/admin/bookings.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("updateprefSlot=true&id=" + $id + "&startTime=" + $startTime + "&endTime=" + $endTime + "&teacher=" + $teacher + "&event=" + $event + "&class=" + $class + "&student=" + $student + "&parent=" + $parent);
            } else {
                //if the button says update
                //show the confirmation popup
                $('#confirm').show();
                //get the prefSlot id from the form
                var prefSlotID = $('#prefSlotID').val();
                //print the id to the console
                console.log(prefSlotID);
                //get the prefSlot object from the database
                $.ajax({
                    type: "POST",
                    url: "/admin/bookings.php",
                    data: {
                        getprefSlotID: true,
                        id: prefSlotID
                    },
                    success: function(data) {
                        //parse the json response
                        var prefSlot = JSON.parse(data);
                        //set the text of the confirmation popup
                        $('#confirmText').text("Are you sure you want to update " + prefSlot.name + "?");
                    }
                });
            }
        });
        //on click of the confirm button
        $(document).on('click', '#confirmButton', function() {
            //get the prefSlot id from the form
            var $id = $('#prefSlotID').val();
            var $startTime = $('#prefSlotStartTime').val();
            var $endTime = $('#prefSlotEndTime').val();
            var $teacher = $('#prefSlotTeacher').val();
            var $event = $('#prefSlotEvent').val();
            var $class = $('#prefSlotClass').val();
            var $student = $('#prefSlotStudent').val();
            var $parent = $('#prefSlotParent').val();

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
            xhttp.open("POST", "/admin/bookings.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("updateprefSlot=true&id=" + $id + "&startTime=" + $startTime + "&endTime=" + $endTime + "&teacher=" + $teacher + "&event=" + $event + "&class=" + $class + "&student=" + $student + "&parent=" + $parent);
        });
        //on click of the cancel button
        $(document).on('click', '#cancelButton', function() {
            //hide the confirmation popup
            $('#confirm').hide();
        });
        $(document).on('click', 'button[id^="delete"]', function() {
            //get the teacher id from the button id
            var prefSlotID = $(this).attr('id').replace('delete', '');
            //print the id to the console
            console.log(prefSlotID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/bookings.php",
                data: {
                    getprefSlotID: true,
                    id: prefSlotID
                },
                success: function(data) {
                    //parse the json response
                    var prefSlot = JSON.parse(data);
                    //set the text of the confirmation popup
                    $('#confirmDeletionText').text("Are you sure you want to delete " + prefSlot.name + "?");
                }
            });
            //show the confirmation popup
            $('#confirmDeletion').show();
        });
        //on click of the confirm button
        $(document).on('click', '#confirmDeletionButton', function() {
            //get the teacher id from the form
            var prefSlotID = $('#prefSlotID').val();
            //print the id to the console
            console.log(prefSlotID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/bookings.php",
                data: {
                    deleteprefSlot: true,
                    id: prefSlotID
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
    <style type="text/css">
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

        .prefSlotForm input {
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
                    <h1>preffered Slots</h1>
                    <p>Here you can view all the preffered SLots in the school</p>
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
                <form id="prefSlotUpdateForm" class="prefSlotForm">
                    <!--prefSlot id, event, parent, teacher, student, class-->
                    <input class="form-control" id="prefSlotID" name="prefSlotID" placeholder="ID">
                    <!--selectors for event, parent, teacher, student, class-->
                    <select class="form-select" id="prefSlotEvent" name="prefSlotEvent">
                    </select>
                    <select class="form-select" id="prefSlotParent" name="prefSlotParent">
                    </select>
                    <select class="form-select" id="prefSlotTeacher" name="prefSlotTeacher">
                    </select>
                    <select class="form-select" id="prefSlotStudent" name="prefSlotStudent">
                    </select>
                    <select class="form-select" id="prefSlotClass" name="prefSlotClass">
                    </select>
                    <!--date selector-->
                    <input class="form-control" id="prefSlotStartTime" name="prefSlotStartTime" placeholder="Start Time">
                    <input class="form-control" id="prefSlotEndTime" name="prefSlotEndTime" placeholder="End Time">

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