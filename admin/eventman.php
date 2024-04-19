<?php
require_once '../utils.php';
//check if the user is logged in
require_once './autils.php';

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
            "error" => "Event could not be updated"
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
    <script>
        
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
                    <div class="col-md-6">
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
                            <label for="EventSlotDuration">Event Slot Duration</label>
                            <input type="number" class="form-control" id="EventSlotDuration" placeholder="Event Slot Duration">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
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
                </div>
            </form>