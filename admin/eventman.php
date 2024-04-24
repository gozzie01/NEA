<?php
require_once '../utils.php';
//check if the user is logged in
require_once './autils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getEventDetails'])) {
    $id = $_POST['id'];
    $event = new Event($id);
    $event->update();
    //just echo the event details

    $response = array(
        "name" => $event->getName(),
        "startTime" => (new DateTime($event->getStartTime()))->format('Y-m-d H:i'),
        "endTime" => (new DateTime($event->getEndTime()))->format('H:i'),
        "openTime" => (new DateTime($event->getOpenTime()))->format('Y-m-d H:i'),
        "closeTime" => (new DateTime($event->getCloseTime()))->format('Y-m-d H:i'),
        "slotDuration" => $event->getSlotDuration(),
        "year" => $event->getYear()
    );
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateClasses'])) {
    $id = $_POST['id'];
    $classes = $_POST['classes'];
    $teachers = $_POST['teachers'];

    //update the classes
    $respon = update_event_classes($id, $classes, $teachers);
    if ($respon) {
        $response = array(
            "success" => "Classes updated successfully"
        );
        echo json_encode($response);
        exit();
    } else {
        $response = array(
            "error" => "Classes could not be updated"
        );
        echo json_encode($response);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getClassesTableHTML'])) {
    $year = $_POST['year'];
    $eventID = $_POST['eventID'];
    $enabled = array();
    $enteachers = array();
    if (event_exists($eventID)) {
        $event = new Event($eventID);
        $event->update();
        $enabled = $event->getClasses();
        $enteachers = $event->getTeachers();
    }
    $classes = get_all_classes_of_year($year);
    $teachers = array();
    foreach ($classes as $class) {
        //add teachers to array
        $teachers = array_merge($teachers, $class->getTeachers());
    }
    $names = get_teachers_names($teachers);
    $html = '';
    $counter = 0;
    $counter2 = 0;
    $counter3 = 0;
    foreach ($classes as $class) {
        $html .= '<tr>';
        $html .= '<td>' . $class->getId() . '</td>';
        $html .= '<td>' . $class->getName() . '</td>';
        $html .= '<td>';
        $teachers = $class->getTeachers();
        foreach ($teachers as $teacher) {
            $html .= $names[$counter] . ', ';
            $counter++;
        }
        $html = rtrim($html, ', ');
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="checkbox" id="class' . $class->getId() . '"';
        if (in_array($class->getId(), $enabled)) {
            $html .= ' checked ';
            $counter3++;
        }
        $html .= '"name="class' . $class->getId() . '">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<select class="form-select" id="teacher' . $class->getId() . '">';
        foreach ($teachers as $teachery) {
            $html .= '<option value="' . $teachery;
            if (in_array($class->getId(), $enabled)) {
                if ($enteachers[$counter3 - 1] == $teachery) {
                    $html .= '" selected ';
                }
            }
            $html .= '">' . $names[$counter2] . '</option>';
            $counter2++;
        }
        $html .= '</select>';
        $html .= '</tr>';
    }
    echo $html;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateEvent'])) {
    try {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $date = new DateTime($_POST['date']);
        $startTime = date('Y-m-d H:i:s', strtotime($date->format('Y-m-d') . ' ' . $_POST['startTime']));
        $endTime = date('Y-m-d H:i:s', strtotime($date->format('Y-m-d') . ' ' . $_POST['endTime']));
        $openTime = date('Y-m-d H:i:s', strtotime($_POST['openTime']));
        $closeTime = date('Y-m-d H:i:s', strtotime($_POST['closeTime']));
        $slotDuration = $_POST['slotDuration'];
        $parentBounds = $_POST['parentBounds'];
        //date times are in the format yyyy-mm-ddTHH:mm
        //convert to a format sql can understand

        $year = $_POST['year'];
        //check if the Event exists
        if (event_exists($id)) {
            //update the Event
            $respon = update_event($id, $name, $startTime, $endTime, $openTime, $closeTime, $slotDuration, $year);
            set_parent_bounds($id, $parentBounds);
        } else {
            //create the Event
            $respon = create_event($name, $startTime, $endTime, $openTime, $closeTime, $slotDuration, $year);
            
        }
        if ($respon) {
            $response = array(
                "success" => "Event updated successfully"
            );
            echo json_encode($response);
            exit();
        } else {
            $response = array(
                "error" => "Event could not be updated"
            );
            echo json_encode($response);
            exit();
        }
    } catch (Exception $e) {
        $response = array(
            "error" => "Event could not be updated" . $e->getMessage()
        );
        echo json_encode($response);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../includes.php'; ?>

<head>
    <meta charset="UTF-8">
    <title>Event Management</title>
    <link rel="stylesheet" href="../css/style.css">
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

        .ClassForm input {
            margin: 2px;
        }
    </style>
    <script>
        $(document).ready(function() {
            var h = $(window).height();
            var w = $(window).width();
            $('.well').height(h - 325);
            $('.table-scroll tbody').height(h - 360);
            //get the event details
            $.ajax({
                type: "POST",
                url: "https://www.samgosden.tech/admin/eventman.php",
                data: {
                    getEventDetails: true,
                    id: <?php echo $_GET['id']; ?>
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    $('#EventName').val(data.name);
                    $('#EventYearGroup').val(data.year);
                    $('#EventOpenTime').val(data.openTime);
                    $('#EventCloseTime').val(data.closeTime);
                    $('#EventEndTime').val(data.endTime);
                    $('#EventSlotDuration').val(data.slotDuration);
                    var date = new Date(data.startTime);
                    var time = date.toTimeString().split(' ')[0];
                    date = date.toISOString().split('T')[0];
                    $('#EventDate').val(date);
                    $('#EventStartTime').val(time);
                    updateTable();

                },
                //otherwise display an error
                error: function() {}
            });
            //when the page resizes, resize the table and well
            $(window).resize(function() {
                var h = $(window).height();
                var w = $(window).width();
                $('.well').height(h - 325);
                $('.table-scroll tbody').height(h - 360);
            });
            //enable all button
            $('#enableAll').click(function() {
                enableAll();
            });
            //when any of the checkboxes are clicked, uncheck the enable all button
            // they all have class in thier id
            $('.table-scroll tbody').on('click', 'input', function() {
                $('#enableAll').prop('checked', false);
            });
            //if they are all checked, check the enable all button
            $('.table-scroll tbody').on('click', 'input', function() {
                var allChecked = true;
                $('.table-scroll tbody tr').each(function() {
                    if (!$(this).find('input').is(':checked')) {
                        allChecked = false;
                    }
                });
                $('#enableAll').prop('checked', allChecked);
            });

            //update the event
            $('#updateEvent').click(function() {
                var name = $('#EventName').val();
                var year = $('#EventYearGroup').val();
                var date = $('#EventDate').val();
                var openTime = $('#EventOpenTime').val();
                var closeTime = $('#EventCloseTime').val();
                var startTime = $('#EventStartTime').val();
                var endTime = $('#EventEndTime').val();
                var slotDuration = $('#EventSlotDuration').val();
                var parentBounds = $('#ParentBounds').val();
                $.ajax({
                    type: "POST",
                    url: "eventman.php",
                    data: {
                        updateEvent: true,
                        id: <?php echo $_GET['id']; ?>,
                        name: name,
                        year: year,
                        date: date,
                        openTime: openTime,
                        closeTime: closeTime,
                        startTime: startTime,
                        endTime: endTime,
                        slotDuration: slotDuration,
                        parentBounds: parentBounds
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {} else {
                            alert(data.error);
                        }
                    }
                });
                //get the selected classes
                var classes = [];
                var teachers = [];
                //loop through the rows
                $('.table-scroll tbody tr').each(function() {
                    var id = $(this).find('td').eq(0).text();
                    var enabled = $(this).find('input').is(':checked');
                    var teacher = $(this).find('select').val();
                    if (enabled) {
                        classes.push(id);
                        teachers.push(teacher);
                    }
                });
                //update the classes
                $.ajax({
                    type: "POST",
                    url: "eventman.php",
                    data: {
                        updateClasses: true,
                        id: <?php echo $_GET['id']; ?>,
                        classes: classes,
                        teachers: teachers
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {} else {
                            alert(data.error);
                        }
                    }
                });

            });
            // search update when the input is changed
            $('#myInput').change(function() {
                searchupdate();
            });
            //on keyup, search the table

            //get the class list and populate the table once the page has loaded and on year group change

            function updateTable() {
                var year = $('#EventYearGroup').val();
                $.ajax({
                    type: "POST",
                    url: "eventman.php",
                    data: {
                        getClassesTableHTML: true,
                        year: year,
                        eventID: <?php echo $_GET['id']; ?>
                    },
                    success: function(response) {
                        $('#mainbody').html(response);
                    }
                });
            }
            $('#EventYearGroup').change(function() {
                updateTable();
            });
        });

        function searchupdate() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("myInput");
            filter = input.value.toUpperCase();
            table = document.getElementsByClassName("table-scroll")[0];
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1]; // student column
                var tdTeacher = tr[i].getElementsByTagName("td")[2]; // teacher column
                if (td || tdTeacher) {
                    var txtValueStudent = td ? (td.textContent || td.innerText) : "";
                    var txtValueTeacher = tdTeacher ? (tdTeacher.textContent || tdTeacher.innerText) : "";
                    if (txtValueStudent.toUpperCase().indexOf(filter) > -1 || txtValueTeacher.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }

        }
        function enableAll() {
            var checked = $('#enableAll').is(':checked');
            $('.table-scroll tbody tr').each(function() {
                $(this).find('input').prop('checked', checked);
            });
        }
    </script>
</head>
<?php include_once '../admin/nav.php'; ?>

<body>
    <div style="display: flex; margin-top: 2pt;">
        <div class="container">
            <div style="display: flex; align-items: center;">
                <h1>Event Management</h1>
                <div style="padding-left: 8pt;">
                    <a href="events.php" class="btn">
                        < <u>Back</u>
                    </a>
                </div>
            </div>
            <!--this page is for making new events and editing existing ones-->
            <!--contain a list of classes for the selected year group-->
            <!--contain a list of teachers-->
            <!--have a select all button for the classes-->

            <form>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="EventName">Event Name</label>
                            <input type="text" class="form-control" id="EventName" placeholder="Event Name">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="EventYearGroup">Year Group</label>
                            <select class="form-select" id="EventYearGroup">
                                <option value="7">Year 7</option>
                                <option value="8">Year 8</option>
                                <option value="9">Year 9</option>
                                <option value="10">Year 10</option>
                                <option value="11">Year 11</option>
                                <option value="12">Year 12</option>
                                <option value="13">Year 13</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="EventOpenTime">Booking Open Time</label>
                            <input type="datetime-local" class="form-control" id="EventOpenTime" placeholder="Event Time">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="EventEndTime">Booking End Time</label>
                            <input type="datetime-local" class="form-control" id="EventCloseTime" placeholder="Event Time">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="EventDate">Event Date</label>
                            <input type="date" class="form-control" id="EventDate" placeholder="Event Date">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="EventStartTime">Event Start Time</label>
                            <input type="time" class="form-control" id="EventStartTime" placeholder="Event Time">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="EventEndTime">Event End Time</label>
                            <input type="time" class="form-control" id="EventEndTime" placeholder="Event Time">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="EventSlotDuration">Slot Len</label>
                            <input type="number" class="form-control" id="EventSlotDuration" placeholder="Event Slot Duration">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <!-- selection for how many bounds to give the parent, none, only before or after or both -->
                            <label for="ParentBounds">Parent Bounds</label>
                            <select class="form-select" id="ParentBounds">
                                <option value="0">None</option>
                                <option value="1">One</option>
                                <option value="2">Both</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- classlist -->
                <div class="row">
                    <div class="col-md-12">
                        <label for="myInput">Class Selection</label>
                        <input class="form-control" id="myInput" type="text" onkeyup="searchupdate()" placeholder="Search..">
                        <div class="well">
                            <table class="table table-striped table-scroll table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">Class ID</th>
                                        <th scope="col">Class Name</th>
                                        <th scope="col">Teachers</th>
                                        <th scope="col">Enabled <input type="checkbox" id="enableAll"></th>
                                        <th scope="col">Teacher</th>
                                    </tr>
                                </thead>
                                <tbody id="mainbody">
                                    <?php
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary" id="updateEvent">Update Event</button>
                <button type="button" class="btn btn-danger" id="generateTimetable">Generate Timetable</button>
            </form>
        </div>
    </div>
</body>