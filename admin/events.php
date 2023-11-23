<?php
require_once '../utils.php';
//check if the user is logged in
require_once './autils.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gettabledata'])) {
    try {
        $events = get_all_events();
        foreach ($events as $Event) {
            echo "<tr id=EventRow", $Event->getID(), ">";
            echo "<td>", $Event->getID(), "</td>";
            echo "<td>", $Event->getName(), "</td>";
            echo "<td>", format_date($Event->getStartTime()), "</td>";
            echo "<td>", format_date($Event->getEndTime()), "</td>";
            echo "<td>", format_date($Event->getOpenTime()), "</td>";
            echo "<td  style='width: 10%;'>", $Event->getSlotDuration(), "</td>";
            echo "<td style='width: 10%;'>", $Event->getYear(), "</td>";
            //link to bookings page with event id
            echo "<td style='width: 10%;'><a href='/admin/bookings.php?event=", $Event->getID(), "'>View</a></td>";
            echo "<td style='width: 8.8%;'><button type='button' class='btn btn-danger' id='delete", $Event->getID(), "'>Delete</button></td>";
            echo "</tr>";
        }
    } catch (Exception $e) {
    }
    die();
}

//get the Event id from the request
//if its a post get the post id if not try to get it from get
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getEventID'])) {
    try {
        $id = $_POST['id'];
        $id = intval($id);
        //get the Event object from the database
        $Event = new Event($id);
        $Event->update();
        $Event_id = $Event->getID();
        $Event_name = $Event->getName();
        $Event_startTime = $Event->getStartTime();
        $Event_endTime = $Event->getEndTime();
        $Event_openTime = $Event->getOpenTime();
        $Event_slotDuration = $Event->getSlotDuration();
        $Event_year = $Event->getYear();
        //format the json response
        $response = array(
            "id" => $Event_id,
            "name" => $Event_name,
            "startTime" => $Event_startTime,
            "endTime" => $Event_endTime,
            "openTime" => $Event_openTime,
            "slotDuration" => $Event_slotDuration,
            "year" => $Event_year
        );
        echo json_encode($response);
    } catch (Exception $e) {
    }
    die();
}

//if the request is a post and the id is set, check if the Event exists, if it does update it, if not create it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateEvent'])) {
    try {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $startTime = date('Y-m-d H:i:s', strtotime($_POST['startTime']));
        $endTime = date('Y-m-d H:i:s', strtotime($_POST['endTime']));
        $openTime = date('Y-m-d H:i:s', strtotime($_POST['openTime']));
        $slotDuration = $_POST['slotDuration'];
        //date times are in the format yyyy-mm-ddTHH:mm
        //convert to a format sql can understand

        $year = $_POST['year'];
        //check if the Event exists
        if (event_exists($id)) {
            //update the Event
            $respon = update_event($id, $name, $startTime, $endTime, $openTime, $slotDuration, $year);
        } else {
            //create the Event
            $respon = create_event($name, $startTime, $endTime, $openTime, $slotDuration, $year);
        }
        if ($respon) {
            $response = array(
                "success" => "Event updated successfully"
            );
            echo json_encode($response);
            die();
        } else {
            $response = array(
                "error" => "Event could not be updated"
            );
            echo json_encode($response);
            die();
        }
    } catch (Exception $e) {
        $response = array(
            "error" => "Event could not be updated"
        );
        echo json_encode($response);
        die();
    }
}
//if the request is a post and the id is set, check if the Event exists, if it does delete it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteEvent'])) {
    $id = $_POST['id'];
    //check if the Event exists
    try {
        if (event_exists($id)) {
            //delete the Event
            $respon = delete_event($id);
        } else {
            $response = array(
                "error" => "Event does not exist"
            );
            echo json_encode($response);
            die();
        }
        if ($respon) {
            $response = array(
                "success" => "Event deleted successfully"
            );
            echo json_encode($response);
            die();
        } else {
            $response = array(
                "error" => "Event does not exist"
            );
            echo json_encode($response);
            die();
        }
    } catch (Exception $e) {
        $response = array(
            "error" => "Event does not exist"
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
            //add the options to account selector and student selector
            //make the multiselects searchable and 
        });

        function updateTable() {
            $.ajax({
                type: "POST",
                url: "/admin/events.php",
                data: {
                    gettabledata: true
                },
                success: function(data) {
                    $('#mainbody').html(data);
                }
            });
        }
        //when a Event is selected from the table, update the edit form to show the Event's details
        $(document).on('click', 'tr', function() {
            if ($(this).find("th").length > 0) {
                return;
            }
            //get the Event id from the row
            var EventID = $(this).find("td:first").html();
            //print the id to the console
            console.log(EventID);
            $(this).addClass('table-danger').siblings().removeClass('table-danger');
            //get the Event object from the database
            $.ajax({
                type: "POST",
                url: "/admin/events.php",
                data: {
                    getEventID: true,
                    id: EventID
                },
                success: function(data) {
                    //parse the json response
                    var Event = JSON.parse(data);
                    //set the values of the edit form to the Event's details
                    $('#EventID').val(Event.id);
                    $('#EventName').val(Event.name);
                    $('#EventStartTime').val(Event.startTime);
                    $('#EventEndTime').val(Event.endTime);
                    $('#EventOpenTime').val(Event.openTime);
                    $('#EventSlotDuration').val(Event.slotDuration);
                    $('#EventYear').val(Event.year);
                    //refresh the selects
                    $('#AccountSelector').trigger('change');
                    $('#StudentSelector').trigger('change');
                    //change the text in the submit button to update
                    $('#submitFormButton').text("Update");
                }
            });
        });
        //clear button to stop selecting a Event
        $(document).on('click', '#clear', function() {
            //remove the selected class from the table
            $('tr').removeClass('table-danger');
            //clear the edit form
            $('#EventID').val("");
            $('#EventName').val("");
            $('#EventStartTime').val("");
            $('#EventEndTime').val("");
            $('#EventOpenTime').val("");
            $('#EventSlotDuration').val("");
            $('#EventYear').val("");

            //change the text in the submit button to add
            $('#submitFormButton').text("Add");
        });
        //on ready clear the edit form
        $(document).ready(function() {
            $('#clear').click();
            var height = $(window).height() - 240;
            $('.table-scroll tbody').css('height', height);
            updateTable();
        });
        $(window).resize(function() {
            //adjust the height of the table to fit the screen
            var height = $(window).height() - 240;
            //just tbody
            $('.table-scroll tbody').css('height', height);
        });
        //on submit do nothing, let the button press function handle it
        $(document).on('submit', '#EventUpdateForm', function(e) {
            e.preventDefault();
        });
        $(document).on('click', '#submitFormButton', function() {
            //if the button says add
            if ($(this).text() == "Add") {
                //submit the form using xhttprequest
                $id = $('#EventID').val();
                $name = $('#EventName').val();
                $startTime = $('#EventStartTime').val();
                $endTime = $('#EventEndTime').val();
                $openTime = $('#EventOpenTime').val();
                $slotDuration = $('#EventSlotDuration').val();
                $year = $('#EventYear').val();

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
                xhttp.open("POST", "/admin/events.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("updateEvent=true" + "&id=" + $id + "&name=" + $name + "&startTime=" + $startTime + "&endTime=" + $endTime + "&openTime=" + $openTime + "&slotDuration=" + $slotDuration + "&year=" + $year);
            } else {
                //if the button says update
                //show the confirmation popup
                $('#confirm').show();
                //get the Event id from the form
                var EventID = $('#EventID').val();
                //print the id to the console
                console.log(EventID);
                //get the Event object from the database
                $.ajax({
                    type: "POST",
                    url: "/admin/events.php",
                    data: {
                        getEventID: true,
                        id: EventID
                    },
                    success: function(data) {
                        //parse the json response
                        var Event = JSON.parse(data);
                        //set the text of the confirmation popup
                        $('#confirmText').text("Are you sure you want to update " + Event.name + "?");
                    }
                });
            }
        });
        //on click of the confirm button
        $(document).on('click', '#confirmButton', function() {
            //submit the form
            $id = $('#EventID').val();
            $name = $('#EventName').val();
            $startTime = $('#EventStartTime').val();
            $endTime = $('#EventEndTime').val();
            $openTime = $('#EventOpenTime').val();
            $slotDuration = $('#EventSlotDuration').val();
            $year = $('#EventYear').val();

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
            xhttp.open("POST", "/admin/events.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("updateEvent=true" + "&id=" + $id + "&name=" + $name + "&startTime=" + $startTime + "&endTime=" + $endTime + "&openTime=" + $openTime + "&slotDuration=" + $slotDuration + "&year=" + $year);
        });
        //on click of the cancel button
        $(document).on('click', '#cancelButton', function() {
            //hide the confirmation popup
            $('#confirm').hide();
        });
        $(document).on('click', 'button[id^="delete"]', function() {
            //get the teacher id from the button id
            var EventID = $(this).attr('id').replace('delete', '');
            //print the id to the console
            console.log(EventID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/events.php",
                data: {
                    getEventID: true,
                    id: EventID
                },
                success: function(data) {
                    //parse the json response
                    var Event = JSON.parse(data);
                    //set the text of the confirmation popup
                    $('#confirmDeletionText').text("Are you sure you want to delete " + Event.name + "?");
                }
            });
            //show the confirmation popup
            $('#confirmDeletion').show();
        });
        //on click of the confirm button
        $(document).on('click', '#confirmDeletionButton', function() {
            //get the teacher id from the form
            var EventID = $('#EventID').val();
            //print the id to the console
            console.log(EventID);
            //get the teacher object from the database
            $.ajax({
                type: "POST",
                url: "/admin/events.php",
                data: {
                    deleteEvent: true,
                    id: EventID
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

        .EventForm input {
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
                    <h1>Events</h1>
                    <p>Here you can view all the Events in the school</p>
                    <input class="form-control" id="myInput" type="text" onkeyup="searchupdate()" placeholder="Search..">
                    <div class="well">
                        <table class="table table-striped table-scroll table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Event ID</th>
                                    <th scope="col">Event Name</th>
                                    <th scope="col">Start Time</th>
                                    <th scope="col">End Time</th>
                                    <th scope="col">Open Time</th>
                                    <th scope="col" style="width: 10%;">Slot Len</th>
                                    <th scope="col" style="width: 10%;">Year</th>
                                    <th scope="col" style="width: 10%;">view</th>
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
                <form id="EventUpdateForm" class="EventForm">
                    <!--Event id, name, yeargroup, select multiclasses from a list of all classes, select  from a list of multi -->
                    <input type="text" class="form-control" placeholder="Event ID" id="EventID">
                    <input type="text" class="form-control" placeholder="Event Name" id="EventName">
                    <!--date selector-->
                    <input type="datetime-local" class="form-control" placeholder="Start Time" id="EventStartTime">
                    <input type="datetime-local" class="form-control" placeholder="End Time" id="EventEndTime">
                    <input type="datetime-local" class="form-control" placeholder="Open Time" id="EventOpenTime">
                    <input type="number" class="form-control" placeholder="Slot Duration" id="EventSlotDuration">
                    <input type="number" class="form-control" placeholder="Year" id="EventYear">
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
            <p id="confirmText">Are you sure you want to update this Event?</p>
            <button type="button" id="confirmButton" class="btn btn-primary">Confirm</button>
            <button type="button" id="cancelButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
    <div id="confirmDeletion" style="display: none; position: fixed; z-index: 1; padding-top: 100px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0, 0, 0); background-color: rgba(0, 0, 0, 0.4);">
        <!--confirmation box-->
        <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%;">
            <p id="confirmDeletionText">Are you sure you want to delete this Event?</p>
            <button type="button" id="confirmDeletionButton" class="btn btn-danger">Confirm</button>
            <button type="button" id="cancelDeletionButton" class="btn btn-primary">Cancel</button>
        </div>
    </div>
</body>