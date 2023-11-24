<?php
/*

require_once '../utils.php';
//check if the user is logged in
require_once './autils.php';
$events = get_all_events();
foreach ($events as $event) {
    echo $event->getName() . "<br>";
    echo format_date($event->getStartTime()) . "<br>";

}
?>
<!DOCTYPE html>
<html lang="en">*/
/*
//test pdf generation
require_once '../utils.php';
require_once '../fpdf.php';
//make a new pdf
$pdf = new FPDF();
//landscape A4
$pdf->AddPage('L', 'A4');
//set the font
$pdf->SetFont('Arial', 'B', 16);
//add some text
$pdf->Cell(40, 10, 'Hello World!');
//write a small  table
$pdf->Ln();
$pdf->Cell(40, 10, 'Table');
$pdf->Ln();
$pdf->Cell(40, 10, '1', 1);
$pdf->Cell(40, 10, '2', 1);
$pdf->Cell(40, 10, '3', 1);
$pdf->Ln();
$pdf->Cell(40, 10, '4', 1);
$pdf->Cell(40, 10, '5', 1);
$pdf->Cell(40, 10, '6', 1);
$pdf->Ln();
//output the pd
$pdf->Output();
*/
//test all the get all functions
require_once '../utils.php';

require_once './autils.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getStudents'])) {
    get_all_students();
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getTeachers'])) {
    get_all_teachers();
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getEvents'])) {
    get_all_events();
    die();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getClasses'])) {
    get_all_classes();
    die();
}
//get parents
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getParents'])) {
    get_all_parents();
    die();
}
//get prefslots
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getPrefSlots'])) {
    get_all_prefSlots();
    die();
}
//get accounts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getAccounts'])) {
    get_all_accounts();
    die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Test</title>
    <?php require_once '../includes.php'; ?>
    <script>
        //when the relevent button is clicked post with the relevent flag
        $(document).ready(function() {
            $("#getStudents").click(function(event) {
                event.preventDefault();
                $.post("test.php", {
                    getStudents: true
                }, function(data) {
                    alert(data);
                });
            });
            $("#getTeachers").click(function(event) {
                event.preventDefault();
                $.post("test.php", {
                    getTeachers: true
                }, function(data) {
                    alert(data);
                });
            });
            $("#getEvents").click(function(event) {
                event.preventDefault();
                $.post("test.php", {
                    getEvents: true
                }, function(data) {
                    alert(data);
                });
            });
            $("#getClasses").click(function(event) {
                event.preventDefault();
                $.post("test.php", {
                    getClasses: true
                }, function(data) {
                    alert(data);
                });
            });
            $("#getParents").click(function(event) {
                event.preventDefault();
                $.post("test.php", {
                    getParents: true
                }, function(data) {
                    alert(data);
                });
            });
            $("#getPrefSlots").click(function(event) {
                event.preventDefault();
                $.post("test.php", {
                    getPrefSlots: true
                }, function(data) {
                    alert(data);
                });
            });
            $("#getAccounts").click(function(event) {
                event.preventDefault();
                $.post("test.php", {
                    getAccounts: true
                }, function(data) {
                    alert(data);
                });
            });
        });
    </script>
</head>

<body>
    <div class="container">
        <form>
            <button id="getStudents" class="btn btn-primary">Get Students</button>
            <button id="getTeachers" class="btn btn-primary">Get Teachers</button>
            <button id="getEvents" class="btn btn-primary">Get Events</button>
            <button id="getClasses" class="btn btn-primary">Get Classes</button>
            <button id="getParents" class="btn btn-primary">Get Parents</button>
            <button id="getPrefSlots" class="btn btn-primary">Get PrefSlots</button>
            <button id="getAccounts" class="btn btn-primary">Get Accounts</button>
        </form>
    </div>
</body>

</html>